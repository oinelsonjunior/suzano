<?php
    session_start();
    require_once ('../inc/general.php');

    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $editando = $id > 0;
    $integrante = [];

    if ($editando) {
        $stmt = $conn->prepare("SELECT * FROM cadastro_integrante WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $integrante = $stmt->get_result()->fetch_assoc();
        if (!$integrante) {
            header("Location: ../integrantes_faccao");
            exit;
        }
    }

    // Lista de padrinhos disponíveis
    $padrinhos = $conn->query("
        SELECT id, apelido FROM cadastro_integrante
        WHERE faccao = 1 
        ORDER BY apelido ASC
    ")->fetch_all(MYSQLI_ASSOC);

    $patentes = [
        1=>'I', 2=>'II', 3=>'III', 4=>'IV', 5=>'V',
        6=>'VI', 7=>'VII', 8=>'VIII', 9=>'IX', 10=>'X'
    ];

    $status_opts = [
        1=>'Ativo', 2=>'Afastado', 3=>'Desligado', 4=>'Suspenso'
    ];

    // Helper para preencher campos no modo edição
    $val = fn($campo, $default = '') => htmlspecialchars($integrante[$campo] ?? $default);
    $sel = fn($campo, $valor) => ($integrante[$campo] ?? '') == $valor ? 'selected' : '';
?>
<!DOCTYPE html>
<html lang="pt-br">
    <base href="../">
    <head>
        <meta charset="utf-8" />
        <title>Abutre's MC | <?= $editando ? 'Editar' : 'Novo' ?> Integrante</title>
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
                                    <h3 class="m-subheader__title" style="text-transform:uppercase;">
                                        <?= $editando ? 'Editar Integrante' : 'Novo Integrante' ?>
                                    </h3>
                                    <ul class="m-subheader__breadcrumbs m-nav m-nav--inline">
                                        <li class="m-nav__item m-nav__item--home">
                                            <a href="index" class="m-nav__link m-nav__link--icon">
                                                <i class="m-nav__link-icon la la-home"></i>
                                            </a>
                                        </li>
                                        <li class="m-nav__separator">—</li>
                                        <li class="m-nav__item">
                                            <a href="integrantes_faccao" class="m-nav__link">
                                                <span class="m-nav__link-text">Integrantes</span>
                                            </a>
                                        </li>
                                        <li class="m-nav__separator">—</li>
                                        <li class="m-nav__item">
                                            <span class="m-nav__link-text"><?= $editando ? 'Editar' : 'Novo' ?></span>
                                        </li>
                                    </ul>
                                </div>
                                <div>
                                    <?php if ($editando): ?>
                                    <a href="integrante_view?id=<?= $id ?>"
                                       class="btn btn-sm btn-info m-btn m-btn--icon m-btn--pill">
                                        <span><i class="la la-eye"></i><span>Ver Perfil</span></span>
                                    </a>
                                    &nbsp;
                                    <?php endif; ?>
                                    <a href="integrantes_faccao"
                                       class="btn btn-sm btn-secondary m-btn m-btn--icon m-btn--pill">
                                        <span><i class="la la-arrow-left"></i><span>Voltar</span></span>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="m-content">
                            <form method="POST" action="integrantes/integrante_save.php" enctype="multipart/form-data">
                                <input type="hidden" name="id" value="<?= $id ?>">

                                <!-- SEÇÃO 1: Identificação -->
                                <div class="m-portlet m-portlet--mobile">
                                    <div class="m-portlet__head">
                                        <div class="m-portlet__head-caption">
                                            <div class="m-portlet__head-title">
                                                <h3 class="m-portlet__head-text">
                                                    <i class="la la-user m--font-brand"></i>
                                                    Identificação
                                                </h3>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="m-portlet__body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group m-form__group">
                                                    <label class="form-control-label">Nome Completo <span class="m--font-danger">*</span></label>
                                                    <input type="text" name="nome" class="form-control m-input"
                                                           value="<?= $val('nome') ?>" required>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group m-form__group">
                                                    <label class="form-control-label">Apelido <span class="m--font-danger">*</span></label>
                                                    <input type="text" name="apelido" class="form-control m-input"
                                                           value="<?= $val('apelido') ?>" required>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group m-form__group">
                                                    <label class="form-control-label">Patente <span class="m--font-danger">*</span></label>
                                                    <select name="patente" class="form-control m-input" required>
                                                        <?php foreach ($patentes as $pv => $pl): ?>
                                                            <option value="<?= $pv ?>" <?= $sel('patente', $pv) ?>><?= $pl ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group m-form__group">
                                                    <label class="form-control-label">Status <span class="m--font-danger">*</span></label>
                                                    <select name="status" class="form-control m-input" required>
                                                        <?php foreach ($status_opts as $sv => $sl): ?>
                                                            <option value="<?= $sv ?>" <?= $sel('status', $sv) ?: ($sv==1 && !$editando ? 'selected' : '') ?>><?= $sl ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="form-group m-form__group">
                                                    <label class="form-control-label">Data de Nascimento</label>
                                                    <input type="date" name="nascimento" class="form-control m-input"
                                                           value="<?= $integrante['nascimento'] ?? '' ? date('Y-m-d', strtotime($integrante['nascimento'])) : '' ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group m-form__group">
                                                    <label class="form-control-label">Data de Apresentação</label>
                                                    <input type="date" name="data_apresentacao" class="form-control m-input"
                                                           value="<?= $integrante['data_apresentacao'] ?? '' ? date('Y-m-d', strtotime($integrante['data_apresentacao'])) : date('Y-m-d') ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group m-form__group">
                                                    <label class="form-control-label">Padrinho</label>
                                                    <select name="padrinho" class="form-control m-input">
                                                        <option value="0">— Sem padrinho —</option>
                                                        <?php foreach ($padrinhos as $pad): ?>
                                                            <option value="<?= $pad['id'] ?>"
                                                                <?= ($integrante['padrinho'] ?? 0) == $pad['id'] ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($pad['apelido']) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group m-form__group">
                                                    <label class="form-control-label">Veículo</label>
                                                    <select name="veiculo" class="form-control m-input">
                                                        <option value="Sim" <?= $sel('veiculo','Sim') ?>>Sim</option>
                                                        <option value="Não" <?= $sel('veiculo','Não') ?>>Não</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group m-form__group">
                                                    <label class="form-control-label">Email</label>
                                                    <input type="email" name="email" class="form-control m-input"
                                                           value="<?= $val('email') ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group m-form__group">
                                                    <label class="form-control-label">CNH</label>
                                                    <input type="text" name="cnh" class="form-control m-input"
                                                           value="<?= $val('cnh') ?>" placeholder="Número da CNH">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- SEÇÃO 2: Foto -->
                                <div class="m-portlet m-portlet--mobile">
                                    <div class="m-portlet__head">
                                        <div class="m-portlet__head-caption">
                                            <div class="m-portlet__head-title">
                                                <h3 class="m-portlet__head-text">
                                                    <i class="la la-camera m--font-success"></i>
                                                    Foto do Integrante
                                                </h3>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="m-portlet__body">
                                        <div class="row align-items-center">
                                            <!-- Preview -->
                                            <div class="col-md-2 m--align-center">
                                                <?php
                                                $foto_atual = $integrante['foto'] ?? null;
                                                $foto_src   = ($foto_atual && file_exists('../' . $foto_atual))
                                                    ? '../' . $foto_atual
                                                    : null;
                                                ?>
                                                <div id="foto-preview"
                                                     style="width:90px;height:90px;border-radius:50%;
                                                            background:#716aca;display:flex;align-items:center;
                                                            justify-content:center;margin:0 auto;overflow:hidden;
                                                            border:3px solid #ebedf2;">
                                                    <?php if ($foto_src): ?>
                                                        <img src="<?= htmlspecialchars($foto_src) ?>"
                                                             style="width:100%;height:100%;object-fit:cover;"
                                                             id="foto-img">
                                                    <?php else: ?>
                                                        <i class="flaticon-user" id="foto-icon"
                                                           style="font-size:2.5rem;color:#fff;"></i>
                                                        <img src="" id="foto-img"
                                                             style="width:100%;height:100%;object-fit:cover;display:none;">
                                                    <?php endif; ?>
                                                </div>
                                                <?php if ($foto_atual): ?>
                                                <div class="m--margin-top-10">
                                                    <label style="font-size:11px;" class="m--font-metal">
                                                        <input type="checkbox" name="remover_foto" value="1">
                                                        Remover foto
                                                    </label>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                            <!-- Upload -->
                                            <div class="col-md-6">
                                                <div class="form-group m-form__group">
                                                    <label class="form-control-label">
                                                        Selecionar foto
                                                        <small class="m--font-metal">(opcional — JPG, PNG ou WEBP, máx. 2MB)</small>
                                                    </label>
                                                    <input type="file" name="foto" id="foto-input"
                                                           class="form-control m-input"
                                                           accept="image/jpeg,image/png,image/webp"
                                                           onchange="previewFoto(this)">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- SEÇÃO 3: Contato -->
                                <div class="m-portlet m-portlet--mobile">
                                    <div class="m-portlet__head">
                                        <div class="m-portlet__head-caption">
                                            <div class="m-portlet__head-title">
                                                <h3 class="m-portlet__head-text">
                                                    <i class="la la-phone m--font-info"></i>
                                                    Contato
                                                </h3>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="m-portlet__body">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="form-group m-form__group">
                                                    <label class="form-control-label">Celular</label>
                                                    <input type="text" name="celular" class="form-control m-input"
                                                           value="<?= $val('celular') ?>" placeholder="(11) 99999-9999">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group m-form__group">
                                                    <label class="form-control-label">Telefone</label>
                                                    <input type="text" name="telefone" class="form-control m-input"
                                                           value="<?= $val('telefone') ?>" placeholder="(11) 3333-3333">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group m-form__group">
                                                    <label class="form-control-label">Comercial</label>
                                                    <input type="text" name="comercial" class="form-control m-input"
                                                           value="<?= $val('comercial') ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group m-form__group">
                                                    <label class="form-control-label">Recados</label>
                                                    <input type="text" name="recados" class="form-control m-input"
                                                           value="<?= $val('recados') ?>">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- SEÇÃO 4: Endereço -->
                                <div class="m-portlet m-portlet--mobile">
                                    <div class="m-portlet__head">
                                        <div class="m-portlet__head-caption">
                                            <div class="m-portlet__head-title">
                                                <h3 class="m-portlet__head-text">
                                                    <i class="la la-map-marker m--font-warning"></i>
                                                    Endereço
                                                </h3>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="m-portlet__body">
                                        <div class="row">
                                            <div class="col-md-2">
                                                <div class="form-group m-form__group">
                                                    <label class="form-control-label">CEP</label>
                                                    <input type="text" name="cep" id="cep" class="form-control m-input"
                                                           value="<?= $val('cep') ?>" placeholder="00000-000"
                                                           onblur="buscarCep(this.value)">
                                                </div>
                                            </div>
                                            <div class="col-md-5">
                                                <div class="form-group m-form__group">
                                                    <label class="form-control-label">Endereço</label>
                                                    <input type="text" name="endereco" id="endereco" class="form-control m-input"
                                                           value="<?= $val('endereco') ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-1">
                                                <div class="form-group m-form__group">
                                                    <label class="form-control-label">Nº</label>
                                                    <input type="text" name="num_endereco" class="form-control m-input"
                                                           value="<?= $val('num_endereco') ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group m-form__group">
                                                    <label class="form-control-label">Complemento</label>
                                                    <input type="text" name="complemento" class="form-control m-input"
                                                           value="<?= $val('complemento') ?>">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group m-form__group">
                                                    <label class="form-control-label">Bairro</label>
                                                    <input type="text" name="bairro" id="bairro" class="form-control m-input"
                                                           value="<?= $val('bairro') ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group m-form__group">
                                                    <label class="form-control-label">Cidade</label>
                                                    <input type="text" name="cidade" id="cidade" class="form-control m-input"
                                                           value="<?= $val('cidade') ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group m-form__group">
                                                    <label class="form-control-label">Estado</label>
                                                    <input type="text" name="estado" id="estado" class="form-control m-input"
                                                           value="<?= $val('estado') ?>" maxlength="2" placeholder="SP">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Ações -->
                                <div class="m-portlet m-portlet--mobile">
                                    <div class="m-portlet__body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <a href="integrantes_faccao" class="btn btn-secondary m-btn m-btn--pill">
                                                Cancelar
                                            </a>
                                            <button type="submit" class="btn btn-brand m-btn m-btn--icon m-btn--pill">
                                                <span>
                                                    <i class="la la-save"></i>
                                                    <span><?= $editando ? 'Salvar Alterações' : 'Cadastrar Integrante' ?></span>
                                                </span>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                            </form>
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
        // Busca automática de CEP via ViaCEP
        // Preview de foto ao vivo
        function previewFoto(input) {
            var icon = document.getElementById('foto-icon');
            var img  = document.getElementById('foto-img');
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    img.src = e.target.result;
                    img.style.display = 'block';
                    if (icon) icon.style.display = 'none';
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        function buscarCep(cep) {
            cep = cep.replace(/\D/g, '');
            if (cep.length !== 8) return;
            fetch('https://viacep.com.br/ws/' + cep + '/json/')
                .then(r => r.json())
                .then(data => {
                    if (data.erro) return;
                    document.getElementById('endereco').value = data.logradouro || '';
                    document.getElementById('bairro').value   = data.bairro    || '';
                    document.getElementById('cidade').value   = data.localidade || '';
                    document.getElementById('estado').value   = data.uf        || '';
                });
        }
        </script>
    </body>
</html>