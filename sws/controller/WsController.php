<?php

require_once 'db/DbConnection.php';

/*
<inputMessage>
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
$app->post('/ws', function() use ($app) {
    
	// call oracle store procedure syntax = 'begin PROC_WITH_CURSOR(:param1, :param2, :out_cursor); end;';
	
	$sql = 'begin ';
	
	// read http body
	$http_body = @file_get_contents('php://input');
	
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
	
	$functionId = $xmlValue[1]['value'];
	echo "functionId = {$functionId} \n";
	$sql .= $functionId . "(";

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
	echo "call Store Procedure = {$sql} \n";
	
	try {
		$results = array();
		$num = 29;
		$conn = getDbConnection();
		$curs = oci_new_cursor($conn);
		$stid = oci_parse($conn, $sql);
		
		//oci_bind_by_name($stid, ":number", $num); // untuk input maxlength dan type bisa dikosongkan, default adalah -1 dan SQLT_CHR
		//oci_bind_by_name($stid, ":number", $num, -1, OCI_B_INT); // eksplisit menyebut type adalah INT
		//oci_bind_by_name($stid, ":number", $num, -1, SQLT_CHR); // default type adalah VARCHAR / CHAR ,masih bisa, php akan melakukan konversi otomatis
						
		foreach($xmlTag as $key => $value) {
			if ($key == 'name') {
				$arrayIndex = $value;
				for ($i = 0; $i < count($arrayIndex); $i++) {
					$index = $arrayIndex[$i];
					echo "oci binding parameter :{$xmlValue[$index]['value']} with value = {$xmlValue[$index + 1]['value']} \n";				
					oci_bind_by_name($stid, ":".$xmlValue[$index]['value'], $xmlValue[$index + 1]['value']);
				}
			}
		}
				
		oci_bind_by_name($stid, ":out_cursor", $curs, -1, OCI_B_CURSOR); // untuk out harus di define type OCI_B_CURSOR
		
		oci_execute($stid);
		oci_execute($curs);
						
		while (($row = oci_fetch_array($curs, OCI_ASSOC+OCI_RETURN_NULLS)) != false) {
			$results[] = $row;
		}				
		
		echo json_encode($results);
		
		$xmlOutput = new SimpleXMLElement('<outputMessage/>');
		array_to_xml($xmlOutput, $results);
		
		print $xmlOutput->asXML();
		
	} catch (Exception $e) {
		//error_log($e->getMessage(), 3, '/var/tmp/phperror.log'); //Write error log
		echo '{"error":{"text":'. $e->getMessage() .'}}';
	} finally {
		oci_free_statement($stid);
		oci_free_statement($curs);
		oci_close($conn);		
	}
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
