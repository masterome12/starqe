<?php
require_once '../config/config.php';
header('Content-Type: application/json; charset=UTF-8');
$method=$_SERVER['REQUEST_METHOD'];
if($method==='POST'&&($_POST['_method']??'')==='PUT') $method='PUT';
if(!isLoggedIn()) jsonErr('Login required.',401);

// Helper: send notification
function sendNotif(int $userId, string $title, string $msg, string $type='info', string $link=''): void {
  dbQ('INSERT INTO notifications (user_id,title,message,type,link) VALUES (?,?,?,?,?)',[$userId,$title,$msg,$type,$link]);
}

// ── GET ────────────────────────────────────────────────────────
if($method==='GET'){
  if(!empty($_GET['ref'])){
    $b=dbOne('SELECT b.*,s.station_name,s.address,s.city,s.charging_type,s.power_capacity,s.price_per_kwh,s.operator,s.latitude,s.longitude FROM bookings b JOIN stations s ON b.station_id=s.station_id WHERE b.reference_no=?',[s($_GET['ref'])]);
    if(!$b) jsonErr('Booking not found.',404);
    if(!isAdmin()&&(int)$b['user_id']!==uid()) jsonErr('Access denied.',403);
    jsonOk($b);
  }
  if(!empty($_GET['id'])){
    $b=dbOne('SELECT b.*,s.station_name,s.city,s.charging_type,s.power_capacity,s.latitude,s.longitude FROM bookings b JOIN stations s ON b.station_id=s.station_id WHERE b.booking_id=?',[si($_GET['id'])]);
    if(!$b) jsonErr('Not found.',404);
    if(!isAdmin()&&(int)$b['user_id']!==uid()) jsonErr('Access denied.',403);
    jsonOk($b);
  }
  if(isAdmin()){
    $page=max(1,si($_GET['page']??1));$per=20;$off=($page-1)*$per;
    $w=[];$p=[];
    if(!empty($_GET['status'])){$w[]='b.status=?';$p[]=$_GET['status'];}
    if(!empty($_GET['date'])){$w[]='b.booking_date=?';$p[]=$_GET['date'];}
    if(!empty($_GET['q'])){$sq='%'.$_GET['q'].'%';$w[]='(b.reference_no LIKE ? OR u.fullname LIKE ? OR s.station_name LIKE ?)';array_push($p,$sq,$sq,$sq);}
    $wc=$w?'WHERE '.implode(' AND ',$w):'';
    $rows=dbAll("SELECT b.*,s.station_name,s.city,u.fullname,u.email FROM bookings b JOIN stations s ON b.station_id=s.station_id JOIN users u ON b.user_id=u.user_id $wc ORDER BY b.created_at DESC LIMIT $per OFFSET $off",$p);
    $tot=dbCount("SELECT COUNT(*) FROM bookings b JOIN stations s ON b.station_id=s.station_id JOIN users u ON b.user_id=u.user_id $wc",$p);
    jsonOk(['bookings'=>$rows,'total'=>$tot,'pages'=>(int)ceil($tot/$per),'page'=>$page]);
  }
  // User bookings
  $status=s($_GET['status']??'');
  $w=$status?'AND b.status=?':'';
  $p=$status?[uid(),$status]:[uid()];
  $rows=dbAll("SELECT b.*,s.station_name,s.city,s.charging_type,s.latitude,s.longitude FROM bookings b JOIN stations s ON b.station_id=s.station_id WHERE b.user_id=? $w ORDER BY b.created_at DESC",$p);
  jsonOk($rows);
}

