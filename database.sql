-- =====================================================================
--  AeroDesk  -  Passenger Flight Booking Through Agency
--  COMPLETE DATABASE FILE  (schema + data + 30 query solutions)
--  CSIT-405 DBMS  |  Sagar Institute of Research and Technology
--  Original case study by Dr. Aumreesh Kumar Saxena
--
--  PART A -> Schema   PART B -> Sample data   PART C -> 30 queries
--  Import in phpMyAdmin or run:  mysql -u root -p < database.sql
--  (The app also auto-imports Parts A + B on first run.)
-- =====================================================================


-- #####################################################################
-- #  PART A  -  SCHEMA                                                 #
-- #####################################################################
DROP DATABASE IF EXISTS aerodesk;
CREATE DATABASE aerodesk CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE aerodesk;

CREATE TABLE passenger (
    pid      VARCHAR(10)  NOT NULL,
    pname    VARCHAR(50)  NOT NULL,
    pgender  VARCHAR(6)   NOT NULL,
    pcity    VARCHAR(50)  NOT NULL,
    PRIMARY KEY (pid)
);

CREATE TABLE agency (
    aid    VARCHAR(10)  NOT NULL,
    aname  VARCHAR(50)  NOT NULL,
    acity  VARCHAR(50)  NOT NULL,
    PRIMARY KEY (aid)
);

CREATE TABLE flight (
    fid    VARCHAR(10)  NOT NULL,
    fdate  DATE         NOT NULL,
    time   TIME         NOT NULL,
    src    VARCHAR(50)  NOT NULL,
    dest   VARCHAR(50)  NOT NULL,
    PRIMARY KEY (fid)
);

CREATE TABLE booking (
    bid    INT AUTO_INCREMENT PRIMARY KEY,
    pid    VARCHAR(10)  NOT NULL,
    aid    VARCHAR(10)  NOT NULL,
    fid    VARCHAR(10)  NOT NULL,
    fdate  DATE         NOT NULL,
    seat   VARCHAR(5)   DEFAULT NULL,
    UNIQUE KEY uq_pax_flight (pid, fid),
    CONSTRAINT fk_b_pass   FOREIGN KEY (pid) REFERENCES passenger(pid),
    CONSTRAINT fk_b_agency FOREIGN KEY (aid) REFERENCES agency(aid),
    CONSTRAINT fk_b_flight FOREIGN KEY (fid) REFERENCES flight(fid)
);

