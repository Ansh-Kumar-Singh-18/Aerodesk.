<?php
session_start();
/* Force the browser to always load the fresh page (no stale cached menu) */
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
require __DIR__ . '/config.php';
require __DIR__ . '/queries.php';
db();
require_login();
$ME = h($_SESSION['username'] ?? 'admin');
$ROLE = h($_SESSION['role'] ?? 'admin');
?>
<!DOCTYPE html><html lang="en" data-theme="dark"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>AeroDesk · Flight Booking Suite</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
<style>
<?php
$css = @file_get_contents(__DIR__ . '/assets/style.css');
echo $css !== false ? $css : '/* style.css not found on disk */';
?>
</style>
</head>
<body>
<div class="shell">
  <!-- ============ SIDEBAR ============ -->
  <aside class="side">
    <a class="brand" href="home.php" title="Back to website" style="cursor:pointer">
      <div class="brand-logo"><svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.8 19.2 16 11l3.5-3.5C21 6 21.5 4 21 3c-1-.5-3 0-4.5 1.5L13 8 4.8 6.2c-.5-.1-.9.1-1.1.5l-.3.5c-.2.5-.1 1 .3 1.3L9 12l-2 3H4l-1 1 3 2 2 3 1-1v-3l3-2 3.5 5.3c.3.4.8.5 1.3.3l.5-.2c.4-.3.6-.7.5-1.2z"/></svg></div>
      <div><div class="brand-name">AeroDesk</div><div class="brand-tag">Booking Suite</div></div>
    </a>
    <nav class="menu">
      <?php
      $NAV = [
        ['dashboard','Dashboard',['admin','agent','customer'],'<rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/>'],
        ['search','Search &amp; Book',['admin','agent','customer'],'<circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/>'],
        ['bookings','Bookings',['admin','agent','customer'],'<path d="M4 4h16v6a2 2 0 0 0 0 4v6H4v-6a2 2 0 0 0 0-4z"/>'],
        ['records','Add Records',['admin'],'<circle cx="12" cy="12" r="9"/><path d="M12 8v8M8 12h8"/>'],
        ['data','Database',['admin','agent'],'<ellipse cx="12" cy="5" rx="8" ry="3"/><path d="M4 5v14c0 1.7 3.6 3 8 3s8-1.3 8-3V5"/><path d="M4 12c0 1.7 3.6 3 8 3s8-1.3 8-3"/>'],
        ['reports','Reports',['admin','agent'],'<path d="M3 3v18h18"/><rect x="7" y="10" width="3" height="7"/><rect x="12" y="6" width="3" height="11"/><rect x="17" y="13" width="3" height="4"/>'],
        ['users','Users',['admin'],'<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.9"/>'],
        ['feedback','Feedback',['admin','agent'],'<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>'],
        ['audit','Audit Logs',['admin'],'<path d="M12 8v4l3 2"/><circle cx="12" cy="12" r="9"/>'],
        ['sql','SQL Lab',['admin','agent'],'<path d="m8 9-3 3 3 3"/><path d="m16 9 3 3-3 3"/><path d="M13 6 11 18"/>'],
        ['about','About',['admin','agent','customer'],'<circle cx="12" cy="12" r="9"/><path d="M12 16v-4M12 8h.01"/>'],
      ];
      $first = true;
      foreach ($NAV as $n) {
        if (!in_array($_SESSION['role'] ?? '', $n[2], true)) continue;
        echo '<button class="nav'.($first?' active':'').'" data-view="'.$n[0].'">'
           . '<svg viewBox="0 0 24 24">'.$n[3].'</svg><span>'.$n[1].'</span></button>';
        $first = false;
      }
      ?>
    </nav>
    <div class="side-foot">
      <div class="user">
        <div class="ava"><?= strtoupper(substr($ME,0,1)) ?></div>
        <div class="user-meta"><div class="user-name"><?= $ME ?></div><div class="user-role"><?= $ROLE ?></div></div>
        <a class="icon-btn" href="logout.php" title="Sign out"><svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/></svg></a>
      </div>
    </div>
  </aside>
  <div class="scrim" id="scrim"></div>

  <!-- ============ MAIN ============ -->
  <main class="main">
    <header class="topbar">
      <div class="crumbs">
        <button class="hamburger" id="hamburger" aria-label="Menu"><svg viewBox="0 0 24 24"><path d="M3 6h18M3 12h18M3 18h18"/></svg></button>
        <span id="crumbView">Dashboard</span>
      </div>
      <div class="top-actions">
        <?php if (in_array($_SESSION['role'] ?? '', ['admin','agent'], true)): ?>
        <button class="chip-btn bell" id="bellBtn" title="Feedback notifications">
          <svg viewBox="0 0 24 24"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/></svg>
          <span class="bell-dot" id="bellDot"></span>
        </button>
        <?php endif; ?>
        <button class="chip-btn" id="themeBtn" title="Toggle theme"><svg viewBox="0 0 24 24" id="themeIcon"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M2 12h2M20 12h2M5 5l1.5 1.5M17.5 17.5 19 19M19 5l-1.5 1.5M6.5 17.5 5 19"/></svg></button>
        <?php if (($_SESSION['role'] ?? '')==='admin'): ?>
        <button class="chip-btn danger-soft" id="resetBtn" title="Reset sample data"><svg viewBox="0 0 24 24"><path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5"/></svg><span class="lbl">Reset</span></button>
        <?php endif; ?>
      </div>
    </header>

    <div id="views"><!-- views injected by JS -->
      <div class="card" style="margin:30px">Loading AeroDesk…</div>
    </div>
    <noscript><div class="card" style="margin:30px;color:#fda4af">This app needs JavaScript enabled.</div></noscript>
  </main>
</div>

<!-- ===== boarding-pass modal ===== -->
<div class="modal-wrap" id="modal"><div class="modal" id="modalBody"></div></div>
<!-- ===== toast ===== -->
<div class="toast-host" id="toasts"></div>

<script>
  window.AERO = { role: <?= json_encode($ROLE) ?>, me: <?= json_encode($ME) ?>, pid: <?= json_encode(function_exists('current_pid') ? current_pid() : ($_SESSION['pid'] ?? '')) ?>,
    queries: <?= json_encode(array_map(fn($q)=>['t'=>$q[0],'sql'=>$q[1],'note'=>$q[2]??''], $QUERIES)) ?> };
</script>
<script>
<?php
$js = @file_get_contents(__DIR__ . '/assets/app.js');
echo $js !== false ? $js : 'document.getElementById("views").innerHTML="<div class=card style=margin:30px;color:#fda4af>app.js not found on disk</div>";';
?>
</script>
</body></html>
