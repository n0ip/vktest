<?php

require_once 'common.php';

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

	case 'newSession':
		session_regenerate_id();
		update_session_uid( 1 );

		print json_encode( [
			'sid' => session_id(),
			'uid' => $_SESSION['uid']
		] );
	break;

	case 'getSession':
		print json_encode( [
			'sid' => $sid,
			'uid' => $_SESSION['uid']
		] );
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
		$client = new Predis\Client();

		if( !isset( $action ) ) {
			print json_encode( ['status' => 'error' ] );
			exit();
		}

		print json_encode( [
			'status' => 'ok',
		] );
	break;

	default:
		print json_encode( ['status' => 'error' ] );
	break;
	
}

