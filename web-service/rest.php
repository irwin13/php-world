<?php

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


require_once 'db_connection.php';
require_once 'db_util.php';
require_once 'xml_parser.php';

set_error_handler("warning_handler", E_WARNING);

function warning_handler($errno, $errstr, $errfile, $errline) { 
	throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

	// read http body
	$http_body = @file_get_contents('php://input');
	$xmlOutput = new SimpleXMLElement('<web-service-output/>');
	
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

	$applicationId = $xmlValue[3]['value'];
	$refNo = $xmlValue[4]['value'];
	
	$functionId = $xmlValue[5]['value'];
	//echo "functionId = {$functionId} \n";

	$data = $xmlValue[6]['value'];

	try {
		$dataArray = parse_input_xml($data, $functionId);		 
		$results = callProcedure($username, $password, $applicationId, $refNo, $functionId, $dataArray);
		
		$status = $results['log'];
		$message = $results['message'];
		insert_log($username, $applicationId, $refNo, $functionId, 'rest', $status, $message);	
		
		array_to_xml($xmlOutput, $results);
		
	} catch (Exception $e) {
		//error_log($e->getMessage(), 3, '/var/tmp/phperror.log'); //Write error log
		//echo '{"error":{"text":'. $e->getMessage() .'}}';
		$xmlOutput->addChild('log', 'error');
		$xmlOutput->addChild('message', $e->getMessage());
		$xmlOutput->addChild('applicationId', $applicationId);
		$xmlOutput->addChild('refNo', $refNo);
		insert_log($username, $applicationId, $refNo, $functionId, 'rest', 'error', $e->getMessage());	
	} 

	
	print $xmlOutput->asXML();

} else {
	echo "only accept POST method";
}
