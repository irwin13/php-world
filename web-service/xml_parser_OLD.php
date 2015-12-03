<?php

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

function parse_input_xml($stringXml, $functionId) {
	$dataArray = array();
	error_log("stringXml = ".$stringXml." \n", 3, '/opt/lampp/logs/php-ws.log');
	
	if (substr($stringXml, 0, 1) === '<') {
		error_log("bukan xml \n", 3, '/opt/lampp/logs/php-ws.log');
		$dataArray[0] = $stringXml;
	} else {
		error_log("tipe xml \n", 3, '/opt/lampp/logs/php-ws.log');
		$parser = xml_parser_create();
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);	
		xml_parse_into_struct($parser, $stringXml, $xmlValue, $xmlTag);
		xml_parser_free($parser);
		error_log("xmlValue count = ".count($xmlValue)." \n", 3, '/opt/lampp/logs/php-ws.log');
		
		// parsing disini berdasarkan functionId
		if ($functionId == 'CREDIT_CHECK') {
			error_log("CREDIT_CHECK \n", 3, '/opt/lampp/logs/php-ws.log');
			// metode akses index langsung (sangat bergantung pada urutan, harus benar)
			/*
			$custNum = $xmlValue[1]['value'];
			$dataArray[0] = $custNum;
			error_log("custNum = ".$custNum." \n", 3, '/opt/lampp/logs/php-ws.log');
			
			$flag = $xmlValue[2]['value'];
			$dataArray[1] = $flag;			
			error_log("flag = ".$flag." \n", 3, '/opt/lampp/logs/php-ws.log');
			*/
			
			// method looping, dinamis, tidak tergantung urutan tag xml
			for ($i = 0; $i < count($xmlValue); $i++) {
				$xmlData = $xmlValue[$i];
				if ($xmlData['tag'] == 'cust_num') {
					$custNum = $xmlData['value'];
					$dataArray[0] = $custNum;
					error_log("custNum = ".$custNum." \n", 3, '/opt/lampp/logs/php-ws.log');
		
				} else if ($xmlData['tag'] == 'pflag') {
					$flag = $xmlData['value'];
					$dataArray[1] = $flag;
					error_log("flag = ".$flag." \n", 3, '/opt/lampp/logs/php-ws.log');
				}
			}
		} else if ($functionId == 'SEND_SERTIFIKAT') {
			error_log("SEND_SERTIFIKAT \n", 3, '/opt/lampp/logs/php-ws.log');
			
			for ($i = 0; $i < count($xmlValue); $i++) {
				$xmlData = $xmlValue[$i];
				if ($xmlData['tag'] == 'order_id') {
					$orderId = $xmlData['value'];
					$dataArray[0] = $orderId;
					error_log("orderId = ".$orderId." \n", 3, '/opt/lampp/logs/php-ws.log');		
				} else if ($xmlData['tag'] == 'line_id') {
					$lineId = $xmlData['value'];
					$dataArray[1] = $lineId;
					error_log("lineId = ".$lineId." \n", 3, '/opt/lampp/logs/php-ws.log');
				} else if ($xmlData['tag'] == 'no_sertifikat') {
					$noSertifikat = $xmlData['value'];
					$dataArray[2] = $noSertifikat;
					error_log("noSertifikat = ".$noSertifikat." \n", 3, '/opt/lampp/logs/php-ws.log');
				} else if ($xmlData['tag'] == 'sertifikat_id') {
					$sertifikatId = $xmlData['value'];
					$dataArray[3] = $sertifikatId;
					error_log("sertifikatId = ".$sertifikatId." \n", 3, '/opt/lampp/logs/php-ws.log');
				} else if ($xmlData['tag'] == 'tgl_sertifikat') {
					$tglSertifikat = $xmlData['value'];
					$dataArray[4] = $tglSertifikat;
					error_log("tglSertifikat = ".$tglSertifikat." \n", 3, '/opt/lampp/logs/php-ws.log');
				}					
			}		
		}
	}

	return $dataArray;
}