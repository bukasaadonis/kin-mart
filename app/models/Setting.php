<?php
namespace App\Models;
use App\Core\Model;
final class Setting extends Model {
    public function allSettings(): array { $rows=$this->all("SELECT `key`,`value`,`group` FROM settings ORDER BY `group`,`key`"); $out=[]; foreach($rows as $r)$out[$r['key']]=$r['value']; return $out; }
    public function get(string $key,mixed $default=null): mixed { $r=$this->one("SELECT `value` FROM settings WHERE `key`=?",[$key]); return $r['value']??$default; }
    public function set(string $key,string $value,string $group='general'): void { $this->exec("INSERT INTO settings(`key`,`value`,`group`) VALUES(?,?,?) ON DUPLICATE KEY UPDATE `value`=VALUES(`value`),`group`=VALUES(`group`)",[$key,$value,$group]); }
}
