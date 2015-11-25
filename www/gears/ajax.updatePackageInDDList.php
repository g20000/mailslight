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

	// фильтруем входящие данные
	$idItem = filter_input(INPUT_POST, 'idItem', FILTER_VALIDATE_INT);
	$parentId = filter_input(INPUT_POST, 'parentMenuId', FILTER_VALIDATE_INT);
	$nameItem = trim(filter_input(INPUT_POST, 'nameItem', FILTER_UNSAFE_RAW));
	$percent = filter_input(INPUT_POST, 'percentVal', FILTER_VALIDATE_INT);
	
	//exit(json_encode(array('type'=>'ok','text'=>$parentId)));
	
	if($nameItem == ""){
		exit(json_encode(array('type'=>'error','text'=>'Заполните поля!')));
	}
	if(($idItem == false) || ($idItem == null) || ($parentId == false) || ($parentId == null)){
		exit(json_encode(array('type'=>'error','text'=>'Ошибка! Невозможно обновить запись!')));
	}
	if(($percent == false) || ($percent == null)){
		exit(json_encode(array('type'=>'error','text'=>'Ошибка! Поле проценты должно содержать числа!')));
	}

	$q = 'UPDATE `pkg_ddlist` SET `name`= "'.$nameItem.'", `percent`="'.$percent.'" WHERE id='.$idItem;
	$request = $db->query($q);
	
	$outputList = showPackagesInDDList($parentId);

	exit(json_encode(array('type'=>'ok','text'=>$outputList)));
?>