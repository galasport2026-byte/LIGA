// ========================================
// CONFIGURACIÓN - CAMBIA LA CONTRASEÑA AQUÍ
// ========================================
const ADMIN_PASSWORD = "kevin123"; // CAMBIA ESTA CONTRASEÑA

// ========================================
// DATA STORAGE
// ========================================
let matches = [];
let teams = [];
let scorers = [];
let currentSection = 'partidos';
let isLoggedIn = false;

// ========================================
// INITIALIZE
// ========================================
document.addEventListener('DOMContentLoaded', function() {
    // Apply saved config immediately so login screen background matches theme
    applyConfigOnLoad();
    checkLogin();
});

function applyConfigOnLoad() {
    const saved = localStorage.getItem('ligaMFMConfig');
    if (!saved) return;
    const config = JSON.parse(saved);

    // Set CSS variables immediately on :root
    const root = document.documentElement;
    if (config.colorPrimary) root.style.setProperty('--primary', config.colorPrimary);
    if (config.colorSecondary) root.style.setProperty('--secondary', config.colorSecondary);
    if (config.colorAccent) root.style.setProperty('--accent', config.colorAccent);
    if (config.colorHighlight) root.style.setProperty('--highlight', config.colorHighlight);

    // Force pageBg background
    const pageBg = document.getElementById('pageBg');
    if (pageBg && config.colorPrimary && config.colorSecondary) {
        pageBg.style.background = `linear-gradient(135deg, ${config.colorPrimary} 0%, ${config.colorSecondary} 50%, ${config.colorPrimary} 100%)`;
    }

    // Force login container elements directly (CSS vars may not repaint instantly)
    const loginContainer = document.querySelector('.login-container');
    if (loginContainer && config.colorAccent) {
        loginContainer.style.borderColor = config.colorAccent;
    }
    const loginH1 = document.querySelector('.login-container h1');
    if (loginH1) {
        if (config.colorPrimary) loginH1.style.color = config.colorPrimary;
        if (config.leagueName) loginH1.textContent = config.leagueName;
    }
    const loginBtn = document.querySelector('.login-container .btn-primary');
    if (loginBtn && config.colorPrimary) {
        loginBtn.style.background = config.colorPrimary;
    }
    const loginInput = document.querySelector('.login-container input[type="password"]');
    if (loginInput && config.colorPrimary) {
        loginInput.style.borderColor = config.colorPrimary;
    }
    const loginLabel = document.querySelector('.login-container label');
    if (loginLabel && config.colorPrimary) {
        loginLabel.style.color = config.colorPrimary;
    }

    // Update logo if custom saved
    const savedLogo = localStorage.getItem('ligaMFMLogo');
    if (savedLogo) {
        const loginLogo = document.querySelector('.login-logo');
        if (loginLogo) loginLogo.src = savedLogo;
    }
}

// ========================================
// LOGIN SYSTEM
// ========================================
function login(event) {
    event.preventDefault();
    const password = document.getElementById('adminPassword').value;
    
    if (password === ADMIN_PASSWORD) {
        isLoggedIn = true;
        sessionStorage.setItem('ligaMFMAdmin', 'true');
        document.getElementById('loginScreen').style.display = 'none';
        document.getElementById('mainApp').style.display = 'block';
        loadData();
        loadMainLogo();
        loadConfig();
        renderAll();
    } else {
        document.getElementById('loginError').style.display = 'block';
        document.getElementById('adminPassword').value = '';
    }
}

function logout() {
    if (confirm('¿Estás seguro de cerrar sesión?')) {
        sessionStorage.removeItem('ligaMFMAdmin');
        location.reload();
    }
}

