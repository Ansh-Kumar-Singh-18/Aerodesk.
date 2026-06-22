<?php
/* =====================================================================
   export.php  -  CSV / Excel exports & booking reports (download)
   ?type=passengers | bookings | report   (admin & agent only)
   Opens cleanly in Excel / Google Sheets.
   ===================================================================== */
session_start();
ob_start();                              // capture stray output
error_reporting(0);
ini_set('display_errors','0');           // never leak notices into the CSV
require __DIR__ . '/config.php';
db();
require_login();
if (!is_role('admin','agent')) { http_response_code(403); exit('Forbidden — admins/agents only.'); }

$type = $_GET['type'] ?? 'passengers';

/* PHP 8.4-safe CSV line writer */
function csv_line($out, array $fields){
    $cells = array_map(function($v){
        $v = (string)$v;
        if (preg_match('/[",\n\r]/', $v)) $v = '"'.str_replace('"','""',$v).'"';
        return $v;
    }, $fields);
    fwrite($out, implode(',', $cells)."\r\n");
}

function send_csv($filename, $headers, $rows){
    if (ob_get_length() !== false) { @ob_end_clean(); }
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="'.$filename.'"');
    $out = fopen('php://output', 'w');
    fwrite($out, chr(0xEF).chr(0xBB).chr(0xBF)); fwrite($out, "sep=,\r\n"); // BOM + Excel separator hint
    csv_line($out, $headers);
    foreach ($rows as $r) csv_line($out, $r);
    fclose($out);
    exit;
}

if ($type === 'passengers') {
    $rows = q_all("SELECT pid,pname,pgender,pcity FROM passenger ORDER BY pid");
    audit('export','passengers.csv');
    send_csv('passengers_'.date('Ymd').'.csv',
        ['Passenger ID','Name','Gender','City'],
        array_map(fn($r)=>[$r['pid'],$r['pname'],$r['pgender'],$r['pcity']], $rows));
}

if ($type === 'bookings') {
    $rows = q_all("SELECT b.bid,b.pid,p.pname,a.aname,b.fid,f.src,f.dest,f.time,b.fdate,b.seat
                   FROM booking b JOIN passenger p ON b.pid=p.pid
                   JOIN agency a ON b.aid=a.aid JOIN flight f ON b.fid=f.fid
                   ORDER BY b.fdate, b.pid");
    audit('export','bookings.csv');
    send_csv('bookings_'.date('Ymd').'.csv',
        ['Booking#','PID','Passenger','Agency','Flight','From','To','Time','Date','Seat'],
        array_map(fn($r)=>[$r['bid'],$r['pid'],$r['pname'],$r['aname'],$r['fid'],$r['src'],$r['dest'],$r['time'],$r['fdate'],$r['seat']], $rows));
}

if ($type === 'report') {
    audit('export','booking_report.csv');
    if (ob_get_length() !== false) { @ob_end_clean(); }
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="booking_report_'.date('Ymd').'.csv"');
    $out = fopen("php://output","w"); fwrite($out, chr(0xEF).chr(0xBB).chr(0xBF)); fwrite($out, "sep=,\r\n");
    csv_line($out, ['AeroDesk — Booking Report', date('Y-m-d H:i')]);
    csv_line($out, []);
    csv_line($out, ['Bookings by Date','Count']);
    foreach (q_all("SELECT fdate, COUNT(*) c FROM booking GROUP BY fdate ORDER BY fdate") as $r) csv_line($out,[$r['fdate'],$r['c']]);
    csv_line($out, []);
    csv_line($out, ['Bookings by Agency','Count']);
    foreach (q_all("SELECT a.aname, COUNT(*) c FROM booking b JOIN agency a ON b.aid=a.aid GROUP BY a.aname ORDER BY c DESC") as $r) csv_line($out,[$r['aname'],$r['c']]);
    csv_line($out, []);
    csv_line($out, ['Bookings by Route','Count']);
    foreach (q_all("SELECT CONCAT(f.src,' to ',f.dest) route, COUNT(*) c FROM booking b JOIN flight f ON b.fid=f.fid GROUP BY route ORDER BY c DESC") as $r) csv_line($out,[$r['route'],$r['c']]);
    csv_line($out, []);
    csv_line($out, ['Bookings by Gender','Count']);
    foreach (q_all("SELECT p.pgender, COUNT(*) c FROM booking b JOIN passenger p ON b.pid=p.pid GROUP BY p.pgender") as $r) csv_line($out,[$r['pgender'],$r['c']]);
    fclose($out); exit;
}

http_response_code(400); echo 'Unknown export type.';
