<?php
session_start();
require __DIR__ . '/config.php';
if (!empty($_SESSION['uid'])) { @audit('logout','signed out'); }
$_SESSION=[];
if(ini_get('session.use_cookies')){$p=session_get_cookie_params();
  setcookie(session_name(),'',time()-42000,$p['path'],$p['domain'],$p['secure'],$p['httponly']);}
session_destroy();
header('Location: login.php'); exit;
