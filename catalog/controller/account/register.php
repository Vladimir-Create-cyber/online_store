<?php
// *	@copyright	OPENCART.PRO 2011 - 2017.
// *	@forum	http://forum.opencart.pro
// *	@source		See SOURCE.txt for source and other copyright.
// *	@license	GNU General Public License version 3; see LICENSE.txt

class ControllerAccountRegister extends Controller
{
	private $error = array();

	public function index()
	{

		if ($this->customer->isLogged()) {
			$this->response->redirect($this->url->link('account/account', '', true));

			//$this->response->addHeader('Content-Type: application/json');
			// $this->response->setOutput(json_encode(['success' => true]));
			// return;
		}

		// $this->load->language('account/register'); // ru-ru

		$language_code = $this->config->get('config_language'); // например, 'ru-ru', 'uk-ua', 'en-gb'

		switch ($language_code) {
			case 'ru-ru':
				$this->load->language('account/register');
				break;
			case 'uk-ua':
				$this->load->language('extension/module/avail');
				break;
			case 'en-gb':
				$this->load->language('seocms/module');
				break;
		}

		$this->document->setTitle($this->language->get('heading_title'));
		$this->document->setRobots('noindex,follow');

		$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment.js');
		$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.js');
		$this->document->addStyle('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.css');

		$this->load->model('account/customer');

		if (
			$this->request->server['REQUEST_METHOD'] == 'POST' &&
			// $this->validate() &&
			empty($this->session->data['tmp_register_verified'])
		) {

			if (!$this->validate()) {
				$json = [
					'success' => false,
					'errors'  => $this->error,
				];

				$this->response->addHeader('Content-Type: application/json');
				$this->response->setOutput(json_encode($json));
				return;
			}

			// 1. Генерируем токен
			$code = bin2hex(openssl_random_pseudo_bytes(16));
			$this->request->post['code'] = $code;
			$verification_code = strtoupper(substr(md5(mt_rand()), 0, 7));
			$this->request->post['verification_code'] = $verification_code;

			$log_data = [
				'firstname' => $this->request->post['firstname'],
				'lastname' => $this->request->post['lastname'],
				'email' => $this->request->post['email'],
				'telephone' => $this->request->post['telephone'],
				'verification_code' => $verification_code,
				'datetime' => date('Y-m-d H:i:s')
			];

			// $customer_id = $this->model_account_customer->addCustomer($this->request->post);

			$this->session->data['tmp_register'] = $this->request->post;

			$this->model_account_customer->deleteLoginAttempts($this->request->post['email']);


			$subject = $this->language->get('email_code');
			$message = $this->language->get('email_thx_for_reg') . "<br><br>";
			$message .= $this->language->get('email_code') . " <b>$verification_code</b><br>";
			$message .= $this->language->get('email_text') . "<br><br>";
			$message .= $this->language->get('email_sincerely') . "<br>";
			$message .= html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8');


			// 4. Подготавливаем письмо
			// $link = $this->url->link('account/register/verify', 'code=' . $code, true);

			// $subject = 'Сonfirm your email';
			// $message = "Thank you for registering!<br>To confirm your email, please click the following link:<br><a href=\"$link\">$link</a> \n";
			// $message .= "Thank you,\n";
			// $message .= html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8');

			$mail = new Mail();
			$mail->protocol = $this->config->get('config_mail_protocol');
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
			$mail->smtp_username = $this->config->get('config_mail_smtp_username');
			$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
			$mail->smtp_port = $this->config->get('config_mail_smtp_port');
			$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

			$mail->setTo($this->request->post['email']);
			$mail->setFrom($this->config->get('config_email'));
			$mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
			$mail->setSubject($subject);
			$mail->setHtml(nl2br($message));
			$mail->send();


			// // Ответ для JS (успешно)
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode(['success' => true]));
			return;
		}




		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_account'),
			'href' => $this->url->link('account/account', '', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_register'),
			'href' => $this->url->link('account/register', '', true)
		);

		$data['heading_title'] = $this->language->get('heading_title');
		$this->document->setRobots('noindex,follow');

		$data['text_account_already'] = sprintf($this->language->get('text_account_already'), $this->url->link('account/login', '', true));
		$data['text_your_details'] = $this->language->get('text_your_details');
		$data['text_your_address'] = $this->language->get('text_your_address');
		$data['text_your_password'] = $this->language->get('text_your_password');
		$data['text_newsletter'] = $this->language->get('text_newsletter');
		$data['text_yes'] = $this->language->get('text_yes');
		$data['text_no'] = $this->language->get('text_no');
		$data['text_select'] = $this->language->get('text_select');
		$data['text_none'] = $this->language->get('text_none');
		$data['text_loading'] = $this->language->get('text_loading');

