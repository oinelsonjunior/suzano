<?php
require_once 'connection.php';

$mes = 5;
$ano = date('Y');

$sql = "SELECT * FROM cadastro_integrante WHERE status NOT IN (2,3)";
$res = $conn->query($sql);

while ($row = $res->fetch_assoc()) {

    $integrante_id = $row['id'];
    $patente = $row['patente'];
    $status = $row['status'];

    // regra de isenção automática
    $isento = ($patente == 6) ? 1 : 0;

    $stmt = $conn->prepare("
        INSERT IGNORE INTO mensalidades 
        (integrante_id, ano, mes, isento, patente_no_mes, status_no_mes)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param("iiiiii",
        $integrante_id,
        $ano,
        $mes,
        $isento,
        $patente,
        $status
    );

    $stmt->execute();
}

echo "Mensalidades geradas!";