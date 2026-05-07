<?php

function patente($p) {
    $map = [
        1=>'I',2=>'II',3=>'III',4=>'IV',5=>'V',
        6=>'VI',7=>'VII',8=>'VIII',9=>'IX',10=>'X'
    ];
    return $map[$p] ?? '-';
}

function status($s) {
    $map = [
        1 => ['Ativo','status-active'],
        2 => ['Afastado','status-active'],
        3 => ['Desligado','status-inactive'],
        4 => ['Suspenso','status-active'],
    ];
    return $map[$s] ?? ['Desconhecido',''];
}