🔐 Dee-Triad Authentication System

A secure, full-stack User Authentication System built with PHP 8.x and PostgreSQL, designed to deliver seamless registration, secure login, and automated password recovery.

Dee-Triad implements modern backend security practices and clean frontend interactions to serve as a foundational authentication module for web applications.

📌 Table of Contents

Overview

Features

Tech Stack

Security Practices

Project Structure

Installation Guide

Environment Configuration

Database Setup

Running the Application

API Overview

Future Improvements

Author

License

📖 Overview

Dee-Triad is a secure authentication module designed to provide:

Reliable user registration

Secure login with hashed passwords

Email-based password recovery

AJAX-driven smooth user interactions

Environment-based secret management

This project demonstrates strong backend engineering fundamentals while maintaining a clean and interactive frontend experience.

✨ Features
🔹 User Registration

Input validation

Secure password hashing using BCRYPT

Unique email enforcement

PostgreSQL storage

🔹 Secure Login

Credential verification

PHP session management

Protected dashboard access

🔹 Password Recovery

6-digit secure verification token

Email delivery via PHPMailer

Secure password reset flow

🔹 Interactive Dashboard

Personalized user landing page

Displays authenticated user information

Secure access control

🔹 Asynchronous API Communication

AJAX-powered requests

Real-time feedback notifications

Loader animations for smooth UX

🔹 Environment Security

.env file for sensitive credentials

🛠 Tech Stack
Layer   Technology
Backend PHP 8.x
Database    PostgreSQL
Mail Service    PHPMailer
Dependency Management   Composer
Frontend    HTML5, CSS3, Vanilla JavaScript (ES6+)
🔒 Security Practices

Password hashing using password_hash() with BCRYPT

Environment variable management via PHP DotEnv

JWT Security based Authentication

Unique email constraints

Server-side validation

Separation of logic (MVC-inspired structure)

📂 Project Structure
Dee/
├── api/                # API Endpoints (login.php, register.php, reset.php)
├── css/                # Stylesheets
├── js/                 # Frontend logic & AJAX handlers
├── models/             # Core logic & database classes
├── vendor/             # Composer dependencies
├── views/              # Protected pages (dashboard.php)
├── .env                # Secret credentials (Not committed)
├── index.html          # Authentication UI
└── composer.json       # Dependency definitions
🚀 Installation Guide
1️⃣ Prerequisites

Ensure you have the following installed:

XAMPP (PHP 8.0+ enabled)

PostgreSQL

Composer (globally installed)

2️⃣ Clone or Move Project
git clone https://github.com/Dee-Traid/Dee-Auth-System.git
3️⃣ Install Dependencies

From the project root directory:

composer install

This will install:

PHPMailer

PHP DotEnv

🗄 Database Setup

Create a PostgreSQL database named:

user_auth

Then execute:

CREATE TABLE user_auth (
    id VARCHAR(50) PRIMARY KEY,
    fullname VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL
);
⚙ Environment Configuration

Create a .env file in the root directory:

DB_HOST=localhost
DB_PORT=5432
DB_NAME=user_auth
DB_USER=your_postgres_user
DB_PASS=your_postgres_password

MAIL_HOST=smtp.gmail.com
MAIL_USER=your_email@gmail.com
MAIL_PASS=your_app_password
MAIL_PORT=465
MAIL_FROM_NAME="Auto Triad Auth"

⚠️ Important:
Never commit your .env file to version control. Add it to .gitignore.

▶ Running the Application

Start Apache via XAMPP.

Ensure PostgreSQL is running.

Visit:

http://localhost/Dee/App.php

You should now be able to:

Register

Log in

Recover password

Access dashboard

🔌 API Overview
Endpoint    Method  Description
/api/register.php   POST    Register new user
/api/login.php  POST    Authenticate user
/api/reset.php  POST    Handle password recovery
/api/logout.php POST    Destroy session

All endpoints return structured JSON responses for frontend handling.

🔮 Added Features

CSRF protection tokens

Rate limiting for login attempts

Email verification on registration

JWT-based authentication


👨‍💻 Author

Dee-Traid

Goal:
Building hands-on experience in Backend Development & Engineering through practical system design and secure architecture implementation.

📄 License

This project is currently for educational and demonstration purposes.
You may modify and use it as needed.
