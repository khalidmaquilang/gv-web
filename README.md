# Setup AWS Lambda Serverless for audio and video processing
- Setup the layer first
  - Download ffmpeg-release-amd64-static.tar.xz at https://johnvansickle.com/ffmpeg/
  - Create two layers (ffmpeg and ffprob)
  - First layer
    - create a bin folder
    - put ffmpeg
    - zip bin
    - upload
  - Second Layer
    - create a bin folder
    - put ffprob
    - zip bin
    - upload
- Create lambda function
  ```php
  import { spawnSync } from 'node:child_process';
  import fs from 'node:fs';
  import path from 'node:path';
  import crypto from 'node:crypto';
  
  // Configuration - Ensure these are set in Lambda Environment Variables
  const BUNNY_STORAGE_ZONE = process.env.BUNNY_STORAGE_ZONE || "gv-dev";
  const BUNNY_ACCESS_KEY = process.env.BUNNY_ACCESS_KEY || "xxxx";
  const BUNNY_REGION = process.env.BUNNY_REGION || 'sg';
  const WEBHOOK_URL = 'https://eb5c238860c7.ngrok-free.app/api/v1/webhook/ffmpeg/b714fc8b-9ccb-4de7-9e42-3b8e57670ad6';
  
  export const handler = async (event) => {
    const { modelId, filename, modelType, isVideo, musicFilename, userId } = event;

    if (!modelId || !filename || isVideo == null || !modelType ) {
        return { status: 'error', message: 'Missing modelId, filename, isVideo, or modelType' };
    }

    const localInputName = path.basename(filename);
    const videoInputPath = `/tmp/vid_${localInputName}`;
    const musicInputPath = musicFilename ? `/tmp/mus_${path.basename(musicFilename)}` : null;

    const outputFileName = `processed_${path.parse(localInputName).name}.mp4`;
    const outputThumbName = `processed_${path.parse(localInputName).name}.webp`;

    const outputPath = `/tmp/${outputFileName}`;
    const thumbPath = `/tmp/${outputThumbName}`;

    const ffmpegPath = '/opt/bin/ffmpeg';
    const ffprobePath = '/opt/bin/ffprobe';

    try {
        // 1. Download Main File
        console.log(`Downloading ${filename}...`);
        const downloadRes = await fetch(`https://${BUNNY_STORAGE_ZONE}.b-cdn.net/${filename}`, {
            headers: { 'AccessKey': BUNNY_ACCESS_KEY }
        });
        if (!downloadRes.ok) throw new Error(`Video Download failed: ${downloadRes.status}`);
        fs.writeFileSync(videoInputPath, Buffer.from(await downloadRes.arrayBuffer()));

        // 2. Download Music (If provided)
        if (musicInputPath) {
            console.log(`Downloading music: ${musicFilename}`);
            const musRes = await fetch(`https://${BUNNY_STORAGE_ZONE}.b-cdn.net/${musicFilename}`, {
                headers: { 'AccessKey': BUNNY_ACCESS_KEY }
            });
            if (!musRes.ok) throw new Error(`Music Download failed: ${musRes.status}`);
            fs.writeFileSync(musicInputPath, Buffer.from(await musRes.arrayBuffer()));
        }

        console.log(`Processing ${isVideo ? 'Video' : 'Audio'}: ${filename}`);

        // 4. Construct FFmpeg Args
        let args = ['-i', videoInputPath];

        if (musicInputPath) {
            // MERGE LOGIC (Mutes original video, uses external audio)
            args.push('-i', musicInputPath);
            args.push(
                '-map', '0:v:0',
                '-map', '1:a:0',
                // 1. Force 9:16, convert to yuv420p (8-bit)
                '-vf', 'scale=1080:1920:force_original_aspect_ratio=increase,crop=1080:1920,format=yuv420p',
                // 2. Cap framerate to 30fps to avoid decoder overloads
                '-r', '30',
                '-c:v', 'libx264',
                '-preset', 'veryfast',
                '-crf', '23',
                // 3. Use 'Main' profile instead of 'High' for wider device support
                '-profile:v', 'main',
                '-level', '4.0',
                '-c:a', 'aac', '-b:a', '128k',
                '-shortest',
                '-movflags', '+faststart', '-y', outputPath
            );
        } else if (!isVideo) {
            // AUDIO ONLY
            args.push('-c:a', 'aac', '-b:a', '192k', '-af', 'loudnorm=I=-16:TP=-1.5:LRA=11', '-y', outputPath);
        } else {
            // VIDEO ONLY (Normalizes existing audio)
            args.push(
                '-vf', 'scale=1080:1920:force_original_aspect_ratio=increase,crop=1080:1920,format=yuv420p',
                '-r', '30',
                '-c:v', 'libx264',
                '-preset', 'veryfast',
                '-crf', '23',
                '-profile:v', 'main',
                '-level', '4.0',
                '-c:a', 'aac', '-af', 'loudnorm=I=-16:TP=-1.5:LRA=11',
                '-movflags', '+faststart', '-y', outputPath
            );
        }

        // 5. Run FFmpeg
        const ffmpeg = spawnSync(ffmpegPath, args);
        if (ffmpeg.status !== 0) throw new Error(`FFmpeg error: ${ffmpeg.stderr?.toString()}`);

        // 6. Get Duration
        const ffprobe = spawnSync(ffprobePath, [
            '-v', 'error', '-show_entries', 'format=duration', '-of', 'default=noprint_wrappers=1:nokey=1', outputPath
        ]);
        const duration = parseFloat(ffprobe.stdout?.toString().trim()) || 0;

        const file_path = isVideo ? `${userId}/processed/videos` : 'processed/musics';

        // 7. Upload to Bunny
        console.log("Uploading to Bunny Storage...");
        const uploadRes = await fetch(`https://${BUNNY_REGION}.storage.bunnycdn.com/${BUNNY_STORAGE_ZONE}/${file_path}/${outputFileName}`, {
            method: 'PUT',
            headers: {
                'AccessKey': BUNNY_ACCESS_KEY,
                'Content-Type': isVideo ? 'video/mp4' : 'audio/mp4',
            },
            body: fs.readFileSync(outputPath)
        });

        if (!uploadRes.ok) throw new Error(`Upload failed: ${await uploadRes.text()}`);

        // Extract Thumbnail from the PROCESSED video
        // This ensures the thumb is exactly 1080x1920 and matches the video colors
        if (isVideo) {
            console.log("Extracting frame for thumbnail...");
            const filterString = 'scale=1080:1920:force_original_aspect_ratio=increase,crop=1080:1920';

            spawnSync(ffmpegPath, [
                '-i', outputPath, // Extracting from the already processed video is safest
                '-ss', '00:00:00.500',
                '-vframes', '1',
                '-vf', filterString, // This forces the 1080x1920 size
                '-vcodec', 'libwebp',
                '-lossless', '0',
                '-q:v', '30',
                '-y', thumbPath
            ]);
        }

        if (isVideo && fs.existsSync(thumbPath)) {
            await fetch(`https://${BUNNY_REGION}.storage.bunnycdn.com/${BUNNY_STORAGE_ZONE}/${file_path}/${outputThumbName}`, {
                method: 'PUT',
                headers: { 'AccessKey': BUNNY_ACCESS_KEY, 'Content-Type': 'image/webp' },
                body: fs.readFileSync(thumbPath)
            });
        }

        // 9. Webhook Success
        await fetch(WEBHOOK_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                model_id: modelId,
                model_type: modelType,
                duration,
                status: 'success',
                path: `${file_path}/${outputFileName}`,
                thumbnail_path: `${file_path}/${outputThumbName}`
            })
        });

        return { status: 'success', modelId, duration };

    } catch (error) {
        console.error("Critical Error:", error.message);
        // Webhook Error
        await fetch(WEBHOOK_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ model_id: modelId, model_type: modelType, duration: 0, status: 'error', message: error.message })
        });
        return { status: 'error', message: error.message };
    } finally {
        // Safe Cleanup
        [videoInputPath, musicInputPath, outputPath, thumbPath].forEach(file => {
            if (file && fs.existsSync(file)) fs.unlinkSync(file);
        });
    }
  };
    ```
- Add the two layers