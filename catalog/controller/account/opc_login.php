<?php
class ControllerAccountOpcLogin extends Controller {
  public function google() {
		if (isset($this->request->get['code']) && $this->request->get['code']) {
			$post_array = array(
	      'code' => $this->request->get['code'],
	      'redirect_uri' => $this->url->link('account/opc_login/google', '', true),
	      'client_id' => $this->config->get('module_opc_login_google_key'),
	      'client_secret' => $this->config->get('module_opc_login_google_secret'),
				'grant_type' => 'authorization_code',
	    );

	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL,"https://www.googleapis.com/oauth2/v4/token");
	    curl_setopt($ch, CURLOPT_POST, 1);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_array));
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    $response = json_decode(curl_exec ($ch), 1);

			if (isset($response['access_token']) && $response['access_token']) {
				$ch = curl_init();
		    curl_setopt($ch, CURLOPT_URL,"https://www.googleapis.com/oauth2/v2/userinfo");
				curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		    	'Authorization: Bearer ' . $response['access_token'],
		    ));
		    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		    $response = json_decode(curl_exec ($ch), 1);
				if (isset($response['email']) && $response['email']) {
					$this->load->model('account/customer');

					$customer = $this->model_account_customer->getCustomerByEmail($response['email']);

					if ($customer && isset($customer['customer_id']) && $customer['customer_id']) {
						$this->session->data['customer_id'] = $customer['customer_id'];
					} else {
						$data = array(
							'firstname' => $response['given_name'],
					    'lastname' => $response['family_name'],
					    'email' => $response['email'],
					    'telephone' => '',
					    'fax' => '',
					    'company' => '',
					    'address_1' => '',
					    'address_2' => '',
					    'city' => '',
					    'postcode' => '',
					    'country_id' => '',
					    'zone_id' => '',
					    'password' => $response['family_name'],
					    'confirm' => $response['family_name'],
					    'newsletter' => 0,
					    'agree' => 1,
						);

						$this->session->data['customer_id'] = $this->model_account_customer->addCustomer($data);
					}

					$this->response->redirect($this->url->link('account/password', '', true));
				} else {
					$this->response->redirect($this->url->link('account/login', '', true));
				}
			} else {
				$this->response->redirect($this->url->link('account/login', '', true));
			}
	    curl_close ($ch);
		} else {
			$this->response->redirect($this->url->link('account/login', '', true));
		}
	}

	public function facebook() {
		$json = array();

		$response = $this->request->post;

		if (isset($response['email']) && $response['email']) {
		  $this->load->model('account/customer');

		  $customer = $this->model_account_customer->getCustomerByEmail($response['email']);

		  if ($customer && isset($customer['customer_id']) && $customer['customer_id']) {
		    $this->session->data['customer_id'] = $customer['customer_id'];
		  } else {
				$name = explode(' ', $response['name'], 2);

		    $data = array(
		      'firstname' => $name[0],
		      'lastname' => $name[1],
		      'email' => $response['email'],
		      'telephone' => '',
		      'fax' => '',
		      'company' => '',
		      'address_1' => '',
		      'address_2' => '',
		      'city' => '',
		      'postcode' => '',
		      'country_id' => '',
		      'zone_id' => '',
		      'password' => $name[0],
		      'confirm' => $name[1],
		      'newsletter' => 0,
		      'agree' => 1,
		    );
		    $this->session->data['customer_id'] = $this->model_account_customer->addCustomer($data);
		  }

		  $json['success'] = 1;
		} else {
		  $json['error'] = 1;
		}
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
