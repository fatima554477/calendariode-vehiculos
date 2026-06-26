<?php
/*
════════════════════════════════════════════════════════════════
  CALENDARIO DE VEHÍCULOS — CRM EPC INNOVA
════════════════════════════════════════════════════════════════
*/

if(!isset($_SESSION)) { session_start(); }
isset($_SESSION["logeado"])?'':header("location: index.php?salir=1");

require "includes/error_reporting.php";
require "calendariodeeventos2/controladorAE.php";
require "calendariodeeventos2/variablesE.php";

$conn = $conexion->db();

// ══════════ PARÁMETROS ══════════
$meses_nombres = [
    1=>'ENERO',2=>'FEBRERO',3=>'MARZO',4=>'ABRIL',5=>'MAYO',6=>'JUNIO',
    7=>'JULIO',8=>'AGOSTO',9=>'SEPTIEMBRE',10=>'OCTUBRE',11=>'NOVIEMBRE',12=>'DICIEMBRE'
];
$meses_abrev = [
    1=>'ene',2=>'feb',3=>'mar',4=>'abr',5=>'may',6=>'jun',
    7=>'jul',8=>'ago',9=>'sep',10=>'oct',11=>'nov',12=>'dic'
];
$dias_semana_nombres = [
    0=>'DOMINGO',1=>'LUNES',2=>'MARTES',3=>'MIÉRCOLES',4=>'JUEVES',5=>'VIERNES',6=>'SÁBADO'
];


$mes_sel  = isset($_GET['mes'])  ? intval($_GET['mes'])  : intval(date('n'));
$anio_sel = isset($_GET['anio']) ? intval($_GET['anio']) : intval(date('Y'));
$filtro_empresa = isset($_GET['empresa']) ? mysqli_real_escape_string($conn, trim($_GET['empresa'])) : '';

$dias_en_mes = cal_days_in_month(CAL_GREGORIAN, $mes_sel, $anio_sel);
$fecha_inicio_mes = sprintf('%04d-%02d-01', $anio_sel, $mes_sel);
$fecha_fin_mes    = sprintf('%04d-%02d-%02d', $anio_sel, $mes_sel, $dias_en_mes);

// ══════════ 1. VEHÍCULOS ══════════
$vehiculos_permitidos = [];
if($conexion->variablespermisos('','vervehiculo','ver')=='si'){
    $queryper = $conexion->lista_plantillaventavehi_todos();
}else{
    $queryper = $conexion->lista_plantillaventavehi();
}
while ($row_permiso = mysqli_fetch_assoc($queryper)) {
    $vehiculos_permitidos[] = intval($row_permiso['id']);
}

$vehiculos = [];
if (!empty($vehiculos_permitidos)) {
    $ids_vehiculos = implode(',', $vehiculos_permitidos);
    // DESPUÉS ✅
$sql_vehiculos = "SELECT id, MARCAV, SUBMARCAV, COLORV, PLACASV, PROPIETARIOV FROM 09vehiculos WHERE id IN (".$ids_vehiculos.") ORDER BY id ASC";
    $res_vehiculos = mysqli_query($conn, $sql_vehiculos);
    while ($row = mysqli_fetch_assoc($res_vehiculos)) {
        $vehiculos[$row['id']] = $row;
    }
}

// ══════════ 2. ASIGNACIONES DEL MES ══════════
$sql_asig = "
    SELECT 
        ve.VEHICULOSEVE_VEHICULO,
        ve.VEHICULOSEVE_ENTREGA,
        ve.VEHICULOSEVE_DEVOLU,
        ve.nombreocupov,
        ve.nombreingresov,
        ae.NOMBRE_EVENTO,
        ae.NOMBRE_CORTO_EVENTO,
        ae.iniciales_evento,
        ae.NUMERO_EVENTO
    FROM 04vehiculoevento ve
    LEFT JOIN 04altaeventos ae ON ve.idRelacion = ae.id
    WHERE ve.VEHICULOSEVE_ENTREGA <= '".$fecha_fin_mes."'
      AND ve.VEHICULOSEVE_DEVOLU  >= '".$fecha_inicio_mes."'
