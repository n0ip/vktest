<?php

require '../vendor/autoload.php';

$client = new Predis\Client();
$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_STRING);

$dbh = new PDO('mysql:host=localhost;dbname=vktest', 'root', 'password');

$dbh->exec(
"CREATE TABLE IF NOT EXISTS projects ( id INT(10) NOT NULL AUTO_INCREMENT,
name VARCHAR(40),
actions TEXT,
PRIMARY KEY (id)
);");

$success = '';

if( !empty( $_POST ) ) {
	echo "<pre>";
	var_dump($_POST);
	echo "</pre>";

	echo json_encode($_POST['actions']);
	
	if( !empty( $_POST['project_name']) ) {
		$sth = $dbh->prepare("INSERT INTO projects (name, actions) VALUES (?, ?)");
		$sth->execute([ $_POST['project_name'], json_encode($_POST['actions'], true) ]);
		header('Location:admin.php');
	};

}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" ng-app="VKAdminApp">
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<link rel="shortcut icon" href="http://vk.com/images/faviconnew.ico?3" />

<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<meta name="description" content="" />

<title>VKStats</title>

<!-- AngularJS libs -->
<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.2.16/angular.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.2.16/angular-resource.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.2.16/angular-route.js"></script>

<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>

<!-- Styles -->
<link rel="stylesheet" type="text/css" href="../styles/main.css" />

<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">

<!-- Optional theme -->
<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap-theme.min.css">

<!-- Latest compiled and minified JavaScript -->
<script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>

<script>
var VKAdminApp = angular.module( 'VKAdminApp', [] );
VKAdminApp.controller( 'MainCtrl', function( $scope ) {

	// $scope.actions = [];

	$scope.elements = 1;
	$scope.add = function( $event ) {
		$event.preventDefault();
		$scope.elements += 1;
	};
	$scope.del = function( $event ) {
		$event.preventDefault();
		if($scope.elements > 1 ) $scope.elements -= 1;
	};

	$scope.range = function(min, max, step){
		step = (step === undefined) ? 1 : step;
		var input = [];
		for (var i = min; i <= max; i += step) input.push(i);
		return input;
	 };
});
</script>

</head>

<body ng-controller="MainCtrl">
<noscript><meta http-equiv="refresh" content="0; URL=http://vk.com/badbrowser.php"></noscript>

<div class='alert-success'><?=$success?></div>
<div class="center">
<h1>Проекты</h1>
<?php switch( $action ) : ?>
<?php default: ?>
<h3>Добавить проект</h3>
<form method="POST" action="admin.php" name="add_project">
	<div>Название: <input type="text" name="project_name" ng-model="project_name" required /></div>

	<button ng-click="add($event)">Добавить элементов</button>
	<button ng-click="del($event)">Убрать элемент</button>
	
	<div ng-repeat="i in range(1, elements)" >
	
	<div>Имя кнопки: <input type="text" name="actions[{{i}}][name]" required></div>
	<div>Тип кнопки:
		<select name="actions[{{i}}][type]">
		<option value="action">action</option>
		<option value="redirect">redirect</option>
	</select>
	</div>
	<div>Целевая:
		<input type="checkbox" name="actions[{{i}}][target]" value="true"></input>
	</div>
	<div>Ссылка:
		<input type="text" name="actions[{{i}}][value]" value="http://"></input>
	</div>
	</div>
	<div><input type="submit"/></div>
</form>

<table class="table admin">
<?php
$sth = $dbh->prepare("SELECT id,name FROM projects");
$sth->execute();
$projects = $sth->fetchAll();
foreach( $projects as $project ) {
	echo "<tr><td><a href=\"stat.php?pid={$project['id']}\">".$project['name']."</a></td></tr>";
}
?>
<?php break ?>
<?php endswitch; ?>
</div>

</body>
</html>
