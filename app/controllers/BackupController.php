<?php
namespace App\Controllers;
use App\Core\Controller;
use App\Core\Database;
final class BackupController extends Controller {
 public function download():never{$db=Database::connection();$name=(string)config('db.name','kinmart');$tables=$db->query("SHOW TABLES")->fetchAll(\PDO::FETCH_COLUMN);$sql="-- Sauvegarde KIN-MART ".date('c')."\nSET FOREIGN_KEY_CHECKS=0;\n";foreach($tables as $t){$cr=$db->query("SHOW CREATE TABLE `$t`")->fetch();$sql.="DROP TABLE IF EXISTS `$t`;\n".$cr['Create Table'].";\n";$rows=$db->query("SELECT * FROM `$t`")->fetchAll();foreach($rows as $row){$cols=array_map(fn($c)=>"`$c`",array_keys($row));$vals=array_map(fn($v)=>$v===null?'NULL':$db->quote((string)$v),array_values($row));$sql.="INSERT INTO `$t`(".implode(',',$cols).") VALUES(".implode(',',$vals).");\n";}$sql.="\n";}$sql.="SET FOREIGN_KEY_CHECKS=1;\n";header('Content-Type: application/sql');header('Content-Disposition: attachment; filename="kinmart_backup_'.date('Ymd_His').'.sql"');echo$sql;exit;}
}
