# ⚡ AeroDesk — Top 25 Viva Cheat Sheet (Last-Minute Revision)

**Project:** Passenger Flight Booking Through Agency (AeroDesk)
**Stack:** HTML · CSS · JavaScript · PHP · MySQL · XAMPP
**By:** Ansh Kumar Singh · CSIT-405 DBMS

---

1. **What is your project?** → A web-based Airline Reservation & Booking Management System where agencies book flights for passengers; demonstrates DBMS concepts (schema, SQL, joins, normalization).

2. **Tech stack?** → HTML, CSS, JavaScript (frontend), PHP (server), MySQL (database), XAMPP (local server).

3. **How many tables? Name them.** → 6: passenger, agency, flight, booking, users, audit_logs.

4. **Primary keys?** → passenger.pid, agency.aid, flight.fid, booking.bid, users.uid, audit_logs.lid.

5. **Foreign keys?** → booking.pid → passenger, booking.aid → agency, booking.fid → flight.

6. **What is booking table?** → The relationship/junction table linking Passenger × Agency × Flight (resolves many-to-many).

7. **Which normal form?** → 3NF (no partial or transitive dependencies).

8. **Primary key vs Foreign key?** → PK uniquely identifies a row (not null/unique); FK references another table's PK for integrity.

9. **What is normalization?** → Organizing data to remove redundancy and avoid insert/update/delete anomalies.

10. **What is referential integrity?** → FK must match an existing PK — e.g. can't delete a flight that has bookings.

11. **What is a JOIN? Example?** → Combines rows from tables. `SELECT p.pname FROM passenger p JOIN booking b ON p.pid=b.pid`.

12. **WHERE vs HAVING?** → WHERE filters rows before grouping; HAVING filters groups after GROUP BY.

13. **What is a subquery?** → A query inside another, e.g. `WHERE pid NOT IN (SELECT pid FROM booking)`.

14. **Aggregate functions?** → COUNT, SUM, AVG, MIN, MAX (used in reports & the 30 queries).

15. **DELETE vs TRUNCATE vs DROP?** → DELETE removes rows (WHERE); TRUNCATE empties a table fast; DROP removes the table itself.

16. **DDL vs DML?** → DDL = CREATE/ALTER/DROP (structure); DML = SELECT/INSERT/UPDATE/DELETE (data).

17. **Architecture?** → Frontend (HTML/CSS/JS) → PHP JSON API (api.php) via AJAX/fetch → MySQL.

18. **What are the user roles?** → Admin (full control), Agent (book/manage + reports), Customer (search/book + own ticket).

19. **How is access controlled?** → Server-side role checks on every API action + role-filtered menu; not just hidden UI.

20. **How are passwords stored?** → Hashed with PHP `password_hash()` (bcrypt), verified with `password_verify()`.

21. **What are audit logs?** → Record of every sensitive action (login, book, cancel, CRUD, export) with user, role, IP, time.

22. **What is the SQL Lab?** → A page that runs all 30 case-study queries live against MySQL and shows results.

23. **Key features?** → Search & book, PDF boarding pass, CSV/Excel export, reports, CRUD, audit logs, responsive glassmorphism UI.

24. **How to run it?** → Put folder in xampp/htdocs, start Apache + MySQL, open `localhost/aerodesk/home.php`. Login: admin/admin123.

25. **One-line summary?** → "A secure, role-based, responsive flight-booking web app demonstrating end-to-end DBMS concepts."

---

**Demo logins:** admin/admin123 · agent/agent123 · customer/customer123
**Live demo plan:** ER relationships → run a SQL Lab query → log in as each role → show normalization on your tables.
