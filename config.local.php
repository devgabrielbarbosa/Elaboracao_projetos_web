<?php
// config.local.php — coloque na raiz do projeto (delivery_lanches) e NÃO comente nada
return [
    'db' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'name' => 'delivery_lanches',
        'user' => 'root',
        'pass' => ''   // se você tem senha, coloque aqui
    ],
    'store' => [
        'lat' => -7.1907,
        'lon' => -48.2078
    ],
    'whatsapp' => '5599999999999'
];
