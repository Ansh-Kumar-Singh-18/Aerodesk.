/* =====================================================================
   AeroDesk · app.js  — SPA controller (vanilla JS + fetch API)
   ===================================================================== */
/* Surface any JS error on screen instead of leaving a blank page. */
window.addEventListener('error', function(e){
  var v = document.getElementById('views');
  if (v) v.innerHTML = '<div class="card" style="margin:30px;color:#fda4af">'
    + '<b>JavaScript error:</b><br>' + (e.message||e.error) + '<br><small style="color:#93a0c8">'
    + (e.filename||'') + ' : line ' + (e.lineno||'?') + '</small></div>';
});
window.addEventListener('unhandledrejection', function(e){
  var v = document.getElementById('views');
  if (v) v.innerHTML = '<div class="card" style="margin:30px;color:#fda4af">'
    + '<b>Request failed:</b><br>' + (e.reason && e.reason.message ? e.reason.message : e.reason) + '</div>';
});
const API = 'api.php';
const $ = (s,el=document)=>el.querySelector(s);
const $$ = (s,el=document)=>[...el.querySelectorAll(s)];
const ROLE = AERO.role;
const isAdmin = ROLE === 'admin';
const isAgent = ROLE === 'agent';
const isCustomer = ROLE === 'customer';
const can = (...roles)=>roles.includes(ROLE);
// hide nav items the current role can't use
/* nav items are already filtered by role server-side (index.php).
   Extra safety: if any role-restricted item slipped through, remove it. */
document.querySelectorAll('.nav[data-role]').forEach(n=>{
  if(!n.dataset.role.split(',').includes(ROLE)) n.remove();
});
const PALETTE = ['#2563EB','#06B6D4','#8B5CF6','#10B981','#EF4444','#60a5fa','#0ea5e9','#a78bfa'];

