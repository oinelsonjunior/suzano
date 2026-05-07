<?php
session_start();
require_once('../inc/general.php');

// Verificar se é superadmin (ID 1)
$stmt_me = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
$stmt_me->bind_param("s", $_SESSION['admin_login']);
$stmt_me->execute();
$me = $stmt_me->get_result()->fetch_assoc();
$eh_superadmin = ((int)$me['id'] === 1);

$usuarios = $conn->query("
    SELECT u.id, u.username, u.email, u.role, u.enabled, u.id_cadastro,
           ci.apelido, ci.patente, ci.status AS status_integrante
    FROM users u
    LEFT JOIN cadastro_integrante ci ON ci.id = u.id_cadastro
    ORDER BY u.role ASC, u.id ASC
")->fetch_all(MYSQLI_ASSOC);

$patentes       = [1=>'I',2=>'II',3=>'III',4=>'IV',5=>'V',6=>'VI',7=>'VII',8=>'VIII',9=>'IX',10=>'X'];
$total_admins   = count(array_filter($usuarios, fn($u) => $u['role'] === 'admin'));
$total_membros  = count(array_filter($usuarios, fn($u) => $u['role'] === 'membro'));
$total_ativos   = count(array_filter($usuarios, fn($u) => $u['enabled'] == 1));
$total_inativos = count(array_filter($usuarios, fn($u) => $u['enabled'] == 0));
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8"/>
    <base href="../"><title>Abutre's MC | Usuários</title>
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
                            <h3 class="m-subheader__title" style="text-transform:uppercase;">Usuários do Sistema</h3>
                            <ul class="m-subheader__breadcrumbs m-nav m-nav--inline">
                                <li class="m-nav__item m-nav__item--home">
                                    <a href="index" class="m-nav__link m-nav__link--icon">
                                        <i class="m-nav__link-icon la la-home"></i>
                                    </a>
                                </li>
                                <li class="m-nav__separator">—</li>
                                <li class="m-nav__item"><span class="m-nav__link-text">Usuários</span></li>
                            </ul>
                        </div>
                        <div>
                            <a href="usuarios/usuario_form.php" class="btn btn-sm btn-brand m-btn m-btn--icon m-btn--pill">
                                <span><i class="la la-plus"></i><span>Novo Usuário</span></span>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="m-content">

                    <?php if (isset($_GET['ok'])): ?>
                    <div class="alert alert-success m-alert--air">
                        <i class="la la-check-circle"></i>&nbsp;
                        <?= ['criado'=>'Usuário criado.','atualizado'=>'Usuário atualizado.','desativado'=>'Usuário desativado.','ativado'=>'Usuário reativado.'][$_GET['ok']] ?? 'OK.' ?>
                    </div>
                    <?php endif; ?>

                    <!-- KPIs -->
                    <div class="m-portlet m-portlet--mobile">
                        <div class="m-portlet__body m-portlet__body--no-padding">
                            <div class="row m-row--no-padding m-row--col-separator-xl">
                                <?php
                                $kpis = [
                                    ['Admins',     $total_admins,  'm--font-brand',   'm--bg-brand',   'Acesso total'],
                                    ['Membros',     $total_membros, 'm--font-info',    'm--bg-info',    'Portal restrito'],
                                    ['Ativos',      $total_ativos,  'm--font-success', 'm--bg-success', 'Podem fazer login'],
                                    ['Desativados', $total_inativos,'m--font-danger',  'm--bg-danger',  'Sem acesso'],
                                ];
                                foreach ($kpis as [$lbl,$val,$fc,$bg,$desc]): ?>
                                <div class="col-md-6 col-lg-3 col-xl-3">
                                    <div class="m-widget24"><div class="m-widget24__item">
                                        <h4 class="m-widget24__title"><?= $lbl ?></h4><br>
                                        <span class="m-widget24__desc"><?= $desc ?></span>
                                        <span class="m-widget24__stats <?= $fc ?>"><?= $val ?></span>
                                        <div class="m--space-10"></div>
                                        <div class="progress m-progress--sm">
                                            <div class="progress-bar <?= $bg ?>" style="width:100%"></div>
                                        </div>
                                        <span class="m-widget24__change">Total</span>
                                        <span class="m-widget24__number"><?= count($usuarios) ?></span>
                                    </div></div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Tabela -->
                    <div class="m-portlet m-portlet--mobile">
                        <div class="m-portlet__head">
                            <div class="m-portlet__head-caption">
                                <div class="m-portlet__head-title">
                                    <h3 class="m-portlet__head-text">
                                        Lista de Usuários
                                        <?php if (!$eh_superadmin): ?>
                                        <small class="m--font-metal" style="font-size:11px;">
                                            — Edição de admins disponível apenas para o Super Admin
                                        </small>
                                        <?php endif; ?>
                                    </h3>
                                </div>
                            </div>
                        </div>
                        <div class="m-portlet__body">
                            <div class="table-responsive">
                                <table class="table table-hover m-table m-table--head-bg-brand">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Usuário</th>
                                            <th>E-mail</th>
                                            <th class="m--align-center">Perfil</th>
                                            <th class="m--align-center">Integrante vinculado</th>
                                            <th class="m--align-center">Status</th>
                                            <th class="m--align-center">Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($usuarios as $u):
                                            $alvo_e_admin      = ($u['role'] === 'admin');
                                            $alvo_e_superadmin = ((int)$u['id'] === 1);
                                            // Admin normal só edita membros; superadmin edita tudo
                                            $pode_editar = $eh_superadmin || !$alvo_e_admin;
                                            // Ninguém desativa a si mesmo nem o superadmin (a menos que seja ele)
                                            $pode_toggle = $pode_editar
                                                && ((int)$u['id'] !== (int)$me['id'])
                                                && (!$alvo_e_superadmin || $eh_superadmin);
                                        ?>
                                        <tr>
                                            <td class="m--font-metal"><?= $u['id'] ?></td>
                                            <td>
                                                <span class="m--font-boldest"><?= htmlspecialchars($u['username']) ?></span>
                                                <?php if ($alvo_e_superadmin): ?>
                                                    <span class="m-badge m-badge--danger m-badge--wide" style="font-size:10px; margin-left:4px;">Super Admin</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="m--font-metal"><?= htmlspecialchars($u['email']) ?></td>
                                            <td class="m--align-center">
                                                <span class="m-badge <?= $u['role'] === 'admin' ? 'm-badge--brand' : 'm-badge--info' ?> m-badge--wide">
                                                    <?= ucfirst($u['role']) ?>
                                                </span>
                                            </td>
                                            <td class="m--align-center">
                                                <?php if ($u['apelido']): ?>
                                                    <a href="integrante_view?id=<?= $u['id_cadastro'] ?>" style="text-decoration:none;">
                                                        <span class="m--font-boldest"><?= htmlspecialchars($u['apelido']) ?></span>
                                                        <span class="m-badge m-badge--info m-badge--wide" style="font-size:10px; margin-left:3px;">
                                                            Pat. <?= $patentes[$u['patente']] ?? '—' ?>
                                                        </span>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="m--font-metal">—</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="m--align-center">
                                                <span class="m-badge <?= $u['enabled'] ? 'm-badge--success' : 'm-badge--danger' ?> m-badge--wide">
                                                    <?= $u['enabled'] ? 'Ativo' : 'Desativado' ?>
                                                </span>
                                            </td>
                                            <td class="m--align-center" style="white-space:nowrap;">
                                                <?php if ($pode_editar): ?>
                                                    <a href="usuarios/usuario_form.php?id=<?= $u['id'] ?>"
                                                       class="btn btn-sm btn-warning m-btn m-btn--icon m-btn--pill" title="Editar">
                                                        <span><i class="la la-pencil"></i></span>
                                                    </a>
                                                <?php endif; ?>
                                                <?php if ($pode_toggle): ?>
                                                    <a href="usuarios/usuario_toggle.php?id=<?= $u['id'] ?>&acao=<?= $u['enabled'] ? 'desativar' : 'ativar' ?>"
                                                       class="btn btn-sm <?= $u['enabled'] ? 'btn-danger' : 'btn-success' ?> m-btn m-btn--icon m-btn--pill"
                                                       title="<?= $u['enabled'] ? 'Desativar' : 'Ativar' ?>"
                                                       onclick="return confirm('<?= $u['enabled'] ? 'Desativar' : 'Reativar' ?> <?= htmlspecialchars(addslashes($u['username'])) ?>?')">
                                                        <span><i class="la la-<?= $u['enabled'] ? 'ban' : 'check' ?>"></i></span>
                                                    </a>
                                                <?php endif; ?>
                                                <?php if (!$pode_editar && !$pode_toggle): ?>
                                                    <span class="m--font-metal" style="font-size:11px;">Protegido</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
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