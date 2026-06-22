# 🎯 Gamma AI Prompt — AeroDesk Project Presentation

Paste the prompt below into **Gamma AI** (gamma.app → "Create new" → "Paste in text"
or "Generate"). Choose ~14–16 cards, a professional/tech theme, and a blue/cyan
color scheme to match the project.

---

## ✅ PROMPT TO PASTE INTO GAMMA

Create a professional, modern presentation (14-16 slides) for a college DBMS
project called **"AeroDesk - Passenger Flight Booking Through Agency."**
Use a sleek tech aesthetic with a dark theme, glassmorphism style, and a
blue (#2563EB) + cyan (#06B6D4) + violet (#8B5CF6) color palette. Use clean
icons, charts, and minimal text per slide (bullet points, not paragraphs).

Project context: It is a web-based Airline Reservation & Booking Management
System where travel agencies book flights for passengers, admins manage flight
schedules, and the system demonstrates DBMS concepts. Built with HTML, CSS,
JavaScript, PHP, MySQL and XAMPP. Course: CSIT-405 DBMS, Sagar Institute of
Research and Technology. Designed & Developed by Ansh Kumar Singh.

Generate these slides:

1. **Title slide** - "AeroDesk - Passenger Flight Booking Through Agency",
   subtitle "Airline Reservation & Booking Management System", a flat plane/
   ticket graphic, and "Designed & Developed by Ansh Kumar Singh -
   CSIT-405 DBMS, Sagar Institute of Research and Technology."

2. **Introduction** - What the project is: a web app for booking flights through
   agencies; helps passengers reserve flights, agencies process bookings, admins
   manage schedules; demonstrates relational design, SQL, joins, normalization.

3. **Problem Statement** - Manual flight booking is slow, error-prone, and
   scattered. Need a centralized, secure, fast digital system for agencies.

4. **Objectives** - Maintain passenger/agency/flight data; process bookings;
   search flights by route/date/time; generate reports; role-based access;
   demonstrate SQL operations in a real scenario.

5. **Technology Stack** - HTML (structure), CSS (glassmorphism UI), JavaScript
   (AJAX/SPA), PHP (server logic), MySQL (database), XAMPP (local server). Show
   as labeled icon cards.

6. **System Modules** - Passenger, Agency, Flight, Booking, Users/Authentication,
   Reports, Audit Logs, SQL Lab, and a public Landing Page. Show as a grid.

7. **Database Design (Schema)** - 6 tables:
   passenger(pid PK, pname, pgender, pcity);
   agency(aid PK, aname, acity);
   flight(fid PK, fdate, time, src, dest);
   booking(bid PK, pid FK, aid FK, fid FK, fdate, seat);
   users(uid PK, username, password, role);
   audit_logs(lid PK, username, role, action, detail, ip, ts).
   Present as a clean table or schema diagram.

8. **ER Diagram / Relationships** - Entities: Passenger, Agency, Flight, Booking.
   Booking is the junction table linking all three (many-to-many resolved).
   One passenger -> many bookings; one agency -> many bookings; one flight ->
   many bookings. Database is normalized to 3NF.

9. **Role-Based Access Control** - Three roles with a comparison table:
   Admin = full control (records CRUD, users, audit logs, reports, exports,
   reset DB); Agent = book/cancel tickets, view database, reports, exports, PDF
   tickets; Customer = search flights, view bookings, download own ticket.

10. **Key Features** - Instant booking with auto-assigned seats; PDF boarding
    pass; CSV/Excel export; analytics dashboard with charts; live SQL Lab running
    all 30 case-study queries; audit logs; full CRUD; fully responsive UI;
    light/dark theme. Show as feature cards with icons.

11. **Security Features** - Hashed passwords (password_hash/bcrypt), session-based
    login, server-side role permission checks, audit logging of every sensitive
    action (with user, role, IP, timestamp), input escaping to prevent SQL
    injection and XSS.

12. **SQL Concepts Demonstrated** - 30 case-study queries covering: joins,
    subqueries, GROUP BY/HAVING, aggregate functions (COUNT/AVG/MAX), set
    operations (union/intersection), DISTINCT, LIKE pattern matching, and
    self-joins (consecutive-date bookings).

13. **Screens / Workflow** - Landing page -> Login (role-based) -> Dashboard ->
    Search & Book -> Boarding pass (PDF) -> Reports/Exports. Describe each step
    briefly (use placeholder screenshot frames).

14. **Advantages** - Centralized data, fast search, reduced manual work, better
    security, supports multiple agencies and roles, real-time updates,
    professional responsive UI.

15. **Limitations & Future Enhancements** - Limitations: no real payment gateway,
    demo dataset, local server. Future: online payment integration, OTP/email/SMS
    notifications, real-time flight status, mobile app, AI-based recommendations.

16. **Conclusion & Thank You** - AeroDesk successfully demonstrates practical
    implementation of DBMS concepts (relational design, SQL, normalization,
    joins) in a real-world airline booking scenario, with a secure, role-based,
    responsive web application. End with "Thank You - Designed & Developed by
    Ansh Kumar Singh."

Keep text concise and bullet-based, use consistent iconography, add simple charts
where relevant (e.g. bookings by destination, role comparison table), and keep
the blue/cyan/violet tech theme throughout.

---

## 💡 Tips after Gamma generates
- Set **Theme** to a dark/tech one; tweak accent to blue (#2563EB).
- Replace the placeholder image frames on the "Screens / Workflow" slide with
  real screenshots from your running app (Dashboard, Search & Book, Boarding
  pass, SQL Lab).
- Reduce to ~12 slides if your time limit is short (merge Advantages +
  Limitations, or Schema + ER).
- Export to **PDF or PPTX** from Gamma for submission.

*Project: AeroDesk - Passenger Flight Booking Through Agency · Ansh Kumar Singh*
