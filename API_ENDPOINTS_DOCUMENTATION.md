# Contact API Endpoints Documentation

## Overview
This document describes the API endpoints for managing contact favorites and personal notes in Monica.

## Base URL
All endpoints are prefixed with `/api/vaults/{vault_id}`

## Authentication
All endpoints require authentication via Laravel Sanctum with the `auth:sanctum` middleware.

## Permissions
- Read operations require `read` ability
- Write operations require `write` ability

---

## Endpoints

### 1. List All Contacts
**GET** `/api/vaults/{vault_id}/contacts`

Lists all contacts in a vault.

**Parameters:**
- `limit` (optional, integer): Number of results per page (1-100, default: 10)

**Response:**
```json
{
  "data": [
    {
      "id": "uuid",
      "vault_id": "uuid",
      "first_name": "John",
      "last_name": "Doe",
      "is_favorite": false,
      "personal_note": null,
      "created_at": 1234567890,
      "updated_at": 1234567890
    }
  ]
}
```

---

### 2. Get Contact Details
**GET** `/api/vaults/{vault_id}/contacts/{contact_id}`

Retrieves a specific contact with all details including favorite status and personal note.

**Response:**
```json
{
  "id": "uuid",
  "vault_id": "uuid",
  "first_name": "John",
  "last_name": "Doe",
  "middle_name": null,
  "nickname": "Johnny",
  "is_favorite": true,
  "personal_note": "Met at conference 2024",
  "job_position": "Software Engineer",
  "listed": true,
  "can_be_deleted": true,
  "last_updated_at": 1234567890,
  "created_at": 1234567890,
  "updated_at": 1234567890,
  "links": {
    "self": "http://localhost/api/vaults/{vault_id}/contacts/{contact_id}"
  }
}
```

---

### 3. List Favorite Contacts
**GET** `/api/vaults/{vault_id}/contacts/favorites`

Lists all contacts marked as favorites in a vault.

**Parameters:**
- `limit` (optional, integer): Number of results per page (1-100, default: 10)

**Response:**
```json
{
  "data": [
    {
      "id": "uuid",
      "vault_id": "uuid",
      "first_name": "John",
      "last_name": "Doe",
      "is_favorite": true,
      "personal_note": "Important contact",
      "created_at": 1234567890,
      "updated_at": 1234567890
    }
  ]
}
```

---

### 4. Mark Contact as Favorite
**POST** `/api/vaults/{vault_id}/contacts/{contact_id}/favorite`

Marks a contact as favorite.

**Response:**
```json
{
  "id": "uuid",
  "vault_id": "uuid",
  "first_name": "John",
  "last_name": "Doe",
  "is_favorite": true,
  "personal_note": null,
  "created_at": 1234567890,
  "updated_at": 1234567890
}
```

---

### 5. Remove Contact from Favorites
**DELETE** `/api/vaults/{vault_id}/contacts/{contact_id}/favorite`

Removes a contact from favorites.

**Response:**
```json
{
  "id": "uuid",
  "vault_id": "uuid",
  "first_name": "John",
  "last_name": "Doe",
  "is_favorite": false,
  "personal_note": null,
  "created_at": 1234567890,
  "updated_at": 1234567890
}
```

---

### 6. Toggle Favorite Status
**PATCH** `/api/vaults/{vault_id}/contacts/{contact_id}/favorite`

Toggles the favorite status of a contact (if favorite, removes; if not favorite, marks as favorite).

**Response:**
```json
{
  "id": "uuid",
  "vault_id": "uuid",
  "first_name": "John",
  "last_name": "Doe",
  "is_favorite": true,
  "personal_note": null,
  "created_at": 1234567890,
  "updated_at": 1234567890
}
```

---

### 7. Update Personal Note
**PUT** `/api/vaults/{vault_id}/contacts/{contact_id}/note`

Updates the personal note for a contact.

**Request Body:**
```json
{
  "personal_note": "Met at tech conference 2024. Interested in AI/ML."
}
```

**Response:**
```json
{
  "id": "uuid",
  "vault_id": "uuid",
  "first_name": "John",
  "last_name": "Doe",
  "is_favorite": false,
  "personal_note": "Met at tech conference 2024. Interested in AI/ML.",
  "created_at": 1234567890,
  "updated_at": 1234567890
}
```

---

## Error Responses

### 401 Unauthorized
```json
{
  "message": "Unauthenticated."
}
```

### 403 Forbidden
```json
{
  "error": "Insufficient permissions"
}
```

### 404 Not Found
```json
{
  "error": "Resource not found"
}
```

### 422 Validation Error
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "personal_note": [
      "The personal note must not exceed 65535 characters."
    ]
  }
}
```

---

## Usage Examples

### cURL Examples

#### List favorite contacts:
```bash
curl -X GET "http://localhost/api/vaults/{vault_id}/contacts/favorites" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

#### Mark contact as favorite:
```bash
curl -X POST "http://localhost/api/vaults/{vault_id}/contacts/{contact_id}/favorite" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

#### Update personal note:
```bash
curl -X PUT "http://localhost/api/vaults/{vault_id}/contacts/{contact_id}/note" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"personal_note": "Important client from Q4 2024"}'
```

#### Toggle favorite status:
```bash
curl -X PATCH "http://localhost/api/vaults/{vault_id}/contacts/{contact_id}/favorite" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

---

## Implementation Details

### Services Used
- `MarkContactAsFavorite`: Marks a contact as favorite
- `RemoveContactFromFavorites`: Removes favorite status
- `ToggleFavoriteContact`: Toggles favorite status (existing service, enhanced)
- `UpdateContactPersonalNote`: Updates personal note

### Database Schema
- `contacts.is_favorite` (boolean, default: false)
- `contacts.personal_note` (text, nullable)

### Resource
All endpoints return data formatted through `ContactResource`.

---

## Notes
- All timestamp fields are returned as Unix timestamps
- The `is_favorite` field is now stored in the main `contacts` table
- Personal notes can be up to 65,535 characters (TEXT field)
- The `listed` field filters contacts visible in listings
- The `last_updated_at` timestamp is automatically updated on modifications
