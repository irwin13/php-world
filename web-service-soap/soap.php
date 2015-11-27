<?php

require 'DbConnection.php';

set_error_handler("warning_handler", E_WARNING);

function warning_handler($errno, $errstr, $errfile, $errline) { 
	throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}

function hello($name) {
	return 'Hello '.$name;
}

function array_to_xml(SimpleXMLElement $xml, array $arr)
{
    foreach ($arr as $key => $value)
    {   
		
		if (is_array($value))
		{   
			$new_object = $xml->addChild($key);
			array_to_xml($new_object, $value);
		}   
		else
		{   				
			$xml->addChild($key, $value);
		}   
	
    }  
}

function validateUser($username, $password)
{
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
	
    $sql = 'SELECT SOURCE, OUTPUT FROM SCI_INT_LIST_TBL WHERE ID = :id';
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

function callSP($username, $password, $functionId, $data) {
	
	$results = array();
	
	try {
		
		$sql = 'begin ';
		$dataArray = explode('|', $data);
		
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
						oci_bind_by_name($stid, ":param".$i, $dataArray[$i], 10000);
					}
							
					oci_bind_by_name($stid, ":out_cursor", $curs, 32000, OCI_B_CURSOR); // untuk out harus di define type OCI_B_CURSOR
					
					oci_execute($stid);
					oci_execute($curs);
					
					$dbRowData = array();
					while (($row = oci_fetch_array($curs, OCI_ASSOC+OCI_RETURN_NULLS)) != false) {
						$dbRowData[] = $row;
					}				
					
					//echo json_encode($results);
					
					$results['status'] = 'success';
					$results['message'] = 'success';
					
					// json format response 
					//$results['data'] = json_encode($results);	
					
					// xml format response
					$xmlOutput = new SimpleXMLElement('<outputMessage/>');
					array_to_xml($xmlOutput->addChild('rowData'), $dbRowData);			
					$results['data'] = $xmlOutput->asXML();
					
					oci_free_statement($stid);
					oci_free_statement($curs);	
					oci_close($conn);		
				} else {
					// execute call to database with return type varchar or number 
					for($i = 0; $i < count($dataArray); $i++) {
						oci_bind_by_name($stid, ":param".$i, $dataArray[$i], 10000);
					}
							
					oci_bind_by_name($stid, ":out_cursor", $varOutput, 10000);
					oci_execute($stid);


					$results['status'] = 'success';
					$results['message'] = 'success';
					$results['data'] = $varOutput;	
					
					oci_free_statement($stid);
					oci_close($conn);		
				}
				
			} else {
				// functionId not found on database
				$results['status'] = 'error';
				$results['message'] = 'functionId '.$functionId.' not found';				
			}

		} else {
			// user validation failed, return 'N' from database
			$results['status'] = 'error';
			$results['message'] = 'invalid username or password';							
		}
		
	} catch (Exception $e) {
		//error_log($e->getMessage(), 3, '/var/tmp/phperror.log'); //Write error log
		//echo '{"error":{"text":'. $e->getMessage() .'}}';
		$results['status'] = 'error';
		$results['message'] = $e->getMessage();									
	} 
	
	return $results;
	
}

$soapConfig = array('encoding' => 'UTF-8');

//$soapServer = new SoapServer("HelloWorld.wsdl", $soapConfig);
//$soapServer->addFunction("hello");

$soapServer = new SoapServer("call-sp.wsdl", $soapConfig);
$soapServer->addFunction("callSP");

try {
    $soapServer->handle();
}
catch (Exception $e) {
    $soapServer->fault('Sender', $e->getMessage());
}