function checkLogin() {
    if (sessionStorage.getItem('ligaMFMAdmin') === 'true') {
        isLoggedIn = true;
        document.getElementById('loginScreen').style.display = 'none';
        document.getElementById('mainApp').style.display = 'block';
        loadData();
        loadMainLogo();
        loadConfig();
        renderAll();
    }
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
// MODAL FUNCTIONS
// ========================================
function openAddModal() {
    if (currentSection === 'partidos') {
        openMatchModal();
    } else if (currentSection === 'posiciones') {
        openTeamModal();
    } else if (currentSection === 'estadisticas') {
        openScorerModal();
    }
}

function openMatchModal(matchId = null) {
    const modal = document.getElementById('matchModal');
    const form = document.getElementById('matchForm');
    form.reset();
    
    document.getElementById('homeLogoPreview').style.display = 'none';
    document.getElementById('awayLogoPreview').style.display = 'none';
    
    if (matchId !== null) {
        const match = matches.find(m => m.id === matchId);
        if (match) {
            document.getElementById('matchId').value = match.id;
            document.getElementById('matchDate').value = match.date;
            document.getElementById('matchTime').value = match.time;
            document.getElementById('homeTeam').value = match.homeTeam;
            document.getElementById('awayTeam').value = match.awayTeam;
            
            if (match.homeLogo) {
                document.getElementById('homeLogoPreview').src = match.homeLogo;
                document.getElementById('homeLogoPreview').style.display = 'block';
            }
            if (match.awayLogo) {
                document.getElementById('awayLogoPreview').src = match.awayLogo;
                document.getElementById('awayLogoPreview').style.display = 'block';
            }
        }
    }
    
    modal.classList.add('active');
}

function openTeamModal(teamId = null) {
    const modal = document.getElementById('teamModal');
    const form = document.getElementById('teamForm');
    form.reset();
    
    document.getElementById('teamLogoPreview').style.display = 'none';
    
    if (teamId !== null) {
        const team = teams.find(t => t.id === teamId);
        if (team) {
            document.getElementById('teamId').value = team.id;
            document.getElementById('teamName').value = team.name;
            document.getElementById('teamPJ').value = team.pj;
            document.getElementById('teamG').value = team.g;
            document.getElementById('teamE').value = team.e;
            document.getElementById('teamP').value = team.p;
            document.getElementById('teamGF').value = team.gf;
            document.getElementById('teamGC').value = team.gc;
            
            if (team.logo) {
                document.getElementById('teamLogoPreview').src = team.logo;
                document.getElementById('teamLogoPreview').style.display = 'block';
            }
        }
    }
    
    modal.classList.add('active');
}

function openScorerModal(scorerId = null) {
    const modal = document.getElementById('scorerModal');
    const form = document.getElementById('scorerForm');
    form.reset();
    
    if (scorerId !== null) {
        const scorer = scorers.find(s => s.id === scorerId);
        if (scorer) {
            document.getElementById('scorerId').value = scorer.id;
            document.getElementById('scorerName').value = scorer.name;
            document.getElementById('scorerTeam').value = scorer.team;
            document.getElementById('scorerGoals').value = scorer.goals;
        }
    }
    
    modal.classList.add('active');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('active');
}

// ========================================
// PREVIEW LOGO
// ========================================
function previewLogo(input, previewId) {
    const preview = document.getElementById(previewId);
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// ========================================
// SAVE FUNCTIONS
// ========================================
function saveMatch(event) {
    event.preventDefault();
    
    const matchId = document.getElementById('matchId').value;
    
    const matchData = {
        id: matchId || Date.now(),
        date: document.getElementById('matchDate').value,
        time: document.getElementById('matchTime').value,
        homeTeam: document.getElementById('homeTeam').value,
        awayTeam: document.getElementById('awayTeam').value,
        homeLogo: document.getElementById('homeLogoPreview').src,
        awayLogo: document.getElementById('awayLogoPreview').src
    };
    
    if (matchId) {
        const index = matches.findIndex(m => String(m.id) === String(matchId));
        matches[index] = matchData;
    } else {
        matches.push(matchData);
    }
    
    saveData();
    renderMatches();
    closeModal('matchModal');
}

function saveTeam(event) {
    event.preventDefault();
    
    const teamId = document.getElementById('teamId').value;
    const pj = parseInt(document.getElementById('teamPJ').value);
    const g = parseInt(document.getElementById('teamG').value);
    const e = parseInt(document.getElementById('teamE').value);
    const p = parseInt(document.getElementById('teamP').value);
    const gf = parseInt(document.getElementById('teamGF').value);
    const gc = parseInt(document.getElementById('teamGC').value);
    
    const teamData = {
        id: teamId || Date.now(),
        name: document.getElementById('teamName').value,
        logo: document.getElementById('teamLogoPreview').src || '',
        pj: pj,
        g: g,
        e: e,
        p: p,
        gf: gf,
        gc: gc,
        dg: gf - gc,
        pts: (g * 3) + e
    };
    
    if (teamId) {
        const index = teams.findIndex(t => String(t.id) === String(teamId));
        teams[index] = teamData;
    } else {
        teams.push(teamData);
    }
    
    saveData();
    renderStandings();
    closeModal('teamModal');
}

function saveScorer(event) {
    event.preventDefault();
    
    const scorerId = document.getElementById('scorerId').value;
    
    const scorerData = {
        id: scorerId || Date.now(),
        name: document.getElementById('scorerName').value,
        team: document.getElementById('scorerTeam').value,
        goals: parseInt(document.getElementById('scorerGoals').value)
    };
    
    if (scorerId) {
        const index = scorers.findIndex(s => String(s.id) === String(scorerId));
        scorers[index] = scorerData;
    } else {
        scorers.push(scorerData);
    }
    
    saveData();
    renderScorers();
    closeModal('scorerModal');
}

// ========================================
// DELETE FUNCTIONS
// ========================================
function deleteMatch(matchId) {
    if (confirm('¿Estás seguro de eliminar este partido?')) {
        matches = matches.filter(m => String(m.id) !== String(matchId));
        saveData();
        renderMatches();
    }
}

function deleteTeam(teamId) {
    if (confirm('¿Estás seguro de eliminar este equipo?')) {
        teams = teams.filter(t => String(t.id) !== String(teamId));
        saveData();
        renderStandings();
    }
}

function deleteScorer(scorerId) {
    if (confirm('¿Estás seguro de eliminar este goleador?')) {
        scorers = scorers.filter(s => String(s.id) !== String(scorerId));
        saveData();
        renderScorers();
    }
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
                <p style="margin-top: 10px; color: #999;">Haz clic en el botón + para agregar un partido</p>
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
                <div class="match-actions">
                    <button class="btn btn-secondary btn-small" onclick="openMatchModal('${match.id}')">✏️ Editar</button>
                    <button class="btn btn-danger btn-small" onclick="deleteMatch('${match.id}')">🗑️ Eliminar</button>
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
                <td colspan="11" style="text-align: center; padding: 40px;">
                    <div class="empty-state">
                        <div class="empty-state-icon">🏆</div>
                        <div class="empty-state-text">No hay equipos registrados</div>
                        <p style="margin-top: 10px; color: #999;">Haz clic en el botón + para agregar un equipo</p>
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
            <td class="center">
                <button class="btn btn-secondary btn-small" onclick="openTeamModal('${team.id}')" style="margin-right: 5px;">✏️</button>
                <button class="btn btn-danger btn-small" onclick="deleteTeam('${team.id}')">🗑️</button>
            </td>
        </tr>
    `).join('');
}

function renderScorers() {
    const tbody = document.getElementById('scorers-tbody');
    
    if (scorers.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" style="text-align: center; padding: 40px;">
                    <div class="empty-state">
                        <div class="empty-state-icon">👟</div>
                        <div class="empty-state-text">No hay goleadores registrados</div>
                        <p style="margin-top: 10px; color: #999;">Haz clic en el botón + para agregar un goleador</p>
                    </div>
                </td>
            </tr>
        `;
        return;
    }
    
    scorers.sort((a, b) => b.goals - a.goals);
    
    tbody.innerHTML = scorers.map((scorer, index) => `
        <tr>
            <td class="position">${index + 1}</td>
            <td>
                <span style="font-weight: 600; font-size: 1.05em;">${scorer.name}</span>
            </td>
            <td>
                <span class="table-team-name">${scorer.team}</span>
            </td>
            <td class="center">
                <span class="goals-badge">${scorer.goals}</span>
            </td>
            <td class="center">
                <button class="btn btn-secondary btn-small" onclick="openScorerModal('${scorer.id}')" style="margin-right: 5px;">✏️</button>
                <button class="btn btn-danger btn-small" onclick="deleteScorer('${scorer.id}')">🗑️</button>
            </td>
        </tr>
    `).join('');
}

function getDefaultLogo() {
    return 'data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="40" fill="%23e2e8f0"/><text x="50" y="60" font-size="30" fill="%236b7280" text-anchor="middle" font-family="Arial">?</text></svg>';
}

// ========================================
// LOCAL STORAGE
// ========================================
function saveData() {
    localStorage.setItem('ligaMFMMatches', JSON.stringify(matches));
    localStorage.setItem('ligaMFMTeams', JSON.stringify(teams));
    localStorage.setItem('ligaMFMScorers', JSON.stringify(scorers));
}

function loadData() {
    const savedMatches = localStorage.getItem('ligaMFMMatches');
    const savedTeams = localStorage.getItem('ligaMFMTeams');
    const savedScorers = localStorage.getItem('ligaMFMScorers');
    
    if (savedMatches) matches = JSON.parse(savedMatches);
    if (savedTeams) teams = JSON.parse(savedTeams);
    if (savedScorers) scorers = JSON.parse(savedScorers);
    
    if (matches.length === 0 && teams.length === 0 && scorers.length === 0) {
        loadSampleData();
    }
}

function loadSampleData() {
    teams = [
        { id: 1, name: 'Barcelona FC', logo: '', pj: 23, g: 19, e: 1, p: 3, gf: 63, gc: 23, dg: 40, pts: 58 },
        { id: 2, name: 'Real Madrid', logo: '', pj: 23, g: 18, e: 3, p: 2, gf: 49, gc: 18, dg: 31, pts: 57 },
        { id: 3, name: 'Atlético Madrid', logo: '', pj: 23, g: 13, e: 6, p: 4, gf: 38, gc: 18, dg: 20, pts: 45 },
        { id: 4, name: 'Villarreal', logo: '', pj: 22, g: 14, e: 3, p: 5, gf: 43, gc: 24, dg: 19, pts: 45 }
    ];
    
    scorers = [
        { id: 1, name: 'Cristiano Ronaldo', team: 'Barcelona FC', goals: 25 },
        { id: 2, name: 'Lionel Messi', team: 'Real Madrid', goals: 22 },
        { id: 3, name: 'Luis Suárez', team: 'Atlético Madrid', goals: 18 },
        { id: 4, name: 'Karim Benzema', team: 'Villarreal', goals: 15 },
        { id: 5, name: 'Antoine Griezmann', team: 'Barcelona FC', goals: 12 }
    ];
    
    const today = new Date();
    const tomorrow = new Date(today);
    tomorrow.setDate(tomorrow.getDate() + 1);
    const nextWeek = new Date(today);
    nextWeek.setDate(nextWeek.getDate() + 7);
    
    matches = [
        { 
            id: 1, 
            date: tomorrow.toISOString().split('T')[0], 
            time: '20:00', 
            homeTeam: 'Barcelona FC', 
            awayTeam: 'Real Madrid', 
            homeLogo: '', 
            awayLogo: '' 
        },
        { 
            id: 2, 
            date: nextWeek.toISOString().split('T')[0], 
            time: '18:30', 
            homeTeam: 'Atlético Madrid', 
            awayTeam: 'Villarreal', 
            homeLogo: '', 
            awayLogo: '' 
        }
    ];
    
    saveData();
}

// ========================================
// CLOSE MODAL ON OUTSIDE CLICK
// ========================================
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.classList.remove('active');
    }
}

