<?php
/* =====================================================================
   ticket.php  -  Boarding pass (print-to-PDF, works on every browser)
   ?bid=<booking id>          -> shows a print-ready ticket, auto-opens
                                 the Print dialog ("Save as PDF").
   ?bid=<id>&raw=1            -> streams a binary .pdf download instead.
   ===================================================================== */
session_start();
require __DIR__ . '/config.php';
db();
require_login();

$bid = (int)($_GET['bid'] ?? 0);
$b = q_one("SELECT b.bid,b.pid,p.pname,p.pgender,a.aname,b.fid,f.src,f.dest,f.time,b.fdate,b.seat
            FROM booking b JOIN passenger p ON b.pid=p.pid
            JOIN agency a ON b.aid=a.aid JOIN flight f ON b.fid=f.fid
            WHERE b.bid=$bid");
if (!$b) { http_response_code(404); exit('Booking not found.'); }
// customers may only open their own boarding pass
if (($_SESSION['role'] ?? '')==='customer' && $b['pid'] !== ($_SESSION['pid'] ?? '__none__')) {
    http_response_code(403); exit('You can only download your own boarding pass.');
}
@audit('ticket_pdf', "booking #$bid");

/* ---------------------------------------------------------------------
   OPTIONAL: raw binary PDF download  (?raw=1)
   --------------------------------------------------------------------- */
if (!empty($_GET['raw'])) {
    while (ob_get_level()) { ob_end_clean(); }   // wipe any prior output
    $esc = fn($s)=>str_replace(['\\','(',')'],['\\\\','\\(','\\)'], (string)$s);
    $h=842; $w=595; $c='';
    $T=function($x,$y,$s,$sz=12,$f='F1',$col='0 0 0') use(&$c,$h,$esc){
        $c.="BT /$f $sz Tf $col rg ".sprintf('%.2f %.2f',$x,$h-$y)." Td (".$esc($s).") Tj ET\n"; };
    $R=function($x,$y,$rw,$rh,$col) use(&$c,$h){
        $c.="$col rg ".sprintf('%.2f %.2f %.2f %.2f',$x,$h-$y-$rh,$rw,$rh)." re f\n"; };
    $R(0,0,595,150,'0.32 0.30 0.86');
    $T(40,55,'AeroDesk',26,'F2','1 1 1'); $T(40,82,'BOARDING PASS',12,'F1','0.85 0.9 1');
    $T(420,55,'Seat',11,'F1','0.85 0.9 1'); $T(420,86,$b['seat'],28,'F2','1 1 1');
    $T(40,130,$b['src'].'  >  '.$b['dest'],20,'F2','1 1 1');
    $fld=function($x,$y,$k,$v) use($T){ $T($x,$y,strtoupper($k),9,'F1','0.45 0.45 0.55'); $T($x,$y+22,$v,15,'F2','0.06 0.08 0.18'); };
    $fld(40,210,'Passenger',$b['pname'].' ('.$b['pid'].')'); $fld(350,210,'Gender',$b['pgender']);
    $fld(40,265,'Flight',$b['fid']); $fld(180,265,'Date',$b['fdate']); $fld(350,265,'Time',$b['time']);
    $fld(40,320,'Agency',$b['aname']); $fld(350,320,'Booking #',(string)$b['bid']);
    $T(40,420,'AeroDesk e-Ticket  -  Generated '.date('Y-m-d H:i'),9,'F1','0.5 0.5 0.6');
    $objs=[1=>"<< /Type /Catalog /Pages 2 0 R >>",
        2=>"<< /Type /Pages /Kids [3 0 R] /Count 1 >>",
        3=>"<< /Type /Page /Parent 2 0 R /MediaBox [0 0 $w $h] /Resources << /Font << /F1 5 0 R /F2 6 0 R >> >> /Contents 4 0 R >>",
        4=>"<< /Length ".strlen($c)." >>\nstream\n".$c."endstream",
        5=>"<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>",
        6=>"<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>"];
    $pdf="%PDF-1.4\n"; $off=[];
    foreach($objs as $i=>$o){ $off[$i]=strlen($pdf); $pdf.="$i 0 obj\n$o\nendobj\n"; }
    $xref=strlen($pdf); $n=count($objs)+1;
    $pdf.="xref\n0 $n\n0000000000 65535 f \n";
    for($i=1;$i<$n;$i++) $pdf.=sprintf("%010d 00000 n \n",$off[$i]);
    $pdf.="trailer\n<< /Size $n /Root 1 0 R >>\nstartxref\n$xref\n%%EOF";
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="boarding_pass_'.$b['bid'].'_'.$b['seat'].'.pdf"');
    header('Content-Length: '.strlen($pdf));
    echo $pdf; exit;
}
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Boarding Pass · <?= h($b['pname']) ?> · <?= h($b['fid']) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<style>
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:Inter,system-ui,sans-serif;background:#0b1020;min-height:100vh;
    display:flex;flex-direction:column;align-items:center;justify-content:center;gap:22px;padding:24px;color:#fff}
  .toolbar{display:flex;gap:10px;flex-wrap:wrap;justify-content:center}
  .btn{cursor:pointer;border:none;border-radius:12px;padding:12px 22px;font-weight:700;font-size:14px;
    font-family:Sora,sans-serif;color:#06121f;background:linear-gradient(135deg,#22d3ee,#6d5efc);
    box-shadow:0 12px 30px rgba(34,211,238,.4);text-decoration:none;display:inline-flex;align-items:center;gap:8px}
  .btn.ghost{background:transparent;border:1px solid rgba(255,255,255,.3);color:#fff;box-shadow:none}
  .btn:hover{filter:brightness(1.08)}
  /* the ticket */
  .ticket{width:680px;max-width:96vw;border-radius:22px;overflow:hidden;background:#fff;color:#0d1430;
    box-shadow:0 30px 80px rgba(0,0,0,.5)}
  .tt{background:linear-gradient(120deg,#2563EB,#8B5CF6 55%,#06B6D4);color:#fff;padding:26px 30px}
  .tt .row1{display:flex;justify-content:space-between;align-items:center;font-size:12px;letter-spacing:1.5px;
    text-transform:uppercase;opacity:.92}
  .tt .big{display:flex;align-items:center;gap:18px;margin-top:14px}
  .tt .c{font-family:Sora;font-weight:800;font-size:34px;letter-spacing:1px}
  .tt .dash{flex:1;border-top:2px dashed rgba(255,255,255,.6);position:relative}
  .tt .dash span{position:absolute;right:-10px;top:-14px;font-size:20px}
  .tt .seat{font-family:Sora;font-weight:800;font-size:30px}
  .tb{padding:26px 30px;display:grid;grid-template-columns:repeat(3,1fr);gap:20px}
  .tb .k{font-size:10.5px;letter-spacing:1px;text-transform:uppercase;color:#7b85a8}
  .tb .v{font-family:Sora;font-weight:700;font-size:16px;margin-top:4px}
  .barcode{height:54px;margin:0 30px 26px;border-radius:6px;
    background:repeating-linear-gradient(90deg,#0d1430 0 2px,#fff 2px 5px)}
  .foot{padding:0 30px 22px;color:#7b85a8;font-size:12px;display:flex;justify-content:space-between}
  @media print{
    body{background:#fff;padding:0}
    .toolbar{display:none}
    .ticket{box-shadow:none;width:100%;max-width:100%;border-radius:0}
    @page{margin:14mm}
  }
</style></head>
<body>
  <div class="toolbar">
    <button class="btn" onclick="window.print()">⬇ Save / Print as PDF</button>
    <a class="btn ghost" href="ticket.php?bid=<?= (int)$b['bid'] ?>&raw=1">Download .pdf file</a>
    <a class="btn ghost" href="index.php">← Back to app</a>
  </div>

  <div class="ticket" id="ticket">
    <div class="tt">
      <div class="row1"><span>AeroDesk · Boarding Pass</span><span>e-Ticket #<?= h($b['bid']) ?></span></div>
      <div class="big">
        <span class="c"><?= h($b['src']) ?></span>
        <span class="dash"><span>✈</span></span>
        <span class="c"><?= h($b['dest']) ?></span>
        <span style="margin-left:auto;text-align:right"><div class="row1" style="opacity:.85">Seat</div><div class="seat"><?= h($b['seat']) ?></div></span>
      </div>
    </div>
    <div class="tb">
      <div><div class="k">Passenger</div><div class="v"><?= h($b['pname']) ?></div></div>
      <div><div class="k">Passenger ID</div><div class="v"><?= h($b['pid']) ?></div></div>
      <div><div class="k">Gender</div><div class="v"><?= h($b['pgender']) ?></div></div>
      <div><div class="k">Flight</div><div class="v"><?= h($b['fid']) ?></div></div>
      <div><div class="k">Date</div><div class="v"><?= h($b['fdate']) ?></div></div>
      <div><div class="k">Time</div><div class="v"><?= h($b['time']) ?></div></div>
      <div><div class="k">Agency</div><div class="v"><?= h($b['aname']) ?></div></div>
      <div><div class="k">From</div><div class="v"><?= h($b['src']) ?></div></div>
      <div><div class="k">To</div><div class="v"><?= h($b['dest']) ?></div></div>
    </div>
    <div class="barcode"></div>
    <div class="foot"><span>AeroDesk e-Ticket</span><span>Generated <?= date('Y-m-d H:i') ?></span></div>
  </div>

  <script>
    // auto-open the print dialog so the user can "Save as PDF" in one step
    window.addEventListener('load', function(){ setTimeout(function(){ window.print(); }, 350); });
  </script>
</body></html>
