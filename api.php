<?php
/* =====================================================================
   api.php  -  JSON API for AeroDesk (all AJAX actions)
   ===================================================================== */
session_start();
require __DIR__ . '/config.php';
require __DIR__ . '/queries.php';
db();

$action = $_REQUEST['action'] ?? '';

/* ---- public: nothing here needs to be public except login handled elsewhere ---- */
if (empty($_SESSION['uid'])) json_out(['ok'=>false,'error'=>'Not authenticated']);

$role = $_SESSION['role'] ?? 'customer';
function admin_only(){ if (role() !== 'admin') json_out(['ok'=>false,'error'=>'Admins only']); }
/* allow only the listed roles, else reject */
function allow(...$roles){ if (!in_array(role(), $roles, true)) json_out(['ok'=>false,'error'=>'You do not have permission for this action']); }
/* customers only see their own data; returns a WHERE-ready filter on b.pid (or '' for admin/agent) */
/* Resolve the logged-in customer's linked passenger id.
   Prefers config's current_pid() (DB lookup), but is fully self-contained so
   the app never fatals even if config.php is an older copy without it. */
function my_pid(){
    if (function_exists('current_pid')) return current_pid();
    if (!empty($_SESSION['pid'])) return $_SESSION['pid'];
    if (!empty($_SESSION['uid'])) {
        $uid = (int)$_SESSION['uid'];
        $pid = @q_val("SELECT pid FROM users WHERE uid=$uid LIMIT 1");
        if ($pid !== null && $pid !== '') { $_SESSION['pid'] = $pid; return $pid; }
    }
    return '';
}
function cust_filter($prefix='b'){
    if (role()==='customer') {
        $pid = esc(my_pid());
        return " AND $prefix.pid = '".$pid."' ";   // empty pid -> matches nothing (safe)
    }
    return '';
}

