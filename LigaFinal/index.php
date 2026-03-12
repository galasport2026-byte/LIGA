<?php
require_once 'includes/config.php';

// ---- Cargar datos ----
try {
    // Configuración
    $cfg = obtener_config($conn);

    // Equipos
    $teams = $conn->query("SELECT * FROM tabla ORDER BY pts DESC, dg DESC, gf DESC")->fetchAll();

    // Próximos partidos
    $matches = $conn->query(
        "SELECT * FROM partidos WHERE match_date >= CURDATE() ORDER BY match_date ASC, match_time ASC LIMIT 10"
    )->fetchAll();

    // Goleadores
    $scorers = $conn->query("SELECT * FROM goleadores ORDER BY goals DESC LIMIT 20")->fetchAll();

    // Fase Final
    $roundOrder = "FIELD(ronda,'Octavos de Final','Cuartos de Final','Semifinal','Tercer Puesto','Final')";
    $playoffs   = $conn->query("SELECT * FROM fase_final ORDER BY $roundOrder, num_partido ASC")->fetchAll();

} catch (PDOException $e) {
    die("Error al cargar datos: " . $e->getMessage());
}

$site_name = $cfg['site_name']  ?? 'Liga MFM';
$subtitle  = $cfg['descripcion'] ?? 'Micro Fútbol Montecristo';
$season    = $cfg['season']     ?? '2025-26';
$logo_url  = $cfg['site_logo']  ?? '';
$color1    = $cfg['color_primary']   ?? '#0a5f0a';
$color2    = $cfg['color_secondary'] ?? '#1a7a1a';
$color_acc = $cfg['color_accent']    ?? '#d4af37';
$color_hl  = $cfg['color_highlight'] ?? '#c41e3a';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= limpiar($site_name) ?> - <?= limpiar($subtitle) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
<style>
/* ===== VARIABLES DINÁMICAS ===== */
:root {
    --primary:   <?= limpiar($color1)   ?>;
    --secondary: <?= limpiar($color2)   ?>;
    --accent:    <?= limpiar($color_acc)?>;
    --highlight: <?= limpiar($color_hl) ?>;
    --light:  #f8f9fa;
    --text:   #2d3748;
    --border: #e2e8f0;
    --dark:   #1a1a1a;
}
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Roboto',sans-serif;background:linear-gradient(135deg,var(--primary) 0%,var(--secondary) 50%,var(--primary) 100%);min-height:100vh;padding:20px;color:var(--text);}
.container{max-width:1400px;margin:0 auto;background:#fff;border-radius:20px;box-shadow:0 20px 60px rgba(0,0,0,.4);overflow:hidden;}

/* HEADER */
.header{background:linear-gradient(135deg,var(--primary),var(--secondary));padding:30px 40px;display:flex;align-items:center;justify-content:center;gap:30px;position:relative;overflow:hidden;}
.header::before{content:'';position:absolute;inset:0;background:url('data:image/svg+xml,<svg width="60" height="60" xmlns="http://www.w3.org/2000/svg"><circle cx="30" cy="30" r="2" fill="rgba(255,255,255,0.08)"/></svg>');opacity:.5;}
.header-logo{width:130px;height:130px;object-fit:contain;position:relative;filter:drop-shadow(0 4px 8px rgba(0,0,0,.3));animation:float 3s ease-in-out infinite;}
@keyframes float{0%,100%{transform:translateY(0)}50%{transform:translateY(-10px)}}
.header-text{position:relative;}
.header h1{font-family:'Oswald',sans-serif;font-size:3.2em;color:#fff;letter-spacing:4px;text-shadow:3px 3px 6px rgba(0,0,0,.4);}
.header p{color:var(--accent);font-size:1.1em;font-weight:600;letter-spacing:2px;}

/* TABS */
.tabs{display:flex;background:var(--light);border-bottom:4px solid var(--accent);padding:0 20px;}
.tab{font-family:'Oswald',sans-serif;padding:18px 36px;background:none;border:none;font-size:1.15em;font-weight:600;cursor:pointer;color:var(--text);text-transform:uppercase;letter-spacing:1px;position:relative;transition:all .3s;}
.tab:hover{color:var(--primary);background:rgba(0,0,0,.03);}
.tab.active{color:var(--primary);background:#fff;}
.tab.active::after{content:'';position:absolute;bottom:-4px;left:0;right:0;height:4px;background:var(--accent);}

/* CONTENT */
.content{padding:40px;min-height:500px;}
.section{display:none;}
.section.active{display:block;animation:fadeIn .4s ease;}
@keyframes fadeIn{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:translateY(0)}}
.section-title{font-family:'Oswald',sans-serif;font-size:2em;color:var(--primary);margin-bottom:28px;padding-bottom:12px;border-bottom:3px solid var(--accent);}

/* MATCH CARDS */
.matches-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(360px,1fr));gap:24px;margin-top:10px;}
.match-card{border:2px solid var(--border);border-radius:14px;padding:26px;box-shadow:0 4px 14px rgba(0,0,0,.08);transition:all .3s;position:relative;overflow:hidden;}
.match-card::before{content:'';position:absolute;top:0;left:0;right:0;height:5px;background:linear-gradient(90deg,var(--primary),var(--accent),var(--primary));}
.match-card:hover{transform:translateY(-6px);box-shadow:0 12px 28px rgba(0,0,0,.18);border-color:var(--accent);}
.match-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;padding-bottom:12px;border-bottom:2px solid var(--light);}
.match-date{font-family:'Oswald',sans-serif;color:var(--primary);font-weight:700;background:var(--light);padding:7px 14px;border-radius:8px;font-size:1em;}
.match-time{font-family:'Oswald',sans-serif;font-size:1.4em;font-weight:700;color:var(--highlight);}
.match-teams{display:flex;justify-content:space-between;align-items:center;gap:16px;margin:20px 0;}
.team{flex:1;text-align:center;}
.team-logo{width:80px;height:80px;object-fit:contain;margin:0 auto 12px;border-radius:10px;background:var(--light);padding:8px;border:2px solid var(--border);display:block;transition:transform .3s;}
.team:hover .team-logo{transform:scale(1.08);}
.team-name{font-family:'Oswald',sans-serif;font-size:1em;font-weight:600;color:var(--primary);text-transform:uppercase;}
.vs{font-family:'Oswald',sans-serif;font-size:2.2em;font-weight:700;color:var(--accent);}

