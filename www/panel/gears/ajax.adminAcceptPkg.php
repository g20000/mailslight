<?php
include_once('../gears/config.php');
include_once($cfg['realpath'].'/gears/headers.php');
include_once($cfg['realpath'].'/gears/bootstrap.php');
include_once($cfg['realpath'].'/gears/functions.php');


include_once($cfg['realpath'].'/gears/l18n.php');
include_once($cfg['realpath'].'/gears/db.php');

$_ts = microtime_float();


// смотрим язык пользователя
$cfg['ln'] = getClientLang();

// создаем инстанс подключения к базе
$db = new dbClass($cfg['db']);

// забираем из базы опции и кладем их в конфиг
$options = $db->query("SELECT * FROM options");

// кладем в конфиг все что забрали из базы (все опции)
if (isset($options[0])) { 
	foreach($options as $k=>$v) {
		$cfg['options'][$v->option]=$v->value;
	}
}
unset($options);

// смотрим на авторизацию
include($cfg['realpath'].'/gears/auth_init.php');

if ($user['rankname']!='admin') exit();

$pkg_id = addslashes(strip_tags(filter_input(INPUT_POST, 'pkg_id', FILTER_VALIDATE_INT)));


$q = "SELECT * FROM `pkg_admin_approve` WHERE `pkg_id` = ".$pkg_id;
$check = $db->query($q);
if (!isset($check) || $check==false) {
	exit(json_encode(array('type'=>'error','text'=>'Не найден товар или покупатель!')));
}

$q = "DELETE FROM `pkg_admin_approve` WHERE `pkg_id` = ".$pkg_id;
$db->query($q);
exit(json_encode(array('type'=>'ok','text'=>'Принято!')));


?>