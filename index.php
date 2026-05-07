<?php

require_once 'includes/connection.php';
session_start();

// Já logado — redireciona conforme role
if (isset($_SESSION['admin_login'])) {
    header("Location: admin/");
    exit;
}
if (isset($_SESSION['membro_login'])) {
    header("Location: membro/");
    exit;
}

$errorMsg = [];

if (isset($_POST['btn_login'])) {
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($email === '') {
        $errorMsg[] = 'Informe o e-mail ou usuário.';
    } elseif ($password === '') {
        $errorMsg[] = 'Informe a senha.';
    } else {
        $safe  = $conn->real_escape_string($email);
        $sql   = "SELECT id, email, password, role, enabled FROM users
                  WHERE email = '$safe' OR username = '$safe' LIMIT 1";
        $res   = $conn->query($sql);
        $user  = $res ? $res->fetch_assoc() : null;

        if (!$user) {
            $errorMsg[] = 'Usuário não encontrado.';
        } elseif ($user['enabled'] != 1) {
            $errorMsg[] = 'Usuário desativado. Fale com o administrador.';
        } elseif (!password_verify($password, $user['password'])) {
            $errorMsg[] = 'Senha incorreta.';
        } else {
            switch ($user['role']) {
                case 'admin':
                    $_SESSION['admin_login'] = $user['email'];
                    header("Location: admin/");
                    exit;
                case 'membro':
                    $_SESSION['membro_login'] = $user['email'];
                    header("Location: membro/");
                    exit;
                default:
                    $errorMsg[] = 'Perfil de acesso inválido.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8"/>
    <title>Abutre's MC | Entrar</title>
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
    <style>
        input.form-control {
            color: #fff !important;
            background-color: #1a1a2e !important;
            border: 1px solid #3a3a5c;
        }
        input.form-control::placeholder { color: #9699a2 !important; }
    </style>
</head>
<body class="m--skin- m-header--fixed m-header--fixed-mobile m-aside-left--enabled m-aside-left--skin-dark m-aside-left--offcanvas m-footer--push m-aside--offcanvas-default">
<div class="m-grid m-grid--hor m-grid--root m-page">
    <div class="m-grid__item m-grid__item--fluid m-grid m-grid--hor m-login m-login--singin m-login--2 m-login-2--skin-3"
         id="m_login" style="background-color:#ffffff;">
        <div class="m-grid__item m-grid__item--fluid m-login__wrapper">
            <div class="m-login__container">

                <div class="m-login__logo" style="text-align:center; margin-bottom:20px;">
                    <a href="#">
                        <img src="images/logo.png" style="max-width:130px;">
                    </a>
                </div>

                <div class="m-login__signin">

                    <?php foreach ($errorMsg as $err): ?>
                        <div class="alert alert-danger">
                            <strong><?= htmlspecialchars($err) ?></strong>
                        </div>
                    <?php endforeach; ?>

                    <div class="m-login__head">
                        <h3 class="m-login__title">Abutre's MC</h3>
                        <div class="m-login__desc" style="color:#6c6e86;">
                            Sistema de Gestão
                        </div>
                    </div>

                    <form class="m-login__form m-form" method="POST" action="">
                        <div class="form-group m-form__group">
                            <input class="form-control m-input" type="text"
                                   placeholder="E-mail ou usuário" name="email"
                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                   autocomplete="off">
                        </div>
                        <div class="form-group m-form__group">
                            <input class="form-control m-input m-login__form-input--last"
                                   type="password" placeholder="Senha" name="password">
                        </div>
                        <div class="m-login__form-action">
                            <button type="submit" name="btn_login"
                                    class="btn btn-focus m-btn m-btn--pill m-btn--custom m-btn--air m-login__btn">
                                Entrar
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>
<script src="js/vendors.bundle.js"></script>
<script src="js/scripts.bundle.js"></script>
</body>
</html>