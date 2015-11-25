<?php
include_once('../gears/config.php');
include_once($cfg['realpath'].'/gears/headers.php');
include_once($cfg['realpath'].'/gears/bootstrap.php');
include_once($cfg['realpath'].'/gears/functions.php');


include_once($cfg['realpath'].'/gears/l18n.php');
include_once($cfg['realpath'].'/gears/db.php');

//header('Content-type: application/json');

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

if ($user['rankname']!='support' && $user['rankname']!='admin' && $user['rankname']!='shipper') {
	exit('Запрещено!');
}

$statuses = $_POST['statusesAndIds'];
$newStatus = array();
$newStatuses = array();
$newEuro = array();

foreach($statuses as $status){
	if(($status[0] != "") && ($status[1] != ""))
	{
		if(isNewStatus(intval($status[0]), $status[1])){
			$q = "INSERT INTO `pkg_statuses` VALUES (NULL, ".$status[0].", '".date("Y-m-d H:i:s", time())."', '".$status[1]."');";
			$db->query($q);
			array_push($newStatus, intval($status[0]));
			array_push($newStatus, $status[1]);
			array_push($newStatuses, $newStatus);
			array_pop($newStatus);
			array_pop($newStatus);
		}
	}
}

foreach($statuses as $status){
	if(($status[0] != "") && ($status[2] != "")){
		$id = intval($status[0]);
		$euro = intval($status[2]);
		if(isNewEuroValue($id, $euro)){
			$q = "UPDATE `pkg_description` SET `euro` = '".$euro."' WHERE `pkg_id` = ".$id;
			$db->query($q);
			array_push($newEuro, intval($status[0]));
			array_push($newEuro, intval($status[2]));
		}
	}
}

	exit(json_encode(array('type'=>'ok','text'=>'Сохранено','info'=>$newStatuses,'newEuro'=>$newEuro)));
?>