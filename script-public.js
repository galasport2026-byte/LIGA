// ========================================
// DATA STORAGE
// ========================================
let matches = [];
let teams = [];
let scorers = [];
let currentSection = 'partidos';

// ========================================
// INITIALIZE
// ========================================
document.addEventListener('DOMContentLoaded', function() {
    loadData();
    loadMainLogo();
    applyPublicConfig();
    renderAll();
    
    // Auto refresh every 30 seconds to get latest data
    setInterval(() => {
        loadData();
        applyPublicConfig();
        renderAll();
    }, 30000);
});

// ========================================
// PUBLIC CONFIG APPLY
// ========================================
function applyPublicConfig() {
    const saved = localStorage.getItem('ligaMFMConfig');
    if (!saved) return;
    const config = JSON.parse(saved);
    const root = document.documentElement;
    if (config.colorPrimary) root.style.setProperty('--primary', config.colorPrimary);
    if (config.colorSecondary) root.style.setProperty('--secondary', config.colorSecondary);
    if (config.colorAccent) root.style.setProperty('--accent', config.colorAccent);
    if (config.colorHighlight) root.style.setProperty('--highlight', config.colorHighlight);

    // Force pageBg background
    if (config.colorPrimary && config.colorSecondary) {
        const pageBg = document.getElementById('pageBg');
        if (pageBg) pageBg.style.background = `linear-gradient(135deg, ${config.colorPrimary} 0%, ${config.colorSecondary} 50%, ${config.colorPrimary} 100%)`;
        const header = document.querySelector('.header');
        if (header) header.style.background = `linear-gradient(135deg, ${config.colorPrimary} 0%, ${config.colorSecondary} 100%)`;
    }

    // Update ADMIN button colors
    const adminBtn = document.getElementById('adminBtn');
    if (adminBtn && config.colorPrimary && config.colorSecondary) {
        adminBtn.style.background = `linear-gradient(135deg, ${config.colorPrimary}, ${config.colorSecondary})`;
        adminBtn.style.color = config.colorAccent || '#d4af37';
        adminBtn.style.borderColor = config.colorAccent || '#d4af37';
    }

    const h1 = document.querySelector('.header h1');
    const p = document.querySelector('.header p');
    if (h1 && config.leagueName) h1.textContent = config.leagueName;
    if (p && config.leagueSubtitle) p.textContent = config.leagueSubtitle;
    if (config.leagueName) document.title = config.leagueName + ' - ' + (config.leagueSubtitle || '');
    const standingsTitle = document.querySelector('#posiciones .section-title');
    if (standingsTitle && config.season) standingsTitle.textContent = '🏆 TABLA DE POSICIONES - TEMPORADA ' + config.season;
}

// ========================================
// MAIN LOGO
// ========================================
function loadMainLogo() {
    const mainLogo = document.getElementById('mainLogo');
    const savedLogo = localStorage.getItem('ligaMFMLogo');
    if (savedLogo) {
        mainLogo.src = savedLogo;
    } else {
        mainLogo.src = 'Logo MFM.png';
    }
}

// ========================================
// SECTION NAVIGATION
// ========================================
function showSection(sectionName) {
    currentSection = sectionName;
    
    document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
    event.target.classList.add('active');
    
    document.querySelectorAll('.section').forEach(section => section.classList.remove('active'));
    document.getElementById(sectionName).classList.add('active');
}

// ========================================
// RENDER FUNCTIONS
// ========================================
function renderAll() {
    renderMatches();
    renderStandings();
    renderScorers();
}

