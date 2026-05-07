<?php
require_once '../inc/connection.php';

$mes = $_GET['mes'] ?? date('m');
$ano = $_GET['ano'] ?? date('Y');

$stmt = $conn->prepare("
SELECT 
    COUNT(*) as total_registros,
    SUM(pago = 1 AND isento = 0) as total_pagos,
    SUM(pago = 0 AND isento = 0) as total_pendentes,
    SUM(isento = 1) as total_isentos,

    SUM(CASE WHEN pago = 1 AND isento = 0 THEN valor_total ELSE 0 END) as total_arrecadado,
    SUM(CASE WHEN pago = 1 AND isento = 0 THEN valor_repasse ELSE 0 END) as total_repasse,
    SUM(CASE WHEN pago = 1 AND isento = 0 THEN valor_faccao ELSE 0 END) as total_faccao

FROM mensalidades
WHERE mes = ? AND ano = ?
");

$stmt->bind_param("ii", $mes, $ano);
$stmt->execute();
$res = $stmt->get_result();
$data = $res->fetch_assoc();

// porcentagem
$percent_pago = $data['total_registros'] > 0 
    ? round(($data['total_pagos'] / $data['total_registros']) * 100)
    : 0;

$sql = "
SELECT SUM(valor_faccao) as caixa_total
FROM mensalidades
WHERE pago = 1
";

$res = $conn->query($sql);
$caixa = $res->fetch_assoc()['caixa_total'];

$sql = "SELECT SUM(valor) as total_saidas FROM caixa_saida";
$res = $conn->query($sql);
$total_saidas = $res->fetch_assoc()['total_saidas'] ?? 0;

$caixa_real = $caixa - $total_saidas;
?>

<?php include '../inc/header.php'; ?>

<div class="container mt-4">

<h4>Financeiro - <?= $mes ?>/<?= $ano ?></h4>

<!-- FILTRO -->
<form class="form-inline mb-3">
    <input type="number" name="mes" class="form-control mr-2" value="<?= $mes ?>" min="1" max="12">
    <input type="number" name="ano" class="form-control mr-2" value="<?= $ano ?>">
    <button class="btn btn-dark">Filtrar</button>
</form>

<a href="pendentes.php?mes=<?= $mes ?>&ano=<?= $ano ?>" 
class="btn btn-warning mb-3">
Ver Pendentes
</a>

<div class="row">

    <div class="card bg-danger p-3">
Saídas<br>
<strong>R$ <?= number_format($total_saidas,2,',','.') ?></strong>
</div>

<div class="card bg-success p-3">
Caixa Real<br>
<strong>R$ <?= number_format($caixa_real,2,',','.') ?></strong>
</div>

    <div class="card bg-info p-3">
Caixa acumulado<br>
<strong>R$ <?= number_format($caixa,2,',','.') ?></strong>
</div>

<div class="col-md-3">
<div class="card bg-dark text-white p-3">
Total<br>
<strong><?= $data['total_registros'] ?></strong>
</div>
</div>

<div class="col-md-3">
<div class="card bg-success text-white p-3">
Pagos<br>
<strong><?= $data['total_pagos'] ?> (<?= $percent_pago ?>%)</strong>
</div>
</div>

<div class="col-md-3">
<div class="card bg-warning text-white p-3">
Pendentes<br>
<strong><?= $data['total_pendentes'] ?></strong>
</div>
</div>

<div class="col-md-3">
<div class="card bg-secondary text-white p-3">
Isentos<br>
<strong><?= $data['total_isentos'] ?></strong>
</div>
</div>

</div>

<hr>

<div class="row">

<div class="col-md-4">
<div class="card bg-primary text-white p-3">
Arrecadado<br>
<strong>R$ <?= number_format($data['total_arrecadado'],2,',','.') ?></strong>
</div>
</div>

<div class="col-md-4">
<div class="card bg-danger text-white p-3">
Repasse<br>
<strong>R$ <?= number_format($data['total_repasse'],2,',','.') ?></strong>
</div>
</div>

<div class="col-md-4">
<div class="card bg-info text-white p-3">
Facção<br>
<strong>R$ <?= number_format($data['total_faccao'],2,',','.') ?></strong>
</div>
</div>

</div>

<hr>

<canvas id="graficoFinanceiro"></canvas>

</div>

