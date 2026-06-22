# 🎓 AeroDesk — COMPLETE Project Question Bank (All Possible Questions)

**Project:** AeroDesk — Passenger Flight Booking Through Agency
**Course:** CSIT-405 DBMS Case Study · Sagar Institute of Research and Technology
**Stack:** HTML · CSS · JavaScript · PHP · MySQL · XAMPP
**Designed & Developed by:** Ansh Kumar Singh

> This is an exhaustive list of every type of question that can be asked in a
> viva, lab exam, interview or project defense — grouped by topic, with short
> model answers. 200+ questions.

---

## SECTION 1 — Introduction & Overview
1. What is your project? → A web-based Airline Reservation & Booking Management System where agencies book flights for passengers; admins manage data; the app demonstrates DBMS concepts.
2. What is the full name / title of the project? → "Passenger Flight Booking Through Agency" (branded AeroDesk).
3. Why did you choose this project? → To apply DBMS theory (schema design, SQL, joins, normalization) to a real airline scenario.
4. What problem does it solve? → Replaces slow, error-prone manual booking with a centralized, secure, fast digital system.
5. Who are the end users? → Admins, Agents, Customers (and passengers, indirectly).
6. What are the main objectives? → Manage passengers/agencies/flights, process bookings, search flights, generate reports, demonstrate SQL.
7. What is the scope of the project? → Booking management, role-based access, reports, audit logs, SQL practice — within a single agency-based system.
8. What are the modules? → Passenger, Agency, Flight, Booking, Users/Auth, Reports, Audit Logs, SQL Lab, Landing page.
9. What are the advantages? → Centralized data, fast search, less manual work, security, multi-role, multi-agency support.
10. What are the limitations? → No real payment gateway, demo dataset, runs on local server, no real-time flight tracking.
11. What future enhancements are possible? → Online payments, OTP/email/SMS, real-time status, mobile app, AI recommendations.
12. Is this a real or academic project? → Academic DBMS case study, built to production-style quality.
13. How long did it take to build? → (Your answer — e.g. a few weeks across design, coding, testing.)
14. Did you work alone or in a team? → I worked on this project: Ansh Kumar Singh.
15. What is unique about your project? → Glassmorphism UI, role-based dashboards, live SQL Lab, PDF tickets, audit logs, fully responsive.

---

## SECTION 2 — DBMS Theory (very commonly asked)
16. What is data? Information? → Data = raw facts; Information = processed, meaningful data.
17. What is a database? → An organized collection of related data.
18. What is a DBMS? → Software to create, manage and query databases (e.g. MySQL).
19. What is an RDBMS? → A DBMS storing data in related tables using keys; supports SQL & relationships.
20. DBMS vs RDBMS? → RDBMS enforces relationships/keys/normalization; DBMS may not.
21. DBMS vs File system? → DBMS removes redundancy, gives security, concurrency, integrity; files don't.
22. What is a table / relation? → A collection of rows (records) and columns (attributes).
23. What is a tuple / row / record? → A single entry in a table.
24. What is an attribute / column / field? → A property of an entity (e.g. pname).
25. What is a domain? → The set of allowed values for an attribute.
26. What is degree and cardinality of a relation? → Degree = number of columns; cardinality = number of rows.
27. What is a primary key? → Uniquely identifies each row; not null, unique (e.g. pid).
28. What is a foreign key? → A column referencing another table's PK to link data (booking.pid → passenger.pid).
29. What is a candidate key? → A minimal set of attributes that can uniquely identify a row.
30. What is a super key? → Any set of attributes that uniquely identifies a row.
31. What is an alternate key? → A candidate key not chosen as the primary key.
32. What is a composite key? → A key made of two or more columns (original booking used pid+fid).
33. What is a surrogate key? → An artificial key (e.g. auto-increment bid) used as PK.
34. What is referential integrity? → FK values must match an existing PK (or be null).
35. What is entity integrity? → PK cannot be null.
36. What is a constraint? Types used here? → Rules on data: PRIMARY KEY, FOREIGN KEY, NOT NULL, UNIQUE, DEFAULT, AUTO_INCREMENT.
37. What is normalization? → Organizing data to reduce redundancy and anomalies.
38. Why normalize? → Avoid insertion, update, deletion anomalies.
39. Explain 1NF. → Atomic values, no repeating groups (all our tables satisfy this).
40. Explain 2NF. → 1NF + no partial dependency on part of a composite key.
41. Explain 3NF. → 2NF + no transitive dependency. AeroDesk schema is in 3NF.
42. Which normal form is your database in? → 3NF.
43. What is BCNF? → A stricter 3NF where every determinant is a candidate key.
44. What is denormalization? → Adding controlled redundancy for performance (e.g. seat/fdate in booking).
45. What is functional dependency? → When one attribute determines another (pid → pname).
46. What are anomalies? → Problems from poor design: insert/update/delete anomalies.
47. What is an ER model / ER diagram? → A diagram of entities and their relationships.
48. What are the entities in your project? → Passenger, Agency, Flight, Booking.
49. What is a weak entity? → An entity that depends on another for identity (Booking depends on the others).
50. What are relationship types/cardinalities? → 1:1, 1:many, many:many. We have many:many resolved by Booking.
51. How is many-to-many handled here? → Via the `booking` junction/bridge table.
52. What is a junction/bridge table? → A table linking two/more entities (booking links passenger, agency, flight).
53. What is an index? → A structure for faster lookups; PKs/UNIQUE keys are auto-indexed.
54. What is a view? → A virtual table from a saved query (theory; can be added).
55. What is a stored procedure? → A stored, reusable set of SQL statements.
56. What is a trigger? → SQL that runs automatically on insert/update/delete.
57. What is a transaction? → A unit of work that is all-or-nothing.
58. What are ACID properties? → Atomicity, Consistency, Isolation, Durability.
59. What is concurrency? → Multiple users accessing data at once; handled by the DBMS.
60. What is a deadlock? → Two transactions waiting on each other's locks forever.

