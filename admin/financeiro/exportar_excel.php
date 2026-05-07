<?php
require_once '../inc/connection.php';

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=financeiro.xls");

echo "Nome\tMes\tValor\n";

$sql = "
SELECT c.apelido, m.mes, m.valor_total
FROM mensalidades m
JOIN cadastro_integrante c ON c.id = m.integrante_id
";

$res = $conn->query($sql);

while($r = $res->fetch_assoc()) {
    echo "{$r['apelido']}\t{$r['mes']}\t{$r['valor_total']}\n";
}