CREATE TABLE users (
    uid       INT AUTO_INCREMENT PRIMARY KEY,
    username  VARCHAR(40)  NOT NULL UNIQUE,
    password  VARCHAR(255) NOT NULL,
    role      VARCHAR(20)  NOT NULL DEFAULT 'admin',   -- admin | agent | customer
    created   TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE audit_logs (
    lid       INT AUTO_INCREMENT PRIMARY KEY,
    username  VARCHAR(40),
    role      VARCHAR(20),
    action    VARCHAR(40),
    detail    VARCHAR(255),
    ip        VARCHAR(45),
    ts        TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE feedback (
    fbid     INT AUTO_INCREMENT PRIMARY KEY,
    name     VARCHAR(60)  NOT NULL,
    email    VARCHAR(80)  NOT NULL,
    message  TEXT         NOT NULL,
    reply    TEXT         DEFAULT NULL,
    status   VARCHAR(12)  NOT NULL DEFAULT 'new',   -- new | read | replied
    created  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    replied  TIMESTAMP    NULL DEFAULT NULL
);


-- #####################################################################
-- #  PART B  -  SAMPLE DATA                                            #
-- #####################################################################
INSERT INTO passenger (pid, pname, pgender, pcity) VALUES
('101','Aman Sharma','Male','Bhopal'),
('102','Priya Verma','Female','Delhi'),
('103','Rohit Singh','Male','Chennai'),
('104','Sneha Jain','Female','Mumbai'),
('105','Akash Patel','Male','Indore'),
('106','Neha Gupta','Female','Bhopal'),
('107','Vikas Kumar','Male','Delhi'),
('108','Anjali Mehta','Female','Chennai'),
('109','Raj Malhotra','Male','Mumbai'),
('110','Arjun Saxena','Male','Bhopal');

INSERT INTO agency (aid, aname, acity) VALUES
('201','Jet','Bhopal'),
('202','AirIndia','Delhi'),
('203','Indigo','Chennai'),
('204','SpiceJet','Mumbai'),
('205','GoFirst','Indore'),
('206','Vistara','Bhopal'),
('207','Akasa','Delhi');

INSERT INTO flight (fid, fdate, time, src, dest) VALUES
('301','2020-11-05','10:00','Bhopal','Chennai'),
('302','2020-11-04','12:00','Chennai','New Delhi'),
('303','2020-12-01','16:00','Mumbai','Delhi'),
('304','2020-12-02','16:00','Mumbai','Delhi'),
('305','2020-11-03','09:00','Delhi','Chennai'),
('306','2020-11-06','18:00','Bhopal','Mumbai'),
('307','2020-12-01','16:00','Chennai','New Delhi'),
('308','2020-12-02','16:00','Chennai','New Delhi'),
('309','2020-11-07','08:00','Indore','Delhi'),
('310','2020-11-08','14:00','Delhi','Bhopal');

INSERT INTO booking (pid, aid, fid, fdate, seat) VALUES
('110','201','301','2020-11-05','12A'),
('110','201','302','2020-11-04','04C'),
('101','201','303','2020-12-01','18F'),
('102','202','304','2020-12-02','07B'),
('103','203','305','2020-11-03','21D'),
('104','204','306','2020-11-06','09A'),
('105','205','307','2020-12-01','14E'),
('106','206','308','2020-12-02','03C'),
('107','207','309','2020-11-07','22A'),
('108','203','310','2020-11-08','11B'),
('109','204','303','2020-12-01','06D'),
('110','201','307','2020-12-01','19F'),
('110','201','308','2020-12-02','02A');

-- Demo logins. The app (config.php) seeds ALL THREE with valid hashes on
-- first run, so these are the working credentials:
--   admin    / admin123      (full control)
--   agent    / agent123      (book & manage tickets, reports, exports)
--   customer / customer123   (search flights, view own bookings, download ticket)
-- Only 'admin' is seeded here; config.php adds agent & customer with correct
-- password hashes for your PHP version.
INSERT INTO users (username, password, role) VALUES
('admin', '$2y$12$X//W9oFDAzmAv/cJgOC5UOvSXKkkxuAQpbNV/urTvs0jD7RcO28iu', 'admin');


-- #####################################################################
-- #  PART C  -  SOLUTIONS TO ALL 30 CASE-STUDY QUERIES                 #
-- #  (commented so import stays clean; copy any block to run)          #
-- #####################################################################
/*
-- 1.  SELECT * FROM flight WHERE dest = 'New Delhi';
-- 2.  SELECT * FROM flight WHERE src = 'Chennai' AND dest = 'New Delhi';
-- 3.  SELECT DISTINCT b.fid FROM booking b JOIN flight f ON b.fid=f.fid
       WHERE b.pid='123' AND f.dest='Chennai' AND f.fdate<'2020-11-06';
-- 4.  SELECT DISTINCT p.pname FROM passenger p JOIN booking b ON p.pid=b.pid;
-- 5.  SELECT pname FROM passenger WHERE pid NOT IN (SELECT pid FROM booking);
-- 6.  SELECT aname FROM agency WHERE acity=(SELECT pcity FROM passenger WHERE pid='123');
-- 7.  SELECT * FROM flight WHERE fdate='2020-12-01' AND time='16:00'
       AND src IN (SELECT src FROM flight WHERE fdate='2020-12-02' AND time='16:00')
       AND dest IN (SELECT dest FROM flight WHERE fdate='2020-12-02' AND time='16:00');
-- 8.  SELECT * FROM flight WHERE time='16:00' AND fdate IN ('2020-12-01','2020-12-02');
-- 9.  SELECT aname FROM agency WHERE aid NOT IN (SELECT aid FROM booking WHERE pid='123');
-- 10. SELECT DISTINCT p.* FROM passenger p JOIN booking b ON p.pid=b.pid
       JOIN agency a ON b.aid=a.aid WHERE p.pgender='Male' AND a.aname='Jet';
-- 11. SELECT p.pid,p.pname,COUNT(*) FROM passenger p JOIN booking b ON p.pid=b.pid
       GROUP BY p.pid,p.pname HAVING COUNT(*)>1;
-- 12. SELECT a.aid,a.aname,COUNT(*) FROM agency a JOIN booking b ON a.aid=b.aid
       GROUP BY a.aid,a.aname ORDER BY COUNT(*) DESC LIMIT 1;
-- 13. SELECT DISTINCT p.pid,p.pname FROM passenger p JOIN booking b ON p.pid=b.pid
       JOIN agency a ON b.aid=a.aid WHERE p.pcity<>a.acity;
-- 14. SELECT f.fid,COUNT(*) FROM flight f JOIN booking b ON f.fid=b.fid
       GROUP BY f.fid HAVING COUNT(*)>5;
-- 15. SELECT p.pid,p.pname,f.fid,f.fdate,f.time FROM booking b JOIN flight f ON b.fid=f.fid
       JOIN passenger p ON b.pid=p.pid ORDER BY f.fdate,f.time LIMIT 1;
-- 16. SELECT p.pid,p.pname,COUNT(DISTINCT f.dest) FROM passenger p JOIN booking b ON p.pid=b.pid
       JOIN flight f ON b.fid=f.fid GROUP BY p.pid,p.pname HAVING COUNT(DISTINCT f.dest)>1;
-- 17. SELECT aname FROM agency WHERE aid NOT IN (SELECT aid FROM booking);
-- 18. SELECT * FROM flight WHERE fid NOT IN (SELECT fid FROM booking);
-- 19. SELECT p.pid,p.pname FROM passenger p JOIN booking b ON p.pid=b.pid
       GROUP BY p.pid,p.pname HAVING COUNT(DISTINCT b.fid)=(SELECT COUNT(*) FROM flight);
-- 20. SELECT f.dest,COUNT(*) FROM booking b JOIN flight f ON b.fid=f.fid
       GROUP BY f.dest ORDER BY COUNT(*) DESC LIMIT 1;
-- 21. SELECT DISTINCT b1.pid FROM booking b1 JOIN booking b2 ON b1.pid=b2.pid
       AND b2.fdate = DATE_ADD(b1.fdate, INTERVAL 1 DAY);
-- 22. SELECT f.src,COUNT(*) FROM booking b JOIN flight f ON b.fid=f.fid
       GROUP BY f.src ORDER BY COUNT(*) DESC;
-- 23. SELECT a.aid,a.aname FROM agency a JOIN booking b ON a.aid=b.aid
       JOIN passenger p ON b.pid=p.pid GROUP BY a.aid,a.aname HAVING COUNT(DISTINCT p.pgender)=2;
-- 24. SELECT f.fid,f.src,f.dest,COUNT(*) FROM flight f JOIN booking b ON f.fid=b.fid
       GROUP BY f.fid,f.src,f.dest ORDER BY COUNT(*) DESC LIMIT 1;
-- 25. SELECT p.pid,p.pname FROM passenger p JOIN booking b ON p.pid=b.pid
       GROUP BY p.pid,p.pname HAVING COUNT(DISTINCT b.aid)=1;
-- 26. SELECT aid,total FROM (SELECT aid,COUNT(*) total FROM booking GROUP BY aid) t
       WHERE total > (SELECT AVG(cnt) FROM (SELECT COUNT(*) cnt FROM booking GROUP BY aid) x);
-- 27. SELECT pname FROM passenger WHERE pid NOT IN
       (SELECT b.pid FROM booking b JOIN flight f ON b.fid=f.fid WHERE f.dest='New Delhi');
-- 28. SELECT fdate,COUNT(*) FROM booking GROUP BY fdate ORDER BY fdate;
-- 29. SELECT * FROM passenger WHERE pname LIKE 'A%';
-- 30. SELECT * FROM flight WHERE src <> dest;
*/
