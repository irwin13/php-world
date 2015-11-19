<?php

require_once 'db/DbConnection.php';

$app->get('/mhs', function() use ($app) {
    $sql = 'SELECT ID, NAMA, ALAMAT, USIA FROM MHS';
	try {
		$results = array();
		$conn = getDbConnection();
		$stid = oci_parse($conn, $sql);
		oci_execute($stid);
						
		while (false!==($row=oci_fetch_array($stid, OCI_BOTH)) ) {
			$results[] = $row;
		}				
		
		$app->render(
			'view/mhsList.tpl.html',
			array(
				'list' => $results
			)
		);
		
		oci_free_statement($stid);
		oci_close($conn);
	} catch (Exception $e) {
		//error_log($e->getMessage(), 3, '/var/tmp/phperror.log'); //Write error log
		echo '{"error":{"text":'. $e->getMessage() .'}}';
	}
});

$app->get('/mhs/sp', function() use ($app) {
    $sql = 'begin PROC_WITH_CURSOR(:number, :cursbv); end;';
	try {
		$results = array();
		$num = 29;
		$conn = getDbConnection();
		$curs = oci_new_cursor($conn);
		$stid = oci_parse($conn, $sql);
		//oci_bind_by_name($stid, ":number", $num); // untuk input maxlength dan type bisa dikosongkan, default adalah -1 dan SQLT_CHR
		oci_bind_by_name($stid, ":number", $num, -1, OCI_B_INT); // eksplisit menyebut type adalah INT
		//oci_bind_by_name($stid, ":number", $num, -1, SQLT_CHR); // default type adalah VARCHAR /CHAR ,masih bisa, php akan melakukan konversi otomatis
		oci_bind_by_name($stid, ":cursbv", $curs, -1, OCI_B_CURSOR); // untuk out harus di define type OCI_B_CURSOR
		oci_execute($stid);
		oci_execute($curs);
						
		while (($row = oci_fetch_array($curs, OCI_ASSOC+OCI_RETURN_NULLS)) != false) {
			$results[] = $row;
		}				
	
		$app->render(
			'view/mhsListSp.tpl.html',
			array(
				'list' => $results
			)
		);
		
		oci_free_statement($stid);
		oci_free_statement($curs);
		oci_close($conn);
	} catch (Exception $e) {
		//error_log($e->getMessage(), 3, '/var/tmp/phperror.log'); //Write error log
		echo '{"error":{"text":'. $e->getMessage() .'}}';
	}
});

/*
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

$app->get('/student-edit/:id', function($id) use ($app) {
    $sql = "SELECT id, first_name, last_name, nim FROM student WHERE id = ?";
	try {
		$db = getDbConnection();
		$stmt = $db->prepare($sql); 
		$stmt->execute([$id]);
		$result = $stmt->fetch(PDO::FETCH_ASSOC);
		$db = null;
				
		$app->render(
			'view/studentEdit.tpl.html',
			array(
				'model' => $result
			)
		);
	} catch (PDOException $e) {
		//error_log($e->getMessage(), 3, '/var/tmp/phperror.log'); //Write error log
		echo '{"error":{"text":'. $e->getMessage() .'}}';
	}
});

$app->post('/student-edit', function() use ($app) {
    $sql = "UPDATE student SET first_name = ?, last_name = ?, nim = ? WHERE id = ?";
	try {
		$db = getDbConnection();
		$first_name = $app->request()->post('first_name');
		$last_name = $app->request()->post('last_name');
		$nim = $app->request()->post('nim');
		$id = $app->request()->post('id');
		
		$stmt = $db->prepare($sql); 
		$stmt->execute([$first_name, $last_name, $nim, $id]);
		$db = null;
				
		$app->redirect('students');
	} catch (PDOException $e) {
		//error_log($e->getMessage(), 3, '/var/tmp/phperror.log'); //Write error log
		echo '{"error":{"text":'. $e->getMessage() .'}}';
	}
});


$app->get('/student-delete/:id', function($id) use ($app) {
    $sql = "DELETE FROM student WHERE id = ?";
	try {
		$db = getDbConnection();
		$stmt = $db->prepare($sql); 
		$stmt->execute([$id]);
		$db = null;
				
		$app->redirect('/sis/students');
		
	} catch (PDOException $e) {
		//error_log($e->getMessage(), 3, '/var/tmp/phperror.log'); //Write error log
		echo '{"error":{"text":'. $e->getMessage() .'}}';
	}
});
*/