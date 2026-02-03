# Pulse API Documentation

This document provides comprehensive documentation for the Pulse REST API.

## Table of Contents

- [Authentication](#authentication)
- [Rate Limiting](#rate-limiting)
- [Error Handling](#error-handling)
- [Endpoints](#endpoints)
  - [Courses](#courses)
  - [Moderation](#moderation)
  - [Collections](#collections)
  - [Organizations](#organizations)
  - [Users](#users)
- [Webhooks](#webhooks)

## Base URL

```
Production: https://api.pulse.edu/v1
Staging: https://staging-api.pulse.edu/v1
```

## Authentication

Pulse API uses Bearer token authentication via Laravel Sanctum.

### Obtaining a Token

```http
POST /api/auth/token
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "your-password",
  "device_name": "api-client"
}
```

**Response:**
```json
{
  "access_token": "1|abc123...",
  "token_type": "Bearer",
  "expires_at": "2026-02-01T00:00:00Z"
}
```

### Using the Token

Include the token in the Authorization header:

```http
GET /api/courses
Authorization: Bearer 1|abc123...
```

### Revoking Tokens

```http
DELETE /api/auth/token
Authorization: Bearer 1|abc123...
```

## Rate Limiting

| Endpoint Type | Limit |
|---------------|-------|
| Authentication | 5 requests/minute |
| API (default) | 60 requests/minute |
| Bulk operations | 10 requests/minute |

Rate limit headers are included in responses:

```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
X-RateLimit-Reset: 1706832000
```

## Error Handling

### Error Response Format

```json
{
  "message": "Human-readable error message",
  "errors": {
    "field_name": [
      "Specific validation error"
    ]
  },
  "code": "ERROR_CODE"
}
```

### HTTP Status Codes

| Code | Description |
|------|-------------|
| 200 | Success |
| 201 | Created |
| 204 | No Content |
| 400 | Bad Request |
| 401 | Unauthorized |
| 403 | Forbidden |
| 404 | Not Found |
| 422 | Validation Error |
| 429 | Too Many Requests |
| 500 | Server Error |

---

## Endpoints

## Courses

### List Courses

```http
GET /api/courses
```

**Query Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| status | string | Filter by status (draft, published, archived) |
| difficulty | string | Filter by difficulty (beginner, intermediate, advanced) |
| search | string | Search in title/description |
| provider_id | integer | Filter by provider |
| program_id | integer | Filter by program |
| page | integer | Page number (default: 1) |
| per_page | integer | Items per page (default: 15, max: 100) |

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "title": "Introduction to Mathematics",
      "description": "A comprehensive introduction to basic math concepts.",
      "short_description": "Learn math basics",
      "status": "published",
      "difficulty_level": "beginner",
      "course_type": "standard",
      "target_grades": ["6", "7", "8"],
      "objectives": [
        "Understand basic arithmetic",
        "Learn fractions and decimals"
      ],
      "estimated_duration_minutes": 45,
      "published_at": "2026-01-15T10:00:00Z",
      "created_at": "2026-01-10T08:00:00Z",
      "creator": {
        "id": 1,
        "name": "Jane Doe"
      },
      "provider": null,
      "program": null
    }
  ],
  "links": {
    "first": "https://api.pulse.edu/v1/courses?page=1",
    "last": "https://api.pulse.edu/v1/courses?page=5",
    "prev": null,
    "next": "https://api.pulse.edu/v1/courses?page=2"
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 5,
    "per_page": 15,
    "to": 15,
    "total": 67
  }
}
```

### Get Single Course

```http
GET /api/courses/{id}
```

**Response:**
```json
{
  "data": {
    "id": 1,
    "title": "Introduction to Mathematics",
    "description": "A comprehensive introduction...",
    "status": "published",
    "difficulty_level": "beginner",
    "steps": [
      {
        "id": 1,
        "title": "Welcome",
        "content": "Welcome to the course!",
        "sort_order": 1,
        "duration_minutes": 5
      }
    ],
    "versions": [
      {
        "id": 1,
        "version_number": 1,
        "created_at": "2026-01-10T08:00:00Z"
      }
    ],
    "moderation_status": "approved",
    "analytics": {
      "enrollments_count": 150,
      "completion_rate": 0.75,
      "average_rating": 4.5
    }
  }
}
```

### Create Course

```http
POST /api/courses
Content-Type: application/json

{
  "title": "Introduction to Mathematics",
  "description": "A comprehensive introduction to basic math concepts.",
  "short_description": "Learn math basics",
  "difficulty_level": "beginner",
  "target_grades": ["6", "7", "8"],
  "objectives": [
    "Understand basic arithmetic",
    "Learn fractions and decimals"
  ],
  "estimated_duration_minutes": 45,
  "provider_id": null,
  "program_id": null
}
```

**Response:** `201 Created`
```json
{
  "data": {
    "id": 2,
    "title": "Introduction to Mathematics",
    "status": "draft",
    ...
  },
  "message": "Course created successfully"
}
```

### Update Course

```http
PUT /api/courses/{id}
Content-Type: application/json

{
  "title": "Updated Course Title",
  "description": "Updated description"
}
```

**Response:** `200 OK`

### Delete Course

```http
DELETE /api/courses/{id}
```

**Response:** `204 No Content`

### Submit Course for Review

```http
POST /api/courses/{id}/submit-for-review
```

**Response:**
```json
{
  "data": {
    "id": 1,
    "status": "pending_review",
    "moderation_queue_item": {
      "id": 5,
      "status": "pending",
      "priority": "normal",
      "sla_deadline": "2026-02-02T10:00:00Z"
    }
  },
  "message": "Course submitted for review"
}
```

### Publish Course

```http
POST /api/courses/{id}/publish
```

**Response:**
```json
{
  "data": {
    "id": 1,
    "status": "published",
    "published_at": "2026-02-01T12:00:00Z"
  },
  "message": "Course published successfully"
}
```

### Duplicate Course

```http
POST /api/courses/{id}/duplicate
```

**Query Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| include_steps | boolean | Include course steps (default: true) |

**Response:**
```json
{
  "data": {
    "id": 3,
    "title": "Introduction to Mathematics (Copy)",
    "status": "draft"
  },
  "message": "Course duplicated successfully"
}
```

---

## Moderation

### List Moderation Queue

```http
GET /api/moderation/queue
```

**Query Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| status | string | Filter by status (pending, in_review, completed) |
| priority | string | Filter by priority (low, normal, high, urgent) |
| content_type | string | Filter by content type |
| assigned_to | integer | Filter by assigned reviewer |

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "content_type": "App\\Models\\MiniCourse",
      "content_id": 5,
      "status": "pending",
      "priority": "normal",
      "sla_hours": 24,
      "sla_deadline": "2026-02-02T10:00:00Z",
      "sla_status": "on_time",
      "submitted_by": {
        "id": 2,
        "name": "John Smith"
      },
      "assigned_to": null,
      "content": {
        "id": 5,
        "title": "Physics 101",
        "status": "pending_review"
      }
    }
  ],
  "meta": {
    "stats": {
      "pending": 12,
      "in_review": 5,
      "overdue": 2
    }
  }
}
```

### Claim Queue Item

```http
POST /api/moderation/queue/{id}/claim
```

**Response:**
```json
{
  "data": {
    "id": 1,
    "status": "in_review",
    "assigned_to": {
      "id": 3,
      "name": "Reviewer Name"
    },
    "started_at": "2026-02-01T14:00:00Z"
  },
  "message": "Queue item claimed successfully"
}
```

### Release Queue Item

```http
POST /api/moderation/queue/{id}/release
```

### Approve Content

```http
POST /api/moderation/queue/{id}/approve
Content-Type: application/json

{
  "feedback": "Great content! Approved for publication.",
  "clarity_score": 0.95,
  "engagement_score": 0.90,
  "accuracy_score": 0.98,
  "appropriateness_score": 1.0
}
```

**Response:**
```json
{
  "data": {
    "queue_item": {
      "id": 1,
      "status": "completed"
    },
    "moderation_result": {
      "id": 10,
      "status": "approved",
      "feedback": "Great content! Approved for publication.",
      "scores": {
        "clarity": 0.95,
        "engagement": 0.90,
        "accuracy": 0.98,
        "appropriateness": 1.0,
        "overall": 0.96
      }
    }
  },
  "message": "Content approved successfully"
}
```

### Reject Content

```http
POST /api/moderation/queue/{id}/reject
Content-Type: application/json

{
  "feedback": "Content contains inappropriate material.",
  "flagged_issues": ["inappropriate_content", "policy_violation"]
}
```

### Request Revision

```http
POST /api/moderation/queue/{id}/request-revision
Content-Type: application/json

{
  "feedback": "Good start, but needs some improvements.",
  "suggestions": [
    "Improve clarity in the introduction",
    "Add more examples in section 2",
    "Fix grammatical errors in conclusion"
  ]
}
```

### Escalate Queue Item

```http
POST /api/moderation/queue/{id}/escalate
Content-Type: application/json

{
  "reason": "Requires senior review due to policy concerns"
}
```

---

## Collections

### List Collections

```http
GET /api/collections
```

**Query Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| type | string | Filter by type (curated, smart, personal) |
| is_public | boolean | Filter by visibility |

### Create Collection

```http
POST /api/collections
Content-Type: application/json

{
  "name": "Math Resources for Middle Organization",
  "description": "Curated collection of math resources",
  "type": "curated",
  "is_public": true
}
```

### Add Entry to Collection

```http
POST /api/collections/{id}/entries
Content-Type: application/json

{
  "content_type": "mini_course",
  "content_id": 5
}
```

### Remove Entry from Collection

```http
DELETE /api/collections/{id}/entries/{entry_id}
```

### Reorder Collection Entries

```http
PUT /api/collections/{id}/entries/reorder
Content-Type: application/json

{
  "entry_ids": [3, 1, 2, 5, 4]
}
```

---

## Organizations

### Get Current Organization

```http
GET /api/organization
```

### List Organization Members

```http
GET /api/organization/members
```

### Invite Member

```http
POST /api/organization/members/invite
Content-Type: application/json

{
  "email": "newuser@example.com",
  "role": "content_creator"
}
```

---

## Users

### Get Current User

```http
GET /api/user
```

**Response:**
```json
{
  "data": {
    "id": 1,
    "name": "Jane Doe",
    "email": "jane@example.com",
    "role": "admin",
    "organization": {
      "id": 1,
      "name": "Example Organization District"
    },
    "created_at": "2026-01-01T00:00:00Z"
  }
}
```

### Update Profile

```http
PUT /api/user
Content-Type: application/json

{
  "name": "Jane Smith",
  "notification_preferences": {
    "email_digest": "daily",
    "moderation_alerts": true
  }
}
```

---

## Webhooks

Pulse can send webhooks for various events.

### Webhook Payload Format

```json
{
  "event": "course.published",
  "timestamp": "2026-02-01T12:00:00Z",
  "data": {
    "course_id": 1,
    "title": "Introduction to Mathematics",
    "published_by": 2
  }
}
```

### Available Events

| Event | Description |
|-------|-------------|
| `course.created` | New course created |
| `course.published` | Course published |
| `course.archived` | Course archived |
| `moderation.completed` | Moderation decision made |
| `enrollment.created` | New enrollment |
| `enrollment.completed` | Learner completed course |

### Webhook Security

Verify webhook signatures using the `X-Pulse-Signature` header:

```php
$signature = hash_hmac(
    'sha256',
    $payload,
    $webhookSecret
);

if (!hash_equals($signature, $request->header('X-Pulse-Signature'))) {
    abort(401);
}
```

---

## SDK Examples

### cURL

```bash
curl -X GET "https://api.pulse.edu/v1/courses" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

### JavaScript (fetch)

```javascript
const response = await fetch('https://api.pulse.edu/v1/courses', {
  headers: {
    'Authorization': 'Bearer YOUR_TOKEN',
    'Accept': 'application/json',
  },
});

const data = await response.json();
```

### PHP (Guzzle)

```php
$client = new \GuzzleHttp\Client();

$response = $client->get('https://api.pulse.edu/v1/courses', [
    'headers' => [
        'Authorization' => 'Bearer YOUR_TOKEN',
        'Accept' => 'application/json',
    ],
]);

$data = json_decode($response->getBody(), true);
```

---

For additional support, contact api-support@pulse.edu or open an issue on GitHub.
