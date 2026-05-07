<?php
    session_start();
    require_once ('../inc/general.php');

    $tipos_evento = [
        1 => 'Sede Estadual (padrão)',
        2 => 'Sede Estadual (reunião geral)',
        3 => 'Sede Suzano (reunião)',
        4 => 'Sede Suzano (confraternização)',
        5 => 'Evento Fora',
        6 => 'Evento Obrigatório',
    ];
?>
<!DOCTYPE html>
<html lang="pt-br">
    <base href="../">
    <head>
        <meta charset="utf-8" />
        <title>Abutre's MC | Novo Evento</title>
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
                                    <h3 class="m-subheader__title" style="text-transform:uppercase;">Novo Evento</h3>
                                    <ul class="m-subheader__breadcrumbs m-nav m-nav--inline">
                                        <li class="m-nav__item m-nav__item--home">
                                            <a href="index" class="m-nav__link m-nav__link--icon">
                                                <i class="m-nav__link-icon la la-home"></i>
                                            </a>
                                        </li>
                                        <li class="m-nav__separator">—</li>
                                        <li class="m-nav__item">
                                            <a href="frequencia/index.php" class="m-nav__link">
                                                <span class="m-nav__link-text">Frequência</span>
                                            </a>
                                        </li>
                                        <li class="m-nav__separator">—</li>
                                        <li class="m-nav__item">
                                            <span class="m-nav__link-text">Novo Evento</span>
                                        </li>
                                    </ul>
                                </div>
                                <div>
                                    <a href="frequencia/index.php"
                                       class="btn btn-sm btn-secondary m-btn m-btn--icon m-btn--pill">
                                        <span><i class="la la-arrow-left"></i><span>Voltar</span></span>
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
                                                        <i class="la la-calendar-plus-o m--font-brand"></i>
                                                        Cadastrar Evento
                                                    </h3>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="m-portlet__body">
                                            <form method="POST" action="frequencia/evento_save.php">

                                                <div class="form-group m-form__group">
                                                    <label class="form-control-label">
                                                        Tipo de Evento <span class="m--font-danger">*</span>
                                                    </label>
                                                    <select name="tipo" id="tipo" class="form-control m-input" required onchange="preencherNome()">
                                                        <option value="">Selecione...</option>
                                                        <?php foreach ($tipos_evento as $id => $nome): ?>
                                                            <option value="<?= $id ?>"><?= $nome ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>

                                                <div class="form-group m-form__group">
                                                    <label class="form-control-label">
                                                        Nome / Descrição <span class="m--font-danger">*</span>
                                                    </label>
                                                    <input type="text" name="nome" id="nome" class="form-control m-input"
                                                           placeholder="Ex: Reunião Mensal Maio 2026" required>
                                                    <span class="m-form__help">Preenchido automaticamente ao selecionar o tipo.</span>
                                                </div>

                                                <div class="form-group m-form__group">
                                                    <label class="form-control-label">
                                                        Data do Evento <span class="m--font-danger">*</span>
                                                    </label>
                                                    <input type="date" name="data_evento" class="form-control m-input"
                                                           value="<?= date('Y-m-d') ?>" required>
                                                </div>

                                                <div class="form-group m-form__group">
                                                    <label class="form-control-label">Observação</label>
                                                    <textarea name="observacao" class="form-control m-input" rows="2"
                                                              placeholder="Informações adicionais (opcional)"></textarea>
                                                </div>

                                                <div class="m-alert m-alert--icon m-alert--air m-alert--info" role="alert">
                                                    <div class="m-alert__icon">
                                                        <i class="la la-info-circle"></i>
                                                    </div>
                                                    <div class="m-alert__text">
                                                        Ao salvar, a chamada será criada automaticamente para todos os integrantes
                                                        <strong>ativos e suspensos</strong> da facção.
                                                    </div>
                                                </div>

                                                <div class="m-form__actions m-form__actions--right m--margin-top-20">
                                                    <a href="frequencia/index.php" class="btn btn-secondary m-btn m-btn--pill">
                                                        Cancelar
                                                    </a>
                                                    &nbsp;
                                                    <button type="submit" class="btn btn-brand m-btn m-btn--icon m-btn--pill">
                                                        <span><i class="la la-save"></i><span>Salvar e Abrir Chamada</span></span>
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
        const tipos = <?= json_encode($tipos_evento) ?>;
        function preencherNome() {
            const sel = document.getElementById('tipo');
            const nome = document.getElementById('nome');
            const tipo = sel.value;
            if (tipo && tipos[tipo]) {
                const mes = new Date().toLocaleString('pt-BR', {month: 'long'});
                const ano  = new Date().getFullYear();
                nome.value = tipos[tipo] + ' — ' + mes.charAt(0).toUpperCase() + mes.slice(1) + ' ' + ano;
            }
        }
        </script>
    </body>
</html>
