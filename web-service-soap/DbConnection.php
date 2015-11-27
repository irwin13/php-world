<?php

function getDbConnection() {
	//$conn = oci_connect('belajar', 'belajar', 'localhost/orcl');
	$conn = oci_connect('APPS', 'APPS', 'oradev.sucofindo.co.id:1527/dev');
	if (!$conn) {
		$e = oci_error();
		trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
	}
	return $conn;
}