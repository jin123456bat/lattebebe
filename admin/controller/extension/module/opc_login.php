<?php
class ControllerExtensionModuleOpcLogin extends Controller {
	private $error = array();

	public function index() {
		$data = $this->load->language('extension/module/opc_login');

		if ($this->request->post) {
			$this->request->post = array_map('trim', $this->request->post);
		}

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		$this->load->model('setting/store');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {

			$this->model_setting_setting->editSetting('module_opc_login', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}

		$opc_error = array(
			'warning',
			'google_key',
			'google_secret',
			'facebook_key',
		);

		foreach ($opc_error as $key => $value) {
			if (isset($this->error[$value])) {
				$data['error_'.$value] = $this->error[$value];
			} else {
				$data['error_'.$value] = '';
			}
		}

		$data['stores'] = array();

		$data['stores'][] = array(
			'id' => 0,
			'name' => $this->config->get('config_name')
		);

		$stores = $this->model_setting_store->getStores();

		if ($stores) {
			foreach ($stores as $key => $store) {
				$data['stores'][] = array(
					'id' => $store['store_id'],
					'name' => $store['name']
				);
			}
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

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/opc_login', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/module/opc_login', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

    $opc_module_config = array(
			'status',
			'store',
			'google_key',
			'google_secret',
			'facebook_key',
		);

    foreach ($opc_module_config as $key => $value) {
      if (isset($this->request->post['module_opc_login_'.$value])) {
  			$data['module_opc_login_'.$value] = $this->request->post['module_opc_login_'.$value];
  		} else {
  			$data['module_opc_login_'.$value] = $this->config->get('module_opc_login_'.$value);
  		}
    }

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/opc_login', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/opc_login')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		$opc_error = array(
			'google_key',
			'google_secret',
			'facebook_key',
		);

		foreach ($opc_error as $key => $value) {
			if (!isset($this->request->post['module_opc_login_'.$value]) || !$this->request->post['module_opc_login_'.$value]) {
				$this->error[$value] = $this->language->get('error_'.$value);
			}
		}

		return !$this->error;
	}
}
