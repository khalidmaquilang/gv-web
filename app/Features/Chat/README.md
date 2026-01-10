# Chat API

This feature provides a complete chat messaging system with read/unread tracking functionality.

## Database Schema

The `chats` table includes the following fields:
- `id` (UUID): Primary key
- `sender_id` (UUID): Foreign key to users table
- `receiver_id` (UUID): Foreign key to users table
- `message` (TEXT): The chat message content
- `is_read` (BOOLEAN): Whether the message has been read (default: false)
- `read_at` (TIMESTAMP): When the message was read
- `created_at` (TIMESTAMP): When the message was created
- `updated_at` (TIMESTAMP): When the message was last updated

Indexes are created on:
- `(sender_id, receiver_id)` for efficient querying
- `(receiver_id, is_read)` for unread count queries

## API Endpoints

All endpoints require authentication via `auth:sanctum` middleware.

### 1. Get Chat Messages
**GET** `/api/v1/chats/{user_id}`

Get paginated chat messages between the authenticated user and another user.

**Parameters:**
- `user_id` (path): The ID of the other user

**Response:**
```json
{
  "data": [
    {
      "id": "uuid",
      "sender": { /* UserData */ },
      "receiver": { /* UserData */ },
      "message": "Hello!",
      "is_read": false,
      "read_at": null,
      "created_at": "2026-01-11T00:00:00Z",
      "formatted_created_at": "5 minutes ago"
    }
  ],
  "meta": { /* pagination metadata */ }
}
```

### 2. Send Chat Message
**POST** `/api/v1/chats`

Send a new chat message.

**Request Body:**
```json
{
  "receiver_id": "uuid",
  "message": "Hello!"
}
```

**Response:**
```json
{
  "message": "success",
  "id": "uuid"
}
```

### 3. Mark Chat as Read
**POST** `/api/v1/chats/{chat_id}/read`

Mark a specific chat message as read. Only the receiver can mark a message as read.

**Parameters:**
- `chat_id` (path): The ID of the chat message

**Response:**
```json
{
  "message": "success"
}
```

### 4. Get Unread Count
**GET** `/api/v1/chats/unread/count`

Get the total count of unread messages for the authenticated user.

**Response:**
```json
{
  "unread_count": 5
}
```

## Folder Structure

```
app/Features/Chat/
├── Actions/
│   ├── GetChatsAction.php
│   ├── SendChatMessageAction.php
│   ├── MarkChatAsReadAction.php
│   └── GetUnreadCountAction.php
├── Controllers/
│   ├── ChatsController.php
│   ├── SendChatMessageController.php
│   ├── MarkChatAsReadController.php
│   └── UnreadCountController.php
├── Data/
│   ├── ChatData.php
│   └── SendChatMessageData.php
├── Models/
│   └── Chat.php
└── Routes/
    └── api.php
```

## Usage Notes

1. **Message Retrieval**: Messages are returned in descending order (newest first) with cursor pagination.

2. **Read Status**: Messages are automatically created with `is_read = false`. Only the receiver can mark messages as read.

3. **Bilateral Chat**: The `GetChatsAction` retrieves all messages between two users regardless of who sent them.

4. **Automatic Route Registration**: Routes are automatically registered via the bootstrap/app.php configuration.

## Testing

The Chat feature includes comprehensive test coverage for all actions and models.

### Test Files

```
app/Features/Chat/Tests/
├── Actions/
│   ├── GetChatsActionTest.php
│   ├── SendChatMessageActionTest.php
│   ├── MarkChatAsReadActionTest.php
│   └── GetUnreadCountActionTest.php
└── Models/
    └── ChatTest.php
```

### Running Tests

Run all Chat feature tests:
```bash
php artisan test --filter Chat
```

Run specific test classes:
```bash
# Test GetChatsAction
php artisan test --filter GetChatsActionTest

# Test SendChatMessageAction
php artisan test --filter SendChatMessageActionTest

# Test MarkChatAsReadAction
php artisan test --filter MarkChatAsReadActionTest

# Test GetUnreadCountAction
php artisan test --filter GetUnreadCountActionTest

# Test Chat Model
php artisan test --filter ChatTest
```

### Test Coverage

**GetChatsActionTest** covers:
- Retrieving chats between two users
- Eager loading relationships
- Descending order by created_at
- Cursor pagination
- Empty results handling
- Authentication requirements
- Read status verification

**SendChatMessageActionTest** covers:
- Message creation
- Default is_read value
- Authenticated user as sender
- Authentication requirements
- ID return value
- Long message handling

**MarkChatAsReadActionTest** covers:
- Marking messages as read
- Receiver-only permissions
- Authentication requirements
- Already-read message handling
- Nonexistent chat handling
- read_at timestamp setting

**GetUnreadCountActionTest** covers:
- Counting unread messages
- Excluding read messages
- Excluding sent messages
- Zero count handling
- Authentication requirements
- Multiple senders
- Large number handling

**ChatTest** covers:
- Sender/receiver relationships
- UUID primary key
- Boolean/datetime casting
- markAsRead() method
- Idempotency
- Timestamps
- Mass assignment
- Long message storage

## Next Steps

To use this API, you need to:

1. Run the migration to create the `chats` table:
   ```bash
   php artisan migrate
   ```

2. Ensure your authentication is set up correctly with Sanctum.

3. Run tests to verify everything works:
   ```bash
   php artisan test --filter Chat
   ```

4. Test the API endpoints using your preferred API client (Postman, Insomnia, etc.)
