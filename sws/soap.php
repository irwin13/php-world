<?php

function hello($name) {
	return 'Hello '.$name;
}

$soapConfig = array('uri' => "http://test-uri/",
					'encoding' => 'UTF-8'
					);
$soapServer = new SoapServer("HelloWorld.wsdl", $soapConfig);
$soapServer->addFunction("hello");

try {
    $soapServer->handle();
}
catch (Exception $e) {
    $soapServer->fault('Sender', $e->getMessage());
}
