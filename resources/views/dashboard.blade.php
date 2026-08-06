<x-app-layout>

<style>
    
</style>

<div class="dash-wrap" id="dashboardRoot">

    <div class="dash-header">
        <div>
            <div class="dash-title"><i class="bi bi-broadcast me-2"></i>Casino Floor Monitor</div>
            <div class="dash-subtitle">Real-time table activity &amp; financial overview</div>
        </div>
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <div class="gameday-chip"><i class="bi bi-calendar3"></i><span id="gamedayLabel">&mdash;</span></div>
            <div class="live-badge"><span class="live-dot"></span>LIVE</div>
            <div class="refresh-controls">
                <div class="countdown-ring">
                    <i class="bi bi-arrow-clockwise"></i>
                    <span>Refresh in</span>
                    <span id="refresh-countdown">&mdash;</span>s
                </div>
                <button class="refresh-btn" id="refreshBtn" onclick="fetchDashboard(true)">
                    <i class="bi bi-arrow-clockwise" id="refreshIcon"></i> Refresh
                </button>
                <select class="interval-select" id="intervalSelect" onchange="setRefreshInterval(this.value)">
                    <option value="10">10s</option>
                    <option value="20" selected>20s</option>
                    <option value="30">30s</option>
                    <option value="60">60s</option>
                </select>
            </div>
        </div>
    </div>

    <div class="kpi-grid">
        <div class="kpi-card" style="--accent-color:#f0c040;--icon-bg:rgba(240,192,64,.1)">
            <div class="kpi-icon"><i class="bi bi-table"></i></div>
            <div class="kpi-label">Open Tables</div>
            <div class="kpi-value" id="kpi-open-tables">&mdash;</div>
            <div class="kpi-sub" id="kpi-total-tables">of &mdash; configured</div>
        </div>
        <div class="kpi-card" style="--accent-color:#68d391;--icon-bg:rgba(56,161,105,.1)">
            <div class="kpi-icon"><i class="bi bi-cash-coin"></i></div>
            <div class="kpi-label">Total Float</div>
            <div class="kpi-value" id="kpi-total-float">&mdash;</div>
            <div class="kpi-sub">live across all tables</div>
        </div>
        <div class="kpi-card" style="--accent-color:#90cdf4;--icon-bg:rgba(49,130,206,.1)">
            <div class="kpi-icon"><i class="bi bi-arrow-left-right"></i></div>
            <div class="kpi-label">Transactions</div>
            <div class="kpi-value" id="kpi-total-txns">&mdash;</div>
            <div class="kpi-sub">today's gameday</div>
        </div>
        <div class="kpi-card" style="--accent-color:#fbd38d;--icon-bg:rgba(237,137,54,.1)">
            <div class="kpi-icon"><i class="bi bi-people"></i></div>
            <div class="kpi-label">Total Buy-ins</div>
            <div class="kpi-value" id="kpi-total-buyins">&mdash;</div>
            <div class="kpi-sub">today</div>
        </div>
        <div class="kpi-card" style="--accent-color:#b794f4;--icon-bg:rgba(128,90,213,.1)">
            <div class="kpi-icon"><i class="bi bi-graph-up-arrow"></i></div>
            <div class="kpi-label">Net Revenue</div>
            <div class="kpi-value" id="kpi-revenue">&mdash;</div>
            <div class="kpi-sub">drops minus fills</div>
        </div>
        <div class="kpi-card" style="--accent-color:#fc8181;--icon-bg:rgba(229,62,62,.1)">
            <div class="kpi-icon"><i class="bi bi-hourglass-split"></i></div>
            <div class="kpi-label">Pending TXNs</div>
            <div class="kpi-value" id="kpi-pending">&mdash;</div>
            <div class="kpi-sub">awaiting processing</div>
        </div>
    </div>

    <div class="section-label"><i class="bi bi-suit-diamond-fill me-1"></i> Open Tables &mdash; Live Activity</div>
    <div class="tables-grid" id="openTablesGrid">
        <div class="table-card">
            <div class="card-header-band">
                <div class="skeleton" style="width:130px;height:18px;"></div>
                <div class="skeleton" style="width:55px;height:18px;border-radius:20px;"></div>
            </div>
            <div class="casino-table-wrap" style="padding:20px;">
                <div class="skeleton" style="width:260px;height:155px;border-radius:50% 50% 8px 8px;margin:auto;"></div>
            </div>
            <div class="table-stats-bar">
                <div class="stat-cell"><div class="skeleton" style="height:32px;"></div></div>
                <div class="stat-cell"><div class="skeleton" style="height:32px;"></div></div>
                <div class="stat-cell"><div class="skeleton" style="height:32px;"></div></div>
            </div>
            <div class="activity-feed"><div class="feed-empty">Loading&hellip;</div></div>
        </div>
    </div>

    <div id="closedTablesSection" style="display:none;">
        <div class="section-label"><i class="bi bi-moon-stars me-1"></i> Closed / Inactive Tables</div>
        <div id="closedTablesWrap"></div>
    </div>

    <div class="bottom-row">
        <div class="chart-card">
            <h6><i class="bi bi-bar-chart-fill"></i>Hourly Transaction Volume &mdash; Today</h6>
            <div class="bar-chart-wrap" id="hourlyChart">
                <div class="feed-empty" style="width:100%;">Loading chart&hellip;</div>
            </div>
        </div>
        <div class="pending-card">
            <h6>
                <i class="bi bi-clock-history"></i>
                Pending Transactions
                <span class="pending-badge ms-auto" id="pendingBadge">0</span>
            </h6>
            <div id="pendingList"><div class="pending-empty">No pending transactions.</div></div>
        </div>
    </div>

    <div class="player-tooltip" id="playerTooltip"></div>

