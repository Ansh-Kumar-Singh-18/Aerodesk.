<?php
/* =====================================================================
   home.php  -  AeroDesk public landing page
   Sections: Hero · Features · Testimonials · Airline Partners · Contact
   Self-contained (inline CSS/JS). Links into the app at login.php/index.php.
   ===================================================================== */
session_start();
require __DIR__ . '/config.php';
$loggedIn = !empty($_SESSION['uid']);
$cta = $loggedIn ? 'index.php' : 'login.php';
$ctaText = $loggedIn ? 'Open Dashboard' : 'Launch App';

// handle contact form -> save feedback so admins get a bell notification
$sent = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') === 'contact') {
    $nm = esc(trim($_POST['name'] ?? ''));
    $em = esc(trim($_POST['email'] ?? ''));
    $ms = esc(trim($_POST['msg'] ?? ''));
    if ($nm !== '' && $em !== '' && $ms !== '') {
        db()->query("INSERT INTO feedback (name,email,message) VALUES ('$nm','$em','$ms')");
        $sent = true;
    }
}
session_write_close();
?>
<!DOCTYPE html><html lang="en" data-theme="dark"><head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>AeroDesk · Modern Flight Booking Management</title>
<meta name="description" content="AeroDesk — a premium airline reservation & booking management suite for agencies, agents and travelers.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<style>
:root{
  --g1:#2563EB;--g2:#06B6D4;--g3:#8B5CF6;
  --grad:linear-gradient(135deg,var(--g1),var(--g2));
  --bg:#070b18;--panel:rgba(255,255,255,.06);--line:rgba(255,255,255,.12);
  --ink:#eef2ff;--muted:#93a0c8;--soft:rgba(255,255,255,.05);
  --shadow:0 24px 60px -16px rgba(0,0,0,.55);
}
*{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth}
body{font-family:Inter,system-ui,sans-serif;color:var(--ink);background:var(--bg);overflow-x:hidden;position:relative}
h1,h2,h3,.d{font-family:Sora,sans-serif}
a{color:inherit;text-decoration:none}
img,svg{display:block}
/* animated gradient backdrop */
body::before{content:"";position:fixed;inset:-25%;z-index:-2;
  background:
    radial-gradient(42% 42% at 18% 20%,rgba(109,94,252,.42),transparent 60%),
    radial-gradient(40% 40% at 82% 12%,rgba(34,211,238,.34),transparent 60%),
    radial-gradient(46% 46% at 75% 88%,rgba(244,113,181,.30),transparent 60%);
  filter:blur(40px);animation:drift 22s ease-in-out infinite alternate}
@keyframes drift{0%{transform:translate(0,0) scale(1)}100%{transform:translate(3%,-2%) scale(1.07)}}

.wrap{max-width:1180px;margin:0 auto;padding:0 24px}
.btn{display:inline-flex;align-items:center;gap:9px;cursor:pointer;border:none;border-radius:14px;
  padding:13px 24px;font-weight:700;font-size:15px;font-family:Sora;color:#06121f;background:var(--grad);
  box-shadow:0 14px 32px -8px rgba(34,211,238,.5);transition:.22s}
.btn:hover{transform:translateY(-3px);filter:brightness(1.07)}
.btn.ghost{background:transparent;border:1px solid var(--line);color:var(--ink);box-shadow:none}
.btn.ghost:hover{border-color:var(--g2)}
.eyebrow{display:inline-block;font-size:12px;font-weight:700;letter-spacing:2px;text-transform:uppercase;
  color:#fff;background:var(--grad);padding:5px 13px;border-radius:30px}
.sec{padding:90px 0}
.sec-head{text-align:center;max-width:680px;margin:0 auto 50px}
.sec-head h2{font-size:34px;margin:14px 0 10px}
.sec-head p{color:var(--muted);font-size:16px}
.glass{background:var(--panel);border:1px solid var(--line);border-radius:20px;backdrop-filter:blur(20px);
  -webkit-backdrop-filter:blur(20px);box-shadow:var(--shadow)}

/* ---------- NAV ---------- */
header.nav{position:sticky;top:0;z-index:50;backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px);
  background:rgba(7,11,24,.6);border-bottom:1px solid var(--line)}
