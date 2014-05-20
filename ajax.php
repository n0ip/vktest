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
		print json_encode( ['status' => 'ok', 
							'rsp' => [
								1 => 'aaa',
								2 => 'bbb',
								4 => 'ccc'
							] ] );
	break;

	case 'getActions':

		$pid = filter_input(INPUT_GET, 'pid', FILTER_SANITIZE_STRING);
		if( !isset( $pid ) ) {
			print json_encode( ['status' => 'error' ] );
			exit();
		}

		print json_encode( [
			'rsp' => [
				[
					'type' => 'action',
					'name' => 'POPUP_DISPLAYED'
				],
				[
					'type' => 'action',
					'name' => 'BIG_RED_BUTTON_CLICKED',
					'target' => true
				],
				[
					'type'  => 'redirect',
					'name'  => 'MAIN_SITE_LINK',
					'value' => 'http://mysite.com/example'
				],
			]
		] );
	break;

	case 'isCompleted':
		$pid = filter_input(INPUT_GET, 'pid', FILTER_SANITIZE_STRING);
		$uid = filter_input(INPUT_GET, 'uid', FILTER_SANITIZE_STRING);

		if( !isset( $pid ) || !isset( $uid ) ) {
			print json_encode( ['status' => 'error' ] );
			exit();
		}

		print json_encode( [
			'status' => 'ok',
			'completed' => true
		] );
	break;

	case 'trackAction':
		$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_STRING);
		$pid    = filter_input(INPUT_GET, 'pid', FILTER_SANITIZE_STRING);
		$uid    = filter_input(INPUT_GET, 'uid', FILTER_SANITIZE_STRING);
		
		$client = new Predis\Client();

		if( !isset( $action ) ) {
			print json_encode( ['status' => 'error' ] );
			exit();
		}

		# Атомарный запрос в redis
		$client->hincrby( 'cl_' . $pid . ':' . date("Y-m-d"), $action, 1 );
		$client->hincrby( 'ucl_' . $pid . ':' . date("Y-m-d"), $uid.":".$action, 1 );
		
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
			list( $elem['uid'], $elem['action'] ) = explode(":", $key);
			if( !isset($unique_stats[ $elem['action'] ]) ) $unique_stats[ $elem['action'] ] = 0;
			$unique_stats[ $elem['action'] ] += 1; // += $stats for not unique
		}
		
		print_r( $unique_stats );
		
	break;
	
	default:
	break;
	
}
