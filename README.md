<div align="center">
✈️ AeroDesk
Passenger Flight Booking Through Agency — Full-Stack Web Application
A premium, role-based flight booking management system where travel agencies book and manage flights for passengers — built with PHP, MySQL, and vanilla JavaScript, wrapped in a modern glassmorphism UI.
![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)
![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white)
Designed & Developed by Ansh Kumar Singh
DBMS Project (CSIT-405) · Sagar Institute of Research and Technology
</div>
![preview](assets/preview.png)
---
📖 Overview
AeroDesk digitizes the real-world workflow of a travel agency booking flights on behalf of passengers. It demonstrates core DBMS concepts — relational design, SQL joins, normalization, aggregate queries, and constraints — inside a polished, fully functional product rather than a bare academic exercise.
The system supports three distinct user roles with server-side access control, a live SQL query lab, analytics dashboards, PDF boarding-pass generation, and a self-healing database that sets itself up on first run.
---
✨ Features
🔐 Role-Based Access Control
Role	Capabilities
Admin	Full control — records, users, audit logs, reports, exports, database reset
Agent	Book/cancel tickets, view database, reports & exports, PDF tickets
Customer	Search flights, book & view own bookings, download own boarding pass
Secure authentication with bcrypt password hashing
Server-side permission enforcement on every action
Query-level data isolation — customers see only their own records
🎫 Booking & Flights
Flight search by date + time with boarding-pass-style result cards
Auto-assigned seats with duplicate-booking prevention
Downloadable PDF boarding passes (no external libraries)
Customer self-service portal linked to a passenger record
🗄️ Database & SQL
Full CRUD for passengers, agencies, flights & bookings (inline edit)
Live SQL Lab — 30 real queries (joins, subqueries, `GROUP BY/HAVING`, `UNION`, aggregates, self-joins) run live against MySQL
Self-healing schema — auto-creates, migrates, and seeds data on startup
📊 Analytics & Reporting
Dashboard charts: bookings by destination, source, and agency
CSV / Excel export of records and reports
Animated KPI counters and donut charts
💬 Extras
Feedback / contact system with admin notification bell and reply-by-email
Audit logging — every sensitive action recorded with user, role, IP & timestamp
Public marketing landing page (`home.php`)
Custom glassmorphism UI — animated gradients, light/dark theme, loading skeletons, toasts, fully responsive
---
🛠️ Tech Stack
Frontend: HTML5, CSS3, JavaScript (vanilla — no frameworks)
Backend: PHP
Database: MySQL / MariaDB
Server: XAMPP (Apache + MySQL)
---
🚀 Getting Started
Prerequisites
XAMPP (PHP 8+ and MySQL)
Installation
Clone the repository into your XAMPP `htdocs` folder:
```bash
   git clone https://github.com/<your-username>/aerodesk.git
   ```
> Place the project at `C:\xampp\htdocs\Aerodesk\`
Start Apache and MySQL from the XAMPP Control Panel.
Open the app in your browser:
```
   http://localhost/Aerodesk/home.php       → public landing page
   http://localhost/Aerodesk/index.php      → app (login)
   ```
That's it! The database (`aerodesk`) and all tables are created and seeded automatically on first load — no manual SQL import needed.
> 💡 The schema is also available in `database.sql` if you prefer to import it manually via phpMyAdmin.
---
🔑 Demo Accounts
Role	Username	Password
Admin	`admin`	`admin123`
Agent	`agent`	`agent123`
Customer	`customer`	`customer123`
---
🗃️ Database Schema
Table	Description
`passenger`	Passenger details (id, name, gender, city)
`agency`	Booking agencies (id, name, city)
`flight`	Flights (id, date, time, source, destination)
`booking`	Bookings linking passenger ↔ agency ↔ flight (with unique seat constraint)
`users`	Login accounts with roles and passenger links
`audit_logs`	Activity log for sensitive actions
`feedback`	Contact-form messages and replies
---
📂 Project Structure
```
aerodesk/
├── index.php          # App shell — boots the JS single-page interface
├── home.php           # Public marketing landing page
├── login.php          # Authentication
├── logout.php
├── config.php         # DB connection + first-run auto-setup + helpers
├── api.php            # JSON API — CRUD, bookings, users, SQL Lab, reports
├── queries.php        # The 30 case-study SQL queries
├── export.php         # CSV / Excel export
├── ticket.php         # PDF boarding-pass generator
├── database.sql       # Schema + sample data + queries (reference)
└── assets/
    ├── style.css      # Glassmorphism theme (light/dark)
    └── app.js         # SPA controller + all views
```
---
📸 Highlights
🎨 Glassmorphism design with animated gradient backgrounds
🌗 Light / dark theme toggle (remembered across visits)
📱 Fully responsive — mobile drawer navigation
⚡ Single-page feel — every screen loads via JSON API (no full reloads)
🔄 Zero-config setup — self-healing database layer
---
👤 Author
Ansh Kumar Singh
DBMS Project (CSIT-405) · Sagar Institute of Research and Technology
---
📄 License
This project was built for academic purposes. Feel free to explore and learn from it.
---
<div align="center">
⭐ If you found this project helpful, consider giving it a star!
</div>