.nav-in{display:flex;align-items:center;justify-content:space-between;height:68px}
.brand{display:flex;align-items:center;gap:12px}
.brand .logo{width:42px;height:42px;border-radius:12px;background:var(--grad);display:grid;place-items:center;
  box-shadow:0 10px 24px rgba(109,94,252,.5)}
.brand .logo svg{width:23px;height:23px;stroke:#fff;fill:none;stroke-width:2}
.brand b{font-family:Sora;font-size:19px;font-weight:800}
.nav-links{display:flex;gap:28px;align-items:center}
.nav-links a{color:var(--muted);font-weight:600;font-size:14.5px;transition:.18s}
.nav-links a:hover{color:var(--ink)}
.nav-cta{display:flex;gap:10px;align-items:center}
.burger{display:none;width:42px;height:42px;border-radius:11px;border:1px solid var(--line);background:var(--panel);
  cursor:pointer;align-items:center;justify-content:center}
.burger svg{stroke:var(--ink);fill:none;stroke-width:2;width:20px;height:20px}

/* ---------- HERO ---------- */
.hero{padding:80px 0 70px;display:grid;grid-template-columns:1.05fr .95fr;gap:40px;align-items:center}
.hero h1{font-size:54px;line-height:1.05;letter-spacing:-.5px}
.hero h1 .grad{background:var(--grad);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent}
.hero p.lead{color:var(--muted);font-size:18px;margin:20px 0 28px;max-width:520px}
.hero-cta{display:flex;gap:14px;flex-wrap:wrap}
.hero-stats{display:flex;gap:34px;margin-top:38px}
.hero-stats .n{font-family:Sora;font-weight:800;font-size:28px;background:var(--grad);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent}
.hero-stats .l{color:var(--muted);font-size:13px}
/* hero art: floating boarding pass */
.hero-art{position:relative;height:420px}
.float{position:absolute;animation:floaty 6s ease-in-out infinite}
@keyframes floaty{0%,100%{transform:translateY(0)}50%{transform:translateY(-16px)}}
.pass{width:340px;padding:0;border-radius:22px;overflow:hidden;box-shadow:0 40px 90px -20px rgba(0,0,0,.7);
  left:50%;top:46%;transform:translate(-50%,-50%);}
.pass .top{background:linear-gradient(120deg,#2563EB,#06B6D4);padding:22px;color:#fff}
.pass .row1{display:flex;justify-content:space-between;font-size:11px;letter-spacing:1px;text-transform:uppercase;opacity:.9}
.pass .big{display:flex;align-items:center;gap:14px;margin-top:12px}
.pass .c{font-family:Sora;font-weight:800;font-size:30px}
.pass .dash{flex:1;border-top:2px dashed rgba(255,255,255,.6);position:relative}
.pass .dash svg{position:absolute;right:-8px;top:-11px;width:22px;height:22px;stroke:#fff;fill:none;stroke-width:2}
.pass .body{background:#0f1630;padding:20px 22px;display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px}
.pass .k{font-size:9.5px;letter-spacing:1px;text-transform:uppercase;color:var(--muted)}
.pass .v{font-family:Sora;font-weight:700;font-size:14px;margin-top:3px}
.pass .barcode{height:40px;margin:0 22px 20px;background:#0f1630;
  background-image:repeating-linear-gradient(90deg,#eef2ff 0 2px,transparent 2px 5px);border-radius:4px}
.chip-badge{position:absolute;padding:12px 16px;border-radius:14px;font-size:13px;font-weight:700;color:#fff;
  display:flex;gap:9px;align-items:center;box-shadow:var(--shadow)}
.chip-badge svg{width:18px;height:18px;stroke:#fff;fill:none;stroke-width:2}
.cb1{background:rgba(52,211,153,.9);top:8%;right:2%;animation:floaty 5s ease-in-out infinite}
.cb2{background:rgba(244,113,181,.9);bottom:6%;left:0;animation:floaty 7s ease-in-out infinite .5s}

/* ---------- FEATURES ---------- */
.feat-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px}
.feat{padding:26px;transition:.25s}
.feat:hover{transform:translateY(-7px);border-color:var(--g2)}
.feat .ic{width:52px;height:52px;border-radius:14px;display:grid;place-items:center;margin-bottom:16px;background:var(--grad)}
.feat .ic svg{width:26px;height:26px;stroke:#fff;fill:none;stroke-width:2}
.feat h3{font-size:18px;margin-bottom:8px}
.feat p{color:var(--muted);font-size:14.5px;line-height:1.6}

/* ---------- TESTIMONIALS ---------- */
.tg{display:grid;grid-template-columns:repeat(3,1fr);gap:20px}
.tcard{padding:26px;display:flex;flex-direction:column;gap:16px;transition:.25s}
.tcard:hover{transform:translateY(-6px)}
.stars{color:#f59e0b;font-size:15px;letter-spacing:2px}
.tcard p{color:var(--ink);font-size:15px;line-height:1.65;opacity:.92}
.tperson{display:flex;align-items:center;gap:12px;margin-top:auto}
.tperson .av{width:44px;height:44px;border-radius:50%;display:grid;place-items:center;font-weight:800;color:#fff;font-family:Sora}
.tperson .nm{font-weight:700;font-size:14.5px}
.tperson .ro{color:var(--muted);font-size:12.5px}

/* ---------- PARTNERS ---------- */
.partners{display:flex;flex-wrap:wrap;gap:16px;justify-content:center}
.partner{display:flex;align-items:center;gap:12px;padding:16px 24px;border-radius:16px;min-width:190px;
  transition:.25s}
.partner:hover{transform:translateY(-5px);border-color:var(--g2)}
.partner .mk{width:40px;height:40px;border-radius:11px;display:grid;place-items:center;color:#fff;font-weight:800;font-family:Sora;font-size:18px;flex:none}
.partner .pn{font-family:Sora;font-weight:700;font-size:16px}
.partner .pt{color:var(--muted);font-size:12px}

/* ---------- CONTACT ---------- */
.contact{display:grid;grid-template-columns:1fr 1fr;gap:28px;align-items:stretch}
.contact .info{padding:34px;display:flex;flex-direction:column;gap:20px}
.contact .info h2{font-size:28px}
.cinfo-row{display:flex;gap:14px;align-items:flex-start}
.cinfo-row .ic{width:44px;height:44px;border-radius:12px;background:var(--soft);display:grid;place-items:center;flex:none}
.cinfo-row .ic svg{width:20px;height:20px;stroke:var(--g2);fill:none;stroke-width:2}
.cinfo-row .k{font-size:12px;color:var(--muted)}
.cinfo-row .v{font-weight:600;margin-top:2px}
.contact form{padding:34px;display:flex;flex-direction:column;gap:6px}
.contact label{font-size:12.5px;color:var(--muted);font-weight:600;margin:12px 0 6px}
.contact input,.contact textarea{width:100%;padding:13px 14px;border-radius:12px;border:1px solid var(--line);
  background:var(--soft);color:var(--ink);font-size:14px;font-family:Inter;outline:none;transition:.18s}
.contact input:focus,.contact textarea:focus{border-color:var(--g2);box-shadow:0 0 0 4px rgba(34,211,238,.16)}
.contact textarea{resize:vertical;min-height:110px}
.sent{background:rgba(52,211,153,.14);border:1px solid rgba(52,211,153,.4);color:#a7f3d0;padding:12px 15px;
  border-radius:12px;font-size:14px;margin-bottom:8px}

/* ---------- FOOTER ---------- */
footer{border-top:1px solid var(--line);padding:34px 0;margin-top:30px;color:var(--muted);font-size:14px}
.foot-in{display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap}
.foot-in .by b{color:var(--ink)}

/* reveal on scroll */
.reveal{opacity:0;transform:translateY(26px);transition:opacity .7s,transform .7s}
.reveal.in{opacity:1;transform:none}

/* ---------- RESPONSIVE ---------- */
@media(max-width:980px){
  .hero{grid-template-columns:1fr;text-align:center;padding-top:50px}
  .hero p.lead{margin-left:auto;margin-right:auto}
  .hero-cta,.hero-stats{justify-content:center}
  .hero-art{height:380px;order:-1}
  .feat-grid,.tg{grid-template-columns:repeat(2,1fr)}
  .contact{grid-template-columns:1fr}
  .nav-links{display:none}
  .burger{display:flex}
}
@media(max-width:640px){
  .sec{padding:64px 0}
  .hero h1{font-size:38px}
  .sec-head h2{font-size:27px}
  .feat-grid,.tg{grid-template-columns:1fr}
  .hero-stats{gap:22px}
  .pass{width:290px}
}
/* mobile menu sheet */
.msheet{display:none;flex-direction:column;gap:4px;padding:12px 24px 20px;border-bottom:1px solid var(--line);
  background:rgba(7,11,24,.92);backdrop-filter:blur(14px)}
.msheet.open{display:flex}
.msheet a{padding:12px;border-radius:10px;color:var(--ink);font-weight:600}
.msheet a:hover{background:var(--soft)}
</style></head>
<body>

<!-- ===================== NAV ===================== -->
<header class="nav">
  <div class="wrap nav-in">
    <div class="brand">
      <span class="logo"><svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M17.8 19.2 16 11l3.5-3.5C21 6 21.5 4 21 3c-1-.5-3 0-4.5 1.5L13 8 4.8 6.2c-.5-.1-.9.1-1.1.5l-.3.5c-.2.5-.1 1 .3 1.3L9 12l-2 3H4l-1 1 3 2 2 3 1-1v-3l3-2 3.5 5.3c.3.4.8.5 1.3.3l.5-.2c.4-.3.6-.7.5-1.2z"/></svg></span>
      <b>AeroDesk</b>
    </div>
    <nav class="nav-links">
      <a href="#features">Features</a>
      <a href="#testimonials">Testimonials</a>
      <a href="#partners">Partners</a>
      <a href="#contact">Contact</a>
    </nav>
    <div class="nav-cta">
      <a class="btn ghost" href="login.php" style="padding:10px 18px">Sign in</a>
      <a class="btn" href="<?= $cta ?>" style="padding:10px 18px"><?= $ctaText ?></a>
      <button class="burger" id="burger"><svg viewBox="0 0 24 24"><path d="M3 6h18M3 12h18M3 18h18"/></svg></button>
    </div>
  </div>
  <div class="msheet" id="msheet">
    <a href="#features">Features</a><a href="#testimonials">Testimonials</a>
    <a href="#partners">Partners</a><a href="#contact">Contact</a>
    <a href="login.php">Sign in</a>
  </div>
</header>

<!-- ===================== HERO ===================== -->
<section class="wrap hero" id="hero">
  <div>
    <span class="eyebrow">✈ Airline Reservation Suite</span>
    <h1 style="margin-top:18px">Book smarter.<br><span class="grad">Fly effortlessly.</span></h1>
    <p class="lead">AeroDesk is a modern flight booking &amp; management platform for travel agencies,
      agents and travelers — real-time bookings, instant PDF boarding passes, analytics and more.</p>
    <div class="hero-cta">
      <a class="btn" href="<?= $cta ?>"><?= $ctaText ?> <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="#06121f" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
      <a class="btn ghost" href="#features">Explore features</a>
    </div>
    <div class="hero-stats">
      <div><div class="n">10k+</div><div class="l">Bookings managed</div></div>
      <div><div class="n">7+</div><div class="l">Airline partners</div></div>
      <div><div class="n">99.9%</div><div class="l">Uptime</div></div>
    </div>
  </div>
  <div class="hero-art">
    <div class="chip-badge cb1"><svg viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg> Booked!</div>
    <div class="chip-badge cb2"><svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M3 10h18"/></svg> e-Ticket</div>
    <div class="pass float glass">
      <div class="top">
        <div class="row1"><span>AeroDesk · Boarding Pass</span><span>Seat 12A</span></div>
        <div class="big"><span class="c">DEL</span><span class="dash"><svg viewBox="0 0 24 24"><path d="M2 12h18M14 6l6 6-6 6"/></svg></span><span class="c">BOM</span></div>
      </div>
      <div class="body">
        <div><div class="k">Passenger</div><div class="v">A. K. Singh</div></div>
        <div><div class="k">Flight</div><div class="v">AD‑307</div></div>
        <div><div class="k">Gate</div><div class="v">A12</div></div>
      </div>
      <div class="barcode"></div>
    </div>
  </div>
</section>

<!-- ===================== FEATURES ===================== -->
<section class="sec" id="features">
  <div class="wrap">
    <div class="sec-head reveal"><span class="eyebrow">Features</span>
      <h2>Everything you need to run bookings</h2>
      <p>A complete toolkit — from search to boarding pass — wrapped in a fast, beautiful interface.</p></div>
    <div class="feat-grid">
      <?php
      $features = [
        ['Instant Booking','Search flights by route &amp; date and issue tickets in one click with auto-assigned seats.','<circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/>'],
        ['PDF Boarding Pass','Generate &amp; download professional PDF boarding passes — no external libraries needed.','<path d="M14 3v5h5"/><path d="M7 3h7l5 5v13H7z"/><path d="M9 13h6M9 17h4"/>'],
        ['Role-Based Access','Admin, Agent &amp; Customer roles, each with a tailored dashboard and permissions.','<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>'],
        ['Analytics &amp; Reports','Live charts plus CSV/Excel exports for passengers, bookings and summary reports.','<path d="M3 3v18h18"/><rect x="7" y="10" width="3" height="7"/><rect x="12" y="6" width="3" height="11"/>'],
        ['Audit Logs','Every sensitive action is recorded with user, role, IP and timestamp for full traceability.','<circle cx="12" cy="12" r="9"/><path d="M12 8v4l3 2"/>'],
        ['Fully Responsive','Looks stunning on mobile, tablet, laptop and desktop with a slick glassmorphism UI.','<rect x="2" y="4" width="14" height="10" rx="1"/><rect x="18" y="8" width="4" height="12" rx="1"/>'],
      ];
      foreach ($features as $i=>$f): ?>
      <div class="feat glass reveal" style="transition-delay:<?= $i*0.06 ?>s">
        <div class="ic"><svg viewBox="0 0 24 24"><?= $f[2] ?></svg></div>
        <h3><?= $f[0] ?></h3><p><?= $f[1] ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ===================== TESTIMONIALS ===================== -->
<section class="sec" id="testimonials">
  <div class="wrap">
    <div class="sec-head reveal"><span class="eyebrow">Testimonials</span>
      <h2>Loved by agencies &amp; travelers</h2>
      <p>Here's what people say about flying through AeroDesk.</p></div>
    <div class="tg">
      <?php
      $tests = [
        ['"AeroDesk cut our booking time in half. The boarding-pass PDFs look incredibly professional."','Priya Verma','Travel Agency Owner','#2563EB'],
        ['"As an agent I love the dashboard — quick search, instant tickets, and clean reports for my clients."','Rohit Singh','Booking Agent','#06B6D4'],
        ['"Booking my flights has never been this smooth. Beautiful, fast and works great on my phone."','Sneha Jain','Frequent Flyer','#8B5CF6'],
      ];
      foreach ($tests as $i=>$t): ?>
      <div class="tcard glass reveal" style="transition-delay:<?= $i*0.08 ?>s">
        <div class="stars">★★★★★</div>
        <p><?= $t[0] ?></p>
        <div class="tperson">
          <div class="av" style="background:linear-gradient(135deg,<?= $t[3] ?>,#06B6D4)"><?= strtoupper($t[1][0]) ?></div>
          <div><div class="nm"><?= $t[1] ?></div><div class="ro"><?= $t[2] ?></div></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ===================== PARTNERS ===================== -->
<section class="sec" id="partners">
  <div class="wrap">
    <div class="sec-head reveal"><span class="eyebrow">Airline Partners</span>
      <h2>Trusted airline network</h2>
      <p>Book across all our partner carriers from a single platform.</p></div>
    <div class="partners">
      <?php
      $partners = [
        ['Jet','Bhopal Hub','#2563EB'],['AirIndia','Delhi Hub','#06B6D4'],['IndiGo','Chennai Hub','#8B5CF6'],
        ['SpiceJet','Mumbai Hub','#10B981'],['GoFirst','Indore Hub','#f59e0b'],['Vistara','Bhopal Hub','#8B5CF6'],
        ['Akasa','Delhi Hub','#EF4444'],
      ];
      foreach ($partners as $i=>$p): ?>
      <div class="partner glass reveal" style="transition-delay:<?= $i*0.05 ?>s">
        <div class="mk" style="background:linear-gradient(135deg,<?= $p[2] ?>,#06B6D4)"><?= $p[0][0] ?></div>
        <div><div class="pn"><?= $p[0] ?></div><div class="pt"><?= $p[1] ?></div></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ===================== CONTACT ===================== -->
<section class="sec" id="contact">
  <div class="wrap">
    <div class="sec-head reveal"><span class="eyebrow">Contact</span>
      <h2>Get in touch</h2>
      <p>Questions, demos or partnership enquiries — we'd love to hear from you.</p></div>
    <div class="contact">
      <div class="info glass reveal">
        <h2>Let's talk ✈</h2>
        <div class="cinfo-row"><span class="ic"><svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg></span>
          <div><div class="k">Email</div><div class="v">anshp41105@gmail.com</div></div></div>
        <div class="cinfo-row"><span class="ic"><svg viewBox="0 0 24 24"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3 19.5 19.5 0 0 1-6-6 19.8 19.8 0 0 1-3-8.6A2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.3 1.8.6 2.7a2 2 0 0 1-.5 2.1L8 9.9a16 16 0 0 0 6 6l1.4-1.2a2 2 0 0 1 2.1-.5c.9.3 1.8.5 2.7.6a2 2 0 0 1 1.7 2z"/></svg></span>
          <div><div class="k">Phone</div><div class="v">+91 94795080558</div></div></div>
        <div class="cinfo-row"><span class="ic"><svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 12-9 12s-9-5-9-12a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg></span>
          <div><div class="k">Office</div><div class="v">SIRT, Bhopal, India</div></div></div>
        <div class="cinfo-row"><span class="ic"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 8v4l3 2"/></svg></span>
          <div><div class="k">Hours</div><div class="v">Mon–Sat · 9am – 8pm IST</div></div></div>
      </div>
      <form class="glass reveal" method="post">
        <input type="hidden" name="form" value="contact">
        <?php if ($sent): ?><div class="sent">✓ Thanks! Your message has been received. We'll reply soon.</div><?php endif; ?>
        <label>Name</label><input name="name" placeholder="Your name" required>
        <label>Email</label><input name="email" type="email" placeholder="you@example.com" required>
        <label>Message</label><textarea name="msg" placeholder="How can we help?" required></textarea>
        <button class="btn" type="submit" style="margin-top:18px;width:100%;justify-content:center">Send message</button>
      </form>
    </div>
  </div>
</section>

<!-- ===================== FOOTER ===================== -->
<footer>
  <div class="wrap foot-in">
    <div class="brand"><span class="logo" style="width:34px;height:34px"><svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M17.8 19.2 16 11l3.5-3.5C21 6 21.5 4 21 3c-1-.5-3 0-4.5 1.5L13 8 4.8 6.2c-.5-.1-.9.1-1.1.5l-.3.5c-.2.5-.1 1 .3 1.3L9 12l-2 3H4l-1 1 3 2 2 3 1-1v-3l3-2 3.5 5.3c.3.4.8.5 1.3.3l.5-.2c.4-.3.6-.7.5-1.2z"/></svg></span><b style="font-family:Sora;font-size:17px;color:var(--ink)">AeroDesk</b></div>
    <div class="by">© <?= date('Y') ?> AeroDesk · Designed &amp; Developed by <b>ANSH KUMAR SINGH</b></div>
    <div><a href="<?= $cta ?>" style="color:var(--g2);font-weight:600"><?= $ctaText ?> →</a></div>
  </div>
</footer>

<script>
  // mobile menu
  const burger=document.getElementById('burger'), sheet=document.getElementById('msheet');
  burger.onclick=()=>sheet.classList.toggle('open');
  sheet.querySelectorAll('a').forEach(a=>a.onclick=()=>sheet.classList.remove('open'));
  // reveal on scroll
  const io=new IntersectionObserver((es)=>es.forEach(e=>{if(e.isIntersecting){e.target.classList.add('in');io.unobserve(e.target);}}),{threshold:.12});
  document.querySelectorAll('.reveal').forEach(el=>io.observe(el));
</script>
</body></html>