---

## SECTION 3 — Schema & Tables (your specific design)
61. How many tables are there? → Six: passenger, agency, flight, booking, users, audit_logs.
62. Describe the passenger table. → pid(PK), pname, pgender, pcity.
63. Describe the agency table. → aid(PK), aname, acity.
64. Describe the flight table. → fid(PK), fdate, time, src, dest.
65. Describe the booking table. → bid(PK), pid(FK), aid(FK), fid(FK), fdate, seat; UNIQUE(pid,fid).
66. Describe the users table. → uid(PK), username, password(hash), role, created.
67. Describe the audit_logs table. → lid(PK), username, role, action, detail, ip, ts.
68. Why is bid an INT AUTO_INCREMENT? → Simple unique reference to a booking (for cancel/PDF).
69. Why UNIQUE(pid,fid) in booking? → Prevents the same passenger booking the same flight twice.
70. What data types did you use and why? → VARCHAR (ids/names), DATE (dates), TIME (time), INT (auto keys), TIMESTAMP (audit time).
71. Why VARCHAR not CHAR? → Variable-length, saves space.
72. What are the foreign keys in booking? → pid, aid, fid referencing passenger, agency, flight.
73. What happens if you delete a passenger with bookings? → Blocked by FK; the app shows "cancel bookings first".
74. How is the database created? → From database.sql, auto-imported by config.php on first run.
75. What charset/collation is used? → utf8mb4 / utf8mb4_unicode_ci (full Unicode support).

---

