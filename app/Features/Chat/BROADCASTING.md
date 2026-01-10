# Chat Broadcasting Setup

The Chat API includes real-time broadcasting support using Laravel Broadcasting. This enables instant message delivery and read receipts without polling.

## Overview

Two broadcast events are implemented:
1. **MessageSent** - Notifies receiver when a new message arrives
2. **MessageRead** - Notifies sender when their message is read

## Events

### MessageSent Event

**Triggered:** When a new chat message is created  
**Channel:** `chat.user.{receiver_id}` (Private)  
**Event Name:** `message.sent`

**Broadcast Data:**
```json
{
  "id": "uuid",
  "sender_id": "uuid",
  "receiver_id": "uuid",
  "message": "Hello!",
  "is_read": false,
  "created_at": "2026-01-11T01:00:00.000Z",
  "sender": {
    "id": "uuid",
    "username": "john_doe",
    "name": "John Doe"
  }
}
```

### MessageRead Event

**Triggered:** When a message is marked as read  
**Channel:** `chat.user.{sender_id}` (Private)  
**Event Name:** `message.read`

**Broadcast Data:**
```json
{
  "id": "uuid",
  "is_read": true,
  "read_at": "2026-01-11T01:05:00.000Z",
  "receiver_id": "uuid"
}
```

## Channel Authorization

Private channels are used to ensure users only receive their own messages.

**Channel Pattern:** `chat.user.{userId}`

**Authorization Logic:**
- Users can only subscribe to channels matching their own user ID
- Implemented in `routes/channels.php`

## Setup Instructions

### 1. Choose a Broadcasting Driver

Laravel supports multiple broadcasting drivers:
- **Reverb** (Laravel's native WebSocket server) - Recommended
- **Pusher** (Third-party service)
- **Ably** (Third-party service)
- **Redis** (Self-hosted)
- **Log** (Development only)

### 2. Environment Configuration

#### Option A: Reverb (Recommended for Laravel 11+)

Add to your `.env`:
```env
BROADCAST_DRIVER=reverb

REVERB_APP_ID=your-app-id
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http
```

Install Reverb:
```bash
php artisan install:broadcasting
```

Start Reverb server:
```bash
php artisan reverb:start
```

#### Option B: Pusher

Add to your `.env`:
```env
BROADCAST_DRIVER=pusher

PUSHER_APP_ID=your-app-id
PUSHER_APP_KEY=your-app-key
PUSHER_APP_SECRET=your-app-secret
PUSHER_APP_CLUSTER=mt1
```

Install Pusher PHP SDK:
```bash
composer require pusher/pusher-php-server
```

#### Option C: Redis

Add to your `.env`:
```env
BROADCAST_DRIVER=redis
BROADCAST_REDIS_CONNECTION=default
```

Make sure Redis is configured and running.

### 3. Queue Configuration

Broadcasting works best with queues. Update `.env`:
```env
QUEUE_CONNECTION=redis  # or database, sqs, etc.
```

Run queue worker:
```bash
php artisan queue:work
```

### 4. Frontend Setup (Laravel Echo)

Install Laravel Echo and a WebSocket client:

```bash
# For Reverb
npm install --save-dev laravel-echo pusher-js

# For Pusher
npm install --save-dev laravel-echo pusher-js

# For Ably
npm install --save-dev laravel-echo ably
```

Configure Echo in your JavaScript:

#### Reverb Configuration
```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
    authEndpoint: '/broadcasting/auth',
    auth: {
        headers: {
            Authorization: `Bearer ${yourAuthToken}`,
        },
    },
});
```

#### Pusher Configuration
```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true,
    authEndpoint: '/broadcasting/auth',
    auth: {
        headers: {
            Authorization: `Bearer ${yourAuthToken}`,
        },
    },
});
```

### 5. Listen for Events

#### Listen for New Messages

```javascript
// Subscribe to the authenticated user's private channel
Echo.private(`chat.user.${userId}`)
    .listen('.message.sent', (event) => {
        console.log('New message received:', event);
        
        // Add message to UI
        addMessageToChat(event);
        
        // Show notification
        showNotification(`New message from ${event.sender.name}`);
    });
```

#### Listen for Read Receipts

```javascript
Echo.private(`chat.user.${userId}`)
    .listen('.message.read', (event) => {
        console.log('Message read:', event);
        
        // Update message UI to show read status
        updateMessageReadStatus(event.id, event.read_at);
    });
```

#### Complete Example

```javascript
// Initialize when user logs in
function initializeChatBroadcasting(userId) {
    // Subscribe to user's private channel
    const channel = Echo.private(`chat.user.${userId}`);
    
    // Listen for new messages
    channel.listen('.message.sent', (event) => {
        // Handle incoming message
        handleNewMessage(event);
    });
    
    // Listen for read receipts
    channel.listen('.message.read', (event) => {
        // Handle read receipt
        handleMessageRead(event);
    });
}

// Clean up when user logs out
function disconnectChatBroadcasting(userId) {
    Echo.leave(`chat.user.${userId}`);
}

// Example handlers
function handleNewMessage(event) {
    // Add message to chat UI
    const message = {
        id: event.id,
        text: event.message,
        sender: event.sender,
        timestamp: event.created_at,
        isRead: event.is_read
    };
    
    addMessageToChatUI(message);
    playNotificationSound();
    updateUnreadCount();
}

function handleMessageRead(event) {
    // Update UI to show message was read
    const messageElement = document.querySelector(`[data-message-id="${event.id}"]`);
    if (messageElement) {
        messageElement.classList.add('read');
        messageElement.querySelector('.read-status').textContent = 'Read';
    }
}
```

## Development & Testing

### Log Driver for Development

For local development without a WebSocket server:

```env
BROADCAST_DRIVER=log
```

Events will be logged to `storage/logs/laravel.log`.

### Testing Events

You can manually trigger events for testing:

```php
use App\Features\Chat\Models\Chat;
use App\Features\Chat\Events\MessageSent;

$chat = Chat::with(['sender', 'receiver'])->first();
broadcast(new MessageSent($chat));
```

## Production Considerations

1. **Use Queues:** Always queue broadcast events in production
2. **SSL/TLS:** Use secure connections (wss://) in production
3. **Scaling:** Consider using Redis for horizontal scaling
4. **Monitoring:** Monitor queue workers and WebSocket connections
5. **Rate Limiting:** Implement rate limiting on broadcast endpoints

## Troubleshooting

### Events Not Broadcasting

1. Check queue is running: `php artisan queue:work`
2. Verify `BROADCAST_DRIVER` is set correctly
3. Check credentials in `.env`
4. Ensure user is authenticated (Sanctum token)

### Authorization Failing

1. Verify Sanctum token is being sent in Echo config
2. Check `/broadcasting/auth` endpoint is accessible
3. Ensure user ID matches channel user ID

### Frontend Not Receiving Events

1. Verify Echo is initialized before subscribing
2. Check browser console for WebSocket errors
3. Confirm channel name matches exactly
4. Ensure event name includes the dot prefix (`.message.sent`)

## Security Notes

- Private channels require authentication
- Users can only subscribe to their own channels
- Authorization is handled via `routes/channels.php`
- Always validate user permissions server-side
- Use HTTPS/WSS in production

## Additional Resources

- [Laravel Broadcasting Documentation](https://laravel.com/docs/broadcasting)
- [Laravel Echo Documentation](https://github.com/laravel/echo)
- [Laravel Reverb Documentation](https://laravel.com/docs/reverb)
- [Pusher Documentation](https://pusher.com/docs)
