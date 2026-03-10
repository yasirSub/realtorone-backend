# RealtorOne API Documentation

**Base URL:** `https://realtorone-backend.onrender.com/api`

## Authentication
Most routes require a Bearer Token. Include this in your headers:
`Authorization: Bearer YOUR_TOKEN_HERE`

---

## 🔐 Auth Endpoints

### Health Check
`GET /health`
Returns app status and database connection status.

### Register
`POST /register`
- **Body:** `{ "name": "...", "email": "...", "password": "..." }`
- **Response:** `201 Created`

### Login
`POST /login`
- **Body:** `{ "email": "...", "password": "..." }`
- **Response:** `{ "status": "ok", "token": "...", "user": {...} }`

### Logout
`POST /logout`
- **Auth required**
- **Response:** `{ "status": "ok", "message": "Logged out successfully" }`

---

## 👤 User Profile

### Get Profile
`GET /user/profile`
- **Auth required**

### Update Profile
`PUT /user/profile`
- **Auth required**
- **Body:** `name`, `email`, `mobile`, `city`, `brokerage`, `instagram`, `linkedin`, `years_experience`, `current_monthly_income`, `target_monthly_income`

### Upload Profile Photo
`POST /user/photo`
- **Auth required**
- **Body:** `multipart/form-data` with `photo` field.

---

## 📊 Diagnosis & Dashboard

### Submit Diagnosis
`POST /diagnosis/submit`
- **Auth required**
- **Body:** `{ "primary_blocker": "leadGeneration|confidence|closing|discipline", "scores": [] }`

### Dashboard Stats
`GET /dashboard/stats`
- **Auth required**
- Returns growth score, execution rate, streaks, and weekly summaries.

---

## ✅ Activities & Tasks

### Get Activities
`GET /activities?date=YYYY-MM-DD`
- **Auth required**

### Create Activity
`POST /activities`
- **Auth required**
- **Body:** `{ "title": "...", "type": "...", "category": "task|subconscious", ... }`

### Complete Activity
`PUT /activities/{id}/complete`
- **Auth required**

---

## 📚 Learning Center

### Get Categories
`GET /learning/categories`
- **Auth required**

### Get Content
`GET /learning/content?category=...`
- **Auth required**

### Update Progress
`POST /learning/progress`
- **Auth required**
- **Body:** `{ "content_id": 1, "progress_percent": 100 }`

---

## 💬 Reven Chat (AI Assistant)

Chat works **without OPENAI_API_KEY** using basic keyword replies. Add `OPENAI_API_KEY` to `.env` for AI-powered answers.

### Send Message
`POST /chat`
- **Auth required**
- **Body:** `{ "message": "...", "session_id": 123 }` (session_id optional)
- **Response:** `{ "success": true, "reply": "...", "session_id": 123 }`

### Chat History
`GET /chat/history`
- **Auth required**
- Returns list of user's chat sessions.

`GET /chat/history/{sessionId}`
- **Auth required**
- Returns messages for a session.
