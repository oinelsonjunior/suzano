<?php
session_start();
require_once('../inc/general.php');

$id     = isset($_GET['id'])     ? (int)$_GET['id']     : 0;
$mes    = isset($_GET['mes'])    ? (int)$_GET['mes']     : (int)date('m');
$ano    = isset($_GET['ano'])    ? (int)$_GET['ano']     : (int)date('Y');
$origem = $_GET['origem'] ?? 'pendentes';

if ($id <= 0) {
    header("Location: ../financeiro/pendentes.php?mes=$mes&ano=$ano");
    exit;
}

// Buscar dados da mensalidade
$stmt = $conn->prepare("
    SELECT m.id, m.isento, m.integrante_id, m.mes, m.ano,
           ci.apelido, ci.nome
    FROM mensalidades m
    JOIN cadastro_integrante ci ON ci.id = m.integrante_id
    WHERE m.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$mens = $stmt->get_result()->fetch_assoc();

if (!$mens) {
    header("Location: ../financeiro/pendentes.php?mes=$mes&ano=$ano");
    exit;
}

// Se já tem isenção registrada, buscar motivo
$isencao_atual = null;
if ($mens['isento']) {
    $stmt2 = $conn->prepare("
        SELECT i.*, u.username as admin_nome
        FROM isencoes i
        LEFT JOIN users u ON u.id = i.admin_id
        WHERE i.mensalidade_id = ? OR (i.integrante_id = ? AND i.mes = ? AND i.ano = ?)
        ORDER BY i.created_at DESC LIMIT 1
    ");
    $stmt2->bind_param("iiii", $id, $mens['integrante_id'], $mens['mes'], $mens['ano']);
    $stmt2->execute();
    $isencao_atual = $stmt2->get_result()->fetch_assoc();
}

$meses_pt = [
    1=>'Janeiro',2=>'Fevereiro',3=>'Março',4=>'Abril',
    5=>'Maio',6=>'Junho',7=>'Julho',8=>'Agosto',
    9=>'Setembro',10=>'Outubro',11=>'Novembro',12=>'Dezembro'
];
?>
<!DOCTYPE html>
<html lang="pt-br">
    <base href="../">
    <head>
        <meta charset="utf-8"/>
        <title>Abutre's MC | Isenção</title>
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <script src="https://ajax.googleapis.com/ajax/libs/webfont/1.6.16/webfont.js"></script>
        <script>
            WebFont.load({
                google: {"families":["Poppins:300,400,500,600,700","Roboto:300,400,500,600,700"]},
                active: function() { sessionStorage.fonts = true; }
            });
        </script>
        <link href="css/style.bundle.css" rel="stylesheet" type="text/css"/>
    </head>
    <body class="m-page--wide m-header--fixed m-header--fixed-mobile m-footer--push m-aside--offcanvas-default">
    <div class="m-grid m-grid--hor m-grid--root m-page">

        <header class="m-grid__item m-header" data-minimize="minimize" data-minimize-offset="200" data-minimize-mobile-offset="200">
            <div class="m-header__top">
                <div class="m-container m-container--responsive m-container--xxl m-container--full-height m-page__container">
                    <div class="m-stack m-stack--ver m-stack--desktop">
                        <div class="m-stack__item m-brand">
                            <div class="m-stack m-stack--ver m-stack--general m-stack--inline">
                                <div class="m-stack__item m-stack__item--middle m-brand__logo">
                                    <a href="index" class="m-brand__logo-wrapper">
                                        <img alt="" src="images/logo.png"/>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php include_once('../inc/topbar.php'); ?>
                    </div>
                </div>
            </div>
            <?php include_once('../inc/header_bottom.php'); ?>
        </header>

        <div class="m-grid__item m-grid__item--fluid m-grid m-grid--hor-desktop m-grid--desktop m-body">
            <div class="m-grid__item m-grid__item--fluid m-grid m-grid--ver m-container m-container--responsive m-container--xxl m-page__container">
                <div class="m-grid__item m-grid__item--fluid m-wrapper">

                    <div class="m-subheader">
                        <div class="d-flex align-items-center">
                            <div class="mr-auto">
                                <h3 class="m-subheader__title" style="text-transform:uppercase;">
                                    <?= $mens['isento'] ? 'Remover Isenção' : 'Registrar Isenção' ?>
                                </h3>
                                <ul class="m-subheader__breadcrumbs m-nav m-nav--inline">
                                    <li class="m-nav__item m-nav__item--home">
                                        <a href="index" class="m-nav__link m-nav__link--icon">
                                            <i class="m-nav__link-icon la la-home"></i>
                                        </a>
                                    </li>
                                    <li class="m-nav__separator">—</li>
                                    <li class="m-nav__item">
                                        <a href="financeiro/pendentes.php?mes=<?= $mes ?>&ano=<?= $ano ?>" class="m-nav__link">
                                            <span class="m-nav__link-text">Pendentes</span>
                                        </a>
                                    </li>
                                    <li class="m-nav__separator">—</li>
                                    <li class="m-nav__item">
                                        <span class="m-nav__link-text">Isenção</span>
                                    </li>
                                </ul>
                            </div>
                            <div>
                                <a href="financeiro/<?= $origem === 'list' ? 'list' : 'pendentes' ?>.php?mes=<?= $mes ?>&ano=<?= $ano ?>"
                                   class="btn btn-sm btn-secondary m-btn m-btn--icon m-btn--pill">
                                    <span><i class="la la-arrow-left"></i><span>Voltar</span></span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="m-content">
                        <div class="row">
                            <div class="col-xl-6 offset-xl-3">

                                <!-- Card de contexto -->
                                <div class="m-portlet m-portlet--mobile">
                                    <div class="m-portlet__body">
                                        <div class="d-flex align-items-center gap-3">
                                            <div style="width:50px;height:50px;border-radius:50%;background:#716aca;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                                <i class="flaticon-user" style="font-size:1.8rem;color:#fff;"></i>
                                            </div>
                                            <div style="margin-left:15px;">
                                                <div class="m--font-boldest" style="font-size:16px;">
                                                    <?= htmlspecialchars($mens['apelido']) ?>
                                                </div>
                                                <div class="m--font-metal" style="font-size:13px;">
                                                    <?= htmlspecialchars($mens['nome']) ?>
                                                </div>
                                                <div class="m--margin-top-5">
                                                    <span class="m-badge m-badge--info m-badge--wide">
                                                        <?= $meses_pt[$mens['mes']] ?>/<?= $mens['ano'] ?>
                                                    </span>
                                                    <?php if ($mens['isento']): ?>
                                                        <span class="m-badge m-badge--metal m-badge--wide">Atualmente Isento</span>
                                                    <?php else: ?>
                                                        <span class="m-badge m-badge--warning m-badge--wide">Pendente</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Isenção atual (se existir) -->
                                <?php if ($mens['isento'] && $isencao_atual): ?>
                                <div class="m-portlet m-portlet--mobile" style="border:1px solid #ebedf2;">
                                    <div class="m-portlet__head">
                                        <div class="m-portlet__head-caption">
                                            <div class="m-portlet__head-title">
                                                <h3 class="m-portlet__head-text">
                                                    <i class="la la-info-circle m--font-info"></i>
                                                    Isenção Registrada
                                                </h3>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="m-portlet__body">
                                        <div class="m-widget1">
                                            <div class="m-widget1__item">
                                                <div class="row m-row--no-padding">
                                                    <div class="col">
                                                        <h5 class="m-widget1__title">Motivo</h5>
                                                        <p class="m--font-metal m--margin-top-5">
                                                            <?= htmlspecialchars($isencao_atual['motivo'] ?: 'Não informado') ?>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="m-widget1__item">
                                                <div class="row m-row--no-padding align-items-center">
                                                    <div class="col">
                                                        <h5 class="m-widget1__title">Registrado por</h5>
                                                    </div>
                                                    <div class="col m--align-right">
                                                        <span class="m--font-metal">
                                                            <?= htmlspecialchars($isencao_atual['admin_nome'] ?? 'Sistema') ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="m-widget1__item">
                                                <div class="row m-row--no-padding align-items-center">
                                                    <div class="col">
                                                        <h5 class="m-widget1__title">Data</h5>
                                                    </div>
                                                    <div class="col m--align-right">
                                                        <span class="m--font-metal">
                                                            <?= $isencao_atual['created_at']
                                                                ? date('d/m/Y H:i', strtotime($isencao_atual['created_at']))
                                                                : '—' ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <!-- Formulário de ação -->
                                <div class="m-portlet m-portlet--mobile">
                                    <div class="m-portlet__head">
                                        <div class="m-portlet__head-caption">
                                            <div class="m-portlet__head-title">
                                                <h3 class="m-portlet__head-text">
                                                    <?php if ($mens['isento']): ?>
                                                        <i class="la la-times-circle m--font-danger"></i>
                                                        Remover Isenção
                                                    <?php else: ?>
                                                        <i class="la la-minus-circle m--font-metal"></i>
                                                        Registrar Isenção
                                                    <?php endif; ?>
                                                </h3>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="m-portlet__body">
                                        <form method="POST" action="financeiro/isento_save.php">
                                            <input type="hidden" name="id"     value="<?= $id ?>">
                                            <input type="hidden" name="mes"    value="<?= $mes ?>">
                                            <input type="hidden" name="ano"    value="<?= $ano ?>">
                                            <input type="hidden" name="origem" value="<?= htmlspecialchars($origem) ?>">
                                            <input type="hidden" name="acao"   value="<?= $mens['isento'] ? 'remover' : 'isentar' ?>">

                                            <?php if (!$mens['isento']): ?>
                                            <div class="form-group m-form__group">
                                                <label class="form-control-label">
                                                    Motivo da Isenção <span class="m--font-danger">*</span>
                                                </label>
                                                <textarea name="motivo" class="form-control m-input" rows="3"
                                                          placeholder="Ex: Afastamento por doença, dificuldade financeira comprovada, isenção por patente..."
                                                          required></textarea>
                                                <span class="m-form__help">
                                                    Este motivo ficará registrado no histórico do integrante.
                                                </span>
                                            </div>
                                            <?php else: ?>
                                            <div class="m-alert m-alert--icon m-alert--air m-alert--warning" role="alert">
                                                <div class="m-alert__icon">
                                                    <i class="la la-exclamation-triangle"></i>
                                                </div>
                                                <div class="m-alert__text">
                                                    Ao remover a isenção, o integrante voltará a constar como
                                                    <strong>pendente</strong> nas mensalidades de
                                                    <?= $meses_pt[$mens['mes']] ?>/<?= $mens['ano'] ?>.
                                                </div>
                                            </div>
                                            <?php endif; ?>

                                            <div class="m-form__actions d-flex justify-content-between m--margin-top-20">
                                                <a href="financeiro/<?= $origem === 'list' ? 'list' : 'pendentes' ?>.php?mes=<?= $mes ?>&ano=<?= $ano ?>"
                                                   class="btn btn-secondary m-btn m-btn--pill">
                                                    Cancelar
                                                </a>
                                                <button type="submit"
                                                        class="btn <?= $mens['isento'] ? 'btn-danger' : 'btn-metal' ?> m-btn m-btn--icon m-btn--pill">
                                                    <span>
                                                        <i class="la la-<?= $mens['isento'] ? 'times' : 'check' ?>"></i>
                                                        <span><?= $mens['isento'] ? 'Remover Isenção' : 'Confirmar Isenção' ?></span>
                                                    </span>
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <?php require_once('../inc/footer.php'); ?>
    </div>

    <div class="m-scroll-top m-scroll-top--skin-top" data-toggle="m-scroll-top" data-scroll-offset="500" data-scroll-speed="300">
        <i class="la la-arrow-up"></i>
    </div>
    <script src="js/vendors.bundle.js"></script>
    <script src="js/scripts.bundle.js"></script>
</body>
</html>
