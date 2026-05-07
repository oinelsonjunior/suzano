<?php
// Inclua este arquivo no topo de TODA página da área admin
// Logo após session_start() e antes de qualquer output

if (!isset($_SESSION['admin_login'])) {
    header("Location: ../");
    exit;
}