/* STANDINGS TABLE */
.standings-table{width:100%;border-collapse:separate;border-spacing:0;margin-top:10px;border-radius:14px;overflow:hidden;box-shadow:0 4px 18px rgba(0,0,0,.1);border:2px solid var(--accent);}
.standings-table thead{background:linear-gradient(135deg,var(--primary),var(--secondary));color:#fff;}
.standings-table th{padding:18px 14px;text-align:left;font-family:'Oswald',sans-serif;font-weight:700;font-size:.95em;text-transform:uppercase;letter-spacing:.8px;}
.standings-table th.center,.standings-table td.center{text-align:center;}
.standings-table tbody tr{border-bottom:1px solid var(--border);transition:all .25s;}
.standings-table tbody tr:hover{background:rgba(10,95,10,.05);}
.standings-table tbody tr:nth-child(1){background:linear-gradient(90deg,rgba(212,175,55,.12),transparent);}
.standings-table tbody tr:nth-child(2),.standings-table tbody tr:nth-child(3){background:linear-gradient(90deg,rgba(34,197,94,.07),transparent);}
.standings-table td{padding:16px 14px;font-size:.93em;}
.position{font-family:'Oswald',sans-serif;font-weight:700;font-size:1.2em;color:var(--primary);text-align:center;width:50px;}
.team-info{display:flex;align-items:center;gap:12px;}
.table-team-logo{width:36px;height:36px;object-fit:contain;border-radius:6px;background:var(--light);padding:4px;border:2px solid var(--border);}
.table-team-name{font-weight:600;}
.points{font-family:'Oswald',sans-serif;font-weight:700;font-size:1.3em;color:var(--primary);background:var(--light);padding:4px 10px;border-radius:6px;}
.goals-badge{font-family:'Oswald',sans-serif;font-weight:700;font-size:1.2em;color:#fff;background:var(--highlight);padding:6px 14px;border-radius:8px;}

/* EMPTY STATE */
.empty-state{text-align:center;padding:60px 20px;color:#aaa;}
.empty-state-icon{font-size:4em;margin-bottom:16px;opacity:.5;}
.empty-state-text{font-family:'Oswald',sans-serif;font-size:1.1em;text-transform:uppercase;letter-spacing:1px;}

/* ADMIN BUTTON */
.admin-btn{position:fixed;bottom:32px;right:32px;background:linear-gradient(135deg,var(--primary),var(--secondary));color:var(--accent);border:2px solid var(--accent);border-radius:12px;padding:12px 22px;font-family:'Oswald',sans-serif;font-size:1em;font-weight:700;text-decoration:none;letter-spacing:1px;box-shadow:0 6px 20px rgba(0,0,0,.3);transition:all .3s;z-index:999;}
.admin-btn:hover{transform:translateY(-4px);box-shadow:0 10px 30px rgba(0,0,0,.4);}

/* SCROLLBAR */
::-webkit-scrollbar{width:8px;}
::-webkit-scrollbar-track{background:var(--light);}
::-webkit-scrollbar-thumb{background:var(--primary);border-radius:4px;}

/* RESPONSIVE */
@media(max-width:768px){
    .header{flex-direction:column;padding:20px;gap:16px;}
    .header h1{font-size:2em;text-align:center;}
    .header p{text-align:center;}
    .header-logo{width:90px;height:90px;}
    .tabs{overflow-x:auto;padding:0 8px;}
    .tab{padding:14px 22px;font-size:1em;}
    .content{padding:20px;}
    .matches-grid{grid-template-columns:1fr;}
    .standings-table{font-size:.78em;}
    .standings-table th,.standings-table td{padding:10px 7px;}
}
</style>
</head>
<body>

<div class="container">
    <!-- HEADER -->
    <div class="header">
        <?php if ($logo_url): ?>
            <img src="<?= limpiar($logo_url) ?>" alt="Logo" class="header-logo">
        <?php else: ?>
            <img src="Logo MFM.png" alt="Logo" class="header-logo">
        <?php endif; ?>
        <div class="header-text">
            <h1><?= limpiar($site_name) ?></h1>
            <p><?= limpiar($subtitle) ?></p>
        </div>
    </div>

    <!-- TABS -->
    <div class="tabs">
        <button class="tab active" onclick="mostrarSeccion('partidos', this)">⚽ Partidos</button>
        <button class="tab" onclick="mostrarSeccion('posiciones', this)">🏆 Posiciones</button>
        <button class="tab" onclick="mostrarSeccion('goleadores', this)">👟 Goleadores</button>
        <button class="tab" onclick="mostrarSeccion('fasefinal', this)">🏅 Fase Final</button>
    </div>

    <div class="content">

        <!-- PARTIDOS -->
        <section id="partidos" class="section active">
            <h2 class="section-title">⚽ PRÓXIMOS PARTIDOS</h2>
            <div class="matches-grid">
                <?php if (empty($matches)): ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">⚽</div>
                        <div class="empty-state-text">No hay partidos programados</div>
                        <p style="margin-top:8px;color:#bbb;">Próximamente se agregarán los partidos</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($matches as $m): ?>
                    <div class="match-card">
                        <div class="match-header">
                            <div class="match-date">
                                <?php
                                $d = new DateTime($m['match_date']);
                                $dias  = ['Sunday'=>'Dom','Monday'=>'Lun','Tuesday'=>'Mar','Wednesday'=>'Mié','Thursday'=>'Jue','Friday'=>'Vie','Saturday'=>'Sáb'];
                                $meses = ['January'=>'Ene','February'=>'Feb','March'=>'Mar','April'=>'Abr','May'=>'May','June'=>'Jun','July'=>'Jul','August'=>'Ago','September'=>'Sep','October'=>'Oct','November'=>'Nov','December'=>'Dic'];
                                $diaN = $dias[$d->format('l')];
                                $mesN = $meses[$d->format('F')];
                                echo $diaN . ', ' . $d->format('d') . ' ' . $mesN . ' ' . $d->format('Y');
                                ?>
                            </div>
                            <div class="match-time"><?= date('H:i', strtotime($m['match_time'])) ?></div>
                        </div>
                        <div class="match-teams">
                            <div class="team">
                                <img src="<?= $m['home_logo'] ?: "data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><circle cx='50' cy='50' r='40' fill='%23e2e8f0'/><text x='50' y='60' font-size='30' fill='%236b7280' text-anchor='middle' font-family='Arial'>?</text></svg>" ?>"
                                    alt="<?= limpiar($m['home_team']) ?>" class="team-logo">
                                <div class="team-name"><?= limpiar($m['home_team']) ?></div>
                            </div>
                            <div class="vs">VS</div>
                            <div class="team">
                                <img src="<?= $m['away_logo'] ?: "data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><circle cx='50' cy='50' r='40' fill='%23e2e8f0'/><text x='50' y='60' font-size='30' fill='%236b7280' text-anchor='middle' font-family='Arial'>?</text></svg>" ?>"
                                    alt="<?= limpiar($m['away_team']) ?>" class="team-logo">
                                <div class="team-name"><?= limpiar($m['away_team']) ?></div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <!-- POSICIONES -->
        <section id="posiciones" class="section">
            <h2 class="section-title">🏆 TABLA DE POSICIONES — TEMPORADA <?= limpiar($season) ?></h2>
            <div style="overflow-x:auto;">
                <table class="standings-table">
                    <thead>
                        <tr>
                            <th class="center">#</th>
                            <th>Equipo</th>
                            <th class="center" title="Partidos Jugados">PJ</th>
                            <th class="center" title="Ganados">G</th>
                            <th class="center" title="Empatados">E</th>
                            <th class="center" title="Perdidos">P</th>
                            <th class="center" title="Goles a Favor">GF</th>
                            <th class="center" title="Goles en Contra">GC</th>
                            <th class="center" title="Diferencia de Goles">DG</th>
                            <th class="center">Pts</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($teams)): ?>
                        <tr><td colspan="10" style="text-align:center;padding:40px;color:#aaa;">No hay equipos registrados.</td></tr>
                    <?php else: ?>
                        <?php foreach ($teams as $i => $t): ?>
                        <tr>
                            <td class="position"><?= $i+1 ?></td>
                            <td>
                                <div class="team-info">
                                    <img src="<?= $t['logo'] ?: "data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><circle cx='50' cy='50' r='40' fill='%23e2e8f0'/><text x='50' y='60' font-size='28' fill='%236b7280' text-anchor='middle' font-family='Arial'>?</text></svg>" ?>"
                                        alt="<?= limpiar($t['name']) ?>" class="table-team-logo">
                                    <span class="table-team-name"><?= limpiar($t['name']) ?></span>
                                </div>
                            </td>
                            <td class="center"><?= $t['pj'] ?></td>
                            <td class="center"><?= $t['g']  ?></td>
                            <td class="center"><?= $t['e']  ?></td>
                            <td class="center"><?= $t['p']  ?></td>
                            <td class="center"><?= $t['gf'] ?></td>
                            <td class="center"><?= $t['gc'] ?></td>
                            <td class="center"><?= ($t['dg']>=0?'+':'').$t['dg'] ?></td>
                            <td class="center"><span class="points"><?= $t['pts'] ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- GOLEADORES -->
        <section id="goleadores" class="section">
            <h2 class="section-title">👟 TABLA DE GOLEADORES</h2>
            <div style="overflow-x:auto;">
                <table class="standings-table">
                    <thead>
                        <tr>
                            <th class="center">#</th>
                            <th>Jugador</th>
                            <th>Equipo</th>
                            <th class="center">Goles</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($scorers)): ?>
                        <tr><td colspan="4" style="text-align:center;padding:40px;color:#aaa;">No hay goleadores registrados.</td></tr>
                    <?php else: ?>
                        <?php foreach ($scorers as $i => $sc): ?>
                        <tr>
                            <td class="position"><?= $i+1 ?></td>
                            <td>
                                <span style="font-weight:600;font-size:1.05em;">
                                    <?= $i===0?'🥇 ':($i===1?'🥈 ':($i===2?'🥉 ':'')) ?>
                                    <?= limpiar($sc['player_name']) ?>
                                </span>
                            </td>
                            <td><span class="table-team-name"><?= limpiar($sc['team_name']) ?></span></td>
                            <td class="center"><span class="goals-badge"><?= $sc['goals'] ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- FASE FINAL -->
        <section id="fasefinal" class="section">
            <h2 class="section-title">🏅 FASE FINAL</h2>
            <?php if (empty($playoffs)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">🏅</div>
                    <div class="empty-state-text">Fase Final próximamente</div>
                    <p style="margin-top:8px;color:#bbb;">Los cruces se anunciarán al finalizar la fase de grupos</p>
                </div>
            <?php else:
                $rondaIconos = ['Octavos de Final'=>'⚔️','Cuartos de Final'=>'🎯','Semifinal'=>'🔥','Tercer Puesto'=>'🥉','Final'=>'🏆'];
                $rondaActual = '';
                $bloques = [];
                foreach ($playoffs as $pf) {
                    $bloques[$pf['ronda']][] = $pf;
                }
                foreach ($bloques as $ronda => $partidos):
                    $icono   = $rondaIconos[$ronda] ?? '⚽';
                    $esFinal = ($ronda === 'Final');
            ?>
            <div style="margin-bottom:32px;border-radius:14px;overflow:hidden;box-shadow:0 4px 18px rgba(0,0,0,.10);border:1.5px solid var(--border);">
                <div style="background:linear-gradient(135deg,<?= $esFinal ? '#b8860b,#d4af37' : 'var(--primary),var(--secondary)' ?>);padding:14px 22px;display:flex;align-items:center;gap:10px;border-bottom:3px solid var(--accent);">
                    <span style="font-size:1.3em;"><?= $icono ?></span>
                    <span style="font-family:'Oswald',sans-serif;font-size:1.15em;font-weight:700;letter-spacing:2px;color:#fff;text-transform:uppercase;"><?= htmlspecialchars($ronda) ?></span>
                </div>
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px;padding:18px;background:#fff;">
                    <?php foreach ($partidos as $pf):
                        $hg      = $pf['goles_local']  !== null ? $pf['goles_local']  : '-';
                        $ag      = $pf['goles_visita'] !== null ? $pf['goles_visita'] : '-';
                        $hWin    = $pf['estado']==='finalizado' && $pf['goles_local']  !== null && $pf['goles_local']  > $pf['goles_visita'];
                        $aWin    = $pf['estado']==='finalizado' && $pf['goles_visita'] !== null && $pf['goles_visita'] > $pf['goles_local'];
                        $stBg    = ['pendiente'=>'rgba(107,114,128,.12)','en_juego'=>'rgba(196,30,58,.12)','finalizado'=>'rgba(10,95,10,.12)'];
                        $stColor = ['pendiente'=>'#6b7280','en_juego'=>'#c41e3a','finalizado'=>'#0a5f0a'];
                        $stLabel = ['pendiente'=>'⏳ Por jugar','en_juego'=>'🔴 En juego','finalizado'=>'✅ Finalizado'];
                        $st      = $pf['estado'] ?? 'pendiente';
                        $bcolor  = $st==='finalizado'?'var(--primary)':($st==='en_juego'?'var(--highlight)':'var(--border)');
                    ?>
                    <div style="border-radius:12px;overflow:hidden;border:2px solid <?= $bcolor ?>;<?= $esFinal?'box-shadow:0 0 0 2px rgba(212,175,55,.4),0 6px 20px rgba(0,0,0,.12);':'' ?>">
                        <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 14px;background:rgba(0,0,0,.03);border-bottom:1px solid var(--border);font-size:.8em;color:#777;">
                            <span>Partido <?= $pf['num_partido'] ?><?= $pf['fecha']?' · '.date('d/m/Y',strtotime($pf['fecha'])):'' ?><?= $pf['hora']?' '.substr($pf['hora'],0,5):'' ?></span>
                            <span style="background:<?= $stBg[$st]??'rgba(0,0,0,.08)' ?>;color:<?= $stColor[$st]??'#999' ?>;padding:3px 9px;border-radius:20px;font-weight:700;font-size:.85em;"><?= $stLabel[$st]??'' ?></span>
                        </div>
                        <div style="padding:14px;">
                            <div style="display:flex;align-items:center;justify-content:space-between;padding:9px 0;border-bottom:1px solid var(--border);">
                                <span style="font-family:'Oswald',sans-serif;font-size:1.05em;font-weight:600;color:<?= $hWin?'var(--primary)':'var(--dark)' ?>;flex:1;"><?= $hWin?'🏆 ':'' ?><?= htmlspecialchars($pf['equipo_local']) ?></span>
                                <span style="font-family:'Oswald',sans-serif;font-size:1.4em;font-weight:700;min-width:36px;text-align:center;padding:2px 8px;border-radius:6px;<?= $hWin?'background:var(--primary);color:#fff;':'background:var(--light);color:var(--dark);' ?>"><?= $hg ?></span>
                            </div>
                            <div style="text-align:center;font-size:.7em;color:#ccc;letter-spacing:2px;padding:2px 0;">VS</div>
                            <div style="display:flex;align-items:center;justify-content:space-between;padding:9px 0;">
                                <span style="font-family:'Oswald',sans-serif;font-size:1.05em;font-weight:600;color:<?= $aWin?'var(--primary)':'var(--dark)' ?>;flex:1;"><?= $aWin?'🏆 ':'' ?><?= htmlspecialchars($pf['equipo_visita']) ?></span>
                                <span style="font-family:'Oswald',sans-serif;font-size:1.4em;font-weight:700;min-width:36px;text-align:center;padding:2px 8px;border-radius:6px;<?= $aWin?'background:var(--primary);color:#fff;':'background:var(--light);color:var(--dark);' ?>"><?= $ag ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </section>

    </div>
</div>

<!-- BOTÓN ADMIN -->
<a href="admin/login.php" class="admin-btn">🔐 ADMIN</a>

<script>
function mostrarSeccion(nombre, btn) {
    document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById(nombre).classList.add('active');
}
</script>
</body>
</html>
