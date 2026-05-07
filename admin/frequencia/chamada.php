<?php
    session_start();
    require_once ('../inc/general.php');

    $evento_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($evento_id <= 0) {
        header("Location: ../frequencia/index.php");
        exit;
    }

    $tipos_evento = [
        1 => 'Sede Estadual (padrão)',
        2 => 'Sede Estadual (reunião geral)',
        3 => 'Sede Suzano (reunião)',
        4 => 'Sede Suzano (confraternização)',
        5 => 'Evento Fora',
        6 => 'Evento Obrigatório',
    ];

    $tipo_badge = [
        1 => 'm-badge--metal',
        2 => 'm-badge--brand',
        3 => 'm-badge--info',
        4 => 'm-badge--success',
        5 => 'm-badge--warning',
        6 => 'm-badge--danger',
    ];

    // Dados do evento
    $stmt = $conn->prepare("SELECT * FROM eventos WHERE id = ?");
    $stmt->bind_param("i", $evento_id);
    $stmt->execute();
    $evento = $stmt->get_result()->fetch_assoc();

    if (!$evento) {
        header("Location: ../frequencia/index.php");
        exit;
    }

    // Frequências com dados do integrante
    $stmt2 = $conn->prepare("
        SELECT f.id AS freq_id, f.presente, f.patente_no_evento,
               ci.id AS integrante_id, ci.apelido, ci.nome, ci.status
        FROM frequencias f
        INNER JOIN cadastro_integrante ci ON ci.id = f.integrante_id
        WHERE f.evento_id = ?
        ORDER BY ci.patente ASC
    ");
    $stmt2->bind_param("i", $evento_id);
    $stmt2->execute();
    $chamada = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);

    $total     = count($chamada);
    $presentes = count(array_filter($chamada, fn($r) => $r['presente'] == 1));
    $ausentes  = $total - $presentes;
    $pc        = $total > 0 ? round(($presentes / $total) * 100) : 0;

    function patente_label($p) {
        $map = [1=>'I',2=>'II',3=>'III',4=>'IV',5=>'V',6=>'VI',7=>'VII',8=>'VIII',9=>'IX',10=>'X'];
        return $map[$p] ?? '—';
    }
