<?php
require_once '../includes/config.php';
if (!esta_logueado()) redirigir('login.php');

$msg = $err = '';

// ============================================
// PROCESAR FORMULARIOS
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    // ---------- EQUIPOS ----------
    if ($accion === 'agregar_equipo' || $accion === 'editar_equipo') {
        $name = trim($_POST['name'] ?? '');
        $logo = trim($_POST['logo'] ?? '');
        $pj   = (int)($_POST['pj'] ?? 0);
        $g    = (int)($_POST['g']  ?? 0);
        $e    = (int)($_POST['e']  ?? 0);
        $p    = (int)($_POST['p']  ?? 0);
        $gf   = (int)($_POST['gf'] ?? 0);
        $gc   = (int)($_POST['gc'] ?? 0);
        if (!$name) {
            $err = 'El nombre del equipo es obligatorio.';
        } else {
            if ($accion === 'agregar_equipo') {
                // CORRECCIÓN: dg y pts son columnas GENERATED, NO se incluyen en el INSERT
                $conn->prepare("INSERT INTO tabla (name,logo,pj,g,e,p,gf,gc) VALUES (?,?,?,?,?,?,?,?)")
                     ->execute([$name,$logo,$pj,$g,$e,$p,$gf,$gc]);
                $msg = "✅ Equipo '$name' agregado.";
            } else {
                $id = (int)$_POST['id'];
                // CORRECCIÓN: dg y pts son columnas GENERATED, NO se incluyen en el UPDATE
                $conn->prepare("UPDATE tabla SET name=?,logo=?,pj=?,g=?,e=?,p=?,gf=?,gc=? WHERE id=?")
                     ->execute([$name,$logo,$pj,$g,$e,$p,$gf,$gc,$id]);
                $msg = "✅ Equipo actualizado.";
            }
        }
    }
    if ($accion === 'borrar_equipo') {
        $conn->prepare("DELETE FROM tabla WHERE id=?")->execute([(int)$_POST['id']]);
        $msg = "🗑️ Equipo eliminado.";
    }

    // ---------- PARTIDOS ----------
    if ($accion === 'agregar_partido' || $accion === 'editar_partido') {
        $hl         = trim($_POST['home_team']  ?? '');
        $vl         = trim($_POST['away_team']  ?? '');
        $fd         = trim($_POST['match_date'] ?? '');
        $fh         = trim($_POST['match_time'] ?? '');
        $hlog       = trim($_POST['home_logo']  ?? '');
        $alog       = trim($_POST['away_logo']  ?? '');
        // CORRECCIÓN: manejar status y marcadores que existen en la tabla partidos
        $status     = trim($_POST['status']     ?? 'programado');
        $home_score = $_POST['home_score'] !== '' ? (int)$_POST['home_score'] : null;
        $away_score = $_POST['away_score'] !== '' ? (int)$_POST['away_score'] : null;

        if (!$hl || !$vl || !$fd || !$fh) {
            $err = 'Completa todos los campos del partido.';
        } else {
            if ($accion === 'agregar_partido') {
                $conn->prepare("INSERT INTO partidos (home_team,away_team,match_date,match_time,home_logo,away_logo,status,home_score,away_score) VALUES (?,?,?,?,?,?,?,?,?)")
                     ->execute([$hl,$vl,$fd,$fh,$hlog,$alog,$status,$home_score,$away_score]);
                $msg = "✅ Partido agregado.";
            } else {
                $id = (int)$_POST['id'];
                $conn->prepare("UPDATE partidos SET home_team=?,away_team=?,match_date=?,match_time=?,home_logo=?,away_logo=?,status=?,home_score=?,away_score=? WHERE id=?")
                     ->execute([$hl,$vl,$fd,$fh,$hlog,$alog,$status,$home_score,$away_score,$id]);
                $msg = "✅ Partido actualizado.";
            }
        }
    }
    if ($accion === 'borrar_partido') {
        $conn->prepare("DELETE FROM partidos WHERE id=?")->execute([(int)$_POST['id']]);
        $msg = "🗑️ Partido eliminado.";
    }

    // ---------- GOLEADORES ----------
    if ($accion === 'agregar_goleador' || $accion === 'editar_goleador') {
        $pn = trim($_POST['player_name'] ?? '');
        $tn = trim($_POST['team_name']   ?? '');
        $gl = (int)($_POST['goals'] ?? 0);
        if (!$pn || !$tn) {
            $err = 'Completa nombre del jugador y equipo.';
        } else {
            if ($accion === 'agregar_goleador') {
                $conn->prepare("INSERT INTO goleadores (player_name,team_name,goals) VALUES (?,?,?)")
                     ->execute([$pn,$tn,$gl]);
                $msg = "✅ Goleador '$pn' agregado.";
            } else {
                $id = (int)$_POST['id'];
                $conn->prepare("UPDATE goleadores SET player_name=?,team_name=?,goals=? WHERE id=?")
                     ->execute([$pn,$tn,$gl,$id]);
                $msg = "✅ Goleador actualizado.";
            }
        }
    }
    if ($accion === 'borrar_goleador') {
        $conn->prepare("DELETE FROM goleadores WHERE id=?")->execute([(int)$_POST['id']]);
        $msg = "🗑️ Goleador eliminado.";
    }

    // ---------- FASE FINAL ----------
    if ($accion === 'agregar_playoff' || $accion === 'editar_playoff') {
        $ronda   = trim($_POST['ronda']         ?? '');
        $num     = (int)($_POST['num_partido']  ?? 1);
        $home    = trim($_POST['equipo_local']  ?? '');
        $away    = trim($_POST['equipo_visita'] ?? '');
        $fecha   = trim($_POST['fecha']         ?? '') ?: null;
        $hora    = trim($_POST['hora']          ?? '') ?: null;
        $hg      = (isset($_POST['goles_local'])  && $_POST['goles_local']  !== '') ? (int)$_POST['goles_local']  : null;
        $ag      = (isset($_POST['goles_visita']) && $_POST['goles_visita'] !== '') ? (int)$_POST['goles_visita'] : null;
        $estado  = trim($_POST['estado']        ?? 'pendiente');
        if (!$ronda || !$home || !$away) {
            $err = 'Completa ronda y equipos.';
        } else {
            if ($accion === 'agregar_playoff') {
                $conn->prepare("INSERT INTO fase_final (ronda,num_partido,equipo_local,equipo_visita,fecha,hora,goles_local,goles_visita,estado) VALUES (?,?,?,?,?,?,?,?,?)")
                     ->execute([$ronda,$num,$home,$away,$fecha,$hora,$hg,$ag,$estado]);
                $msg = "✅ Partido de fase final agregado.";
            } else {
                $id = (int)$_POST['id'];
                $conn->prepare("UPDATE fase_final SET ronda=?,num_partido=?,equipo_local=?,equipo_visita=?,fecha=?,hora=?,goles_local=?,goles_visita=?,estado=? WHERE id=?")
                     ->execute([$ronda,$num,$home,$away,$fecha,$hora,$hg,$ag,$estado,$id]);
                $msg = "✅ Partido actualizado.";
            }
        }
    }
    if ($accion === 'borrar_playoff') {
        $conn->prepare("DELETE FROM fase_final WHERE id=?")->execute([(int)$_POST['id']]);
        $msg = "🗑️ Partido eliminado.";
    }

    // ---------- CONFIGURACIÓN ----------
    if ($accion === 'guardar_config') {
        $campos = ['site_name','descripcion','season','site_logo',
                   'color_primary','color_secondary','color_accent','color_highlight'];
        foreach ($campos as $c) {
            $v = trim($_POST[$c] ?? '');
            $conn->prepare("INSERT INTO edit (setting_key,setting_value) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_value=?")
                 ->execute([$c,$v,$v]);
        }
        $msg = "✅ Configuración guardada.";
    }
}

