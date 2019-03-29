<?php class Controllerextensionpaymentcustom extends Controller {
  private $error = array();
 
  public function index() {
    $this->load->language('extension/payment/custom');
    $this->document->setTitle('Custom Payment Method Configuration');
    $this->load->model('setting/setting');
 
    if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
       // print_r($this->request->post);
      $this->model_setting_setting->editSetting('custom', $this->request->post);
      $this->session->data['success'] = 'Saved.';
      $this->response->redirect($this->url->link('extension/payment/custom', 'user_token=' . $this->session->data['user_token'], true));
    }
	
    $data['heading_title'] = $this->language->get('heading_title');
    $data['entry_text_config_one'] = $this->language->get('text_config_one');
    $data['entry_text_config_two'] = $this->language->get('text_config_two');
    $data['button_save'] = $this->language->get('text_button_save');
    $data['button_cancel'] = $this->language->get('text_button_cancel');
    $data['entry_order_status'] = $this->language->get('entry_order_status');
    $data['text_enabled'] = $this->language->get('text_enabled');
    $data['text_disabled'] = $this->language->get('text_disabled');
    $data['entry_status'] = $this->language->get('entry_status');
 
    $data['action'] = $this->url->link('extension/payment/custom', 'user_token=' . $this->session->data['user_token'], true);
    $data['cancel'] = $this->url->link('extension/payment', 'user_token=' . $this->session->data['user_token'], true);
 
    if (isset($this->request->post['text_config_one'])) {
      $data['text_config_one'] = $this->request->post['text_config_one'];
    } else {
      $data['text_config_one'] = $this->config->get('text_config_one');
    }
        
    if (isset($this->request->post['text_config_two'])) {
      $data['text_config_two'] = $this->request->post['text_config_two'];
    } else {
      $data['text_config_two'] = $this->config->get('text_config_two');
    }
            
    if (isset($this->request->post['custom_status'])) {
      $data['custom_status'] = $this->request->post['custom_status'];
    } else {
      $data['custom_status'] = $this->config->get('custom_status');
    }
        
    if (isset($this->request->post['custom_order_status_id'])) {
      $data['custom_order_status_id'] = $this->request->post['custom_order_status_id'];
    } else {
      $data['custom_order_status_id'] = $this->config->get('custom_order_status_id');
    }
 
    $this->load->model('localisation/order_status');
    $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
    $this->template = 'payment/customtpl';
    
    $data['header'] = $this->load->controller('common/header');
	$data['column_left'] = $this->load->controller('common/column_left');
	$data['footer'] = $this->load->controller('common/footer');
        
            
    $this->children = array(
      'common/header',
      'common/footer'
    );
 
    //$this->response->setOutput($this->render());twig
        //$this->response->setOutput($this->load->view('common/header', $data));
	    $this->response->setOutput($this->load->view('extension/payment/customtpl', $data));
	    //$this->response->setOutput($this->load->view('common/footer', $data));
  }
}