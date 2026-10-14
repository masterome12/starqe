<?php
// ============================================================
// STARQE v2.0 — Master Config & Helpers
// ============================================================
session_start();

define('DB_HOST',    'localhost');
define('DB_NAME',    'starqe_db');
define('DB_USER',    'root');
define('DB_PASS',    '');
define('APP_FOLDER', 'starqe');
define('BASE_URL',   'http://localhost/' . APP_FOLDER);
define('APP_NAME',   'STARQE');

date_default_timezone_set('Asia/Manila');

// ── PDO ──────────────────────────────────────────────────────
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',
                DB_USER, DB_PASS,
                [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
                 PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
                 PDO::ATTR_EMULATE_PREPARES=>false]);
        } catch (PDOException $e) {
            header('Content-Type: application/json');
            die(json_encode(['success'=>false,'message'=>'Database connection failed.']));
        }
    }
    return $pdo;
}
function dbQ(string $sql, array $p=[]): PDOStatement { $s=getDB()->prepare($sql); $s->execute($p); return $s; }
function dbOne(string $sql, array $p=[]): ?array     { $r=dbQ($sql,$p)->fetch(); return $r?:null; }
function dbAll(string $sql, array $p=[]): array      { return dbQ($sql,$p)->fetchAll(); }
function dbId(): int                                 { return (int)getDB()->lastInsertId(); }
function dbCount(string $sql, array $p=[]): int      { return (int)(dbQ($sql,$p)->fetchColumn()??0); }

// ── Auth ──────────────────────────────────────────────────────
function isLoggedIn(): bool { return !empty($_SESSION['uid']); }
function isAdmin(): bool    { return isLoggedIn() && ($_SESSION['role']??'')==='admin'; }
function isUser(): bool     { return isLoggedIn() && in_array($_SESSION['role']??'',['user','admin']); }
function uid(): int         { return (int)($_SESSION['uid']??0); }
function uname(): string    { return htmlspecialchars($_SESSION['fullname']??'Guest',ENT_QUOTES,'UTF-8'); }
function urole(): string    { return $_SESSION['role']??'guest'; }
function upic(): string     { return $_SESSION['pic']??''; }
function uemail(): string   { return htmlspecialchars($_SESSION['email']??'',ENT_QUOTES,'UTF-8'); }

function setUserSession(array $u): void {
    session_regenerate_id(true);
    $_SESSION['uid']      = (int)$u['user_id'];
    $_SESSION['fullname'] = $u['fullname'];
    $_SESSION['email']    = $u['email'];
    $_SESSION['role']     = $u['role'];
    $_SESSION['pic']      = $u['profile_picture']??'';
    $_SESSION['login_at'] = time();
}
function clearSession(): void {
    $_SESSION=[];
    if(isset($_COOKIE[session_name()])) setcookie(session_name(),'',time()-3600,'/');
    session_destroy();
}
function requireLogin(): void {
    if(!isLoggedIn()){ header('Location:'.BASE_URL.'/login.php'); exit; }
}
function requireAdmin(): void {
    if(!isAdmin()){ header('Location:'.BASE_URL.'/index.php'); exit; }
}

// ── CSRF ──────────────────────────────────────────────────────
function csrf(): string {
    if(empty($_SESSION['csrf'])) $_SESSION['csrf']=bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function verifyCsrf(?string $t): bool {
    return !empty($_SESSION['csrf']) && !empty($t) && hash_equals($_SESSION['csrf'],$t);
}

// ── Sanitize ──────────────────────────────────────────────────
function s(?string $v): string  { return htmlspecialchars(trim((string)$v),ENT_QUOTES|ENT_HTML5,'UTF-8'); }
function si($v): int            { return (int)filter_var($v,FILTER_SANITIZE_NUMBER_INT); }
function sf($v): float          { return (float)filter_var($v,FILTER_SANITIZE_NUMBER_FLOAT,FILTER_FLAG_ALLOW_FRACTION); }
function se(?string $v): string { return filter_var(trim((string)$v),FILTER_SANITIZE_EMAIL); }
function validEmail(string $e): bool { return (bool)filter_var($e,FILTER_VALIDATE_EMAIL); }

// ── JSON ──────────────────────────────────────────────────────
function jsonOk($data=null, string $msg='Success'): void {
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(['success'=>true,'message'=>$msg,'data'=>$data]); exit;
}
function jsonErr(string $msg='Error', int $code=400): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(['success'=>false,'message'=>$msg,'data'=>null]); exit;
}

// ── Utils ─────────────────────────────────────────────────────
function redirect(string $path): void { header('Location:'.BASE_URL.$path); exit; }
function genRef(string $pfx='STQ'): string { return $pfx.'-'.strtoupper(substr(md5(uniqid(mt_rand(),true)),0,8)); }
function money(float $v): string { return '₱'.number_format($v,2); }
function timeAgo(string $d): string {
    $diff=time()-strtotime($d);
    if($diff<60)    return $diff.'s ago';
    if($diff<3600)  return floor($diff/60).'m ago';
    if($diff<86400) return floor($diff/3600).'h ago';
    return floor($diff/86400).'d ago';
}
function getSettings(): array {
    return dbOne('SELECT * FROM settings LIMIT 1') ?? [
        'site_name'=>'STARQE','contact_email'=>'support@starqe.ph',
        'default_price'=>12,'maintenance_mode'=>0,
        'booking_advance_days'=>7,'max_booking_hours'=>8
    ];
}
function logHistory(?int $uid, string $action, string $desc): void {
    $ip=$_SERVER['HTTP_CLIENT_IP']??$_SERVER['HTTP_X_FORWARDED_FOR']??$_SERVER['REMOTE_ADDR']??'0.0.0.0';
    dbQ('INSERT INTO history (user_id,action,description,ip_address) VALUES (?,?,?,?)',[$uid,$action,$desc,$ip]);
}
function statusBadge(string $s): string {
    $map=['available'=>['bg-success-soft','text-success','Available'],
          'busy'     =>['bg-warning-soft','text-warning','Busy'],
          'full'     =>['bg-danger-soft', 'text-danger', 'Full'],
          'maintenance'=>['bg-secondary-soft','text-secondary','Maintenance'],
          'pending'  =>['bg-warning-soft','text-warning','Pending'],
          'confirmed'=>['bg-success-soft','text-success','Confirmed'],
          'approved' =>['bg-success-soft','text-success','Approved'],
          'rejected' =>['bg-danger-soft', 'text-danger', 'Rejected'],
          'cancelled'=>['bg-danger-soft', 'text-danger', 'Cancelled'],
          'completed'=>['bg-info-soft',   'text-info',   'Completed'],
          'enabled'  =>['bg-success-soft','text-success','Enabled'],
          'disabled' =>['bg-danger-soft', 'text-danger', 'Disabled']];
    [$bg,$tc,$lbl]=$map[$s]??['bg-secondary-soft','text-secondary',ucfirst($s)];
    return "<span class='badge-status $bg $tc'>$lbl</span>";
}
function favIds(int $uid): array {
    return array_column(dbAll('SELECT station_id FROM favorites WHERE user_id=?',[$uid]),'station_id');
}
function isFav(int $uid, int $sid): bool {
    return (bool)dbOne('SELECT 1 FROM favorites WHERE user_id=? AND station_id=?',[$uid,$sid]);
}
function getClientIP(): string {
    return $_SERVER['HTTP_CLIENT_IP']??$_SERVER['HTTP_X_FORWARDED_FOR']??$_SERVER['REMOTE_ADDR']??'0.0.0.0';
}