</div>

@push('scripts')
<script>
(function(){
    'use strict';
    var LIVE_URL = "{{ route('dashboard.live-data') }}";
    var intervalSec = 20, countdown = 20, tickerId = null;

    function fmt(n){ return new Intl.NumberFormat('en-US',{minimumFractionDigits:2,maximumFractionDigits:2}).format(n||0); }
    function fmtI(n){ return new Intl.NumberFormat('en-US').format(n||0); }

    function seatPositions(cx,cy,rx,ry){
        var s=[];
        for(var i=0;i<6;i++){
            var a=Math.PI-(Math.PI/5)*i;
            s.push({x:cx+rx*Math.cos(a),y:cy-ry*Math.sin(a)});
        }
        return s;
    }

    function lighten(hex,amt){
        try{
            var n=parseInt(hex.replace('#',''),16);
            var r=Math.min(255,(n>>16)+amt);
            var g=Math.min(255,((n>>8)&0xff)+amt);
            var b=Math.min(255,(n&0xff)+amt);
            return 'rgb('+r+','+g+','+b+')';
        }catch(e){return hex;}
    }

    function buildSVG(t){
        var W=310,H=215,cx=155,cy=172,rx=122,ry=115;
        var fc=t.felt_color||'#1a5c2e';
        var players=t.players||[];
        var isOpen=t.is_open;
        var seats=seatPositions(cx,cy,rx,ry);
        var uid=t.id;
        var sh='';

        seats.forEach(function(pos,i){
            var p=players[i]||{active:false};
            var active=p.active&&isOpen;
            var rCls=active?'active':'inactive';
            var sn=i+1;
            var lbl=active?(p.tab_id?p.tab_id.slice(-5):'P'+sn):'Empty';
            var lc=active?'#68d391':'#556677';
            var ico=active?'\uD83D\uDC64':'\u25CC';
            var pulse=active?('<circle class="pulse-ring" cx="'+pos.x.toFixed(1)+'" cy="'+pos.y.toFixed(1)+'" r="19" fill="none" stroke="#68d391" stroke-width="1.5" opacity="0.5" style="animation-delay:'+(i*0.35)+'s"/>'):'';
            var tab=(p.tab_id||'').replace(/"/g,'&quot;');
            var bal=p.balance!=null?p.balance:'';
            var act=(p.last_action||'').replace(/"/g,'&quot;');
            var amnt=p.last_amount!=null?p.last_amount:'';
            var at=(p.last_at||'').replace(/"/g,'&quot;');
            sh+='<g class="player-seat" data-seat="'+sn+'" data-active="'+(active?1:0)+'" data-tab="'+tab+'" data-balance="'+bal+'" data-action="'+act+'" data-amount="'+amnt+'" data-at="'+at+'" onmouseenter="dashTTShow(event,this)" onmouseleave="dashTTHide()">';
            sh+=pulse;
            sh+='<circle class="seat-ring '+rCls+'" cx="'+pos.x.toFixed(1)+'" cy="'+pos.y.toFixed(1)+'" r="17"/>';
            sh+='<text x="'+pos.x.toFixed(1)+'" y="'+(pos.y+1).toFixed(1)+'" font-size="13" text-anchor="middle" dominant-baseline="middle">'+ico+'</text>';
            sh+='<text x="'+pos.x.toFixed(1)+'" y="'+(pos.y+27).toFixed(1)+'" font-size="9" fill="'+lc+'" text-anchor="middle" dominant-baseline="middle" font-family="Outfit,sans-serif" font-weight="600">'+lbl+'</text>';
            sh+='</g>';
        });

        var dx=cx,dy=cy-22;
        var glow=isOpen?('<path d="M '+(cx-rx)+' '+cy+' A '+rx+' '+ry+' 0 0 1 '+(cx+rx)+' '+cy+'" fill="none" stroke="#68d391" stroke-width="2.5" opacity="0.45"/>'):'';

        return '<svg class="casino-table-svg" viewBox="0 0 '+W+' '+H+'" xmlns="http://www.w3.org/2000/svg">'
            +'<defs><radialGradient id="fg'+uid+'" cx="50%" cy="70%" r="60%"><stop offset="0%" stop-color="'+lighten(fc,18)+'"/><stop offset="100%" stop-color="'+fc+'"/></radialGradient></defs>'
            +'<ellipse cx="'+cx+'" cy="'+(cy+8)+'" rx="'+(rx+6)+'" ry="18" fill="rgba(0,0,0,.45)"/>'
            +'<path d="M '+(cx-rx-8)+' '+cy+' A '+(rx+8)+' '+(ry+8)+' 0 0 1 '+(cx+rx+8)+' '+cy+' L '+(cx+rx+8)+' '+(cy+38)+' L '+(cx-rx-8)+' '+(cy+38)+' Z" fill="#7a5600"/>'
            +'<path d="M '+(cx-rx)+' '+cy+' A '+rx+' '+ry+' 0 0 1 '+(cx+rx)+' '+cy+' L '+(cx+rx)+' '+(cy+32)+' L '+(cx-rx)+' '+(cy+32)+' Z" fill="url(#fg'+uid+')"/>'
            +'<path d="M '+(cx-rx+5)+' '+(cy+10)+' Q '+cx+' '+(cy-22)+' '+(cx+rx-5)+' '+(cy+10)+'" fill="none" stroke="rgba(255,255,255,.06)" stroke-width="1"/>'
            +'<path d="M '+(cx-rx+20)+' '+(cy+4)+' Q '+cx+' '+(cy-44)+' '+(cx+rx-20)+' '+(cy+4)+'" fill="none" stroke="rgba(255,255,255,.04)" stroke-width="1"/>'
            +'<ellipse cx="'+cx+'" cy="'+(cy+8)+'" rx="62" ry="18" fill="none" stroke="rgba(255,255,255,.15)" stroke-width="1.5" stroke-dasharray="4 4"/>'
            +'<text x="'+cx+'" y="'+(cy+8)+'" text-anchor="middle" dominant-baseline="middle" font-family="Orbitron,sans-serif" font-size="11" font-weight="700" fill="rgba(255,255,255,.25)" letter-spacing="2">'+t.game_code+'</text>'
            +'<circle cx="'+dx+'" cy="'+dy+'" r="16" fill="#0c1c0a" stroke="#f0c040" stroke-width="2"/>'
            +'<text x="'+dx+'" y="'+(dy-1)+'" text-anchor="middle" dominant-baseline="middle" font-size="12">\uD83C\uDFA9</text>'
            +'<text x="'+dx+'" y="'+(dy+16)+'" text-anchor="middle" dominant-baseline="middle" font-family="Outfit,sans-serif" font-size="8" fill="#f0c040" font-weight="700">DEALER</text>'
            +sh+glow+'</svg>';
    }

    function buildCard(t){
        var el=document.createElement('div');
        el.className='table-card '+(t.is_open?'is-open':'is-closed');
        el.id='tc-'+t.id;
        var fc=t.is_open?'#68d391':'#8899aa';
        var rh='';
        if(t.recent_txns&&t.recent_txns.length){
            t.recent_txns.forEach(function(tx){
                var out=tx.txn_type==='DROP'||tx.txn_type==='CASHOUT';
                rh+='<div class="feed-row">'
                    +'<span class="txn-pill txn-'+tx.txn_type+'">'+tx.txn_type+'</span>'
                    +'<span style="color:#8899aa;overflow:hidden;max-width:70px;text-overflow:ellipsis;">'+(tx.tab_id?tx.tab_id.slice(-7):'Table')+'</span>'
                    +'<span style="color:#667788;font-size:.68rem;">'+(tx.at||'')+'</span>'
                    +'<span class="feed-amount" style="color:'+(out?'#fc8181':'#68d391')+';">'+(out?'-':'+')+fmt(tx.amount)+'</span>'
                    +'</div>';
            });
        } else { rh='<div class="feed-empty">No transactions today.</div>'; }
        var ap=t.active_players||0;
        var oa=t.opened_at?'Opened '+t.opened_at:'';
        el.innerHTML='<div class="card-header-band"><div><div class="card-table-name">'+t.name+'</div>'+(oa?'<div style="font-size:.67rem;color:#556677;margin-top:2px;">'+oa+'</div>':'')+'</div>'
            +'<div class="d-flex align-items-center gap-2"><span class="card-game-badge">'+t.game_code+'</span>'
            +'<span class="status-pill '+(t.is_open?'open':'closed')+'">'+(t.is_open?'<span class="live-dot"></span>':'')+(t.is_open?'Open':'Closed')+'</span></div></div>'
            +'<div class="casino-table-wrap">'+buildSVG(t)+'</div>'
            +'<div class="table-stats-bar">'
            +'<div class="stat-cell"><div class="sc-label">Float</div><div class="sc-val" style="color:'+fc+';">'+(t.is_open&&t.float_current!=null?fmt(t.float_current):'&mdash;')+'</div></div>'
            +'<div class="stat-cell"><div class="sc-label">TXNs</div><div class="sc-val" style="color:#90cdf4;">'+fmtI(t.txn_count)+'</div></div>'
            +'<div class="stat-cell"><div class="sc-label">Buy-ins</div><div class="sc-val" style="color:#fbd38d;">'+fmt(t.total_buyin)+'</div></div>'
            +'</div>'
            +'<div class="activity-feed"><div class="feed-title"><i class="bi bi-activity me-1"></i>Recent Activity'+(ap>0?' <span style="color:#68d391;font-size:.68rem;margin-left:8px;">&middot; '+ap+' active player'+(ap!==1?'s':'')+'</span>':'')+'</div>'+rh+'</div>';
        return el;
    }

    function renderTables(data){
        var og=document.getElementById('openTablesGrid');
        var cw=document.getElementById('closedTablesWrap');
        var cs=document.getElementById('closedTablesSection');
        var open=data.tables.filter(function(t){return t.is_open;});
        var closed=data.tables.filter(function(t){return !t.is_open;});
        og.innerHTML='';
        if(!open.length){
            og.innerHTML='<div style="color:var(--txt-muted);font-size:.85rem;padding:32px 0;grid-column:1/-1;text-align:center;"><i class="bi bi-moon-stars" style="font-size:2.5rem;display:block;margin-bottom:12px;opacity:.35;"></i>No tables are currently open.</div>';
        } else {
            open.forEach(function(t){og.appendChild(buildCard(t));});
        }
        cw.innerHTML='';
        if(closed.length){
            cs.style.display='';
            closed.forEach(function(t){
                var r=document.createElement('div');
                r.className='closed-table-row';
                r.innerHTML='<span class="card-game-badge">'+t.game_code+'</span><span style="font-weight:600;">'+t.name+'</span><span style="color:var(--txt-muted);font-size:.72rem;margin-left:auto;">'+fmtI(t.txn_count)+' txns &middot; Buy-in '+fmt(t.total_buyin)+'</span><span class="status-pill closed">Closed</span>';
                cw.appendChild(r);
            });
        } else { cs.style.display='none'; }
    }

    function renderKPIs(k,gd){
        document.getElementById('kpi-open-tables').textContent=k.open_tables;
        document.getElementById('kpi-total-tables').textContent='of '+k.total_tables+' configured';
        document.getElementById('kpi-total-float').textContent=fmt(k.total_float);
        document.getElementById('kpi-total-txns').textContent=fmtI(k.total_txns);
        document.getElementById('kpi-total-buyins').textContent=fmt(k.total_buyins);
        var re=document.getElementById('kpi-revenue');
        re.textContent=fmt(k.total_revenue);
        re.style.color=k.total_revenue>=0?'#68d391':'#fc8181';
        document.getElementById('kpi-pending').textContent=k.pending_txns;
        document.getElementById('gamedayLabel').textContent=gd;
        document.getElementById('pendingBadge').textContent=k.pending_txns;
    }

    function renderChart(hv){
        var ch=document.getElementById('hourlyChart');
        ch.innerHTML='';
        var vals=[];
        for(var h=0;h<24;h++){if(hv[h])vals.push({h:h,c:hv[h].count});}
        if(!vals.length){ch.innerHTML='<div class="feed-empty" style="width:100%;">No transactions recorded yet today.</div>';return;}
        var mx=Math.max.apply(null,vals.map(function(v){return v.c;}).concat([1]));
        vals.forEach(function(v){
            var col=document.createElement('div');
            col.className='bar-col';
            var bh=Math.max(2,Math.round((v.c/mx)*88));
            var lb=v.h<10?'0'+v.h:''+v.h;
            col.innerHTML='<div class="bar-fill" style="height:'+bh+'px;" title="'+v.c+' txns at '+lb+':00"></div><div class="bar-hour">'+lb+'</div>';
            ch.appendChild(col);
        });
    }

    window.dashTTShow=function(e,el){
        var tt=document.getElementById('playerTooltip');
        var active=el.getAttribute('data-active')==='1';
        var seat=el.getAttribute('data-seat');
        var tab=el.getAttribute('data-tab');
        var bal=el.getAttribute('data-balance');
        var act=el.getAttribute('data-action');
        var amt=el.getAttribute('data-amount');
        var at=el.getAttribute('data-at');
        if(active){
            tt.innerHTML='<div class="tt-seat">Seat '+seat+'</div>'
                +'<div class="tt-row"><span class="tt-key">Tab ID</span><span class="tt-val">'+(tab||'&mdash;')+'</span></div>'
                +'<div class="tt-row"><span class="tt-key">Balance</span><span class="tt-val">'+(bal!==''?fmt(parseFloat(bal)):'&mdash;')+'</span></div>'
                +'<div class="tt-row"><span class="tt-key">Last Action</span><span class="tt-val">'+(act||'&mdash;')+'</span></div>'
                +'<div class="tt-row"><span class="tt-key">Amount</span><span class="tt-val">'+(amt!==''?fmt(parseFloat(amt)):'&mdash;')+'</span></div>'
                +'<div class="tt-row"><span class="tt-key">At</span><span class="tt-val">'+(at||'&mdash;')+'</span></div>';
        } else {
            tt.innerHTML='<div class="tt-seat">Seat '+seat+'</div><div style="color:var(--txt-muted);margin-top:6px;font-size:.72rem;">Empty seat</div>';
        }
        tt.classList.add('visible');
        document.addEventListener('mousemove',dashTTMove);
    };
    window.dashTTHide=function(){
        document.getElementById('playerTooltip').classList.remove('visible');
        document.removeEventListener('mousemove',dashTTMove);
    };
    function dashTTMove(e){
        var tt=document.getElementById('playerTooltip');
        tt.style.left=(e.clientX+16)+'px';
        tt.style.top=(e.clientY-8)+'px';
    }

    async function fetchDashboard(manual){
        if(manual){
            var btn=document.getElementById('refreshBtn');
            btn.classList.add('spinning');
            setTimeout(function(){btn.classList.remove('spinning');},700);
        }
        try{
            var res=await fetch(LIVE_URL,{headers:{'X-Requested-With':'XMLHttpRequest'}});
            var data=await res.json();
            if(!data.success)return;
            renderKPIs(data.kpis,data.gameday);
            renderTables(data);
            renderChart(data.hourly_volume||{});
        }catch(err){console.error('Dashboard fetch error:',err);}
        countdown=intervalSec;
    }
    window.fetchDashboard=fetchDashboard;

    function startTicker(){
        if(tickerId)clearInterval(tickerId);
        countdown=intervalSec;
        tickerId=setInterval(function(){
            countdown--;
            var el=document.getElementById('refresh-countdown');
            if(el)el.textContent=countdown;
            if(countdown<=0)fetchDashboard(false);
        },1000);
    }

    window.setRefreshInterval=function(sec){
        intervalSec=parseInt(sec);
        startTicker();
    };

    document.addEventListener('DOMContentLoaded',function(){
        var el=document.getElementById('refresh-countdown');
        if(el)el.textContent=intervalSec;
        fetchDashboard(false);
        startTicker();
    });
})();
</script>
@endpush

</x-app-layout>