<?php
require_once '../config/config.php';
header('Content-Type: application/json; charset=UTF-8');
if(!isUser()) jsonErr('Login required.',401);
$method=$_SERVER['REQUEST_METHOD'];
if($method==='POST'&&($_POST['_method']??'')==='DELETE') $method='DELETE';

if($method==='GET'){
  $rows=dbAll('SELECT f.*,s.station_name,s.address,s.city,s.status,s.charging_type,s.available_ports,s.total_ports,s.price_per_kwh,s.latitude,s.longitude,s.operator,s.is_fast_charge,s.is_verified FROM favorites f JOIN stations s ON f.station_id=s.station_id WHERE f.user_id=? ORDER BY f.created_at DESC',[uid()]);
  jsonOk($rows);
}
if($method==='POST'){
  $sid=si($_POST['station_id']??0);
  if(!$sid) jsonErr('station_id required.');
  if(!dbOne('SELECT station_id FROM stations WHERE station_id=?',[$sid])) jsonErr('Station not found.',404);
  $ex=dbOne('SELECT favorite_id FROM favorites WHERE user_id=? AND station_id=?',[uid(),$sid]);
  if($ex){
    dbQ('DELETE FROM favorites WHERE user_id=? AND station_id=?',[uid(),$sid]);
    jsonOk(['action'=>'removed'],'Removed from favorites.');
  }
  dbQ('INSERT INTO favorites (user_id,station_id) VALUES (?,?)',[uid(),$sid]);
  jsonOk(['action'=>'added'],'Added to favorites ❤️');
}
jsonErr('Method not allowed.',405);
