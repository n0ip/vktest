<?php

require 'vendor/autoload.php';

session_start();
$sid = session_id();

update_session_uid();

function update_session_uid( $update = false ) {
	if( !isset($_SESSION['uid']) || $update ) {
		$uid = uniqid();
		$_SESSION['uid'] = $uid;
		return true;
	}
	return false;
}
