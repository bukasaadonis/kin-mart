<?php
$root=dirname(__DIR__);
$required=['index.php','.htaccess','database/kinmart.sql','app/core/Router.php','app/core/Database.php','app/controllers/PosController.php','app/models/Sale.php','public/assets/images/logo-kinmart.jpg','INSTALLATION.txt'];
$missing=[];foreach($required as $f)if(!is_file($root.'/'.$f))$missing[]=$f;
echo "KIN-MART structure check\n";
echo "PHP: ".PHP_VERSION."\n";
echo "PDO drivers: ".implode(',',PDO::getAvailableDrivers())."\n";
echo $missing?"MISSING:\n- ".implode("\n- ",$missing)."\n":"OK: fichiers essentiels présents.\n";
exit($missing?1:0);
