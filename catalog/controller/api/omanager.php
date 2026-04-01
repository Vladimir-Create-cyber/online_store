<?php
class ControllerApiOmanager extends Controller {
	public function history() {
		$this->load->language('api/order');
		$json = array();

		if (!isset($this->session->data['api_id'])) {
			$json['error'] = $this->language->get('error_permission');
		} else {
			// Add keys for missing post vars
			$keys = array(
				'order_status_id',
				'notify',
				'override',
				'comment'
			);

			foreach ($keys as $key) {
				if (!isset($this->request->post[$key])) {
					$this->request->post[$key] = '';
				}
			}

			$this->load->model('checkout/order');

			if (isset($this->request->get['order_id'])) {
				$order_id = $this->request->get['order_id'];
			} else {
				$order_id = 0;
			}
			
			if (isset($this->request->post['comment'])) {
				$this->request->post['comment'] = html_entity_decode($this->request->post['comment'], ENT_QUOTES, 'UTF-8');
			}

			$order_info = $this->model_checkout_order->getOrder($order_id);

			if ($order_info) {
				$this->model_checkout_order->addOrderHistory($order_id, $this->request->post['order_status_id'], $this->request->post['comment'], $this->request->post['notify'], $this->request->post['override']);

				$json['success'] = $this->language->get('text_success');
			} else {
				$json['error'] = $this->language->get('error_not_found');
			}
		}

		if (isset($this->request->server['HTTP_ORIGIN'])) {
			$this->response->addHeader('Access-Control-Allow-Origin: ' . $this->request->server['HTTP_ORIGIN']);
			$this->response->addHeader('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
			$this->response->addHeader('Access-Control-Max-Age: 1000');
			$this->response->addHeader('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function change_statuses() {
		$this->load->language('api/order');
		$json = array();
		$errors = array();
		$success = array();

		if (!isset($this->session->data['api_id'])) {
			$json['error'] = $this->language->get('error_permission');
		} else {
			// Add keys for missing post vars
			$keys = array(
				'order_status_id',
				'notify',
				'override',
				'comment'
			);

			foreach ($keys as $key) {
				if (!isset($this->request->post[$key])) {
					$this->request->post[$key] = '';
				}
			}

			$this->load->model('checkout/order');

			if (isset($this->request->post['selected'])) {
				foreach ($this->request->post['selected'] as $order_id) {
					$comment = $this->request->post['comment'];
					$order_info = $this->model_checkout_order->getOrder($order_id);
					if ($order_info) {
						$find = array('{order_id}', '{store_name}', '{store_url}', '{track_no}', '{firstname}', '{lastname}', '{customer}', '{recipient}', '{total}', '{reward}', '{novaposhta_ei_number}', '{novaposhta_cn_number}');
						$replace = array(
							'order_id' => $order_info['order_id'],
							'store_name' => html_entity_decode($order_info['store_name'], ENT_QUOTES, 'UTF-8'),
							'store_url' => $order_info['store_url'],
							'track_no' => $order_info['track_no'],
							'firstname' => $order_info['firstname'],
							'lastname' => $order_info['lastname'],
							'customer' => $order_info['payment_firstname'] . ' ' . $order_info['payment_lastname'],
							'recipient' => $order_info['shipping_firstname'] . ' ' . $order_info['shipping_lastname'],
							'total' => $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value']),
							'reward' => $order_info['reward'],
							'novaposhta_ei_number' => isset($order_info['novaposhta_ei_number']) ? $order_info['novaposhta_ei_number'] : '',
							'novaposhta_cn_number' => isset($order_info['novaposhta_cn_number']) ? $order_info['novaposhta_cn_number'] : ''
						);

						$comment = str_replace($find, $replace, $comment);

						$this->model_checkout_order->addOrderHistory($order_id, $this->request->post['order_status_id'], $comment, $this->request->post['notify'], $this->request->post['override']);

						$success[] = $order_id;
					} else {
						$errors[] = $order_id;
					}
				}
			}
		}

		if (!empty($errors)) {
			$json['error'] = $this->language->get('error_not_found') . ': '.implode(', ', $errors);
		}

		if (!empty($success)) {
			$json['success'] = $this->language->get('text_success') . ': '.implode(', ', $success);
		}

		if (isset($this->request->server['HTTP_ORIGIN'])) {
			$this->response->addHeader('Access-Control-Allow-Origin: ' . $this->request->server['HTTP_ORIGIN']);
			$this->response->addHeader('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
			$this->response->addHeader('Access-Control-Max-Age: 1000');
			$this->response->addHeader('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}