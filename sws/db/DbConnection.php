<?php
function getDbConnection() {
	$conn = oci_connect('belajar', 'belajar', 'localhost/orcl');
	if (!$conn) {
		$e = oci_error();
		trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
	}
	return $conn;
}