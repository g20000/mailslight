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
	$itemName = trim(filter_input(INPUT_POST, 'itemName', FILTER_UNSAFE_RAW));
	$newPercent = filter_input(INPUT_POST, 'newPercent', FILTER_VALIDATE_INT);
	$parentId = trim(filter_input(INPUT_POST, 'parentMenuId', FILTER_VALIDATE_INT));
	if($itemName == ""){
		exit(json_encode(array('type'=>'error','text'=>'Заполните поля!')));
	}
	
	if(($newPercent == NULL) || ($newPercent == false)){
		exit(json_encode(array('type'=>'error','text'=>'В поле ввода проценты присутствуют пробелы!')));
	}
	
	if(($parentId == NULL) || ($parentId == false)){
		exit(json_encode(array('type'=>'error','text'=>'Ошибка добавления!')));
	}

	$q = "INSERT INTO `pkg_ddlist` (name, percent, pkg_cat_ddlist_fk) VALUES('".$itemName."','".$newPercent."', '".$parentId."')";
	$dbRequest = $db->query($q);
	
	$outputList = showPackagesInDDList($parentId);
	
	exit(json_encode(array('type'=>'ok','text'=>$outputList)));
?>