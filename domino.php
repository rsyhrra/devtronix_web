<?php
$room_id = $_GET['room'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title>Domino Online — Devtronix</title>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;700;900&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        theme: {
            extend: {
                fontFamily: { sans: ['Space Grotesk', 'sans-serif'] },
                colors: {
                    'brutal-yellow': '#FFF000',
                    'brutal-green': '#00FF66',
                    'brutal-pink': '#FF007A',
                    'brutal-cyan': '#00F0FF',
                    'brutal-purple': '#9E00FF',
                    'brutal-cream': '#FAF6EE',
                }
            }
        }
    }
</script>
<style>
:root { --bg: #FAF6EE; }
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
  background-color: var(--bg);
  background-image: radial-gradient(#000000 1.5px, transparent 1.5px), radial-gradient(#000000 1.5px, var(--bg) 1.5px);
  background-size: 30px 30px;
  background-position: 0 0, 15px 15px;
  color: #000;
  font-family: 'Space Grotesk', sans-serif;
  min-height: 100vh;
  overflow-x: hidden;
}
.neo-btn {
  font-weight: 900; padding: 12px 24px; border: 3px solid #000; border-radius: 12px; cursor: pointer;
  transition: all 0.1s; text-transform: uppercase; display: inline-flex; align-items: center; justify-content: center;
  box-shadow: 4px 4px 0px 0px rgba(0,0,0,1);
}
.neo-btn:hover:not(:disabled) { transform: translate(-2px, -2px); box-shadow: 6px 6px 0px 0px rgba(0,0,0,1); }
.neo-btn:active:not(:disabled) { transform: translate(2px, 2px); box-shadow: 0px 0px 0px 0px rgba(0,0,0,1); }
.neo-btn:disabled { opacity: 0.5; cursor: not-allowed; box-shadow: none; transform: none; }
.btn-yellow { background: #FFF000; }
.btn-pink { background: #FF007A; color: white; }
.btn-green { background: #00FF66; }
.btn-cyan { background: #00F0FF; }

.glass-panel { background: #fff; border: 3px solid #000; border-radius: 16px; padding: 24px; box-shadow: 6px 6px 0px 0px rgba(0,0,0,1); }
.screen { display: none; min-height: 80vh; flex-direction: column; align-items: center; padding: 40px 20px; }
.screen.active { display: flex; }

/* Domino Tiles */
.domino-tile {
    width: 60px; height: 120px; background: #fff; border: 3px solid #000; border-radius: 10px;
    display: flex; flex-direction: column; justify-content: space-between; align-items: center;
    box-shadow: 3px 3px 0px 0px rgba(0,0,0,1); padding: 5px; cursor: pointer; transition: transform 0.2s;
    user-select: none;
}
.domino-tile:hover { transform: translateY(-10px); }
.domino-tile.horizontal {
    width: 120px; height: 60px; flex-direction: row;
}
.domino-tile.horizontal:hover { transform: translateY(-5px); }
.domino-half {
    width: 100%; height: 50%; display: grid; grid-template-columns: repeat(3, 1fr); grid-template-rows: repeat(3, 1fr);
    padding: 2px;
}
.domino-tile.horizontal .domino-half { width: 50%; height: 100%; }
.domino-divider { width: 100%; height: 3px; background: #000; }
.domino-tile.horizontal .domino-divider { width: 3px; height: 100%; }
.dot { background: #000; width: 10px; height: 10px; border-radius: 50%; margin: auto; }
.empty-dot { opacity: 0; }

.board-container {
    width: 100%; max-width: 900px; height: 400px; background: #00FF66; border: 4px solid #000;
    border-radius: 20px; box-shadow: 8px 8px 0px 0px rgba(0,0,0,1); margin: 20px 0;
    position: relative; overflow: hidden; display: flex; align-items: center; justify-content: center;
}
.board-track { position: relative; width: 0; height: 0; transition: transform 0.5s cubic-bezier(0.25, 1, 0.5, 1); }
.domino-tile.on-board { position: absolute; transform: none !important; cursor: default; }

.player-info { border: 3px solid #000; border-radius: 12px; padding: 10px; font-weight: 900; background: #fff; box-shadow: 3px 3px 0px 0px rgba(0,0,0,1); text-align: center; }
.player-info.active { background: #FFF000; animation: pulse 1s infinite; }
@keyframes pulse { 0% { transform: scale(1); } 50% { transform: scale(1.05); } 100% { transform: scale(1); } }

.my-hand { display: flex; gap: 10px; flex-wrap: wrap; justify-content: center; margin-top: 20px; padding: 20px; background: #FAF6EE; border: 3px solid #000; border-radius: 20px; box-shadow: inset 4px 4px 0px rgba(0,0,0,0.1); }

.modal-bg { position: fixed; inset: 0; background: rgba(0,0,0,0.8); display: flex; align-items: center; justify-content: center; z-index: 100; opacity: 0; pointer-events: none; transition: 0.2s; }
.modal-bg.active { opacity: 1; pointer-events: auto; }
.modal-box { background: #fff; border: 4px solid #000; border-radius: 20px; padding: 30px; box-shadow: 8px 8px 0px #000; max-width: 400px; text-align: center; }

input[type="text"] { font-family: 'Space Grotesk', sans-serif; font-weight: 700; border: 3px solid #000; padding: 12px; border-radius: 10px; width: 100%; font-size: 1.1rem; }
input[type="text"]:focus { outline: none; background: #FFF000; }
</style>
</head>
<body>

<nav class="bg-white border-b-4 border-black p-4 flex justify-between items-center sticky top-0 z-[50] shadow-[0px_4px_0px_0px_rgba(0,0,0,1)]">
  <div class="flex items-center gap-3 font-black text-2xl text-black uppercase tracking-tight">
    🎲 DOMINO
  </div>
  <a href="index.php" class="neo-btn btn-cyan text-sm py-2 px-4 shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]">⭠ KEMBALI</a>
</nav>

<!-- LOBBY SCREEN -->
<div id="lobby" class="screen active">
  <div class="glass-panel w-full max-w-md text-center">
    <h1 class="text-4xl font-black uppercase mb-2">Masuk Ruangan</h1>
    <div class="w-16 h-1 bg-black mx-auto mb-6"></div>
    
    <input type="text" id="player-name" placeholder="Masukkan Nama Anda..." class="mb-4">
    <input type="hidden" id="room-input" value="<?= htmlspecialchars($room_id) ?>">
    
    <?php if($room_id): ?>
      <p class="font-bold mb-4">Anda diundang ke Room: <span class="bg-brutal-yellow px-2 border-2 border-black"><?= htmlspecialchars($room_id) ?></span></p>
      <button class="neo-btn btn-green w-full text-lg mb-4" onclick="joinRoom()">GABUNG SEKARANG</button>
    <?php else: ?>
      <button class="neo-btn btn-yellow w-full text-lg mb-4" onclick="createRoom()">BUAT RUANGAN BARU</button>
      <div class="relative flex py-4 items-center">
        <div class="flex-grow border-t-2 border-black"></div>
        <span class="flex-shrink-0 mx-4 font-black">ATAU</span>
        <div class="flex-grow border-t-2 border-black"></div>
      </div>
      <input type="text" id="manual-room-id" placeholder="Kode Ruangan (Contoh: 4f2fe3)" class="mb-4">
      <button class="neo-btn btn-cyan w-full text-lg mb-4" onclick="manualJoin()">GABUNG RUANGAN</button>
    <?php endif; ?>
  </div>
</div>

<!-- WAITING SCREEN -->
<div id="waiting" class="screen">
  <div class="glass-panel w-full max-w-md text-center">
    <h2 class="text-3xl font-black uppercase mb-4">Menunggu Pemain</h2>
    <p class="font-bold mb-4">Bagikan link ini ke teman Anda (Max 4 Orang):</p>
    <div class="flex items-center gap-2 mb-6">
      <input type="text" id="invite-link" readonly class="text-sm bg-gray-100">
      <button class="neo-btn btn-pink py-3 px-4 text-sm" onclick="copyLink()">COPY</button>
    </div>
    
    <h3 class="font-black text-xl border-b-2 border-black pb-2 mb-4 text-left">Pemain Terkoneksi:</h3>
    <ul id="player-list" class="text-left font-bold text-lg space-y-2 mb-6">
      <!-- Players injected here -->
    </ul>
    
    <div class="flex justify-center">
      <div class="w-8 h-8 border-4 border-black border-t-brutal-yellow rounded-full animate-spin"></div>
    </div>
    <p class="mt-4 font-bold animate-pulse text-sm">Menunggu pemain lain masuk...</p>
  </div>
</div>

<!-- GAME SCREEN -->
<div id="game" class="screen w-full max-w-6xl mx-auto">
  <div class="flex justify-between items-center w-full mb-4">
    <div id="room-info" class="font-black bg-white border-3 border-black px-4 py-2 rounded-lg shadow-[3px_3px_0px_0px_rgba(0,0,0,1)]">ROOM: -</div>
    <div id="status-info" class="font-black text-xl uppercase bg-brutal-yellow border-3 border-black px-6 py-2 rounded-lg shadow-[3px_3px_0px_0px_rgba(0,0,0,1)]">Menunggu Giliran</div>
  </div>

  <div class="grid grid-cols-3 gap-4 w-full mb-4">
    <div id="p-left" class="player-info hidden">Kiri: 0 Kartu</div>
    <div id="p-top" class="player-info hidden">Atas: 0 Kartu</div>
    <div id="p-right" class="player-info hidden">Kanan: 0 Kartu</div>
  </div>

  <div class="board-container" id="board-container">
    <div class="board-track" id="board-track">
      <!-- Dominoes on board -->
    </div>
  </div>

  <div class="flex justify-between items-center w-full mt-4">
    <div id="p-me" class="player-info active text-xl border-4 px-6 py-3">Nama Saya</div>
    <button id="btn-pass" class="neo-btn btn-pink" onclick="passTurn()" disabled>LEWATI GILIRAN</button>
  </div>

  <div class="my-hand w-full" id="my-hand">
    <!-- Player's dominoes -->
  </div>
</div>

<!-- ACTION MODAL (Left or Right placement) -->
<div class="modal-bg" id="action-modal">
  <div class="modal-box">
    <h2 class="text-2xl font-black uppercase mb-4">Pilih Sisi</h2>
    <p class="font-bold mb-6">Di mana Anda ingin meletakkan balok ini?</p>
    <div class="flex gap-4 justify-center">
      <button class="neo-btn btn-cyan" onclick="confirmPlay('left')">⬅️ KIRI</button>
      <button class="neo-btn btn-green" onclick="confirmPlay('right')">KANAN ➡️</button>
    </div>
    <button class="mt-6 font-bold underline" onclick="closeModal()">Batal</button>
  </div>
</div>

<!-- WINNER MODAL -->
<div class="modal-bg" id="winner-modal">
  <div class="modal-box">
    <h1 class="text-4xl font-black uppercase mb-4" id="win-title">GAME OVER</h1>
    <p class="font-bold text-xl mb-6" id="win-desc"></p>
    <button class="neo-btn btn-yellow w-full" onclick="nextRound()" id="btn-next-round">RONDE BERIKUTNYA</button>
  </div>
</div>

<!-- PAUSE MODAL -->
<div class="modal-bg" id="pause-modal">
  <div class="modal-box">
    <h2 class="text-3xl font-black uppercase mb-4 text-brutal-pink animate-pulse">Menunggu...</h2>
    <p class="font-bold mb-4">Pemain <span id="disconnected-player-name" class="bg-brutal-yellow px-2 border-2 border-black">...</span> terputus!</p>
    <div class="text-5xl font-black mb-6" id="disconnect-timer">30</div>
    <p class="text-sm font-bold text-slate-600">Permainan dibatalkan jika waktu habis.</p>
  </div>
</div>


<script>
// Logic Frontend
const API_URL = 'api_domino.php';
let ROOM_ID = document.getElementById('room-input').value;
let PLAYER_ID = sessionStorage.getItem('domino_pid_' + ROOM_ID) || null;
let STATE = null;
let pollInterval = null;
let selectedTile = null;

function showScreen(id) {
    document.querySelectorAll('.screen').forEach(s => s.classList.remove('active'));
    document.getElementById(id).classList.add('active');
}

function renderDots(value) {
    const dots = Array(9).fill(false);
    if(value===1) { dots[4]=true; }
    else if(value===2) { dots[0]=true; dots[8]=true; }
    else if(value===3) { dots[0]=true; dots[4]=true; dots[8]=true; }
    else if(value===4) { dots[0]=true; dots[2]=true; dots[6]=true; dots[8]=true; }
    else if(value===5) { dots[0]=true; dots[2]=true; dots[4]=true; dots[6]=true; dots[8]=true; }
    else if(value===6) { dots[0]=true; dots[2]=true; dots[3]=true; dots[5]=true; dots[6]=true; dots[8]=true; }
    
    let html = '';
    dots.forEach(d => {
        html += `<div class="dot ${d ? '' : 'empty-dot'}"></div>`;
    });
    return html;
}

function createTileElement(a, b, isHorizontal=false) {
    const div = document.createElement('div');
    div.className = `domino-tile ${isHorizontal ? 'horizontal' : ''}`;
    div.innerHTML = `
        <div class="domino-half">${renderDots(a)}</div>
        <div class="domino-divider"></div>
        <div class="domino-half">${renderDots(b)}</div>
    `;
    return div;
}

async function apiCall(action, data={}) {
    const formData = new URLSearchParams();
    formData.append('action', action);
    for(let k in data) formData.append(k, data[k]);
    
    const res = await fetch(API_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: formData
    });
    return await res.json();
}

async function createRoom() {
    const name = document.getElementById('player-name').value.trim() || 'Player 1';
    const res = await apiCall('create_room', { name });
    if(res.success) {
        ROOM_ID = res.room_id;
        PLAYER_ID = res.player_id;
        sessionStorage.setItem('domino_pid_' + ROOM_ID, PLAYER_ID);
        setupWaiting();
    }
}

async function joinRoom() {
    const name = document.getElementById('player-name').value.trim() || 'Player Join';
    const res = await apiCall('join_room', { room_id: ROOM_ID, name: name, player_id: PLAYER_ID || '' });
    if(res.success) {
        PLAYER_ID = res.player_id;
        sessionStorage.setItem('domino_pid_' + ROOM_ID, PLAYER_ID);
        setupWaiting();
    } else {
        alert(res.error);
    }
}

function manualJoin() {
    const rId = document.getElementById('manual-room-id').value.trim();
    if (!rId) {
        alert("Masukkan kode ruangan!");
        return;
    }
    ROOM_ID = rId;
    joinRoom();
}

function setupWaiting() {
    showScreen('waiting');
    const link = window.location.href.split('?')[0] + '?room=' + ROOM_ID;
    document.getElementById('invite-link').value = link;
    startPolling();
}

function copyLink() {
    const el = document.getElementById('invite-link');
    el.select();
    document.execCommand('copy');
    alert('Link disalin!');
}

function startPolling() {
    if(pollInterval) clearInterval(pollInterval);
    pollInterval = setInterval(fetchState, 2000);
    fetchState();
}

async function fetchState() {
    const res = await fetch(`${API_URL}?action=get_state&room_id=${ROOM_ID}&player_id=${PLAYER_ID}`);
    const data = await res.json();
    if(data.success) {
        STATE = data;
        updateUI();
    }
}

function updateUI() {
    if(STATE.status === 'waiting') {
        const list = document.getElementById('player-list');
        list.innerHTML = '';
        STATE.players.forEach(p => {
            list.innerHTML += `<li>✅ ${p.name} ${p.id===PLAYER_ID ? '(Anda)' : ''}</li>`;
        });
    } else if(STATE.status === 'playing' || STATE.status === 'finished' || STATE.status === 'paused') {
        if(document.getElementById('waiting').classList.contains('active') || document.getElementById('lobby').classList.contains('active')) {
            showScreen('game');
        }
        
        document.getElementById('room-info').innerText = 'ROOM: ' + ROOM_ID;
        
        // Find my index
        let myIdx = STATE.players.findIndex(p => p.id === PLAYER_ID);
        if(myIdx===-1) myIdx=0;
        
        // Positions relative to me (0=Me, 1=Right, 2=Top, 3=Left)
        const posEls = [document.getElementById('p-me'), document.getElementById('p-right'), document.getElementById('p-top'), document.getElementById('p-left')];
        
        STATE.players.forEach((p, i) => {
            let relPos = (i - myIdx + 4) % 4;
            let el = posEls[relPos];
            el.classList.remove('hidden');
            let score = STATE.scores && STATE.scores[p.id] ? STATE.scores[p.id] : 0;
            el.innerText = `${p.name}: ${STATE.hand_counts[p.id]} Kartu [Skor: ${score}]`;
            
            if(STATE.turn_index === i) {
                el.classList.add('active');
            } else {
                el.classList.remove('active');
            }
        });
        
        // My Turn Status
        const isMyTurn = (STATE.players[STATE.turn_index].id === PLAYER_ID);
        const statusEl = document.getElementById('status-info');
        const passBtn = document.getElementById('btn-pass');
        
        let hasValidMove = false;
        if (STATE.left_end === null) {
            if ((STATE.round || 1) === 1) {
                STATE.my_hand.forEach(t => {
                    if (t[0] === 6 && t[1] === 6) hasValidMove = true;
                });
            } else {
                hasValidMove = true;
            }
        } else {
            STATE.my_hand.forEach(t => {
                if (t[0] === STATE.left_end || t[1] === STATE.left_end || t[0] === STATE.right_end || t[1] === STATE.right_end) {
                    hasValidMove = true;
                }
            });
        }

        if(isMyTurn) {
            statusEl.innerText = "GILIRAN ANDA!";
            statusEl.className = "font-black text-xl uppercase bg-brutal-green border-3 border-black px-6 py-2 rounded-lg shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] animate-pulse";
            
            if (!hasValidMove) {
                passBtn.disabled = false; // Enable button so user can manually skip if they want
                statusEl.innerText = "AUTO SKIP...";
                statusEl.className = "font-black text-xl uppercase bg-brutal-pink border-3 border-black px-6 py-2 rounded-lg shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] text-white";
                if (!window.autoPassTimer) {
                    window.autoPassTimer = setTimeout(() => passTurn(true), 1500);
                }
            } else {
                passBtn.disabled = true; // Disable button because they MUST play
                if (window.autoPassTimer) {
                    clearTimeout(window.autoPassTimer);
                    window.autoPassTimer = null;
                }
            }
        } else {
            if (window.autoPassTimer) {
                clearTimeout(window.autoPassTimer);
                window.autoPassTimer = null;
            }
            statusEl.innerText = "Menunggu Giliran...";
            statusEl.className = "font-black text-xl uppercase bg-white border-3 border-black px-6 py-2 rounded-lg shadow-[3px_3px_0px_0px_rgba(0,0,0,1)]";
            passBtn.disabled = true;
        }
        
        // Render My Hand
        const handEl = document.getElementById('my-hand');
        handEl.innerHTML = '';
        STATE.my_hand.forEach(tile => {
            const tEl = createTileElement(tile[0], tile[1]);
            tEl.onclick = () => playTile(tile);
            
            let valid = false;
            if (STATE.left_end === null) {
                if ((STATE.round || 1) === 1) {
                    if (tile[0] === 6 && tile[1] === 6) valid = true;
                } else {
                    valid = true;
                }
            }
            else if (tile[0] === STATE.left_end || tile[1] === STATE.left_end || tile[0] === STATE.right_end || tile[1] === STATE.right_end) valid = true;
            
            if(!isMyTurn) {
                tEl.style.opacity = '0.5';
            } else if(valid) {
                tEl.style.boxShadow = '0 0 15px 5px rgba(0, 255, 102, 0.8)';
                tEl.style.borderColor = '#00FF66';
            }
            handEl.appendChild(tEl);
        });
        
        // Render Board (Snaking & Auto-Zoom)
        const boardTrack = document.getElementById('board-track');
        boardTrack.innerHTML = '';
        
        const T_W = 60, T_H = 120, G = 4;
        let minX = 0, maxX = 0, minY = 0, maxY = 0;
        
        let lastL = null;
        let lastR = null;
        
        STATE.board.forEach((item, idx) => {
            let x, y, w, h;
            let isDouble = (item.tile[0] === item.tile[1]);
            
            if (idx === 0) {
                // First tile (Center)
                x = 0; y = 0;
                w = isDouble ? T_W : T_H;
                h = isDouble ? T_H : T_W;
                
                lastL = { x, y, wasDouble: isDouble, dx: -1, dy: 0 };
                lastR = { x, y, wasDouble: isDouble, dx: 1, dy: 0 };
            } else {
                let p = item.side === 'left' ? lastL : lastR;
                
                let old_dx = p.dx, old_dy = p.dy;
                
                // Advanced Snaking logic (Left upper half, Right lower half)
                if (item.side === 'left') {
                    if (p.dx === -1 && p.x < -200) { p.dx = 0; p.dy = -1; }
                    else if (p.dx === 1 && p.x > 200) { p.dx = 0; p.dy = -1; }
                    else if (p.dy === -1) {
                        if (p.x < 0) { p.dx = 1; p.dy = 0; }
                        else { p.dx = -1; p.dy = 0; }
                    }
                } else {
                    if (p.dx === 1 && p.x > 200) { p.dx = 0; p.dy = 1; }
                    else if (p.dx === -1 && p.x < -200) { p.dx = 0; p.dy = 1; }
                    else if (p.dy === 1) {
                        if (p.x > 0) { p.dx = -1; p.dy = 0; }
                        else { p.dx = 1; p.dy = 0; }
                    }
                }
                
                let movingHoriz = (p.dy === 0);
                let isHorizontal = movingHoriz ? !isDouble : isDouble;
                w = isHorizontal ? T_H : T_W;
                h = isHorizontal ? T_W : T_H;
                
                let out_cx = p.x;
                let out_cy = p.y;
                if (!p.wasDouble) {
                    out_cx += old_dx * 30;
                    out_cy += old_dy * 30;
                }
                
                let edgeX = out_cx;
                let edgeY = out_cy;
                if (p.dx === -1) edgeX -= 30;
                else if (p.dx === 1) edgeX += 30;
                else if (p.dy === -1) edgeY -= 30;
                else if (p.dy === 1) edgeY += 30;
                
                let new_in_cx = edgeX + p.dx * (30 + G);
                let new_in_cy = edgeY + p.dy * (30 + G);
                
                if (isDouble) {
                    x = new_in_cx;
                    y = new_in_cy;
                } else {
                    x = new_in_cx + p.dx * 30;
                    y = new_in_cy + p.dy * 30;
                }
                
                p.x = x; p.y = y; p.wasDouble = isDouble;
            }
            
            let isHorizontal = (w === T_H);
            
            minX = Math.min(minX, x - w/2);
            maxX = Math.max(maxX, x + w/2);
            minY = Math.min(minY, y - h/2);
            maxY = Math.max(maxY, y + h/2);
            
            const tEl = createTileElement(item.tile[0], item.tile[1], isHorizontal);
            tEl.classList.add('on-board');
            tEl.style.left = (x - w/2) + 'px';
            tEl.style.top = (y - h/2) + 'px';
            
            // Visual reversing for correct dot alignment
            if (idx > 0) {
                let p = item.side === 'left' ? lastL : lastR;
                if (item.side === 'left') {
                    if (p.dx === 1) tEl.style.flexDirection = 'row-reverse';
                    if (p.dy === 1) tEl.style.flexDirection = 'column-reverse';
                } else {
                    if (p.dx === -1) tEl.style.flexDirection = 'row-reverse';
                    if (p.dy === -1) tEl.style.flexDirection = 'column-reverse';
                }
            }
            
            boardTrack.appendChild(tEl);
        });
        
        // Calculate Scale for Auto-Zoom
        let bw = Math.max(200, maxX - minX + 80);
        let bh = Math.max(200, maxY - minY + 80);
        let contW = document.getElementById('board-container').clientWidth;
        let contH = document.getElementById('board-container').clientHeight;
        
        let scale = Math.min(1.2, Math.min(contW / bw, contH / bh));
        boardTrack.style.transform = `scale(${scale})`;
        
        // Handle Game Statuses (Paused, Aborted, Finished)
        if(STATE.status === 'paused') {
            document.getElementById('disconnected-player-name').innerText = STATE.disconnected_player || 'Seseorang';
            let remain = Math.max(0, STATE.disconnect_timer - STATE.current_time);
            document.getElementById('disconnect-timer').innerText = remain;
            document.getElementById('pause-modal').classList.add('active');
        } else if(STATE.status === 'aborted') {
            document.getElementById('pause-modal').classList.remove('active');
            if(!window.abortedAlerted) {
                window.abortedAlerted = true;
                setTimeout(() => {
                    alert("Permainan dibatalkan karena pemain terputus terlalu lama.");
                    window.location.href = 'index.php';
                }, 100);
            }
        } else {
            document.getElementById('pause-modal').classList.remove('active');
        }

        if(STATE.status === 'finished') {
            document.getElementById('winner-modal').classList.add('active');
            
            let winnerName = 'Unknown';
            if(STATE.winner !== 'draw') {
                const w = STATE.players.find(p => p.id === STATE.winner);
                winnerName = w ? w.name : 'Unknown';
            }
            
            if (STATE.is_deadlock) {
                document.getElementById('win-title').innerText = "KANDANG (DEADLOCK)!";
                document.getElementById('win-desc').innerText = `Permainan buntu!\n${winnerName} menang karena memiliki sisa kartu & bulatan paling sedikit.`;
            } else {
                document.getElementById('win-title').innerText = "GAME OVER";
                document.getElementById('win-desc').innerText = `Pemenang Ronde Ini: ${winnerName}!\nSemua pemain mendapatkan +1 Poin jika menang.`;
            }
            
            // Only show Next Round button to the winner (or player 0 if draw/unknown for fallback)
            let isWinner = (STATE.winner === PLAYER_ID) || (!STATE.winner && STATE.players[0].id === PLAYER_ID);
            document.getElementById('btn-next-round').style.display = isWinner ? 'block' : 'none';
        } else {
            document.getElementById('winner-modal').classList.remove('active');
        }
    }
}

function playTile(tile) {
    if(STATE.players[STATE.turn_index].id !== PLAYER_ID) return;
    
    // Check valid placement
    const le = STATE.left_end;
    const re = STATE.right_end;
    
    if(le === null) {
        // First move, always right/center
        executePlay(tile, 'right');
        return;
    }
    
    const canLeft = (tile[0] === le || tile[1] === le);
    const canRight = (tile[0] === re || tile[1] === re);
    
    if(canLeft && canRight && le !== re) {
        selectedTile = tile;
        document.getElementById('action-modal').classList.add('active');
    } else if(canLeft) {
        executePlay(tile, 'left');
    } else if(canRight) {
        executePlay(tile, 'right');
    } else {
        alert("Balok ini tidak cocok di meja!");
    }
}

function closeModal() {
    document.getElementById('action-modal').classList.remove('active');
    selectedTile = null;
}

function confirmPlay(side) {
    if(!selectedTile) return;
    executePlay(selectedTile, side);
    closeModal();
}

async function executePlay(tile, side) {
    // Optimistic UI update could go here
    const res = await apiCall('play_card', {
        room_id: ROOM_ID,
        player_id: PLAYER_ID,
        tile: JSON.stringify(tile),
        side: side
    });
    
    if(!res.success) {
        alert(res.error);
    } else {
        fetchState();
    }
}

async function passTurn(auto = false) {
    if(!auto && !confirm("Yakin ingin melewati giliran (Hanya jika benar-benar tidak ada kartu yang cocok)?")) return;
    
    // Clear timer reference so it can be re-triggered if this fails
    window.autoPassTimer = null;
    
    const res = await apiCall('pass', {
        room_id: ROOM_ID,
        player_id: PLAYER_ID
    });
    
    if(!res.success) {
        alert(res.error);
    } else {
        fetchState();
    }
}

async function nextRound() {
    document.getElementById('btn-next-round').innerText = "MENYIAPKAN...";
    document.getElementById('btn-next-round').disabled = true;
    const res = await apiCall('next_round', { room_id: ROOM_ID });
    if(res.success) {
        fetchState();
        setTimeout(() => {
            document.getElementById('btn-next-round').innerText = "RONDE BERIKUTNYA";
            document.getElementById('btn-next-round').disabled = false;
        }, 2000);
    } else {
        alert(res.error);
        document.getElementById('btn-next-round').innerText = "RONDE BERIKUTNYA";
        document.getElementById('btn-next-round').disabled = false;
    }
}

// Auto join if URL has room and we have player ID
if(ROOM_ID && PLAYER_ID) {
    // Attempt re-join
    joinRoom();
}

window.addEventListener('beforeunload', function() {
    if (ROOM_ID && PLAYER_ID && STATE && STATE.status === 'waiting') {
        const formData = new FormData();
        formData.append('action', 'leave_room');
        formData.append('room_id', ROOM_ID);
        formData.append('player_id', PLAYER_ID);
        navigator.sendBeacon(API_URL, formData);
    }
});
</script>

</body>
</html>
