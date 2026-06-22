# ✈️ AeroDesk — Premium Flight Booking Suite (PHP · MySQL · JS)

A second, more **modern, professional and attractive** web app built from the
**CSIT-405 DBMS Case Study** (*Passenger Flight Booking Through Agency*, Sagar
Institute of Research and Technology; original case study by Dr. Aumreesh Kumar
Saxena).

> This is a fresh, redesigned alternative to the earlier `flight-booking-system`
> app — same database concepts, a far richer UI/UX.

![preview](assets/preview.png)

---

## ✨ What makes it special

- **Glassmorphism UI** with animated gradient blobs, a hero banner, and a
  **light / dark theme toggle** (remembered across visits).
- **Single-page feel** — every screen loads instantly via a **JSON API + fetch**
  (AJAX); no full-page reloads.
- **Boarding-pass experience** — search results render as airline tickets, and a
  booking pops a printable-style **boarding pass** with an auto-assigned seat.
- **Animated dashboard** — count-up KPI numbers, animated bar charts, and a
  CSS **donut chart** of bookings by destination.
- **Live SQL Lab** — all **30** case-study queries, syntax-highlighted and run
  **live against MySQL**, with the real result set.
- **Full CRUD** for passengers, agencies, flights (inline edit + delete with
  referential-integrity protection).
- **Role-based login** — three roles with tailored dashboards & permissions:
  - **Admin** (`admin`/`admin123`) — full control: records, users, audit logs, reports, exports, DB reset
  - **Agent** (`agent`/`agent123`) — book/cancel tickets, read-only database, reports & exports, PDF tickets
  - **Customer** (`customer`/`customer123`) — search flights, view bookings, download own boarding pass
- **Audit logs** (admin) — every sensitive action (login, book, cancel, add/edit/delete, export, ticket, reset) is recorded with user, role, IP and timestamp.
- **File & ticket management**
  - **PDF boarding pass** download (built-in PDF generator, no libraries)
  - **Passenger / Bookings export** to Excel/CSV
  - **Booking reports** (CSV) + an analytics Reports dashboard with charts
- **User management** — create accounts (admin/agent/customer), reset passwords, delete (with self-delete & last-user safeguards).
- **Toast notifications** and confirm dialogs throughout.

---

## 🌐 Landing page (`home.php`)

A polished public marketing page with five sections — **Hero, Features,
Testimonials, Airline Partners, Contact** — matching the app's glassmorphism
style, fully responsive, with scroll-reveal animations and a working contact
form. Open **http://localhost/aerodesk/home.php**. It links to **Sign in** /
**Launch App**, and the app's sidebar logo links back to it.

## 📦 Files

| File | Purpose |
|------|---------|
| `index.php` | App shell (renders layout, boots the JS SPA). |
| `login.php` / `logout.php` | Authentication. |
| `api.php` | JSON API — all reads, CRUD, bookings, users, SQL Lab. |
| `config.php` | DB connection + first-run **auto-setup** + helpers. |
| `queries.php` | The 30 case-study queries. |
| `assets/style.css` | Premium dark/light theme. |
| `assets/app.js` | SPA controller (views, charts, modals, toasts). |
| `database.sql` | **All-in-one**: schema + data + 30 query solutions (comments). |
| `README.md` / `INSTALL.txt` | Docs. |

---

## 🚀 Run with XAMPP

1. Start **Apache** + **MySQL** in XAMPP.
2. Copy the `aerodesk` folder into `xampp/htdocs/`.
3. Open **http://localhost/aerodesk/** and sign in.

The database creates and seeds itself automatically on first load.

**Login:** `admin` / `admin123`

> Different MySQL credentials? Edit the `DB_*` constants at the top of `config.php`.
> Want to import the SQL manually instead? phpMyAdmin → Import → `database.sql`.

---

## 🗄️ Schema

```
passenger(pid PK, pname, pgender, pcity)
agency   (aid PK, aname, acity)
flight   (fid PK, fdate, time, src, dest)
booking  (bid PK, pid FK, aid FK, fid FK, fdate, seat)   -- Passenger × Agency × Flight
users    (uid PK, username, password, role, created)     -- app login
```

---

## 📝 Notes

- Queries **3, 6, 9** reference `pid = 123`, which is not in the dataset.
  `database.sql` keeps `123` exactly as the case study states; the SQL Lab demos
  them with `pid = 110` (flagged in the UI) so you see real output.
- Verified on **PHP 8.4 + MariaDB/MySQL**: auth + roles, all 30 SQL queries,
  booking/seat assignment, CRUD with FK protection, and user management all work.

---

*An academic DBMS deliverable — relational design, joins, subqueries, set
operations, aggregation & normalization — delivered as a polished PHP + MySQL
single-page application.*