";
if ($filtro_empresa != '') {
    $sql_asig .= " AND ae.iniciales_evento = '".$filtro_empresa."' ";
}
$sql_asig .= " ORDER BY ve.VEHICULOSEVE_ENTREGA ASC";
$res_asig = mysqli_query($conn, $sql_asig);

$asignaciones = [];
$vehiculos_con_empresa = [];

while ($row = mysqli_fetch_assoc($res_asig)) {
    $id_v = $row['VEHICULOSEVE_VEHICULO'];
    $nombre_ev = strtoupper(trim($row['NOMBRE_EVENTO'].' '.$row['NOMBRE_CORTO_EVENTO']));
    $es_mant = (strpos($nombre_ev, 'MANTENIMIENTO') !== false);

    $asignaciones[$id_v][] = [
        'fi' => $row['VEHICULOSEVE_ENTREGA'],
        'ff' => $row['VEHICULOSEVE_DEVOLU'],
        'conductor'  => $row['nombreocupov'],
        'solicitante'=> $row['nombreingresov'],
        'mant'       => $es_mant,
        'empresa'    => $row['iniciales_evento'],
        'evento'     => $row['NOMBRE_EVENTO'],
        'num_evento' => $row['NUMERO_EVENTO']
    ];
    if (!empty($row['iniciales_evento'])) {
        $vehiculos_con_empresa[$id_v] = $row['iniciales_evento'];
    }
}

// ══════════ 3. VENCIMIENTOS TARJETA CIRCULACIÓN ══════════
$sql_venc = "
    SELECT idRelacion AS id_vehiculo, DOCUMENTO_VENCIMIENTOT
    FROM 09fechavencimientotenencia 
    WHERE DOCUMENTO_VENCIMIENTOT >= '".$fecha_inicio_mes."' 
      AND DOCUMENTO_VENCIMIENTOT <= '".$fecha_fin_mes."'
";
$res_venc = mysqli_query($conn, $sql_venc);
$vencimientos = [];
while ($row = mysqli_fetch_assoc($res_venc)) {
    $vencimientos[$row['id_vehiculo']][] = $row['DOCUMENTO_VENCIMIENTOT'];
}

// ══════════ 4. FILTRAR VEHÍCULOS POR EMPRESA ══════════
$vehiculos_filtrados = $vehiculos;
if ($filtro_empresa != '') {
    $temp = [];
    foreach ($vehiculos as $id_v => $v) {
        if (isset($vehiculos_con_empresa[$id_v]) && $vehiculos_con_empresa[$id_v] == $filtro_empresa) {
            $temp[$id_v] = $v;
        }
    }
    if (!empty($temp)) $vehiculos_filtrados = $temp;
}
$total_registros = count($vehiculos_filtrados);
$colores_vehiculos = colores_por_vehiculo($vehiculos_filtrados);


// ══════════ 5. EMPRESAS PARA SELECT ══════════
$sql_emp = "SELECT DISTINCT NCE_OBSERVACIONES FROM 03datosdelaempresa WHERE NCE_OBSERVACIONES IS NOT NULL AND NCE_OBSERVACIONES != '' ORDER BY NCE_OBSERVACIONES";
$res_emp = mysqli_query($conn, $sql_emp);
$empresas_lista = [];
while ($row = mysqli_fetch_assoc($res_emp)) {
    $empresas_lista[] = $row['NCE_OBSERVACIONES'];
}

// ══════════ FUNCIÓN ESTADO DE CELDA ══════════
function estado_dia($id_v, $fecha, $asig, $venc, $colores_vehiculos) {

    if (isset($venc[$id_v])) {
        foreach ($venc[$id_v] as $fv) {
            if ($fv == $fecha) return ['clase'=>'cell-vencimiento','html'=>'VENC. TARJETA','style'=>''];
        }
    }
    if (isset($asig[$id_v])) {
        foreach ($asig[$id_v] as $a) {
            if ($fecha >= $a['fi'] && $fecha <= $a['ff']) {
                if ($a['mant']) return ['clase'=>'cell-mantenimiento','html'=>'MANTENIMIENTO','style'=>''];
                $num = !empty($a['num_evento']) ? htmlspecialchars($a['num_evento']) : 'N/D';
                $cond = !empty($a['conductor']) ? htmlspecialchars(primeros_dos_nombres($a['conductor'])) : 'ASIGNADO';
                $html = "<strong></strong> {$num}<br>"
                      . "<strong></strong> {$cond}";
                  $color = color_vehiculo($id_v, $colores_vehiculos);

                return ['clase'=>'cell-ocupado','html'=>$html,'style'=>"background:{$color['bg']};color:{$color['fg']}"];
            }
        }
    }
    return ['clase'=>'cell-vacio','html'=>'','style'=>''];
}

