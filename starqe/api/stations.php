<?php
require_once '../config/config.php';
header('Content-Type: application/json; charset=UTF-8');
$method=$_SERVER['REQUEST_METHOD'];
// Support method override
if($method==='POST'&&($_POST['_method']??'')==='PUT')   $method='PUT';
if($method==='POST'&&($_POST['_method']??'')==='DELETE') $method='DELETE';

// ── GET ────────────────────────────────────────────────────────
if($method==='GET'){
  // Single station
  if(!empty($_GET['id'])){
    $id=si($_GET['id']);
    $s=dbOne('SELECT * FROM stations WHERE station_id=?',[$id]);
    if(!$s) jsonErr('Station not found.',404);
    $s['is_favorite']=isUser()?isFav(uid(),(int)$s['station_id']):false;
    jsonOk($s);
  }
  // Nearby
  if(!empty($_GET['nearby'])){
    $lat=sf($_GET['lat']??0); $lng=sf($_GET['lng']??0);
    if(!$lat||!$lng) jsonErr('lat and lng required.');
    $rows=dbAll("SELECT *,(6371*ACOS(COS(RADIANS(?))*COS(RADIANS(latitude))*COS(RADIANS(longitude)-RADIANS(?))+SIN(RADIANS(?))*SIN(RADIANS(latitude)))) AS distance FROM stations HAVING distance<=50 ORDER BY distance LIMIT 10",[$lat,$lng,$lat]);
    jsonOk($rows);
  }
  // Filtered list for map
  $where=[];$p=[];
  if(!empty($_GET['status']))  {$where[]='status=?';        $p[]=$_GET['status'];}
  if(!empty($_GET['type']))    {$where[]='charging_type=?'; $p[]=$_GET['type'];}
  if(!empty($_GET['city']))    {$where[]='city LIKE ?';     $p[]='%'.$_GET['city'].'%';}
  if(!empty($_GET['fast']))    {$where[]='is_fast_charge=1';}
  if(!empty($_GET['q'])){
    $sq='%'.$_GET['q'].'%';
    $where[]='(station_name LIKE ? OR address LIKE ? OR city LIKE ? OR operator LIKE ?)';
    array_push($p,$sq,$sq,$sq,$sq);
  }
  $wc=$where?'WHERE '.implode(' AND ',$where):'';
  $rows=dbAll("SELECT station_id,station_name,address,city,latitude,longitude,status,charging_type,power_capacity,available_ports,total_ports,price_per_kwh,operator,opening_time,closing_time,is_verified,is_fast_charge FROM stations $wc ORDER BY station_name",$p);
  $favIds=isUser()?favIds(uid()):[];
  foreach($rows as &$row) $row['is_favorite']=in_array((int)$row['station_id'],$favIds);
  jsonOk($rows);
}

// ── POST (Create) ──────────────────────────────────────────────
if($method==='POST'){
  if(!isAdmin()) jsonErr('Admin only.',403);
  $d=$_POST;
  $required=['station_name','city','latitude','longitude'];
  foreach($required as $f){ if(empty($d[$f])) jsonErr("Field '$f' is required."); }

  // Duplicate check
  $dup=dbOne('SELECT station_id FROM stations WHERE station_name=? AND city=?',[s($d['station_name']),s($d['city'])]);
  if($dup) jsonErr('A station with this name already exists in '.$d['city'].'.');

  // Handle image upload
  $imgName=null;
  if(!empty($_FILES['image']['name'])){
    $ext=strtolower(pathinfo($_FILES['image']['name'],PATHINFO_EXTENSION));
    if(!in_array($ext,['jpg','jpeg','png','webp'])) jsonErr('Invalid image type.');
    if($_FILES['image']['size']>3*1024*1024) jsonErr('Image too large (max 3MB).');
    $imgName='station_'.time().'_'.mt_rand(1000,9999).'.'.$ext;
    $dir=__DIR__.'/../assets/uploads/stations/';
    if(!is_dir($dir)) mkdir($dir,0755,true);
    if(!move_uploaded_file($_FILES['image']['tmp_name'],$dir.$imgName)) jsonErr('Image upload failed.');
  }

  dbQ('INSERT INTO stations (station_name,address,city,latitude,longitude,status,charging_type,power_capacity,available_ports,total_ports,price_per_kwh,operator,opening_time,closing_time,image,is_verified,is_fast_charge) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
    [s($d['station_name']),s($d['address']??''),s($d['city']),sf($d['latitude']),sf($d['longitude']),
     s($d['status']??'available'),s($d['charging_type']??'AC'),sf($d['power_capacity']??7.4),
     si($d['available_ports']??2),si($d['total_ports']??2),sf($d['price_per_kwh']??12),
     s($d['operator']??''),s($d['opening_time']??'06:00:00'),s($d['closing_time']??'22:00:00'),
     $imgName,si($d['is_verified']??0),si($d['is_fast_charge']??0)]);
  $newId=dbId();
  logHistory(uid(),'STATION_CREATED','Station created: '.s($d['station_name']).' #'.$newId);
  jsonOk(['station_id'=>$newId],'Station created successfully!',201);
}

