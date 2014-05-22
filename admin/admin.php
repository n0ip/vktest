<?php

require '../vendor/autoload.php';

$client = new Predis\Client();
$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_STRING);

$dbh = new PDO('mysql:host=localhost;dbname=vktest', 'root', 'password');

if( !empty( $_POST ) ) {
	
	if( !empty( $_POST['project_name']) ) {
		$sth = $dbh->prepare("INSERT INTO projects (name, actions) VALUES (?, ?)");
		$sth->execute([ $_POST['project_name'], json_encode( array_values( $_POST['actions'] ) ) ]);

		foreach( $_POST['actions'] as $action ) {
			if( isset( $action['target']) && $action['target'] == "true" ) {
				$client->hset( 'target', $dbh->lastInsertId(), $action['name'] );
			}
		}

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
	 
	 $scope.target_checked = false;
	 
	 $scope.check_target = function( index ) {
		 if(!$scope.target_checked ) {
			 	 $scope.target_checked = true;
		 } else {
			 	 $scope.target_checked = false;
		 }
		 $scope.checked_index = index;
	 };
	 
	 $scope.check_disabled = function( index ) {
		 if( $scope.target_checked === true && $scope.checked_index !== index ) {
			 return true;
		 }
	 };

});
</script>

</head>

<body ng-controller="MainCtrl">
<noscript><meta http-equiv="refresh" content="0; URL=http://vk.com/badbrowser.php"></noscript>

<h4><a href="../../">Главная</a></h4>

<div class="center">
<h1>Проекты</h1>
<?php switch( $action ) : ?>
<?php default: ?>

<h3>Добавить проект</h3>

<div class="bg-gray add-form">
<form method="POST" action="admin.php" name="add_project" class="form-horizontal">
	<div>Название: <input type="text" name="project_name" ng-model="project_name" required /></div>

	<button ng-click="add($event)" class="btn btn-success">Добавить элемент</button>
	<button ng-click="del($event)" class="btn btn-danger">Убрать элемент</button>
	
	<div ng-repeat="i in range(1, elements)" class="el-group" >
	
	<div>Имя кнопки: <input type="text" name="actions[{{i}}][name]" required></div>
	<div>Тип кнопки:
		<select name="actions[{{i}}][type]">
		<option value="action">action</option>
		<option value="redirect">redirect</option>
	</select>
	</div>
	<div>Целевая:
		<input type="checkbox" name="actions[{{i}}][target]" ng-click="check_target(i)" ng-disabled="check_disabled(i)" value="true"></input>
	</div>
	<div>Ссылка:
		<input type="text" name="actions[{{i}}][value]" value="http://"></input>
	</div>
	</div>
	<div><input type="submit" class="btn btn-default"/></div>
</form>
</div>

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
