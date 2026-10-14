<?php
require_once '../config/config.php';
header('Content-Type: application/json; charset=UTF-8');
if(!isAdmin()) jsonErr('Admin only.',403);
$method=$_SERVER['REQUEST_METHOD'];
if($method==='POST'&&($_POST['_method']??'')==='PUT') $method='PUT';
if($method==='GET'){ jsonOk(getSettings()); }
if($method==='POST'||$method==='PUT'){
  $d=$_POST;
  $ex=dbOne('SELECT setting_id FROM settings LIMIT 1');
  $vals=[s($d['site_name']??'STARQE'),se($d['contact_email']??''),sf($d['default_price']??12),si($d['maintenance_mode']??0),si($d['booking_advance_days']??7),si($d['max_booking_hours']??8)];
  if($ex) dbQ('UPDATE settings SET site_name=?,contact_email=?,default_price=?,maintenance_mode=?,booking_advance_days=?,max_booking_hours=? WHERE setting_id=?',array_merge($vals,[$ex['setting_id']]));
  else     dbQ('INSERT INTO settings (site_name,contact_email,default_price,maintenance_mode,booking_advance_days,max_booking_hours) VALUES (?,?,?,?,?,?)',$vals);
  logHistory(uid(),'SETTINGS_UPDATED','System settings updated');
  jsonOk(null,'Settings saved.');
}
jsonErr('Method not allowed.',405);