switch ($action) {

/* ---------------- READ / STATS ---------------- */
case 'stats':
    $cf = cust_filter('b');   // '' for admin/agent, " AND b.pid='<pid>' " for customers
    $byDest = q_all("SELECT f.dest, COUNT(*) c FROM booking b JOIN flight f ON b.fid=f.fid WHERE 1=1 $cf GROUP BY f.dest ORDER BY c DESC");
    $byAgency = q_all("SELECT a.aname, COUNT(*) c FROM booking b JOIN agency a ON b.aid=a.aid WHERE 1=1 $cf GROUP BY a.aname ORDER BY c DESC");
    $bySrc = q_all("SELECT f.src, COUNT(*) c FROM booking b JOIN flight f ON b.fid=f.fid WHERE 1=1 $cf GROUP BY f.src ORDER BY c DESC");
    $myPid = esc(my_pid());
    $myBookings = (role()==='customer' && $myPid!=='')
        ? (int)q_val("SELECT COUNT(*) FROM booking WHERE pid='$myPid'")
        : (int)q_val("SELECT COUNT(*) FROM booking");
    json_out(['ok'=>true,
        'counts'=>[
            'passenger'=>(int)q_val("SELECT COUNT(*) FROM passenger"),
            'agency'=>(int)q_val("SELECT COUNT(*) FROM agency"),
            'flight'=>(int)q_val("SELECT COUNT(*) FROM flight"),
            'booking'=>(int)q_val("SELECT COUNT(*) FROM booking"),
            'myBooking'=>$myBookings,
        ],
        'byDest'=>$byDest, 'byAgency'=>$byAgency, 'bySrc'=>$bySrc,
        'recent'=>q_all("SELECT b.bid,b.pid,p.pname,a.aname,f.src,f.dest,b.fdate,b.seat
                         FROM booking b JOIN passenger p ON b.pid=p.pid
                         JOIN agency a ON b.aid=a.aid JOIN flight f ON b.fid=f.fid
                         WHERE 1=1 $cf ORDER BY b.bid DESC LIMIT 6"),
    ]);

case 'lists':
    // customers only get their own passenger record
    $paxWhere = (role()==='customer') ? "WHERE pid='".esc(my_pid())."'" : '';
    json_out(['ok'=>true,
        'passengers'=>q_all("SELECT * FROM passenger $paxWhere ORDER BY pid"),
        'agencies'=>q_all("SELECT * FROM agency ORDER BY aid"),
        'flights'=>q_all("SELECT * FROM flight ORDER BY fid"),
        'cities'=>q_all("SELECT src AS city FROM flight UNION SELECT dest FROM flight UNION SELECT pcity FROM passenger UNION SELECT acity FROM agency ORDER BY city"),
        'nextP'=>next_id('passenger','pid','101'),
        'nextA'=>next_id('agency','aid','201'),
        'nextF'=>next_id('flight','fid','301'),
    ]);

case 'search':
    $w=[]; foreach(['src','dest'] as $k){ if(!empty($_GET[$k])) $w[]="$k='".esc($_GET[$k])."'"; }
    if(!empty($_GET['fdate'])) $w[]="fdate='".esc($_GET['fdate'])."'";
    if(!empty($_GET['time']))  $w[]="TIME_FORMAT(time,'%H:%i')='".esc($_GET['time'])."'";
    $where = $w ? 'WHERE '.implode(' AND ',$w) : '';
    json_out(['ok'=>true,'rows'=>q_all("SELECT * FROM flight $where ORDER BY fdate,time")]);

case 'table':
    allow('admin','agent');
    $t = $_GET['t'] ?? 'passenger';
    if(!in_array($t,['passenger','agency','flight','booking'])) json_out(['ok'=>false,'error'=>'bad table']);
    if ($t==='booking')
        json_out(['ok'=>true,'rows'=>q_all("SELECT b.bid,b.pid,p.pname,b.aid,a.aname,b.fid,f.src,f.dest,b.fdate,b.seat
            FROM booking b JOIN passenger p ON b.pid=p.pid JOIN agency a ON b.aid=a.aid JOIN flight f ON b.fid=f.fid ORDER BY b.bid")]);
    json_out(['ok'=>true,'rows'=>q_all("SELECT * FROM $t ORDER BY 1")]);

case 'bookings':
    $cf = cust_filter('b');   // customers: only their own bookings
    json_out(['ok'=>true,'rows'=>q_all("SELECT b.bid,b.pid,p.pname,b.aid,a.aname,b.fid,f.src,f.dest,b.fdate,b.seat
        FROM booking b JOIN passenger p ON b.pid=p.pid JOIN agency a ON b.aid=a.aid JOIN flight f ON b.fid=f.fid
        WHERE 1=1 $cf ORDER BY b.fdate,b.pid")]);

case 'sqllab':
    allow('admin','agent');
    global $QUERIES;
    $i = max(0, min(count($QUERIES)-1, (int)($_GET['q'] ?? 0)));
    $rows = q_all($QUERIES[$i][1]);
    json_out(['ok'=>true,'rows'=>isset($rows['__error'])?[]:$rows,
        'error'=>$rows['__error']??null,
        'count'=>isset($rows['__error'])?0:count($rows)]);

/* ---------------- CREATE ---------------- */
case 'book':
    allow('admin','agent','customer');
    $pid=esc($_POST['pid']??''); $aid=esc($_POST['aid']??''); $fid=esc($_POST['fid']??'');
    // a customer can only book for their own linked passenger
    if (role()==='customer') {
        if (my_pid()==='') json_out(['ok'=>false,'error'=>'Your account is not linked to a passenger. Contact an admin.']);
        $pid = esc(my_pid());
    }
    $seat=esc(strtoupper(trim($_POST['seat']??'')));
    if(!$pid||!$aid||!$fid) json_out(['ok'=>false,'error'=>'All fields required']);
    if(q_val("SELECT COUNT(*) FROM booking WHERE pid='$pid' AND fid='$fid'")>0)
        json_out(['ok'=>false,'error'=>"Passenger already booked on flight $fid"]);
    $fd=esc(q_val("SELECT fdate FROM flight WHERE fid='$fid'"));
    if($seat===''){ $seat = sprintf('%02d%s', rand(1,30), chr(rand(65,70))); }
    if(db()->query("INSERT INTO booking (pid,aid,fid,fdate,seat) VALUES ('$pid','$aid','$fid','$fd','$seat')")){
        $bid=(int)db()->insert_id;
        audit('book',"pid=$pid flight=$fid seat=$seat");
        json_out(['ok'=>true,'msg'=>"Booked! Seat $seat on flight $fid",'seat'=>$seat,'bid'=>$bid]);
    }
    json_out(['ok'=>false,'error'=>db()->error]);

case 'add_passenger':
    admin_only();
    $pid=trim($_POST['pid']??''); if($pid==='')$pid=next_id('passenger','pid','101'); $pid=esc($pid);
    $nm=esc(trim($_POST['pname']??'')); $g=esc($_POST['pgender']??'Male'); $ct=esc(trim($_POST['pcity']??''));
    if($nm===''||$ct==='') json_out(['ok'=>false,'error'=>'Name and city required']);
    if(q_val("SELECT COUNT(*) FROM passenger WHERE pid='$pid'")>0) json_out(['ok'=>false,'error'=>"ID $pid exists"]);
    db()->query("INSERT INTO passenger VALUES('$pid','$nm','$g','$ct')");
    audit('add_passenger',"$pid $nm");
    json_out(['ok'=>true,'msg'=>"Passenger $pid added"]);

case 'add_agency':
    admin_only();
    $aid=trim($_POST['aid']??''); if($aid==='')$aid=next_id('agency','aid','201'); $aid=esc($aid);
    $nm=esc(trim($_POST['aname']??'')); $ct=esc(trim($_POST['acity']??''));
    if($nm===''||$ct==='') json_out(['ok'=>false,'error'=>'Name and city required']);
    if(q_val("SELECT COUNT(*) FROM agency WHERE aid='$aid'")>0) json_out(['ok'=>false,'error'=>"ID $aid exists"]);
    db()->query("INSERT INTO agency VALUES('$aid','$nm','$ct')");
    audit('add_agency',"$aid $nm");
    json_out(['ok'=>true,'msg'=>"Agency $aid added"]);

case 'add_flight':
    admin_only();
    $fid=trim($_POST['fid']??''); if($fid==='')$fid=next_id('flight','fid','301'); $fid=esc($fid);
    $fd=esc(trim($_POST['fdate']??'')); $tm=esc(trim($_POST['time']??''));
    $sc=esc(trim($_POST['src']??'')); $de=esc(trim($_POST['dest']??''));
    if(!$fd||!$tm||!$sc||!$de) json_out(['ok'=>false,'error'=>'All fields required']);
    if(q_val("SELECT COUNT(*) FROM flight WHERE fid='$fid'")>0) json_out(['ok'=>false,'error'=>"ID $fid exists"]);
    db()->query("INSERT INTO flight VALUES('$fid','$fd','$tm','$sc','$de')");
    audit('add_flight',"$fid $sc-$de");
    json_out(['ok'=>true,'msg'=>"Flight $fid added"]);

/* ---------------- UPDATE ---------------- */
case 'edit_passenger':
    admin_only();
    $pid=esc($_POST['pid']??''); $nm=esc(trim($_POST['pname']??'')); $g=esc($_POST['pgender']??'Male'); $ct=esc(trim($_POST['pcity']??''));
    if($nm===''||$ct==='') json_out(['ok'=>false,'error'=>'Name and city required']);
    db()->query("UPDATE passenger SET pname='$nm',pgender='$g',pcity='$ct' WHERE pid='$pid'");
    audit('edit_passenger',"$pid");
    json_out(['ok'=>true,'msg'=>"Passenger $pid updated"]);

case 'edit_agency':
    admin_only();
    $aid=esc($_POST['aid']??''); $nm=esc(trim($_POST['aname']??'')); $ct=esc(trim($_POST['acity']??''));
    if($nm===''||$ct==='') json_out(['ok'=>false,'error'=>'Name and city required']);
    db()->query("UPDATE agency SET aname='$nm',acity='$ct' WHERE aid='$aid'");
    audit('edit_agency',"$aid");
    json_out(['ok'=>true,'msg'=>"Agency $aid updated"]);

case 'edit_flight':
    admin_only();
    $fid=esc($_POST['fid']??''); $fd=esc(trim($_POST['fdate']??'')); $tm=esc(trim($_POST['time']??''));
    $sc=esc(trim($_POST['src']??'')); $de=esc(trim($_POST['dest']??''));
    if(!$fd||!$tm||!$sc||!$de) json_out(['ok'=>false,'error'=>'All fields required']);
    db()->query("UPDATE flight SET fdate='$fd',time='$tm',src='$sc',dest='$de' WHERE fid='$fid'");
    audit('edit_flight',"$fid");
    json_out(['ok'=>true,'msg'=>"Flight $fid updated"]);

/* ---------------- DELETE ---------------- */
case 'cancel':
    allow('admin','agent');
    $bid=(int)($_POST['bid']??0);
    db()->query("DELETE FROM booking WHERE bid=$bid");
    audit('cancel',"booking #$bid");
    json_out(['ok'=>true,'msg'=>'Booking cancelled']);

case 'del_passenger':
    admin_only(); $pid=esc($_POST['pid']??'');
    if(q_val("SELECT COUNT(*) FROM booking WHERE pid='$pid'")>0) json_out(['ok'=>false,'error'=>"Has bookings — cancel them first"]);
    db()->query("DELETE FROM passenger WHERE pid='$pid'"); audit('del_passenger',"$pid"); json_out(['ok'=>true,'msg'=>"Passenger $pid deleted"]);

case 'del_agency':
    admin_only(); $aid=esc($_POST['aid']??'');
    if(q_val("SELECT COUNT(*) FROM booking WHERE aid='$aid'")>0) json_out(['ok'=>false,'error'=>"Has bookings — cancel them first"]);
    db()->query("DELETE FROM agency WHERE aid='$aid'"); audit('del_agency',"$aid"); json_out(['ok'=>true,'msg'=>"Agency $aid deleted"]);

case 'del_flight':
    admin_only(); $fid=esc($_POST['fid']??'');
    if(q_val("SELECT COUNT(*) FROM booking WHERE fid='$fid'")>0) json_out(['ok'=>false,'error'=>"Has bookings — cancel them first"]);
    db()->query("DELETE FROM flight WHERE fid='$fid'"); audit('del_flight',"$fid"); json_out(['ok'=>true,'msg'=>"Flight $fid deleted"]);

/* ---------------- USERS ---------------- */
case 'users':
    admin_only();
    // ensure the pid column exists (older DBs may not have it) — safe to run repeatedly
    @db()->query("ALTER TABLE users ADD COLUMN pid VARCHAR(10) DEFAULT NULL");
    $rows = q_all("SELECT u.uid,u.username,u.role,u.pid,p.pname,u.created
        FROM users u LEFT JOIN passenger p ON u.pid=p.pid ORDER BY u.uid");
    if (isset($rows['__error'])) {
        // fallback if the join/column still fails — return users without passenger name
        $rows = q_all("SELECT uid,username,role,NULL AS pid,NULL AS pname,created FROM users ORDER BY uid");
    }
    json_out(['ok'=>true,'rows'=>$rows,'me'=>(int)$_SESSION['uid']]);

case 'add_user':
    admin_only();
    $u=trim($_POST['username']??''); $p=$_POST['password']??'';
    $r=in_array($_POST['role']??'',['admin','agent','customer'],true)?$_POST['role']:'customer';
    if($u===''||strlen($p)<4) json_out(['ok'=>false,'error'=>'Username + 4-char password required']);
    $ue=esc($u);
    if(q_val("SELECT COUNT(*) FROM users WHERE username='$ue'")>0) json_out(['ok'=>false,'error'=>"'$u' exists"]);
    // link a passenger (only meaningful for customers); validate it exists
    $pidIn = trim($_POST['pid'] ?? '');
    $pidSql = 'NULL';
    if ($r==='customer' && $pidIn!=='') {
        $pe = esc($pidIn);
        if (q_val("SELECT COUNT(*) FROM passenger WHERE pid='$pe'")==0) json_out(['ok'=>false,'error'=>"Passenger $pidIn not found"]);
        $pidSql = "'$pe'";
    }
    $hp=esc(password_hash($p,PASSWORD_DEFAULT));
    db()->query("INSERT INTO users (username,password,role,pid) VALUES ('$ue','$hp','$r',$pidSql)");
    audit('add_user',"$u ($r)".($pidSql!=='NULL'?" -> pid $pidIn":''));
    json_out(['ok'=>true,'msg'=>"User '$u' created as ".strtoupper($r)]);

case 'edit_user':
    admin_only();
    $uid=(int)($_POST['uid']??0);
    $r=in_array($_POST['role']??'',['admin','agent','customer'],true)?$_POST['role']:'customer';
    $pidIn=trim($_POST['pid']??'');
    $pidSql='NULL';
    if ($r==='customer' && $pidIn!=='') {
        $pe=esc($pidIn);
        if (q_val("SELECT COUNT(*) FROM passenger WHERE pid='$pe'")==0) json_out(['ok'=>false,'error'=>"Passenger $pidIn not found"]);
        $pidSql="'$pe'";
    }
    db()->query("UPDATE users SET role='$r', pid=$pidSql WHERE uid=$uid");
    // if editing yourself, refresh session so changes apply immediately
    if ($uid===(int)$_SESSION['uid']) { $_SESSION['role']=$r; $_SESSION['pid']=($pidSql==='NULL'?null:$pidIn); }
    audit('edit_user',"uid=$uid -> $r".($pidSql!=='NULL'?" pid $pidIn":''));
    json_out(['ok'=>true,'msg'=>'User updated']);

case 'reset_pw':
    admin_only(); $uid=(int)($_POST['uid']??0); $p=$_POST['password']??'';
    if(strlen($p)<4) json_out(['ok'=>false,'error'=>'Password too short']);
    $hp=esc(password_hash($p,PASSWORD_DEFAULT));
    db()->query("UPDATE users SET password='$hp' WHERE uid=$uid");
    audit('reset_pw',"uid=$uid");
    json_out(['ok'=>true,'msg'=>'Password updated']);

case 'del_user':
    admin_only(); $uid=(int)($_POST['uid']??0);
    if($uid===(int)$_SESSION['uid']) json_out(['ok'=>false,'error'=>'Cannot delete yourself']);
    if((int)q_val("SELECT COUNT(*) FROM users")<=1) json_out(['ok'=>false,'error'=>'Cannot delete last user']);
    db()->query("DELETE FROM users WHERE uid=$uid"); audit('del_user',"uid=$uid"); json_out(['ok'=>true,'msg'=>'User deleted']);

case 'reset_db':
    admin_only();
    db()->query("DROP DATABASE IF EXISTS ".DB_NAME);
    $sql=preg_replace('#/\*.*?\*/#s','',file_get_contents(__DIR__.'/database.sql'));
    db()->multi_query($sql);
    do{ if($r=db()->store_result())$r->free(); }while(db()->more_results()&&db()->next_result());
    db()->select_db(DB_NAME);
    audit('reset_db','restored sample data');
    json_out(['ok'=>true,'msg'=>'Database reset to sample data']);

/* ---------------- AUDIT LOGS (admin) ---------------- */
case 'audit':
    admin_only();
    json_out(['ok'=>true,'rows'=>q_all("SELECT lid,username,role,action,detail,ip,ts FROM audit_logs ORDER BY lid DESC LIMIT 200")]);

/* ---------------- FEEDBACK / NOTIFICATIONS (admin + agent) ---------------- */
case 'fb_count':            // for the bell badge — number of unread (new) messages
    allow('admin','agent');
    json_out(['ok'=>true,'unread'=>(int)q_val("SELECT COUNT(*) FROM feedback WHERE status='new'")]);

case 'fb_list':             // all feedback, newest first
    allow('admin','agent');
    json_out(['ok'=>true,
        'unread'=>(int)q_val("SELECT COUNT(*) FROM feedback WHERE status='new'"),
        'rows'=>q_all("SELECT fbid,name,email,message,reply,status,created,replied FROM feedback ORDER BY fbid DESC")]);

case 'fb_read_all':         // mark all 'new' as 'read' (clears the bell)
    allow('admin','agent');
    db()->query("UPDATE feedback SET status='read' WHERE status='new'");
    json_out(['ok'=>true,'msg'=>'Marked all as read']);

case 'fb_reply':            // save a reply to a feedback message
    allow('admin','agent');
    $id=(int)($_POST['fbid']??0); $rep=esc(trim($_POST['reply']??''));
    if($rep==='') json_out(['ok'=>false,'error'=>'Reply cannot be empty']);
    db()->query("UPDATE feedback SET reply='$rep',status='replied',replied=NOW() WHERE fbid=$id");
    audit('fb_reply',"feedback #$id");
    // return the customer's email + original message so the UI can open an email compose window
    $fb = q_one("SELECT name,email,message FROM feedback WHERE fbid=$id");
    json_out(['ok'=>true,'msg'=>'Reply saved','email'=>$fb['email']??'','name'=>$fb['name']??'','message'=>$fb['message']??'']);

case 'fb_delete':
    allow('admin','agent');
    $id=(int)($_POST['fbid']??0);
    db()->query("DELETE FROM feedback WHERE fbid=$id");
    audit('fb_delete',"feedback #$id");
    json_out(['ok'=>true,'msg'=>'Feedback deleted']);

/* ---------------- REPORTS (admin + agent) ---------------- */
case 'report':
    allow('admin','agent');
    json_out(['ok'=>true,
        'byDate'=>q_all("SELECT b.fdate, COUNT(*) c FROM booking b GROUP BY b.fdate ORDER BY b.fdate"),
        'byAgency'=>q_all("SELECT a.aname, COUNT(*) c FROM booking b JOIN agency a ON b.aid=a.aid GROUP BY a.aname ORDER BY c DESC"),
        'byRoute'=>q_all("SELECT CONCAT(f.src,' → ',f.dest) route, COUNT(*) c FROM booking b JOIN flight f ON b.fid=f.fid GROUP BY route ORDER BY c DESC"),
        'byGender'=>q_all("SELECT p.pgender, COUNT(*) c FROM booking b JOIN passenger p ON b.pid=p.pid GROUP BY p.pgender"),
        'totals'=>['bookings'=>(int)q_val("SELECT COUNT(*) FROM booking"),
            'passengers'=>(int)q_val("SELECT COUNT(DISTINCT pid) FROM booking"),
            'flights'=>(int)q_val("SELECT COUNT(DISTINCT fid) FROM booking"),
            'agencies'=>(int)q_val("SELECT COUNT(DISTINCT aid) FROM booking")],
    ]);

default:
    json_out(['ok'=>false,'error'=>'Unknown action']);
}
