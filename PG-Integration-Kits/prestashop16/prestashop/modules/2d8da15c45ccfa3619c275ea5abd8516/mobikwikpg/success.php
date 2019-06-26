<?php  
 
		include(dirname(__FILE__).'/../../config/config.inc.php');
		include(dirname(__FILE__).'/../../init.php');
		include(dirname(__FILE__).'/mobikwikpg.php');
		include(dirname(__FILE__).'/../../header.php');
		include(dirname(__FILE__).'/mobikwikpg_common.inc');

			
		 $mbk_pg = new mobikwikpg();
		 
		 $postdata=$_REQUEST;
		             
		 $key=Configuration::get('MOBIKWIKPG_MERCHANT_IDENTIFIER');
	  	 $secret_key_var=Configuration::get('MOBIKWIKPG_SECRET_KEY');

		
		

		 $baseUrl=Tools::getShopDomain(true, true).__PS_BASE_URI__;	
		 $order_id= $postdata['orderId'];
		 $transactionId= $postdata['pgTransId'];		 
		  
		 $smarty->assign('baseUrl',$baseUrl);
		 $smarty->assign('orderId',$order_id);
		 $smarty->assign('transactionId',$transactionId);

		 $amount        = $postdata['amount'];
		 $productinfo   = $postdata['productDescription'];
		 
		 $all = '';
      $checksumsequence= array("amount","bank","bankid","cardId",
        "cardScheme","cardToken","cardhashid","doRedirect","orderId",
        "paymentMethod","paymentMode","responseCode","responseDescription",
        "productDescription","product1Description","product2Description",
        "product3Description","product4Description","pgTransId","pgTransTime");
      foreach($checksumsequence as $seqvalue) {
        if(array_key_exists($seqvalue, $postdata))  {
      
          $all .= $seqvalue;
          $all .="=";
          $all .= $postdata[$seqvalue];
          $all .= "&";
          }
        }
     $calculated_checksum = hash_hmac('sha256', $all , $secret_key_var);

		$response_checksum = $postdata['checksum']; 
			 if($calculated_checksum != $response_checksum)
			 {			
				$history = new OrderHistory();
				$history->id_order = (int)($order_id);
				$history->changeIdOrderState(Configuration::get('PS_OS_ERROR'), $history->id_order);
				$history->add();				
				$smarty->display('failure.tpl');
             }
			 else
			 {			
				global $cart,$cookie;
				$total = $amount/100;
				$currency = new Currency(Tools::getValue('currency_payement', false) ? Tools::getValue('currency_payement') : $cookie->id_currency);
				$customer = new Customer((int)$cart->id_customer);

				if($postdata['responseCode'] == 100)
				{
					
                   $mbk_pg->validateOrder((int)$cart->id, Configuration::get('PS_OS_PAYMENT'), $total, $mbk_pg->displayName, NULL, NULL, (int)$currency->id, false, $customer->secure_key);

                   $result = Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ . 'orders WHERE id_cart = ' . (int)$cart->id);                 
				   $smarty->display('success.tpl');

                  
					                   }            
				else
				{			

				   $m $mbk_pg->validateOrder((int)$cart->id, _PS_OS_ERROR_, $total, $mbk_pg->displayName, NULL, NULL, (int)$currency->id, false, $customer->secure_key);

				   $result = Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ . 'orders WHERE id_cart = ' . (int)$cart->id);

                   $smarty->display('failure.tpl');

				
				}

				
			   $successQuery="update ps_mobikwikpg_order set payment_response='$responseValue', payment_status_description= '".$postdata['responseDescription']."', payment_status= '".$postdata['responseCode']."', id_order='".$result['id_order']."'  where id_transaction= ".$postdata['orderId'];
			   Db::getInstance()->Execute($successQuery);	

            Tools::redirectLink(__PS_BASE_URI__ . 'order-detail.php?id_order=' . $result['id_order']);

			 }			
		  	
		
           include(dirname(__FILE__).'/../../footer.php');	
?>
