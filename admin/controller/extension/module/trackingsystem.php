<?php
class ControllerExtensionModuleTrackingsystem extends Controller {
	private $error = array(); 
	
	public function index(){
		
		$this->load->language('extension/module/trackingsystem');

		$this->document->setTitle($this->language->get('heading_title1'));
		
		$this->load->model('setting/setting');
		
		$this->load->model('extension/trackingsystem');
		
		$this->model_extension_trackingsystem->CreateColumn();
				
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()){
			
			$this->model_setting_setting->editSetting('module_trackingsystem', $this->request->post);
			
			$this->session->data['success'] = $this->language->get('text_success');
			
			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}
				
		$data['heading_title'] = $this->language->get('heading_title1');
		
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_comment'] = $this->language->get('entry_comment');
		$data['entry_courier'] = $this->language->get('entry_courier');
		$data['entry_courier_link'] = $this->language->get('entry_link');
		$data['entry_notify'] = $this->language->get('entry_notify');
		$data['tab_setting'] = $this->language->get('tab_setting');
		$data['tab_data'] = $this->language->get('tab_data');
		
		$data['help_order_status'] = $this->language->get('help_order_status');
		$data['button_save'] = $this->language->get('button_save');
		$data['tracking_edit'] = $this->language->get('tracking_edit');
		$data['button_cancel'] = $this->language->get('button_cancel');
		$data['button_add'] = $this->language->get('button_add');
		$data['button_remove'] = $this->language->get('button_remove');
		
 		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}
		
		if (isset($this->error['code'])) {
			$data['error_code'] = $this->error['code'];
		} else {
			$data['error_code'] = array();
		}
				
  		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
		);

		if (!isset($this->request->get['module_id'])) {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/trackingsystem', 'user_token=' . $this->session->data['user_token'], true)
			);
		} else {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/trackingsystem', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true)
			);
		}

		if (!isset($this->request->get['module_id'])) {
			$data['action'] = $this->url->link('extension/module/trackingsystem', 'user_token=' . $this->session->data['user_token'], true);
		} else {
			$data['action'] = $this->url->link('extension/module/trackingsystem', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true);
		}

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);
		
		$data['user_token'] = $this->session->data['user_token'];
		
		if (isset($this->request->post['module_trackingsystem_status'])) {
			$data['module_trackingsystem_status'] = $this->request->post['module_trackingsystem_status'];
		} else {
			$data['module_trackingsystem_status'] = $this->config->get('module_trackingsystem_status');
		}
		
		if (isset($this->request->post['module_trackingsystem_notify'])) {
			$data['module_trackingsystem_notify'] = $this->request->post['module_trackingsystem_notify'];
		} else {
			$data['module_trackingsystem_notify'] = $this->config->get('module_trackingsystem_notify');
		}
		
		if (isset($this->request->post['module_trackingsystem_comment'])) {
			$data['module_trackingsystem_comment'] = $this->request->post['module_trackingsystem_comment'];
		} elseif ($this->config->has('module_trackingsystem_comment')) {
			$data['module_trackingsystem_comment'] = $this->config->get('module_trackingsystem_comment');
		} else {
			$data['module_trackingsystem_comment'] = '';
		}
		
		if (isset($this->request->post['module_trackingsystem_setting'])){
			$data['module_trackingsystem_settings'] = $this->request->post['module_trackingsystem_setting'];
		} elseif ($this->config->get('module_trackingsystem_setting')) { 
			$data['module_trackingsystem_settings'] = $this->config->get('module_trackingsystem_setting');
		}else{
			$data['module_trackingsystem_settings'] = array();
		}
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/trackingsystem', $data));
	}
	
	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/trackingsystem')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		if (isset($this->request->post['module_trackingsystem_setting'])) 
		{
			foreach ($this->request->post['module_trackingsystem_setting'] as $key => $value) 
			{
				if (!$value['code']) 
				{
					$this->error['code'][$key] = $this->language->get('error_code');
				}
			}
		}
		
		return !$this->error;
	}
}
?>