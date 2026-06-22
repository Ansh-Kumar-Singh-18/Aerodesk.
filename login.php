<?php
session_start();
require __DIR__ . '/config.php';
db();
if (!empty($_SESSION['uid'])) { header('Location: index.php'); exit; }
$err='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $u=esc(trim($_POST['username']??'')); $p=$_POST['password']??'';
    $row=q_one("SELECT * FROM users WHERE username='$u' LIMIT 1");
    if($row && password_verify($p,$row['password'])){
        session_regenerate_id(true);
        $_SESSION['uid']=$row['uid']; $_SESSION['username']=$row['username']; $_SESSION['role']=$row['role']; $_SESSION['pid']=$row['pid']??null;
        audit('login','signed in');
        header('Location: index.php'); exit;
    }
    @db()->query("INSERT INTO audit_logs (username,role,action,detail,ip) VALUES ('".esc(trim($_POST['username']??''))."','-','login_failed','bad credentials','".esc($_SERVER['REMOTE_ADDR']??'')."')");
    $err='Invalid username or password.';
}
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>AeroDesk · Sign in</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<style>
:root{--g1:#2563EB;--g2:#06B6D4;--ink:#0b1120;--glass:rgba(255,255,255,.12)}
*{box-sizing:border-box;margin:0;padding:0}
body{min-height:100vh;display:grid;place-items:center;font-family:Inter,system-ui,sans-serif;
  background:linear-gradient(125deg,#0b1120,#131a3a 40%,#0b2540);overflow:hidden;position:relative}
.blob{position:absolute;border-radius:50%;filter:blur(70px);opacity:.55;animation:float 14s ease-in-out infinite}
.b1{width:420px;height:420px;background:#2563EB;top:-120px;left:-100px}
.b2{width:380px;height:380px;background:#06B6D4;bottom:-120px;right:-80px;animation-delay:-5s}
.b3{width:260px;height:260px;background:#8B5CF6;top:40%;left:55%;animation-delay:-9s}
@keyframes float{0%,100%{transform:translate(0,0)}50%{transform:translate(40px,-30px)}}
.card{position:relative;z-index:2;width:390px;max-width:92vw;padding:40px 34px;border-radius:24px;
  background:var(--glass);backdrop-filter:blur(22px);border:1px solid rgba(255,255,255,.22);
  box-shadow:0 30px 80px rgba(0,0,0,.5);color:#fff;animation:rise .6s cubic-bezier(.2,.8,.2,1)}
@keyframes rise{from{opacity:0;transform:translateY(24px)}to{opacity:1;transform:none}}
.logo{width:64px;height:64px;border-radius:18px;display:grid;place-items:center;margin:0 auto 18px;
  background:linear-gradient(135deg,var(--g1),var(--g2));box-shadow:0 12px 30px rgba(109,94,252,.5)}
.logo svg{width:34px;height:34px}
h1{font-family:Sora,sans-serif;font-weight:800;text-align:center;font-size:26px;letter-spacing:.5px}
.sub{text-align:center;opacity:.7;font-size:13px;margin:6px 0 26px}
label{display:block;font-size:12px;opacity:.8;margin:16px 0 7px;font-weight:600}
.field{position:relative}
.field svg{position:absolute;left:13px;top:50%;transform:translateY(-50%);width:18px;height:18px;opacity:.6}
input{width:100%;padding:13px 14px 13px 40px;border-radius:12px;border:1px solid rgba(255,255,255,.25);
  background:rgba(255,255,255,.08);color:#fff;font-size:14px;outline:none;transition:.2s}
input::placeholder{color:rgba(255,255,255,.45)}
input:focus{border-color:var(--g2);box-shadow:0 0 0 4px rgba(34,211,238,.18)}
.btn{width:100%;margin-top:24px;padding:14px;border:none;border-radius:12px;cursor:pointer;font-weight:700;
  font-size:15px;color:#06121f;background:linear-gradient(135deg,var(--g2),var(--g1));
  box-shadow:0 12px 30px rgba(34,211,238,.35);transition:.2s;font-family:Sora,sans-serif}
.btn:hover{transform:translateY(-2px);filter:brightness(1.07)}
.err{background:rgba(244,63,94,.18);border:1px solid rgba(244,63,94,.4);color:#fecdd3;
  padding:11px;border-radius:11px;font-size:13px;text-align:center;margin-bottom:6px}
.hint{margin-top:20px;text-align:center;font-size:12px;opacity:.75}
.hint b{color:var(--g2)}
</style></head>
<body>
<div class="blob b1"></div><div class="blob b2"></div><div class="blob b3"></div>
<form class="card" method="post">
  <div class="logo"><svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.8 19.2 16 11l3.5-3.5C21 6 21.5 4 21 3c-1-.5-3 0-4.5 1.5L13 8 4.8 6.2c-.5-.1-.9.1-1.1.5l-.3.5c-.2.5-.1 1 .3 1.3L9 12l-2 3H4l-1 1 3 2 2 3 1-1v-3l3-2 3.5 5.3c.3.4.8.5 1.3.3l.5-.2c.4-.3.6-.7.5-1.2z"/></svg></div>
  <h1>AeroDesk</h1>
  <p class="sub">Flight Booking Management Suite</p>
  <?php if($err): ?><div class="err"><?= h($err) ?></div><?php endif; ?>
  <label>Username</label>
  <div class="field"><svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/></svg>
    <input name="username" value="admin" autocomplete="username" autofocus></div>
  <label>Password</label>
  <div class="field"><svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><rect x="4" y="11" width="16" height="9" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
    <input name="password" type="password" placeholder="••••••••" autocomplete="current-password"></div>
  <button class="btn">Sign in →</button>
  <div style="text-align:center;margin-top:16px"><a href="home.php" style="color:var(--g2);font-size:13px;font-weight:600">← Back to website</a></div>
  <div class="hint" style="line-height:1.9">Demo accounts (click to fill):<br>
    <a href="#" class="demo" data-u="admin" data-p="admin123"><b>admin</b> / admin123</a> &nbsp;·&nbsp;
    <a href="#" class="demo" data-u="agent" data-p="agent123"><b>agent</b> / agent123</a> &nbsp;·&nbsp;
    <a href="#" class="demo" data-u="customer" data-p="customer123"><b>customer</b> / customer123</a>
  </div>
  <script>
    document.querySelectorAll('.demo').forEach(a=>a.onclick=e=>{e.preventDefault();
      document.querySelector('[name=username]').value=a.dataset.u;
      document.querySelector('[name=password]').value=a.dataset.p;});
  </script>
</form>
</body></html>