?>
<!DOCTYPE html>
<html lang="pt-br">
    <base href="../">
    <head>
        <meta charset="utf-8" />
        <title>Abutre's MC | Chamada</title>
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
        <style>
            .btn-chamada {
                min-width: 110px;
                transition: all .15s ease;
            }
            .btn-presente  { background-color: #34bfa3; border-color: #34bfa3; color: #fff; }
            .btn-ausente   { background-color: #f4516c; border-color: #f4516c; color: #fff; }
            .btn-indefinido{ background-color: #ebedf2; border-color: #ebedf2; color: #6f727d; }
            .row-presente  { background-color: rgba(52,191,163,.06); }
            .row-ausente   { background-color: rgba(244,81,108,.04); }
            #contador-presentes { transition: all .2s; }
        </style>
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
                                        Chamada — <?= htmlspecialchars($evento['nome']) ?>
                                    </h3>
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
                                            <span class="m-nav__link-text">Chamada</span>
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

                            <!-- Info do evento -->
                            <div class="m-portlet m-portlet--mobile">
                                <div class="m-portlet__body">
                                    <div class="row align-items-center">
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-center">
                                                <div class="m--margin-right-20">
                                                    <i class="la la-calendar-check-o m--font-brand" style="font-size:3rem;"></i>
                                                </div>
                                                <div>
                                                    <h4 class="m--font-boldest m--margin-bottom-5">
                                                        <?= htmlspecialchars($evento['nome']) ?>
                                                    </h4>
                                                    <span class="m-badge <?= $tipo_badge[$evento['tipo']] ?> m-badge--wide m--margin-right-10">
                                                        <?= $tipos_evento[$evento['tipo']] ?>
                                                    </span>
                                                    <span class="m--font-metal">
                                                        <?= date('d/m/Y', strtotime($evento['data_evento'])) ?>
                                                    </span>
                                                    <?php if ($evento['observacao']): ?>
                                                        <br><small class="m--font-metal"><?= htmlspecialchars($evento['observacao']) ?></small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row text-center">
                                                <div class="col-4">
                                                    <div class="m--font-boldest m--font-success" style="font-size:2rem;" id="contador-presentes"><?= $presentes ?></div>
                                                    <div class="m--font-metal" style="font-size:12px;">Presentes</div>
                                                </div>
                                                <div class="col-4">
                                                    <div class="m--font-boldest m--font-danger" style="font-size:2rem;" id="contador-ausentes"><?= $ausentes ?></div>
                                                    <div class="m--font-metal" style="font-size:12px;">Ausentes</div>
                                                </div>
                                                <div class="col-4">
                                                    <div class="m--font-boldest m--font-brand" style="font-size:2rem;" id="contador-pc"><?= $pc ?>%</div>
                                                    <div class="m--font-metal" style="font-size:12px;">Presença</div>
                                                </div>
                                            </div>
                                            <div class="progress m-progress--sm m--margin-top-10">
                                                <div class="progress-bar m--bg-success" id="barra-pc" style="width:<?= $pc ?>%; transition: width .3s;"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- CHAMADA -->
                            <div class="m-portlet m-portlet--mobile">
                                <div class="m-portlet__head">
                                    <div class="m-portlet__head-caption">
                                        <div class="m-portlet__head-title">
                                            <h3 class="m-portlet__head-text">
                                                Lista de Chamada
                                                <small class="m--font-metal m--margin-left-10" style="font-size:13px;">
                                                    Clique no botão para alternar presença/ausência
                                                </small>
                                            </h3>
                                        </div>
                                    </div>
                                    <div class="m-portlet__head-tools">
                                        <!-- Marcar todos presente / ausente -->
                                        <button onclick="marcarTodos(1)" class="btn btn-sm btn-success m-btn m-btn--pill m--margin-right-5">
                                            <i class="la la-check"></i> Todos presentes
                                        </button>
                                        <button onclick="marcarTodos(0)" class="btn btn-sm btn-danger m-btn m-btn--pill">
                                            <i class="la la-times"></i> Todos ausentes
                                        </button>
                                    </div>
                                </div>
                                <div class="m-portlet__body">

                                    <?php if (empty($chamada)): ?>
                                        <div class="m--align-center" style="padding:40px 0;">
                                            <i class="la la-users m--font-metal" style="font-size:4rem;"></i>
                                            <h3 class="m--font-metal m--margin-top-10">Nenhum integrante convocado.</h3>
                                        </div>
                                    <?php else: ?>

                                    <div class="table-responsive">
                                        <table class="table table-hover m-table" id="tabela-chamada">
                                            <thead class="m-table__head">
                                                <tr>
                                                    <th style="width:40px;">#</th>
                                                    <th>Apelido</th>
                                                    <th>Nome</th>
                                                    <th class="m--align-center">Patente</th>
                                                    <th class="m--align-center">Presença</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($chamada as $i => $r): ?>
                                                <tr id="row-<?= $r['freq_id'] ?>"
                                                    class="<?= $r['presente'] ? 'row-presente' : 'row-ausente' ?>">
                                                    <td class="m--font-metal"><?= $i + 1 ?></td>
                                                    <td class="m--font-boldest"><?= htmlspecialchars($r['apelido']) ?></td>
                                                    <td class="m--font-metal"><?= htmlspecialchars($r['nome']) ?></td>
                                                    <td class="m--align-center">
                                                        <span class="m-badge m-badge--info m-badge--wide">
                                                            Pat. <?= patente_label($r['patente_no_evento']) ?>
                                                        </span>
                                                    </td>
                                                    <td class="m--align-center">
                                                        <button
                                                            class="btn btn-chamada <?= $r['presente'] ? 'btn-presente' : 'btn-ausente' ?>"
                                                            id="btn-<?= $r['freq_id'] ?>"
                                                            data-freq="<?= $r['freq_id'] ?>"
                                                            data-presente="<?= (int)$r['presente'] ?>"
                                                            onclick="togglePresenca(this)">
                                                            <?= $r['presente'] ? '<i class="la la-check"></i> Presente' : '<i class="la la-times"></i> Ausente' ?>
                                                        </button>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <?php endif; ?>
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
        const totalConvocados = <?= $total ?>;

        function atualizarContadores() {
            const presentes = document.querySelectorAll('.btn-presente').length;
            const ausentes  = totalConvocados - presentes;
            const pc        = totalConvocados > 0 ? Math.round((presentes / totalConvocados) * 100) : 0;

            document.getElementById('contador-presentes').textContent = presentes;
            document.getElementById('contador-ausentes').textContent  = ausentes;
            document.getElementById('contador-pc').textContent        = pc + '%';
            document.getElementById('barra-pc').style.width           = pc + '%';
        }

        function togglePresenca(btn) {
            const freqId   = btn.dataset.freq;
            const presente = btn.dataset.presente === '1' ? 0 : 1;
            const row      = document.getElementById('row-' + freqId);

            // Feedback imediato
            btn.dataset.presente = presente;
            if (presente) {
                btn.className    = 'btn btn-chamada btn-presente';
                btn.innerHTML    = '<i class="la la-check"></i> Presente';
                row.className    = 'row-presente';
            } else {
                btn.className    = 'btn btn-chamada btn-ausente';
                btn.innerHTML    = '<i class="la la-times"></i> Ausente';
                row.className    = 'row-ausente';
            }
            atualizarContadores();

            // Salvar via AJAX
            fetch('frequencia/presenca_toggle.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'freq_id=' + freqId + '&presente=' + presente
            }).catch(() => {
                // Reverter em caso de erro de rede
                btn.dataset.presente = presente ? 0 : 1;
                togglePresenca(btn);
            });
        }

        function marcarTodos(presente) {
            document.querySelectorAll('[data-freq]').forEach(btn => {
                if (parseInt(btn.dataset.presente) !== presente) {
                    btn.dataset.presente = presente ? 0 : 1; // forçar toggle
                    togglePresenca(btn);
                }
            });
        }
        </script>
    </body>
</html>
