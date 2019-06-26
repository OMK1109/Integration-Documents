<?php  
 
			include(dirname(__FILE__).'/../../config/config.inc.php');
			include(dirname(__FILE__).'/../../init.php');
			include(dirname(__FILE__).'/mobikwikpg.php');
			include(dirname(__FILE__).'/../../header.php');
			include(dirname(__FILE__).'/mobikwikpg_common.inc');
			
			$mbk_pg = new mobikwikpg();
			$response=$_REQUEST;

			
      
			
			 $baseUrl=Tools::getShopDomain(true, true).__PS_BASE_URI__;
			
		   if ($response['status'] == 'failure')
			{
				$order_id= $response['txnid']-9410;
				
				$transactionId= $response['mihpayid'];
			//	$log=Configuration::get('mbk_pg_LOGS');
				
				 
				
					
				
				$smarty->assign('baseUrl',$baseUrl);
				$smarty->assign('orderId',$order_id);
				$smarty->assign('transactionId',$transactionId);	 
				global $cart,$cookie;
				$total = $amount;
				$currency = new Currency(Tools::getValue('currency_payement', false) ? Tools::getValue('currency_payement') : $cookie->id_currency);
				$customer = new Customer((int)$cart->id_customer);
                $mbk_pg->validateOrder((int)$cart->id, _PS_OS_ERROR_, $total, $mbk_pg->displayName, NULL, NULL, (int)$currency->id, false, $customer->secure_key);

										
		}
			
			

            $smarty->display('failure.tpl');
			$result = Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ . 'orders WHERE id_cart = ' . (int)$cart->id);

			

            Tools::redirectLink(__PS_BASE_URI__ . 'order-detail.php?id_order=' . $result['id_order']);
			
            include(dirname(__FILE__).'/../../footer.php');


?>