function primeros_dos_nombres($nombre_completo) {
    $nombre_completo = trim((string)$nombre_completo);
    if ($nombre_completo === '') return '';
    $partes = preg_split('/\s+/', $nombre_completo);
    if (!$partes) return '';
    return implode(' ', array_slice($partes, 0, 2));
}

function paleta_colores_vehiculos() {

    return [

        ['bg'=>'#d6e9ff','fg'=>'#003b73'],
        ['bg'=>'#ffe0cc','fg'=>'#7a2e00'],
        ['bg'=>'#e4f7d2','fg'=>'#2f5d00'],
        ['bg'=>'#f3ddff','fg'=>'#5c0080'],
        ['bg'=>'#ffd9ec','fg'=>'#7b003d'],
        ['bg'=>'#d8f7f5','fg'=>'#005f5b'],
        ['bg'=>'#fff4cc','fg'=>'#6b5600'],
            ['bg'=>'#e2e6ff','fg'=>'#1f2a7a'],

        ['bg'=>'#ffd6d6','fg'=>'#7a0000'],

        ['bg'=>'#dfffe6','fg'=>'#006b1f'],

        ['bg'=>'#f0e0c8','fg'=>'#5c3600'],

        ['bg'=>'#d7ebff','fg'=>'#004a7c'],

        ['bg'=>'#f7d7ff','fg'=>'#650078'],

        ['bg'=>'#d6fff2','fg'=>'#00604b'],

        ['bg'=>'#ffe6f0','fg'=>'#7b0030'],

        ['bg'=>'#e8ffd1','fg'=>'#3c6600']

    ];

   
}



function colores_por_vehiculo($vehiculos_filtrados) {

    $palette = paleta_colores_vehiculos();

    $colores = [];

    $indice = 0;

    foreach ($vehiculos_filtrados as $id_v => $vehiculo) {

        $colores[$id_v] = $palette[$indice % count($palette)];

        $indice++;

    }

    return $colores;

}



function color_vehiculo($id_v, $colores_vehiculos) {

    if (isset($colores_vehiculos[$id_v])) {

        return $colores_vehiculos[$id_v];

    }
    $palette = paleta_colores_vehiculos();

    return $palette[0];

}

function obtener_asignacion_activa($id_v, $fecha, $asig) {
    if (!isset($asig[$id_v])) return null;
    foreach ($asig[$id_v] as $a) {
        if ($fecha >= $a['fi'] && $fecha <= $a['ff']) return $a;
    }
    return null;
}
function texto_estado_excel($estado) {

    $texto = trim(strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $estado['html'])));

$texto = preg_replace('/[ \t]+/', ' ', $texto);



    $texto = preg_replace('/\s*\n\s*/', "\n", $texto);


    return $texto;

}

function estilo_excel_estado($estado) {



    $estilos_base = 'vertical-align:middle;text-align:center;white-space:normal;mso-data-placement:same-cell;';



    if (!empty($estado['style'])) {

        return $estilos_base.$estado['style'].';font-weight:bold;';

    }



    switch ($estado['clase']) {

        case 'cell-mantenimiento':

            return $estilos_base.'background:#fff3cd;color:#856404;font-weight:bold;';

        case 'cell-vencimiento':

            return $estilos_base.'background:#f8d7da;color:#721c24;font-weight:bold;';

        case 'cell-vacio':

            return $estilos_base.'background:#ffffff;color:#000000;';

        default:

            return $estilos_base;

    }



}


