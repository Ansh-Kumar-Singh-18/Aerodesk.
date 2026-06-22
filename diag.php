<?php
/* =====================================================================
   diag.php  -  AeroDesk self-check.  Open: http://localhost/aerodesk/diag.php
   Delete this file once everything is green.
   ===================================================================== */
error_reporting(E_ALL);
ini_set('display_errors', '1');
header('Content-Type: text/html; charset=utf-8');

function row($label,$ok,$detail=''){
  $c = $ok===true ? '#16a34a' : ($ok===false ? '#dc2626' : '#d97706');
  $i = $ok===true ? '✔' : ($ok===false ? '�’✗' : '⚠');
  echo "<tr><td style='padding:8px 14px;border-bottom:1px solid #eee'>".htmlspecialchars($label)."</td>"
     . "<td style='padding:8px 14px;border-bottom:1px solid #eee;color:$c;font-weight:700'>$i</td>"
     . "<td style='padding:8px 14px;border-bottom:1px solid #eee;color:#555'>".$detail."</td></tr>";
}
?>
<!doctype html><html><head><meta charset="utf-8"><title>AeroDesk · Diagnostics</title>
<style>body{font-family:system-ui,Segoe UI,sans-serif;background:#0f1630;color:#fff;padding:40px}
.card{max-width:780px;margin:auto;background:#fff;color:#111;border-radius:14px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.4)}
h1{margin:0;padding:20px 22px;background:linear-gradient(135deg,#6d5efc,#22d3ee);color:#fff;font-size:20px}
table{width:100%;border-collapse:collapse;font-size:14px}
.fix{padding:18px 22px;background:#fafafa;font-size:13.5px;color:#444;line-height:1.6}
code{background:#eef;padding:2px 6px;border-radius:5px}</style></head>
<body><div class="card"><h1>✈️ AeroDesk — Diagnostics</h1>
<table>
<?php
// 1. PHP version
$php_ok = version_compare(PHP_VERSION,'7.4.0','>=');
row('PHP version', $php_ok, 'You have <b>'.PHP_VERSION.'</b> (need 7.4+)');

// 2. mysqli extension
$mysqli_ok = extension_loaded('mysqli');
row('mysqli extension', $mysqli_ok, $mysqli_ok ? 'loaded' : 'NOT loaded — enable <code>extension=mysqli</code> in php.ini');

// 3. required files
foreach (['config.php','database.sql','queries.php','api.php','index.php','assets/style.css','assets/app.js'] as $f){
  row("File: $f", file_exists(__DIR__.'/'.$f));
}

// 4. DB connect (uses config constants without auto-setup)
$conn=null; $dbinfo='';
if ($mysqli_ok && file_exists(__DIR__.'/config.php')){
  // read constants from config.php without running db()
  $src=file_get_contents(__DIR__.'/config.php');
  preg_match("/DB_HOST',\s*'([^']*)'/",$src,$mH); preg_match("/DB_USER',\s*'([^']*)'/",$src,$mU);
  preg_match("/DB_PASS',\s*'([^']*)'/",$src,$mP); preg_match("/DB_NAME',\s*'([^']*)'/",$src,$mN);
  $H=$mH[1]??'127.0.0.1'; $U=$mU[1]??'root'; $P=$mP[1]??''; $N=$mN[1]??'aerodesk';
  mysqli_report(MYSQLI_REPORT_OFF);
  $conn=@new mysqli($H,$U,$P);
  if($conn->connect_errno){
    row('MySQL connection', false, "Host <b>$H</b>, user <b>$U</b> — ".htmlspecialchars($conn->connect_error));
  } else {
    row('MySQL connection', true, "connected to <b>$H</b> as <b>$U</b>");
    $has = $conn->select_db($N);
    row("Database '$N' exists", $has===true ? true : 'warn', $has? 'yes' : 'will be auto-created on first load of index.php');
  }
} else {
  row('MySQL connection', 'warn', 'skipped (mysqli or config.php missing)');
}

// 5. sessions
$sess_ok = function_exists('session_start');
row('Sessions available', $sess_ok);
?>
</table>
<div class="fix">
<b>What to do next</b><br>
• If everything above is green ✔ → open <code>http://localhost/aerodesk/index.php</code> — it should work.<br>
• If <b>MySQL connection</b> is red → start MySQL in XAMPP, or set your password in <code>config.php</code> (<code>DB_PASS</code>).<br>
• If a <b>file</b> is red → the folder wasn’t copied completely; re-copy the whole <code>aerodesk</code> folder (including the <code>assets</code> subfolder) into <code>htdocs</code>.<br>
• If you still get a white screen on <code>index.php</code>, the real error will now be visible because this app build shows errors. Also check <code>xampp/apache/logs/error.log</code>.<br>
<br><i>Delete diag.php when done.</i>
</div></div></body></html>
