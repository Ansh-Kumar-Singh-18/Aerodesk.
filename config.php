<?php
/* =====================================================================
   config.php  -  AeroDesk : DB connection, auto-setup, helpers
   Edit the four DB_* constants if your MySQL credentials differ.
   ===================================================================== */

/* Show errors instead of a blank white screen.
   (Set both to '0' for production once everything works.) */
error_reporting(E_ALL);
ini_set('display_errors', '1');

/* Last-resort catcher: if a fatal error happens, show it instead of a
   blank white page. */
register_shutdown_function(function(){
    $e = error_get_last();
    if ($e && in_array($e['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        if (!headers_sent()) header('Content-Type: text/html; charset=utf-8');
        echo '<div style="font-family:system-ui,sans-serif;max-width:640px;margin:50px auto;'
           . 'padding:24px;border:1px solid #fca5a5;border-radius:14px;background:#fff5f5;color:#991b1b">'
           . '<h2 style="margin:0 0 8px">⚠️ PHP error caught</h2><p style="color:#444">'
           . htmlspecialchars($e['message']) . '</p><p style="color:#777;font-size:13px">'
           . htmlspecialchars($e['file']) . ' : line ' . (int)$e['line']
           . '</p><p style="color:#444;font-size:13px">Open <a href="diag.php">diag.php</a> for a full self-check.</p></div>';
    }
});

define('DB_HOST', 'localhost');   // auto-fallback to 127.0.0.1 if needed (see db())
define('DB_PORT', 3306);          // XAMPP default MySQL port (3307 also auto-tried)
define('DB_USER', 'root');
define('DB_PASS', '');          // XAMPP default: empty
define('DB_NAME', 'aerodesk');

mysqli_report(MYSQLI_REPORT_OFF);

function db() {
    static $c = null;
    if ($c instanceof mysqli) return $c;

    // Try several common XAMPP connection targets so it "just works".
    // Each attempt has a short timeout, so the page can NEVER hang forever.
    $targets = [
        ['localhost',  DB_PORT],
        ['127.0.0.1',  DB_PORT],
        ['localhost',  3307],     // some XAMPP installs use 3307
        ['127.0.0.1',  3307],
    ];
    $lastErr = '';
    foreach ($targets as $t) {
        $try = mysqli_init();
        @$try->options(MYSQLI_OPT_CONNECT_TIMEOUT, 2);   // fail fast, never long buffering
        if (@$try->real_connect($t[0], DB_USER, DB_PASS, '', (int)$t[1])) {
            $c = $try; break;
        }
        $lastErr = mysqli_connect_error() ?: 'connection failed';
        $c = null;
    }
    if (!($c instanceof mysqli)) {
        die('<div style="font-family:sans-serif;max-width:600px;margin:60px auto;padding:24px;'
          . 'border:1px solid #fbb;border-radius:12px;background:#fff5f5;color:#a00">'
          . '<h2>Cannot connect to MySQL</h2>'
          . '<p>Make sure <b>MySQL</b> is <b>green/running</b> in the XAMPP Control Panel.</p>'
          . '<p>If your MySQL has a password, set <code>DB_PASS</code> at the top of <code>config.php</code>.</p>'
          . '<p style="color:#777;font-size:13px">Last error: ' . htmlspecialchars($lastErr) . '</p></div>');
    }

    // Does the DB exist AND have its core tables? If not, (re)import.
    $needsSetup = !$c->select_db(DB_NAME);
    if (!$needsSetup) {
        $chk = @$c->query("SELECT 1 FROM passenger LIMIT 1");
        if ($chk === false) { $needsSetup = true; }     // db exists but tables missing/broken
        elseif ($chk) { $chk->free(); }
    }
    if ($needsSetup) {
        $file = __DIR__ . '/database.sql';
        if (!is_readable($file)) die('Cannot read database.sql — make sure the whole folder was copied.');
        $sql = preg_replace('#/\*.*?\*/#s', '', file_get_contents($file));
        if (!$c->multi_query($sql)) die('Setup failed: ' . htmlspecialchars($c->error));
        do { if ($r = $c->store_result()) $r->free(); } while ($c->more_results() && $c->next_result());
        if ($c->errno) die('Setup error: ' . htmlspecialchars($c->error));
        $c->select_db(DB_NAME);
    }
    $c->set_charset('utf8mb4');

    // make sure the default admin works for THIS php version
    @$c->query("CREATE TABLE IF NOT EXISTS users (uid INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(40) UNIQUE, password VARCHAR(255), role VARCHAR(20) DEFAULT 'admin',
        pid VARCHAR(10) DEFAULT NULL,
        created TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
    // add pid column if upgrading from an older version
    @$c->query("ALTER TABLE users ADD COLUMN pid VARCHAR(10) DEFAULT NULL");
    // audit log table
    @$c->query("CREATE TABLE IF NOT EXISTS audit_logs (
        lid INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(40), role VARCHAR(20), action VARCHAR(40),
        detail VARCHAR(255), ip VARCHAR(45),
        ts TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
    // feedback / contact messages table
    @$c->query("CREATE TABLE IF NOT EXISTS feedback (
        fbid INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(60) NOT NULL, email VARCHAR(80) NOT NULL,
        message TEXT NOT NULL, reply TEXT DEFAULT NULL,
        status VARCHAR(12) NOT NULL DEFAULT 'new',
        created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        replied TIMESTAMP NULL DEFAULT NULL)");

    // seed demo accounts for each role (admin / agent / customer)
    // demo 'customer' is linked to passenger 110 (Arjun Saxena) so it has bookings to show
    $seed = [
        ['admin','admin123','admin',null],
        ['agent','agent123','agent',null],
        ['customer','customer123','customer','110'],
    ];
    foreach ($seed as $u) {
        $n = 0;
        if ($r = $c->query("SELECT COUNT(*) FROM users WHERE username='".$c->real_escape_string($u[0])."'")) { $n=(int)$r->fetch_row()[0]; $r->free(); }
        if ($n === 0) {
            $h = $c->real_escape_string(password_hash($u[1], PASSWORD_DEFAULT));
            $pid = $u[3]===null ? 'NULL' : "'".$c->real_escape_string($u[3])."'";
            $c->query("INSERT INTO users (username,password,role,pid) VALUES ('".$c->real_escape_string($u[0])."','$h','".$u[2]."',$pid)");
        }
    }
    // REPAIR: link the demo 'customer' account to passenger 110 if it exists but isn't linked yet
    // (older DBs created the account before the pid column existed, leaving pid NULL -> "not linked")
    if ($pchk = @$c->query("SELECT COUNT(*) FROM passenger WHERE pid='110'")) {
        $hasPax = (int)$pchk->fetch_row()[0]; $pchk->free();
        if ($hasPax > 0) {
            @$c->query("UPDATE users SET pid='110' WHERE username='customer' AND role='customer' AND (pid IS NULL OR pid='')");
        }
    }
    return $c;
}

function q_all($sql){ $r=db()->query($sql); if($r===false) return ['__error'=>db()->error];
    $o=[]; while($x=$r->fetch_assoc())$o[]=$x; $r->free(); return $o; }
function q_one($sql){ $r=db()->query($sql); if(!$r) return null; $x=$r->fetch_assoc(); $r->free(); return $x; }
function q_val($sql){ $r=db()->query($sql); if(!$r) return 0; $x=$r->fetch_row(); $r->free(); return $x?$x[0]:0; }
function esc($v){ return db()->real_escape_string($v); }
function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function next_id($t,$c,$start){ $m=(int)q_val("SELECT MAX(CAST($c AS UNSIGNED)) FROM $t"); return (string)($m>0?$m+1:$start); }

function json_out($data){ header('Content-Type: application/json'); echo json_encode($data); exit; }
function require_login(){ if (empty($_SESSION['uid'])) { header('Location: login.php'); exit; } }

/* ---------- roles & permissions ----------
   admin    : full control (records, users, audit, reports, exports)
   agent    : book/cancel tickets, view/search flights, export & reports
   customer : search flights, view own bookings, download own ticket
*/
function role(){ return $_SESSION['role'] ?? 'customer'; }
/* Always resolve the logged-in user's linked passenger id straight from the DB
   (by uid). This is robust even if login.php is an old copy that never put 'pid'
   into the session. Result is cached into the session once resolved. */
function current_pid(){
    if (!empty($_SESSION['pid'])) return $_SESSION['pid'];
    if (empty($_SESSION['uid'])) return '';
    $uid = (int)$_SESSION['uid'];
    $pid = @q_val("SELECT pid FROM users WHERE uid=$uid LIMIT 1");
    if ($pid !== null && $pid !== '') { $_SESSION['pid'] = $pid; return $pid; }
    return '';
}
function is_role(...$roles){ return in_array(role(), $roles, true); }

/** Write an entry to the audit log. */
function audit($action, $detail=''){
    $u = esc($_SESSION['username'] ?? 'guest');
    $r = esc($_SESSION['role'] ?? '-');
    $a = esc($action); $d = esc(substr((string)$detail,0,250));
    $ip = esc($_SERVER['REMOTE_ADDR'] ?? '');
    @db()->query("INSERT INTO audit_logs (username,role,action,detail,ip) VALUES ('$u','$r','$a','$d','$ip')");
}
