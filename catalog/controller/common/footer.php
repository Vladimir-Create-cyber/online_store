<?php
// *	@copyright	OPENCART.PRO 2011 - 2017.
// *	@forum	http://forum.opencart.pro
// *	@source		See SOURCE.txt for source and other copyright.
// *	@license	GNU General Public License version 3; see LICENSE.txt

class ControllerCommonFooter extends Controller {
	public function index() {
		$this->load->language('common/footer');

		$data['scripts'] = $this->document->getScripts('footer');

		$data['text_information'] = $this->language->get('text_information');
		$data['text_service'] = $this->language->get('text_service');
		$data['text_extra'] = $this->language->get('text_extra');
		$data['text_contact'] = $this->language->get('text_contact');
		$data['text_return'] = $this->language->get('text_return');
		$data['text_sitemap'] = $this->language->get('text_sitemap');
		$data['text_manufacturer'] = $this->language->get('text_manufacturer');
		$data['text_voucher'] = $this->language->get('text_voucher');
		$data['text_affiliate'] = $this->language->get('text_affiliate');
		$data['text_special'] = $this->language->get('text_special');
		$data['text_bestseller'] = $this->language->get('text_bestseller');
		$data['text_mostviewed'] = $this->language->get('text_mostviewed');
		$data['text_latest'] = $this->language->get('text_latest');
		$data['text_account'] = $this->language->get('text_account');
		$data['text_order'] = $this->language->get('text_order');
		$data['text_wishlist'] = $this->language->get('text_wishlist');
		$data['text_newsletter'] = $this->language->get('text_newsletter');
		$data['text_error_tel'] = $this->language->get('text_error_tel');
		$data['text_error_tel_number'] = $this->language->get('text_error_tel_number');
		$data['text_form_title'] = $this->language->get('text_form_title');
		$data['text_form_poland'] = $this->language->get('text_form_poland');
		$data['text_form_support'] = $this->language->get('text_form_support');
		
		// **********************************************//

		$data['text_formma_title'] = $this->language->get('text_formma_title');
		$data['text_formma_title_name'] = $this->language->get('text_formma_title_name');
		$data['text_formma_title_company'] = $this->language->get('text_formma_title_company');
		$data['text_formma_title_email'] = $this->language->get('text_formma_title_email');
		$data['text_formma_title_tel'] = $this->language->get('text_formma_title_tel');
		$data['text_formma_title_butt'] = $this->language->get('text_formma_title_butt');

		$data['text_formma_res_name'] = $this->language->get('text_formma_res_name');
		$data['text_formma_res_phone'] = $this->language->get('text_formma_res_phone');
		$data['text_formma_res_email'] = $this->language->get('text_formma_res_email');
		$data['text_formma_res_success'] = $this->language->get('text_formma_res_success');
		$data['text_formma_res_error'] = $this->language->get('text_formma_res_error');



		// Отримання номерів телефонів з налаштувань
		$poland = $this->config->get('config_telephone'); // Номер телефону Польщі
		$usa = $this->config->get('config_telephone_usa'); // Номер телефону США
		$data['whatssApp_number'] = $this->config->get('config_telephone_whatsApp'); // Номер телефону WhatsApp

		// Передача номерів телефонів в масив $data
		$data['poland'] = $poland;
		$data['usa'] = $usa;
		$data['whatssApp'] = preg_replace('/[^A-Za-z0-9А-Яа-я]/u', '', $data['whatssApp_number']); 


		

		// **********************************************//
		


		$this->load->model('catalog/information');

		$data['informations'] = array();

		foreach ($this->model_catalog_information->getInformations() as $result) {
			if ($result['bottom']) {
				$data['informations'][] = array(
					'title' => $result['title'],
					'href'  => $this->url->link('information/information', 'information_id=' . $result['information_id'])
				);
			}
		}

		$data['contact'] = $this->url->link('information/contact');
		$data['return'] = $this->url->link('account/return/add', '', true);
		$data['sitemap'] = $this->url->link('information/sitemap');
		$data['manufacturer'] = $this->url->link('product/manufacturer');
		$data['voucher'] = $this->url->link('account/voucher', '', true);
		$data['affiliate'] = $this->url->link('affiliate/account', '', true);
		$data['special'] = $this->url->link('product/special');
		$data['bestseller'] = $this->url->link('product/bestseller');
		$data['mostviewed'] = $this->url->link('product/mostviewed');
		$data['latest'] = $this->url->link('product/latest');
		$data['account'] = $this->url->link('account/account', '', true);
		$data['order'] = $this->url->link('account/order', '', true);
		$data['wishlist'] = $this->url->link('account/wishlist', '', true);
		$data['newsletter'] = $this->url->link('account/newsletter', '', true);
		$data['logged'] = $this->customer->isLogged();

		$data['powered'] = sprintf($this->language->get('text_powered'), $this->config->get('config_name'), date('Y', time()));


		// baner block
		if($data['logged']){
			$this->load->model('design/banner');
			$this->load->model('tool/image');
			$banner = $this->model_design_banner->getBanner(11);

			foreach ($banner as $result) {
				if (is_file(DIR_IMAGE . $result['image'])) {
					$data['banners'][] = array(
						'title' => $result['title'],
						'link'  => $result['link'],
						'image' => $this->model_tool_image->resize($result['image'], 400, 400)

					);
				}
			}

		}

		// Whos Online
		if ($this->config->get('config_customer_online')) {
			$this->load->model('tool/online');

			if (isset($this->request->server['REMOTE_ADDR'])) {
				$ip = $this->request->server['REMOTE_ADDR'];
			} else {
				$ip = '';
			}

			if (isset($this->request->server['HTTP_HOST']) && isset($this->request->server['REQUEST_URI'])) {
				$url = 'http://' . $this->request->server['HTTP_HOST'] . $this->request->server['REQUEST_URI'];
			} else {
				$url = '';
			}

			if (isset($this->request->server['HTTP_REFERER'])) {
				$referer = $this->request->server['HTTP_REFERER'];
			} else {
				$referer = '';
			}

			$this->model_tool_online->addOnline($ip, $this->customer->getId(), $url, $referer);
		}

		return $this->load->view('common/footer', $data);
	}
}
