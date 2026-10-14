<?php
// api/auth.php
require_once '../config/config.php';
header('Content-Type: application/json; charset=UTF-8');
if($_SERVER['REQUEST_METHOD']!=='POST') jsonErr('POST only',405);

$action=trim($_POST['action']??'');

// Rate limiting
$rk='rl_'.md5(getClientIP());
if($action==='login'){
  if(!isset($_SESSION[$rk])) $_SESSION[$rk]=['c'=>0,'t'=>time()];
  if(time()-$_SESSION[$rk]['t']>300) $_SESSION[$rk]=['c'=>0,'t'=>time()];
  $_SESSION[$rk]['c']++;
  if($_SESSION[$rk]['c']>5) jsonErr('Too many attempts. Wait 5 minutes.',429);
}

if($action==='login'){
  $email=se($_POST['email']??''); $pw=$_POST['password']??''; $rem=!empty($_POST['remember']);
  if(!$email||!$pw) jsonErr('Email and password are required.');
  $u=dbOne('SELECT * FROM users WHERE email=?',[$email]);
  if(!$u||!password_verify($pw,$u['password_hash'])) jsonErr('Invalid email or password.');
  if($u['status']==='disabled') jsonErr('Account is disabled. Contact support.');
  setUserSession($u);
  if($rem){ $token=bin2hex(random_bytes(32)); dbQ('UPDATE users SET remember_token=? WHERE user_id=?',[$token,$u['user_id']]); setcookie('sq_rem',$token,time()+30*86400,'/','',$_SERVER['HTTPS']??false,true); }
  unset($_SESSION[$rk]);
  logHistory((int)$u['user_id'],'LOGIN','Login from '.getClientIP());
  jsonOk(['role'=>$u['role'],'redirect'=>BASE_URL.($u['role']==='admin'?'/admin/dashboard.php':'/dashboard.php')],'Login successful!');
}

if($action==='register'){
  $name=s($_POST['fullname']??''); $email=se($_POST['email']??''); $pw=$_POST['password']??''; $conf=$_POST['confirm_password']??'';
  if(!$name||!$email||!$pw) jsonErr('All fields are required.');
  if(!validEmail($email)) jsonErr('Invalid email address.');
  if(strlen($pw)<8) jsonErr('Password must be at least 8 characters.');
  if(!preg_match('/[A-Z]/',$pw)) jsonErr('Password needs at least one uppercase letter.');
  if(!preg_match('/[0-9]/',$pw)) jsonErr('Password needs at least one number.');
  if($pw!==$conf) jsonErr('Passwords do not match.');
  if(dbOne('SELECT 1 FROM users WHERE email=?',[$email])) jsonErr('Email already registered.');
  $hash=password_hash($pw,PASSWORD_BCRYPT,['cost'=>12]);
  dbQ('INSERT INTO users (fullname,email,password_hash,role,status) VALUES (?,?,?,?,?)',[$name,$email,$hash,'user','enabled']);
  $uid=dbId();
  $u=dbOne('SELECT * FROM users WHERE user_id=?',[$uid]);
  setUserSession($u);
  logHistory($uid,'REGISTER','New user: '.$email);
  jsonOk(['redirect'=>BASE_URL.'/dashboard.php'],'Welcome to STARQE!');
}

if($action==='guest'){
  session_regenerate_id(true);
  $_SESSION['role']='guest'; $_SESSION['fullname']='Guest'; $_SESSION['uid']=0;
  jsonOk(['redirect'=>BASE_URL.'/map.php'],'Browsing as guest.');
}

if($action==='logout'){
  if(isLoggedIn()) logHistory(uid(),'LOGOUT','User logged out');
  clearSession();
  jsonOk(['redirect'=>BASE_URL.'/index.php'],'Logged out.');
}
jsonErr('Unknown action.',400);
