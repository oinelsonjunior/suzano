<?php
    session_start();
    require_once ('../inc/general.php');

    $mes = isset($_GET['mes']) ? (int)$_GET['mes'] : (int)date('m');
    $ano = isset($_GET['ano']) ? (int)$_GET['ano'] : (int)date('Y');
    // Modo edição
    $edit_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $saida_edit = null;
    if ($edit_id > 0) {
        $se = $conn->prepare("SELECT * FROM caixa_saida WHERE id = ?");
        $se->bind_param("i", $edit_id);
        $se->execute();
        $saida_edit = $se->get_result()->fetch_assoc();
    }

    $meses_pt = [
        1=>'Janeiro',2=>'Fevereiro',3=>'Março',4=>'Abril',
        5=>'Maio',6=>'Junho',7=>'Julho',8=>'Agosto',
        9=>'Setembro',10=>'Outubro',11=>'Novembro',12=>'Dezembro'
    ];
    $nome_mes = $meses_pt[$mes] ?? $mes;
?>
<!DOCTYPE html>
<html lang="pt-br">
    <base href="../">
    <head>
        <meta charset="utf-8" />
        <title>Abutre's MC | Nova Saída</title>
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <script src="https://ajax.googleapis.com/ajax/libs/webfont/1.6.16/webfont.js"></script>
        <script>
            WebFont.load({
                google: {"families":["Poppins:300,400,500,600,700","Roboto:300,400,500,600,700"]},
                active: function() { sessionStorage.fonts = true; }
            });
        </script>
        <link href="css/style.bundle.css" rel="stylesheet" type="text/css" />
    </head>

    <body class="m-page--wide m-header--fixed m-header--fixed-mobile m-footer--push m-aside--offcanvas-default">
        <div class="m-grid m-grid--hor m-grid--root m-page">

            <!-- HEADER -->
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

            <!-- BODY -->
            <div class="m-grid__item m-grid__item--fluid m-grid m-grid--hor-desktop m-grid--desktop m-body">
                <div class="m-grid__item m-grid__item--fluid m-grid m-grid--ver m-container m-container--responsive m-container--xxl m-page__container">
                    <div class="m-grid__item m-grid__item--fluid m-wrapper">

                        <!-- Subheader -->
                        <div class="m-subheader">
                            <div class="d-flex align-items-center">
                                <div class="mr-auto">
                                    <h3 class="m-subheader__title" style="text-transform:uppercase;">
                                        Nova Saída de Caixa
                                    </h3>
                                    <ul class="m-subheader__breadcrumbs m-nav m-nav--inline">
                                        <li class="m-nav__item m-nav__item--home">
                                            <a href="index" class="m-nav__link m-nav__link--icon">
                                                <i class="m-nav__link-icon la la-home"></i>
                                            </a>
                                        </li>
                                        <li class="m-nav__separator">—</li>
                                        <li class="m-nav__item">
                                            <a href="financeiro/dashboard?mes=<?= $mes ?>&ano=<?= $ano ?>" class="m-nav__link">
                                                <span class="m-nav__link-text">Financeiro</span>
                                            </a>
                                        </li>
                                        <li class="m-nav__separator">—</li>
                                        <li class="m-nav__item">
                                            <a href="financeiro/fluxo_caixa.php?mes=<?= $mes ?>&ano=<?= $ano ?>" class="m-nav__link">
                                                <span class="m-nav__link-text">Fluxo de Caixa</span>
                                            </a>
                                        </li>
                                        <li class="m-nav__separator">—</li>
                                        <li class="m-nav__item">
                                            <span class="m-nav__link-text">Nova Saída</span>
                                        </li>
                                    </ul>
                                </div>
                                <div>
                                    <a href="financeiro/fluxo_caixa.php?mes=<?= $mes ?>&ano=<?= $ano ?>"
                                       class="btn btn-sm btn-secondary m-btn m-btn--icon m-btn--pill">
                                        <span>
                                            <i class="la la-arrow-left"></i>
                                            <span>Voltar</span>
                                        </span>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="m-content">
                            <div class="row">
                                <div class="col-xl-6 offset-xl-3">
                                    <div class="m-portlet m-portlet--mobile">
                                        <div class="m-portlet__head">
                                            <div class="m-portlet__head-caption">
                                                <div class="m-portlet__head-title">
                                                    <h3 class="m-portlet__head-text">
                                                        <i class="la la-arrow-circle-down m--font-danger"></i>
                                                        <?= $edit_id > 0 ? "Editar Saída" : "Registrar Saída — $nome_mes/$ano" ?>
                                                    </h3>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="m-portlet__body">
                                            <form method="POST" action="financeiro/saida_save.php">
                                                <input type="hidden" name="mes" value="<?= $mes ?>">
                                                <input type="hidden" name="ano" value="<?= $ano ?>">
                                                <input type="hidden" name="id"  value="<?= $edit_id ?>">

                                                <div class="form-group m-form__group">
                                                    <label class="form-control-label">Descrição <span class="m--font-danger">*</span></label>
                                                    <input type="text" name="descricao" class="form-control m-input"
                                                           value="<?= htmlspecialchars($saida_edit['descricao'] ?? '') ?>"
                                                           placeholder="Ex: Combustível, Aluguel do espaço..." required>
                                                </div>

                                                <div class="form-group m-form__group">
                                                    <label class="form-control-label">Valor (R$) <span class="m--font-danger">*</span></label>
                                                    <input type="text" name="valor" id="valor" class="form-control m-input"
                                                           value="<?= $saida_edit ? number_format($saida_edit['valor'],2,',','.') : '' ?>"
                                                           placeholder="0,00" required>
                                                </div>

                                                <div class="form-group m-form__group">
                                                    <label class="form-control-label">Data <span class="m--font-danger">*</span></label>
                                                    <input type="date" name="data_saida" class="form-control m-input"
                                                           value="<?= $saida_edit['data_saida'] ?? date('Y-m-d') ?>" required>
                                                </div>

                                                <div class="m-form__actions m-form__actions--right">
                                                    <a href="financeiro/fluxo_caixa.php?mes=<?= $mes ?>&ano=<?= $ano ?>"
                                                       class="btn btn-secondary m-btn m-btn--pill">
                                                        Cancelar
                                                    </a>
                                                    &nbsp;
                                                    <button type="submit"
                                                            class="btn btn-danger m-btn m-btn--icon m-btn--pill">
                                                        <span>
                                                            <i class="la la-save"></i>
                                                            <span>Registrar Saída</span>
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

            <?php require_once ('../inc/footer.php'); ?>
        </div>

        <div class="m-scroll-top m-scroll-top--skin-top" data-toggle="m-scroll-top" data-scroll-offset="500" data-scroll-speed="300">
            <i class="la la-arrow-up"></i>
        </div>

        <script src="js/vendors.bundle.js" type="text/javascript"></script>
        <script src="js/scripts.bundle.js" type="text/javascript"></script>
        <script>
        // Máscara de valor monetário
        document.getElementById('valor').addEventListener('input', function(e) {
            let v = e.target.value.replace(/\D/g, '');
            v = (parseInt(v) / 100).toFixed(2);
            e.target.value = isNaN(v) ? '' : v.replace('.', ',');
        });
        </script>

    </body>
</html>