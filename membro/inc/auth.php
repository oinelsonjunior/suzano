<?php
// Guard de autenticação da área membro
// Inclua no topo de toda página em membro/ logo após session_start()

if (!isset($_SESSION['membro_login'])) {
    header("Location: ../");
    exit;
}