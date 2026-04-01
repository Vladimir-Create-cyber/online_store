<?php
class ControllerCommonHeader extends Controller {
	public function index() {

	    // if (!$this->customer->isLogged() ) {
	    //    if (!isset($this->request->get['route']) || $this->request->get['route'] != 'account/login' && $this->request->get['route'] != 'account/register' ) {
	    //      $this->response->redirect($this->url->link('account/login', '', true));
	    //    }
	    // }


		$this->load->model('extension/extension');

		$data['analytics'] = array();

		$analytics = $this->model_extension_extension->getExtensions('analytics');

		foreach ($analytics as $analytic) {
			if ($this->config->get($analytic['code'] . '_status')) {
				$data['analytics'][] = $this->load->controller('extension/analytics/' . $analytic['code'], $this->config->get($analytic['code'] . '_status'));
			}
		}

		if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
			$server = $this->config->get('config_ssl');
		} else {
			$server = $this->config->get('config_url');
		}

		if (is_file(DIR_IMAGE . $this->config->get('config_icon'))) {
			$this->document->addLink($server . 'image/' . $this->config->get('config_icon'), 'icon');
		}

		$data['title'] = $this->document->getTitle();

		$data['base'] = $server;
		$data['description'] = $this->document->getDescription();
		$data['keywords'] = $this->document->getKeywords();
		$data['links'] = $this->document->getLinks();
		$data['styles'] = $this->document->getStyles();
		$data['scripts'] = $this->document->getScripts();
		$data['lang'] = $this->language->get('code');
		$data['direction'] = $this->language->get('direction');

		$data['name'] = $this->config->get('config_name');

		if (is_file(DIR_IMAGE . $this->config->get('config_logo'))) {
			$data['logo'] = $server . 'image/' . $this->config->get('config_logo');
		} else {
			$data['logo'] = '';
		}