function renderMatches() {
    const container = document.getElementById('matches-container');
    
    if (matches.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <div class="empty-state-icon">⚽</div>
                <div class="empty-state-text">No hay partidos programados</div>
                <p style="margin-top: 10px; color: #999;">Próximamente se agregarán los partidos</p>
            </div>
        `;
        return;
    }
    
    matches.sort((a, b) => new Date(a.date + ' ' + a.time) - new Date(b.date + ' ' + b.time));
    
    container.innerHTML = matches.map(match => {
        const matchDate = new Date(match.date);
        const formattedDate = matchDate.toLocaleDateString('es-ES', { 
            weekday: 'short', 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric' 
        });
        
        return `
            <div class="match-card">
                <div class="match-header">
                    <div class="match-date">${formattedDate}</div>
                    <div class="match-time">${match.time}</div>
                </div>
                <div class="match-teams">
                    <div class="team">
                        <img src="${match.homeLogo || getDefaultLogo()}" alt="${match.homeTeam}" class="team-logo">
                        <div class="team-name">${match.homeTeam}</div>
                    </div>
                    <div class="vs">VS</div>
                    <div class="team">
                        <img src="${match.awayLogo || getDefaultLogo()}" alt="${match.awayTeam}" class="team-logo">
                        <div class="team-name">${match.awayTeam}</div>
                    </div>
                </div>
            </div>
        `;
    }).join('');
}

function renderStandings() {
    const tbody = document.getElementById('standings-tbody');
    
    if (teams.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="10" style="text-align: center; padding: 40px;">
                    <div class="empty-state">
                        <div class="empty-state-icon">🏆</div>
                        <div class="empty-state-text">Tabla de posiciones en preparación</div>
                        <p style="margin-top: 10px; color: #999;">Próximamente se actualizará la tabla</p>
                    </div>
                </td>
            </tr>
        `;
        return;
    }
    
    teams.sort((a, b) => {
        if (b.pts !== a.pts) return b.pts - a.pts;
        if (b.dg !== a.dg) return b.dg - a.dg;
        return b.gf - a.gf;
    });
    
    tbody.innerHTML = teams.map((team, index) => `
        <tr>
            <td class="position">${index + 1}</td>
            <td>
                <div class="team-info">
                    <img src="${team.logo || getDefaultLogo()}" alt="${team.name}" class="table-team-logo">
                    <span class="table-team-name">${team.name}</span>
                </div>
            </td>
            <td class="center">${team.pj}</td>
            <td class="center">${team.g}</td>
            <td class="center">${team.e}</td>
            <td class="center">${team.p}</td>
            <td class="center">${team.gf}</td>
            <td class="center">${team.gc}</td>
            <td class="center">${team.dg > 0 ? '+' : ''}${team.dg}</td>
            <td class="center"><span class="points">${team.pts}</span></td>
        </tr>
    `).join('');
}