		$data['entry_customer_group'] = $this->language->get('entry_customer_group');
		$data['entry_firstname'] = $this->language->get('entry_firstname');
		$data['entry_lastname'] = $this->language->get('entry_lastname');
		$data['entry_email'] = $this->language->get('entry_email');
		$data['entry_telephone'] = $this->language->get('entry_telephone');
		$data['entry_fax'] = $this->language->get('entry_fax');
		$data['entry_company'] = $this->language->get('entry_company');
		$data['entry_address_1'] = $this->language->get('entry_address_1');
		$data['entry_address_2'] = $this->language->get('entry_address_2');
		$data['entry_postcode'] = $this->language->get('entry_postcode');
		$data['entry_city'] = $this->language->get('entry_city');
		$data['entry_country'] = $this->language->get('entry_country');
		$data['entry_zone'] = $this->language->get('entry_zone');
		$data['entry_newsletter'] = $this->language->get('entry_newsletter');
		$data['entry_password'] = $this->language->get('entry_password');
		$data['entry_confirm'] = $this->language->get('entry_confirm');

		// Языковые переменные для попапа
		$data['popuop_title']     = $this->language->get('popuop_title');
		$data['popuop_text']        = $this->language->get('popuop_text');
		$data['popuop_below'] = $this->language->get('popuop_below');
		$data['popuop_button']      = $this->language->get('popuop_button');
		$data['popuop_again_button']      = $this->language->get('popuop_again_button');

		// Языковые переменные для алертов
		$data['alert_error1']     = $this->language->get('alert_error1');
		$data['alert_error2']        = $this->language->get('alert_error2');
		$data['alert_error3'] = $this->language->get('alert_error3');
		$data['alert_error4']      = $this->language->get('alert_error4');
		$data['error_email']      = $this->language->get('error_email');
		$data['error_email_exists']      = $this->language->get('error_email_exists');
		$data['error_firstname']      = $this->language->get('error_firstname');
		$data['error_lastname']      = $this->language->get('error_lastname');
		$data['error_telephone']      = $this->language->get('error_telephone');
		$data['error_password']      = $this->language->get('error_password');
		$data['error_confirm']      = $this->language->get('error_confirm');
		$data['error_telephone']      = $this->language->get('error_telephone');



		$data['button_continue'] = $this->language->get('button_continue');
		$data['button_upload'] = $this->language->get('button_upload');

