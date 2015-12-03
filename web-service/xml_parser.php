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
			$xml->addChild($key, htmlspecialchars($value));
		}   
	
    }  
}

function isXmlString($str) {
	libxml_use_internal_errors(true);
	$xmlDoc = simplexml_load_string($str);
	if ($xmlDoc) {
		return true;
	} else {
		return false;
	}
}

function countXmlOutput($stringXmlOutput){
	if (isXmlString($stringXmlOutput)) {
		$totalCount = 0;

		$parser = xml_parser_create();
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);	
		xml_parse_into_struct($parser, $stringXmlOutput, $xmlValue, $xmlTag);
		xml_parser_free($parser);
			
		for ($i = 0; $i < count($xmlValue); $i++) {
			$xmlData = $xmlValue[$i];
			if ($xmlData['tag'] == 'rowData') {
				$totalCount++;
				error_log("totalCount = ".$totalCount." \n", 3, '/opt/lampp/logs/php-ws.log');			
			}
		}
		
		return $totalCount / 2;	
	
	} else {
		return -1;
	}
}

function parse_input_xml($stringXml, $functionId) {
	$dataArray = array();
	$stringXml = preg_replace('/\s+/', '', $stringXml);
	error_log("======== stringXml = [".$stringXml."]============== \n", 3, '/opt/lampp/logs/php-ws.log');
	if (isXmlString($stringXml)) {
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
		
				} else if ($xmlData['tag'] == 'flag') {
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
		} else if($functionId == 'RET_ORDER'){
			//tambahan functionID
			error_log("RET_ORDER \n", 3, '/opt/lampp/logs/php-ws.log');
			for ($i = 0; $i < count($xmlValue); $i++) {
				$xmlData = $xmlValue[$i];
				if ($xmlData['tag'] == 'order_number') {
					$orderNum = $xmlData['value'];
					$dataArray[0] = $orderNum;
					error_log("orderNum = ".$orderNum." \n", 3, '/opt/lampp/logs/php-ws.log');
		
				} else {
					
				}
			}
		} else if ($functionId == 'CREATE_CUS') {
			error_log("CREATE_CUS \n", 3, '/opt/lampp/logs/php-ws.log');
   
			for ($i = 0; $i < count($xmlValue); $i++) {
				$xmlData = $xmlValue[$i];
				if ($xmlData['tag'] == 'customer_type') {
					 $customertype = $xmlData['value'];
					 $dataArray[0] = $customertype;
					 error_log("customertype = ".$customertype." \n", 3, '/opt/lampp/logs/php-ws.log');  
				} else if ($xmlData['tag'] == 'party_name') {
					 $partyname = $xmlData['value'];
					 $dataArray[1] = $partyname;
					 error_log("partyname = ".$partyname." \n", 3, '/opt/lampp/logs/php-ws.log');
				} else if ($xmlData['tag'] == 'bentukusaha') {
					 $bentukusaha = $xmlData['value'];
					 $dataArray[2] = $bentukusaha;
					 error_log("bentukusaha = ".$bentukusaha." \n", 3, '/opt/lampp/logs/php-ws.log');
				} else if ($xmlData['tag'] == 'sektorusaha') {
					 $sektorusaha = $xmlData['value'];
					 $dataArray[3] = $sektorusaha;
					 error_log("sektorusaha = ".$sektorusaha." \n", 3, '/opt/lampp/logs/php-ws.log');
				} else if ($xmlData['tag'] == 'bidangusaha') {
					 $bidangusaha = $xmlData['value'];
					 $dataArray[4] = $bidangusaha;
					 error_log("bidangusaha = ".$bidangusaha." \n", 3, '/opt/lampp/logs/php-ws.log');
				} else if ($xmlData['tag'] == 'address1') {
					 $address1 = $xmlData['value'];
					 $dataArray[5] = $address1;
					 error_log("address1 = ".$address1." \n", 3, '/opt/lampp/logs/php-ws.log');
				} else if ($xmlData['tag'] == 'address2') {
					 $address2 = $xmlData['value'];
					 $dataArray[6] = $address2;
					 error_log("address2 = ".$address2." \n", 3, '/opt/lampp/logs/php-ws.log');
				} else if ($xmlData['tag'] == 'tax_ref') {
					 $tax_ref = $xmlData['value'];
					 $dataArray[7] = $tax_ref;
					 error_log("tax_ref = ".$tax_ref." \n", 3, '/opt/lampp/logs/php-ws.log');
				} else if ($xmlData['tag'] == 'city') {
					 $city = $xmlData['value'];
					 $dataArray[8] = $city;
					 error_log("city = ".$city." \n", 3, '/opt/lampp/logs/php-ws.log');
				} else if ($xmlData['tag'] == 'country') {
					 $country = $xmlData['value'];
					 $dataArray[9] = $country;
					 error_log("country = ".$country." \n", 3, '/opt/lampp/logs/php-ws.log');
				} else if ($xmlData['tag'] == 'state') {
					 $state = $xmlData['value'];
					 $dataArray[10] = $state;
					 error_log("state = ".$state." \n", 3, '/opt/lampp/logs/php-ws.log');
				} else if ($xmlData['tag'] == 'person_first_name') {
					 $person_first_name = $xmlData['value'];
					 $dataArray[11] = $person_first_name;
					 error_log("person_first_name = ".$person_first_name." \n", 3, '/opt/lampp/logs/php-ws.log');
				} else if ($xmlData['tag'] == 'person_last_name') {
					 $person_last_name = $xmlData['value'];
					 $dataArray[12] = $person_last_name;
					 error_log("person_last_name = ".$person_last_name." \n", 3, '/opt/lampp/logs/php-ws.log');
				} else if ($xmlData['tag'] == 'person_title') {
					 $person_title = $xmlData['value'];
					 $dataArray[13] = $person_title;
					 error_log("person_title = ".$person_title." \n", 3, '/opt/lampp/logs/php-ws.log');
				} else if ($xmlData['tag'] == 'person_identifier') {
					 $person_identifier = $xmlData['value'];
					 $dataArray[14] = $person_identifier;
					 error_log("person_identifier = ".$person_identifier." \n", 3, '/opt/lampp/logs/php-ws.log');
				} else if ($xmlData['tag'] == 'phone_country_code') {
					 $phone_country_code = $xmlData['value'];
					 $dataArray[15] = $phone_country_code;
					 error_log("phone_country_code = ".$phone_country_code." \n", 3, '/opt/lampp/logs/php-ws.log');
				} else if ($xmlData['tag'] == 'phone_area_code') {
					 $phone_country_code = $xmlData['value'];
					 $dataArray[16] = $phone_area_code;
					 error_log("phone_area_code = ".$phone_area_code." \n", 3, '/opt/lampp/logs/php-ws.log');
				} else if ($xmlData['tag'] == 'phone_number1') {
					 $phone_number1 = $xmlData['value'];
					 $dataArray[17] = $phone_number1;
					 error_log("phone_number1 = ".$phone_number1." \n", 3, '/opt/lampp/logs/php-ws.log');
				} else if ($xmlData['tag'] == 'phone_number2') {
					 $phone_number2 = $xmlData['value'];
					 $dataArray[18] = $phone_number2;
					 error_log("phone_number2 = ".$phone_number2." \n", 3, '/opt/lampp/logs/php-ws.log');
				} else if ($xmlData['tag'] == 'fax') {
					 $fax = $xmlData['value'];
					 $dataArray[19] = $fax;
					 error_log("fax = ".$fax." \n", 3, '/opt/lampp/logs/php-ws.log');
				} else if ($xmlData['tag'] == 'email_address') {
					 $email_address = $xmlData['value'];
					 $dataArray[20] = $email_address;
					 error_log("email_address = ".$email_address." \n", 3, '/opt/lampp/logs/php-ws.log');
				} 
			}  
			//BATAS CREATE_CUS
		} else if ($functionId == 'CREATE_ORDER') {
			//mulai create order
			error_log("CREATE_ORDER \n", 3, '/opt/lampp/logs/php-ws.log');
	   
		   for ($i = 0; $i < count($xmlValue); $i++) {
				$xmlData = $xmlValue[$i];
				if ($xmlData['tag'] == 'order_type') {
					 $order_type = $xmlData['value'];
					 $dataArray[0] = $order_type;
					 error_log("order_type = ".$order_type." \n", 3, '/opt/lampp/logs/php-ws.log');  
				} else if ($xmlData['tag'] == 'date_order') {
					 $date_order = $xmlData['value'];
					 $dataArray[1] = $date_order;
					 error_log("date_order = ".$date_order." \n", 3, '/opt/lampp/logs/php-ws.log');
				} else if ($xmlData['tag'] == 'price_list') {
					 $price_list = $xmlData['value'];
					 $dataArray[2] = $price_list;
					 error_log("price_list = ".$price_list." \n", 3, '/opt/lampp/logs/php-ws.log');
				} else if ($xmlData['tag'] == 'sales_person') {
					 $sales_person = $xmlData['value'];
					 $dataArray[3] = $sales_person;
					 error_log("sales_person = ".$sales_person." \n", 3, '/opt/lampp/logs/php-ws.log');
				} else if ($xmlData['tag'] == 'currency') {
					 $currency = $xmlData['value'];
					 $dataArray[4] = $currency;
					 error_log("currency = ".$currency." \n", 3, '/opt/lampp/logs/php-ws.log');
				} else if ($xmlData['tag'] == 'customer') {
					 $customer = $xmlData['value'];
					 $dataArray[5] = $customer;
					 error_log("customer = ".$customer." \n", 3, '/opt/lampp/logs/php-ws.log');
				} else if ($xmlData['tag'] == 'payment_term') {
					 $payment_term = $xmlData['value'];
					 $dataArray[6] = $payment_term;
					 error_log("payment_term = ".$payment_term." \n", 3, '/opt/lampp/logs/php-ws.log');
				} else if ($xmlData['tag'] == 'line') {
					 $line = $xmlData['value'];
					 $dataArray[7] = $line;
					 error_log("line = ".$line." \n", 3, '/opt/lampp/logs/php-ws.log');
				} else if ($xmlData['tag'] == 'order_item') {
					 $order_item = $xmlData['value'];
					 $dataArray[8] = $order_item;
					 error_log("order_item = ".$order_item." \n", 3, '/opt/lampp/logs/php-ws.log');
				} else if ($xmlData['tag'] == 'qty') {
					 $qty = $xmlData['value'];
					 $dataArray[9] = $qty;
					 error_log("qty = ".$qty." \n", 3, '/opt/lampp/logs/php-ws.log');
				} else if ($xmlData['tag'] == 'unit_selling_price') {
					 $unit_selling_price = $xmlData['value'];
					 $dataArray[10] = $unit_selling_price;
					 error_log("unit_selling_price = ".$unit_selling_price." \n", 3, '/opt/lampp/logs/php-ws.log');
				} else if ($xmlData['tag'] == 'request_date') {
					 $request_date = $xmlData['value'];
					 $dataArray[11] = $request_date;
					 error_log("request_date = ".$request_date." \n", 3, '/opt/lampp/logs/php-ws.log');
				} else if ($xmlData['tag'] == 'komoditi') {
					 $komoditi = $xmlData['value'];
					 $dataArray[12] = $komoditi;
					 error_log("komoditi = ".$komoditi." \n", 3, '/opt/lampp/logs/php-ws.log');
				} else if ($xmlData['tag'] == 'payment_type') {
					 $payment_type = $xmlData['value'];
					 $dataArray[13] = $payment_type;
					 error_log("payment_type = ".$payment_type." \n", 3, '/opt/lampp/logs/php-ws.log');
				} else if ($xmlData['tag'] == 'status') {
					 $status = $xmlData['value'];
					 $dataArray[14] = $status;
					 error_log("status = ".$status." \n", 3, '/opt/lampp/logs/php-ws.log');
				} else if ($xmlData['tag'] == 'created_by') {
					 $created_by = $xmlData['value'];
					 $dataArray[15] = $created_by;
					 error_log("created_by = ".$created_by." \n", 3, '/opt/lampp/logs/php-ws.log');
				} else if ($xmlData['tag'] == 'charge_name') {
					 $charge_name = $xmlData['value'];
					 $dataArray[16] = $charge_name;
					 error_log("charge_name = ".$charge_name." \n", 3, '/opt/lampp/logs/php-ws.log');
				} else if ($xmlData['tag'] == 'charge_amount') {
					 $charge_amount = $xmlData['value'];
					 $dataArray[17] = $charge_amount;
					 error_log("charge_amount = ".$charge_amount." \n", 3, '/opt/lampp/logs/php-ws.log');
				} else if ($xmlData['tag'] == 'order_name') {
					 $order_name = $xmlData['value'];
					 $dataArray[18] = $order_name;
					 error_log("order_name = ".$order_name." \n", 3, '/opt/lampp/logs/php-ws.log');
				} else if ($xmlData['tag'] == 'data_type') {
					 $data_type = $xmlData['value'];
					 $dataArray[19] = $data_type;
					 error_log("data_type = ".$data_type." \n", 3, '/opt/lampp/logs/php-ws.log');
				} else if ($xmlData['tag'] == 'percent') {
					 $percent = $xmlData['value'];
					 $dataArray[20] = $percent;
					 error_log("percent = ".$percent." \n", 3, '/opt/lampp/logs/php-ws.log');
				} else if ($xmlData['tag'] == 'payment_term_line') {
					 $payment_term_line = $xmlData['value'];
					 $dataArray[21] = $payment_term_line;
					 error_log("payment_term_line = ".$payment_term_line." \n", 3, '/opt/lampp/logs/php-ws.log');
				}else if ($xmlData['tag'] == 'user_name') {
					 $user_name = $xmlData['value'];
					 $dataArray[22] = $user_name;
					 error_log("user_name = ".$user_name." \n", 3, '/opt/lampp/logs/php-ws.log');
				}else if ($xmlData['tag'] == 'id_app') {
					 $id_app = $xmlData['value'];
					 $dataArray[23] = $id_app;
					 error_log("id_app = ".$id_app." \n", 3, '/opt/lampp/logs/php-ws.log');
				}else if ($xmlData['tag'] == 'no_reff') {
					 $no_reff = $xmlData['value'];
					 $dataArray[24] = $no_reff;
					 error_log("no_reff = ".$no_reff." \n", 3, '/opt/lampp/logs/php-ws.log');
				}else if ($xmlData['tag'] == 'org_id') {
					 $org_id = $xmlData['value'];
					 $dataArray[25] = $org_id;
					 error_log("org_id = ".$org_id." \n", 3, '/opt/lampp/logs/php-ws.log');
				}
			}  
		} else if($functionId == 'UP_CUST') {
			//tambahan functionID
			error_log("UP_CUST \n", 3, '/opt/lampp/logs/php-ws.log');
			for ($i = 0; $i < count($xmlValue); $i++) {
				$xmlData = $xmlValue[$i];
				if ($xmlData['tag'] == 'acct_num') {
					$acct_num = $xmlData['value'];
					$dataArray[0] = $acct_num;
					error_log("acct_num = ".$acct_num." \n", 3, '/opt/lampp/logs/php-ws.log');
		
				} else if ($xmlData['tag'] == 'org_id') {
				 $org_id = $xmlData['value'];
				 $dataArray[1] = $org_id;
				 error_log("org_id = ".$org_id." \n", 3, '/opt/lampp/logs/php-ws.log');
				}
			}
		} else if($functionId == 'ORDER_CONFRM'){
			error_log("ORDER_CONFRM \n", 3, '/opt/lampp/logs/php-ws.log');
			for ($i = 0; $i < count($xmlValue); $i++) {
				$xmlData = $xmlValue[$i];
				if ($xmlData['tag'] == 'order_number') {
					$order_number = $xmlData['value'];
					$dataArray[0] = $order_number;
					error_log("order_number = ".$order_number." \n", 3, '/opt/lampp/logs/php-ws.log');
		
				} else {
					
				}
			}
		} else if($functionId == 'ORDER_BOOKING'){
			error_log("ORDER_BOOKING \n", 3, '/opt/lampp/logs/php-ws.log');
			for ($i = 0; $i < count($xmlValue); $i++) {
				$xmlData = $xmlValue[$i];
				if ($xmlData['tag'] == 'order_number') {
					$order_number = $xmlData['value'];
					$dataArray[0] = $order_number;
					error_log("order_number = ".$order_number." \n", 3, '/opt/lampp/logs/php-ws.log');
		
				} else if ($xmlData['tag'] == 'org_id') {
					 $org_id = $xmlData['value'];
					 $dataArray[1] = $org_id;
					 error_log("org_id = ".$org_id." \n", 3, '/opt/lampp/logs/php-ws.log');
				} else if ($xmlData['tag'] == 'user_id') {
					 $user_id = $xmlData['value'];
					 $dataArray[2] = $user_id;
					 error_log("user_id = ".$user_id." \n", 3, '/opt/lampp/logs/php-ws.log');
				}
			}
		} else if($functionId == 'CANCEL_ORDER'){
			error_log("CANCEL_ORDER \n", 3, '/opt/lampp/logs/php-ws.log');
			for ($i = 0; $i < count($xmlValue); $i++) {
				$xmlData = $xmlValue[$i];
				if ($xmlData['tag'] == 'order_number') {
					$order_number = $xmlData['value'];
					$dataArray[0] = $order_number;
					error_log("order_number = ".$order_number." \n", 3, '/opt/lampp/logs/php-ws.log');
		
				} else{	}
			}
		} else if ($functionId == 'IWO') {
			error_log("IWO \n", 3, '/opt/lampp/logs/php-ws.log');
   
			for ($i = 0; $i < count($xmlValue); $i++) {
				$xmlData = $xmlValue[$i];
				if ($xmlData['tag'] == 'date_order') {
					 $date_order = $xmlData['value'];
					 $dataArray[0] = $date_order;
					 error_log("date_order = ".$date_order." \n", 3, '/opt/lampp/logs/php-ws.log');
				} else if ($xmlData['tag'] == 'price_list') {
					 $price_list = $xmlData['value'];
					 $dataArray[1] = $price_list;
					 error_log("price_list = ".$price_list." \n", 3, '/opt/lampp/logs/php-ws.log');
				} else if ($xmlData['tag'] == 'sales_person') {
					 $sales_person = $xmlData['value'];
					 $dataArray[2] = $sales_person;
					 error_log("sales_person = ".$sales_person." \n", 3, '/opt/lampp/logs/php-ws.log');
				} else if ($xmlData['tag'] == 'currency') {
					 $currency = $xmlData['value'];
					 $dataArray[3] = $currency;
					 error_log("currency = ".$currency." \n", 3, '/opt/lampp/logs/php-ws.log');
				} else if ($xmlData['tag'] == 'customer') {
					 $customer = $xmlData['value'];
					 $dataArray[4] = $customer;
					 error_log("customer = ".$customer." \n", 3, '/opt/lampp/logs/php-ws.log');
				} else if ($xmlData['tag'] == 'payment_term') {
					 $payment_term = $xmlData['value'];
					 $dataArray[5] = $payment_term;
					 error_log("payment_term = ".$payment_term." \n", 3, '/opt/lampp/logs/php-ws.log');
				} else if ($xmlData['tag'] == 'line') {
					 $line = $xmlData['value'];
					 $dataArray[6] = $line;
					 error_log("line = ".$line." \n", 3, '/opt/lampp/logs/php-ws.log');
				} else if ($xmlData['tag'] == 'order_item') {
					 $order_item = $xmlData['value'];
					 $dataArray[7] = $order_item;
					 error_log("order_item = ".$order_item." \n", 3, '/opt/lampp/logs/php-ws.log');
				} else if ($xmlData['tag'] == 'qty') {
					 $qty = $xmlData['value'];
					 $dataArray[8] = $qty;
					 error_log("qty = ".$qty." \n", 3, '/opt/lampp/logs/php-ws.log');
				} else if ($xmlData['tag'] == 'unit_selling_price') {
					 $unit_selling_price = $xmlData['value'];
					 $dataArray[9] = $unit_selling_price;
					 error_log("unit_selling_price = ".$unit_selling_price." \n", 3, '/opt/lampp/logs/php-ws.log');
				} else if ($xmlData['tag'] == 'request_date') {
					 $request_date = $xmlData['value'];
					 $dataArray[10] = $request_date;
					 error_log("request_date = ".$request_date." \n", 3, '/opt/lampp/logs/php-ws.log');
				} else if ($xmlData['tag'] == 'komoditi') {
					 $komoditi = $xmlData['value'];
					 $dataArray[11] = $komoditi;
					 error_log("komoditi = ".$komoditi." \n", 3, '/opt/lampp/logs/php-ws.log');
				} else if ($xmlData['tag'] == 'payment_type') {
					 $payment_type = $xmlData['value'];
					 $dataArray[12] = $payment_type;
					 error_log("payment_type = ".$payment_type." \n", 3, '/opt/lampp/logs/php-ws.log');
				} else if ($xmlData['tag'] == 'status') {
					 $status = $xmlData['value'];
					 $dataArray[13] = $status;
					 error_log("status = ".$status." \n", 3, '/opt/lampp/logs/php-ws.log');
				} else if ($xmlData['tag'] == 'created_by') {
					 $created_by = $xmlData['value'];
					 $dataArray[14] = $created_by;
					 error_log("created_by = ".$created_by." \n", 3, '/opt/lampp/logs/php-ws.log');
				} else if ($xmlData['tag'] == 'charge_name') {
					 $charge_name = $xmlData['value'];
					 $dataArray[15] = $charge_name;
					 error_log("charge_name = ".$charge_name." \n", 3, '/opt/lampp/logs/php-ws.log');
				} else if ($xmlData['tag'] == 'charge_amount') {
					 $charge_amount = $xmlData['value'];
					 $dataArray[16] = $charge_amount;
					 error_log("charge_amount = ".$charge_amount." \n", 3, '/opt/lampp/logs/php-ws.log');
				} else if ($xmlData['tag'] == 'order_name') {
					 $order_name = $xmlData['value'];
					 $dataArray[17] = $order_name;
					 error_log("order_name = ".$order_name." \n", 3, '/opt/lampp/logs/php-ws.log');
				} else if ($xmlData['tag'] == 'data_type') {
					 $data_type = $xmlData['value'];
					 $dataArray[18] = $data_type;
					 error_log("data_type = ".$data_type." \n", 3, '/opt/lampp/logs/php-ws.log');
				} else if ($xmlData['tag'] == 'percent') {
					 $percent = $xmlData['value'];
					 $dataArray[19] = $percent;
					 error_log("percent = ".$percent." \n", 3, '/opt/lampp/logs/php-ws.log');
				} else if ($xmlData['tag'] == 'payment_term_line') {
					 $payment_term_line = $xmlData['value'];
					 $dataArray[20] = $payment_term_line;
					 error_log("payment_term_line = ".$payment_term_line." \n", 3, '/opt/lampp/logs/php-ws.log');
				}else if ($xmlData['tag'] == 'user_name') {
					 $user_name = $xmlData['value'];
					 $dataArray[21] = $user_name;
					 error_log("user_name = ".$user_name." \n", 3, '/opt/lampp/logs/php-ws.log');
				}else if ($xmlData['tag'] == 'id_app') {
					 $id_app = $xmlData['value'];
					 $dataArray[22] = $id_app;
					 error_log("id_app = ".$id_app." \n", 3, '/opt/lampp/logs/php-ws.log');
				}else if ($xmlData['tag'] == 'no_reff') {
					 $no_reff = $xmlData['value'];
					 $dataArray[23] = $no_reff;
					 error_log("no_reff = ".$no_reff." \n", 3, '/opt/lampp/logs/php-ws.log');
				}else if ($xmlData['tag'] == 'org_id') {
					 $org_id = $xmlData['value'];
					 $dataArray[24] = $org_id;
					 error_log("org_id = ".$org_id." \n", 3, '/opt/lampp/logs/php-ws.log');
				}
			}  
		} else {
			throw new ErrorException("functionId not registered");
		}  	
	} else {
		error_log("bukan xml \n", 3, '/opt/lampp/logs/php-ws.log');
		$dataArray[0] = $stringXml;
	}

	return $dataArray;
}