function exportar_calendario_excel($vehiculos_filtrados, $asignaciones, $vencimientos, $colores_vehiculos, $dias_en_mes, $mes_sel, $anio_sel, $meses_nombres, $meses_abrev, $dias_semana_nombres) {

    $nombre_archivo = 'calendario_vehiculos_'.$anio_sel.'_'.str_pad($mes_sel, 2, '0', STR_PAD_LEFT).'.xls';



    header('Content-Type: application/vnd.ms-excel; charset=UTF-8');

    header('Content-Disposition: attachment; filename="'.$nombre_archivo.'"');

    header('Pragma: no-cache');

    header('Expires: 0');



    echo "\xEF\xBB\xBF";

      echo '<table border="1" style="border-collapse:collapse;font-family:Arial, sans-serif;font-size:10pt;">';


    echo '<tr><th colspan="'.(3 + $dias_en_mes).'" style="font-size:14pt;font-weight:bold;text-align:center;">CALENDARIO DE VEH&Iacute;CULOS - '.$meses_nombres[$mes_sel].' '.$anio_sel.'</th></tr>';



    echo '<tr>';

     echo '<th style="background:#c9e8e8;font-weight:bold;text-align:center;">No.</th><th style="background:#c9e8e8;font-weight:bold;text-align:center;">Veh&iacute;culo</th><th style="background:#c9e8e8;font-weight:bold;text-align:center;">Placas</th>';




    for ($d = 1; $d <= $dias_en_mes; $d++) {

        $fecha = sprintf('%04d-%02d-%02d', $anio_sel, $mes_sel, $d);

        $dw = date('w', strtotime($fecha));

               $estilo_th = 'font-weight:bold;text-align:center;';




        echo '<th style="'.$estilo_th.'">'.sprintf('%02d', $d).'-'.$meses_abrev[$mes_sel].' '.$dias_semana_nombres[$dw].'</th>';


    }

    echo '</tr>';



    $cnt = 0;

    foreach ($vehiculos_filtrados as $id_v => $v) {

        $cnt++;

        echo '<tr>';

         echo '<td style="text-align:center;">'.$cnt.'</td>';


     echo '<td style="font-weight:bold;">'.htmlspecialchars(trim($v['MARCAV'].' '.$v['SUBMARCAV']), ENT_QUOTES, 'UTF-8').'</td>';


     echo '<td style="text-align:center;background:#fff3e0;color:#b04a00;font-weight:bold;">'.htmlspecialchars($v['PLACASV'], ENT_QUOTES, 'UTF-8').'</td>';




        for ($d = 1; $d <= $dias_en_mes; $d++) {

            $fecha = sprintf('%04d-%02d-%02d', $anio_sel, $mes_sel, $d);

            $estado = estado_dia($id_v, $fecha, $asignaciones, $vencimientos, $colores_vehiculos);

          echo '<td style="'.estilo_excel_estado($estado).'">'.nl2br(htmlspecialchars(texto_estado_excel($estado), ENT_QUOTES, 'UTF-8')).'</td>';


        }

        echo '</tr>';

    }



    echo '</table>';

    exit;

}



if (isset($_GET['exportar']) && $_GET['exportar'] === 'excel') {

    exportar_calendario_excel($vehiculos_filtrados, $asignaciones, $vencimientos, $colores_vehiculos, $dias_en_mes, $mes_sel, $anio_sel, $meses_nombres, $meses_abrev, $dias_semana_nombres);

}

