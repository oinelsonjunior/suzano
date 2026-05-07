<?php
require_once '../inc/connection.php';

$sql = "
    SELECT c.apelido, COUNT(*) AS meses_atrasados
    FROM mensalidades m
    JOIN cadastro_integrante c ON c.id = m.integrante_id
    WHERE m.pago = 0
      AND m.isento = 0
    GROUP BY m.integrante_id
    HAVING meses_atrasados >= 2
    ORDER BY meses_atrasados DESC
";

$result = $conn->query($sql);

$inadimplentes = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $inadimplentes[] = $row;
    }
}
?>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-danger text-white d-flex align-items-center gap-2">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <h5 class="mb-0">Inadimplentes</h5>
        <span class="badge bg-white text-danger ms-auto"><?= count($inadimplentes) ?></span>
    </div>

    <div class="card-body p-0">
        <?php if (empty($inadimplentes)): ?>
            <p class="text-muted text-center py-4 mb-0">
                <i class="bi bi-check-circle me-1"></i> Nenhum inadimplente encontrado.
            </p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th class="text-center">Meses em atraso</th>
                            <th class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($inadimplentes as $inadimplente): ?>
                            <tr>
                                <td><?= htmlspecialchars($inadimplente['apelido'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="text-center">
                                    <span class="badge bg-danger">
                                        <?= (int) $inadimplente['meses_atrasados'] ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="mensalidades.php?apelido=<?= urlencode($inadimplente['apelido']) ?>"
                                       class="btn btn-sm btn-outline-light"
                                       title="Ver mensalidades">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>