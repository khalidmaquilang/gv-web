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
  
  /**
     * Detect video aspect ratio using ffprobe
    */
    function getVideoAspectRatio(videoPath, ffprobePath) {
        const probe = spawnSync(ffprobePath, [
            '-v', 'error',
            '-select_streams', 'v:0',
            '-show_entries', 'stream=width,height',
            '-of', 'csv=p=0',
            videoPath
        ]);
        const output = probe.stdout.toString().trim();
        if (!output) {
            throw new Error('Failed to detect video dimensions');
        }
        const [width, height] = output.split(',').map(Number);
        return { width, height, ratio: width / height };
    }
    /**
    * Get optimal scaling filter based on aspect ratio
    * Preserves original aspect ratio while standardizing resolution
    * Used for camera-recorded videos
    */
    function getOptimalScale(aspectRatio) {
        // Portrait (0.4 - 0.65): Scale to 1080 width, maintain aspect ratio
        if (aspectRatio <= 0.65) {
            return 'scale=1080:-2:force_original_aspect_ratio=decrease';
        }
        // Square-ish (0.65 - 1.2): Scale to 1080x1080 with padding if needed
        else if (aspectRatio <= 1.2) {
            return 'scale=1080:1080:force_original_aspect_ratio=decrease,pad=1080:1080:(ow-iw)/2:(oh-ih)/2:black';
        }
        // Landscape (1.2+): Scale to 1920 width, maintain aspect ratio
        else {
            return 'scale=1920:-2:force_original_aspect_ratio=decrease';
        }
    }
    /**
    * Get compression-only filter for uploaded videos
    * Preserves exact original dimensions, just compresses
    */
    function getCompressionOnlyScale(width, height) {
        // Keep original dimensions exactly, just ensure even numbers for codec compatibility
        const evenWidth = width % 2 === 0 ? width : width - 1;
        const evenHeight = height % 2 === 0 ? height : height - 1;
        return `scale=${evenWidth}:${evenHeight}`;
    }
    export const handler = async (event) => {
        const { modelId, filename, modelType, isVideo, musicFilename, userId, fromCamera = true } = event;
        if (!modelId || !filename || isVideo == null || !modelType) {
            return { status: 'error', message: 'Missing modelId, filename, isVideo, or modelType' };
        }
        console.log(`Processing video - Source: ${fromCamera ? 'Camera' : 'Upload'}`);
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
            // 3. Detect aspect ratio for video files and choose processing strategy
            let scaleFilter = null;
            let videoInfo = null;
            if (isVideo) {
                videoInfo = getVideoAspectRatio(videoInputPath, ffprobePath);
                if (fromCamera) {
                    // Camera: Standardize to common aspect ratios
                    scaleFilter = getOptimalScale(videoInfo.ratio);
                    console.log(`Camera video - Original: ${videoInfo.width}x${videoInfo.height}, ratio: ${videoInfo.ratio.toFixed(2)}`);
                    console.log(`Standardizing with filter: ${scaleFilter}`);
                } else {
                    // Upload: Preserve original dimensions, just compress
                    scaleFilter = getCompressionOnlyScale(videoInfo.width, videoInfo.height);
                    console.log(`Uploaded video - Preserving dimensions: ${videoInfo.width}x${videoInfo.height}, ratio: ${videoInfo.ratio.toFixed(2)}`);
                    console.log(`Compression-only filter: ${scaleFilter}`);
                }
            }
            // 4. Construct FFmpeg Args
            let args = ['-i', videoInputPath];
            if (musicInputPath) {
                // MERGE LOGIC (Mutes original video, uses external audio)
                args.push('-i', musicInputPath);
                args.push(
                    '-map', '0:v:0',
                    '-map', '1:a:0',
                    // Use dynamic scale filter that preserves aspect ratio
                    '-vf', `${scaleFilter},format=yuv420p`,
                    // Cap framerate to 30fps to avoid decoder overloads
                    '-r', '30',
                    '-c:v', 'libx264',
                    '-preset', 'veryfast',
                    '-crf', '23',
                    // Use 'Main' profile instead of 'High' for wider device support
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
                    '-vf', `${scaleFilter},format=yuv420p`,
                    '-r', '30',
                    '-t', '60',
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
            console.log(`Running FFmpeg with args: ${args.join(' ')}`);
            const ffmpeg = spawnSync(ffmpegPath, args);
            if (ffmpeg.status !== 0) {
                const errorMsg = ffmpeg.stderr?.toString() || 'Unknown FFmpeg error';
                throw new Error(`FFmpeg error: ${errorMsg}`);
            }
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
            // 8. Extract Thumbnail from the PROCESSED video
            // This ensures the thumb matches the video's actual dimensions and aspect ratio
            if (isVideo) {
                console.log("Extracting frame for thumbnail...");
                // Use the SAME filter as the video to ensure thumbnail matches
                const thumbResult = spawnSync(ffmpegPath, [
                    '-i', outputPath, // Extract from processed video
                    '-ss', '00:00:00.500',
                    '-vframes', '1',
                    '-vf', scaleFilter, // Use same scale filter as video
                    '-vcodec', 'libwebp',
                    '-lossless', '0',
                    '-q:v', '30',
                    '-y', thumbPath
                ]);
                if (thumbResult.status !== 0) {
                    console.warn('Thumbnail extraction failed, but continuing...');
                }
            }
            // 9. Upload Thumbnail
            if (isVideo && fs.existsSync(thumbPath)) {
                console.log("Uploading thumbnail...");
                const thumbUploadRes = await fetch(`https://${BUNNY_REGION}.storage.bunnycdn.com/${BUNNY_STORAGE_ZONE}/${file_path}/${outputThumbName}`, {
                    method: 'PUT',
                    headers: { 'AccessKey': BUNNY_ACCESS_KEY, 'Content-Type': 'image/webp' },
                    body: fs.readFileSync(thumbPath)
                });
                if (!thumbUploadRes.ok) {
                    console.warn('Thumbnail upload failed, but video was successful');
                }
            }
            // 10. Webhook Success
            console.log("Sending success webhook...");
            await fetch(WEBHOOK_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    model_id: modelId,
                    model_type: modelType,
                    duration,
                    status: 'success',
                    path: `${file_path}/${outputFileName}`,
                    thumbnail_path: `${file_path}/${outputThumbName}`,
                    // Include video metadata for reference
                    original_width: videoInfo?.width,
                    original_height: videoInfo?.height,
                    aspect_ratio: videoInfo?.ratio,
                    from_camera: fromCamera
                })
            });
            return {
                status: 'success',
                modelId,
                duration,
                dimensions: videoInfo ? `${videoInfo.width}x${videoInfo.height}` : 'N/A',
                source: fromCamera ? 'camera' : 'upload',
                processing_mode: fromCamera ? 'standardized' : 'compressed'
            };
        } catch (error) {
            console.error("Critical Error:", error.message);
            console.error("Stack trace:", error.stack);
            // Webhook Error
            await fetch(WEBHOOK_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    model_id: modelId,
                    model_type: modelType,
                    duration: 0,
                    status: 'error',
                    message: error.message
                })
            });
            return { status: 'error', message: error.message };
        } finally {
            // Safe Cleanup
            console.log("Cleaning up temporary files...");
            [videoInputPath, musicInputPath, outputPath, thumbPath].forEach(file => {
                if (file && fs.existsSync(file)) {
                    try {
                        fs.unlinkSync(file);
                    } catch (cleanupError) {
                        console.warn(`Failed to cleanup ${file}:`, cleanupError.message);
                    }
                }
            });
        }
    };
    ```
- Add the two layers

# Setup RTMP Server
- Make sure you have nginx installed on your server
- Run `sudo apt install libnginx-mod-rtmp`
- `sudo nano /etc/nginx/nginx.conf`
  ```
    rtmp {
    server {
        listen 1935;
        chunk_size 4096;

        application live {
            live on;
            record off;

            # FIXED PATH: Save directly into the "public" folder so it is accessible via URL
            hls on;
            hls_path /home/forge/rtmp.maralabs.ph/public/hls;
            hls_fragment 1;
            hls_playlist_length 6;
            hls_cleanup on;
        }
    }
  }
  ```
- In your site nginx config, add this to your server block
  ```php
  # HLS files (general)
  location /hls/ {
      root /home/forge/rtmp.maralabs.ph/public;
      add_header Access-Control-Allow-Origin "*" always;
      add_header Access-Control-Expose-Headers "Content-Length";
      try_files $uri =404;
  }

  # Playlist (.m3u8)
  location ~* /hls/.+\.m3u8$ {
      root /home/forge/rtmp.maralabs.ph/public;
      add_header Cache-Control "public, max-age=2, must-revalidate";
      add_header Access-Control-Allow-Origin "*" always;
      try_files $uri =404;
  }

  # Segments (.ts, .m4s)
  location ~* /hls/.+\.(ts|m4s)$ {
      root /home/forge/rtmp.maralabs.ph/public;
      add_header Cache-Control "public, max-age=600, immutable";
      add_header Access-Control-Allow-Origin "*" always;
      try_files $uri =404;
  }
  ```
# Setup your cloudflare
- go to Caching > Caching Rules
- create 2 rules
  - HLS Segment Rule
    - (ends_with(http.request.uri.path, ".ts")) or (ends_with(http.request.uri.path, ".m4s"))
    - Edge TTL is 10 minutes
    - Browser TTL is Respect Origin TTL
  - Playlist (.m3u8)
    - (ends_with(http.request.uri.path, ".m3u8"))
    - Edge TTL is 2 seconds
    - Browser TTL is Respect Origin TTL