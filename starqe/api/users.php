<?php
require_once '../config/config.php';
header('Content-Type: application/json; charset=UTF-8');
if(!isLoggedIn()) jsonErr('Login required.',401);
$method=$_SERVER['REQUEST_METHOD'];
if($method==='POST'&&($_POST['_method']??'')==='PUT')    $method='PUT';
if($method==='POST'&&($_POST['_method']??'')==='DELETE') $method='DELETE';

if($method==='GET'){
  if(!empty($_GET['export'])&&isAdmin()){
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="starqe_users_'.date('Ymd').'.csv"');
    $rows=dbAll('SELECT user_id,fullname,email,role,status,created_at FROM users ORDER BY created_at DESC');
    $out=fopen('php://output','w'); fputcsv($out,['ID','Name','Email','Role','Status','Registered']);
    foreach($rows as $r) fputcsv($out,array_values($r)); fclose($out); exit;
  }
  if(!isAdmin()) jsonErr('Admin only.',403);
  $page=max(1,si($_GET['page']??1));$per=20;$off=($page-1)*$per;
  $q=s($_GET['q']??'');
  $w=$q?"WHERE fullname LIKE ? OR email LIKE ?":'';
  $p=$q?["%$q%","%$q%"]:[];
  $rows=dbAll("SELECT user_id,fullname,email,role,status,profile_picture,created_at FROM users $w ORDER BY created_at DESC LIMIT $per OFFSET $off",$p);
  $tot=dbCount("SELECT COUNT(*) FROM users $w",$p);
  jsonOk(['users'=>$rows,'total'=>$tot,'pages'=>(int)ceil($tot/$per)]);
}
if($method==='POST'){
  $action=s($_POST['action']??'');
  if($action==='update_profile'){
    $data=[];$params=[];
    if(!empty($_POST['fullname'])){$data[]='fullname=?';$params[]=s($_POST['fullname']);}
    if(!empty($_POST['email'])){
      if(!validEmail($_POST['email'])) jsonErr('Invalid email.');
      $dup=dbOne('SELECT user_id FROM users WHERE email=? AND user_id!=?',[se($_POST['email']),uid()]);
      if($dup) jsonErr('Email already in use.');
      $data[]='email=?';$params[]=se($_POST['email']);
    }
    if($data){$params[]=uid();dbQ('UPDATE users SET '.implode(',',$data).' WHERE user_id=?',$params);}
    if(!empty($_POST['fullname'])) $_SESSION['fullname']=$_POST['fullname'];
    if(!empty($_POST['email']))    $_SESSION['email']=$_POST['email'];
    logHistory(uid(),'PROFILE_UPDATED','Profile updated');
    jsonOk(null,'Profile updated.');
  }
  if($action==='change_password'){
    $cur=$_POST['current_password']??'';$new=$_POST['new_password']??'';$conf=$_POST['confirm_password']??'';
    $u=dbOne('SELECT password_hash FROM users WHERE user_id=?',[uid()]);
    if(!password_verify($cur,$u['password_hash'])) jsonErr('Current password is incorrect.');
    if(strlen($new)<8) jsonErr('New password must be at least 8 characters.');
    if(!preg_match('/[A-Z]/',$new)) jsonErr('Needs at least one uppercase letter.');
    if(!preg_match('/[0-9]/',$new)) jsonErr('Needs at least one number.');
    if($new!==$conf) jsonErr('Passwords do not match.');
    dbQ('UPDATE users SET password_hash=? WHERE user_id=?',[password_hash($new,PASSWORD_BCRYPT,['cost'=>12]),uid()]);
    logHistory(uid(),'PASSWORD_CHANGED','Password changed');
    jsonOk(null,'Password updated.');
  }
  if($action==='upload_picture'){
    if(empty($_FILES['picture'])) jsonErr('No file uploaded.');
    $f=$_FILES['picture'];
    if($f['error']!==UPLOAD_ERR_OK) jsonErr('Upload error.');
    if($f['size']>2*1024*1024) jsonErr('Max 2MB allowed.');
    $fi=finfo_open(FILEINFO_MIME_TYPE); $mime=finfo_file($fi,$f['tmp_name']); finfo_close($fi);
    if(!in_array($mime,['image/jpeg','image/png','image/webp','image/gif'])) jsonErr('Invalid image type.');
    $ext=strtolower(pathinfo($f['name'],PATHINFO_EXTENSION));
    $fn='profile_'.uid().'_'.time().'.'.$ext;
    $dir=__DIR__.'/../assets/uploads/profiles/';
    if(!is_dir($dir)) mkdir($dir,0755,true);
    if(!move_uploaded_file($f['tmp_name'],$dir.$fn)) jsonErr('Failed to save file.');
    dbQ('UPDATE users SET profile_picture=? WHERE user_id=?',[$fn,uid()]);
    $_SESSION['pic']=$fn;
    jsonOk(['url'=>BASE_URL.'/assets/uploads/profiles/'.$fn],'Photo updated.');
  }
  jsonErr('Unknown action.');
}
if($method==='PUT'){
  if(!isAdmin()) jsonErr('Admin only.',403);
  $id=si($_POST['user_id']??0); if(!$id) jsonErr('user_id required.');
  $data=[];$params=[];
  if(isset($_POST['status'])&&in_array($_POST['status'],['enabled','disabled'])){$data[]='status=?';$params[]=$_POST['status'];}
  if(isset($_POST['role'])&&in_array($_POST['role'],['admin','user'])){$data[]='role=?';$params[]=$_POST['role'];}
  if(!empty($_POST['fullname'])){$data[]='fullname=?';$params[]=s($_POST['fullname']);}
  if($data){$params[]=$id;dbQ('UPDATE users SET '.implode(',',$data).' WHERE user_id=?',$params);}
  logHistory(uid(),'USER_UPDATED',"User #$id updated by admin");
  jsonOk(null,'User updated.');
}
if($method==='DELETE'){
  if(!isAdmin()) jsonErr('Admin only.',403);
  $id=si($_POST['user_id']??0);
  if(!$id||$id===uid()) jsonErr('Cannot delete this user.');
  dbQ('DELETE FROM users WHERE user_id=?',[$id]);
  logHistory(uid(),'USER_DELETED',"User #$id deleted");
  jsonOk(null,'User deleted.');
}
jsonErr('Method not allowed.',405);
