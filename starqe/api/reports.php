<?php
require_once '../config/config.php';
header('Content-Type: application/json; charset=UTF-8');
if(!isLoggedIn()) jsonErr('Login required.',401);
$method=$_SERVER['REQUEST_METHOD'];
if($method==='POST'&&($_POST['_method']??'')==='PUT') $method='PUT';

if($method==='GET'){
  if(isAdmin()){
    $page=max(1,si($_GET['page']??1));$per=20;$off=($page-1)*$per;
    $w=[];$p=[];
    if(!empty($_GET['status'])){$w[]='r.status=?';$p[]=$_GET['status'];}
    $wc=$w?'WHERE '.implode(' AND ',$w):'';
    $rows=dbAll("SELECT r.*,u.fullname,u.email,s.station_name,s.city FROM reports r JOIN users u ON r.user_id=u.user_id JOIN stations s ON r.station_id=s.station_id $wc ORDER BY r.created_at DESC LIMIT $per OFFSET $off",$p);
    $tot=dbCount("SELECT COUNT(*) FROM reports r $wc",$p);
    jsonOk(['reports'=>$rows,'total'=>$tot,'pages'=>(int)ceil($tot/$per)]);
  }
  $rows=dbAll('SELECT r.*,s.station_name,s.city FROM reports r JOIN stations s ON r.station_id=s.station_id WHERE r.user_id=? ORDER BY r.created_at DESC',[uid()]);
  jsonOk($rows);
}
if($method==='POST'){
  if(!isUser()) jsonErr('Guests cannot submit reports.');
  $sid=si($_POST['station_id']??0);$type=s($_POST['report_type']??'');$rem=s($_POST['remarks']??'');
  if(!$sid||!$type) jsonErr('station_id and report_type required.');
  $types=['full','offline','damaged','wrong_info','other'];
  if(!in_array($type,$types)) jsonErr('Invalid report type.');
  dbQ('INSERT INTO reports (user_id,station_id,report_type,remarks) VALUES (?,?,?,?)',[uid(),$sid,$type,$rem]);
  $rid=dbId();
  logHistory(uid(),'REPORT_SUBMITTED',"Report #$rid for station #$sid");
  jsonOk(['report_id'=>$rid],'Report submitted. Thank you!',201);
}
if($method==='PUT'){
  if(!isAdmin()) jsonErr('Admin only.',403);
  $id=si($_POST['report_id']??0);$status=s($_POST['status']??'');$note=s($_POST['admin_note']??'');
  if(!$id||!in_array($status,['approved','rejected','pending'])) jsonErr('Invalid request.');
  dbQ('UPDATE reports SET status=?,admin_note=? WHERE report_id=?',[$status,$note,$id]);
  logHistory(uid(),'REPORT_UPDATED',"Report #$id → $status");
  jsonOk(null,'Report updated.');
}
jsonErr('Method not allowed.',405);