/* ---------- helpers ---------- */
async function api(action, params={}, method='GET'){
  let url = API + '?action=' + encodeURIComponent(action);
  let opt = {method};
  if(method==='GET'){ for(const k in params) url += `&${k}=${encodeURIComponent(params[k])}`; }
  else { const fd=new FormData(); for(const k in params) fd.append(k,params[k]); opt.body=fd; }
  const r = await fetch(url,opt); return r.json();
}
/* Force a real file download (fetch -> Blob -> save), avoids opening in-tab */
async function downloadFile(url, filename){
  try{
    const res = await fetch(url, {credentials:'same-origin'});
    if(!res.ok){ toast('Download failed ('+res.status+')', false); return; }
    const blob = await res.blob();
    const a = document.createElement('a');
    const obj = URL.createObjectURL(blob);
    a.href = obj; a.download = filename;
    document.body.appendChild(a); a.click(); a.remove();
    setTimeout(()=>URL.revokeObjectURL(obj), 1500);
    toast('Downloaded '+filename);
  }catch(e){ toast('Download error: '+e.message, false); }
}
const esc = s => String(s??'').replace(/[&<>"]/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]));
function toast(msg, ok=true){
  const t=document.createElement('div'); t.className='toast '+(ok?'ok':'err');
  t.innerHTML=`<svg viewBox="0 0 24 24">${ok?'<path d="M20 6 9 17l-5-5"/>':'<path d="M18 6 6 18M6 6l12 12"/>'}</svg><span>${esc(msg)}</span>`;
  $('#toasts').appendChild(t); setTimeout(()=>{t.style.opacity='0';t.style.transform='translateX(40px)';setTimeout(()=>t.remove(),300);},2800);
}
function genderTag(g){return `<span class="tag ${g==='Male'?'m':'f'}">${esc(g)}</span>`;}
function routeCell(s,d){return `<span class="route">${esc(s)} <span class="arr">✈</span> ${esc(d)}</span>`;}
function tableHTML(rows, cols){
  if(rows && rows.__error) return '<div class="empty" style="color:#fda4af">Error: '+esc(rows.__error)+'</div>';
  if(!rows||!rows.length) return '<div class="empty">No records found.</div>';
  const c = cols || Object.keys(rows[0]).map(k=>({k,label:k.toUpperCase()}));
  let h='<div class="tbl-wrap"><table><thead><tr>'+c.map(x=>`<th>${esc(x.label)}</th>`).join('')+'</tr></thead><tbody>';
  rows.forEach(r=>{ h+='<tr>'+c.map(x=> '<td>'+(x.render?x.render(r):esc(r[x.k]))+'</td>').join('')+'</tr>'; });
  return h+'</tbody></table></div>';
}
function animateNum(el,to){const d=1100,t0=performance.now();to=+to||0;
  // easeOutExpo for a snappy, premium count-up
  (function go(t){let p=Math.min(1,(t-t0)/d);
    let e=p===1?1:1-Math.pow(2,-10*p);
    el.textContent=Math.round(e*to).toLocaleString();
    if(p<1)requestAnimationFrame(go);})(performance.now());}
// reusable loading skeleton
function skeleton(kind){
  if(kind==='stats') return `<div class="skel-grid cols-4" style="margin-bottom:18px">${'<div class="card skel skel-card"></div>'.repeat(4)}</div>
    <div class="skel-grid split">${'<div class="card skel" style="height:240px"></div>'.repeat(2)}</div>`;
  if(kind==='table') return `<div class="card"><div class="skel-line w40"></div>${'<div class="skel-line"></div>'.repeat(6)}</div>`;
  if(kind==='cards') return `<div class="skel-grid cols-3">${'<div class="card skel" style="height:180px"></div>'.repeat(3)}</div>`;
  return '<div class="card skel"></div>';
}
function bars(rows,labelKey,valKey,color){
  if(!rows.length) return '<div class="empty">No data.</div>';
  const max=Math.max(...rows.map(r=>+r[valKey]),1);
  return '<div class="bars">'+rows.map((r,i)=>{
    const col = color || PALETTE[i%PALETTE.length];
    return `<div class="bar-row"><div class="bl">${esc(r[labelKey])}</div>
      <div class="bar-track"><div class="bar-fill" data-w="${(+r[valKey]/max*100)}" style="background:linear-gradient(90deg,${col},${col}aa)">${r[valKey]}</div></div></div>`;
  }).join('')+'</div>';
}
function runBars(scope){ $$('.bar-fill',scope).forEach(b=>{ requestAnimationFrame(()=>b.style.width=b.dataset.w+'%'); }); }
function donut(rows){
  if(!rows.length) return '<div class="empty">No data.</div>';
  const total=rows.reduce((a,r)=>a+ +r.c,0)||1; let acc=0; const segs=[];
  rows.forEach((r,i)=>{const frac=+r.c/total;const col=PALETTE[i%PALETTE.length];
    segs.push(`${col} ${(acc*100).toFixed(2)}% ${((acc+frac)*100).toFixed(2)}%`);acc+=frac;});
  const leg=rows.map((r,i)=>`<div><i style="background:${PALETTE[i%PALETTE.length]}"></i>${esc(r.dest||r.aname||r.src)} · <b>${r.c}</b></div>`).join('');
  return `<div class="donut-wrap"><div style="width:140px;height:140px;border-radius:50%;
    background:conic-gradient(${segs.join(',')});mask:radial-gradient(circle 42px at center,transparent 98%,#000 100%);
    -webkit-mask:radial-gradient(circle 42px at center,transparent 98%,#000 100%)"></div>
    <div class="legend">${leg}</div></div>`;
}
function hsql(s){return esc(s)
  .replace(/('[^']*')/g,'<span class="str">$1</span>')
  .replace(/\b(SELECT|FROM|WHERE|AND|OR|JOIN|ON|GROUP BY|ORDER BY|HAVING|DISTINCT|NOT IN|IN|AS|LIMIT|DESC|ASC|INTERVAL|DAY|UNION)\b/g,'<span class="kw">$1</span>')
  .replace(/\b(COUNT|AVG|DATE_ADD|MAX|MIN|SUM)\b/g,'<span class="fn">$1</span>');}

/* ---------- modal ---------- */
function openModal(html){const m=$('#modal');$('#modalBody').innerHTML=html;m.classList.add('show');}
function closeModal(){$('#modal').classList.remove('show');}
$('#modal').addEventListener('click',e=>{if(e.target.id==='modal')closeModal();});

/* ---------- router ---------- */
const VIEWS={};
let CACHE={lists:null};
async function lists(force){ if(!CACHE.lists||force) CACHE.lists=await api('lists'); return CACHE.lists; }

const SKEL_FOR={dashboard:'stats',search:'cards',records:'cards',bookings:'table',data:'table',users:'table',sql:'table',about:'stats',feedback:'table',audit:'table',reports:'stats'};
function setView(name){
  $$('.nav').forEach(n=>n.classList.toggle('active',n.dataset.view===name));
  $('#crumbView').textContent = $(`.nav[data-view="${name}"] span`).textContent;
  const host=$('#views'); host.innerHTML=skeleton(SKEL_FOR[name]||'card');
  VIEWS[name](host);
}
$$('.nav').forEach(n=>n.addEventListener('click',()=>setView(n.dataset.view)));

/* ============================ DASHBOARD ============================ */
VIEWS.dashboard = async (host)=>{
  const s = await api('stats');
  const c = s.counts;
  const hero = `
    <div class="hero">
      <div class="plane"><svg viewBox="0 0 24 24"><path d="M17.8 19.2 16 11l3.5-3.5C21 6 21.5 4 21 3c-1-.5-3 0-4.5 1.5L13 8 4.8 6.2c-.5-.1-.9.1-1.1.5l-.3.5c-.2.5-.1 1 .3 1.3L9 12l-2 3H4l-1 1 3 2 2 3 1-1v-3l3-2 3.5 5.3c.3.4.8.5 1.3.3l.5-.2c.4-.3.6-.7.5-1.2z"/></svg></div>
      <span class="eyebrow">${ROLE==='admin'?'Admin Dashboard':ROLE==='agent'?'Agent Dashboard':'Customer Portal'}</span>
      <h2>Welcome back, ${esc(AERO.me)} 👋</h2>
      <p>${ROLE==='admin'?'Full control over passengers, agencies, flights, bookings, users and audit logs.':ROLE==='agent'?'Book &amp; manage tickets, run reports and export data for your passengers.':'Search flights, view your bookings and download your boarding passes.'}</p>
      <p style="margin-top:10px;font-family:Sora;font-weight:700;letter-spacing:.4px">
        ✨ Designed by <span style="color:#fff">ANSH KUMAR SINGH</span></p>
      <button class="hero-cta" id="goSearch">Book a flight →</button>
    </div>`;

  /* ----- CUSTOMER PORTAL: simple, focused on what they can do ----- */
  if (ROLE === 'customer') {
    host.innerHTML = `<div class="view">${hero}
      <div class="grid cols-3">
        ${statCard('Flights Available',c.flight,'routes you can book','#8B5CF6','<path d="M17.8 19.2 16 11l3.5-3.5C21 6 21.5 4 21 3c-1-.5-3 0-4.5 1.5L13 8 4.8 6.2"/>')}
        ${statCard('Destinations',(s.byDest||[]).length,'places to fly','#06B6D4','<path d="M21 10c0 7-9 12-9 12s-9-5-9-12a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>')}
        ${statCard('My Bookings',(c.myBooking??0),'your tickets','#10B981','<rect x="3" y="4" width="18" height="16" rx="2"/><path d="M3 10h18"/>')}
      </div>
      <div class="grid cols-3" style="margin-top:18px">
        ${quickCard('Search &amp; Book','Find a flight by date and time','search','#2563EB','<circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/>')}
        ${quickCard('My Bookings','View tickets &amp; download boarding pass','bookings','#06B6D4','<path d="M4 4h16v6a2 2 0 0 0 0 4v6H4z"/>')}
        ${quickCard('About','Learn more about AeroDesk','about','#8B5CF6','<circle cx="12" cy="12" r="9"/><path d="M12 16v-4M12 8h.01"/>')}
      </div>
      <div class="card" style="margin-top:18px"><div class="sec-title"><span class="pip"></span>Popular Destinations</div>${donut(s.byDest)}</div>
    </div>`;
    wireDash(host);
    $$('[data-go]',host).forEach(b=>b.onclick=()=>setView(b.dataset.go));
    return;
  }

  /* ----- ADMIN & AGENT: full operational analytics ----- */
  const statsRow = ROLE==='admin' ? `
    <div class="grid cols-4">
      ${statCard('Passengers',c.passenger,'registered travelers','#2563EB','<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/>')}
      ${statCard('Agencies',c.agency,'travel partners','#06B6D4','<path d="M3 21V8l9-5 9 5v13"/><path d="M9 21v-6h6v6"/>')}
      ${statCard('Flights',c.flight,'scheduled routes','#8B5CF6','<path d="M17.8 19.2 16 11l3.5-3.5C21 6 21.5 4 21 3c-1-.5-3 0-4.5 1.5L13 8 4.8 6.2"/>')}
      ${statCard('Bookings',c.booking,'tickets issued','#10B981','<rect x="3" y="4" width="18" height="16" rx="2"/><path d="M3 10h18"/>')}
    </div>` : `
    <div class="grid cols-3">
      ${statCard('Flights',c.flight,'scheduled routes','#8B5CF6','<path d="M17.8 19.2 16 11l3.5-3.5C21 6 21.5 4 21 3c-1-.5-3 0-4.5 1.5L13 8 4.8 6.2"/>')}
      ${statCard('Bookings',c.booking,'tickets issued','#10B981','<rect x="3" y="4" width="18" height="16" rx="2"/><path d="M3 10h18"/>')}
      ${statCard('Passengers',c.passenger,'registered travelers','#2563EB','<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/>')}
    </div>`;
  host.innerHTML = `<div class="view">${hero}
    ${statsRow}
    <div class="grid split" style="margin-top:18px">
      <div class="card"><div class="sec-title"><span class="pip"></span>Recent Bookings</div>
        ${tableHTML(s.recent,[
          {k:'pname',label:'Passenger'},{k:'aname',label:'Agency'},
          {k:'route',label:'Route',render:r=>routeCell(r.src,r.dest)},
          {k:'seat',label:'Seat'},{k:'fdate',label:'Date'}])}
      </div>
      <div class="card"><div class="sec-title"><span class="pip" style="background:var(--g3)"></span>Bookings by Destination</div>
        ${donut(s.byDest)}
      </div>
    </div>
    <div class="grid cols-2" style="margin-top:18px">
      <div class="card"><div class="sec-title"><span class="pip"></span>Agency Performance</div><div id="agc">${bars(s.byAgency,'aname','c')}</div></div>
      <div class="card"><div class="sec-title"><span class="pip" style="background:var(--g3)"></span>Passengers by Source City</div><div id="src">${bars(s.bySrc,'src','c','#06B6D4')}</div></div>
    </div>
  </div>`;
  wireDash(host);
};
function wireDash(host){
  $$('.stat',host).forEach((card,i)=>{ card.style.animationDelay=(i*0.08)+'s'; card.classList.add('pop'); });
  $$('.num[data-to]',host).forEach((el,i)=>setTimeout(()=>animateNum(el,+el.dataset.to), i*80));
  runBars(host);
  if($('#goSearch')) $('#goSearch').onclick=()=>setView('search');
}
function quickCard(title,desc,view,color,ic){
  return `<div class="card" data-go="${view}" style="cursor:pointer">
    <div class="ic" style="width:46px;height:46px;border-radius:13px;display:grid;place-items:center;margin-bottom:12px;background:linear-gradient(135deg,${color},${color}99)">
      <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" width="22" height="22">${ic}</svg></div>
    <div class="font-d" style="font-weight:700;font-size:16px">${title}</div>
    <div class="muted" style="font-size:13px;margin-top:4px">${desc}</div></div>`;
}
function statCard(label,val,sub,color,ic){
  return `<div class="card stat">
    <div class="top"><span class="label">${label}</span>
      <span class="ic" style="background:linear-gradient(135deg,${color},${color}99)"><svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2">${ic}</svg></span></div>
    <div class="num" data-to="${val}">0</div><div class="sub">${sub}</div></div>`;
}

/* ============================ SEARCH & BOOK ============================ */
VIEWS.search = async (host)=>{
  const L = await lists();
  const cityOpts = ['<option value="">Any</option>'].concat(L.cities.map(c=>`<option>${esc(c.city)}</option>`)).join('');
  const fdates = [...new Set(L.flights.map(f=>f.fdate))].sort();
  const minD = fdates[0]||'', maxD = fdates[fdates.length-1]||'';
  host.innerHTML = `<div class="view">
    <div class="page-head"><span class="eyebrow">Find a flight</span><h2>Search &amp; Book</h2>
      <p>Pick a date from the calendar and enter a departure time, then issue a boarding pass in one click.</p></div>
    <div class="card">
      <div class="row">
        <div><label>From</label><select id="fSrc">${cityOpts}</select></div>
        <div><label>To</label><select id="fDest">${cityOpts}</select></div>
        <div><label>Date 📅</label><input id="fDate" type="date" ${minD?`min="${minD}" max="${maxD}"`:''}></div>
        <div><label>Departure Time 🕑</label><input id="fTime" type="time" step="60"></div>
        <div style="display:flex;align-items:flex-end;gap:8px">
          <button class="btn" style="flex:1" id="doSearch">Search</button>
          <button class="btn ghost" id="clearSearch" title="Clear filters">Clear</button>
        </div>
      </div>
      <div class="muted" style="font-size:12px;margin-top:10px">Available dates: <b style="color:var(--ink)">${minD||'—'}</b> to <b style="color:var(--ink)">${maxD||'—'}</b>. Leave fields blank to see all flights.</div>
    </div>
    <div id="sresults" style="margin-top:18px"></div>
  </div>`;
  const run = async ()=>{
    const res = await api('search',{src:$('#fSrc').value,dest:$('#fDest').value,fdate:$('#fDate').value,time:$('#fTime').value});
    const box = $('#sresults');
    if(!res.rows.length){ box.innerHTML='<div class="card empty">No flights match your search. Try clearing the time or date.</div>'; return; }
    box.innerHTML = `<div class="grid cols-2">`+res.rows.map(f=>`
      <div class="bp">
        <div class="main-s">
          <div class="fly"><span class="city">${esc(f.src)}</span>
            <span class="line"><svg viewBox="0 0 24 24"><path d="M2 12h18M14 6l6 6-6 6"/></svg></span>
            <span class="city">${esc(f.dest)}</span></div>
          <div class="det"><span>📅 ${f.fdate}</span><span>🕑 ${String(f.time).slice(0,5)}</span></div>
        </div>
        <div class="stub"><span class="lbl">Flight</span><span class="fid">${esc(f.fid)}</span>
          <button class="btn sm" style="margin-top:6px" data-book="${esc(f.fid)}">Book</button></div>
      </div>`).join('')+`</div>`;
    $$('[data-book]',box).forEach(b=>b.onclick=()=>bookModal(b.dataset.book));
  };
  $('#doSearch').onclick=run;
  $('#clearSearch').onclick=()=>{ $('#fSrc').value=''; $('#fDest').value=''; $('#fDate').value=''; $('#fTime').value=''; run(); };
  // live search when date/time change
  $('#fDate').onchange=run; $('#fTime').onchange=run;
  run();
};

async function bookModal(fid){
  const L = await lists();
  const f = L.flights.find(x=>x.fid===fid)||{};
  // customers can only book for their own linked passenger
  let paxList = L.passengers;
  if (isCustomer) {
    paxList = L.passengers.filter(p=>String(p.pid)===String(AERO.pid));
    if (!paxList.length) {
      openModal(`<div class="card" style="position:relative">
        <span class="modal-x" onclick="closeModal()" style="color:var(--ink)">×</span>
        <div class="sec-title"><span class="pip"></span>Cannot book</div>
        <p class="muted">Your account isn't linked to a passenger yet. Please ask an admin to link your account (Manage Users → Edit).</p>
      </div>`);
      return;
    }
  }
  const lockPax = isCustomer;   // customer can't change the passenger
  const pax = paxList.map(p=>`<option value="${p.pid}">${p.pid} · ${esc(p.pname)}</option>`).join('');
  const ag = L.agencies.map(a=>`<option value="${a.aid}">${a.aid} · ${esc(a.aname)}</option>`).join('');
  openModal(`<div style="position:relative" class="card">
    <span class="modal-x" onclick="closeModal()" style="color:var(--ink)">×</span>
    <div class="sec-title"><span class="pip"></span>Book flight ${esc(fid)} — ${esc(f.src)} → ${esc(f.dest)}</div>
    <div class="muted" style="margin:-6px 0 10px;font-size:13px">📅 ${esc(f.fdate)} &nbsp;·&nbsp; 🕑 Departs ${esc(String(f.time).slice(0,5))}</div>
    <label>Passenger</label><select id="bPid" ${lockPax?'disabled':''}>${pax}</select>
    <label>Agency</label><select id="bAid">${ag}</select>
    <label>Seat <span style="opacity:.6">(blank = auto-assign)</span></label><input id="bSeat" placeholder="e.g. 12A">
    <button class="btn full" id="bGo" style="margin-top:18px">Issue Boarding Pass</button>
  </div>`);
  $('#bGo').onclick=async()=>{
    $('#bGo').disabled=true;
    const pid = isCustomer ? AERO.pid : $('#bPid').value;
    const r=await api('book',{pid,aid:$('#bAid').value,fid,seat:$('#bSeat').value},'POST');
    if(r.ok){ const p=paxList.find(x=>String(x.pid)===String(pid))||L.passengers.find(x=>String(x.pid)===String(pid))||{pname:''}, a=L.agencies.find(x=>x.aid===$('#bAid').value);
      ticketModal({bid:r.bid,pname:p.pname,aname:a.aname,fid,src:f.src,dest:f.dest,fdate:f.fdate,time:f.time,seat:r.seat});
      toast(r.msg); }
    else { toast(r.error,false); $('#bGo').disabled=false; }
  };
}
function ticketModal(t){
  openModal(`<div class="ticket" style="position:relative">
    <span class="modal-x" onclick="closeModal()">×</span>
    <div class="tt"><div class="row1"><span>AeroDesk · Boarding Pass</span><span>Seat ${esc(t.seat)}</span></div>
      <div class="big"><span class="c">${esc(t.src)}</span>
        <span class="dash"><svg viewBox="0 0 24 24"><path d="M2 12h18M14 6l6 6-6 6"/></svg></span>
        <span class="c">${esc(t.dest)}</span></div></div>
    <div class="tb">
      <div><div class="k">Passenger</div><div class="v">${esc(t.pname)}</div></div>
      <div><div class="k">Flight</div><div class="v">${esc(t.fid)}</div></div>
      <div><div class="k">Seat</div><div class="v">${esc(t.seat)}</div></div>
      <div><div class="k">Date</div><div class="v">${esc(t.fdate)}</div></div>
      <div><div class="k">Time</div><div class="v">${esc(t.time)}</div></div>
      <div><div class="k">Agency</div><div class="v">${esc(t.aname)}</div></div>
    </div>
    <div class="barcode"></div>
    ${t.bid?`<div style="padding:0 24px 22px"><a class="btn full" href="ticket.php?bid=${t.bid}" target="_blank">⬇ Download Boarding Pass (PDF)</a></div>`:''}
  </div>`);
  CACHE.lists=null; // booking count changed
}

/* ============================ BOOKINGS ============================ */
VIEWS.bookings = async (host)=>{
  const L = await lists(true);
  const res = await api('bookings');
  // customers can only book for themselves (their linked passenger)
  const myPax = isCustomer ? L.passengers.filter(p=>String(p.pid)===String(AERO.pid)) : L.passengers;
  const paxData = (myPax.length?myPax:L.passengers);
  const pax = paxData.map(p=>`<option value="${p.pid}">${p.pid} · ${esc(p.pname)}</option>`).join('');
  const noLink = isCustomer && !myPax.length;
  const ag = L.agencies.map(a=>`<option value="${a.aid}">${a.aid} · ${esc(a.aname)}</option>`).join('');
  const fl = L.flights.map(f=>`<option value="${f.fid}">${f.fid} · ${esc(f.src)}→${esc(f.dest)} (${f.fdate})</option>`).join('');
  host.innerHTML=`<div class="view">
    <div class="page-head"><span class="eyebrow">Tickets</span><h2>Bookings</h2><p>Create or cancel tickets. Issued as boarding passes.</p></div>
    <div class="card"><div class="sec-title"><span class="pip"></span>New Booking</div>
      ${noLink?'<p class="muted">Your account isn\'t linked to a passenger yet. Ask an admin to link it (Manage Users → Edit).</p>':`
      <div class="row">
        <div><label>Passenger</label><select id="nPid" ${isCustomer?'disabled':''}>${pax}</select></div>
        <div><label>Agency</label><select id="nAid">${ag}</select></div>
        <div><label>Flight</label><select id="nFid">${fl}</select></div>
        <div style="display:flex;align-items:flex-end"><button class="btn full" id="nGo">Book Ticket</button></div>
      </div>`}
    </div>
    <div class="card" style="margin-top:18px"><div class="sec-title"><span class="pip" style="background:var(--g3)"></span>Booking Records <span style="color:var(--muted);font-weight:400;font-size:12px">(${res.rows.length})</span></div>
      <div id="btbl"></div></div>
  </div>`;
  const draw = (rows)=>{ $('#btbl').innerHTML = tableHTML(rows,[
    {k:'pid',label:'PID'},{k:'pname',label:'Passenger'},{k:'aname',label:'Agency'},{k:'fid',label:'Flight'},
    {k:'route',label:'Route',render:r=>routeCell(r.src,r.dest)},{k:'seat',label:'Seat'},{k:'fdate',label:'Date'},
    {k:'x',label:'',render:r=>`<div class="acts">
        <a class="btn sm" href="ticket.php?bid=${r.bid}" target="_blank" title="Download boarding pass">⬇ PDF</a>
        ${can('admin','agent')?`<button class="btn danger ghost sm" data-cancel="${r.bid}">Cancel</button>`:''}
      </div>`}]);
    $$('[data-cancel]').forEach(b=>b.onclick=async()=>{ const r=await api('cancel',{bid:b.dataset.cancel},'POST');
      toast(r.msg,r.ok); if(r.ok){CACHE.lists=null;VIEWS.bookings(host);} }); };
  draw(res.rows);
  if($('#nGo')) $('#nGo').onclick=async()=>{
    const pid = isCustomer ? AERO.pid : $('#nPid').value;
    const r=await api('book',{pid,aid:$('#nAid').value,fid:$('#nFid').value},'POST');
    toast(r.ok?r.msg:r.error,r.ok); if(r.ok){CACHE.lists=null;VIEWS.bookings(host);} };
};

/* ============================ ADD RECORDS ============================ */
VIEWS.records = async (host)=>{
  if(!isAdmin){ host.innerHTML=lockView(); return; }
  const L = await lists(true);
  const cityList = `<datalist id="cl">${L.cities.map(c=>`<option value="${esc(c.city)}">`).join('')}</datalist>`;
  host.innerHTML=`<div class="view">
    <div class="page-head"><span class="eyebrow">Admin</span><h2>Add Records</h2><p>Insert passengers, agencies and flights straight into MySQL.</p></div>
    <div class="grid cols-3">
      <div class="card"><div class="sec-title"><span class="pip"></span>Add Passenger</div>
        <label>ID</label><input id="pPid" placeholder="auto: ${L.nextP}">
        <label>Full name</label><input id="pName" placeholder="e.g. Kiran Rao">
        <label>Gender</label><select id="pGen"><option>Male</option><option>Female</option></select>
        <label>City</label><input id="pCity" list="cl" placeholder="e.g. Pune">
        <button class="btn full" id="pAdd" style="margin-top:16px">+ Add Passenger</button></div>

      <div class="card"><div class="sec-title"><span class="pip" style="background:var(--g2)"></span>Add Agency</div>
        <label>ID</label><input id="aAid" placeholder="auto: ${L.nextA}">
        <label>Agency name</label><input id="aName" placeholder="e.g. AirAsia">
        <label>City</label><input id="aCity" list="cl" placeholder="e.g. Pune">
        <button class="btn full" id="aAdd" style="margin-top:16px">+ Add Agency</button></div>

      <div class="card"><div class="sec-title"><span class="pip" style="background:var(--g3)"></span>Add Flight</div>
        <label>ID</label><input id="fFid" placeholder="auto: ${L.nextF}">
        <label>Date</label><input id="fDate" type="date">
        <label>Time</label><input id="fTime" type="time">
        <label>Source</label><input id="fSrc" list="cl" placeholder="e.g. Pune">
        <label>Destination</label><input id="fDest" list="cl" placeholder="e.g. Goa">
        <button class="btn full" id="fAdd" style="margin-top:16px">+ Add Flight</button></div>
    </div>${cityList}
  </div>`;
  const refresh=()=>{CACHE.lists=null;};
  $('#pAdd').onclick=async()=>{const r=await api('add_passenger',{pid:$('#pPid').value,pname:$('#pName').value,pgender:$('#pGen').value,pcity:$('#pCity').value},'POST');toast(r.ok?r.msg:r.error,r.ok);if(r.ok){refresh();VIEWS.records(host);}};
  $('#aAdd').onclick=async()=>{const r=await api('add_agency',{aid:$('#aAid').value,aname:$('#aName').value,acity:$('#aCity').value},'POST');toast(r.ok?r.msg:r.error,r.ok);if(r.ok){refresh();VIEWS.records(host);}};
  $('#fAdd').onclick=async()=>{const r=await api('add_flight',{fid:$('#fFid').value,fdate:$('#fDate').value,time:$('#fTime').value,src:$('#fSrc').value,dest:$('#fDest').value},'POST');toast(r.ok?r.msg:r.error,r.ok);if(r.ok){refresh();VIEWS.records(host);}};
};

/* ============================ DATABASE (CRUD) ============================ */
let DATA_TAB='passenger', EDIT_ID=null;
VIEWS.data = async (host)=>{
  if(!can('admin','agent')){ host.innerHTML=lockView(); return; }
  host.innerHTML=`<div class="view">
    <div class="page-head"><span class="eyebrow">Live data</span><h2>Database Browser</h2><p>Browse, edit and delete records in real time.</p></div>
    <div class="pills" id="dpills">
      ${['passenger','agency','flight','booking'].map(t=>`<button class="pill ${t===DATA_TAB?'active':''}" data-t="${t}">${t[0].toUpperCase()+t.slice(1)}</button>`).join('')}
      ${isAdmin?`<button class="btn sm" id="addHere" style="margin-left:auto">+ Add</button>`:''}
      ${isAdmin?`<button class="pill" id="resetData">↺ Reset sample data</button>`:''}
    </div>
    <div class="card" id="dbox"></div>${'<datalist id="cl"></datalist>'}
  </div>`;
  $$('#dpills .pill[data-t]').forEach(p=>p.onclick=()=>{DATA_TAB=p.dataset.t;EDIT_ID=null;VIEWS.data(host);});
  if(isAdmin && $('#addHere')) $('#addHere').onclick=()=>addRecordModal(DATA_TAB,host);
  if(isAdmin) $('#resetData').onclick=async()=>{ if(!confirm('Reset the whole database to original sample data?'))return;
    const r=await api('reset_db',{},'POST'); toast(r.msg,r.ok); CACHE.lists=null; EDIT_ID=null; VIEWS.data(host); };
  const L=await lists(); $('#cl').innerHTML=L.cities.map(c=>`<option value="${esc(c.city)}">`).join('');
  await drawData();
};
async function drawData(){
  const res=await api('table',{t:DATA_TAB}); const box=$('#dbox'); if(!box)return;
  if(DATA_TAB==='booking'){ box.innerHTML=tableHTML(res.rows,[
    {k:'bid',label:'#'},{k:'pid',label:'PID'},{k:'pname',label:'Passenger'},{k:'aname',label:'Agency'},
    {k:'fid',label:'Flight'},{k:'route',label:'Route',render:r=>routeCell(r.src,r.dest)},{k:'seat',label:'Seat'},{k:'fdate',label:'Date'}])
    + `<p class="note">Bookings are created / cancelled in the Bookings tab.</p>`; return; }

  const rows=res.rows; const adminCol = isAdmin;
  let cols, head;
  if(DATA_TAB==='passenger'){ head=['PID','Name','Gender','City']; }
  else if(DATA_TAB==='agency'){ head=['AID','Agency','City']; }
  else { head=['FID','Date','Time','Source','Destination']; }
  let h='<div class="tbl-wrap"><table><thead><tr>'+head.map(x=>`<th>${x}</th>`).join('')+(adminCol?'<th>Actions</th>':'')+'</tr></thead><tbody>';
  rows.forEach(r=>{
    const id = r.pid||r.aid||r.fid;
    if(adminCol && EDIT_ID===id){ h+=editRow(r); return; }
    if(DATA_TAB==='passenger') h+=`<tr><td>${esc(r.pid)}</td><td>${esc(r.pname)}</td><td>${genderTag(r.pgender)}</td><td>${esc(r.pcity)}</td>`;
    else if(DATA_TAB==='agency') h+=`<tr><td>${esc(r.aid)}</td><td>${esc(r.aname)}</td><td>${esc(r.acity)}</td>`;
    else h+=`<tr><td>${esc(r.fid)}</td><td>${esc(r.fdate)}</td><td>${esc(r.time)}</td><td>${esc(r.src)}</td><td>${esc(r.dest)}</td>`;
    if(adminCol) h+=`<td class="acts"><button class="btn ghost sm" data-edit="${id}">Edit</button>
      <button class="btn danger ghost sm" data-del="${id}">Delete</button></td>`;
    h+='</tr>';
  });
  h+='</tbody></table></div>';
  if(adminCol) h+=`<p class="note">⚑ Records linked to bookings can't be deleted until those bookings are cancelled.</p>`;
  box.innerHTML=h;
  $$('[data-edit]',box).forEach(b=>b.onclick=()=>{EDIT_ID=b.dataset.edit;drawData();});
  $$('[data-del]',box).forEach(b=>b.onclick=async()=>{ if(!confirm('Delete '+b.dataset.del+'?'))return;
    const act = DATA_TAB==='passenger'?'del_passenger':DATA_TAB==='agency'?'del_agency':'del_flight';
    const key = DATA_TAB==='passenger'?'pid':DATA_TAB==='agency'?'aid':'fid';
    const r=await api(act,{[key]:b.dataset.del},'POST'); toast(r.ok?r.msg:r.error,r.ok); if(r.ok){CACHE.lists=null;drawData();} });
  $$('[data-save]',box).forEach(b=>b.onclick=()=>saveEdit(b.dataset.save));
  $$('[data-cancel-edit]',box).forEach(b=>b.onclick=()=>{EDIT_ID=null;drawData();});
}
async function addRecordModal(tab, host){
  const L = await lists();
  const cl = `<datalist id="mcl">${L.cities.map(c=>`<option value="${esc(c.city)}">`).join('')}</datalist>`;
  let title, body, go;
  if(tab==='passenger'){
    title='Add Passenger';
    body=`<label>Passenger ID</label><input id="m_id" placeholder="auto: ${L.nextP}">
      <label>Full name</label><input id="m_name" placeholder="e.g. Kiran Rao">
      <label>Gender</label><select id="m_gen"><option>Male</option><option>Female</option></select>
      <label>City</label><input id="m_city" list="mcl" placeholder="e.g. Pune">`;
    go=()=>api('add_passenger',{pid:$('#m_id').value,pname:$('#m_name').value,pgender:$('#m_gen').value,pcity:$('#m_city').value},'POST');
  } else if(tab==='agency'){
    title='Add Agency';
    body=`<label>Agency ID</label><input id="m_id" placeholder="auto: ${L.nextA}">
      <label>Agency name</label><input id="m_name" placeholder="e.g. AirAsia">
      <label>City</label><input id="m_city" list="mcl" placeholder="e.g. Pune">`;
    go=()=>api('add_agency',{aid:$('#m_id').value,aname:$('#m_name').value,acity:$('#m_city').value},'POST');
  } else if(tab==='flight'){
    title='Add Flight';
    body=`<label>Flight ID</label><input id="m_id" placeholder="auto: ${L.nextF}">
      <label>Date</label><input id="m_date" type="date">
      <label>Time</label><input id="m_time" type="time">
      <label>Source</label><input id="m_src" list="mcl" placeholder="e.g. Pune">
      <label>Destination</label><input id="m_dest" list="mcl" placeholder="e.g. Goa">`;
    go=()=>api('add_flight',{fid:$('#m_id').value,fdate:$('#m_date').value,time:$('#m_time').value,src:$('#m_src').value,dest:$('#m_dest').value},'POST');
  } else { toast('Use the Bookings tab to add bookings',false); return; }

  openModal(`<div class="card" style="position:relative">
    <span class="modal-x" onclick="closeModal()" style="color:var(--ink)">×</span>
    <div class="sec-title"><span class="pip"></span>${title}</div>
    ${body}${cl}
    <button class="btn full" id="m_go" style="margin-top:18px">+ ${title}</button>
  </div>`);
  $('#m_go').onclick=async()=>{ $('#m_go').disabled=true;
    const r=await go(); toast(r.ok?r.msg:r.error, r.ok);
    if(r.ok){ closeModal(); CACHE.lists=null; drawData(); } else { $('#m_go').disabled=false; }
  };
}

function editRow(r){
  if(DATA_TAB==='passenger') return `<tr><td>${esc(r.pid)}</td>
    <td><input id="e_name" value="${esc(r.pname)}"></td>
    <td><select id="e_gen"><option ${r.pgender==='Male'?'selected':''}>Male</option><option ${r.pgender==='Female'?'selected':''}>Female</option></select></td>
    <td><input id="e_city" list="cl" value="${esc(r.pcity)}"></td>
    <td class="acts"><button class="btn sm" data-save="${r.pid}">Save</button><button class="btn ghost sm" data-cancel-edit>Cancel</button></td></tr>`;
  if(DATA_TAB==='agency') return `<tr><td>${esc(r.aid)}</td>
    <td><input id="e_name" value="${esc(r.aname)}"></td>
    <td><input id="e_city" list="cl" value="${esc(r.acity)}"></td>
    <td class="acts"><button class="btn sm" data-save="${r.aid}">Save</button><button class="btn ghost sm" data-cancel-edit>Cancel</button></td></tr>`;
  return `<tr><td>${esc(r.fid)}</td>
    <td><input id="e_date" type="date" value="${esc(r.fdate)}"></td>
    <td><input id="e_time" type="time" value="${esc(String(r.time).slice(0,5))}"></td>
    <td><input id="e_src" list="cl" value="${esc(r.src)}"></td>
    <td><input id="e_dest" list="cl" value="${esc(r.dest)}"></td>
    <td class="acts"><button class="btn sm" data-save="${r.fid}">Save</button><button class="btn ghost sm" data-cancel-edit>Cancel</button></td></tr>`;
}
async function saveEdit(id){
  let r;
  if(DATA_TAB==='passenger') r=await api('edit_passenger',{pid:id,pname:$('#e_name').value,pgender:$('#e_gen').value,pcity:$('#e_city').value},'POST');
  else if(DATA_TAB==='agency') r=await api('edit_agency',{aid:id,aname:$('#e_name').value,acity:$('#e_city').value},'POST');
  else r=await api('edit_flight',{fid:id,fdate:$('#e_date').value,time:$('#e_time').value,src:$('#e_src').value,dest:$('#e_dest').value},'POST');
  toast(r.ok?r.msg:r.error,r.ok); if(r.ok){EDIT_ID=null;CACHE.lists=null;drawData();}
}

/* ============================ USERS ============================ */
VIEWS.users = async (host)=>{
  if(!isAdmin){ host.innerHTML=lockView(); return; }
  const res=await api('users');
  const L=await lists();
  const paxOpts = '<option value="">— none —</option>'+L.passengers.map(p=>`<option value="${p.pid}">${p.pid} · ${esc(p.pname)}</option>`).join('');
  host.innerHTML=`<div class="view">
    <div class="page-head"><span class="eyebrow">Admin</span><h2>Manage Users</h2><p>Create, reset and remove login accounts.</p></div>
    <div class="grid split">
      <div class="card"><div class="sec-title"><span class="pip"></span>Add User</div>
        <label>Username</label><input id="uName" placeholder="e.g. staff1">
        <label>Password <span style="opacity:.6">(min 4)</span></label><input id="uPass" placeholder="set a password">
        <label>Role</label><select id="uRole">
          <option value="admin">Admin — full control</option>
          <option value="agent">Agent — book &amp; manage tickets, reports</option>
          <option value="customer" selected>Customer — search &amp; book only</option>
        </select>
        <div id="uPaxWrap"><label>Link to Passenger <span style="opacity:.6">(for customers)</span></label>
          <select id="uPid">${paxOpts}</select>
          <div class="muted" style="font-size:11.5px;margin-top:5px">A customer only sees this passenger's bookings.</div>
        </div>
        <div class="muted" style="font-size:12px;margin-top:8px;line-height:1.6">
          <b style="color:var(--ink)">Access levels:</b><br>
          • <b>Admin</b>: records, users, audit logs, reports, exports, reset DB<br>
          • <b>Agent</b>: book/cancel, view database, reports, exports, PDF tickets<br>
          • <b>Customer</b>: search flights, view bookings, download own ticket
        </div>
        <button class="btn full" id="uAdd" style="margin-top:16px">+ Create User</button></div>
      <div class="card"><div class="sec-title"><span class="pip" style="background:var(--g3)"></span>Accounts <span style="color:var(--muted);font-size:12px;font-weight:400">(${res.rows.length})</span></div>
        ${tableHTML(res.rows,[
          {k:'uid',label:'#'},
          {k:'username',label:'User',render:r=>esc(r.username)+(r.uid==res.me?'<span class="you">you</span>':'')},
          {k:'role',label:'Role',render:r=>`<span class="tag role">${esc(r.role)}</span>`},
          {k:'pname',label:'Passenger',render:r=>r.pid?esc(r.pid+' · '+(r.pname||'?')):'<span style="color:var(--muted)">—</span>'},
          {k:'x',label:'',render:r=>`<div class="acts">
            <button class="btn ghost sm" data-edit-u="${r.uid}" data-role="${esc(r.role)}" data-pid="${esc(r.pid||'')}" data-un="${esc(r.username)}">Edit</button>
            <button class="btn ghost sm" data-pw="${r.uid}">Reset PW</button>
            ${r.uid!=res.me?`<button class="btn danger ghost sm" data-du="${r.uid}" data-un="${esc(r.username)}">Delete</button>`:''}</div>`}
        ])}
      </div>
    </div>
  </div>`;
  // show passenger link only when role = customer
  const togglePax=()=>{ $('#uPaxWrap').style.display = $('#uRole').value==='customer' ? '' : 'none'; };
  $('#uRole').onchange=togglePax; togglePax();
  $('#uAdd').onclick=async()=>{const r=await api('add_user',{username:$('#uName').value,password:$('#uPass').value,role:$('#uRole').value,pid:$('#uPid').value},'POST');toast(r.ok?r.msg:r.error,r.ok);if(r.ok)VIEWS.users(host);};
  $$('[data-edit-u]').forEach(b=>b.onclick=()=>{
    const uid=b.dataset.editU, curRole=b.dataset.role, curPid=b.dataset.pid, un=b.dataset.un;
    const roleOpt=rr=>`<option value="${rr}" ${rr===curRole?'selected':''}>${rr.charAt(0).toUpperCase()+rr.slice(1)}</option>`;
    openModal(`<div class="card" style="position:relative">
      <span class="modal-x" onclick="closeModal()" style="color:var(--ink)">×</span>
      <div class="sec-title"><span class="pip"></span>Edit user — ${esc(un)}</div>
      <label>Role</label><select id="eRole">${roleOpt('admin')}${roleOpt('agent')}${roleOpt('customer')}</select>
      <div id="ePaxWrap"><label>Link to Passenger <span style="opacity:.6">(for customers)</span></label>
        <select id="ePid">${paxOpts.replace('value="'+curPid+'"','value="'+curPid+'" selected')}</select></div>
      <button class="btn full" id="eGo" style="margin-top:18px">Save changes</button>
    </div>`);
    const tog=()=>{ $('#ePaxWrap').style.display = $('#eRole').value==='customer' ? '' : 'none'; };
    $('#eRole').onchange=tog; tog();
    $('#eGo').onclick=async()=>{
      const r=await api('edit_user',{uid,role:$('#eRole').value,pid:$('#ePid').value},'POST');
      toast(r.ok?r.msg:r.error,r.ok); if(r.ok){ closeModal(); VIEWS.users(host); }
    };
  });
  $$('[data-pw]').forEach(b=>b.onclick=async()=>{const p=prompt('New password (min 4 chars):');if(p===null)return;if(p.length<4){toast('Too short',false);return;}const r=await api('reset_pw',{uid:b.dataset.pw,password:p},'POST');toast(r.ok?r.msg:r.error,r.ok);});
  $$('[data-du]').forEach(b=>b.onclick=async()=>{if(!confirm('Delete user '+b.dataset.un+'?'))return;const r=await api('del_user',{uid:b.dataset.du},'POST');toast(r.ok?r.msg:r.error,r.ok);if(r.ok)VIEWS.users(host);});
};

/* ============================ SQL LAB ============================ */
let SQL_I=0;
VIEWS.sql = async (host)=>{
  if(!can('admin','agent')){ host.innerHTML=lockView(); return; }
  host.innerHTML=`<div class="view">
    <div class="page-head"><span class="eyebrow">Practice</span><h2>SQL Lab</h2><p>All 30 case-study queries — executed live against MySQL.</p></div>
    <div class="sql-grid">
      <div class="qlist">${AERO.queries.map((q,i)=>`<div class="qitem ${i===SQL_I?'active':''}" data-i="${i}"><span class="qnum">${i+1}</span><span>${esc(q.t)}</span></div>`).join('')}</div>
      <div class="card" id="qpanel"></div>
    </div></div>`;
  $$('.qitem',host).forEach(it=>it.onclick=()=>{SQL_I=+it.dataset.i;$$('.qitem',host).forEach(x=>x.classList.toggle('active',x===it));drawQuery();});
  drawQuery();
};
async function drawQuery(){
  const q=AERO.queries[SQL_I]; const panel=$('#qpanel'); if(!panel)return;
  panel.innerHTML='<div class="skel-line w60"></div><div class="skel" style="height:90px;margin:12px 0"></div><div class="skel-line w40"></div>'+('<div class="skel-line"></div>'.repeat(4));
  const res=await api('sqllab',{q:SQL_I});
  panel.innerHTML=`<div class="font-d" style="font-weight:700;font-size:15px;margin-bottom:6px">Q${SQL_I+1}. ${esc(q.t)}</div>
    ${q.note?`<div class="note">⚑ ${esc(q.note)}</div>`:''}
    <pre class="code" style="margin-top:12px">${hsql(q.sql)}</pre>
    <div class="meta-row"><span class="chip">${res.count} row${res.count===1?'':'s'}</span><span>live result from MySQL</span></div>
    ${res.error?`<div class="empty" style="color:#fda4af">SQL error: ${esc(res.error)}</div>`:tableHTML(res.rows)}`;
}

/* ============================ ABOUT ============================ */
VIEWS.about = async (host)=>{
  let c={passenger:0,agency:0,flight:0,booking:0};
  try{ const s=await api('stats'); c=s.counts; }catch(e){}
  host.innerHTML=`<div class="view">
    <div class="page-head"><span class="eyebrow">About this project</span>
      <h2>AeroDesk — Flight Booking Suite</h2>
      <p>Passenger Flight Booking Through Agency · CSIT-405 DBMS Case Study</p></div>

    <div class="hero" style="background:linear-gradient(120deg,#4338ca,#7c3aed 55%,#0891b2)">
      <div class="plane"><svg viewBox="0 0 24 24"><path d="M17.8 19.2 16 11l3.5-3.5C21 6 21.5 4 21 3c-1-.5-3 0-4.5 1.5L13 8 4.8 6.2c-.5-.1-.9.1-1.1.5l-.3.5c-.2.5-.1 1 .3 1.3L9 12l-2 3H4l-1 1 3 2 2 3 1-1v-3l3-2 3.5 5.3c.3.4.8.5 1.3.3l.5-.2c.4-.3.6-.7.5-1.2z"/></svg></div>
      <h2>Designed &amp; Developed by</h2>
      <p style="font-family:Sora;font-size:28px;font-weight:800;color:#fff;margin-top:6px;letter-spacing:.5px">Ansh Kumar Singh</p>
      <p style="opacity:.9">Full-stack web application built with PHP, MySQL, HTML, CSS &amp; JavaScript.</p>
    </div>

    <div class="grid split">
      <div class="card">
        <div class="sec-title"><span class="pip"></span>Project Description</div>
        <p style="color:var(--muted);line-height:1.75">
          <b style="color:var(--ink)">AeroDesk</b> is a web-based Airline Reservation &amp;
          Booking Management System that lets travel agencies book flights for passengers,
          manage flight schedules, and generate reports. It demonstrates core DBMS concepts —
          relational schema design, joins, subqueries, set operations, aggregation and
          normalization — through a real-world airline booking scenario.
        </p>
        <p style="color:var(--muted);line-height:1.75;margin-top:12px">
          The app features secure login with admin/staff roles, full CRUD for passengers,
          agencies and flights, instant booking with auto-assigned seats and boarding passes,
          an analytics dashboard, and a live <b style="color:var(--ink)">SQL Lab</b> that runs
          all 30 case-study queries directly against MySQL.
        </p>
      </div>
      <div class="card">
        <div class="sec-title"><span class="pip" style="background:var(--g3)"></span>At a Glance</div>
        <div class="bars">
          ${aboutRow('Passengers',c.passenger,'#2563EB')}
          ${aboutRow('Agencies',c.agency,'#06B6D4')}
          ${aboutRow('Flights',c.flight,'#8B5CF6')}
          ${aboutRow('Bookings',c.booking,'#10B981')}
        </div>
        <div style="margin-top:18px;border-top:1px solid var(--line);padding-top:14px">
          <div style="display:flex;flex-wrap:wrap;gap:8px">
            ${['PHP','MySQL','HTML5','CSS3','JavaScript','AJAX'].map(t=>`<span class="chip">${t}</span>`).join('')}
          </div>
        </div>
      </div>
    </div>

    <div class="card" style="margin-top:18px;text-align:center">
      <div style="font-family:Sora;font-weight:700">CSIT-405 DBMS &middot; Passenger Flight Booking Through Agency</div>
      <p style="color:var(--muted);margin-top:4px">Sagar Institute of Research and Technology &nbsp;·&nbsp; Designed &amp; Developed by <b style="color:var(--ink)">Ansh Kumar Singh</b></p>
    </div>
  </div>`;
};
function aboutRow(label,val,color){
  return `<div class="bar-row"><div class="bl">${label}</div>
    <div class="bar-track"><div class="bar-fill" style="width:100%;background:linear-gradient(90deg,${color},${color}aa)">${val}</div></div></div>`;
}

/* ============================ REPORTS ============================ */
VIEWS.reports = async (host)=>{
  if(!can('admin','agent')){ host.innerHTML=lockView(); return; }
  const r = await api('report');
  if(!r.ok){ host.innerHTML=lockView(); return; }
  const t=r.totals;
  host.innerHTML=`<div class="view">
    <div class="page-head"><span class="eyebrow">Analytics</span><h2>Booking Reports</h2>
      <p>Summary analytics and downloadable reports.</p></div>
    <div class="grid cols-4">
      ${miniStat('Total Bookings',t.bookings,'#2563EB')}
      ${miniStat('Passengers',t.passengers,'#06B6D4')}
      ${miniStat('Flights Used',t.flights,'#8B5CF6')}
      ${miniStat('Agencies',t.agencies,'#10B981')}
    </div>
    <div class="card" style="margin-top:18px;display:flex;gap:12px;flex-wrap:wrap;align-items:center">
      <span class="sec-title" style="margin:0"><span class="pip"></span>Downloads</span>
      <button class="btn sm" data-dl="report" data-fn="booking_report.csv">⬇ Booking Report (CSV)</button>
      <button class="btn sm ghost" data-dl="bookings" data-fn="bookings.csv">⬇ All Bookings (Excel/CSV)</button>
      <button class="btn sm ghost" data-dl="passengers" data-fn="passengers.csv">⬇ Passengers (Excel/CSV)</button>
    </div>
    <div class="grid cols-2" style="margin-top:18px">
      <div class="card"><div class="sec-title"><span class="pip"></span>Bookings by Route</div>${bars(r.byRoute,'route','c')}</div>
      <div class="card"><div class="sec-title"><span class="pip" style="background:var(--g3)"></span>Bookings by Agency</div>${bars(r.byAgency,'aname','c','#06B6D4')}</div>
    </div>
    <div class="grid cols-2" style="margin-top:18px">
      <div class="card"><div class="sec-title"><span class="pip" style="background:var(--g2)"></span>Bookings by Date</div>${bars(r.byDate,'fdate','c','#10B981')}</div>
      <div class="card"><div class="sec-title"><span class="pip" style="background:var(--g3)"></span>By Gender</div>${donutG(r.byGender)}</div>
    </div>
  </div>`;
  $$('.num[data-to]',host).forEach((el,i)=>setTimeout(()=>animateNum(el,+el.dataset.to), i*80));
  runBars(host);
  $$('[data-dl]',host).forEach(b=>b.onclick=()=>downloadFile('export.php?type='+b.dataset.dl, b.dataset.fn));
};
function miniStat(label,val,color){
  return `<div class="card stat"><div class="top"><span class="label">${label}</span>
    <span class="ic" style="background:linear-gradient(135deg,${color},${color}99)"><svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><path d="M3 3v18h18"/></svg></span></div>
    <div class="num" data-to="${val}">0</div><div class="sub">total</div></div>`;
}
function donutG(rows){
  if(!rows||!rows.length) return '<div class="empty">No data.</div>';
  const total=rows.reduce((a,r)=>a+ +r.c,0)||1; let acc=0; const segs=[];
  const cols={Male:'#2563EB',Female:'#8B5CF6'};
  rows.forEach((r,i)=>{const f=+r.c/total;const col=cols[r.pgender]||PALETTE[i%PALETTE.length];
    segs.push(`${col} ${(acc*100).toFixed(2)}% ${((acc+f)*100).toFixed(2)}%`);acc+=f;});
  const leg=rows.map((r,i)=>`<div><i style="background:${cols[r.pgender]||PALETTE[i%PALETTE.length]}"></i>${esc(r.pgender)} · <b>${r.c}</b></div>`).join('');
  return `<div class="donut-wrap"><div style="width:140px;height:140px;border-radius:50%;
    background:conic-gradient(${segs.join(',')});mask:radial-gradient(circle 42px at center,transparent 98%,#000 100%);
    -webkit-mask:radial-gradient(circle 42px at center,transparent 98%,#000 100%)"></div><div class="legend">${leg}</div></div>`;
}

/* ============================ FEEDBACK ============================ */
/* Build a pre-filled email reply to the customer.
   gmailComposeUrl -> Gmail "compose" tab (works on any machine signed in to Gmail)
   mailtoUrl       -> default OS mail client fallback */
function replyBody(name, original, reply){
  return 'Hi '+(name||'there')+',\n\n'+
    reply+'\n\n'+
    '— — — — — — — — — —\n'+
    'Your original message:\n"'+(original||'')+'"\n\n'+
    'Regards,\nAeroDesk Support Team';
}
function gmailComposeUrl(to, name, original, reply){
  return 'https://mail.google.com/mail/?view=cm&fs=1&tf=1&to='+encodeURIComponent(to)+
         '&su='+encodeURIComponent('Re: Your AeroDesk message')+
         '&body='+encodeURIComponent(replyBody(name,original,reply));
}
function mailtoUrl(to, name, original, reply){
  return 'mailto:'+encodeURIComponent(to)+
         '?subject='+encodeURIComponent('Re: Your AeroDesk message')+
         '&body='+encodeURIComponent(replyBody(name,original,reply));
}

VIEWS.feedback = async (host)=>{
  if(!can('admin','agent')){ host.innerHTML=lockView(); return; }
  const res = await api('fb_list');
  // opening this page clears the "new" notifications
  await api('fb_read_all',{},'POST'); refreshBell();
  const rows = res.rows||[];
  const card = f=>`
    <div class="fb-card ${f.status==='new'?'unread':''}">
      <div class="fb-top">
        <span class="fb-name">${esc(f.name)}</span>
        <span class="fb-email">&lt;${esc(f.email)}&gt;</span>
        ${f.status==='new'?'<span class="badge-new">NEW</span>':''}
        ${f.status==='replied'?'<span class="badge-replied">REPLIED</span>':''}
        <span class="fb-time">${esc(f.created)}</span>
      </div>
      <div class="fb-msg">${esc(f.message)}</div>
      ${f.reply?`<div class="fb-reply"><b>Your reply:</b> ${esc(f.reply)} <span style="color:var(--muted)">· ${esc(f.replied||'')}</span></div>`:''}
      <div class="fb-actions">
        <textarea id="rep_${f.fbid}" placeholder="Type a reply to ${esc(f.name)}...">${f.reply?esc(f.reply):''}</textarea>
        <button class="btn sm" data-reply="${f.fbid}" data-email="${esc(f.email)}" data-name="${esc(f.name)}" data-msg="${esc(f.message)}">${f.reply?'Update reply':'Send reply'}</button>
        <button class="btn danger ghost sm" data-del="${f.fbid}">Delete</button>
      </div>
    </div>`;
  host.innerHTML=`<div class="view">
    <div class="page-head"><span class="eyebrow">Notifications</span><h2>Feedback &amp; Messages</h2>
      <p>Messages submitted through the website contact form. Reply to respond.</p></div>
    <div class="card">
      <div class="sec-title"><span class="pip"></span>Inbox <span style="color:var(--muted);font-weight:400;font-size:12px">(${rows.length})</span></div>
      ${rows.length? rows.map(card).join('') : '<div class="empty">No feedback yet.</div>'}
    </div>
  </div>`;
  $$('[data-reply]',host).forEach(b=>b.onclick=async()=>{
    const txt=$('#rep_'+b.dataset.reply).value.trim();
    if(!txt){ toast('Reply cannot be empty',false); return; }
    // IMPORTANT: open the email window NOW (synchronously inside the click) so the
    // browser doesn't block the popup. Opening it after an "await" gets blocked.
    let win=null;
    if(b.dataset.email){
      win=window.open(gmailComposeUrl(b.dataset.email,b.dataset.name,b.dataset.msg,txt),'_blank');
    }
    const r=await api('fb_reply',{fbid:b.dataset.reply,reply:txt},'POST');
    if(r.ok){
      if(b.dataset.email){
        // if the popup was blocked, fall back to the OS default mail app
        if(!win||win.closed){ window.location.href=mailtoUrl(b.dataset.email,b.dataset.name,b.dataset.msg,txt); }
        toast('Reply saved · email opened to '+b.dataset.email, true);
      } else toast(r.msg, true);
      VIEWS.feedback(host);
    } else { if(win) win.close(); toast(r.error,false); }
  });
  $$('[data-del]',host).forEach(b=>b.onclick=async()=>{ if(!confirm('Delete this feedback?'))return;
    const r=await api('fb_delete',{fbid:b.dataset.del},'POST'); toast(r.ok?r.msg:r.error,r.ok); if(r.ok) VIEWS.feedback(host);
  });
};

/* ---------- bell notification ---------- */
async function refreshBell(){
  if(!can('admin','agent')) return;
  try{
    const r=await api('fb_count');
    const dot=$('#bellDot'), bell=$('#bellBtn');
    if(dot&&bell){
      const n=r.unread||0;
      dot.classList.toggle('show', n>0);
      bell.classList.toggle('has-new', n>0);
      bell.title = n>0 ? (n+' new feedback message'+(n>1?'s':'')) : 'Feedback notifications';
    }
  }catch(e){}
}

/* ============================ AUDIT LOGS ============================ */
VIEWS.audit = async (host)=>{
  if(!isAdmin){ host.innerHTML=lockView(); return; }
  const r = await api('audit');
  const badge = a => {
    const map={login:'#10B981',logout:'#93a0c8',login_failed:'#EF4444',book:'#06B6D4',cancel:'#EF4444',
      reset_db:'#f59e0b',export:'#a78bfa',ticket_pdf:'#a78bfa'};
    const c = map[a] || '#2563EB';
    return `<span class="tag" style="background:${c}22;color:${c}">${esc(a)}</span>`;
  };
  host.innerHTML=`<div class="view">
    <div class="page-head"><span class="eyebrow">Security</span><h2>Audit Logs</h2>
      <p>Every sensitive action is recorded — last 200 events.</p></div>
    <div class="card">
      <div class="sec-title"><span class="pip"></span>Activity Trail <span style="color:var(--muted);font-weight:400;font-size:12px">(${r.rows.length})</span></div>
      ${tableHTML(r.rows,[
        {k:'lid',label:'#'},
        {k:'ts',label:'Time'},
        {k:'username',label:'User'},
        {k:'role',label:'Role',render:x=>`<span class="tag role">${esc(x.role)}</span>`},
        {k:'action',label:'Action',render:x=>badge(x.action)},
        {k:'detail',label:'Detail'},
        {k:'ip',label:'IP'},
      ])}
    </div>
  </div>`;
};

/* ---------- shared ---------- */
function lockView(){return `<div class="view"><div class="card empty"><div style="font-size:40px">🔒</div>
  <h3 class="font-d" style="margin:10px 0 4px">Admins only</h3>
  <p>Your account role is <b>staff</b>. Ask an administrator for access.</p></div></div>`;}

/* ---------- theme + reset ---------- */
const themeBtn=$('#themeBtn');
function setTheme(t){document.documentElement.dataset.theme=t;localStorage.setItem('aero-theme',t);
  $('#themeIcon').innerHTML = t==='dark'
    ? '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M2 12h2M20 12h2M5 5l1.5 1.5M17.5 17.5 19 19M19 5l-1.5 1.5M6.5 17.5 5 19"/>'
    : '<path d="M21 12.8A9 9 0 1 1 11.2 3 7 7 0 0 0 21 12.8z"/>';}
setTheme(localStorage.getItem('aero-theme')||'dark');
themeBtn.onclick=()=>setTheme(document.documentElement.dataset.theme==='dark'?'light':'dark');
if($('#resetBtn')) $('#resetBtn').onclick=async()=>{ if(!isAdmin){toast('Admins only',false);return;}
  if(!confirm('Reset the whole database to original sample data?'))return;
  const r=await api('reset_db',{},'POST'); toast(r.msg,r.ok); CACHE.lists=null; setView('dashboard'); };

/* ---------- mobile drawer ---------- */
const sideEl=document.querySelector('.side'), scrimEl=$('#scrim'), hamEl=$('#hamburger');
function openDrawer(){ sideEl.classList.add('open'); scrimEl.classList.add('show'); }
function closeDrawer(){ sideEl.classList.remove('open'); scrimEl.classList.remove('show'); }
if(hamEl) hamEl.onclick=()=>sideEl.classList.contains('open')?closeDrawer():openDrawer();
if(scrimEl) scrimEl.onclick=closeDrawer;
// close drawer after choosing a menu item on small screens
$$('.nav').forEach(n=>n.addEventListener('click',closeDrawer));
// auto-close if resized up to desktop
window.addEventListener('resize',()=>{ if(window.innerWidth>980) closeDrawer(); });

/* ---------- bell: click opens Feedback, poll every 20s ---------- */
if($('#bellBtn')) $('#bellBtn').onclick=()=>setView('feedback');
if(can('admin','agent')){ refreshBell(); setInterval(refreshBell, 20000); }

/* ---------- boot ---------- */
setView('dashboard');