		$this->load->language('common/header');
		$data['og_url'] = (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1')) ? HTTPS_SERVER : HTTP_SERVER) . substr($this->request->server['REQUEST_URI'], 1, (strlen($this->request->server['REQUEST_URI'])-1));
		$data['og_image'] = $this->document->getOgImage();

		$data['text_home'] = $this->language->get('text_home');

		// Wishlist
		if ($this->customer->isLogged()) {
			$this->load->model('account/wishlist');

			$data['text_wishlist'] = sprintf($this->language->get('text_wishlist'), $this->model_account_wishlist->getTotalWishlist());

			$customer_group_id = $this->customer->getGroupId();

			$this->load->model('account/customer_group');

			$data['customer_groupe'] = $this->model_account_customer_group->getCustomerGroup($customer_group_id);

			// krumax dev
			$data['download'] = 'https://dsn.group/price/' . $customer_group_id . "_" . $this->session->data['currency'] . ".xlsx";

		} else {
			$data['text_wishlist'] = sprintf($this->language->get('text_wishlist'), (isset($this->session->data['wishlist']) ? count($this->session->data['wishlist']) : 0));
		}

		$data['text_shopping_cart'] = $this->language->get('text_shopping_cart');
		$data['text_logged'] = sprintf($this->language->get('text_logged'), $this->url->link('account/account', '', true), $this->customer->getFirstName(), $this->url->link('account/logout', '', true));

		$data['text_account'] = $this->language->get('text_account');
		$data['text_register'] = $this->language->get('text_register');
		$data['text_login'] = $this->language->get('text_login');
		$data['text_order'] = $this->language->get('text_order');
		$data['text_transaction'] = $this->language->get('text_transaction');
		$data['text_download'] = $this->language->get('text_download');
		$data['text_logout'] = $this->language->get('text_logout');
		$data['text_checkout'] = $this->language->get('text_checkout');
		$data['text_page'] = $this->language->get('text_page');
		$data['text_category'] = $this->language->get('text_category');
		$data['text_all'] = $this->language->get('text_all');

		$data['text_opt_groupe'] = $this->language->get('text_opt_groupe');// krumax fix

		$data['text_contact'] = $this->language->get('text_contact');	// krumax fix
		$data['text_contact_url'] = $this->language->get('text_contact_url');	// krumax fix

		// Отримання номерів телефонів з налаштувань
		$poland = $this->config->get('config_telephone'); // Номер телефону Польщі
		$usa = $this->config->get('config_telephone_usa'); // Номер телефону США
		$whatssApp = $this->config->get('config_telephone_whatsApp'); // Номер телефону WhatsApp

		// Передача номерів телефонів в масив $data
		$data['poland'] = $poland;
		$data['usa'] = $usa;
		$data['whatssApp'] = preg_replace('/[^A-Za-z0-9А-Яа-я]/u', '', $whatssApp); 

		// Використання sprintf для форматування строк з номерами телефонів, отриманими з налаштувань
		$data['text_tel_usa'] = sprintf($this->language->get('text_tel_usa'), $usa);
		$data['text_tel_pol'] = sprintf($this->language->get('text_tel_pol'), $poland);
		$data['text_tel_whatsapp'] = sprintf($this->language->get('text_tel_whatsapp'), $whatssApp);




		$data['text_request_call'] = $this->language->get('text_request_call');	// krumax fix
		$data['text_socials'] = $this->language->get('text_socials');	// krumax fix

		$data['home'] = $this->url->link('common/home');
		$data['wishlist'] = $this->url->link('account/wishlist', '', true);
		$data['logged'] = $this->customer->isLogged();
		if($data["logged"]){
			foreach($_SESSION as $item){
				if($item["payment_address"]['firstname']){
					$data["clientname"] = $item["payment_address"]['firstname']." ". $item["payment_address"]['lastname'];
				} else{
					// $data["clientname"] = $this->customer->getFirstName() . ' ' .mb_substr($this->customer->getLastName(),0,1);
					$data["clientname"] = $this->customer->getFirstName() . ' '. $this->customer->getLastName();

				}
			}
		}		
		$data['account'] = $this->url->link('account/account', '', true);
		$data['register'] = $this->url->link('account/register', '', true);
		$data['login'] = $this->url->link('account/login', '', true);
		$data['order'] = $this->url->link('account/order', '', true);
		$data['transaction'] = $this->url->link('account/transaction', '', true);
		$data['logout'] = $this->url->link('account/logout', '', true);
		$data['shopping_cart'] = $this->url->link('checkout/cart');
		$data['checkout'] = $this->url->link('checkout/checkout', '', true);
		$data['contact'] = $this->url->link('information/contact');
		$data['telephone'] = $this->config->get('config_telephone');

		// Menu
		$this->load->model('catalog/category');

		$this->load->model('catalog/product');

		$data['categories'] = array();

		$categories = $this->model_catalog_category->getCategories(0);

		foreach ($categories as $category) {
			if ($category['top']) {
				// Level 2
				$children_data = array();

				$children = $this->model_catalog_category->getCategories($category['category_id']);

				foreach ($children as $child) {
					$filter_data = array(
						'filter_category_id'  => $child['category_id'],
						'filter_sub_category' => true
					);

					$children_data[] = array(
						'name'  => $child['name'] . ($this->config->get('config_product_count') ? ' (' . $this->model_catalog_product->getTotalProducts($filter_data) . ')' : ''),
						'href'  => $this->url->link('product/category', 'path=' . $category['category_id'] . '_' . $child['category_id'])
					);
				}

				// Level 1
				$data['categories'][] = array(
					'name'     => $category['name'],
					'children' => $children_data,
					'column'   => $category['column'] ? $category['column'] : 1,
					'href'     => $this->url->link('product/category', 'path=' . $category['category_id'])
				);
			}
		}

		$data['language'] = $this->load->controller('common/language');
		$data['currency'] = $this->load->controller('common/currency');
		$data['search'] = $this->load->controller('common/search');
		$data['cart'] = $this->load->controller('common/cart');

		// For page specific css
		if (isset($this->request->get['route'])) {
			if (isset($this->request->get['product_id'])) {
				$class = '-' . $this->request->get['product_id'];
			} elseif (isset($this->request->get['path'])) {
				$class = '-' . $this->request->get['path'];
			} elseif (isset($this->request->get['manufacturer_id'])) {
				$class = '-' . $this->request->get['manufacturer_id'];
			} elseif (isset($this->request->get['information_id'])) {
				$class = '-' . $this->request->get['information_id'];
			} else {
				$class = '';
			}

			$data['class'] = str_replace('/', '-', $this->request->get['route']) . $class;
		} else {
			$data['class'] = 'common-home';
		}

		$this->load->model('setting/module');

		$id = 69; //id html module on admin page
		// Language id
		$config_language_id = $this->config->get('config_language_id');
		// select module
		$html = $this->model_setting_module->getModule($id);
		$data['status_sale_banner'] = $html['status'];
		$data['sale_banner'] = htmlspecialchars_decode($html['module_description'][$config_language_id]['description']);

		return $this->load->view('common/header', $data);
	}
}