## SECTION 4 — SQL & the 30 Queries
76. What is SQL? → Structured Query Language to manage relational data.
77. What are SQL sub-languages? → DDL, DML, DCL, TCL.
78. What is DDL? → Data Definition: CREATE, ALTER, DROP, TRUNCATE.
79. What is DML? → Data Manipulation: SELECT, INSERT, UPDATE, DELETE.
80. What is DCL? → Data Control: GRANT, REVOKE.
81. What is TCL? → Transaction Control: COMMIT, ROLLBACK, SAVEPOINT.
82. DELETE vs TRUNCATE vs DROP? → DELETE removes rows (WHERE, logged); TRUNCATE removes all rows fast; DROP removes the table.
83. WHERE vs HAVING? → WHERE filters rows before grouping; HAVING filters groups after GROUP BY.
84. What is a JOIN? → Combines rows from tables on a condition.
85. Types of joins? → INNER, LEFT, RIGHT, FULL, SELF, CROSS.
86. Which join do you use most? → INNER JOIN.
87. Give a JOIN example from your project. → `SELECT p.pname FROM passenger p JOIN booking b ON p.pid=b.pid`.
88. What is a self join? Where used? → Joining a table to itself — Q21 (consecutive-date bookings).
89. What is a subquery? → A query nested inside another.
90. What is a correlated subquery? → A subquery referencing the outer query.
91. Give a subquery example. → Q5: `WHERE pid NOT IN (SELECT pid FROM booking)`.
92. What are aggregate functions? → COUNT, SUM, AVG, MIN, MAX.
93. What is GROUP BY? → Groups rows for aggregation.
94. What does DISTINCT do? → Removes duplicate rows.
95. What is ORDER BY? → Sorts results (ASC/DESC).
96. What is LIMIT? → Restricts number of rows; used for "top 1" queries.
97. What is LIKE / wildcards? → Pattern matching; Q29 `LIKE 'A%'`. `%`=any chars, `_`=one char.
98. What are set operations? → UNION, INTERSECT, EXCEPT/MINUS.
99. How did you do intersection (Q7)? → Match same route on both dates at 16:00 using IN subqueries.
100. How did you do union (Q8)? → `fdate IN ('2020-12-01','2020-12-02')`.
101. How to find the maximum/top record? → `ORDER BY ... DESC LIMIT 1` (Q12, Q24).
102. How to count rows per group? → `COUNT(*) ... GROUP BY` (Q22, Q28).
103. How does Q21 (consecutive dates) work? → Self-join with `DATE_ADD(fdate, INTERVAL 1 DAY)`.
104. Why do Q3/Q6/Q9 use pid 110 not 123? → pid 123 isn't in the dataset; demoed with 110 (flagged in UI).
105. Where are the 30 queries used live? → The SQL Lab module runs each against MySQL.
106. Can you run a query live? → Yes — open SQL Lab and click any of the 30.
107. How do you prevent SQL injection? → Escaping input with mysqli_real_escape_string (prepared statements ideal).
108. What is the difference between IN and EXISTS? → IN checks a value list; EXISTS checks if a subquery returns rows.
109. What is a NULL? → Absence of a value; not 0 or empty string.
110. How to handle NULL in queries? → IS NULL / IS NOT NULL / COALESCE.

---

## SECTION 5 — Application Architecture (PHP/MySQL/JS)
111. What is the overall architecture? → Front-end (HTML/CSS/JS) → PHP JSON API (api.php) → MySQL.
112. Is it a single-page app? → Yes, SPA-style; JS swaps views and calls the API via fetch.
113. How does front-end talk to back-end? → AJAX fetch() to api.php returning JSON.
114. What is AJAX? → Asynchronous JS that updates parts of a page without reload.
115. What is JSON? Why use it? → Lightweight data format; easy for JS to parse.
116. What does each file do?
   - config.php: DB connection + auto-setup + helpers + audit + roles
   - api.php: all JSON actions (CRUD, bookings, reports, users, SQL Lab)
   - index.php: app shell (inlines CSS/JS, role-based menu)
   - login.php / logout.php: authentication
   - home.php: public landing page
   - export.php: CSV/Excel exports
   - ticket.php: PDF boarding pass
   - queries.php: the 30 queries
   - database.sql: schema + data
   - assets/style.css, assets/app.js: UI + logic
117. Why inline CSS/JS into index.php? → To avoid 404 asset issues on some setups; one self-contained page.
118. How is the DB connection made? → mysqli with auto-fallback host + timeout in config.php.
119. Why a connection timeout? → So the page never hangs/buffers forever if MySQL is down.
120. How does first-run setup work? → If DB/tables missing, config.php imports database.sql automatically.
121. Walk through a booking end-to-end. → Pick passenger/agency/flight → fetch api.php?action=book → INSERT → returns seat+bid → boarding pass shown.
122. What is GET vs POST? Which do you use? → GET reads (search, lists); POST writes (book, add, delete).
123. What is a session? → Server-side storage of user state across requests ($_SESSION).
124. How is login state kept? → uid/username/role in $_SESSION after login.
125. What is stateless vs stateful? → API requests are mostly stateless; user identity via session.
126. How are errors handled? → display_errors on, a fatal-error catcher, and diag.php self-check.
127. What is the PRG pattern? → Post/Redirect/Get to avoid duplicate submits (we use JSON+JS instead).
128. How does the SQL Lab run queries safely? → Predefined read-only queries from queries.php.
129. Why MySQL + XAMPP? → XAMPP bundles Apache+MySQL+PHP for easy local development.
130. Could this run online? → Yes — any PHP+MySQL host; import database.sql, set creds in config.php.

