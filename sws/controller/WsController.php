<?php

require_once 'db/DbConnection.php';

/*
<message>
  <functionId></functionId>
  <parameterList>
    <parameter>
      <name>param1</name>
      <value>irwin</value>
    </parameter>
    <parameter>
      <name>param2</name>
      <value>bekasi</value>
    </parameter>    
  </parameterList>
</message>
*/
$app->post('/ws', function() use ($app) {
    $sql = 'begin PROC_WITH_CURSOR(:number, :cursbv); end;';
	
	// read http body
	$http_body = @file_get_contents('php://input');
	
	// xml parsing
	$xml = new SimpleXMLElement($http_body);
	echo "functionId = {$xml->functionId} \n";
	
	foreach ($xml->parameterList->parameter as $element) {
	  foreach($element as $key => $val) {
	   echo "parameter name={$key} : value={$val} \n";
	  }
	}	
	
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
		echo json_encode($results);
		$xmlOutput = new SimpleXMLElement('<rootTag/>');
		array_to_xml($xmlOutput, $results[0]);
		print $xmlOutput->asXML();
		
		oci_free_statement($stid);
		oci_free_statement($curs);
		oci_close($conn);
	} catch (Exception $e) {
		//error_log($e->getMessage(), 3, '/var/tmp/phperror.log'); //Write error log
		echo '{"error":{"text":'. $e->getMessage() .'}}';
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
