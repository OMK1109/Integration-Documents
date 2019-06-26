<?php


if (!defined('_PS_VERSION_'))
    exit;

class mobikwikpg extends PaymentModule
{
    const LEFT_COLUMN = 0;
    const RIGHT_COLUMN = 1;
    const FOOTER = 2;
    const DISABLE = -1;
    const SANDBOX_SECRET_KEY = '0678056d96914a8583fb518caf42828a';  // add merhantIdentifier
    const SANDBOX_MERCHANT_IDENTIFIER = 'b19e8f103bce406cbd3476431b6b7973<';     // add secret key
    
    public function __construct()
    {
        $this->name = 'mobikwikpg';
        $this->tab = 'payments_gateways';
        $this->version = '1.0'; 
    

        parent::__construct();       
       
        $this->author  = 'Mobikwik';
        $this->page = basename(__FILE__, '.php');

        $this->displayName =$this->trans('Mobikwik Payment Gateway', array(), 'Modules.Mobikwikpg.Admin');
        $this->description = $this->trans('Pay Using Mobikwik Payment Gateway', array(), 'Modules.Mobikwikpg.Admin');
        $this->confirmUninstall = $this->trans('Are you sure you want to delete these details?', array(), 'Modules.Mobikwikpg.Admin');
 
        $merchant_identifier= Configuration::get('MERCHANT_IDENTIFIER');
        $secret_key= Configuration::get('SECRET_KEY');
        $gateway_mode= Configuration::get('GATEWAY_MODE');

        $this->page = basename(__FILE__, '.php');


    }