---

## SECTION 6 — Security & Roles
131. What security features are implemented? → Hashed passwords, sessions, role-based access, server-side checks, audit logs, input escaping.
132. What are the roles? → Admin, Agent, Customer.
133. What can Admin do? → Everything: records CRUD, users, audit logs, reports, exports, reset DB.
134. What can Agent do? → Book/cancel, view database (read-only), reports, exports, PDF tickets.
135. What can Customer do? → Search flights, view bookings, download own ticket.
136. How is role-based access enforced? → Backend gates (admin_only(), allow()) on every API action + role-filtered menu.
137. Is hiding menu items enough? → No — the server enforces permissions too (UI hiding is just convenience).
138. How are passwords stored? → Hashed with password_hash() (bcrypt); verified with password_verify().
139. Why not store plain passwords? → Security — if DB leaks, hashes can't be reversed easily.
140. What are audit logs? → A record of sensitive actions (login, book, cancel, CRUD, export, reset) with user, role, IP, time.
141. Why audit logs? → Accountability and traceability of who did what.
142. How do you prevent SQL injection? → Escape all user input before queries.
143. How do you prevent XSS? → Escape output with htmlspecialchars (h()).
144. Can a customer delete a flight? → No — server returns "Admins only".
145. What stops deleting your own account / last admin? → Explicit guards in del_user.
146. What is authentication vs authorization? → Authentication = who you are (login); Authorization = what you can do (roles).
147. What is session hijacking? How mitigated? → Stealing a session; mitigated via session_regenerate_id on login.
148. Are there demo credentials? → admin/admin123, agent/agent123, customer/customer123.

---

## SECTION 7 — Features & Functionality
149. What are the core features? → Search & book, bookings, records CRUD, reports, exports, PDF tickets, SQL Lab, audit, users.
150. How does search work? → Filter flights by source, destination, date (calendar), and time.
151. How is a seat assigned? → User can enter one, else auto-assigned randomly (e.g. 12A).
152. How is the boarding pass generated? → ticket.php: a print-to-PDF HTML page (+ optional raw PDF mode).
153. What can be exported? → Passengers, all bookings, and a summary booking report (CSV/Excel).
154. How does CSV open cleanly in Excel? → UTF-8 BOM + a `sep=,` hint so columns split correctly.
155. How does the download avoid opening in-tab? → JS Blob download (fetch → blob → save).
156. What does the dashboard show? → Role-based KPIs, charts (destinations, agencies, sources), recent bookings.
157. What is the SQL Lab? → A page that runs all 30 case-study queries live and shows results.
158. What is the Reports page? → Analytics charts + downloadable CSV reports (admin/agent).
159. What is on the landing page? → Hero, Features, Testimonials, Airline Partners, Contact.
160. Is there full CRUD? → Yes — create/read/update/delete for passengers, agencies, flights.

---

## SECTION 8 — UI / UX & Design
161. What design style is used? → Glassmorphism (blur + transparency), gradients, soft shadows, rounded corners.
162. What is the color palette? → Primary #2563EB, Secondary #06B6D4, Accent #8B5CF6, Success #10B981, Danger #EF4444.
163. Is it responsive? → Yes — mobile, tablet, laptop, desktop with breakpoints + hamburger drawer.
164. What interactive effects are there? → Animated counters, loading skeletons, hover/card-lift, toasts, modals.
165. Is there a light/dark theme? → Yes, toggle saved in localStorage.
166. How are charts drawn? → Pure CSS/JS bars + CSS conic-gradient donut (no chart library).
167. What fonts are used? → Sora (headings), Inter (body), JetBrains Mono (code).
168. How do you make it accessible/usable? → Clear contrast, large tap targets, readable dropdowns, keyboard-friendly forms.
169. What happens on slow load? → Shimmering skeleton placeholders show until data arrives.
170. How is the boarding pass styled? → Like a real airline ticket with route, seat, barcode.

