<?php
require_once '../config/config.php';
header('Content-Type: application/json; charset=UTF-8');
if(!isAdmin()) jsonErr('Admin only.',403);
$period=s($_GET['period']??'month');
$limit=match($period){'week'=>"AND b.created_at>=DATE_SUB(NOW(),INTERVAL 7 DAY)",'year'=>"AND b.created_at>=DATE_SUB(NOW(),INTERVAL 12 MONTH)",default=>"AND b.created_at>=DATE_SUB(NOW(),INTERVAL 30 DAY)"};
$grp=match($period){'year'=>"DATE_FORMAT(b.created_at,'%Y-%m')",default=>"DATE(b.created_at)"};
jsonOk([
  'totals'=>[
    'users'            =>dbCount('SELECT COUNT(*) FROM users'),
    'stations'         =>dbCount('SELECT COUNT(*) FROM stations'),
    'bookings'         =>dbCount('SELECT COUNT(*) FROM bookings'),
    'revenue'          =>(float)(dbOne("SELECT COALESCE(SUM(total_amount),0) r FROM bookings WHERE status IN ('approved','completed')")['r']??0),
    'available'        =>dbCount("SELECT COUNT(*) FROM stations WHERE status='available'"),
    'new_users'        =>dbCount("SELECT COUNT(*) FROM users WHERE MONTH(created_at)=MONTH(NOW()) AND YEAR(created_at)=YEAR(NOW())"),
    'bookings_month'   =>dbCount("SELECT COUNT(*) FROM bookings WHERE MONTH(created_at)=MONTH(NOW()) AND YEAR(created_at)=YEAR(NOW())"),
    'pending_bookings' =>dbCount("SELECT COUNT(*) FROM bookings WHERE status='pending'"),
    'pending_reports'  =>dbCount("SELECT COUNT(*) FROM reports WHERE status='pending'"),
  ],
  'revenue_chart'  =>dbAll("SELECT $grp period,COALESCE(SUM(total_amount),0) revenue,COUNT(*) cnt FROM bookings b WHERE status IN ('approved','completed') $limit GROUP BY $grp ORDER BY period"),
  'station_usage'  =>dbAll("SELECT s.station_name,COUNT(b.booking_id) bookings,COALESCE(SUM(b.total_amount),0) revenue FROM bookings b JOIN stations s ON b.station_id=s.station_id WHERE b.status IN ('approved','completed') GROUP BY b.station_id ORDER BY bookings DESC LIMIT 8"),
  'peak_hours'     =>dbAll("SELECT HOUR(booking_time) hr,COUNT(*) cnt FROM bookings GROUP BY HOUR(booking_time) ORDER BY hr"),
  'station_status' =>dbAll("SELECT status,COUNT(*) cnt FROM stations GROUP BY status"),
  'user_growth'    =>dbAll("SELECT DATE_FORMAT(created_at,'%Y-%m') month,COUNT(*) cnt FROM users GROUP BY DATE_FORMAT(created_at,'%Y-%m') ORDER BY month DESC LIMIT 12"),
]);
