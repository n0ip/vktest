<?php

require('common.php');

$func = filter_input(INPUT_GET, 'function', FILTER_SANITIZE_STRING);

switch( $func ) {

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
}