// ── Navegación meses ──
$ma = $mes_sel-1; $aa = $anio_sel; if($ma<1){$ma=12;$aa--;}
$ms = $mes_sel+1; $as = $anio_sel; if($ms>12){$ms=1;$as++;}
$pe = $filtro_empresa!='' ? '&empresa='.$filtro_empresa : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CALENDARIO VEHÍCULOS — <?php echo $meses_nombres[$mes_sel].' '.$anio_sel; ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
  body{font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;font-size:14px;background:#f5f5f5}

  /* ══════════ STICKY ENCABEZADOS (vertical) ══════════ */
  thead tr:first-child th{
    position:sticky;
    top:0;
    background:#c9e8e8;
    z-index:10;
    font-size:13px;
    text-align:center;
    vertical-align:middle;
    padding:8px 6px;
    border:1px solid #a0c4c4;
  }
  thead tr:nth-child(2) td{
    position:sticky;
    top:42px;
    background:#e2f2f2;
    z-index:9;
    padding:5px;
    border:1px solid #c0d8d8;
  }
  thead tr:nth-child(2) td input{font-size:13px;padding:3px 6px;width:100%}

  /* ══════════ STICKY COLUMNAS FIJAS (horizontal) ══════════ */
  /* Anchos fijos de las 3 columnas izquierdas */
  .col-sticky-0{
    position:sticky;
    left:0;
    min-width:38px;
    max-width:38px;
    width:38px;
    z-index:5;
  }
  .col-sticky-1{
    position:sticky;
    left:38px;
    min-width:50px;
    max-width:50px;
    width:50px;
    z-index:5;
  }
  .col-sticky-2{
    position:sticky;
    left:88px;
     min-width:140px;
    max-width:140px;
    width:140px;

    z-index:5;
    box-shadow:3px 0 8px rgba(0,0,0,.15);
  }

  /* Encabezados: sticky vertical + horizontal combinados (z-index mayor) */
  thead tr:first-child th.col-sticky-0,
  thead tr:first-child th.col-sticky-1,
  thead tr:first-child th.col-sticky-2{
    z-index:20;
    background:#c9e8e8;
  }
  thead tr:nth-child(2) td.col-sticky-0,
  thead tr:nth-child(2) td.col-sticky-1,
  thead tr:nth-child(2) td.col-sticky-2{
    z-index:19;
    background:#e2f2f2;
  }

  /* Celdas del body: heredar fondo de la fila para que no quede en blanco */
  tbody tr td.col-sticky-0,
  tbody tr td.col-sticky-1,
  tbody tr td.col-sticky-2{
    background:#fff;
  }
  tbody tr:nth-child(even) td.col-sticky-0,
  tbody tr:nth-child(even) td.col-sticky-1,
  tbody tr:nth-child(even) td.col-sticky-2{
    background:#f2f2f2;
  }
  tbody tr:hover td.col-sticky-0,
  tbody tr:hover td.col-sticky-1,
  tbody tr:hover td.col-sticky-2{
    background:#e8e8e8;
  }
.veh-marca  { font-size: 10px; font-weight: 500; color: #1a6e9f; letter-spacing: .5px; line-height: 1.3; }
.veh-sub    {

    font-size: 12px;

    font-weight: 500;

    color: var(--color-text-primary);

    line-height: 1.25;

    white-space: normal;

    overflow-wrap: anywhere;

    display: -webkit-box;

    -webkit-line-clamp: 2;

    -webkit-box-orient: vertical;

    overflow: hidden;

  }

.veh-placas { font-size: 12px; font-weight: 500; color: #b04a00; background: #fff3e0;
              border-radius: 3px; padding: 1px 5px; letter-spacing: .7px; display: inline-block; margin-top: 2px; }

  /* ══════════ RESTO DE ESTILOS ══════════ */
  .table-scroll{max-height:650px;overflow-y:auto;overflow-x:auto;border:1px solid #ccc;background:#fff}
  .table{margin-bottom:0;font-size:14px}
  .table td,.table th{white-space:nowrap;vertical-align:middle;padding:7px 10px}
  .table tbody tr:hover{filter:brightness(.95)}
  .cell-disponible{background:#d4edda;color:#155724;font-weight:600}
  .cell-mantenimiento{background:#fff3cd;color:#856404;font-weight:600}
  .cell-ocupado{font-weight:600}
  .cell-vencimiento{background:#f8d7da;color:#721c24;font-weight:600}
  .cell-vacio{background:#fff}
  .leyenda{display:flex;gap:18px;flex-wrap:wrap;align-items:center;padding:12px 20px;background:#fff;border-bottom:1px solid #ddd}
  .leyenda-item{display:flex;align-items:center;gap:6px;font-size:13px}
  .leyenda-color{width:18px;height:18px;border-radius:3px;border:1px solid #aaa}
  .page-header{background:linear-gradient(135deg,#2c3e50,#3498db);color:#fff;padding:18px 25px;display:flex;justify-content:space-between;align-items:center}
  .page-header h1{font-size:22px;font-weight:700;margin:0}
  .page-header .info{font-size:13px;opacity:.8}
  .controles{padding:12px 20px;background:#eef;display:flex;gap:15px;align-items:center;flex-wrap:wrap}
  .controles label{font-weight:600;font-size:14px}
  .controles select{font-size:14px;padding:5px 10px;border:1px solid #bbb;border-radius:4px}
  .controles .btn-f{background:#3498db;color:#fff;border:none;padding:6px 18px;border-radius:4px;font-size:14px;cursor:pointer}
  .controles .btn-f:hover{background:#2980b9}
  .hint-text{font-size:14px;color:#666;padding:10px 20px}
  .col-check{width:30px;text-align:center}
  .dia-col{min-width:100px;max-width:150px;font-size:13px}
  .dia-header{font-size:12px}
  .col-no{min-width:45px;text-align:center}
    .col-nombre{min-width:140px;max-width:140px;white-space:normal}
  .col-empresa{min-width:70px;text-align:center}
  .col-evento{min-width:120px;text-align:center}
  .col-fecha{min-width:110px;text-align:center}
  .col-quien{min-width:160px}
  .th-fs{background:#e2e3e5!important}
  .nav-m{display:flex;align-items:center;gap:12px}
  .nav-m a{color:#fff;text-decoration:none;font-size:22px;font-weight:bold;padding:0 10px}
  .nav-m a:hover{opacity:.7}
</style>
</head>
<body>

<!-- HEADER -->
<div class="page-header">
  <div>
    <h1>CALENDARIO DE VEHÍCULOS</h1>
    <div class="info"> Control de asignación de vehículos</div>
  </div>
  <div class="nav-m">
    <a href="?mes=<?php echo $ma; ?>&anio=<?php echo $aa.$pe; ?>">◀</a>
    <span style="font-size:20px;font-weight:700"><?php echo $meses_nombres[$mes_sel].' '.$anio_sel; ?></span>
    <a href="?mes=<?php echo $ms; ?>&anio=<?php echo $as.$pe; ?>">▶</a>
  </div>
 <div class="d-flex gap-2 flex-wrap justify-content-end">

    <a class="btn btn-sm btn-success" style="font-size:14px" href="?mes=<?php echo $mes_sel; ?>&anio=<?php echo $anio_sel; ?><?php echo $pe; ?>&exportar=excel">📊 EXPORTAR A EXCEL</a>

    <button class="btn btn-sm btn-outline-light" style="font-size:14px" onclick="window.print()">🖨️ IMPRIMIR</button>

  </div>

</div>

<!-- CONTROLES -->
<form method="GET" class="controles">
  <label>MES:</label>
  <select name="mes">
    <?php foreach($meses_nombres as $n=>$nom): ?>
    <option value="<?php echo $n; ?>" <?php echo $n==$mes_sel?'selected':''; ?>><?php echo $nom; ?></option>
    <?php endforeach; ?>
  </select>
  <label>AÑO:</label>
  <select name="anio">
    <?php for($a=date('Y')-3;$a<=date('Y')+2;$a++): ?>
    <option value="<?php echo $a; ?>" <?php echo $a==$anio_sel?'selected':''; ?>><?php echo $a; ?></option>
    <?php endfor; ?>
  </select>
  <label>EMPRESA:</label>
  <select name="empresa">
    <option value="">TODAS</option>
    <?php foreach($empresas_lista as $e): ?>
    <option value="<?php echo htmlspecialchars($e); ?>" <?php echo $filtro_empresa==$e?'selected':''; ?>><?php echo htmlspecialchars($e); ?></option>
    <?php endforeach; ?>
  </select>
  <button type="submit" class="btn-f">FILTRAR</button>
</form>

<!-- LEYENDA -->
<div class="leyenda">
  <strong>LEYENDA:</strong>
  <div class="leyenda-item"><div class="leyenda-color"></div> DISPONIBLE</div>
  <div class="leyenda-item"><div class="leyenda-color" style="background:linear-gradient(135deg,#d6e9ff,#ffe0cc,#e4f7d2)"></div> ASIGNADO (COLOR POR VEHÍCULO)</div>

  <div class="leyenda-item"><div class="leyenda-color"></div> MANTENIMIENTO</div>
  <div class="leyenda-item"><div class="leyenda-color"></div> VENCIMIENTO / ALERTA</div>
</div>

<div class="hint-text"><?php echo $total_registros; ?> vehículos registrados</div>

<!-- TABLA -->
<div class="table-scroll">
<table class="table table-striped table-bordered" id="tablaVehiculos">
  <thead>
    <tr>
      <!-- ░░ COLUMNAS FIJAS: sticky horizontal + sticky vertical ░░ -->
      <th class="col-sticky-0"></th>
      <th class="col-sticky-1">No.</th>
      <th class="col-sticky-2">NOMBRE DEL<br> VEHÍCULO</th>
      <!-- ░░ COLUMNAS DE DÍAS: solo sticky vertical ░░ -->
      <?php for($d=1;$d<=$dias_en_mes;$d++):
          $fs = sprintf('%04d-%02d-%02d',$anio_sel,$mes_sel,$d);
          $dw = date('w',strtotime($fs));
          $esFin = ($dw==0||$dw==6);
          $bg = $esFin ? 'background:#e2e3e5' : 'background:#c9e8e8';
      ?>
      <th class="dia-header <?php echo $esFin?'th-fs':''; ?>" style="<?php echo $bg; ?>" title="<?php echo $dias_semana_nombres[$dw]; ?>"><?php echo sprintf('%02d',$d).'-'.$meses_abrev[$mes_sel].'<br><small>'.$dias_semana_nombres[$dw].'</small>'; ?></th>
      <?php endfor; ?>
    </tr>
    <tr>
      <!-- ░░ FILA DE FILTROS: columnas fijas también sticky ░░ -->
      <td class="col-sticky-0"></td>
      <td class="col-sticky-1"><input type="text" class="form-control form-control-sm" id="filtro_no" placeholder="#"></td>
      <td class="col-sticky-2"><input type="text" class="form-control form-control-sm" id="filtro_nombre" placeholder="Buscar vehículo..."></td>
      <?php for($d=1;$d<=$dias_en_mes;$d++): ?><td></td><?php endfor; ?>
    </tr>
  </thead>
  <tbody>
<?php
$cnt = 0;
foreach($vehiculos_filtrados as $id_v => $v):
    $cnt++;
?>
    <tr>
      <!-- ░░ CELDAS FIJAS DEL BODY ░░ -->
      <td class="col-check col-sticky-0"><input type="checkbox" class="form-check-input"></td>
      <td class="col-no col-sticky-1"><?php echo $cnt; ?></td>
<td class="col-nombre col-sticky-2">
  <div class="veh-marca"><?php echo htmlspecialchars($v['MARCAV']); ?></div>
  <div class="veh-sub"><?php echo htmlspecialchars($v['SUBMARCAV']); ?></div>
  <div><span class="veh-placas"><?php echo htmlspecialchars($v['PLACASV']); ?></span></div>
</td>
      <!-- ░░ CELDAS DE DÍAS ░░ -->
      <?php
      $d = 1;
      while($d <= $dias_en_mes):
          $fdia = sprintf('%04d-%02d-%02d',$anio_sel,$mes_sel,$d);
            $est = estado_dia($id_v, $fdia, $asignaciones, $vencimientos, $colores_vehiculos);

      ?>
      <td class="<?php echo $est['clase']; ?> dia-col" style="<?php echo isset($est['style']) ? $est['style'] : ''; ?>"><?php echo $est['html']; ?></td>
      <?php
          $d++;
      endwhile;
      ?>
    </tr>
<?php endforeach; ?>
  </tbody>
</table>
</div>

<div class="hint-text">Mostrando 1 al <?php echo $total_registros; ?> de <?php echo $total_registros; ?> registros</div>

<script>
['filtro_nombre'].forEach(function(id){
    document.getElementById(id).addEventListener('input', function(){
        var f = this.value.toUpperCase();
        document.querySelectorAll('#tablaVehiculos tbody tr').forEach(function(fila){
            var cel = fila.querySelector('.col-nombre');
            if(cel) fila.style.display = cel.textContent.toUpperCase().indexOf(f)>-1?'':'none';
        });
    });
});
document.querySelectorAll('.col-check input[type="checkbox"]').forEach(function(cb){
    cb.addEventListener('change',function(){
        this.closest('tr').style.filter = this.checked ? 'brightness(65%) sepia(100%) saturate(200%) hue-rotate(0deg)' : 'none';
    });
});
</script>
</body>
</html>