// ── POST (create) ──────────────────────────────────────────────
if($method==='POST'&&($_POST['_method']??'')!=='PUT'){
  if(!isUser()) jsonErr('Guests cannot book. Please register.',403);
  $sid=si($_POST['station_id']??0);$date=s($_POST['booking_date']??'');$time=s($_POST['booking_time']??'');$dur=si($_POST['duration_hours']??1);$notes=s($_POST['notes']??'');
  if(!$sid||!$date||!$time) jsonErr('Station, date, and time are required.');
  if(strtotime($date)<strtotime('today')) jsonErr('Cannot book in the past.');
  $cfg=getSettings();
  if($dur<1||$dur>$cfg['max_booking_hours']) jsonErr("Duration must be 1–{$cfg['max_booking_hours']} hours.");
  $s=dbOne('SELECT * FROM stations WHERE station_id=?',[$sid]);
  if(!$s) jsonErr('Station not found.',404);
  if($s['status']==='maintenance') jsonErr('Station is under maintenance.');
  // Conflict check
  $conflict=dbOne("SELECT booking_id FROM bookings WHERE station_id=? AND booking_date=? AND booking_time=? AND status IN ('pending','confirmed','approved')",[$sid,$date,$time]);
  if($conflict) jsonErr('This time slot is already booked. Choose another time.');
  $total=round($s['power_capacity']*$s['price_per_kwh']*$dur,2);
  $ref=genRef('STQ');
  dbQ('INSERT INTO bookings (user_id,station_id,booking_date,booking_time,duration_hours,total_amount,status,reference_no,notes) VALUES (?,?,?,?,?,?,?,?,?)',[uid(),$sid,$date,$time,$dur,$total,'pending',$ref,$notes]);
  $bid=dbId();
  logHistory(uid(),'BOOKING_CREATED',"Booking $ref for station #$sid");
  // Notify admins
  $admins=dbAll("SELECT user_id FROM users WHERE role='admin' AND status='enabled'");
  foreach($admins as $a){
    sendNotif((int)$a['user_id'],'New Booking Request',"Ref: $ref — {$s['station_name']}",'info',BASE_URL.'/admin/bookings.php');
  }
  // Notify user
  sendNotif(uid(),'Booking Submitted',"Your booking $ref has been submitted and is pending approval.",'info',BASE_URL.'/booking.php?ref='.$ref);
  $booking=dbOne('SELECT b.*,s.station_name,s.city,s.charging_type FROM bookings b JOIN stations s ON b.station_id=s.station_id WHERE b.booking_id=?',[$bid]);
  jsonOk($booking,"Booking submitted! Reference: $ref",201);
}

// ── PUT (update status) ────────────────────────────────────────
if($method==='PUT'){
  $id=si($_POST['booking_id']??0);$status=s($_POST['status']??'');$note=s($_POST['admin_note']??'');
  if(!$id||!$status) jsonErr('booking_id and status required.');
  $b=dbOne('SELECT * FROM bookings WHERE booking_id=?',[$id]);
  if(!$b) jsonErr('Booking not found.',404);
  if(!isAdmin()){
    if((int)$b['user_id']!==uid()) jsonErr('Access denied.',403);
    if($status!=='cancelled') jsonErr('Users can only cancel bookings.');
    if(!in_array($b['status'],['pending','confirmed','approved'])) jsonErr('Cannot cancel this booking.');
  }
  $allowed=['pending','confirmed','approved','rejected','cancelled','completed'];
  if(!in_array($status,$allowed)) jsonErr('Invalid status.');
  $setNote=$note?',admin_note=?':'';
  $params=[$status,$id]; if($note) array_splice($params,1,0,[$note]);
  dbQ("UPDATE bookings SET status=?,updated_at=NOW() $setNote WHERE booking_id=?",$params);
  logHistory(uid(),'BOOKING_STATUS',"Booking #{$id} → $status");

  // Notification to user
  $msgs=['approved'=>['✅ Booking Approved!',"Your booking {$b['reference_no']} has been approved. You're all set!",'success'],
         'rejected'=>['❌ Booking Rejected',"Your booking {$b['reference_no']} was rejected.".($note?" Reason: $note":''),'error'],
         'completed'=>['🎉 Charging Complete',"Booking {$b['reference_no']} has been marked complete. Thank you!",'success'],
         'cancelled'=>['Booking Cancelled',"Booking {$b['reference_no']} has been cancelled.",'info']];
  if(isset($msgs[$status])){
    [$title,$msg,$type]=$msgs[$status];
    sendNotif((int)$b['user_id'],$title,$msg,$type,BASE_URL.'/booking.php?ref='.$b['reference_no']);
  }
  jsonOk(null,'Booking updated.');
}
jsonErr('Method not allowed.',405);