// ========================================
// INLINE HEADER EDITING
// ========================================
function triggerLogoEdit() {
    document.getElementById('inlineLogoInput').click();
}

function applyInlineLogo(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            const src = e.target.result;
            document.getElementById('mainLogo').src = src;
            localStorage.setItem('ligaMFMLogo', src);
            showToast('✅ Escudo actualizado');
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function saveInlineName() {
    const val = document.getElementById('headerLeagueName').textContent.trim();
    if (!val) { document.getElementById('headerLeagueName').textContent = 'LIGA MFM'; return; }
    const config = getConfigFromStorage();
    config.leagueName = val;
    localStorage.setItem('ligaMFMConfig', JSON.stringify(config));
    document.title = val + ' - Administración';
    showToast('✅ Nombre guardado');
}

function saveInlineSubtitle() {
    const val = document.getElementById('headerLeagueSubtitle').textContent.trim();
    if (!val) { document.getElementById('headerLeagueSubtitle').textContent = 'MICRO FÚTBOL MONTECRISTO'; return; }
    const config = getConfigFromStorage();
    config.leagueSubtitle = val;
    localStorage.setItem('ligaMFMConfig', JSON.stringify(config));
    showToast('✅ Subtítulo guardado');
}

// ========================================
// FLOATING COLOR PANEL
// ========================================
let colorPanelOpen = false;

function toggleColorPanel() {
    const panel = document.getElementById('floatingColorPanel');
    colorPanelOpen = !colorPanelOpen;
    if (colorPanelOpen) {
        // Load current colors into panel
        const config = getConfigFromStorage();
        document.getElementById('fcpPrimary').value = config.colorPrimary;
        document.getElementById('fcpPrimaryHex').value = config.colorPrimary;
        document.getElementById('fcpSecondary').value = config.colorSecondary;
        document.getElementById('fcpSecondaryHex').value = config.colorSecondary;
        document.getElementById('fcpAccent').value = config.colorAccent;
        document.getElementById('fcpAccentHex').value = config.colorAccent;
        document.getElementById('fcpHighlight').value = config.colorHighlight;
        document.getElementById('fcpHighlightHex').value = config.colorHighlight;
        liveColorPreview();
        panel.style.display = 'block';
    } else {
        panel.style.display = 'none';
    }
}

function syncFcpHex(pickerId, hexId) {
    const hex = document.getElementById(hexId).value;
    if (/^#[0-9A-Fa-f]{6}$/.test(hex)) {
        document.getElementById(pickerId).value = hex;
        liveColorPreview();
    }
}

function liveColorPreview() {
    const p = document.getElementById('fcpPrimary').value;
    const s = document.getElementById('fcpSecondary').value;
    const a = document.getElementById('fcpAccent').value;
    const h = document.getElementById('fcpHighlight').value;

    // Sync hex fields with picker values
    document.getElementById('fcpPrimaryHex').value = p;
    document.getElementById('fcpSecondaryHex').value = s;
    document.getElementById('fcpAccentHex').value = a;
    document.getElementById('fcpHighlightHex').value = h;

    // Update preview bar
    document.getElementById('fcpPreviewBar').style.background =
        `linear-gradient(90deg, ${p}, ${s}, ${a}, ${h})`;

    // Update CSS variables on :root
    const root = document.documentElement;
    root.style.setProperty('--primary', p);
    root.style.setProperty('--secondary', s);
    root.style.setProperty('--accent', a);
    root.style.setProperty('--highlight', h);

    // Force-update elements that may not re-read CSS vars in real time
    const pageBg = document.getElementById('pageBg');
    if (pageBg) pageBg.style.background = `linear-gradient(135deg, ${p} 0%, ${s} 50%, ${p} 100%)`;

    const header = document.querySelector('.header');
    if (header) header.style.background = `linear-gradient(135deg, ${p} 0%, ${s} 100%)`;

    const tabsBar = document.querySelector('.tabs');
    if (tabsBar) tabsBar.style.borderBottomColor = a;

    const activeTabs = document.querySelectorAll('.tab.active::after');
    document.querySelectorAll('.tab.active').forEach(t => t.style.color = p);

    const sectionTitles = document.querySelectorAll('.section-title');
    sectionTitles.forEach(el => {
        el.style.color = p;
        el.style.borderBottomColor = a;
    });

    const tableHeaders = document.querySelectorAll('.standings-table thead');
    tableHeaders.forEach(el => el.style.background = `linear-gradient(135deg, ${p}, ${s})`);

    const points = document.querySelectorAll('.points');
    points.forEach(el => el.style.color = p);

    const positions = document.querySelectorAll('.position');
    positions.forEach(el => el.style.color = p);

    const goalsBadges = document.querySelectorAll('.goals-badge');
    goalsBadges.forEach(el => { el.style.background = h; });

    const matchTimes = document.querySelectorAll('.match-time');
    matchTimes.forEach(el => el.style.color = h);

    const vsEls = document.querySelectorAll('.vs');
    vsEls.forEach(el => el.style.color = a);

    const addBtn = document.querySelector('.add-btn');
    if (addBtn) addBtn.style.background = `linear-gradient(135deg, ${p}, ${s})`;
    if (addBtn) addBtn.style.borderColor = a;

    const palBtn = document.querySelector('.color-palette-btn');
    if (palBtn) palBtn.style.background = `linear-gradient(135deg, ${a}, ${a}dd)`;

    const fcpHeader = document.querySelector('.fcp-header');
    if (fcpHeader) fcpHeader.style.background = `linear-gradient(135deg, ${p}, ${s})`;

    const matchCards = document.querySelectorAll('.match-card::before');
    document.querySelectorAll('.match-card').forEach(card => {
        card.style.setProperty('--primary', p);
        card.style.setProperty('--accent', a);
    });
}

function saveFcpColors() {
    const config = getConfigFromStorage();
    config.colorPrimary = document.getElementById('fcpPrimary').value;
    config.colorSecondary = document.getElementById('fcpSecondary').value;
    config.colorAccent = document.getElementById('fcpAccent').value;
    config.colorHighlight = document.getElementById('fcpHighlight').value;
    localStorage.setItem('ligaMFMConfig', JSON.stringify(config));
    showToast('✅ Colores guardados');
    toggleColorPanel();
}

function resetFcpColors() {
    document.getElementById('fcpPrimary').value = DEFAULT_CONFIG.colorPrimary;
    document.getElementById('fcpSecondary').value = DEFAULT_CONFIG.colorSecondary;
    document.getElementById('fcpAccent').value = DEFAULT_CONFIG.colorAccent;
    document.getElementById('fcpHighlight').value = DEFAULT_CONFIG.colorHighlight;
    liveColorPreview();
}


const DEFAULT_CONFIG = {
    leagueName: 'LIGA MFM',
    leagueSubtitle: 'MICRO FÚTBOL MONTECRISTO',
    season: '2025-26',
    colorPrimary: '#0a5f0a',
    colorSecondary: '#1a7a1a',
    colorAccent: '#d4af37',
    colorHighlight: '#c41e3a'
};

function loadConfig() {
    const saved = localStorage.getItem('ligaMFMConfig');
    const config = saved ? JSON.parse(saved) : DEFAULT_CONFIG;
    applyConfig(config);
    populateConfigForm(config);
}

function applyConfig(config) {
    // Apply CSS variables
    const root = document.documentElement;
    root.style.setProperty('--primary', config.colorPrimary);
    root.style.setProperty('--secondary', config.colorSecondary);
    root.style.setProperty('--accent', config.colorAccent);
    root.style.setProperty('--highlight', config.colorHighlight);

    // Force pageBg background (dedicated div is 100% reliable vs body background)
    const pageBg = document.getElementById('pageBg');
    if (pageBg) pageBg.style.background = `linear-gradient(135deg, ${config.colorPrimary} 0%, ${config.colorSecondary} 50%, ${config.colorPrimary} 100%)`;

    // Force header background
    const header = document.querySelector('.header');
    if (header) header.style.background = `linear-gradient(135deg, ${config.colorPrimary} 0%, ${config.colorSecondary} 100%)`;

    // Apply names in header (inline editable elements)
    const nameEl = document.getElementById('headerLeagueName');
    const subEl = document.getElementById('headerLeagueSubtitle');
    if (nameEl) nameEl.textContent = config.leagueName;
    if (subEl) subEl.textContent = config.leagueSubtitle;

    // Update season in standings title
    const standingsTitle = document.querySelector('#posiciones .section-title');
    if (standingsTitle) standingsTitle.textContent = '🏆 TABLA DE POSICIONES - TEMPORADA ' + config.season;

    // Update page title
    document.title = config.leagueName + ' - Administración';
}

function populateConfigForm(config) {
    const el = id => document.getElementById(id);
    if (!el('configLeagueName')) return; // section not visible yet
    el('configLeagueName').value = config.leagueName;
    el('configLeagueSubtitle').value = config.leagueSubtitle;
    el('configSeason').value = config.season;
    el('configColorPrimary').value = config.colorPrimary;
    el('configColorPrimaryHex').value = config.colorPrimary;
    el('configColorSecondary').value = config.colorSecondary;
    el('configColorSecondaryHex').value = config.colorSecondary;
    el('configColorAccent').value = config.colorAccent;
    el('configColorAccentHex').value = config.colorAccent;
    el('configColorHighlight').value = config.colorHighlight;
    el('configColorHighlightHex').value = config.colorHighlight;
    updateColorPreview();

    // Logo
    const savedLogo = localStorage.getItem('ligaMFMLogo');
    if (savedLogo) el('configLogoPreview').src = savedLogo;
}

function getConfigFromStorage() {
    const saved = localStorage.getItem('ligaMFMConfig');
    return saved ? JSON.parse(saved) : { ...DEFAULT_CONFIG };
}

function saveConfigColors() {
    const config = getConfigFromStorage();
    config.colorPrimary = document.getElementById('configColorPrimaryHex').value || document.getElementById('configColorPrimary').value;
    config.colorSecondary = document.getElementById('configColorSecondaryHex').value || document.getElementById('configColorSecondary').value;
    config.colorAccent = document.getElementById('configColorAccentHex').value || document.getElementById('configColorAccent').value;
    config.colorHighlight = document.getElementById('configColorHighlightHex').value || document.getElementById('configColorHighlight').value;
    localStorage.setItem('ligaMFMConfig', JSON.stringify(config));
    applyConfig(config);
    showToast('✅ Colores guardados correctamente');
}

function saveConfigNames() {
    const config = getConfigFromStorage();
    config.leagueName = document.getElementById('configLeagueName').value.trim() || DEFAULT_CONFIG.leagueName;
    config.leagueSubtitle = document.getElementById('configLeagueSubtitle').value.trim() || DEFAULT_CONFIG.leagueSubtitle;
    config.season = document.getElementById('configSeason').value.trim() || DEFAULT_CONFIG.season;
    localStorage.setItem('ligaMFMConfig', JSON.stringify(config));
    applyConfig(config);
    showToast('✅ Nombres guardados correctamente');
}

function previewConfigLogo(input) {
    const preview = document.getElementById('configLogoPreview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => { preview.src = e.target.result; };
        reader.readAsDataURL(input.files[0]);
    }
}

function saveConfigLogo() {
    const preview = document.getElementById('configLogoPreview');
    if (preview.src && !preview.src.includes('Logo MFM.png')) {
        localStorage.setItem('ligaMFMLogo', preview.src);
        document.getElementById('mainLogo').src = preview.src;
        showToast('✅ Logo guardado correctamente');
    } else {
        showToast('⚠️ Selecciona una imagen primero');
    }
}

function syncColorFromPicker(pickerId, hexId) {
    document.getElementById(hexId).value = document.getElementById(pickerId).value;
    updateColorPreview();
}

function syncColor(pickerId, hexId) {
    const hex = document.getElementById(hexId).value;
    if (/^#[0-9A-Fa-f]{6}$/.test(hex)) {
        document.getElementById(pickerId).value = hex;
        updateColorPreview();
    }
}

function updateColorPreview() {
    const bar = document.getElementById('colorPreviewBar');
    if (!bar) return;
    const p = document.getElementById('configColorPrimaryHex')?.value || '#0a5f0a';
    const s = document.getElementById('configColorSecondaryHex')?.value || '#1a7a1a';
    const a = document.getElementById('configColorAccentHex')?.value || '#d4af37';
    const h = document.getElementById('configColorHighlightHex')?.value || '#c41e3a';
    bar.style.background = `linear-gradient(90deg, ${p}, ${s}, ${a}, ${h})`;
}

function resetConfig() {
    if (!confirm('¿Restablecer todos los ajustes al diseño original? Esto no borrará partidos, equipos ni goleadores.')) return;
    localStorage.removeItem('ligaMFMConfig');
    localStorage.removeItem('ligaMFMLogo');
    applyConfig(DEFAULT_CONFIG);
    populateConfigForm(DEFAULT_CONFIG);
    document.getElementById('mainLogo').src = 'Logo MFM.png';
    showToast('✅ Configuración restablecida');
}

function showToast(msg) {
    let toast = document.getElementById('configToast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'configToast';
        toast.style.cssText = 'position:fixed;bottom:120px;right:40px;background:#1a1a1a;color:white;padding:14px 24px;border-radius:10px;font-weight:600;font-size:1em;box-shadow:0 4px 20px rgba(0,0,0,0.4);z-index:9999;transition:opacity 0.4s;';
        document.body.appendChild(toast);
    }
    toast.textContent = msg;
    toast.style.opacity = '1';
    clearTimeout(toast._timeout);
    toast._timeout = setTimeout(() => { toast.style.opacity = '0'; }, 2800);
}
// ========================================
// FASE FINAL - PLAYOFFS
// ========================================
let playoffs = [];

const ROUND_ORDER = ['Octavos de Final', 'Cuartos de Final', 'Semifinal', 'Tercer Puesto', 'Final'];
const ROUND_ICONS = {
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

function savePlayoffs() {
    localStorage.setItem('ligaMFMPlayoffs', JSON.stringify(playoffs));
}

function openPlayoffModal(playoffId = null) {
    const form = document.getElementById('playoffForm');
    form.reset();
    document.getElementById('playoffId').value = '';

    if (playoffId !== null) {
        const p = playoffs.find(x => String(x.id) === String(playoffId));
        if (p) {
            document.getElementById('playoffId').value = p.id;
            document.getElementById('playoffRound').value = p.round;
            document.getElementById('playoffMatchNum').value = p.matchNum || 1;
            document.getElementById('playoffDate').value = p.date || '';
            document.getElementById('playoffTime').value = p.time || '';
            document.getElementById('playoffHome').value = p.home;
            document.getElementById('playoffAway').value = p.away;
            document.getElementById('playoffHomeGoals').value = p.homeGoals !== null && p.homeGoals !== undefined ? p.homeGoals : '';
            document.getElementById('playoffAwayGoals').value = p.awayGoals !== null && p.awayGoals !== undefined ? p.awayGoals : '';
            document.getElementById('playoffStatus').value = p.status || 'pendiente';
        }
    }
    document.getElementById('playoffModal').classList.add('active');
}

function savePlayoff(event) {
    event.preventDefault();
    const id = document.getElementById('playoffId').value;
    const homeGoals = document.getElementById('playoffHomeGoals').value;
    const awayGoals = document.getElementById('playoffAwayGoals').value;

    const data = {
        id: id || Date.now(),
        round: document.getElementById('playoffRound').value,
        matchNum: parseInt(document.getElementById('playoffMatchNum').value) || 1,
        date: document.getElementById('playoffDate').value,
        time: document.getElementById('playoffTime').value,
        home: document.getElementById('playoffHome').value.trim(),
        away: document.getElementById('playoffAway').value.trim(),
        homeGoals: homeGoals !== '' ? parseInt(homeGoals) : null,
        awayGoals: awayGoals !== '' ? parseInt(awayGoals) : null,
        status: document.getElementById('playoffStatus').value
    };

    if (id) {
        const idx = playoffs.findIndex(x => String(x.id) === String(id));
        playoffs[idx] = data;
    } else {
        playoffs.push(data);
    }

    savePlayoffs();
    renderBracketAdmin();
    closeModal('playoffModal');
    showToast('✅ Partido guardado');
}

function deletePlayoff(id) {
    if (!confirm('¿Eliminar este partido de fase final?')) return;
    playoffs = playoffs.filter(x => String(x.id) !== String(id));
    savePlayoffs();
    renderBracketAdmin();
}

function renderBracketAdmin() {
    const container = document.getElementById('bracket-admin-container');
    if (!container) return;

    if (playoffs.length === 0) {
        container.innerHTML = `
            <div class="bracket-empty-state">
                <div class="empty-state-icon">🏅</div>
                <div class="empty-state-text">No hay partidos de fase final registrados</div>
                <p style="margin-top:10px;color:#999;">Usa el botón de arriba para agregar partidos</p>
            </div>`;
        return;
    }

    const rounds = ROUND_ORDER.filter(r => playoffs.some(p => p.round === r));
    container.innerHTML = rounds.map(round => {
        const matches = playoffs
            .filter(p => p.round === round)
            .sort((a, b) => a.matchNum - b.matchNum);

        const isFinal = round === 'Final';
        return `
            <div class="bracket-round ${isFinal ? 'es-final' : ''}">
                <div class="bracket-round-title">${ROUND_ICONS[round] || '⚽'} ${round}</div>
                <div class="bracket-matches">
                    ${matches.map(m => playoffCardAdmin(m)).join('')}
                </div>
            </div>`;
    }).join('<div class="bracket-wrapper">') + '</div>'.repeat(rounds.length > 0 ? 1 : 0);
    container.innerHTML = `<div class="bracket-wrapper">${rounds.map(round => {
        const matches = playoffs.filter(p => p.round === round).sort((a, b) => a.matchNum - b.matchNum);
        const isFinal = round === 'Final';
        return `
            <div class="bracket-round ${isFinal ? 'es-final' : ''}">
                <div class="bracket-round-title">${ROUND_ICONS[round] || '⚽'} ${round}</div>
                <div class="bracket-matches">
                    ${matches.map(m => playoffCardAdmin(m)).join('')}
                </div>
            </div>`;
    }).join('')}</div>`;
}

function playoffCardAdmin(m) {
    const hg = m.homeGoals !== null && m.homeGoals !== undefined ? m.homeGoals : '-';
    const ag = m.awayGoals !== null && m.awayGoals !== undefined ? m.awayGoals : '-';
    const hWin = m.status === 'finalizado' && m.homeGoals > m.awayGoals;
    const aWin = m.status === 'finalizado' && m.awayGoals > m.homeGoals;
    const dateStr = m.date ? new Date(m.date).toLocaleDateString('es-ES', {day:'2-digit', month:'short'}) : '';
    const timeStr = m.time || '';
    const statusLabel = { pendiente: '⏳ Por jugar', en_juego: '🔴 En juego', finalizado: '✅ Finalizado' };

    return `
        <div class="playoff-card ${m.status}">
            <div class="playoff-card-header">
                <span>Partido ${m.matchNum}${dateStr ? ' · ' + dateStr : ''}${timeStr ? ' ' + timeStr : ''}</span>
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
            <div class="playoff-card-actions">
                <button class="btn btn-secondary btn-small" onclick="openPlayoffModal('${m.id}')" style="flex:1;">✏️ Editar</button>
                <button class="btn btn-danger btn-small" onclick="deletePlayoff('${m.id}')" style="flex:1;">🗑️ Eliminar</button>
            </div>
        </div>`;
}

// Patch renderAll and loadData to include playoffs
const _origLoadData = loadData;
loadData = function() {
    _origLoadData();
    loadPlayoffs();
};

const _origRenderAll = renderAll;
renderAll = function() {
    _origRenderAll();
    renderBracketAdmin();
};