// ============================================
// CARGAR DATOS
// CORRECCIÓN: usar nombres reales de tablas (tabla, partidos, goleadores)
// ============================================
$teams   = $conn->query("SELECT * FROM tabla    ORDER BY pts DESC, dg DESC")->fetchAll();
$matches = $conn->query("SELECT * FROM partidos ORDER BY match_date ASC, match_time ASC")->fetchAll();
$scorers = $conn->query("SELECT * FROM goleadores ORDER BY goals DESC")->fetchAll();
$cfg     = obtener_config($conn);

// Edición
$eTeam    = isset($_GET['et'])  ? $conn->query("SELECT * FROM tabla      WHERE id=".(int)$_GET['et'])->fetch()  : null;
$eMatch   = isset($_GET['em'])  ? $conn->query("SELECT * FROM partidos   WHERE id=".(int)$_GET['em'])->fetch()  : null;
$eScorer  = isset($_GET['eg'])  ? $conn->query("SELECT * FROM goleadores WHERE id=".(int)$_GET['eg'])->fetch()  : null;
$ePlayoff = isset($_GET['ep'])  ? $conn->query("SELECT * FROM fase_final WHERE id=".(int)$_GET['ep'])->fetch()  : null;

// Cargar playoffs ordenados por ronda
$roundOrder = "FIELD(ronda,'Octavos de Final','Cuartos de Final','Semifinal','Tercer Puesto','Final')";
$playoffs   = $conn->query("SELECT * FROM fase_final ORDER BY $roundOrder, num_partido ASC")->fetchAll();

$panelActivo = $_GET['panel'] ?? 'equipos';
if ($eTeam)    $panelActivo = 'equipos';
if ($eMatch)   $panelActivo = 'partidos';
if ($eScorer)  $panelActivo = 'goleadores';
if ($ePlayoff) $panelActivo = 'fasefinal';

$color1   = $cfg['color_primary']   ?? '#0a5f0a';
$color2   = $cfg['color_secondary'] ?? '#1a7a1a';
$colorAcc = $cfg['color_accent']    ?? '#d4af37';
$colorHl  = $cfg['color_highlight'] ?? '#c41e3a';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard – Liga MFM</title>
<link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
<style>
:root{
    --p:  <?= $color1   ?>;
    --s:  <?= $color2   ?>;
    --a:  <?= $colorAcc ?>;
    --hl: <?= $colorHl  ?>;
    --light:#f8f9fa;--border:#e2e8f0;--text:#2d3748;
}
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Roboto',sans-serif;background:linear-gradient(135deg,var(--p),var(--s));min-height:100vh;padding:0;}
.wrap{max-width:1320px;margin:0 auto;padding:22px;}

