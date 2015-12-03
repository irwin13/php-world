<?php

require_once 'db_connection.php';
require_once 'db_util.php';
require_once 'xml_parser.php';

set_error_handler("warning_handler", E_WARNING);

function warning_handler($errno, $errstr, $errfile, $errline) { 
	throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}

function hello($name) {
	return 'Hello '.$name;
}

function legacy($username, $password, $applicationId, $refNo, $functionId, $data) {
	$dataArray = parse_input_xml($data, $functionId);
	
	$results = callProcedure($username, $password, $applicationId, $refNo, $functionId, $dataArray);
	$dataOutput = $results['output'];
	$outputCount = countXmlOutput($dataOutput);
	if ($outputCount > 0) {
		$results['description'] = $outputCount;	
	}	
	$status = $results['log'];
	$message = $results['message'];
	insert_log($username, $applicationId, $refNo, $functionId, 'soap', $status, $message);
	return $results;
}

$soapConfig = array('encoding' => 'UTF-8');

//$soapServer = new SoapServer("HelloWorld.wsdl", $soapConfig);
//$soapServer->addFunction("hello");

$soapServer = new SoapServer("LegacyService.wsdl", $soapConfig);
$soapServer->addFunction("legacy");

try {
    $soapServer->handle();
}
catch (Exception $e) {
    $soapServer->fault('Sender', $e->getMessage());
}
