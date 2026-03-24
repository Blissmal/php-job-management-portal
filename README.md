# Project Folder Structure

**Job Application Project — Collaboration Reference**

This document describes the complete folder structure of the project

---

## Root Layout

```
project-root/
├───css -------------------Custom styles
├───database --------------SQL schema format
├───php
│   ├───config ------------DB connection
│   └───function ----------DB operations (Login, Register, Job crud, application crud, profile management etc)
├───uploads ---------------Resume folder uploads
└───views -----------------Site files
    └───partials ----------Nav and Footer
index.php -----------------Entry point
```

---

## Directory Reference

### `/php/config/`

Core bootstrap files (configurations).

| File | Purpose |
|---|---|
| `connection.php` | PDO database handle via `getDB()`. Defines `BASE_URL`. |

---

### `/php/function/`

One file per server-side action. Each file is a POST or GET handler.


**Conventions for new function files:**

- We `require_once` the config at the top
- We call `session_start()` before reading `$_SESSION`
- Role and method validation before any logic
- `$_SESSION['error']` or `$_SESSION['success']` to store failed or successful messages (For custom styling)


---

### `/views`

View files are included by the entry point (`index.php`). These produce HTML output only. No database writes (Only GET requests).


---

### `/uploads/resumes/`

All resume file uploads are stored in this folder and the url string stored in the database.


#### Rules

| Rule | Detail |
|---|---|
| **Profile resumes replace on update** | `profile.php` calls `unlink()` on the old file before writing the new one |
| **Accepted formats** | PDF, DOC, DOCX only. Validated by MIME type via `finfo` |
| **Max file size** | 10 MB |
| **Filename format** | `cv_<uniqid>.<ext>` for applications, `resume_<uniqid>.<ext>` for profiles |


---

## Session Variables

| Key | Type | Set by | Consumed by |
|---|---|---|---|
| `user_id` | `int` | `login.php` | All function files |
| `role` | `string` (`seeker` / `employer`) | `login.php` | All function files, all pages |
| `success` | `string` | Any function file | Page views (flash message) |
| `error` | `string` | Any function file | Page views (flash message) |

Flash messages (`success` / `error`) are read and unset from the session before routing.
