<?php
require_once '../inc/connection.php';

$ano = $_GET['ano'] ?? date('Y');

$sql = "
SELECT mes,
SUM(valor_total) as arrecadado
FROM mensalidades
WHERE pago = 1 AND ano = ?
GROUP BY mes
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $ano);
$stmt->execute();
$res = $stmt->get_result();

$dados = [];
while($r = $res->fetch_assoc()) {
    $dados[$r['mes']] = $r['arrecadado'];
}
?>

<canvas id="graficoAno"></canvas>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById('graficoAno'), {
    type: 'bar',
    data: {
        labels: [1,2,3,4,5,6,7,8,9,10,11,12],
        datasets: [{
            label: 'Arrecadação',
            data: <?= json_encode(array_values($dados)) ?>
        }]
    }
});
</script>