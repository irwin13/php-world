<?php

require_once 'db/DbConnection.php';
// SQL EXAMPLE for call oracle store procedure  = 'begin PROC_WITH_CURSOR(:param1, :param2, :out_cursor); end;';
/*
input format 
<inputMessage>
	<username>sucofindo</username>
	<password>12345</password>
	<functionId>PROC_WITH_CURSOR</functionId>
	<parameterList>
		<parameter>
			<name>number</name>
			<value>13</value>
		</parameter>
		<parameter>
			<name>char</name>
			<value>irwin</value>
		</parameter>    
	</parameterList>
</inputMessage>
*/

/*
output format
<outputMessage>
	<status></status>
	<message></message>
	<0>
		<col1>data1</col1>
		<col2>data2</col1>
		<col2>data2</col1>
	</0>
	<1>
		<col1>data1</col1>
		<col2>data2</col1>
		<col2>data2</col1>
	</1>		
</outputMessage>
*/

$app->post('/ws', function() use ($app) {
    	
	try {
		
		$sql = 'begin ';
		
		// read http body
		$http_body = @file_get_contents('php://input');
		$xmlOutput = new SimpleXMLElement('<outputMessage/>');
		
		// xml parsing	
		$parser = xml_parser_create();
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);	
		xml_parse_into_struct($parser, $http_body, $xmlValue, $xmlTag);
		xml_parser_free($parser);
		
		//echo "\nTags array\n";
		//print_r($xmlTag);	
		//echo "\nVals array\n";
		//print_r($xmlValue);
		
		// call validateUser function based on username and password in xml, if == 'Y' continue process, if == 'N' cancel process, return xml denied
		$username = $xmlValue[1]['value'];
		$password = $xmlValue[2]['value'];
		//echo "username = {$username} \n";
		//echo "password = {$password} \n";
		
		$validationStatus = validateUser($username, $password);
		
		if ($validationStatus == 'Y') {

			$functionId = $xmlValue[3]['value'];
			$mappingTable = functionMapping($functionId);
			//echo "functionId = {$functionId} \n";
			//print_r($mappingTable);
						
			if (count($mappingTable) > 0) {
				$functionMap = $mappingTable[0];
				$sourceName = $functionMap['SOURCE_NAME'];				
				$outputType = $functionMap['OUTPUT_TYPE'];
				//echo "sourceName = {$sourceName} \n";
				//echo "outputType = {$outputType} \n";
				
				$isCursor;
				if ($outputType == 'cur') {
					$isCursor = true;
				} else {
					$isCursor = false;
				}
				
				$sql .= $sourceName . "(";

				foreach($xmlTag as $key => $value) {
					if ($key == 'name') {
						$arrayIndex = $value;
						for ($i = 0; $i < count($arrayIndex); $i++) {
							$index = $arrayIndex[$i];
							$sql .= ':'.$xmlValue[$index]['value'].', ';
						}
					}
				}
				
				$sql .= ':out_cursor); end;';
				//echo "call Store Procedure = {$sql} \n";
				
				$results = array();
				$conn = getDbConnection();
				$stid = oci_parse($conn, $sql);
						
				if ($isCursor) {
					// execute call to database with return type cursor
					$curs = oci_new_cursor($conn);
								
					//oci_bind_by_name($stid, ":number", $num); // untuk input maxlength dan type bisa dikosongkan, default adalah -1 dan SQLT_CHR
					//oci_bind_by_name($stid, ":number", $num, -1, OCI_B_INT); // eksplisit menyebut type adalah INT
					//oci_bind_by_name($stid, ":number", $num, -1, SQLT_CHR); // default type adalah VARCHAR / CHAR ,masih bisa, php akan melakukan konversi otomatis
									
					foreach($xmlTag as $key => $value) {
						if ($key == 'name') {
							$arrayIndex = $value;
							for ($i = 0; $i < count($arrayIndex); $i++) {
								$index = $arrayIndex[$i];
								//echo "oci binding parameter :{$xmlValue[$index]['value']} with value = {$xmlValue[$index + 1]['value']} \n";				
								oci_bind_by_name($stid, ":".$xmlValue[$index]['value'], $xmlValue[$index + 1]['value'], 10000);
							}
						}
					}
							
					oci_bind_by_name($stid, ":out_cursor", $curs, 32000, OCI_B_CURSOR); // untuk out harus di define type OCI_B_CURSOR
					
					oci_execute($stid);
					oci_execute($curs);
					
					while (($row = oci_fetch_array($curs, OCI_ASSOC+OCI_RETURN_NULLS)) != false) {
						$results[] = $row;
					}				
					
					//echo json_encode($results);
					
					$xmlOutput->addChild('status', 'success');
					$xmlOutput->addChild('message', 'success');
					array_to_xml($xmlOutput->addChild('data'), $results);			
					
					oci_free_statement($stid);
					oci_free_statement($curs);	
					oci_close($conn);		
				} else {
					// execute call to database with return type varchar or number 
					foreach($xmlTag as $key => $value) {
						if ($key == 'name') {
							$arrayIndex = $value;
							for ($i = 0; $i < count($arrayIndex); $i++) {
								$index = $arrayIndex[$i];
								//echo "oci binding parameter :{$xmlValue[$index]['value']} with value = {$xmlValue[$index + 1]['value']} \n";				
								oci_bind_by_name($stid, ":".$xmlValue[$index]['value'], $xmlValue[$index + 1]['value'], 10000);
							}
						}
					}
							
					oci_bind_by_name($stid, ":out_cursor", $varOutput, 10000);
					oci_execute($stid);

					$xmlOutput->addChild('status', 'success');
					$xmlOutput->addChild('message', 'success');
					$xmlOutput->addChild('data', $varOutput);
					//print "output response = ". $varOutput . "\n";			
					
					oci_free_statement($stid);
					oci_close($conn);		
				}
				
			} else {
				// functionId not found on database
				$xmlOutput->addChild('status', 'error');
				$xmlOutput->addChild('message', 'functionId '.$functionId.' not found');									
			}

		} else {
			// user validation failed, return 'N' from database
			$xmlOutput->addChild('status', 'error');
			$xmlOutput->addChild('message', 'invalid username or password');					
		}
		
	} catch (Exception $e) {
		//error_log($e->getMessage(), 3, '/var/tmp/phperror.log'); //Write error log
		//echo '{"error":{"text":'. $e->getMessage() .'}}';
		$xmlOutput->addChild('status', 'error');
		$xmlOutput->addChild('message', $e->getMessage());
	} 
	
	print $xmlOutput->asXML();

});

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
		$sql = 'begin :response := FND_USER_PKG_WRP.FORMS_VALIDATE_PASSWORD(:username, :password); end;';
		
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
	
    $sql = 'SELECT SOURCE_NAME, OUTPUT_TYPE FROM SCI_INT_LIST_TBL WHERE ID = :id';
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


