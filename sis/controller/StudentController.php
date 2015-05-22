<?php

require_once 'db/DbConnection.php';

$app->get('/students', function() use ($app) {
    $sql = "SELECT id, first_name, last_name, nim FROM student";
	try {
		$db = getDbConnection();
		$stmt = $db->query($sql); 
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$db = null;
				
		$app->render(
			'view/studentList.tpl.html',
			array(
				'list' => $result
			)
		);
	} catch(PDOException $e) {
		//error_log($e->getMessage(), 3, '/var/tmp/phperror.log'); //Write error log
		echo '{"error":{"text":'. $e->getMessage() .'}}';
	}
});