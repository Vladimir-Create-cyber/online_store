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
	  
		$json = array();
		
		if ($this->request->server['REQUEST_METHOD'] == 'POST') {

			if (!$this->validate()) {
				
				try {
					// Перевіряю чи є потрібна COOKIE
					if(isset($_COOKIE['site_client_uuid']) and !empty($_COOKIE['site_client_uuid'])){
						//гружу свою модельку
						$this->load->model('account/action');
						//робою запит на перевірку активності даного користувача (типу щоб знати чи створювати новий запис чи оновити старий)
						$client_action_id = $this->model_account_action->isExist($_COOKIE['site_client_uuid']);
						//Формую масив для створення|оновлення запису
						$sendData = [
							'uuid' => $_COOKIE['site_client_uuid'],
							'name' => $this->request->post['name'],
							'tel'  => $this->request->post['tel'],
							'email'=> $this->request->post['email'],
						];

						//перевіряю що робить створювати чи оновлювати
						//$client_action_id - або false або ідентифікатор існуючого запису
						if($client_action_id){
							$this->model_account_action->update($client_action_id,$sendData);
						}else{
							$this->model_account_action->add($sendData);
						}
					}			

					$AMOApi = new \amoCRMApi($config['auth']['subdomain'],$config['auth']['login'], $config['auth']['token']);

					$data['pipeline_id'] = 3136549; // Вхідні заявки
					$data['status_id'] = 32102401; // статус
					$data['responsible_user_id'] = 8263684; // manager id

					$data['name'] = 'Вхідна заявка';
					$data['tag'] = 'ФОРМА ПІДПИСКИ';
					$data['tags'] = [
						
								'name' => 'ФОРМА ПІДПИСКИ',
							
						];
					
					$data['custom_fields'] = [
						[
							'id' => 1207439,
							'values'=> [
								[
									'value'=> $this->request->post['message']
								]
							]
						],
						[
							'id' =>  2075753,
							'values'=> [
								[
									'value'=> $_COOKIE['site_client_uuid']
								]
							]
						]


					];

					$leadID = $AMOApi->newLead($data);

					$data['name'] = $this->request->post['name'];
					$data['_embedded'] = [
						[
							'tags' => [
								'name' => 'ФОРМА ПІДПИСКИ',
							],
						]
					];
					// $data['tag'] = 'ФОРМА ПІДПИСКИ';
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
				// $AMOApi::$logger->error("[{$ex->getCode()}] {$ex->getMessage()} in {$ex->getFile()}:{$ex->getLine()}");
				}
				
				require_once join(DIRECTORY_SEPARATOR,[$_SERVER['DOCUMENT_ROOT'],'mailsendTelegram.php']);

				$json['result'] = $this->language->get('success');
				$json['contactID'] = $contactID;
				$json['message'] = $this->request->post['message'];
				$this->response->setOutput(json_encode($json));
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

		if(empty($this->request->post['g-recaptcha-response'])){
			$error_name = $this->language->get('error_captcha');
			$json['error'] = 'error';
			$json['error_name'] = $error_name;
		} else {
			$verify = $this->verifyCaptcha('6LeEH2EqAAAAACg6s0N-XEaPNgG2BMLl3TCsitvf', $this->request->post['g-recaptcha-response'], 'amplified-lamp-340412', 'LOGIN');
			if($verify){
				$error_name = $this->language->get('error_captcha_valid');
				$json['error'] = 'error';
				$json['error_name'] = $error_name;
			}
		}
		return $json;
	}

	private function verifyCaptcha($recaptchaKey, $token, $projectId, $action) {
		// Ваш секретний API ключ для доступу до Google reCAPTCHA Enterprise
		$apiKey = 'AIzaSyATjsniODPTUUxU_x47B91lGiIG7wBCygc'; // Замініть на свій API ключ
		$url = "https://recaptchaenterprise.googleapis.com/v1/projects/$projectId/assessments?key=$apiKey";
	
		// Підготуйте дані для запиту
		$data = [
			'event' => [
				'token' => $token,
				'siteKey' => $recaptchaKey,
				'expectedAction' => $action
			]
		];
	
		// Виконайте POST-запит за допомогою cURL
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
	
		// Отримайте відповідь від API
		$response = curl_exec($ch);
		curl_close($ch);
	
		// Розберіть JSON-відповідь
		$responseData = json_decode($response, true);
	
		// Перевірте, чи є токен дійсним
		if (isset($responseData['tokenProperties']['valid']) && !$responseData['tokenProperties']['valid']) {
			return false;
		}
	
		// Перевірте, чи збігається дія
		if ($responseData['tokenProperties']['action'] == $action) {
			// Отримайте оцінку ризику
			$score = $responseData['riskAnalysis']['score'];	
			// Ви можете визначити, чи є ризик прийнятним для подальших дій
			if ($score >= 0.5) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	
}