// web-service to test validation function on database
$app->post('/ws-function', function() use ($app) {
	
	print "validateUser = ".validateUser('sucofindo', '12345') . "\n";
	
	try {
		$sql = 'begin :response := FND_USER_PKG_WRP.FORMS_VALIDATE_PASSWORD(:username, :password); end;';
		
		$conn = getDbConnection();
		$stid = oci_parse($conn, $sql);

		$username = 'sucofindo';
		$password = '12345';
		
		oci_bind_by_name($stid, ":username", $username);				
		oci_bind_by_name($stid, ":password", $password);				
		oci_bind_by_name($stid, ":response", $response, 1, SQLT_CHR);
				
		oci_execute($stid);
		print "output response = ". $response . "\n";			

	} catch (Exception $e) {
		//error_log($e->getMessage(), 3, '/var/tmp/phperror.log'); //Write error log
		echo '{"error":{"text":'. $e->getMessage() .'}}';
	} finally {
		oci_free_statement($stid);
		oci_close($conn);		
	}
	
});

$app->get('/ws-test', function() use ($app) {
	$xml = '<inputMessage>
  <username>sucofindo</username>
  <password>12345</password>
  <functionId>2</functionId>
  <parameterList>
    <parameter>
      <name>param1</name>
      <value>1233</value>
    </parameter>
    <parameter>
      <name>param2</name>
      <value>abc</value>
    </parameter>    
  </parameterList>
</inputMessage>';

	$curl = curl_init('http://localhost:8888/sws/ws');
	curl_setopt($curl, CURLOPT_POST, 1);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $xml);
	$result = curl_exec($curl);
    echo "{$result}";
});