---

## SECTION 9 — Testing, Deployment, Troubleshooting
171. How do you run the project? → Put folder in xampp/htdocs, start Apache+MySQL, open localhost/aerodesk/home.php.
172. What are the demo logins? → admin/admin123, agent/agent123, customer/customer123.
173. What happens on first run? → DB auto-creates and seeds sample data + 3 role users.
174. How did you fix the blank-screen issue? → Inlined CSS/JS, fatal-error catcher, diag.php.
175. How did you fix the PDF not downloading? → Output buffering + print-to-PDF HTML fallback.
176. How did you fix CSV "opening in localhost"? → JS Blob download forces a save.
177. How did you fix Excel showing empty? → Added `sep=,` so columns split by comma.
178. How did you fix the Apache port problem? → Set Listen to a fixed port / used PHP built-in server.
179. How did you test it? → Verified all 30 queries, CRUD, role permissions, exports, PDF, responsiveness.
180. How would you deploy online? → PHP+MySQL hosting; import database.sql; set DB creds.
181. What if MySQL has a password? → Set DB_PASS in config.php.
182. What PHP version does it need? → PHP 7.4+ (tested on 8.x), with mysqli extension.
183. Is the data persistent? → Yes — stored in MySQL; survives restarts.
184. How do you reset the data? → Admin "Reset sample data" button (reimports database.sql).

---

## SECTION 10 — Tricky / Conceptual Follow-ups
185. Why a surrogate key bid instead of composite (pid,fid)? → Easier to reference one booking (cancel/PDF) while UNIQUE(pid,fid) still prevents duplicates.
186. What if two customers pick the same seat? → Seats auto-assigned; UNIQUE(pid,fid) stops double-booking the same flight.
187. How do you ensure data consistency? → FK constraints + UNIQUE keys + server-side validation.
188. How would you scale this for millions of rows? → Indexes, pagination, caching, prepared statements, a framework/ORM.
189. Why not use a framework like Laravel? → To clearly demonstrate core PHP+SQL for the DBMS course.
190. What is the busiest route in your data? → Chennai → New Delhi (most-booked).
191. What design patterns did you use? → Front controller-ish API, role-gating, helper functions.
192. What is the difference between client-side and server-side validation? → Client = fast UX; server = security (we do both).
193. What if JavaScript is disabled? → A noscript message; core data still requires JS for the SPA.
194. How is the app organized (separation of concerns)? → DB/logic in PHP, presentation in CSS, behavior in JS.
195. What did you learn from this project? → Relational design, SQL, PHP+MySQL integration, auth, roles, responsive UI, debugging.
196. What was the hardest part? → (Your answer — e.g. role-based access + environment setup/debugging.)
197. What would you improve with more time? → Payments, email tickets, prepared statements, pagination, unit tests.
198. How is referential integrity demonstrated live? → Try deleting a passenger/flight with bookings — it's blocked.
199. Can the system handle multiple agencies? → Yes — agency is a first-class entity; bookings record which agency.
200. Give a one-line summary of your project. → "A secure, role-based, responsive flight-booking management web app demonstrating end-to-end DBMS concepts."

---

## SECTION 11 — Rapid-fire one-word/one-line
201. Language for server logic? → PHP.
202. Database used? → MySQL.
203. Local server? → XAMPP (Apache).
204. Front-end languages? → HTML, CSS, JavaScript.
205. Number of case-study queries? → 30.
206. Number of tables? → 6.
207. Primary key of booking? → bid.
208. How many roles? → 3 (admin, agent, customer).
209. Password security function? → password_hash() / password_verify().
210. Export format? → CSV (Excel-compatible).
211. Ticket format? → PDF / printable boarding pass.
212. Default admin login? → admin / admin123.
213. Which normal form? → 3NF.
214. Data exchange format with API? → JSON.
215. Relationship table? → booking.

---

*Exam tip: Be ready to (1) open the ER relationships, (2) run any of the 30 queries
live in the SQL Lab, (3) log in as each role to show different access, and
(4) explain normalization using your own tables.*
*Designed & Developed by Ansh Kumar Singh.*
