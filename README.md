# Setup AWS Lambda Serverless for audio and video processing
```php
import { spawnSync } from 'node:child_process';
import fs from 'node:fs';
import path from 'node:path';
import crypto from 'node:crypto';

// Configuration - Ensure these are set in Lambda Environment Variables
const BUNNY_STORAGE_ZONE = "gv-dev";
const BUNNY_ACCESS_KEY = "bunny password";
const BUNNY_REGION = 'sg';

export const handler = async (event) => {
    // 0. Extract variables from the event
    const { modelId, filename, modelType, isVideo } = event;

    if (!modelId || !filename || isVideo == null || !modelType) {
        return { status: 'error', message: 'Missing modelId or filename or isVideo or modelType in event' };
    }

    const localInputName = path.basename(filename);
    const inputPath = `/tmp/${localInputName}`;
    const outputFileName = `processed_${path.parse(filename).name}.mp4`;
    const outputPath = `/tmp/${outputFileName}`;

    const ffmpegPath = '/opt/bin/ffmpeg';
    const ffprobePath = '/opt/bin/ffprobe';

    const webhook_url = 'https://ec4f11df9299.ngrok-free.app/api/v1/webhook/ffmpeg/b714fc8b-9ccb-4de7-9e42-3b8e57670ad6';

    try {
        // 1. Download
        console.log(`Downloading ${filename}...`);
        const downloadRes = await fetch(`https://${BUNNY_STORAGE_ZONE}.b-cdn.net/${filename}`, {
            headers: { 'AccessKey': BUNNY_ACCESS_KEY }
        });

        if (!downloadRes.ok) throw new Error(`Download failed: ${downloadRes.status}`);
        fs.writeFileSync(inputPath, Buffer.from(await downloadRes.arrayBuffer()));

        // 2. Identify Media Type
        console.log(`Processing ${isVideo ? 'Video' : 'Audio'}: ${filename}`);

        // 3. Construct FFmpeg Args
        let args = ['-i', inputPath];
        if (! isVideo) {
            args.push('-c:a', 'aac', '-af', 'loudnorm=I=-16:TP=-1.5:LRA=11', '-y', outputPath);
        } else {
            args.push(
                '-vf', 'scale=1080:1920:force_original_aspect_ratio=increase,crop=1080:1920',
                '-c:v', 'libx264', '-preset', 'veryfast', '-crf', '23',
                '-c:a', 'aac', '-af', 'loudnorm=I=-16:TP=-1.5:LRA=11',
                '-movflags', '+faststart', '-y', outputPath
            );
        }

        // 4. Run FFmpeg
        const ffmpeg = spawnSync(ffmpegPath, args);
        if (ffmpeg.status !== 0) throw new Error(`FFmpeg error: ${ffmpeg.stderr?.toString()}`);

        // 5. Run FFprobe (from the second layer)
        const ffprobe = spawnSync(ffprobePath, [
            '-v', 'error', '-show_entries', 'format=duration', '-of', 'default=noprint_wrappers=1:nokey=1', outputPath
        ]);
        const duration = parseFloat(ffprobe.stdout?.toString().trim()) || 0;

	    const file_path = isVideo ? 'processed/videos' : 'processed/musics';

        // 6. Upload to Bunny
        console.log("Uploading processed file...");
        const fileBuffer = fs.readFileSync(outputPath);
        console.log(`https://${BUNNY_REGION}.storage.bunnycdn.com/${BUNNY_STORAGE_ZONE}/${file_path}/${outputFileName}`);
        const uploadRes = await fetch(`https://${BUNNY_REGION}.storage.bunnycdn.com/${BUNNY_STORAGE_ZONE}/${file_path}/${outputFileName}`, {
            method: 'PUT',
            headers: {
                'AccessKey': BUNNY_ACCESS_KEY,
                'Content-Type': 'application/octet-stream',
                'accept': 'application/json'
            },
            body: fileBuffer // This is the Node.js equivalent of --data-binary
        });

        console.log(`Bunny Response Status: ${uploadRes.status} (${uploadRes.statusText})`);

        if (!uploadRes.ok) throw new Error(`Upload failed: ${await uploadRes.text()}`);

        // 7. Webhook back to Laravel
        const webhookPayload = { model_id: modelId, model_type: modelType, duration, status: 'success', path: `${file_path}/${outputFileName}` };

        await fetch(webhook_url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(webhookPayload)
        });

        return { status: 'success', modelId, duration };

    } catch (error) {
        console.error("Critical Error:", error.message);

        // 7. Webhook back to Laravel
        const webhookPayload = { model_id: modelId, model_type: modelType, duration: 0, status: 'error', path: '' };

        await fetch(webhook_url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(webhookPayload)
        });            

        return { status: 'error', message: error.message };
    } finally {
        // Final Cleanup of /tmp
        [inputPath, outputPath].forEach(file => {
            if (fs.existsSync(file)) fs.unlinkSync(file);
        });
    }
};
```