function renderScorers() {
    const tbody = document.getElementById('scorers-tbody');
    
    if (scorers.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="4" style="text-align: center; padding: 40px;">
                    <div class="empty-state">
                        <div class="empty-state-icon">👟</div>
                        <div class="empty-state-text">Tabla de goleadores en preparación</div>
                        <p style="margin-top: 10px; color: #999;">Próximamente se actualizarán los goleadores</p>
                    </div>
                </td>
            </tr>
        `;
        return;
    }
    
    scorers.sort((a, b) => b.goals - a.goals);
    
    tbody.innerHTML = scorers.map((scorer, index) => {
        let medalEmoji = '';
        if (index === 0) medalEmoji = '🥇 ';
        else if (index === 1) medalEmoji = '🥈 ';
        else if (index === 2) medalEmoji = '🥉 ';
        
        return `
            <tr>
                <td class="position">${index + 1}</td>
                <td>
                    <span style="font-weight: 600; font-size: 1.05em;">${medalEmoji}${scorer.name}</span>
                </td>
                <td>
                    <span class="table-team-name">${scorer.team}</span>
                </td>
                <td class="center">
                    <span class="goals-badge">${scorer.goals}</span>
                </td>
            </tr>
        `;
    }).join('');
}

function getDefaultLogo() {
    return 'data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="40" fill="%23e2e8f0"/><text x="50" y="60" font-size="30" fill="%236b7280" text-anchor="middle" font-family="Arial">?</text></svg>';
}

// ========================================
// LOCAL STORAGE (READ ONLY)
// ========================================
function loadData() {
    const savedMatches = localStorage.getItem('ligaMFMMatches');
    const savedTeams = localStorage.getItem('ligaMFMTeams');
    const savedScorers = localStorage.getItem('ligaMFMScorers');
    
    if (savedMatches) matches = JSON.parse(savedMatches);
    if (savedTeams) teams = JSON.parse(savedTeams);
    if (savedScorers) scorers = JSON.parse(savedScorers);
}
// ========================================
// FASE FINAL - PLAYOFFS (PUBLIC)
// ========================================
let playoffs = [];

const ROUND_ORDER_PUB = ['Octavos de Final', 'Cuartos de Final', 'Semifinal', 'Tercer Puesto', 'Final'];
const ROUND_ICONS_PUB = {
    'Octavos de Final': '⚔️',
    'Cuartos de Final': '🎯',
    'Semifinal': '🔥',
    'Tercer Puesto': '🥉',
    'Final': '🏆'
};

function loadPlayoffs() {
    const saved = localStorage.getItem('ligaMFMPlayoffs');
    if (saved) playoffs = JSON.parse(saved);
}

function renderBracketPublic() {
    const container = document.getElementById('bracket-container');
    if (!container) return;

    if (playoffs.length === 0) {
        container.innerHTML = `
            <div class="bracket-empty-state">
                <div class="empty-state-icon">🏅</div>
                <div class="empty-state-text">Fase Final próximamente</div>
                <p style="margin-top:10px;color:#999;">Los cruces se anunciarán al finalizar la fase de grupos</p>
            </div>`;
        return;
    }

    const rounds = ROUND_ORDER_PUB.filter(r => playoffs.some(p => p.round === r));
    container.innerHTML = `<div class="bracket-wrapper">${rounds.map(round => {
        const matches = playoffs.filter(p => p.round === round).sort((a, b) => a.matchNum - b.matchNum);
        const isFinal = round === 'Final';
        return `
            <div class="bracket-round ${isFinal ? 'es-final' : ''}">
                <div class="bracket-round-title">${ROUND_ICONS_PUB[round] || '⚽'} ${round}</div>
                <div class="bracket-matches">
                    ${matches.map(m => playoffCardPublic(m)).join('')}
                </div>
            </div>`;
    }).join('')}</div>`;
}

function playoffCardPublic(m) {
    const hg = m.homeGoals !== null && m.homeGoals !== undefined ? m.homeGoals : '-';
    const ag = m.awayGoals !== null && m.awayGoals !== undefined ? m.awayGoals : '-';
    const hWin = m.status === 'finalizado' && m.homeGoals > m.awayGoals;
    const aWin = m.status === 'finalizado' && m.awayGoals > m.homeGoals;
    const dateStr = m.date ? new Date(m.date).toLocaleDateString('es-ES', {weekday:'short', day:'2-digit', month:'short'}) : '';
    const timeStr = m.time || '';
    const statusLabel = { pendiente: '⏳ Por jugar', en_juego: '🔴 En juego', finalizado: '✅ Finalizado' };

    return `
        <div class="playoff-card ${m.status}">
            <div class="playoff-card-header">
                <span>Partido ${m.matchNum}${dateStr ? ' · ' + dateStr : ''}${timeStr ? ' · ' + timeStr : ''}</span>
                <span class="playoff-status-badge ${m.status}">${statusLabel[m.status] || ''}</span>
            </div>
            <div class="playoff-teams">
                <div class="playoff-team-row">
                    <span class="playoff-team-name ${hWin ? 'winner' : ''}">${hWin ? '🏆 ' : ''}${m.home}</span>
                    <span class="playoff-score ${hWin ? 'winner-score' : ''}">${hg}</span>
                </div>
                <div class="playoff-vs-line">VS</div>
                <div class="playoff-team-row">
                    <span class="playoff-team-name ${aWin ? 'winner' : ''}">${aWin ? '🏆 ' : ''}${m.away}</span>
                    <span class="playoff-score ${aWin ? 'winner-score' : ''}">${ag}</span>
                </div>
            </div>
        </div>`;
}

// Patch loadData and renderAll
const _pubOrigLoad = loadData;
loadData = function() {
    _pubOrigLoad();
    loadPlayoffs();
};

const _pubOrigRender = renderAll;
renderAll = function() {
    _pubOrigRender();
    renderBracketPublic();
};