		$data['text_public_offer'] = $this->language->get('text_public_offer');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['firstname'])) {
			$data['error_firstname'] = $this->error['firstname'];
		} else {
			$data['error_firstname'] = '';
		}

		if (isset($this->error['lastname'])) {
			$data['error_lastname'] = $this->error['lastname'];
		} else {
			$data['error_lastname'] = '';
		}

		if (isset($this->error['email'])) {
			$data['error_email'] = $this->error['email'];
		} else {
			$data['error_email'] = '';
		}

		if (isset($this->error['telephone'])) {
			$data['error_telephone'] = $this->error['telephone'];
		} else {
			$data['error_telephone'] = '';
		}

		if (isset($this->error['address_1'])) {
			$data['error_address_1'] = $this->error['address_1'];
		} else {
			$data['error_address_1'] = '';
		}

		if (isset($this->error['city'])) {
			$data['error_city'] = $this->error['city'];
		} else {
			$data['error_city'] = '';
		}

		if (isset($this->error['postcode'])) {
			$data['error_postcode'] = $this->error['postcode'];
		} else {
			$data['error_postcode'] = '';
		}

		if (isset($this->error['country'])) {
			$data['error_country'] = $this->error['country'];
		} else {
			$data['error_country'] = '';
		}

		if (isset($this->error['zone'])) {
			$data['error_zone'] = $this->error['zone'];
		} else {
			$data['error_zone'] = '';
		}

		if (isset($this->error['custom_field'])) {
			$data['error_custom_field'] = $this->error['custom_field'];
		} else {
			$data['error_custom_field'] = array();
		}

		if (isset($this->error['password'])) {
			$data['error_password'] = $this->error['password'];
		} else {
			$data['error_password'] = '';
		}

		if (isset($this->error['confirm'])) {
			$data['error_confirm'] = $this->error['confirm'];
		} else {
			$data['error_confirm'] = '';
		}

		$data['action'] = $this->url->link('account/register', '', true);

		$data['customer_groups'] = array();

		if (is_array($this->config->get('config_customer_group_display'))) {
			$this->load->model('account/customer_group');

			$customer_groups = $this->model_account_customer_group->getCustomerGroups();

			foreach ($customer_groups as $customer_group) {
				if (in_array($customer_group['customer_group_id'], $this->config->get('config_customer_group_display'))) {
					$data['customer_groups'][] = $customer_group;
				}
			}
		}

		if (isset($this->request->post['customer_group_id'])) {
			$data['customer_group_id'] = $this->request->post['customer_group_id'];
		} else {
			$data['customer_group_id'] = $this->config->get('config_customer_group_id');
		}

		if (isset($this->request->post['firstname'])) {
			$data['firstname'] = $this->request->post['firstname'];
		} else {
			$data['firstname'] = '';
		}

		if (isset($this->request->post['lastname'])) {
			$data['lastname'] = $this->request->post['lastname'];
		} else {
			$data['lastname'] = '';
		}

		if (isset($this->request->post['email'])) {
			$data['email'] = $this->request->post['email'];
		} else {
			$data['email'] = '';
		}

		if (isset($this->request->post['telephone'])) {
			$data['telephone'] = $this->request->post['telephone'];
		} else {
			$data['telephone'] = '';
		}

		if (isset($this->request->post['fax'])) {
			$data['fax'] = $this->request->post['fax'];
		} else {
			$data['fax'] = '';
		}

		if (isset($this->request->post['company'])) {
			$data['company'] = $this->request->post['company'];
		} else {
			$data['company'] = '';
		}

		if (isset($this->request->post['address_1'])) {
			$data['address_1'] = $this->request->post['address_1'];
		} else {
			$data['address_1'] = '';
		}

		if (isset($this->request->post['address_2'])) {
			$data['address_2'] = $this->request->post['address_2'];
		} else {
			$data['address_2'] = '';
		}

		if (isset($this->request->post['postcode'])) {
			$data['postcode'] = $this->request->post['postcode'];
		} elseif (isset($this->session->data['shipping_address']['postcode'])) {
			$data['postcode'] = $this->session->data['shipping_address']['postcode'];
		} else {
			$data['postcode'] = '';
		}

		if (isset($this->request->post['city'])) {
			$data['city'] = $this->request->post['city'];
		} else {
			$data['city'] = '';
		}

		if (isset($this->request->post['country_id'])) {
			$data['country_id'] = (int)$this->request->post['country_id'];
		} elseif (isset($this->session->data['shipping_address']['country_id'])) {
			$data['country_id'] = $this->session->data['shipping_address']['country_id'];
		} else {
			$data['country_id'] = $this->config->get('config_country_id');
		}

		if (isset($this->request->post['zone_id'])) {
			$data['zone_id'] = (int)$this->request->post['zone_id'];
		} elseif (isset($this->session->data['shipping_address']['zone_id'])) {
			$data['zone_id'] = $this->session->data['shipping_address']['zone_id'];
		} else {
			$data['zone_id'] = '';
		}

		$this->load->model('localisation/country');

		$data['countries'] = $this->model_localisation_country->getCountries();

		// Custom Fields
		$this->load->model('account/custom_field');

		$data['custom_fields'] = $this->model_account_custom_field->getCustomFields();

		if (isset($this->request->post['custom_field'])) {
			if (isset($this->request->post['custom_field']['account'])) {
				$account_custom_field = $this->request->post['custom_field']['account'];
			} else {
				$account_custom_field = array();
			}

			if (isset($this->request->post['custom_field']['address'])) {
				$address_custom_field = $this->request->post['custom_field']['address'];
			} else {
				$address_custom_field = array();
			}

			$data['register_custom_field'] = $account_custom_field + $address_custom_field;
		} else {
			$data['register_custom_field'] = array();
		}

		if (isset($this->request->post['password'])) {
			$data['password'] = $this->request->post['password'];
		} else {
			$data['password'] = '';
		}

		if (isset($this->request->post['confirm'])) {
			$data['confirm'] = $this->request->post['confirm'];
		} else {
			$data['confirm'] = '';
		}

		if (isset($this->request->post['newsletter'])) {
			$data['newsletter'] = $this->request->post['newsletter'];
		} else {
			$data['newsletter'] = '';
		}

		// Captcha
		if ($this->config->get($this->config->get('config_captcha') . '_status') && in_array('register', (array)$this->config->get('config_captcha_page'))) {
			$data['captcha'] = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha'), $this->error);
		} else {
			$data['captcha'] = '';
		}

		if ($this->config->get('config_account_id')) {
			$this->load->model('catalog/information');

			$information_info = $this->model_catalog_information->getInformation($this->config->get('config_account_id'));

			if ($information_info) {
				$data['text_agree'] = sprintf($this->language->get('text_agree'), $this->url->link('information/information/agree', 'information_id=' . $this->config->get('config_account_id'), true), $information_info['title'], $information_info['title']);
			} else {
				$data['text_agree'] = '';
			}
		} else {
			$data['text_agree'] = '';
		}

		if (isset($this->request->post['agree'])) {
			$data['agree'] = $this->request->post['agree'];
		} else {
			$data['agree'] = false;
		}

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->load->language('account/register');

		if ($this->config->get('config_language') == 'uk-ua') {
			$this->load->language('extension/module/avail');
		} elseif ($this->config->get('config_language') == 'en-gb') {
			$this->load->language('seocms/module');
		} elseif ($this->config->get('config_language') == 'ru-gb') {
			$this->load->language('account/register');
		} elseif ($this->config->get('config_language') == 'es-es') {
			$this->load->language('account/register');
		}



		// Если не POST — отрисовываем обычную страницу
		$this->response->setOutput($this->load->view('account/register', $data));
	}



	public function verify()
	{
		file_put_contents(DIR_LOGS . 'verify_debug.log', "session_id: " . session_id() . "\n", FILE_APPEND);
		file_put_contents(DIR_LOGS . 'verify_debug.log', "tmp_register: " . print_r($this->session->data['tmp_register'], true), FILE_APPEND);

		$json = [];

		if ($this->request->server['REQUEST_METHOD'] === 'POST') {
			$code = isset($this->request->post['code']) ? trim($this->request->post['code']) : '';

			// Проверка: есть ли сессионные данные
			if (!empty($this->session->data['tmp_register'])) {
				$expectedCode = $this->session->data['tmp_register']['verification_code'];

				if ($code === $expectedCode) {
					// Код правильный — создаём пользователя
					$this->load->model('account/customer');

					$this->session->data['tmp_register']['code'] = $code; // сохраняем для CRM, если надо
					$customer_id = $this->model_account_customer->addCustomer($this->session->data['tmp_register'], false);

					$this->db->query("UPDATE " . DB_PREFIX . "customer SET approved = 1 WHERE customer_id = '" . (int)$customer_id . "'");

					// Логиним
					$this->customer->login(
						$this->session->data['tmp_register']['email'],
						$this->session->data['tmp_register']['password']
					);

					// Очистка временных данных
					unset($this->session->data['tmp_register']);


					// Флаг успешной верификации
					$this->session->data['tmp_register_verified'] = true;

					$json['success'] = true;
					file_put_contents(DIR_LOGS . 'verify_debug.log', "Success reached\n", FILE_APPEND);
					$json['redirect'] = 'https://dsn.group/thankyou-page';
					unset($this->session->data['tmp_register_verified']);
				} else {
					$json['error'] = 'Неверный код';
				}
			} else {
				$json['error'] = 'Сессия истекла или регистрация не начата';
			}
		} else {
			$json['error'] = 'Неверный метод запроса';
		}

		$this->response->addHeader('Content-Type: application/json');
		file_put_contents(DIR_LOGS . 'verify_debug.log', "Final JSON: " . print_r($json, true), FILE_APPEND);

		$this->response->setOutput(json_encode($json));
	}

	public function resendCode()
	{
		$json = [];

		if (!isset($this->session->data['tmp_register'])) {
			$json['error'] = 'Данные для отправки не найдены. Пожалуйста, заполните форму регистрации заново.';
		} else {
			// Количество отправок
			if (!isset($this->session->data['tmp_register_resent_count'])) {
				$this->session->data['tmp_register_resent_count'] = 0;
			}

			// Последнее время отправки
			if (!isset($this->session->data['tmp_register_last_resent'])) {
				$this->session->data['tmp_register_last_resent'] = 0;
			}

			$now = time();
			$last_send_time = $this->session->data['tmp_register_last_resent'];
			$cooldown = 60; // секунд

			if ($this->session->data['tmp_register_resent_count'] >= 3) {
				$json['error'] = 'Вы достигли лимита повторной отправки уведомлений. Попробуйте позже.';
			} elseif ($now - $last_send_time < $cooldown) {
				$remaining = $cooldown - ($now - $last_send_time);
				$json['error'] = 'Подождите ' . $remaining . ' сек. перед следующей отправкой.';
			} else {
				// Обновляем время и счётчик
				$this->session->data['tmp_register_resent_count']++;
				$this->session->data['tmp_register_last_resent'] = $now;

				$data = $this->session->data['tmp_register'];

				// Генерация нового кода
				$verification_code = strtoupper(substr(md5(mt_rand()), 0, 7));
				$data['verification_code'] = $verification_code;
				$this->session->data['tmp_register']['verification_code'] = $verification_code;

				// Формируем письмо
				$this->load->language('account/register');

				$subject = $this->language->get('email_code');
				$message = $this->language->get('email_thx_for_reg') . "<br><br>";
				$message .= $this->language->get('email_code') . " <b>$verification_code</b><br>";
				$message .= $this->language->get('email_text') . "<br><br>";
				$message .= $this->language->get('email_sincerely') . "<br>";
				$message .= html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8');

				// Отправка письма
				$mail = new Mail();
				$mail->protocol = $this->config->get('config_mail_protocol');
				$mail->parameter = $this->config->get('config_mail_parameter');
				$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
				$mail->smtp_username = $this->config->get('config_mail_smtp_username');
				$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
				$mail->smtp_port = $this->config->get('config_mail_smtp_port');
				$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

				$mail->setTo($data['email']);
				$mail->setFrom($this->config->get('config_email'));
				$mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
				$mail->setSubject($subject);
				$mail->setHtml(nl2br($message));
				$mail->send();

				$remaining_attempts = 3 - $this->session->data['tmp_register_resent_count'];
				$json['success'] = 'Код был отправлен повторно. Осталось попыток: ' . $remaining_attempts;
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}








	private function validate()
	{
		file_put_contents(DIR_LOGS . 'verify_debug.log', "===== NEW ATTEMPT =====\n", FILE_APPEND);
		file_put_contents(DIR_LOGS . 'verify_debug.log', print_r($this->request->post, true), FILE_APPEND);


		if ((utf8_strlen(trim($this->request->post['firstname'])) < 1) || (utf8_strlen(trim($this->request->post['firstname'])) > 32)) {
			$this->error['firstname'] = $this->language->get('error_firstname');
		}

		if ((utf8_strlen(trim($this->request->post['lastname'])) < 1) || (utf8_strlen(trim($this->request->post['lastname'])) > 32)) {
			$this->error['lastname'] = $this->language->get('error_lastname');
		}

		$email = $this->request->post['email'];

		if (
			utf8_strlen($email) > 96 ||
			!filter_var($email, FILTER_VALIDATE_EMAIL) ||
			!preg_match('/^[^@]+@[^@]+\.[^@]{2,}$/', $email)
		) {
			$this->error['email'] = $this->language->get('error_email');
		}

		// if ((utf8_strlen($this->request->post['email']) > 96) || !filter_var($this->request->post['email'], FILTER_VALIDATE_EMAIL)) {
		// 	$this->error['email'] = $this->language->get('error_email');
		// }

		if ($this->model_account_customer->getTotalCustomersByEmail($this->request->post['email'])) {
			$this->error['warning'] = $this->language->get('error_exists');
			$this->error['email_exists'] = $this->language->get('error_exists');
		}


		$telephone = trim($this->request->post['telephone']);

		if (empty($telephone)) {
			$this->error['telephone'] = $this->language->get('error_telephone');
			return !$this->error;
		}
		if ((utf8_strlen($telephone) < 10) || (utf8_strlen($telephone) > 15)) {
			$this->error['telephone'] = $this->language->get('error_telephone');
			return !$this->error;
		}

		if (!preg_match('/^\+?[0-9]+$/', $telephone)) {
			$this->error['telephone'] = $this->language->get('error_telephone');
			return !$this->error;
		}




		if ((utf8_strlen(trim($this->request->post['address_1'])) < 3) || (utf8_strlen(trim($this->request->post['address_1'])) > 128)) {
			$this->error['address_1'] = $this->language->get('error_address_1');
		}

		if ((utf8_strlen(trim($this->request->post['city'])) < 2) || (utf8_strlen(trim($this->request->post['city'])) > 128)) {
			$this->error['city'] = $this->language->get('error_city');
		}

		$this->load->model('localisation/country');

		$country_info = $this->model_localisation_country->getCountry($this->request->post['country_id']);

		if ($country_info && $country_info['postcode_required'] && (utf8_strlen(trim($this->request->post['postcode'])) < 2 || utf8_strlen(trim($this->request->post['postcode'])) > 10)) {
			$this->error['postcode'] = $this->language->get('error_postcode');
		}

		if ($this->request->post['country_id'] == '') {
			$this->error['country'] = $this->language->get('error_country');
		}

		if (!isset($this->request->post['zone_id']) || $this->request->post['zone_id'] == '' || !is_numeric($this->request->post['zone_id'])) {
			$this->error['zone'] = $this->language->get('error_zone');
		}

		// Customer Group
		if (isset($this->request->post['customer_group_id']) && is_array($this->config->get('config_customer_group_display')) && in_array($this->request->post['customer_group_id'], $this->config->get('config_customer_group_display'))) {
			$customer_group_id = $this->request->post['customer_group_id'];
		} else {
			$customer_group_id = $this->config->get('config_customer_group_id');
		}

		// Custom field validation
		$this->load->model('account/custom_field');

		$custom_fields = $this->model_account_custom_field->getCustomFields($customer_group_id);

		foreach ($custom_fields as $custom_field) {
			if ($custom_field['required'] && empty($this->request->post['custom_field'][$custom_field['location']][$custom_field['custom_field_id']])) {
				$this->error['custom_field'][$custom_field['custom_field_id']] = sprintf($this->language->get('error_custom_field'), $custom_field['name']);
			} elseif (($custom_field['type'] == 'text') && !empty($custom_field['validation']) && !filter_var($this->request->post['custom_field'][$custom_field['location']][$custom_field['custom_field_id']], FILTER_VALIDATE_REGEXP, array('options' => array('regexp' => $custom_field['validation'])))) {
				$this->error['custom_field'][$custom_field['custom_field_id']] = sprintf($this->language->get('error_custom_field'), $custom_field['name']);
			}
		}

		if ((utf8_strlen($this->request->post['password']) < 4) || (utf8_strlen($this->request->post['password']) > 20)) {
			$this->error['password'] = $this->language->get('error_password');
		}

		if ($this->request->post['confirm'] != $this->request->post['password']) {
			$this->error['confirm'] = $this->language->get('error_confirm');
		}

		// Captcha
		if ($this->config->get($this->config->get('config_captcha') . '_status') && in_array('register', (array)$this->config->get('config_captcha_page'))) {
			$captcha = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha') . '/validate');

			if ($captcha) {
				$this->error['captcha'] = $captcha;
			}
		}

		// Agree to terms
		// if ($this->config->get('config_account_id')) {
		// 	$this->load->model('catalog/information');

		// 	$information_info = $this->model_catalog_information->getInformation($this->config->get('config_account_id'));

		// 	if ($information_info && !isset($this->request->post['agree'])) {
		// 		$this->error['warning'] = sprintf($this->language->get('error_agree'), $information_info['title']);
		// 	}
		// }

		if (empty($this->request->post['g-recaptcha-response'])) {
			$this->error['g-captcha'] = $captcha;
		}
		// } else {
		// 	$verify = $this->verifyCaptcha('6LeEH2EqAAAAACg6s0N-XEaPNgG2BMLl3TCsitvf', $this->request->post['g-recaptcha-response'], 'amplified-lamp-340412', 'LOGIN');
		// 	if($verify){
		// 		$this->error['g-captcha'] = $captcha;
		// 	}
		// }

		return !$this->error;
	}

	public function customfield()
	{
		$json = array();

		$this->load->model('account/custom_field');

		// Customer Group
		if (isset($this->request->get['customer_group_id']) && is_array($this->config->get('config_customer_group_display')) && in_array($this->request->get['customer_group_id'], $this->config->get('config_customer_group_display'))) {
			$customer_group_id = $this->request->get['customer_group_id'];
		} else {
			$customer_group_id = $this->config->get('config_customer_group_id');
		}

		$custom_fields = $this->model_account_custom_field->getCustomFields($customer_group_id);

		foreach ($custom_fields as $custom_field) {
			$json[] = array(
				'custom_field_id' => $custom_field['custom_field_id'],
				'required'        => $custom_field['required']
			);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	private function verifyCaptcha($recaptchaKey, $token, $projectId, $action)
	{
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
		curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Referer: ' . HTTPS_SERVER]);
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
			return true;
		} else {
			return false;
		}
	}
}
