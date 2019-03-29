<?php
class ControllerExtensionPaymentCustom extends Controller {
    
  public function index() {
      
    $this->load->language('extension/payment/custom');
    $data['button_confirm'] = $this->language->get('button_confirm');
    
      $root = "http://".$_SERVER['HTTP_HOST'].str_replace(basename($_SERVER['SCRIPT_NAME']),"",$_SERVER['SCRIPT_NAME']).'catalog/controller/extension/payment';
      $data['action'] = $this->url->link('extension/payment/custom/worldPayRequest');
      
      //$data['action'] = $this->url->link('extension/payment/custom/worldPayRequest');
  
    $this->load->model('checkout/order');
    $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
    
        //meTrnStausSuccess.php
        
        $this->config->set('mid', 'WL0000000027698');
        $this->config->set('enckey', '6375b97b954b37f956966977e5753ee6');
        $this->config->set('mode', 'test');//production
        /*$this->config->set($key, $value);
        $this->config->set($key, $value);*/
        
    if ($order_info) {
        
      $data['mid']              = trim($this->config->get('mid')); 
      $data['enckey']           = trim($this->config->get('enckey'));
      $data['orderid']          = date('His') . $this->session->data['order_id'];
      $data['callbackurl']      = $this->url->link('extension/payment/custom/status');
      $data['mode']             = $this->config->get('mode');
      //$data['callbackurl']      = $root.'/meTrnSuccess.php';
      $data['orderdate']        = date('YmdHis');
      $data['currency']         = $order_info['currency_code'];
      $data['orderamount']      = $this->currency->format($order_info['total'], $data['currency'] , false, false);
      $data['billemail']        = $order_info['email'];
      $data['billphone']        = html_entity_decode($order_info['telephone'], ENT_QUOTES, 'UTF-8');
      $data['billaddress']      = html_entity_decode($order_info['payment_address_1'], ENT_QUOTES, 'UTF-8');
      $data['billcountry']      = html_entity_decode($order_info['payment_iso_code_2'], ENT_QUOTES, 'UTF-8');
      $data['billprovince']     = html_entity_decode($order_info['payment_zone'], ENT_QUOTES, 'UTF-8');;
      $data['billcity']         = html_entity_decode($order_info['payment_city'], ENT_QUOTES, 'UTF-8');
      $data['billpost']         = html_entity_decode($order_info['payment_postcode'], ENT_QUOTES, 'UTF-8');
      $data['deliveryname']     = html_entity_decode($order_info['shipping_firstname'] . $order_info['shipping_lastname'], ENT_QUOTES, 'UTF-8');
      $data['deliveryaddress']  = html_entity_decode($order_info['shipping_address_1'], ENT_QUOTES, 'UTF-8');
      $data['deliverycity']     = html_entity_decode($order_info['shipping_city'], ENT_QUOTES, 'UTF-8');
      $data['deliverycountry']  = html_entity_decode($order_info['shipping_iso_code_2'], ENT_QUOTES, 'UTF-8');
      $data['deliveryprovince'] = html_entity_decode($order_info['shipping_zone'], ENT_QUOTES, 'UTF-8');
      $data['deliveryemail']    = $order_info['email'];
      $data['deliveryphone']    = html_entity_decode($order_info['telephone'], ENT_QUOTES, 'UTF-8');
      $data['deliverypost']     = html_entity_decode($order_info['shipping_postcode'], ENT_QUOTES, 'UTF-8');
      
      if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/extension/payment/custom')){
          
        $this->template = $this->config->get('config_template') . '/extension/payment/custom';
        
      } else {
          
        $this->template = 'default/template/extension/payment/custom';
      }

		return $this->load->view('extension/payment/custom', $data);
		
    }
    
  }
  
  public function worldPayRequest() {
      
        $this->config->set('test_action', 'https://cgt.in.worldline.com/ipg/doMEPayRequest');
        $this->config->set('production_action', 'https://ipg.in.worldline.com/doMEPayRequest');
      
    include 'AWLMEAPI.php';
      
    //create an Object of the above included class
	$obj = new AWLMEAPI();

	//create an object of Request Message
	$reqMsgDTO = new ReqMsgDTO();

	/* Populate the above DTO Object On the Basis Of The Received Values */
	// PG MID
	$reqMsgDTO->setMid($_REQUEST['mid']);
	// Merchant Unique order id
	$reqMsgDTO->setOrderId($_REQUEST['OrderId']);
	//Transaction amount in paisa format
	$reqMsgDTO->setTrnAmt($_REQUEST['amount']);
	//Transaction remarks
	$reqMsgDTO->setTrnRemarks("This txn has to be done ");
	// Merchant transaction type (S/P/R)
	$reqMsgDTO->setMeTransReqType($_REQUEST['meTransReqType']);
	// Merchant encryption key
	$reqMsgDTO->setEnckey($_REQUEST['enckey']);
	// Merchant transaction currency
	$reqMsgDTO->setTrnCurrency($_REQUEST['currencyName']);
	// Recurring period, if merchant transaction type is R
	$reqMsgDTO->setRecurrPeriod($_REQUEST['recurPeriod']);
	// Recurring day, if merchant transaction type is R
	$reqMsgDTO->setRecurrDay($_REQUEST['recurDay']);
	// No of recurring, if merchant transaction type is R
	$reqMsgDTO->setNoOfRecurring($_REQUEST['numberRecurring']);
	// Merchant response URl
	$reqMsgDTO->setResponseUrl($_REQUEST['responseUrl']);
	// Optional additional fields for merchant
	$reqMsgDTO->setAddField1($_REQUEST['addField1']);
	$reqMsgDTO->setAddField2($_REQUEST['addField2']);
	$reqMsgDTO->setAddField3($_REQUEST['addField3']);
	$reqMsgDTO->setAddField4($_REQUEST['addField4']);
	$reqMsgDTO->setAddField5($_REQUEST['addField5']);
	$reqMsgDTO->setAddField6($_REQUEST['addField6']);
	$reqMsgDTO->setAddField7($_REQUEST['addField7']);
	$reqMsgDTO->setAddField8($_REQUEST['addField8']);
	
	/* 
	 * After Making Request Message Send It To Generate Request 
	 * The variable `$urlParameter` contains encrypted request message
	 */
	 //Generate transaction request message
	$merchantRequest = "";
	
	$reqMsgDTO = $obj->generateTrnReqMsg($reqMsgDTO);
	
	if ($reqMsgDTO->getStatusDesc() == "Success"){
	    
		$merchantRequest = $reqMsgDTO->getReqMsg();
	}
	
	if($_REQUEST['mod'] == 'production'){
	   
	    $action = $this->config->get('production_action');
	    
	}else{
	    
	    $action = $this->config->get('test_action');
	    
	}
	
?>

        <!--live: https://ipg.in.worldline.com/doMEPayRequest  test: https://cgt.in.worldline.com/ipg/doMEPayRequest-->
        
        <form action="<?php echo $action;?>" method="post" name="txnSubmitFrm">
        	<h4 align="center">Redirecting To Payment Please Wait..</h4>
        	<h4 align="center">Please Do Not Press Back Button OR Refresh Page</h4>
        	<input type="hidden" size="200" name="merchantRequest" id="merchantRequest" value="<?php echo $merchantRequest; ?>"  />
        	<input type="hidden" name="MID" id="MID" value="<?php echo $reqMsgDTO->getMid(); ?>"/>
        </form>
        
        <script  type="text/javascript">
        	//submit the form to the worldline
        	document.txnSubmitFrm.submit();
        </script>

<?php
      
	}
      
  
  public function status(){
      
      /**
	 * This Is the Kit File To Be included For Transaction Request/Response
	 */
	include 'AWLMEAPI.php';
	
	//create an Object of the above included class
	$obj = new AWLMEAPI();
	
	/* This is the response Object */
	$resMsgDTO = new ResMsgDTO();

	/* This is the request Object */
	$reqMsgDTO = new ReqMsgDTO();
	
	//This is the Merchant Key that is used for decryption also
	$enc_key = "6375b97b954b37f956966977e5753ee6";
	
	/* Get the Response from the WorldLine */
	$responseMerchant = $_REQUEST['merchantResponse'];
	
	$response = $obj->parseTrnResMsg( $responseMerchant , $enc_key );
	
	$order_data['getPgMeTrnRefNo']  = $response->getPgMeTrnRefNo();
	$order_data['getOrderId']       = $response->getOrderId();
	$order_data['getTrnAmt']        = $response->getTrnAmt();
	$order_data['getStatusCode']    = $response->getStatusCode();
	$order_data['getStatusDesc']    = $response->getStatusDesc();
	$order_data['getTrnReqDate']    = $response->getTrnReqDate();
	$order_data['getResponseCode']  = $response->getResponseCode();
	$order_data['getRrn']           = $response->getRrn();
	$order_data['getAuthZCode']     = $response->getAuthZCode();
	$order_data['getAddField1']     = $response->getAddField1();
	$order_data['getAddField2']     = $response->getAddField2();
	
	//$this->load->model('extension/payment/custom');
	$this->load->model('checkout/order');
	
	$order_id = substr(trim($order_data['getOrderId']),6);
	
	if ($response->getStatusCode() == 's' || $response->getStatusCode() == 'S')
			{
				$this->model_checkout_order->addOrderHistory($order_id, 1, $order_data['getStatusDesc'].'('.$order_data['getOrderId'].')', true);
				
				if($this->session) $this->session->data['success'] = "WorldPay Response - ".$order_data['getStatusDesc'];
				
				$data['success'] = $this->session->data['success'];
				
				//if($this->response) $this->response->redirect($this->url->link('checkout/success', '', 'SSL'));				
			}
			elseif ($response->getStatusCode() == 'f' || $response->getStatusCode() == 'F') 
			{
				$this->model_checkout_order->addOrderHistory($order_id, 0, $order_data['getStatusDesc'], true);
				
				if($this->session) $this->session->data['error'] = "WorldPay Response - ".$order_data['getStatusDesc'].$response->getStatusCode();
				
				if($this->response) $this->response->redirect($this->url->link('checkout/checkout', '', 'SSL'));
				
			}else //signature mismatch
    		{
    		    //$this->model_checkout_order->addOrderHistory($order_data['getOrderId'], 0, $order_data['getStatusDesc'], true);
    		    
    			if($this->session) $this->session->data['error'] = "Invalid or forged transactiond...".$order_data['getStatusDesc'];		//forged 
    			
    			if($this->response) $this->response->redirect($this->url->link('checkout/checkout', '', 'SSL'));
    		}
	
	
	//return;
	
	
      	$redirect = '';

	/*	if ($this->cart->hasShipping()) {
			// Validate if shipping address has been set.
			if (!isset($this->session->data['shipping_address'])) {
				$redirect = $this->url->link('checkout/checkout', '', true);
			}

			// Validate if shipping method has been set.
			if (!isset($this->session->data['shipping_method'])) {
				$redirect = $this->url->link('checkout/checkout', '', true);
			}
			
		} else {
			unset($this->session->data['shipping_address']);
			unset($this->session->data['shipping_method']);
			unset($this->session->data['shipping_methods']);
		}

		// Validate if payment address has been set.
		if (!isset($this->session->data['payment_address'])) {
			$redirect = $this->url->link('checkout/checkout', '', true);
		}

		// Validate if payment method has been set.
		if (!isset($this->session->data['payment_method'])) {
			$redirect = $this->url->link('checkout/checkout', '', true);
		}

		// Validate cart has products and has stock.
		if ((!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))) {
			$redirect = $this->url->link('checkout/cart');
		}

		// Validate minimum quantity requirements.
		$products = $this->cart->getProducts();

		foreach ($products as $product) {
			$product_total = 0;

			foreach ($products as $product_2) {
				if ($product_2['product_id'] == $product['product_id']) {
					$product_total += $product_2['quantity'];
				}
			}

			if ($product['minimum'] > $product_total) {
				$redirect = $this->url->link('checkout/cart');

				break;
			}
		}

		if (!$redirect) {
			$order_data = array();

			$totals = array();
			$taxes = $this->cart->getTaxes();
			$total = 0;

			// Because __call can not keep var references so we put them into an array.
			$total_data = array(
				'totals' => &$totals,
				'taxes'  => &$taxes,
				'total'  => &$total
			);

			$this->load->model('setting/extension');

			$sort_order = array();

			$results = $this->model_setting_extension->getExtensions('total');

			foreach ($results as $key => $value) {
				$sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
			}

			array_multisort($sort_order, SORT_ASC, $results);

			foreach ($results as $result) {
				if ($this->config->get('total_' . $result['code'] . '_status')) {
					$this->load->model('extension/total/' . $result['code']);

					// We have to put the totals in an array so that they pass by reference.
					$this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
				}
			}

			$sort_order = array();

			foreach ($totals as $key => $value) {
				$sort_order[$key] = $value['sort_order'];
			}

			array_multisort($sort_order, SORT_ASC, $totals);

			$order_data['totals'] = $totals;

			$this->load->language('checkout/checkout');

			$order_data['invoice_prefix'] = $this->config->get('config_invoice_prefix');
			$order_data['store_id'] = $this->config->get('config_store_id');
			$order_data['store_name'] = $this->config->get('config_name');

			if ($order_data['store_id']) {
				$order_data['store_url'] = $this->config->get('config_url');
			} else {
				if ($this->request->server['HTTPS']) {
					$order_data['store_url'] = HTTPS_SERVER;
				} else {
					$order_data['store_url'] = HTTP_SERVER;
				}
			}
			
			$this->load->model('account/customer');

			if ($this->customer->isLogged()) {
				$customer_info = $this->model_account_customer->getCustomer($this->customer->getId());

				$order_data['customer_id'] = $this->customer->getId();
				$order_data['customer_group_id'] = $customer_info['customer_group_id'];
				$order_data['firstname'] = $customer_info['firstname'];
				$order_data['lastname'] = $customer_info['lastname'];
				$order_data['email'] = $customer_info['email'];
				$order_data['telephone'] = $customer_info['telephone'];
				$order_data['custom_field'] = json_decode($customer_info['custom_field'], true);
			} elseif (isset($this->session->data['guest'])) {
				$order_data['customer_id'] = 0;
				$order_data['customer_group_id'] = $this->session->data['guest']['customer_group_id'];
				$order_data['firstname'] = $this->session->data['guest']['firstname'];
				$order_data['lastname'] = $this->session->data['guest']['lastname'];
				$order_data['email'] = $this->session->data['guest']['email'];
				$order_data['telephone'] = $this->session->data['guest']['telephone'];
				$order_data['custom_field'] = $this->session->data['guest']['custom_field'];
			}

			$order_data['payment_firstname'] = $this->session->data['payment_address']['firstname'];
			$order_data['payment_lastname'] = $this->session->data['payment_address']['lastname'];
			$order_data['payment_company'] = $this->session->data['payment_address']['company'];
			$order_data['payment_address_1'] = $this->session->data['payment_address']['address_1'];
			$order_data['payment_address_2'] = $this->session->data['payment_address']['address_2'];
			$order_data['payment_city'] = $this->session->data['payment_address']['city'];
			$order_data['payment_postcode'] = $this->session->data['payment_address']['postcode'];
			$order_data['payment_zone'] = $this->session->data['payment_address']['zone'];
			$order_data['payment_zone_id'] = $this->session->data['payment_address']['zone_id'];
			$order_data['payment_country'] = $this->session->data['payment_address']['country'];
			$order_data['payment_country_id'] = $this->session->data['payment_address']['country_id'];
			$order_data['payment_address_format'] = $this->session->data['payment_address']['address_format'];
			$order_data['payment_custom_field'] = (isset($this->session->data['payment_address']['custom_field']) ? $this->session->data['payment_address']['custom_field'] : array());

			if (isset($this->session->data['payment_method']['title'])) {
				$order_data['payment_method'] = $this->session->data['payment_method']['title'];
			} else {
				$order_data['payment_method'] = '';
			}

			if (isset($this->session->data['payment_method']['code'])) {
				$order_data['payment_code'] = $this->session->data['payment_method']['code'];
			} else {
				$order_data['payment_code'] = '';
			}

			if ($this->cart->hasShipping()) {
				$order_data['shipping_firstname'] = $this->session->data['shipping_address']['firstname'];
				$order_data['shipping_lastname'] = $this->session->data['shipping_address']['lastname'];
				$order_data['shipping_company'] = $this->session->data['shipping_address']['company'];
				$order_data['shipping_address_1'] = $this->session->data['shipping_address']['address_1'];
				$order_data['shipping_address_2'] = $this->session->data['shipping_address']['address_2'];
				$order_data['shipping_city'] = $this->session->data['shipping_address']['city'];
				$order_data['shipping_postcode'] = $this->session->data['shipping_address']['postcode'];
				$order_data['shipping_zone'] = $this->session->data['shipping_address']['zone'];
				$order_data['shipping_zone_id'] = $this->session->data['shipping_address']['zone_id'];
				$order_data['shipping_country'] = $this->session->data['shipping_address']['country'];
				$order_data['shipping_country_id'] = $this->session->data['shipping_address']['country_id'];
				$order_data['shipping_address_format'] = $this->session->data['shipping_address']['address_format'];
				$order_data['shipping_custom_field'] = (isset($this->session->data['shipping_address']['custom_field']) ? $this->session->data['shipping_address']['custom_field'] : array());

				if (isset($this->session->data['shipping_method']['title'])) {
					$order_data['shipping_method'] = $this->session->data['shipping_method']['title'];
				} else {
					$order_data['shipping_method'] = '';
				}

				if (isset($this->session->data['shipping_method']['code'])) {
					$order_data['shipping_code'] = $this->session->data['shipping_method']['code'];
				} else {
					$order_data['shipping_code'] = '';
				}
			} else {
				$order_data['shipping_firstname'] = '';
				$order_data['shipping_lastname'] = '';
				$order_data['shipping_company'] = '';
				$order_data['shipping_address_1'] = '';
				$order_data['shipping_address_2'] = '';
				$order_data['shipping_city'] = '';
				$order_data['shipping_postcode'] = '';
				$order_data['shipping_zone'] = '';
				$order_data['shipping_zone_id'] = '';
				$order_data['shipping_country'] = '';
				$order_data['shipping_country_id'] = '';
				$order_data['shipping_address_format'] = '';
				$order_data['shipping_custom_field'] = array();
				$order_data['shipping_method'] = '';
				$order_data['shipping_code'] = '';
			}

			$order_data['products'] = array();

			foreach ($this->cart->getProducts() as $product) {
				$option_data = array();

				foreach ($product['option'] as $option) {
					$option_data[] = array(
						'product_option_id'       => $option['product_option_id'],
						'product_option_value_id' => $option['product_option_value_id'],
						'option_id'               => $option['option_id'],
						'option_value_id'         => $option['option_value_id'],
						'name'                    => $option['name'],
						'value'                   => $option['value'],
						'type'                    => $option['type']
					);
				}

				$order_data['products'][] = array(
					'product_id' => $product['product_id'],
					'name'       => $product['name'],
					'model'      => $product['model'],
					'option'     => $option_data,
					'download'   => $product['download'],
					'quantity'   => $product['quantity'],
					'subtract'   => $product['subtract'],
					'price'      => $product['price'],
					'total'      => $product['total'],
					'tax'        => $this->tax->getTax($product['price'], $product['tax_class_id']),
					'reward'     => $product['reward']
				);
			}

			// Gift Voucher
			$order_data['vouchers'] = array();

			if (!empty($this->session->data['vouchers'])) {
				foreach ($this->session->data['vouchers'] as $voucher) {
					$order_data['vouchers'][] = array(
						'description'      => $voucher['description'],
						'code'             => token(10),
						'to_name'          => $voucher['to_name'],
						'to_email'         => $voucher['to_email'],
						'from_name'        => $voucher['from_name'],
						'from_email'       => $voucher['from_email'],
						'voucher_theme_id' => $voucher['voucher_theme_id'],
						'message'          => $voucher['message'],
						'amount'           => $voucher['amount']
					);
				}
			}

			$order_data['comment'] = $this->session->data['comment'];
			$order_data['total'] = $total_data['total'];

			if (isset($this->request->cookie['tracking'])) {
				$order_data['tracking'] = $this->request->cookie['tracking'];

				$subtotal = $this->cart->getSubTotal();

				// Affiliate
				$affiliate_info = $this->model_account_customer->getAffiliateByTracking($this->request->cookie['tracking']);

				if ($affiliate_info) {
					$order_data['affiliate_id'] = $affiliate_info['customer_id'];
					$order_data['commission'] = ($subtotal / 100) * $affiliate_info['commission'];
				} else {
					$order_data['affiliate_id'] = 0;
					$order_data['commission'] = 0;
				}

				// Marketing
				$this->load->model('checkout/marketing');

				$marketing_info = $this->model_checkout_marketing->getMarketingByCode($this->request->cookie['tracking']);

				if ($marketing_info) {
					$order_data['marketing_id'] = $marketing_info['marketing_id'];
				} else {
					$order_data['marketing_id'] = 0;
				}
				
			} else {
			    
				$order_data['affiliate_id'] = 0;
				$order_data['commission'] = 0;
				$order_data['marketing_id'] = 0;
				$order_data['tracking'] = '';
			}

			$order_data['language_id'] = $this->config->get('config_language_id');
			$order_data['currency_id'] = $this->currency->getId($this->session->data['currency']);
			$order_data['currency_code'] = $this->session->data['currency'];
			$order_data['currency_value'] = $this->currency->getValue($this->session->data['currency']);
			$order_data['ip'] = $this->request->server['REMOTE_ADDR'];

			if (!empty($this->request->server['HTTP_X_FORWARDED_FOR'])) {
				$order_data['forwarded_ip'] = $this->request->server['HTTP_X_FORWARDED_FOR'];
			} elseif (!empty($this->request->server['HTTP_CLIENT_IP'])) {
				$order_data['forwarded_ip'] = $this->request->server['HTTP_CLIENT_IP'];
			} else {
				$order_data['forwarded_ip'] = '';
			}

			if (isset($this->request->server['HTTP_USER_AGENT'])) {
				$order_data['user_agent'] = $this->request->server['HTTP_USER_AGENT'];
			} else {
				$order_data['user_agent'] = '';
			}

			if (isset($this->request->server['HTTP_ACCEPT_LANGUAGE'])) {
				$order_data['accept_language'] = $this->request->server['HTTP_ACCEPT_LANGUAGE'];
			} else {
				$order_data['accept_language'] = '';
			}

			$this->load->model('checkout/order');

			$this->session->data['order_id'] = $this->model_checkout_order->addOrder($order_data);

			$this->load->model('tool/upload');

			$data['products'] = array();

			foreach ($this->cart->getProducts() as $product) {
			    
				$option_data = array();

				foreach ($product['option'] as $option) {
					if ($option['type'] != 'file') {
						$value = $option['value'];
					} else {
						$upload_info = $this->model_tool_upload->getUploadByCode($option['value']);

						if ($upload_info) {
							$value = $upload_info['name'];
						} else {
							$value = '';
						}
					}

					$option_data[] = array(
						'name'  => $option['name'],
						'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value)
					);
				}

				$recurring = '';

				if ($product['recurring']) {
					$frequencies = array(
						'day'        => $this->language->get('text_day'),
						'week'       => $this->language->get('text_week'),
						'semi_month' => $this->language->get('text_semi_month'),
						'month'      => $this->language->get('text_month'),
						'year'       => $this->language->get('text_year'),
					);

					if ($product['recurring']['trial']) {
						$recurring = sprintf($this->language->get('text_trial_description'), $this->currency->format($this->tax->calculate($product['recurring']['trial_price'] * $product['quantity'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']), $product['recurring']['trial_cycle'], $frequencies[$product['recurring']['trial_frequency']], $product['recurring']['trial_duration']) . ' ';
					}

					if ($product['recurring']['duration']) {
						$recurring .= sprintf($this->language->get('text_payment_description'), $this->currency->format($this->tax->calculate($product['recurring']['price'] * $product['quantity'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']), $product['recurring']['cycle'], $frequencies[$product['recurring']['frequency']], $product['recurring']['duration']);
					} else {
						$recurring .= sprintf($this->language->get('text_payment_cancel'), $this->currency->format($this->tax->calculate($product['recurring']['price'] * $product['quantity'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']), $product['recurring']['cycle'], $frequencies[$product['recurring']['frequency']], $product['recurring']['duration']);
					}
				}

				$data['products'][] = array(
					'cart_id'    => $product['cart_id'],
					'product_id' => $product['product_id'],
					'name'       => $product['name'],
					'model'      => $product['model'],
					'option'     => $option_data,
					'recurring'  => $recurring,
					'quantity'   => $product['quantity'],
					'subtract'   => $product['subtract'],
					'price'      => $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']),
					'total'      => $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')) * $product['quantity'], $this->session->data['currency']),
					'href'       => $this->url->link('product/product', 'product_id=' . $product['product_id'])
				);
			}

			// Gift Voucher
			$data['vouchers'] = array();

			if (!empty($this->session->data['vouchers'])) {
				foreach ($this->session->data['vouchers'] as $voucher) {
					$data['vouchers'][] = array(
						'description' => $voucher['description'],
						'amount'      => $this->currency->format($voucher['amount'], $this->session->data['currency'])
					);
				}
			}

			$data['totals'] = array();

			foreach ($order_data['totals'] as $total){
			    
				$data['totals'][] = array(
					'title' => $total['title'],
					'text'  => $this->currency->format($total['value'], $this->session->data['currency'])
				);
			}
			

			$data['payment'] = $this->load->controller('extension/payment/' . $this->session->data['payment_method']['code']);
			
			//print_r($data['payment']); cybro
			
		} else {
		    
			$data['redirect'] = $redirect;
		}
		
		*/
		
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');
		
		$data['myaccount'] = $this->url->link('account/account');
		$data['mydownloads'] = $this->url->link('account/download');
		$data['contactinformation'] = $this->url->link('information/contact');
		$data['commonhome'] = $this->url->link('common/home');
		$data['myorders'] = $this->url->link('account/order');

		$this->response->setOutput($this->load->view('extension/payment/worldlinepaymentstaus', $data));
		
  }
  
  
  public function callbacks(){
      
      echo 'test function';
  }
  
}
?>