// ── PUT (Update) — FIX: always use station_id from payload ─────
if($method==='PUT'){
  if(!isAdmin()) jsonErr('Admin only.',403);
  // CRITICAL FIX: station_id must come from form data
  $id=si($_POST['station_id']??0);
  if(!$id) jsonErr('station_id is required for update.');

  // Verify station exists
  $existing=dbOne('SELECT station_id,image FROM stations WHERE station_id=?',[$id]);
  if(!$existing) jsonErr("Station #$id not found. Cannot update.",404);

  // Build update fields dynamically
  $fields=[];$params=[];
  $allowed=['station_name','address','city','status','charging_type','operator','opening_time','closing_time'];
  foreach($allowed as $f){
    if(isset($_POST[$f])){$fields[]="$f=?";$params[]=s($_POST[$f]);}
  }
  $numericFields=['latitude'=>'sf','longitude'=>'sf','power_capacity'=>'sf','price_per_kwh'=>'sf','available_ports'=>'si','total_ports'=>'si','is_verified'=>'si','is_fast_charge'=>'si'];
  foreach($numericFields as $f=>$fn){
    if(isset($_POST[$f])){$fields[]="$f=?";$params[]=$fn($_POST[$f]);}
  }

  // Handle image
  if(!empty($_FILES['image']['name'])){
    $ext=strtolower(pathinfo($_FILES['image']['name'],PATHINFO_EXTENSION));
    if(!in_array($ext,['jpg','jpeg','png','webp'])) jsonErr('Invalid image type.');
    $imgName='station_'.$id.'_'.time().'.'.$ext;
    $dir=__DIR__.'/../assets/uploads/stations/';
    if(!is_dir($dir)) mkdir($dir,0755,true);
    if(move_uploaded_file($_FILES['image']['tmp_name'],$dir.$imgName)){
      $fields[]='image=?'; $params[]=$imgName;
    }
  }

  if(empty($fields)) jsonErr('No fields to update.');
  $params[]=$id;
  dbQ('UPDATE stations SET '.implode(',',$fields).',updated_at=NOW() WHERE station_id=?',$params);
  logHistory(uid(),'STATION_UPDATED',"Station #$id updated");

  // Return updated station for optimistic UI
  $updated=dbOne('SELECT * FROM stations WHERE station_id=?',[$id]);
  jsonOk($updated,'Station updated successfully!');
}

// ── DELETE ─────────────────────────────────────────────────────
if($method==='DELETE'){
  if(!isAdmin()) jsonErr('Admin only.',403);
  $id=si($_POST['station_id']??$_GET['id']??0);
  if(!$id) jsonErr('station_id required.');
  $existing=dbOne('SELECT station_id,station_name FROM stations WHERE station_id=?',[$id]);
  if(!$existing) jsonErr("Station #$id not found.",404);
  dbQ('DELETE FROM stations WHERE station_id=?',[$id]);
  logHistory(uid(),'STATION_DELETED','Station #'.$id.' deleted: '.$existing['station_name']);
  jsonOk(null,'Station deleted.');
}

jsonErr('Method not allowed.',405);
