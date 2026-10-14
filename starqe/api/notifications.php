<?php
require_once '../config/config.php';
header('Content-Type: application/json; charset=UTF-8');
if(!isLoggedIn()) jsonErr('Login required.',401);
$method=$_SERVER['REQUEST_METHOD'];

if($method==='GET'){
  $items=dbAll('SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT 20',[uid()]);
  $unread=dbCount('SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0',[uid()]);
  jsonOk(['items'=>$items,'unread'=>$unread]);
}
if($method==='POST'){
  $action=s($_POST['action']??'');
  if($action==='read'){
    $id=si($_POST['notif_id']??0);
    if($id) dbQ('UPDATE notifications SET is_read=1 WHERE notif_id=? AND user_id=?',[$id,uid()]);
    jsonOk(null,'Marked read.');
  }
  if($action==='read_all'){
    dbQ('UPDATE notifications SET is_read=1 WHERE user_id=?',[uid()]);
    jsonOk(null,'All marked read.');
  }
}
jsonErr('Method not allowed.',405);
