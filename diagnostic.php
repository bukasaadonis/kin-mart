<?php
require __DIR__.'/app/helpers/functions.php';
$GLOBALS['config']=require __DIR__.'/app/config/config.php';
$pdoDrivers=class_exists('PDO')?PDO::getAvailableDrivers():[];
$checks=[
 'PHP >= 8.0'=>version_compare(PHP_VERSION,'8.0.0','>='),
 'Extension PDO'=>class_exists('PDO'),
 'Pilote pdo_mysql'=>in_array('mysql',$pdoDrivers,true),
 'Uploads produits écrivable'=>is_writable(__DIR__.'/public/uploads/products'),
 'Uploads documents écrivable'=>is_writable(__DIR__.'/public/uploads/documents'),
 'Schéma SQL présent'=>is_file(__DIR__.'/database/kinmart.sql'),
 'Logo KIN-MART présent'=>is_file(__DIR__.'/public/assets/images/logo-kinmart.jpg'),
];
?><!doctype html><html lang="fr"><meta charset="utf-8"><title>Diagnostic KIN-MART</title><style>body{font-family:Arial;max-width:850px;margin:40px auto}.ok{color:green}.bad{color:#b00}table{border-collapse:collapse;width:100%}td{padding:10px;border-bottom:1px solid #ddd}</style><h1>Diagnostic KIN-MART</h1><p>Ce fichier peut être supprimé après l’installation.</p><table><?php foreach($checks as $k=>$v):?><tr><td><?=htmlspecialchars($k)?></td><td class="<?=$v?'ok':'bad'?>"><?=$v?'OK':'À CORRIGER'?></td></tr><?php endforeach;?></table><p>Base configurée : <?=htmlspecialchars(config('db.name'))?> — hôte <?=htmlspecialchars(config('db.host'))?>:<?=htmlspecialchars(config('db.port'))?></p><p><a href="<?=htmlspecialchars(base_url('login'))?>">Ouvrir la connexion KIN-MART</a></p></html>
