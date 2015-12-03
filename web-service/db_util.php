<?php 

function validateUser($username, $password) {
	$response = 'N';
	
	try {
		$sql = 'begin :response := fnd_web_sec.VALIDATE_LOGIN(:username, :password); end;';
		
		$conn = getDbConnection();
		$stid = oci_parse($conn, $sql);

		oci_bind_by_name($stid, ":username", $username);				
		oci_bind_by_name($stid, ":password", $password);				
		oci_bind_by_name($stid, ":response", $response, -1, SQLT_CHR);
				
		oci_execute($stid);
		//print "output response = ". $response . "\n";			

	} catch (Exception $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}';
	} finally {
		oci_free_statement($stid);
		oci_close($conn);		
	}
	
	return $response;
}

function functionMapping($id) {
	
    $sql = 'SELECT SOURCE, OUTPUT FROM SCI_INT_LIST_TBL WHERE SHORT = :id';
	try {
		$results = array();
		$conn = getDbConnection();
		$stid = oci_parse($conn, $sql);
		oci_bind_by_name($stid, ":id", $id);			
		oci_execute($stid);
						
		while (false!==($row=oci_fetch_array($stid, OCI_BOTH)) ) {
			$results[] = $row;
		}				
				
	} catch (Exception $e) {
		//echo '{"error functionMapping ":{"text":'. $e->getMessage() .'}}';
	} finally {
		oci_free_statement($stid);
		oci_close($conn);		
	}
	
	return $results;
}

function callProcedure($username, $password, $applicationId, $refNo, $functionId, $dataArray) {
	
	$results = array();
	
	try {
		
		$sql = 'begin ';
		
		$validationStatus = validateUser($username, $password);
		
		if ($validationStatus == 'Y') {

			$mappingTable = functionMapping($functionId);
						
			if (count($mappingTable) > 0) {
				$functionMap = $mappingTable[0];
				$sourceName = $functionMap['SOURCE'];				
				$outputType = $functionMap['OUTPUT'];
				
				$isCursor;
				if ($outputType == 'cur') {
					$isCursor = true;
				} else {
					$isCursor = false;
				}
				
				$sql .= $sourceName . "(";

				for($i = 0; $i < count($dataArray); $i++) {
					$sql .= ':param'.$i.', ';
				}
				
				$sql .= ':out_cursor); end;';
				
				// tunggu Pak Teddy update semua store procedure dengan 4 parameter seperti ini
				//$sql .= ':username, :applicationId, :refNo, :out_cursor); end;';
				
				//echo "call Store Procedure = {$sql} \n";
							
				$conn = getDbConnection();
				$stid = oci_parse($conn, $sql);
						
				if ($isCursor) {
					// execute call to database with return type cursor
					$curs = oci_new_cursor($conn);
								
					//oci_bind_by_name($stid, ":number", $num); // untuk input maxlength dan type bisa dikosongkan, default adalah -1 dan SQLT_CHR
					//oci_bind_by_name($stid, ":number", $num, -1, OCI_B_INT); // eksplisit menyebut type adalah INT
					//oci_bind_by_name($stid, ":number", $num, -1, SQLT_CHR); // default type adalah VARCHAR / CHAR ,masih bisa, php akan melakukan konversi otomatis

					for($i = 0; $i < count($dataArray); $i++) {
						//echo "oci binding parameter :{$xmlValue[$index]['value']} with value = {$xmlValue[$index + 1]['value']} \n";				
						oci_bind_by_name($stid, ":param".$i, htmlspecialchars($dataArray[$i]), 10000);
					}
					
					// tunggu Pak Teddy update semua store procedure
					//oci_bind_by_name($stid, ":username", $username, 10000);
					//oci_bind_by_name($stid, ":applicationId", $applicationId, 10000);
					//oci_bind_by_name($stid, ":refNo", $refNo, 10000);

					// untuk out harus di define type OCI_B_CURSOR
					oci_bind_by_name($stid, ":out_cursor", $curs, 32000, OCI_B_CURSOR); 
					
					oci_execute($stid);
					oci_execute($curs);
					
					$dbRowData = array();
					while (($row = oci_fetch_array($curs, OCI_ASSOC+OCI_RETURN_NULLS)) != false) {
						$dbRowData[] = $row;
					}				
					
					//echo json_encode($results);
					
					$results['log'] = 'success';
					$results['message'] = 'success';
					
					// json format response 
					//$results['data'] = json_encode($results);	
					
					// xml format response
					$xmlOutput = new SimpleXMLElement('<rowDataList/>');
					for ($i = 0; $i < count($dbRowData); $i++) {
						$dbRow = $dbRowData[$i];
						if (is_array($dbRow)) {
							array_to_xml($xmlOutput->addChild('rowData'), $dbRow);				
						} else {
							$xmlOutput->addChild('rowData', htmlspecialchars($dbRow));
						}
						
					}
					$results['output'] = $xmlOutput->asXML();
					
					oci_free_statement($stid);
					oci_free_statement($curs);	
					oci_close($conn);		
				} else {
					// execute call to database with return type varchar or number 
					for($i = 0; $i < count($dataArray); $i++) {
						oci_bind_by_name($stid, ":param".$i, htmlspecialchars($dataArray[$i]), 10000);
					}

					// tunggu Pak Teddy update semua store procedure
					//oci_bind_by_name($stid, ":username", $username, 10000);
					//oci_bind_by_name($stid, ":applicationId", $applicationId, 10000);
					//oci_bind_by_name($stid, ":refNo", $refNo, 10000);
					
					oci_bind_by_name($stid, ":out_cursor", $varOutput, 10000);
					oci_execute($stid);

					$results['log'] = 'success';
					$results['message'] = 'success';
					$results['output'] = htmlspecialchars($varOutput);	
					
					oci_free_statement($stid);
					oci_close($conn);		
				}
				
			} else {
				// functionId not found on database
				$results['log'] = 'error';
				$results['message'] = 'functionId '.$functionId.' not found';				
			}

		} else {
			// user validation failed, return 'N' from database
			$results['log'] = 'error';
			$results['message'] = 'invalid username or password';							
		}
		
	} catch (Exception $e) {
		//error_log($e->getMessage(), 3, '/var/tmp/phperror.log'); //Write error log
		//echo '{"error":{"text":'. $e->getMessage() .'}}';
		$results['log'] = 'error';
		$results['message'] = $e->getMessage();		
	} 
	
	return $results;
	
}

function insert_log($username, $applicationId, $refNo, $functionId, $wsType, $status, $message) {
	$conn = getDbConnection();
	$stid = oci_parse($conn, 'insert into SCI_LOG_SERVICE(USER_NAME, ID_APP, NO_REFF, FUNCTION, STATUS, WS_TYPE, RESULT_MESSAGE) 
	values (:username, :applicationId, :refNo, :functionId, :status, :wsType, :message)');
	
	oci_bind_by_name($stid, ':username', $username);
	oci_bind_by_name($stid, ':applicationId', $applicationId);
	oci_bind_by_name($stid, ':refNo', $refNo);
	oci_bind_by_name($stid, ':functionId', $functionId);
	oci_bind_by_name($stid, ':wsType', $wsType);
	oci_bind_by_name($stid, ':status', $status);
	oci_bind_by_name($stid, ':message', $message);
	
	$r = oci_execute($stid);  // executes and commits
	if ($r) {
		error_log("DataInsertSuksesLog =  \n", 3, '/opt/lampp/logs/php-ws.log');	
	}

	oci_free_statement($stid);
	oci_close($conn);
} 