/* TOP BAR */
.topbar{display:flex;align-items:center;justify-content:space-between;background:linear-gradient(135deg,var(--p),var(--s));border-radius:16px;padding:16px 28px;margin-bottom:22px;box-shadow:0 8px 28px rgba(0,0,0,.3);border:2px solid rgba(212,175,55,.35);}
.tb-left{display:flex;align-items:center;gap:14px;}
.tb-logo{width:52px;height:52px;object-fit:contain;filter:drop-shadow(0 2px 6px rgba(0,0,0,.4));}
.tb-title h1{font-family:'Oswald',sans-serif;color:#fff;font-size:1.6em;letter-spacing:3px;margin:0;}
.tb-title p{color:var(--a);font-size:.78em;letter-spacing:2px;margin:0;}
.tb-right{display:flex;gap:10px;align-items:center;}
.badge-user{background:rgba(255,255,255,.12);color:#fff;padding:7px 16px;border-radius:20px;font-size:.84em;border:1px solid rgba(255,255,255,.2);}
.btn-link{padding:9px 18px;border-radius:8px;font-size:.85em;font-weight:700;text-decoration:none;border:none;cursor:pointer;font-family:'Roboto',sans-serif;transition:all .2s;}
.btn-ver{background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.25);}
.btn-ver:hover{background:rgba(255,255,255,.28);}
.btn-salir{background:rgba(196,30,58,.2);color:#fff;border:1px solid rgba(196,30,58,.5);}
.btn-salir:hover{background:rgba(196,30,58,.5);}

/* STATS */
.stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:14px;margin-bottom:22px;}
.stat{background:#fff;border-radius:14px;padding:20px;text-align:center;box-shadow:0 4px 14px rgba(0,0,0,.1);border-left:5px solid var(--a);}
.stat-n{font-family:'Oswald',sans-serif;font-size:2.6em;color:var(--p);line-height:1;}
.stat-l{font-size:.75em;color:#888;text-transform:uppercase;letter-spacing:1px;margin-top:3px;}

/* NAV */
.nav{display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap;}
.navbtn{font-family:'Oswald',sans-serif;padding:11px 24px;border:1px solid rgba(255,255,255,.2);border-radius:10px;cursor:pointer;background:rgba(255,255,255,.15);color:#fff;font-size:.97em;letter-spacing:1px;transition:all .22s;text-decoration:none;display:inline-block;}
.navbtn:hover{background:rgba(255,255,255,.28);}
.navbtn.active{background:#fff;color:var(--p);font-weight:700;box-shadow:0 4px 14px rgba(0,0,0,.22);}

/* CARD */
.card{background:#fff;border-radius:16px;box-shadow:0 6px 22px rgba(0,0,0,.12);border:2px solid var(--border);overflow:hidden;margin-bottom:20px;}
.card-head{background:linear-gradient(135deg,var(--p),var(--s));padding:16px 26px;display:flex;justify-content:space-between;align-items:center;}
.card-head h2{font-family:'Oswald',sans-serif;color:#fff;font-size:1.25em;letter-spacing:2px;margin:0;}
.card-body{padding:26px;}

/* PANEL */
.panel{display:none;}
.panel.active{display:block;animation:fadeIn .35s ease;}
@keyframes fadeIn{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:translateY(0)}}

/* FORM */
.fgrid{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
.fgrid.c3{grid-template-columns:repeat(3,1fr);}
.fgrid.c4{grid-template-columns:repeat(4,1fr);}
.fg{display:flex;flex-direction:column;gap:5px;}
.fg label{font-size:.8em;font-weight:700;color:var(--p);text-transform:uppercase;letter-spacing:.5px;}
.fg input,.fg select{padding:11px 13px;border:2px solid var(--border);border-radius:8px;font-size:.95em;font-family:'Roboto',sans-serif;transition:border .2s;}
.fg input:focus,.fg select:focus{outline:none;border-color:var(--p);}
.facciones{display:flex;gap:10px;margin-top:18px;}
.btn{padding:12px 22px;border:none;border-radius:8px;font-weight:700;cursor:pointer;font-family:'Roboto',sans-serif;font-size:.9em;text-transform:uppercase;letter-spacing:.4px;transition:all .2s;}
.btn-prim{background:var(--p);color:#fff;}
.btn-prim:hover{background:var(--s);transform:translateY(-1px);}
.btn-sec{background:var(--a);color:#1a1a1a;}
.btn-sec:hover{background:#c4a030;}
.btn-danger{background:var(--hl);color:#fff;}
.btn-danger:hover{background:#a01828;}

/* TABLE */
.dtbl{width:100%;border-collapse:collapse;font-size:.88em;}
.dtbl th{background:var(--p);color:#fff;padding:12px 13px;text-align:left;font-family:'Oswald',sans-serif;letter-spacing:.4px;}
.dtbl td{padding:11px 13px;border-bottom:1px solid var(--border);vertical-align:middle;}
.dtbl tr:hover td{background:#f0fdf4;}
.acts{display:flex;gap:6px;}
.bxs{padding:6px 11px;font-size:.78em;border-radius:6px;font-weight:700;text-transform:uppercase;border:none;cursor:pointer;transition:all .2s;}
.bxs.edit{background:#d4af37;color:#1a1a1a;}
.bxs.edit:hover{background:#c4a030;}
.bxs.del{background:var(--hl);color:#fff;}
.bxs.del:hover{background:#a01828;}

/* ALERTS */
.alert{padding:13px 18px;border-radius:10px;margin-bottom:18px;font-weight:600;border-left:5px solid;}
.alert-ok{background:#f0fdf4;border-color:#22c55e;color:#15803d;}
.alert-err{background:#fef2f2;border-color:#e94560;color:#be123c;}

/* COLOR PREVIEW */
.color-row{display:flex;gap:12px;align-items:center;flex-wrap:wrap;margin-top:8px;}
.color-swatch{width:34px;height:34px;border-radius:8px;border:2px solid var(--border);cursor:pointer;}

/* DIVIDER */
.div{height:1px;background:var(--border);margin:20px 0;}

@media(max-width:768px){
    .fgrid,.fgrid.c3,.fgrid.c4{grid-template-columns:1fr;}
    .stats{grid-template-columns:1fr 1fr;}
    .topbar{flex-direction:column;gap:12px;text-align:center;}
    .tb-right{flex-wrap:wrap;justify-content:center;}
}
</style>
</head>
<body>
<div class="wrap">

<!-- TOP BAR -->
<div class="topbar">
    <div class="tb-left">
        <img src="../Logo MFM.png" alt="Logo" class="tb-logo" onerror="this.style.display='none'">
        <div class="tb-title">
            <h1>LIGA MFM</h1>
            <p>PANEL DE ADMINISTRACIÓN</p>
        </div>
    </div>
    <div class="tb-right">
        <span class="badge-user">👤 <?= limpiar($_SESSION['admin_name'] ?? $_SESSION['admin_user'] ?? 'Admin') ?></span>
        <a href="../index.php" class="btn-link btn-ver">🌐 Ver sitio</a>
        <a href="logout.php"  class="btn-link btn-salir">🚪 Salir</a>
    </div>
</div>

<!-- ALERTAS -->
<?php if ($msg): ?><div class="alert alert-ok"><?= $msg ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert-err">❌ <?= limpiar($err) ?></div><?php endif; ?>

<!-- STATS -->
<div class="stats">
    <div class="stat"><div class="stat-n"><?= count($teams)   ?></div><div class="stat-l">🏟️ Equipos</div></div>
    <div class="stat"><div class="stat-n"><?= count($matches) ?></div><div class="stat-l">⚽ Partidos</div></div>
    <div class="stat"><div class="stat-n"><?= count($scorers) ?></div><div class="stat-l">👟 Goleadores</div></div>
    <div class="stat"><div class="stat-n"><?= array_sum(array_column($scorers,'goals')) ?></div><div class="stat-l">🥅 Goles totales</div></div>
</div>

<!-- NAVEGACIÓN -->
<div class="nav">
    <a href="?panel=equipos"     class="navbtn <?= $panelActivo==='equipos'     ?'active':'' ?>">🏟️ EQUIPOS</a>
    <a href="?panel=partidos"    class="navbtn <?= $panelActivo==='partidos'    ?'active':'' ?>">⚽ PARTIDOS</a>
    <a href="?panel=goleadores"  class="navbtn <?= $panelActivo==='goleadores'  ?'active':'' ?>">👟 GOLEADORES</a>
    <a href="?panel=fasefinal"   class="navbtn <?= $panelActivo==='fasefinal'   ?'active':'' ?>">🏅 FASE FINAL</a>
    <a href="?panel=config"      class="navbtn <?= $panelActivo==='config'      ?'active':'' ?>">⚙️ CONFIGURACIÓN</a>
</div>

<!-- ========================
     PANEL EQUIPOS
========================= -->
<div class="panel <?= $panelActivo==='equipos'?'active':'' ?>">

    <div class="card">
        <div class="card-head"><h2><?= $eTeam ? '✏️ EDITAR EQUIPO' : '➕ AGREGAR EQUIPO' ?></h2></div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="accion" value="<?= $eTeam?'editar_equipo':'agregar_equipo' ?>">
                <?php if ($eTeam): ?><input type="hidden" name="id" value="<?= $eTeam['id'] ?>"><?php endif; ?>
                <div class="fgrid" style="margin-bottom:14px;">
                    <div class="fg">
                        <label>Nombre del equipo *</label>
                        <input type="text" name="name" required placeholder="Ej: Deportivo Montecristo"
                            value="<?= limpiar($eTeam['name'] ?? '') ?>">
                    </div>
                    <div class="fg">
                        <label>URL del logo (opcional)</label>
                        <input type="text" name="logo" placeholder="https://... o ruta relativa"
                            value="<?= limpiar($eTeam['logo'] ?? '') ?>">
                    </div>
                </div>
                <!-- CORRECCIÓN: solo los campos editables (dg y pts se calculan solos en la BD) -->
                <div class="fgrid c3">
                    <?php foreach (['pj'=>'PJ — Partidos Jugados','g'=>'G — Ganados','e'=>'E — Empatados','p'=>'P — Perdidos','gf'=>'GF — Goles a Favor','gc'=>'GC — Goles en Contra'] as $f=>$lbl): ?>
                    <div class="fg">
                        <label><?= $lbl ?></label>
                        <input type="number" name="<?= $f ?>" min="0" value="<?= $eTeam[$f] ?? 0 ?>">
                    </div>
                    <?php endforeach; ?>
                </div>
                <p style="font-size:.78em;color:#aaa;margin-top:8px;">⚡ <strong>DG</strong> (Diferencia de Goles) y <strong>Pts</strong> (Puntos) se calculan <em>automáticamente</em> por la base de datos.</p>
                <div class="facciones">
                    <button type="submit" class="btn btn-prim"><?= $eTeam?'💾 Guardar cambios':'➕ Agregar equipo' ?></button>
                    <?php if ($eTeam): ?><a href="?panel=equipos" class="btn btn-sec">✖ Cancelar</a><?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-head"><h2>📋 EQUIPOS (<?= count($teams) ?>)</h2></div>
        <div style="overflow-x:auto;">
            <table class="dtbl">
                <thead><tr><th>#</th><th>Equipo</th><th>PJ</th><th>G</th><th>E</th><th>P</th><th>GF</th><th>GC</th><th>DG</th><th>Pts</th><th>Acciones</th></tr></thead>
                <tbody>
                <?php if (empty($teams)): ?>
                    <tr><td colspan="11" style="text-align:center;padding:30px;color:#aaa;">No hay equipos registrados.</td></tr>
                <?php else: ?>
                    <?php foreach ($teams as $i => $t): ?>
                    <tr>
                        <td><?= $i+1 ?></td>
                        <td>
                            <?php if ($t['logo']): ?><img src="<?= limpiar($t['logo']) ?>" style="width:26px;height:26px;object-fit:contain;border-radius:4px;vertical-align:middle;margin-right:6px;" onerror="this.style.display='none'"><?php endif; ?>
                            <strong><?= limpiar($t['name']) ?></strong>
                        </td>
                        <td><?= $t['pj'] ?></td><td><?= $t['g'] ?></td><td><?= $t['e'] ?></td><td><?= $t['p'] ?></td>
                        <td><?= $t['gf'] ?></td><td><?= $t['gc'] ?></td>
                        <td><?= ($t['dg']>=0?'+':'').$t['dg'] ?></td>
                        <td><strong style="color:var(--p);font-family:'Oswald',sans-serif;font-size:1.1em;"><?= $t['pts'] ?></strong></td>
                        <td>
                            <div class="acts">
                                <a href="?et=<?= $t['id'] ?>" class="bxs edit">✏️ Editar</a>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('¿Eliminar equipo?')">
                                    <input type="hidden" name="accion" value="borrar_equipo">
                                    <input type="hidden" name="id"     value="<?= $t['id'] ?>">
                                    <button type="submit" class="bxs del">🗑️ Borrar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ========================
     PANEL PARTIDOS
========================= -->
<div class="panel <?= $panelActivo==='partidos'?'active':'' ?>">

    <div class="card">
        <div class="card-head"><h2><?= $eMatch ? '✏️ EDITAR PARTIDO' : '➕ AGREGAR PARTIDO' ?></h2></div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="accion" value="<?= $eMatch?'editar_partido':'agregar_partido' ?>">
                <?php if ($eMatch): ?><input type="hidden" name="id" value="<?= $eMatch['id'] ?>"><?php endif; ?>
                <div class="fgrid" style="margin-bottom:14px;">
                    <div class="fg">
                        <label>Equipo Local *</label>
                        <input type="text" name="home_team" required placeholder="Ej: Caporo"
                            value="<?= limpiar($eMatch['home_team'] ?? '') ?>">
                    </div>
                    <div class="fg">
                        <label>Equipo Visitante *</label>
                        <input type="text" name="away_team" required placeholder="Ej: Bad Boys"
                            value="<?= limpiar($eMatch['away_team'] ?? '') ?>">
                    </div>
                    <div class="fg">
                        <label>Logo Local (URL)</label>
                        <input type="text" name="home_logo" placeholder="https://..."
                            value="<?= limpiar($eMatch['home_logo'] ?? '') ?>">
                    </div>
                    <div class="fg">
                        <label>Logo Visitante (URL)</label>
                        <input type="text" name="away_logo" placeholder="https://..."
                            value="<?= limpiar($eMatch['away_logo'] ?? '') ?>">
                    </div>
                    <div class="fg">
                        <label>Fecha *</label>
                        <input type="date" name="match_date" required
                            value="<?= $eMatch['match_date'] ?? '' ?>">
                    </div>
                    <div class="fg">
                        <label>Hora *</label>
                        <input type="time" name="match_time" required
                            value="<?= $eMatch['match_time'] ?? '' ?>">
                    </div>
                    <!-- CORRECCIÓN: agregar campo status que existe en la tabla partidos -->
                    <div class="fg">
                        <label>Estado</label>
                        <select name="status">
                            <?php foreach (['programado'=>'📅 Programado','en_vivo'=>'🔴 En vivo','finalizado'=>'✅ Finalizado'] as $v=>$l): ?>
                            <option value="<?= $v ?>" <?= ($eMatch['status']??'programado')===$v?'selected':'' ?>><?= $l ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="fg" style="grid-column:span 1;">
                        <label>Marcador (solo si hay resultado)</label>
                        <div style="display:flex;gap:8px;align-items:center;">
                            <input type="number" name="home_score" min="0" placeholder="Local"
                                value="<?= $eMatch['home_score'] ?? '' ?>" style="width:80px;">
                            <span style="font-weight:700;color:#999;">–</span>
                            <input type="number" name="away_score" min="0" placeholder="Visit."
                                value="<?= $eMatch['away_score'] ?? '' ?>" style="width:80px;">
                        </div>
                    </div>
                </div>
                <div class="facciones">
                    <button type="submit" class="btn btn-prim"><?= $eMatch?'💾 Guardar cambios':'➕ Agregar partido' ?></button>
                    <?php if ($eMatch): ?><a href="?panel=partidos" class="btn btn-sec">✖ Cancelar</a><?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-head"><h2>📋 PARTIDOS (<?= count($matches) ?>)</h2></div>
        <div style="overflow-x:auto;">
            <table class="dtbl">
                <thead><tr><th>Fecha</th><th>Hora</th><th>Local</th><th>Visitante</th><th>Marcador</th><th>Estado</th><th>Acciones</th></tr></thead>
                <tbody>
                <?php if (empty($matches)): ?>
                    <tr><td colspan="7" style="text-align:center;padding:30px;color:#aaa;">No hay partidos.</td></tr>
                <?php else: ?>
                    <?php foreach ($matches as $m): ?>
                    <tr>
                        <td><?= date('d/m/Y', strtotime($m['match_date'])) ?></td>
                        <td><?= date('H:i',   strtotime($m['match_time'])) ?></td>
                        <td>
                            <?php if ($m['home_logo']): ?><img src="<?= limpiar($m['home_logo']) ?>" style="width:22px;height:22px;vertical-align:middle;object-fit:contain;margin-right:4px;" onerror="this.style.display='none'"><?php endif; ?>
                            <?= limpiar($m['home_team']) ?>
                        </td>
                        <td>
                            <?php if ($m['away_logo']): ?><img src="<?= limpiar($m['away_logo']) ?>" style="width:22px;height:22px;vertical-align:middle;object-fit:contain;margin-right:4px;" onerror="this.style.display='none'"><?php endif; ?>
                            <?= limpiar($m['away_team']) ?>
                        </td>
                        <!-- CORRECCIÓN: mostrar marcador home_score / away_score -->
                        <td style="text-align:center;font-family:'Oswald',sans-serif;font-size:1.1em;font-weight:700;">
                            <?= ($m['home_score'] !== null) ? $m['home_score'].' – '.$m['away_score'] : '–' ?>
                        </td>
                        <!-- CORRECCIÓN: status correcto es 'programado' no 'pendiente' -->
                        <td><span style="background:<?= $m['status']==='finalizado'?'#22c55e':($m['status']==='en_vivo'?'#f59e0b':'#6b7280') ?>;color:#fff;padding:3px 10px;border-radius:20px;font-size:.8em;font-weight:700;"><?= $m['status']==='programado'?'📅 Programado':($m['status']==='en_vivo'?'🔴 En vivo':'✅ Finalizado') ?></span></td>
                        <td>
                            <div class="acts">
                                <a href="?em=<?= $m['id'] ?>" class="bxs edit">✏️ Editar</a>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('¿Eliminar partido?')">
                                    <input type="hidden" name="accion" value="borrar_partido">
                                    <input type="hidden" name="id"     value="<?= $m['id'] ?>">
                                    <button type="submit" class="bxs del">🗑️ Borrar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ========================
     PANEL GOLEADORES
========================= -->
<div class="panel <?= $panelActivo==='goleadores'?'active':'' ?>">

    <div class="card">
        <div class="card-head"><h2><?= $eScorer ? '✏️ EDITAR GOLEADOR' : '➕ AGREGAR GOLEADOR' ?></h2></div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="accion" value="<?= $eScorer?'editar_goleador':'agregar_goleador' ?>">
                <?php if ($eScorer): ?><input type="hidden" name="id" value="<?= $eScorer['id'] ?>"><?php endif; ?>
                <div class="fgrid c3">
                    <div class="fg">
                        <label>Nombre del jugador *</label>
                        <input type="text" name="player_name" required placeholder="Ej: Andrés García"
                            value="<?= limpiar($eScorer['player_name'] ?? '') ?>">
                    </div>
                    <div class="fg">
                        <label>Equipo *</label>
                        <!-- CORRECCIÓN: selector con equipos existentes + opción libre -->
                        <input type="text" name="team_name" required placeholder="Ej: Caporo"
                            list="lista-equipos"
                            value="<?= limpiar($eScorer['team_name'] ?? '') ?>">
                        <datalist id="lista-equipos">
                            <?php foreach ($teams as $t): ?>
                            <option value="<?= limpiar($t['name']) ?>">
                            <?php endforeach; ?>
                        </datalist>
                    </div>
                    <div class="fg">
                        <label>Goles</label>
                        <input type="number" name="goals" min="0" value="<?= $eScorer['goals'] ?? 0 ?>">
                    </div>
                </div>
                <div class="facciones">
                    <button type="submit" class="btn btn-prim"><?= $eScorer?'💾 Guardar cambios':'➕ Agregar goleador' ?></button>
                    <?php if ($eScorer): ?><a href="?panel=goleadores" class="btn btn-sec">✖ Cancelar</a><?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-head"><h2>📋 GOLEADORES (<?= count($scorers) ?>)</h2></div>
        <div style="overflow-x:auto;">
            <table class="dtbl">
                <thead><tr><th>#</th><th>Jugador</th><th>Equipo</th><th>Goles</th><th>Acciones</th></tr></thead>
                <tbody>
                <?php if (empty($scorers)): ?>
                    <tr><td colspan="5" style="text-align:center;padding:30px;color:#aaa;">No hay goleadores.</td></tr>
                <?php else: ?>
                    <?php foreach ($scorers as $i => $sc): ?>
                    <tr>
                        <td><?= $i+1 ?></td>
                        <td><strong><?= limpiar($sc['player_name']) ?></strong></td>
                        <td><?= limpiar($sc['team_name']) ?></td>
                        <td><span style="font-family:'Oswald',sans-serif;font-size:1.2em;font-weight:700;color:var(--hl);"><?= $sc['goals'] ?></span></td>
                        <td>
                            <div class="acts">
                                <a href="?eg=<?= $sc['id'] ?>" class="bxs edit">✏️ Editar</a>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('¿Eliminar goleador?')">
                                    <input type="hidden" name="accion" value="borrar_goleador">
                                    <input type="hidden" name="id"     value="<?= $sc['id'] ?>">
                                    <button type="submit" class="bxs del">🗑️ Borrar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ========================
     PANEL FASE FINAL
========================= -->
<div class="panel <?= $panelActivo==='fasefinal'?'active':'' ?>">
    <div class="card">
        <div class="card-head"><h2><?= $ePlayoff ? '✏️ EDITAR PARTIDO' : '➕ AGREGAR PARTIDO — FASE FINAL' ?></h2></div>
        <div class="card-body">
        <form method="POST" action="?panel=fasefinal">
            <?php if ($ePlayoff): ?>
                <input type="hidden" name="id"     value="<?= $ePlayoff['id'] ?>">
                <input type="hidden" name="accion" value="editar_playoff">
            <?php else: ?>
                <input type="hidden" name="accion" value="agregar_playoff">
            <?php endif; ?>
            <div class="fgrid">
                <div class="fg">
                    <label>Ronda *</label>
                    <select name="ronda" required>
                        <?php
                        $rondas = ['Octavos de Final','Cuartos de Final','Semifinal','Tercer Puesto','Final'];
                        foreach ($rondas as $r):
                            $sel = ($ePlayoff && $ePlayoff['ronda']===$r) ? 'selected' : '';
                        ?>
                        <option value="<?= $r ?>" <?= $sel ?>><?= $r ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="fg">
                    <label>N° Partido</label>
                    <input type="number" name="num_partido" min="1" max="8" value="<?= $ePlayoff['num_partido'] ?? 1 ?>">
                </div>
                <div class="fg">
                    <label>Equipo Local *</label>
                    <input type="text" name="equipo_local" required placeholder="Nombre o 'Por definir'"
                        list="lista-equipos-ff"
                        value="<?= htmlspecialchars($ePlayoff['equipo_local'] ?? '') ?>">
                </div>
                <div class="fg">
                    <label>Equipo Visitante *</label>
                    <input type="text" name="equipo_visita" required placeholder="Nombre o 'Por definir'"
                        list="lista-equipos-ff"
                        value="<?= htmlspecialchars($ePlayoff['equipo_visita'] ?? '') ?>">
                </div>
                <datalist id="lista-equipos-ff">
                    <option value="Por definir">
                    <?php foreach ($teams as $t): ?>
                    <option value="<?= limpiar($t['name']) ?>">
                    <?php endforeach; ?>
                </datalist>
                <div class="fg">
                    <label>Goles Local</label>
                    <input type="number" name="goles_local" min="0" placeholder="–"
                        value="<?= $ePlayoff['goles_local'] ?? '' ?>">
                </div>
                <div class="fg">
                    <label>Goles Visitante</label>
                    <input type="number" name="goles_visita" min="0" placeholder="–"
                        value="<?= $ePlayoff['goles_visita'] ?? '' ?>">
                </div>
                <div class="fg">
                    <label>Fecha</label>
                    <input type="date" name="fecha" value="<?= $ePlayoff['fecha'] ?? '' ?>">
                </div>
                <div class="fg">
                    <label>Hora</label>
                    <input type="time" name="hora" value="<?= $ePlayoff['hora'] ?? '' ?>">
                </div>
                <div class="fg">
                    <label>Estado</label>
                    <select name="estado">
                        <?php foreach (['pendiente'=>'⏳ Por jugar','en_juego'=>'🔴 En juego','finalizado'=>'✅ Finalizado'] as $v=>$l): ?>
                        <option value="<?= $v ?>" <?= ($ePlayoff['estado']??'pendiente')===$v?'selected':'' ?>><?= $l ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="facciones" style="margin-top:18px;">
                <button type="submit" class="btn btn-prim"><?= $ePlayoff ? '💾 Guardar cambios' : '➕ Agregar partido' ?></button>
                <?php if ($ePlayoff): ?><a href="?panel=fasefinal" class="btn btn-sec">✖ Cancelar</a><?php endif; ?>
            </div>
        </form>
        </div>
    </div>

    <div class="card">
        <div class="card-head"><h2>📋 FASE FINAL (<?= count($playoffs) ?> partidos)</h2></div>
        <div class="card-body" style="padding:0;">
        <?php if (empty($playoffs)): ?>
            <p style="text-align:center;padding:30px;color:#aaa;">No hay partidos de fase final registrados.</p>
        <?php else:
            $rondaActual = '';
            foreach ($playoffs as $pf):
                if ($pf['ronda'] !== $rondaActual):
                    if ($rondaActual !== '') echo '</tbody></table>';
                    $rondaActual = $pf['ronda'];
                    $iconos = ['Octavos de Final'=>'⚔️','Cuartos de Final'=>'🎯','Semifinal'=>'🔥','Tercer Puesto'=>'🥉','Final'=>'🏆'];
                    $icono = $iconos[$rondaActual] ?? '⚽';
        ?>
            <h3 style="font-family:'Oswald',sans-serif;background:linear-gradient(135deg,<?= $color1 ?>,<?= $color2 ?>);color:#fff;padding:10px 18px;margin:0;font-size:1.05em;letter-spacing:1px;">
                <?= $icono ?> <?= htmlspecialchars($rondaActual) ?>
            </h3>
            <table style="width:100%;border-collapse:collapse;">
            <thead style="background:#f8f9fa;">
                <tr>
                    <th style="padding:10px 14px;text-align:left;font-size:.82em;color:#666;font-family:'Oswald',sans-serif;">N°</th>
                    <th style="padding:10px 14px;text-align:left;font-size:.82em;color:#666;font-family:'Oswald',sans-serif;">PARTIDO</th>
                    <th style="padding:10px 14px;text-align:center;font-size:.82em;color:#666;font-family:'Oswald',sans-serif;">RESULTADO</th>
                    <th style="padding:10px 14px;text-align:center;font-size:.82em;color:#666;font-family:'Oswald',sans-serif;">ESTADO</th>
                    <th style="padding:10px 14px;text-align:center;font-size:.82em;color:#666;font-family:'Oswald',sans-serif;">FECHA</th>
                    <th style="padding:10px 14px;text-align:center;font-size:.82em;color:#666;font-family:'Oswald',sans-serif;">ACCIONES</th>
                </tr>
            </thead>
            <tbody>
        <?php endif; ?>
                <tr style="border-bottom:1px solid #e2e8f0;">
                    <td style="padding:12px 14px;font-weight:700;color:<?= $color1 ?>;"><?= $pf['num_partido'] ?></td>
                    <td style="padding:12px 14px;">
                        <strong><?= htmlspecialchars($pf['equipo_local']) ?></strong>
                        <span style="color:#999;margin:0 8px;">vs</span>
                        <strong><?= htmlspecialchars($pf['equipo_visita']) ?></strong>
                    </td>
                    <td style="padding:12px 14px;text-align:center;font-family:'Oswald',sans-serif;font-size:1.2em;font-weight:700;">
                        <?= ($pf['goles_local'] !== null) ? $pf['goles_local'].' – '.$pf['goles_visita'] : '– vs –' ?>
                    </td>
                    <td style="padding:12px 14px;text-align:center;">
                        <?php
                        $badges = ['pendiente'=>['#6b7280','⏳ Por jugar'],'en_juego'=>['#c41e3a','🔴 En juego'],'finalizado'=>['#0a5f0a','✅ Finalizado']];
                        [$bc,$bl] = $badges[$pf['estado']] ?? ['#999','?'];
                        echo "<span style='background:".htmlspecialchars($bc)."22;color:".htmlspecialchars($bc).";padding:4px 10px;border-radius:20px;font-size:.82em;font-weight:700;'>$bl</span>";
                        ?>
                    </td>
                    <td style="padding:12px 14px;text-align:center;font-size:.9em;color:#555;">
                        <?= $pf['fecha'] ? date('d/m/Y', strtotime($pf['fecha'])) : '–' ?>
                        <?= $pf['hora'] ? '<br>'.substr($pf['hora'],0,5) : '' ?>
                    </td>
                    <td style="padding:12px 14px;text-align:center;">
                        <div class="acts" style="justify-content:center;">
                        <a href="?ep=<?= $pf['id'] ?>" class="bxs edit">✏️ Editar</a>
                        <form method="POST" action="?panel=fasefinal" style="display:inline;" onsubmit="return confirm('¿Eliminar este partido?')">
                            <input type="hidden" name="accion" value="borrar_playoff">
                            <input type="hidden" name="id"     value="<?= $pf['id'] ?>">
                            <!-- CORRECCIÓN: era btn-dang (no existe), ahora es bxs del -->
                            <button type="submit" class="bxs del">🗑️ Borrar</button>
                        </form>
                        </div>
                    </td>
                </tr>
        <?php endforeach;
            if ($rondaActual !== '') echo '</tbody></table>';
        endif; ?>
        </div>
    </div>
</div>

<!-- ========================
     PANEL CONFIGURACIÓN
========================= -->
<div class="panel <?= $panelActivo==='config'?'active':'' ?>">
    <div class="card">
        <div class="card-head"><h2>⚙️ CONFIGURACIÓN DEL SITIO</h2></div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="accion" value="guardar_config">

                <h3 style="font-family:'Oswald',sans-serif;color:var(--p);margin-bottom:14px;font-size:1.1em;letter-spacing:1px;">📝 INFORMACIÓN DE LA LIGA</h3>
                <div class="fgrid" style="margin-bottom:14px;">
                    <div class="fg">
                        <label>Nombre de la liga</label>
                        <input type="text" name="site_name" value="<?= limpiar($cfg['site_name'] ?? 'Liga MFM') ?>">
                    </div>
                    <div class="fg">
                        <label>Subtítulo / descripción</label>
                        <input type="text" name="descripcion" value="<?= limpiar($cfg['descripcion'] ?? 'Micro Fútbol Montecristo') ?>">
                    </div>
                    <div class="fg">
                        <label>Temporada</label>
                        <input type="text" name="season" placeholder="Ej: 2025-26" value="<?= limpiar($cfg['season'] ?? '2025-26') ?>">
                    </div>
                    <div class="fg">
                        <label>URL del logo del sitio</label>
                        <input type="text" name="site_logo" placeholder="https://... o Logo MFM.png"
                            value="<?= limpiar($cfg['site_logo'] ?? '') ?>">
                    </div>
                </div>

                <div class="div"></div>
                <h3 style="font-family:'Oswald',sans-serif;color:var(--p);margin-bottom:14px;font-size:1.1em;letter-spacing:1px;">🎨 COLORES DEL SITIO</h3>
                <div class="fgrid c4">
                    <div class="fg">
                        <label>Color primario</label>
                        <div style="display:flex;gap:8px;align-items:center;">
                            <input type="color" id="cp1" value="<?= $cfg['color_primary'] ?? '#0a5f0a' ?>"
                                oninput="document.getElementById('tp1').value=this.value"
                                style="width:44px;height:44px;border-radius:8px;border:2px solid var(--border);cursor:pointer;padding:2px;">
                            <input type="text" id="tp1" name="color_primary" value="<?= limpiar($cfg['color_primary'] ?? '#0a5f0a') ?>"
                                oninput="document.getElementById('cp1').value=this.value" style="flex:1;">
                        </div>
                    </div>
                    <div class="fg">
                        <label>Color secundario</label>
                        <div style="display:flex;gap:8px;align-items:center;">
                            <input type="color" id="cp2" value="<?= $cfg['color_secondary'] ?? '#1a7a1a' ?>"
                                oninput="document.getElementById('tp2').value=this.value"
                                style="width:44px;height:44px;border-radius:8px;border:2px solid var(--border);cursor:pointer;padding:2px;">
                            <input type="text" id="tp2" name="color_secondary" value="<?= limpiar($cfg['color_secondary'] ?? '#1a7a1a') ?>"
                                oninput="document.getElementById('cp2').value=this.value" style="flex:1;">
                        </div>
                    </div>
                    <div class="fg">
                        <label>Color acento</label>
                        <div style="display:flex;gap:8px;align-items:center;">
                            <input type="color" id="cp3" value="<?= $cfg['color_accent'] ?? '#d4af37' ?>"
                                oninput="document.getElementById('tp3').value=this.value"
                                style="width:44px;height:44px;border-radius:8px;border:2px solid var(--border);cursor:pointer;padding:2px;">
                            <input type="text" id="tp3" name="color_accent" value="<?= limpiar($cfg['color_accent'] ?? '#d4af37') ?>"
                                oninput="document.getElementById('cp3').value=this.value" style="flex:1;">
                        </div>
                    </div>
                    <div class="fg">
                        <label>Color resaltado</label>
                        <div style="display:flex;gap:8px;align-items:center;">
                            <input type="color" id="cp4" value="<?= $cfg['color_highlight'] ?? '#c41e3a' ?>"
                                oninput="document.getElementById('tp4').value=this.value"
                                style="width:44px;height:44px;border-radius:8px;border:2px solid var(--border);cursor:pointer;padding:2px;">
                            <input type="text" id="tp4" name="color_highlight" value="<?= limpiar($cfg['color_highlight'] ?? '#c41e3a') ?>"
                                oninput="document.getElementById('tp4').value=this.value" style="flex:1;">
                        </div>
                    </div>
                </div>
                <p style="font-size:.78em;color:#aaa;margin-top:8px;">* Los cambios de color se verán reflejados en el sitio público al guardar.</p>

                <div class="div"></div>
                <div style="background:#fffbeb;border-radius:10px;padding:16px;border-left:4px solid #d4af37;">
                    <strong>🔑 Cambiar contraseña admin</strong><br>
                    <small style="color:#666;">Para cambiar usuario o contraseña, edita directamente la tabla <code>admins</code> en phpMyAdmin.</small>
                </div>

                <div class="facciones" style="margin-top:20px;">
                    <button type="submit" class="btn btn-prim">💾 Guardar configuración</button>
                </div>
            </form>
        </div>
    </div>
</div>

</div><!-- .wrap -->
</body>
</html>
