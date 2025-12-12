# 🎓 ClassRoom Booking System

A lightweight and secure PHP + MySQL web app that lets users sign up, log in, and book classrooms or individual seats.  
It features **strong web security**, **responsive design**, and **AJAX interactivity** for smooth user experience.

---

## 🚀 Features

- 🔐 **Authentication**
  - Secure login/signup using prepared SQL statements.
  - Enforced session hardening and CSRF protection.
  - Two-Factor Authentication (2FA) using Google Authenticator.
- 🧠 **Security-First Design**
  - Content Security Policy (CSP) restricting scripts and data sources.
  - CSRF tokens (for both form and JSON/AJAX requests).
  - HSTS, Referrer-Policy, Permissions-Policy headers.
  - Secure sessions (`httponly`, `SameSite=Lax`, proxy-aware `secure` cookies).
- 🗃️ **Database Safety**
  - MySQL with parameterized queries.
  - Supports both PDO and mysqli (transition mode).
  - UTF-8MB4 throughout.
- 💻 **Responsive UI**
  - Built with modern CSS for both desktop and mobile.
  - Smooth background animation (`particles.js`).
  - AJAX-powered booking tables.
- ⚙️ **Environment-based configuration**
  - `.env.php` for production secrets (never committed to VCS).
  - `.env.example.php` as template for developers.
- 🧩 **Modular Structure**
  - Clean separation of config, assets, JS modules, and PHP endpoints.

---

## 🧱 Project Structure

```
CLASSROOMPROJECT/
│
├── assets/
│   ├── images/                # Logos and SVG illustrations
│   └── js/                    # Frontend JS modules (login, booking, etc.)
│
├── config/                    # Configuration and bootstrap files
│   ├── .env.example.php
│   ├── .env.php
│   ├── bootstrap.php          # Starts session, loads env, CSP, security headers
│   ├── csrf.php               # CSRF token utilities and validators
│   ├── db.php                 # PDO + mysqli database adapter
│   └── env.php                # env() helper and environment loader
│
├── vendor/                    # Composer dependencies (Google Authenticator)
│
├── *.php                      # App endpoints
│   ├── index.php              # Login page with CSRF + session protection
│   ├── signup.php             # Secure signup endpoint
│   ├── twofactor.php          # 2FA verification handler
│   ├── useraccount.php        # Protected dashboard (requires 2FA)
│   ├── seatBookings.php       # Seat booking management
│   ├── classRoomBookings.php  # Classroom booking management
│   ├── logout.php             # Secure logout
│   └── resetPassword.php      # Password reset form
│
├── style.css                  # Global responsive styles
├── particle.js / particle.css  # Background animation
├── classroomBooking.sql       # Database schema
├── composer.json              # Dependencies metadata
└── README.md
```

---

## 🧩 Database Schema

**Database:** `classroombooking`

Main tables:

| Table | Purpose |
|-------|----------|
| `user` | Stores user credentials, hashed passwords, 2FA flag |
| `classroom_booking` | Records classroom reservations |
| `seat_booking` | Records individual seat bookings |

---

## ⚙️ Installation

### 1️⃣ Prerequisites
- PHP ≥ 8.1
- MySQL ≥ 5.7 / MariaDB ≥ 10.4
- Composer

### 2️⃣ Clone & Install
```bash
git clone https://github.com/nerdigenius/ClassRoomBooking.git
cd ClassRoomBooking
composer install
```

### 3️⃣ Configure Environment
Copy `.env.example.php` → `.env.php` and edit the values:
```php
return [
  'APP_ENV'   => 'prod',
  'APP_URL'   => 'https://yourdomain.com',

  'DB_DRIVER' => 'mysql',
  'DB_HOST'   => '127.0.0.1',
  'DB_PORT'   => '3306',
  'DB_NAME'   => 'classroombooking',
  'DB_USER'   => 'root',
  'DB_PASS'   => 'your-password',

  'SESSION_NAME' => 'crb_session',
];
```

### 4️⃣ Import Database
Create the schema and import the SQL file:
```bash
mysql -u root -p classroombooking < classroomBooking.sql
```

### 5️⃣ Run Locally

#### Option A: PHP Built-in Server
```bash
php -S localhost:8000
```
Then visit **http://localhost:8000**

#### Option B: XAMPP / WAMP / Apache
1. Move the project folder to your `htdocs` directory (e.g., `C:\xampp\htdocs\ClassroomProject`).
2. Start Apache and MySQL via the XAMPP Control Panel.
3. Visit **http://localhost/ClassroomProject** in your browser.

---

## �️ Security Architecture

This application has been audited and hardened against common web vulnerabilities (OWASP Top 10).

### 🔐 Authentication & Session Management
- **Password Storage**: Uses strong `password_hash` (Bcrypt/Argon2) with auto-salted hashes.
- **Session Hardening**: 
  - `HttpOnly` and `Secure` cookies prevents XSS theft.
  - `SameSite=Lax` mitigates CSRF.
  - Session ID regeneration on login avoids session fixation.
- **Two-Factor Authentication (2FA)**: Time-based One-Time Password (TOTP) via Google Authenticator.
- **Brute-Force Protection**: 
  - Login: Throttles users after 5 failed attempts.
  - Signup: Rate-limited to prevent mass account creation.
  - 2FA: Rate-limited to prevent code guessing.

### 💉 Injection Prevention
- **SQL Injection**: All database queries use **Prepared Statements** (PDO/MySQLi), completely neutralizing SQL injection vectors.
- **Cross-Site Scripting (XSS)**: 
  - **Content Security Policy (CSP)**: Strict policy blocks inline scripts and restricts sources to self/trusted CDNs.
  - **Output Encoding**: User input is escaped using `htmlspecialchars` before rendering.

### 🛂 Access Control & Integrity
- **CSRF Protection**: Comprehensive protection using cryptographically secure tokens validated on all state-changing requests (POST/PUT/DELETE).
- **IDOR Prevention**: Deletion and modification logic explicitly verifies that the resource belongs to the logged-in user.
- **Rate Limiting**: Critical actions (booking, deleting) are rate-limited per session to prevent abuse.

### 🌐 Secure Configuration
- **Security Headers**:
  - `Strict-Transport-Security` (HSTS): Enforces HTTPS.
  - `X-Content-Type-Options: nosniff`: Prevents MIME-type confusion.
  - `X-Frame-Options: SAMEORIGIN`: Prevents clickjacking.
  - `Referrer-Policy`: Protects user privacy.
- **Error Handling**: Detailed errors are shown only in `dev` mode; production mode logs generic errors to file to prevent info leakage.

---

## 🧰 Deployment

### ✅ Recommended free host
- [InfinityFree](https://www.infinityfree.net/)
  - Upload the project files under `/htdocs`.
  - Create a MySQL database from the control panel.
  - Update `.env.php` credentials.
  - Ensure SSL (https://) is enabled to enforce secure cookies.

### 🔐 Production tweaks
- Set `APP_ENV => 'prod'`.
- Verify all API requests go over HTTPS.

---

## 📦 Dependencies

| Library | Purpose |
|----------|----------|
| `sonata-project/google-authenticator` | Google Authenticator 2FA |
| `particles.js` | Animated login background |
| PHP built-ins | PDO, session, JSON, password hashing |

---

## 🧑‍💻 Author

**Mir Ashiqul Hossain**  
Full-stack developer passionate about secure web apps.  
📧 ashiq.upwork.profile@gmail.com

---

## 🪪 License

This project is proprietary and closed source.  
For personal learning or demonstration use only.
