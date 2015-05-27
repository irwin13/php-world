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
	} catch (PDOException $e) {
		//error_log($e->getMessage(), 3, '/var/tmp/phperror.log'); //Write error log
		echo '{"error":{"text":'. $e->getMessage() .'}}';
	}
});

$app->get('/student-create', function() use ($app) {
	$app->render('view/studentInsert.tpl.html');
});

$app->post('/student-create', function() use ($app) {
    $sql = "INSERT INTO student (first_name, last_name, nim) VALUES (?, ?, ?)";
	try {
		$db = getDbConnection();
		$first_name = $app->request()->post('first_name');
		$last_name = $app->request()->post('last_name');
		$nim = $app->request()->post('nim');
		
		$stmt = $db->prepare($sql); 
		$generated_id = $stmt->execute([$first_name, $last_name, $nim]);
		$db = null;
				
		$app->redirect('students');
	} catch (PDOException $e) {
		//error_log($e->getMessage(), 3, '/var/tmp/phperror.log'); //Write error log
		echo '{"error":{"text":'. $e->getMessage() .'}}';
	}
});