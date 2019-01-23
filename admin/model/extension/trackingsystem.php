<?php
class ModelExtensionTrackingsystem extends Model {
	
	public function CreateColumn(){
		$query = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "order` LIKE 'courier'");
		if(!$query->num_rows){
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "order` ADD `courier` varchar(255) NOT NULL AFTER `date_modified`");
		}
	
		$query = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "order` LIKE 'tracking_number'");
		if(!$query->num_rows){
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "order` ADD `tracking_number` varchar(255) NOT NULL AFTER `courier`");
		}
	}

	public function addtrackingnumber($data,$order_status_id){
		
		$this->load->model('sale/order');
		$notify = ($this->config->has('module_trackingsystem_notify') ?  $this->config->get('module_trackingsystem_notify') : 0);
		
		$settings = $this->config->has('module_trackingsystem_setting') ?  $this->config->get('module_trackingsystem_setting') :array();
		foreach ($settings as $setting) {
			$links[trim($setting['code'])] = $setting['link'];
		}
		
		$order_id = trim($data['order_id']);
		$code = trim($data['courier_code']);
		$courier = trim($data['courier']);
		if($order_id != '' || $code != ''){
			$query = $this->db->query("SELECT `order_id` FROM `" . DB_PREFIX . "order` WHERE order_id = '" . (int)$order_id . "'");
			if ($query->rows){
				$this->db->query("UPDATE `" . DB_PREFIX . "order` set `courier`='" . $this->db->escape($courier) . "',`tracking_number`='" . $this->db->escape($code) . "', `order_status_id`='" . $order_status_id ."' WHERE order_id = '" . (int)$order_id . "'");
				
				$find = array(
					'{order_id}',
					'{shipping_method}',
					'{number}',
					'{link}',
				);

				$replace = array(
				  'order_id'  			=> $order_id,
				  'shipping_method'  	=> $courier,
				  'code'   				=> $code,
				  'link' 				=> (isset($links[$courier]) ? $links[$courier] : ''),
				);
				
				$comment = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $this->config->get('module_trackingsystem_comment')))));

				$this->addOrderHistory($order_id,$order_status_id,$comment,$notify);
			}
		}
	}
	
	public function addOrderHistory($order_id, $order_status_id, $comment = '', $notify = false, $override = false) {
		$this->load->model('sale/order');
		$order_info = $this->model_sale_order->getOrder($order_id);
		
		if ($order_info) {
			// Fraud Detection
			$this->load->model('extension/customer');

			$customer_info = $this->model_extension_customer->getCustomer($order_info['customer_id']);

			if ($customer_info && $customer_info['safe']) {
				$safe = true;
			} else {
				$safe = false;
			}

			// Only do the fraud check if the customer is not on the safe list and the order status is changing into the complete or process order status
			if (!$safe && !$override && in_array($order_status_id, array_merge($this->config->get('config_processing_status'), $this->config->get('config_complete_status')))) {
				// Anti-Fraud
				$this->load->model('setting/extension');

				$extensions = $this->model_setting_extension->getExtensions('fraud');

				foreach ($extensions as $extension) {
					if ($this->config->get('fraud_' . $extension['code'] . '_status')) {
						$this->load->model('extension/fraud/' . $extension['code']);

						if (property_exists($this->{'model_extension_fraud_' . $extension['code']}, 'check')) {
							$fraud_status_id = $this->{'model_extension_fraud_' . $extension['code']}->check($order_info);
	
							if ($fraud_status_id) {
								$order_status_id = $fraud_status_id;
							}
						}
					}
				}
			}

			// If current order status is not processing or complete but new status is processing or complete then commence completing the order
			if (!in_array($order_info['order_status_id'], array_merge($this->config->get('config_processing_status'), $this->config->get('config_complete_status'))) && in_array($order_status_id, array_merge($this->config->get('config_processing_status'), $this->config->get('config_complete_status')))) {
				// Redeem coupon, vouchers and reward points
				$order_totals = $this->model_sale_order->getOrderTotals($order_id);

				foreach ($order_totals as $order_total) {
					$this->load->model('extension/total/' . $order_total['code']);

					if (property_exists($this->{'model_extension_total_' . $order_total['code']}, 'confirm')) {
						// Confirm coupon, vouchers and reward points
						$fraud_status_id = $this->{'model_extension_total_' . $order_total['code']}->confirm($order_info, $order_total);
						
						// If the balance on the coupon, vouchers and reward points is not enough to cover the transaction or has already been used then the fraud order status is returned.
						if ($fraud_status_id) {
							$order_status_id = $fraud_status_id;
						}
					}
				}

				// Stock subtraction
				$order_products = $this->model_sale_order->getOrderProducts($order_id);

				foreach ($order_products as $order_product) {
					$this->db->query("UPDATE " . DB_PREFIX . "product SET quantity = (quantity - " . (int)$order_product['quantity'] . ") WHERE product_id = '" . (int)$order_product['product_id'] . "' AND subtract = '1'");

					$order_options = $this->model_sale_order->getOrderOptions($order_id, $order_product['order_product_id']);

					foreach ($order_options as $order_option) {
						$this->db->query("UPDATE " . DB_PREFIX . "product_option_value SET quantity = (quantity - " . (int)$order_product['quantity'] . ") WHERE product_option_value_id = '" . (int)$order_option['product_option_value_id'] . "' AND subtract = '1'");
					}
				}
				
				// Add commission if sale is linked to affiliate referral.
				if ($order_info['affiliate_id'] && $this->config->get('config_affiliate_auto')) {
					$this->load->model('extension/customer');

					if (!$this->model_extension_customer->getTotalTransactionsByOrderId($order_id)) {
						$this->model_extension_customer->addTransaction($order_info['affiliate_id'], $this->language->get('text_order_id') . ' #' . $order_id, $order_info['commission'], $order_id);
					}
				}
			}

			// Update the DB with the new statuses
			$this->db->query("UPDATE `" . DB_PREFIX . "order` SET order_status_id = '" . (int)$order_status_id . "', date_modified = NOW() WHERE order_id = '" . (int)$order_id . "'");

			$this->db->query("INSERT INTO " . DB_PREFIX . "order_history SET order_id = '" . (int)$order_id . "', order_status_id = '" . (int)$order_status_id . "', notify = '" . (int)$notify . "', comment = '" . $this->db->escape($comment) . "', date_added = NOW()");

			// If old order status is the processing or complete status but new status is not then commence restock, and remove coupon, voucher and reward history
			if (in_array($order_info['order_status_id'], array_merge($this->config->get('config_processing_status'), $this->config->get('config_complete_status'))) && !in_array($order_status_id, array_merge($this->config->get('config_processing_status'), $this->config->get('config_complete_status')))) {
				// Restock
				$order_products = $this->model_sale_order->getOrderProducts($order_id);

				foreach($order_products as $order_product) {
					$this->db->query("UPDATE `" . DB_PREFIX . "product` SET quantity = (quantity + " . (int)$order_product['quantity'] . ") WHERE product_id = '" . (int)$order_product['product_id'] . "' AND subtract = '1'");

					$order_options = $this->model_sale_order->getOrderOptions($order_id, $order_product['order_product_id']);

					foreach ($order_options as $order_option) {
						$this->db->query("UPDATE " . DB_PREFIX . "product_option_value SET quantity = (quantity + " . (int)$order_product['quantity'] . ") WHERE product_option_value_id = '" . (int)$order_option['product_option_value_id'] . "' AND subtract = '1'");
					}
				}

				// Remove coupon, vouchers and reward points history
				$order_totals = $this->model_sale_order->getOrderTotals($order_id);
				
				foreach ($order_totals as $order_total) {
					$this->load->model('extension/total/' . $order_total['code']);

					if (property_exists($this->{'model_extension_total_' . $order_total['code']}, 'unconfirm')) {
						$this->{'model_extension_total_' . $order_total['code']}->unconfirm($order_id);
					}
				}

				// Remove commission if sale is linked to affiliate referral.
				if ($order_info['affiliate_id']) {
					$this->load->model('extension/customer');
					
					$this->model_extension_customer->deleteTransactionByOrderId($order_id);
				}
			}

			$this->cache->delete('product');
			
			$language = new Language($order_info['language_code']);
			$language->load($order_info['language_code']);
			$language->load('extension/order_edit');

			$data['text_order_id'] = $language->get('text_order_id');
			$data['text_date_added'] = $language->get('text_date_added');
			$data['text_order_status'] = $language->get('text_order_status');
			$data['text_link'] = $language->get('text_link');
			$data['text_comment'] = $language->get('text_comment');
			$data['text_footer'] = $language->get('text_footer');

			$data['order_id'] = $order_info['order_id'];
			$data['date_added'] = date($language->get('date_format_short'), strtotime($order_info['date_added']));
			
			$order_status_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_status WHERE order_status_id = '" . (int)$order_status_id . "' AND language_id = '" . (int)$order_info['language_id'] . "'");
		
			if ($order_status_query->num_rows) {
				$data['order_status'] = $order_status_query->row['name'];
			} else {
				$data['order_status'] = '';
			}

			if ($order_info['customer_id']) {
				$data['link'] = $order_info['store_url'] . 'index.php?route=account/order/info&order_id=' . $order_info['order_id'];
			} else {
				$data['link'] = '';
			}

			$data['comment'] = strip_tags($comment);

			$this->load->model('setting/setting');
			
			$from = $this->model_setting_setting->getSettingValue('config_email', $order_info['store_id']);
			
			if (!$from) {
				$from = $this->config->get('config_email');
			}
			
			$mail = new Mail($this->config->get('config_mail_engine'));
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
			$mail->smtp_username = $this->config->get('config_mail_smtp_username');
			$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
			$mail->smtp_port = $this->config->get('config_mail_smtp_port');
			$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

			$mail->setTo($order_info['email']);
			$mail->setFrom($from);
			$mail->setSender(html_entity_decode($order_info['store_name'], ENT_QUOTES, 'UTF-8'));
			$mail->setSubject(html_entity_decode(sprintf($language->get('text_subject'), $order_info['store_name'], $order_info['order_id']), ENT_QUOTES, 'UTF-8'));
			$mail->setText($this->load->view('extension/order_edit', $data));
			$mail->send();
		}
		
		if ($order_info && !$order_info['order_status_id'] && $order_status_id && in_array('order', (array)$this->config->get('config_mail_alert'))) {	
			$this->load->language('extension/order_alert');
			
			// HTML Mail
			$data['text_received'] = $this->language->get('text_received');
			$data['text_order_id'] = $this->language->get('text_order_id');
			$data['text_date_added'] = $this->language->get('text_date_added');
			$data['text_order_status'] = $this->language->get('text_order_status');
			$data['text_product'] = $this->language->get('text_product');
			$data['text_total'] = $this->language->get('text_total');
			$data['text_comment'] = $this->language->get('text_comment');
			
			$data['order_id'] = $order_info['order_id'];
			$data['date_added'] = date($this->language->get('date_format_short'), strtotime($order_info['date_added']));

			$order_status_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_status WHERE order_status_id = '" . (int)$order_status_id . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "'");

			if ($order_status_query->num_rows) {
				$data['order_status'] = $order_status_query->row['name'];
			} else {
				$data['order_status'] = '';
			}

			$this->load->model('tool/upload');
			
			$data['products'] = array();

			$order_products = $this->model_sale_order->getOrderProducts($order_id);

			foreach ($order_products as $order_product) {
				$option_data = array();
				
				$order_options = $this->model_sale_order->getOrderOptions($order_info['order_id'], $order_product['order_product_id']);
				
				foreach ($order_options as $order_option) {
					if ($order_option['type'] != 'file') {
						$value = $order_option['value'];
					} else {
						$upload_info = $this->model_tool_upload->getUploadByCode($order_option['value']);
	
						if ($upload_info) {
							$value = $upload_info['name'];
						} else {
							$value = '';
						}
					}

					$option_data[] = array(
						'name'  => $order_option['name'],
						'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value)
					);					
				}
					
				$data['products'][] = array(
					'name'     => $order_product['name'],
					'model'    => $order_product['model'],
					'quantity' => $order_product['quantity'],
					'option'   => $option_data,
					'total'    => html_entity_decode($this->currency->format($order_product['total'] + ($this->config->get('config_tax') ? ($order_product['tax'] * $order_product['quantity']) : 0), $order_info['currency_code'], $order_info['currency_value']), ENT_NOQUOTES, 'UTF-8')
				);
			}
			
			$data['vouchers'] = array();
			
			$order_vouchers = $this->model_sale_order->getOrderVouchers($order_id);

			foreach ($order_vouchers as $order_voucher) {
				$data['vouchers'][] = array(
					'description' => $order_voucher['description'],
					'amount'      => html_entity_decode($this->currency->format($order_voucher['amount'], $order_info['currency_code'], $order_info['currency_value']), ENT_NOQUOTES, 'UTF-8')
				);					
			}

			$data['totals'] = array();
			
			$order_totals = $this->model_sale_order->getOrderTotals($order_id);

			foreach ($order_totals as $order_total) {
				$data['totals'][] = array(
					'title' => $order_total['title'],
					'value' => html_entity_decode($this->currency->format($order_total['value'], $order_info['currency_code'], $order_info['currency_value']), ENT_NOQUOTES, 'UTF-8')
				);
			}

			$data['comment'] = strip_tags($order_info['comment']);

			$mail = new Mail($this->config->get('config_mail_engine'));
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
			$mail->smtp_username = $this->config->get('config_mail_smtp_username');
			$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
			$mail->smtp_port = $this->config->get('config_mail_smtp_port');
			$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

			$mail->setTo($this->config->get('config_email'));
			$mail->setFrom($this->config->get('config_email'));
			$mail->setSender(html_entity_decode($order_info['store_name'], ENT_QUOTES, 'UTF-8'));
			$mail->setSubject(html_entity_decode(sprintf($this->language->get('text_subject'), $this->config->get('config_name'), $order_info['order_id']), ENT_QUOTES, 'UTF-8'));
			$mail->setText($this->load->view('extension/order_alert', $data));
			$mail->send();

			// Send to additional alert emails
			$emails = explode(',', $this->config->get('config_mail_alert_email'));

			foreach ($emails as $email) {
				if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
					$mail->setTo($email);
					$mail->send();
				}
			}
		}
	}
}
?>