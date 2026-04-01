<?php
// *	@copyright	OPENCART.PRO 2011 - 2017.
// *	@forum	http://forum.opencart.pro
// *	@source		See SOURCE.txt for source and other copyright.
// *	@license	GNU General Public License version 3; see LICENSE.txt

class ControllerCommonHome extends Controller {
	public function index() {
		$this->document->setTitle($this->config->get('config_meta_title'));
		$this->document->setDescription($this->config->get('config_meta_description'));
		$this->document->setKeywords($this->config->get('config_meta_keyword'));

		if (isset($this->request->get['route'])) {
			$this->document->addLink($this->config->get('config_url'), 'canonical');
		}

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('common/home', $data));
	}
	
	public function amocrm() {
		
		$this->load->language('common/home');
		
		require_once join(DIRECTORY_SEPARATOR,[$_SERVER['DOCUMENT_ROOT'],'amo','api.php']);
      $config = require join(DIRECTORY_SEPARATOR,[$_SERVER['DOCUMENT_ROOT'],'amo','amo.conf.php']);

	  echo '<pre>';
	  var_dump($_COOKIE);
	  echo '</pre>';
	  exit;
	  die;
	  
		$json = array();
		
		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			if (!$this->validate()) {
			
			try {
			  $AMOApi = new \amoCRMApi($config['auth']['subdomain'],$config['auth']['login'], $config['auth']['token']);

				$data['pipeline_id'] = 3136549; // Вхідні заявки
				$data['status_id'] = 32102401; // статус
				$data['responsible_user_id'] = 3910795; // manager id

				$data['name'] = 'Вхідна заявка';
				
				$data['custom_fields'] = [
					[
						'id' => 1207439,
						'values'=> [
							[
								'value'=> $this->request->post['message']
							]
						]
					]


				];

				$leadID = $AMOApi->newLead($data);

				$data['name'] = $this->request->post['name'];

				$data['custom_fields'] = [

						[
							'id' => 555915,
							'values'=> [
								[
									'value'=> $this->request->post['tel'],
									'enum'=>'WORK'
								]
							]
						],
						[
							'id' => 555917,
							'values'=> [
								[
									'value'=> $this->request->post['email'],
									'enum'=>'WORK'
								]
							]
						],
						[
							'id' => 1207441,
							'values'=> [
								[
									'value'=> $this->request->post['message']
								]
							]
						]


					];

			$data['date_create'] = time();
			$data['linked_leads_id'] = array($leadID);
			$contactID = $AMOApi->newContact($data);

			} catch (Exception $ex) {
			  $AMOApi::$logger->error("[{$ex->getCode()}] {$ex->getMessage()} in {$ex->getFile()}:{$ex->getLine()}");
			}

			$json['result'] = $this->language->get('success');
			$json['contactID'] = $contactID;
			$json['message'] = $this->request->post['message'];

			echo json_encode($json);
			} else {
				$json = $this->validate();
				echo json_encode($json);
			}
		}

	}
	
	private function validate() {
		if(empty($this->request->post['name'])){
			$error_name = $this->language->get('error_name');
			$json['error'] = 'error';
			$json['error_name'] = $error_name;
		}
		if(empty($this->request->post['tel']) || strlen($this->request->post['tel']) < 8 ){
			$error_name = $this->language->get('error_tel');
			$json['error'] = 'error';
			$json['error_name'] = $error_name;
		} elseif (!preg_match('/^[+]?[0-9]/', $this->request->post['tel'])) {
			$json['error'] = 'error';
			$error_name = $this->language->get('error_tel_valid');
			$json['error_name'] = $error_name;
		}
		if(empty($this->request->post['email'])){
			$error_name = $this->language->get('error_email');
			$json['error'] = 'error';
			$json['error_name'] = $error_name;
		} elseif (!filter_var($this->request->post['email'], FILTER_VALIDATE_EMAIL)){
			$error_name = $this->language->get('error_email_valid');
			$json['error'] = 'error';
			$json['error_name'] = $error_name;
		}
		
		
		if(empty($this->request->post['message'])){
			$error_name = $this->language->get('error_message');
			$json['error'] = 'error';
			$json['error_name'] = $error_name;
		}
		return $json;
	}
	
}
