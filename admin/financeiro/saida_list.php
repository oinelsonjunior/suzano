<?php
    session_start();
    require_once('../inc/general.php');

    // Filtro de período
    $ano = isset($_GET['ano']) ? (int)$_GET['ano'] : (int)date('Y');
    $mes = isset($_GET['mes']) ? (int)$_GET['mes'] : 0; // 0 = todos

    $meses_pt = [
        0=>'Todos', 1=>'Janeiro', 2=>'Fevereiro', 3=>'Março', 4=>'Abril',
        5=>'Maio', 6=>'Junho', 7=>'Julho', 8=>'Agosto',
        9=>'Setembro', 10=>'Outubro', 11=>'Novembro', 12=>'Dezembro'
    ];

    $where_mes = $mes > 0 ? "AND MONTH(data_saida) = $mes" : "";
    $saidas = $conn->query("
        SELECT * FROM caixa_saida
        WHERE YEAR(data_saida) = $ano $where_mes
        ORDER BY data_saida DESC
    ")->fetch_all(MYSQLI_ASSOC);

    $total = array_sum(array_column($saidas, 'valor'));

    // Caixa total arrecadado (para contexto)
    $res_caixa = $conn->query("SELECT SUM(valor_faccao) as caixa FROM mensalidades WHERE pago = 1");
    $caixa_bruto = (float)($res_caixa->fetch_assoc()['caixa'] ?? 0);

    // Total geral de saídas (todas)
    $res_total = $conn->query("SELECT SUM(valor) as total FROM caixa_saida");
    $total_geral = (float)($res_total->fetch_assoc()['total'] ?? 0);

    $saldo = $caixa_bruto - $total_geral;
?>
<!DOCTYPE html>
<html lang="pt-br">
    <base href="../">
    <head>
        <meta charset="utf-8"/>
        <title>Abutre's MC | Saídas de Caixa</title>
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <script src="https://ajax.googleapis.com/ajax/libs/webfont/1.6.16/webfont.js"></script>
        <script>WebFont.load({google:{"families":["Poppins:300,400,500,600,700","Roboto:300,400,500,600,700"]},active:function(){sessionStorage.fonts=true;}});</script>
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
                                <h3 class="m-subheader__title" style="text-transform:uppercase;">Saídas de Caixa</h3>
                                <ul class="m-subheader__breadcrumbs m-nav m-nav--inline">
                                    <li class="m-nav__item m-nav__item--home">
                                        <a href="index" class="m-nav__link m-nav__link--icon">
                                            <i class="m-nav__link-icon la la-home"></i>
                                        </a>
                                    </li>
                                    <li class="m-nav__separator">—</li>
                                    <li class="m-nav__item">
                                        <a href="financeiro/dashboard" class="m-nav__link">
                                            <span class="m-nav__link-text">Financeiro</span>
                                        </a>
                                    </li>
                                    <li class="m-nav__separator">—</li>
                                    <li class="m-nav__item"><span class="m-nav__link-text">Saídas</span></li>
                                </ul>
                            </div>
                            <div>
                                <a href="financeiro/saida_form.php"
                                   class="btn btn-sm btn-danger m-btn m-btn--icon m-btn--pill">
                                    <span><i class="la la-plus"></i><span>Nova Saída</span></span>
                                </a>
                                &nbsp;
                                <a href="financeiro/fluxo_caixa.php"
                                   class="btn btn-sm btn-secondary m-btn m-btn--icon m-btn--pill">
                                    <span><i class="la la-arrow-left"></i><span>Fluxo de Caixa</span></span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="m-content">

                        <?php if (isset($_GET['ok'])): ?>
                        <div class="alert alert-success m-alert--air">
                            <i class="la la-check-circle"></i>&nbsp;
                            <?= $_GET['ok'] === 'deletada' ? 'Saída excluída com sucesso.' : 'Saída salva com sucesso.' ?>
                        </div>
                        <?php endif; ?>

                        <!-- KPIs -->
                        <div class="m-portlet m-portlet--mobile">
                            <div class="m-portlet__body m-portlet__body--no-padding">
                                <div class="row m-row--no-padding m-row--col-separator-xl">
                                    <div class="col-md-4">
                                        <div class="m-widget24"><div class="m-widget24__item">
                                            <h4 class="m-widget24__title">Saídas no período</h4><br>
                                            <span class="m-widget24__desc"><?= $meses_pt[$mes] ?>/<?= $ano ?></span>
                                            <span class="m-widget24__stats m--font-danger">
                                                R$ <?= number_format($total, 2, ',', '.') ?>
                                            </span>
                                            <div class="m--space-10"></div>
                                            <div class="progress m-progress--sm">
                                                <div class="progress-bar m--bg-danger" style="width:100%"></div>
                                            </div>
                                            <span class="m-widget24__change"><?= count($saidas) ?> registro<?= count($saidas)!=1?'s':'' ?></span>
                                            <span class="m-widget24__number"><?= count($saidas) ?></span>
                                        </div></div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="m-widget24"><div class="m-widget24__item">
                                            <h4 class="m-widget24__title">Total Geral de Saídas</h4><br>
                                            <span class="m-widget24__desc">Todas as saídas registradas</span>
                                            <span class="m-widget24__stats m--font-danger">
                                                R$ <?= number_format($total_geral, 2, ',', '.') ?>
                                            </span>
                                            <div class="m--space-10"></div>
                                            <?php $pc = $caixa_bruto > 0 ? min(100, round(($total_geral/$caixa_bruto)*100)) : 0; ?>
                                            <div class="progress m-progress--sm">
                                                <div class="progress-bar m--bg-danger" style="width:<?= $pc ?>%"></div>
                                            </div>
                                            <span class="m-widget24__change">Sobre o arrecadado</span>
                                            <span class="m-widget24__number"><?= $pc ?>%</span>
                                        </div></div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="m-widget24"><div class="m-widget24__item">
                                            <h4 class="m-widget24__title">Saldo Real do Caixa</h4><br>
                                            <span class="m-widget24__desc">Arrecadado menos saídas</span>
                                            <span class="m-widget24__stats <?= $saldo >= 0 ? 'm--font-success' : 'm--font-danger' ?>">
                                                R$ <?= number_format($saldo, 2, ',', '.') ?>
                                            </span>
                                            <div class="m--space-10"></div>
                                            <?php $pc_s = $caixa_bruto > 0 ? min(100, round(($saldo/$caixa_bruto)*100)) : 0; ?>
                                            <div class="progress m-progress--sm">
                                                <div class="progress-bar <?= $saldo >= 0 ? 'm--bg-success' : 'm--bg-danger' ?>"
                                                     style="width:<?= abs($pc_s) ?>%"></div>
                                            </div>
                                            <span class="m-widget24__change">Sobre o arrecadado</span>
                                            <span class="m-widget24__number"><?= $pc_s ?>%</span>
                                        </div></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Filtro -->
                        <div class="m-portlet m-portlet--mobile">
                            <div class="m-portlet__body">
                                <form class="form-inline" method="GET">
                                    <label class="mr-2 font-weight-bold">Período:</label>
                                    <select name="mes" class="form-control form-control-sm mr-2">
                                        <?php foreach ($meses_pt as $n => $nm): ?>
                                            <option value="<?= $n ?>" <?= $n == $mes ? 'selected' : '' ?>><?= $nm ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="number" name="ano" class="form-control form-control-sm mr-2"
                                           value="<?= $ano ?>" min="2020" max="2099" style="width:90px;">
                                    <button class="btn btn-sm btn-dark m-btn m-btn--icon m-btn--pill" type="submit">
                                        <span><i class="la la-search"></i><span>Filtrar</span></span>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Tabela -->
                        <div class="m-portlet m-portlet--mobile">
                            <div class="m-portlet__head">
                                <div class="m-portlet__head-caption">
                                    <div class="m-portlet__head-title">
                                        <h3 class="m-portlet__head-text">
                                            <i class="la la-arrow-circle-down m--font-danger"></i>
                                            Saídas — <?= $meses_pt[$mes] ?><?= $mes > 0 ? '/' : ' ' ?><?= $ano ?>
                                            <small class="m--font-metal m--margin-left-10" style="font-size:13px;">
                                                <?= count($saidas) ?> registro<?= count($saidas)!=1?'s':'' ?>
                                            </small>
                                        </h3>
                                    </div>
                                </div>
                                <div class="m-portlet__head-tools">
                                    <a href="financeiro/saida_form.php"
                                       class="btn btn-sm btn-outline-danger m-btn m-btn--pill">
                                        <i class="la la-plus"></i> Nova Saída
                                    </a>
                                </div>
                            </div>
                            <div class="m-portlet__body">
                                <?php if (empty($saidas)): ?>
                                    <div class="m--align-center" style="padding:40px 0;">
                                        <i class="la la-inbox m--font-metal" style="font-size:3rem;"></i>
                                        <h3 class="m--font-metal m--margin-top-10">Nenhuma saída no período selecionado.</h3>
                                    </div>
                                <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover m-table m-table--head-bg-danger">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Data</th>
                                                <th>Descrição</th>
                                                <th class="m--align-right">Valor</th>
                                                <th class="m--align-center">Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($saidas as $i => $s): ?>
                                            <tr>
                                                <td class="m--font-metal"><?= $i + 1 ?></td>
                                                <td class="m--font-metal" style="white-space:nowrap;">
                                                    <?= date('d/m/Y', strtotime($s['data_saida'])) ?>
                                                </td>
                                                <td class="m--font-boldest">
                                                    <?= htmlspecialchars($s['descricao']) ?>
                                                </td>
                                                <td class="m--align-right m--font-danger m--font-boldest">
                                                    R$ <?= number_format($s['valor'], 2, ',', '.') ?>
                                                </td>
                                                <td class="m--align-center" style="white-space:nowrap;">
                                                    <a href="financeiro/saida_form.php?id=<?= $s['id'] ?>&mes=<?= (int)date('m', strtotime($s['data_saida'])) ?>&ano=<?= (int)date('Y', strtotime($s['data_saida'])) ?>"
                                                       class="btn btn-sm btn-warning m-btn m-btn--icon m-btn--pill"
                                                       title="Editar">
                                                        <span><i class="la la-pencil"></i></span>
                                                    </a>
                                                    <a href="financeiro/saida_delete.php?id=<?= $s['id'] ?>&ano=<?= $ano ?>&mes=<?= $mes ?>"
                                                       class="btn btn-sm btn-danger m-btn m-btn--icon m-btn--pill"
                                                       title="Excluir"
                                                       onclick="return confirm('Excluir a saída \'<?= htmlspecialchars(addslashes($s['descricao'])) ?>\'? Esta ação não pode ser desfeita.')">
                                                        <span><i class="la la-trash"></i></span>
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot>
                                            <tr class="m-table__foot m--font-boldest">
                                                <td colspan="3">
                                                    Total do período
                                                </td>
                                                <td class="m--align-right m--font-danger">
                                                    R$ <?= number_format($total, 2, ',', '.') ?>
                                                </td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                <?php endif; ?>
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