<?php
class ControllerExtensionModuleOpcLogin extends Controller {
	public function index() {
		$data = $this->load->language('extension/module/opc_login');

		$this->load->model('tool/image');

		$data['logged'] = $this->customer->isLogged();

		$client_id = $this->config->get('module_opc_login_google_key');

		$redirect_url = $this->url->link('account/opc_login/google', '', true);

		$data['google'] = '';

		$data['google_image'] = $this->model_tool_image->resize('google.png', 200, 50);

		if ($client_id) {
				$data['google'] = 'https://accounts.google.com/o/oauth2/v2/auth?redirect_uri=' . $redirect_url . '&prompt=consent&response_type=code&client_id=' . $client_id . '&scope=https://www.googleapis.com/auth/userinfo.email&access_type=offline';
		}

		$data['facebook'] = $this->config->get('module_opc_login_facebook_key');

		return $this->load->view('extension/module/opc_login', $data);
	}
}
