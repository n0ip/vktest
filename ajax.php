<?php

require 'vendor/autoload.php';

$func = filter_input(INPUT_GET, 'function', FILTER_SANITIZE_STRING);

switch( $func ) {

	case 'isAllowed':
		$pid = filter_input(INPUT_GET, 'pid', FILTER_SANITIZE_STRING);
		$uid = filter_input(INPUT_GET, 'uid', FILTER_SANITIZE_STRING);

		if( !isset( $pid ) || !isset( $uid ) ) {
			print json_encode( ['status' => 'error' ] );
			exit();
		}

		print json_encode( ['status' => 'ok' ] );
	break;

	case 'getProjects':

		$dbh = new PDO('mysql:host=localhost;dbname=vktest', 'root', 'password');
		$sth = $dbh->prepare("SELECT id, name FROM projects");
		$sth->execute();

		$response = [];

		$results = $sth->fetchAll( PDO::FETCH_ASSOC );
		foreach( $results as $result ) {
			$response[ $result['id'] ] = $result['name'];
		}

		print json_encode( ['status' => 'ok', 
							'rsp' => $response ] );
	break;

	case 'getActions':

		$pid = filter_input(INPUT_GET, 'pid', FILTER_SANITIZE_STRING);
		if( !isset( $pid ) ) {
			print json_encode( ['status' => 'error' ] );
			exit();
		}

		$dbh = new PDO('mysql:host=localhost;dbname=vktest', 'root', 'password');
		$sth = $dbh->prepare("SELECT actions FROM projects where id = ?");
		$sth->execute( [ $pid ] );
		$result = $sth->fetchAll();
		$response = json_decode( $result[0]['actions'], true );

		print json_encode( ['status' => 'ok',
							'rsp' => $response ] );
		
	break;

	case 'isCompleted':
		$pid = filter_input(INPUT_GET, 'pid', FILTER_SANITIZE_STRING);
		$uid = filter_input(INPUT_GET, 'uid', FILTER_SANITIZE_STRING);

		if( !isset( $pid ) || !isset( $uid ) ) {
			print json_encode( ['status' => 'error' ] );
			exit();
		}

		$completed = false;

		$client = new Predis\Client();

		$result = $client->hgetall( 'complete_' . $pid );

		if( isset( $result[$uid] ) && $result[$uid] == 1 ) {
			$completed = true;
		}

		print json_encode( [
			'status' => 'ok',
			'completed' => $completed
		] );
	break;

	case 'trackAction':
		$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_STRING);
		$pid    = filter_input(INPUT_GET, 'pid', FILTER_SANITIZE_STRING);
		$uid    = filter_input(INPUT_GET, 'uid', FILTER_SANITIZE_STRING);
		
		$client = new Predis\Client();
		$date = date("Y-m-d"); # TODO Принимать сюда дату

		if( !isset( $action ) ) {
			print json_encode( ['status' => 'error' ] );
			exit();
		}

		$result = $client->hget( 'target', $pid );
		if( $result == $action ) {
			$client->hset( 'complete_' . $pid, $uid, 1 );
			$client->hset( 'complete_' . $pid . $date, $uid, 1 );
		}

		# Атомарный запрос в redis
		$client->hincrby( 'click_' . $pid . ':' . $date, $action, 1 );
		$client->hincrby( 'project_clicks_' . $pid . ':', $action, 1 );

		if( $client->hset( 'unique_click_' . $pid . ':' . $date, $uid.":".$action, 1 ) ) {
			$client->hincrby( 'total_unique_clicks_' . $pid . ':' . $date, $action, 1 );
			$client->hincrby( 'project_total_unique_clicks_' . $pid . ':', $action, 1 );
		}
		
		print json_encode( [
			'status' => 'ok',
		] );
	break;

	case 'admin':
		$client = new Predis\Client();
		
		$pid = 1;

		$stats = $client->hgetall( 'cl_' . $pid . ':' . date("Y-m-d") );
		print_r( $stats );
		
		$stats = $client->hgetall( 'ucl_' . $pid . ':' . date("Y-m-d") );
		$unique_stats = [];
		
		foreach( $stats as $key => $stat ) {
			$elem = [];
			if( !empty( $key ) ) {
				list( $elem['uid'], $elem['action'] ) = explode(":", $key);
			
				if( !isset($unique_stats[ $elem['action'] ]) ) $unique_stats[ $elem['action'] ] = 0;
				$unique_stats[ $elem['action'] ] += 1; // += $stats for not unique
			}
		}
		
		print_r( $unique_stats );
		
	break;

	default:
		$client = new Predis\Client();

		$pid = 1;
		#$client->hset( 'target', 5, 'POPUP_DISPLAYED' );
		# $client->hset( 'target', $pid, 'BIG_RED_BUTTON_CLICKED' );
		# $result = $client->hget( 'target', $pid );

		$result = $client->hgetall( 'cl_' . $pid . ':' . date("Y-m-d") );
		print_r( $result );

	break;
	
}