    public function install()
    {
        unlink(dirname(__FILE__).'/../../cache/class_index.php');
        if ( !parent::install() 
            OR !$this->registerHook('payment') 
            OR !$this->registerHook('paymentReturn') 
            OR !Configuration::updateValue('MOBIKWIKPG_MERCHANT_IDENTIFIER', '') 
            OR !Configuration::updateValue('MOBIKWIKPG_SECRET_KEY', '') 
            OR !Configuration::updateValue('GATEWAY_MODE', '') 
           
              )
        {            
            return false;
        }

        if (!Db::getInstance()->Execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'mobikwikpg_order` (
          `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `id_order` int(10) unsigned NOT NULL,
          `id_transaction` varchar(255) NOT NULL,
          `payment_status` varchar(255) NOT NULL,
          `payment_status_description` varchar(255) NOT NULL,
          `order_date` timestamp default now(),
           
          PRIMARY KEY (`id`)
        ) ENGINE='._MYSQL_ENGINE_.'  DEFAULT CHARSET=utf8'))
            

        return true;
    }

    public function uninstall()
    {
        unlink(dirname(__FILE__).'/../../cache/class_index.php');
        return ( parent::uninstall() 
            AND Configuration::deleteByName('MOBIKWIKPG_MERCHANT_IDENTIFIER') 
            AND Configuration::deleteByName('MOBIKWIKPG_SECRET_KEY') 
            AND Configuration::deleteByName('GATEWAY_MODE') 
           
            );

    }


    public function getContent()
    {
        global $cookie;
        $errors = array();
        $html ='' ;

        /* Update configuration variables */
        if ( Tools::isSubmit( 'btnSubmit' ) )
        {
            
            if( $merchant_identifier =  Tools::getValue( 'mobikwikpg_merchant_identifier' ) )
            {
                 Configuration::updateValue( 'MOBIKWIKPG_MERCHANT_IDENTIFIER', $merchant_identifier );
            }

             if( $secret_key =  Tools::getValue( 'mobikwikpg_secret_key' ) )
            {
                 Configuration::updateValue( 'MOBIKWIKPG_SECRET_KEY', $secret_key );
            }


             if( $gateway_mode =  Tools::getValue( 'mobikwikpg_gateway_mode' ) )
                {
                     Configuration::updateValue( 'MOBIKWIKPG_GATEWAY_MODE', $gateway_mode );
                }

      
            if( method_exists ('Tools','clearSmartyCache') )
            {
                Tools::clearSmartyCache();
            } 
            
        }      
        
        /* Display errors */
        if (sizeof($errors))
        {
            $html .= '<ul style="color: red; font-weight: bold; margin-bottom: 30px; width: 506px; background: #FFDFDF; border: 1px dashed #BBB; padding: 10px;">';
            foreach ($errors AS $error)
                $html .= '<li>'.$error.'</li>';
            $html .= '</ul>';
        }

    /* Display settings form */
        $html .= '
        <form action="'.$_SERVER['REQUEST_URI'].'" method="post">
          <fieldset>
          <legend><img src="'.__PS_BASE_URI__.'modules/mobikwikpg/logo.png" />'.$this->l('Settings').'</legend>
            <p>'.$this->l('Use the "Test" mode to test out the module then you can use the "Live" mode if no problems arise. Remember to insert your merchant key and ID for the live mode.').'</p>
            <label>
              '.$this->l('Mode').'
            </label>
            <div class="margin-form" style="width:110px;">
              <select name="mobikwikpg_gateway_mode">
                <option value="live"'.(Configuration::get('MOBIKWIKPG_GATEWAY_MODE') == 'live' ? ' selected="selected"' : '').'>'.$this->l('Live').'&nbsp;&nbsp;</option>
                <option value="test"'.(Configuration::get('MOBIKWIKPG_GATEWAY_MODE') == 'test' ? ' selected="selected"' : '').'>'.$this->l('Test').'&nbsp;&nbsp;</option>
              </select>
            </div>

            <p>'.$this->l('You can find your Merchant Identifier and Secret Key in your Mobikwik account.').'</p>
            <label>
              '.$this->l('Merchant Identifier').'
            </label>
            <div class="margin-form">
              <input type="text" name="mobikwikpg_merchant_identifier" value="'.Tools::getValue('mobikwikpg_merchant_identifier', Configuration::get('MOBIKWIKPG_MERCHANT_IDENTIFIER')).'" />
            </div>
            <label>
              '.$this->l('Secret Key').'
            </label>
            <div class="margin-form">
              <input type="text" name="mobikwikpg_secret_key" value="'.trim(Tools::getValue('mobikwikpg_secret_key', Configuration::get('MOBIKWIKPG_SECRET_KEY'))).'" />
            </div> 

          
            <div style="float:right;"><input type="submit" name="btnSubmit" class="button" value="'.$this->l('   Save   ').'" /></div><div class="clear"></div>
          </fieldset>
        </form>
        <br /><br />
        <fieldset>
          <legend><img src="../img/admin/warning.gif" />'.$this->l('Information').'</legend>
          <p>- '.$this->l('Please provide Merchant Identifier and Secret Key').'</p>         
        </fieldset>
        </div>'; 
    
        return $html;
    }


    public function hookPayment($params)
    {   
        
        global $cookie, $cart;
      
        if (!$this->active)
        {
            return;
        }
        
        // Buyer details
        $customer = new Customer((int)($cart->id_customer));
        
         $total = $cart->getOrderTotal()*100; 
         $amount = number_format( sprintf( "%01.2f", $total ), 2, '.', '' );

        $data = array();

        $currency = 'INR';

         $deloveryAddress = new Address((int)($cart->id_address_delivery));       
         $Zipcode      =  $deloveryAddress->postcode;

         if($deloveryAddress->phone)
         {
             $phone=$deloveryAddress->phone;

         } else
         {
             $phone=$deloveryAddress->phone_mobile;

         }

          $baseUrl=Tools::getShopDomain(true, true).__PS_BASE_URI__;

          $secret_key_var = Configuration::get('MOBIKWIKPG_SECRET_KEY');
        
        if( Configuration::get('MOBIKWIKPG_GATEWAY_MODE') == 'live' )
        {

            $data['info']['merchantIdentifier'] = Configuration::get('MOBIKWIKPG_MERCHANT_IDENTIFIER');
            $secret_key_var = Configuration::get('MOBIKWIKPG_SECRET_KEY');
           
            $data['mobikwikpg_url'] = 'https://api.zaakpay.com/api/paymentTransact/V8';

        }
     
        else
        {

            $orderId = 'ZPpresta'.rand(200,3000).'-'.$cart->id;

            $data['info']['merchantIdentifier'] = self::SANDBOX_MERCHANT_IDENTIFIER; 

            $secret_key_var = self::SANDBOX_SECRET_KEY; 

            $data['mobikwikpg_url'] = 'http://zaakpaystaging.centralindia.cloudapp.azure.com:8080/api/paymentTransact/V8';

        }

         $surl=$baseUrl.'modules/'.$this->name.'/success.php'
         ; 
        $data['info']['orderId'] = $cart->id;   
        $data['info']['buyerFirstName'] = $customer->firstname;
        $data['info']['buyerLastName'] = $customer->lastname;
        $data['info']['buyerEmail'] = $customer->email;
        $data['info']['buyerPincode'] = $Zipcode ;
        $data['info']['buyerPhoneNumber'] = $phone;
        
        $data['info']['currency'] = 'INR';
        $data['info']['returnUrl'] = $surl;
        $data['info']['amount'] = $total;
        $data['info']['productDescription'] = Configuration::get('PS_SHOP_NAME') .' purchase, Cart Item ID #'. $cart->id;

        $all = '';

        $checksumsequence= array("amount","bankid","buyerAddress",
           "buyerCity","buyerCountry","buyerEmail","buyerFirstName","buyerLastName","buyerPhoneNumber","buyerPincode",
           "buyerState","currency","debitorcredit","merchantIdentifier","merchantIpAddress","mode","orderId",
           "product1Description","product2Description","product3Description","product4Description",
           "productDescription","productInfo","purpose","returnUrl","shipToAddress","shipToCity","shipToCountry",
           "shipToFirstname","shipToLastname","shipToPhoneNumber","shipToPincode","shipToState","showMobile","txnDate",
           "txnType","zpPayOption");

    foreach($checksumsequence as $seqvalue) {
      if(array_key_exists($seqvalue,$data['info'])) {

        if(!$data['info'][$seqvalue]=="")
        {

          if($seqvalue != 'checksum')
          {
            $all .= $seqvalue;
            $all .="=";
            $all .= $data['info'][$seqvalue];
            $all .= "&";
          }
        }

      }
    }
    $checksum = hash_hmac('sha256', $all , $secret_key_var);

        $data['info']['checksum'] = $checksum;
        $this->context->smarty->assign( 'data', $data );   
        return $this->display(__FILE__, 'mobikwikpg.tpl'); 
    }

    public function hookPaymentReturn($params)
    {
        if (!$this->active)
        {
            return;
        }
        $test = __FILE__;
        return $this->display($test, 'mobikwikpg_success.tpl');
    }
   
}


