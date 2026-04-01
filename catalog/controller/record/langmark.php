<?php
if (!class_exists('ControllerRecordLangmark', false)) {
	class ControllerRecordLangmark extends Controller {
		protected $data;
		protected $settings;
		protected $protocol;
		private $jetcache_buildcache = false;
		private $host;
		protected $http_user_agent = '';

		public function __construct($registry) {
			//Start
			parent::__construct($registry);

			if (!defined('SC_VERSION')) define('SC_VERSION', substr(str_replace('.', '', VERSION), 0, 2));

			if (isset($this->request->server['HTTP_X_REQUESTED_WITH']) && strtolower($this->request->server['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
				$this->jetcache_buildcache = false;
				$jetcache_headers = getallheaders();
				if (isset($jetcache_headers['JETCACHE_BUILDCACHE'])) {
					$this->jetcache_buildcache = true;
				}
			}

			$this->host = parse_url(HTTP_SERVER);
			$this->host = $this->host['host'];

			if ((isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) == 'on' || $_SERVER['HTTPS'] == '1')) || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && (strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'https') || (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && strtolower($_SERVER['HTTP_X_FORWARDED_SSL']) == 'on'))) {
				$this->protocol = 'https';
			} else {
				$this->protocol = 'http';
			}

			if ($this->protocol == 'https') {
				$config_url_0 = HTTPS_SERVER;
			} else {
				$config_url_0 = HTTP_SERVER;
			}

			$this->load->model('setting/store');

			$this->data['stores'][0] = array(
				'store_id' => 0,
				'name'     => $this->config->get('config_name'),
				'url' => $config_url_0
			);

			$stores = $this->model_setting_store->getStores();

			foreach ($stores as $result) {
				if ($this->protocol == 'https' && isset($result['ssl']) && $result['ssl'] != '') {
					$store_url = $result['ssl'];
				} else {
					$store_url = $result['url'];
				}

				$this->data['stores'][$result['store_id']] = array(
					'store_id' => $result['store_id'],
					'name' => $result['name'],
					'url' => $store_url
				);
			}

			if (isset($this->registry->get('request')->server['HTTP_USER_AGENT']) && $this->registry->get('request')->server['HTTP_USER_AGENT'] != '') {
				$this->http_user_agent = $this->registry->get('request')->server['HTTP_USER_AGENT'];
			} else {
				$this->http_user_agent = '';
			}

			$this->settings = $this->config->get('asc_langmark_' . $this->config->get('config_store_id'));
		}

		private function getMulti() {
			$multi = $this->registry->get('langmark_multi');
			if (!isset($multi['name'])) {
				if (isset($this->session->data['langmark_multi']['name'])) {
					$multi = $this->settings['multi'][$this->session->data['langmark_multi']['name']];
				}
			}
			return $multi;
		}

		public function index($settingswidget = array()) {

			if (empty($settingswidget)) {
				if ($this->registry->get('lm_settingswidget')) {
					$settingswidget = $this->registry->get('lm_settingswidget');
				}
			}

			$this->data['settingswidget'] = $this->data['settings_widget'] = $settingswidget;

			$this->config->set('blog_work', true);
			$this->load->model('localisation/language');

			$html = '';
			$language_code = $this->config->get('config_language');
			$language_id = $this->config->get('config_language_id');
			$store_id = $this->config->get('config_store_id');
			$this->data['config_store_id'] = $store_id;

			$this->data['languages'] = array();
			$array_hreflang = array();
			$array_hre = array();

			if (SC_VERSION < 20) {
				$this->load->language('module/language');
			} else {
				$this->load->language('common/language');
			}

			$this->data['text_language'] = $this->language->get('text_language');
			$this->data['action'] = '';
			$this->data['code'] = $this->data['language_code'] = $this->session->data['language'];

			if (isset($this->request->get['route'])) {
				$route = $this->request->get['route'];
			} else {
				$route = 'common/home';
			}

			$langmark_multi = $this->getMulti();

			$current_config_store_id = $this->config->get('config_store_id');

			foreach ($this->data['stores'] as $store_num => $store) {

				$this->config->set('config_store_id', $store['store_id']);

				$settings = $this->config->get('asc_langmark_' . $store['store_id']);


				if (isset($settings['use_link_status']) && !$settings['use_link_status']) {

					$url_current = $this->url->link($route, $this->getQueryString(array(
						'route',
						'_route_',
						'site_language',
						'tag'
					)));
					$url_current = str_replace('&amp;', '&', $url_current);
					$prefix_current = $langmark_multi['prefix'];

					$pos_current = stripos($url_current,  $prefix_current);
					$pos_current_len = $pos_current + strlen($prefix_current);
					$substr_pos_current_len = substr($url_current, $pos_current_len, 1);
					$substr_prefix_current = substr($prefix_current, -1);
				}

				$languages = $this->model_localisation_language->getLanguages();

				foreach ($languages as $result) {
					$languages_by_id[$result['language_id']] = $result;
				}

				if (!empty($settings['multi'])) {

					foreach ($settings['multi'] as $name => $settings_multi) {

						$this->config->set('config_store_id', $settings_multi['store_id']);

						if (isset($languages_by_id[$settings_multi['language_id']]) && $languages_by_id[$settings_multi['language_id']]['status']) {

							if (isset($settings_multi['prefix_switcher_stores']) && $settings_multi['prefix_switcher_stores'] || $store['store_id'] == $store_id) {

								if ((isset($settings_multi['hreflang_switcher']) && $settings_multi['hreflang_switcher']) || (isset($settings_multi['prefix_switcher']) && $settings_multi['prefix_switcher'])) {

									if (!isset($settings['use_link_status']) || (isset($settings['use_link_status']) && $settings['use_link_status'])) {

										if (isset($settings_multi['name'])) {
											// Why ?
											//$settings_multi['store_id'] = $store['store_id'];
											$this->registry->set('langmark_multi', $settings_multi);
										}

										$this->switchLanguage($settings, $settings_multi['language_id'], $languages_by_id[$settings_multi['language_id']]['code']);

										$url_lang = $this->url->link($route, $this->getQueryString(array(
											'route',
											'_route_',
											'site_language',
											'tag'
										)));
										$url_lang = str_replace('&amp;', '&', $url_lang);
									} else {

										$prefix_replace = $settings_multi['prefix'];

										$substr_prefix_replace = substr($prefix_replace, -1);

										if ($substr_prefix_current != '/' && $substr_prefix_replace == '/' && $substr_pos_current_len == '/') {
											$prefix_replace = substr($prefix_replace, 0, -1);
										}

										if ($substr_prefix_current == '/' && $substr_prefix_replace != '/' && $substr_pos_current_len != '/' && $substr_pos_current_len) {
											$prefix_replace = $prefix_replace . '/';
										}

										$url_lang = str_ireplace($prefix_current,  $prefix_replace, $url_current);
										$url_lang = str_replace('&amp;', '&', $url_lang);
									}
								}
								$run_href = true;
								$hreflang = $settings_multi['hreflang'];
								if (isset($settings_multi['hreflang_switcher']) && $settings_multi['hreflang_switcher']) {
									if ($settings_multi['store_id'] != $store_id) {
										if (isset($settings_multi['prefix_switcher_stores']) && $settings_multi['prefix_switcher_stores']) {
										} else {
											$run_href = false;
										}
									}

									if ($run_href && $hreflang != '') {
										$array_href[$settings_multi['hreflang']] = array('href' => $url_lang, 'hreflang' => $settings_multi['hreflang']);
									}
								}
								/* OLD
								if (isset($settings_multi['hreflang_switcher']) && $settings_multi['hreflang_switcher'] && $settings_multi['store_id'] == $store_id) {

							        $hreflang = '';
							        if (isset($settings_multi['hreflang']) && $settings_multi['hreflang'] != '') {
							            if (count($settings['multi']) > 0 && $settings_multi['store_id'] == $store_id) {
							            	$hreflang = $settings_multi['hreflang'];
							            }
							        } else {
							        	$hreflang = $languages_by_id[$settings_multi['language_id']]['code'];
							        }
									if ($hreflang != '') {
										if (!isset($array_hreflang[$hreflang])) {
											$array_hreflang[$hreflang] = Array('href' => $url_lang , 'hreflang' => $hreflang );
										}
									}
				                }
                                */
								if (isset($settings_multi['prefix_switcher']) && $settings_multi['prefix_switcher']) {

									//if ($settings_multi['prefix'] == $langmark_multi['prefix']) {
									if ($settings_multi['prefix'] == $langmark_multi['prefix'] && $langmark_multi['store_id'] == $current_config_store_id) {
										$current = true;
									} else {
										$current = false;
									}

									if (!isset($languages_by_id[$settings_multi['language_id']]['image'])) {
										$languages_by_id[$settings_multi['language_id']]['image'] = 'catalog/language/' . $languages_by_id[$settings_multi['language_id']]['code'] . '/' . $languages_by_id[$settings_multi['language_id']]['code'] . '.png';
									}
									if (isset($settings_multi['prefix_main']) && $settings_multi['prefix_main'] && $settings_multi['store_id'] == $current_config_store_id) { //
										$prefix_main = true;
									} else {
										$prefix_main = false;
									}

									if ($prefix_main && isset($settings_multi['hreflang_switcher']) && $settings_multi['hreflang_switcher'] && isset($this->settings['xdefault_status']) && $this->settings['xdefault_status'] && !empty($array_href)) {
										$array_href['x-default'] = array('href' => $url_lang, 'hreflang' => 'x-default');
									}

									$this->data['languages'][$url_lang] = array(
										'url'  => $url_lang,
										'name'  => $settings_multi['name'],
										'language'  => $languages_by_id[$settings_multi['language_id']]['name'],
										'code'  => $languages_by_id[$settings_multi['language_id']]['code'],
										'code_short'  => strtolower(substr($languages_by_id[$settings_multi['language_id']]['code'], 0, 2)),
										'image' => $languages_by_id[$settings_multi['language_id']]['image'],
										'store_id' => $store['store_id'],
										'store_name' => $store['name'],
										'language_id'  => $settings_multi['language_id'],
										'main' => $prefix_main,
										'current' =>  $current
									);
								}
							}
						}
					}
				}

				$this->registry->set('langmark_multi', $langmark_multi);

				$this->session->data['langmark_multi'] = array();
				$this->session->data['langmark_multi']['name'] = $langmark_multi['name'];
				$this->session->data['langmark_multi']['store_id'] = $langmark_multi['store_id'];

				$this->switchLanguage($this->settings, $language_id, $language_code);

				if (method_exists($this->document, 'setSCHreflang')  && isset($this->settings['hreflang_status']) && $this->settings['hreflang_status']) {
					if (!empty($array_href)) {
						$this->document->setSCHreflang($array_href);
						$this->registry->set('SCHreflang', $array_href);
					}
				}
			}

			$this->config->set('config_store_id', $current_config_store_id);

			$this->registry->set('langmark_multi', $langmark_multi);

			$this->session->data['langmark_multi'] = array();
			$this->session->data['langmark_multi']['name'] = $langmark_multi['name'];
			$this->session->data['langmark_multi']['store_id'] = $langmark_multi['store_id'];

			$this->data['settings_widget']['bot'] = false;
			$this->data['settings_widget']['autoredirect_delay_mobile'] = 11000;
			$this->data['settings_widget']['autoredirect_delay_desktop'] = 5000;

			$this->bots();
			$this->ex_langs();

			if (isset($settingswidget['langswitch_replace']) && !$settingswidget['langswitch_replace']) {
				$template = $settingswidget['template'];
				$template_info  = pathinfo($template);
				$template = $template_info['filename'];
				$this_template = $this->seocmslib->template('agootemplates/widgets/langmark/' . $template);
			} else {
				$template = 'langmark.tpl';
				$template_info  = pathinfo($template);
				$template = $template_info['filename'];
				$this_template = $this->seocmslib->template('agootemplates/record/' . $template);
			}

			$this->data['language'] = $this->language;
			$this->data['theme'] = $this->seocmslib->theme_folder;

			$this->config->set('blog_work', false);

			$this->template = $this_template;

			if (SC_VERSION < 20) {
				$html = $this->render();
			} else {
				$html = $this->load->view($this->template, $this->data);
			}

			if (isset($this->data['settings_widget']['bot']) && $this->data['settings_widget']['bot']) {
				$html = '';
			}

			return $html;
		}

		private function ex_langs() {
			if (
				isset($this->data['settings_widget']['autoredirect']) && $this->data['settings_widget']['autoredirect']
				&& isset($this->data['settings_widget']['autoredirect_langs_ex']) && !empty($this->data['settings_widget']['autoredirect_langs_ex'])
			) {
				foreach ($this->data['settings_widget']['autoredirect_langs_ex'] as $num => $language_id) {
					$language_id = (int)$language_id;
					if ($this->config->get('config_language_id') == $language_id) {
						$this->data['settings_widget']['autoredirect'] = false;
						$this->data['settings_widget']['bot'] = true;
						break;
					}
				}
			}
		}

		private function bots() {
			if (
				isset($this->http_user_agent)
				&& isset($this->data['settings_widget']['autoredirect']) && $this->data['settings_widget']['autoredirect']
				&& isset($this->data['settings_widget']['bots_status']) && $this->data['settings_widget']['bots_status']
				&& !empty($this->data['settings_widget']['bots'])
			) {

				$string_array_explode = explode(PHP_EOL, trim($this->data['settings_widget']['bots']));

				foreach ($string_array_explode as $num => $token_bot) {
					$token_bot = trim($token_bot);
					if ($token_bot != '' && $token_bot[0] != '#' && stripos($this->http_user_agent, $token_bot) !== false) {
						$this->data['settings_widget']['autoredirect'] = false;
						$this->data['settings_widget']['bot'] = true;
						setcookie('languager', '1', time() + 60 * 60 * 24 * 30, '/', $this->host);
						$this->request->cookie['languager'] = '1';
						break;
					}
				}
			}

			if (isset($this->data['settings_widget']['autoredirect'])  && $this->data['settings_widget']['autoredirect']) {

				if (
					isset($this->data['settings_widget']['bots_status']) && $this->data['settings_widget']['bots_status']
					&& isset($this->data['settings_widget']['bot']) && !$this->data['settings_widget']['bot']
				) {
					$this->data['settings_widget']['autoredirect_delay_mobile'] = 300;
					$this->data['settings_widget']['autoredirect_delay_desktop'] = 300;

					setcookie('languager', '0', time() + 60 * 60 * 24 * 30, '/', $this->host);
					$this->request->cookie['languager'] = '0';
				} else {
					$this->data['settings_widget']['autoredirect_delay_mobile'] = 11000;
					$this->data['settings_widget']['autoredirect_delay_desktop'] = 5000;
				}
			}
		}




		private function getQueryString($exclude = array()) {
			if (!is_array($exclude)) {
				$exclude = array();
			}
			return urldecode(http_build_query(array_diff_key($this->request->get, array_flip($exclude))));
		}

		private function switchLanguage($settings, $language_id, $code) {
			$ajax = false;

			if (isset($this->request->server['HTTP_ACCEPT'])) {

				if (strpos(strtolower($this->request->server['HTTP_ACCEPT']), strtolower('image')) !== false) {

					if (strpos(strtolower($this->request->server['HTTP_ACCEPT']), strtolower('html')) !== false) {
						$ajax = false;
					} else {
						$ajax = true;
					}
				}

				if (strpos(strtolower($this->request->server['HTTP_ACCEPT']), strtolower('js')) !== false) {
					$ajax = true;
				}

				if (strpos(strtolower($this->request->server['HTTP_ACCEPT']), strtolower('json')) !== false) {
					$ajax = true;
				}

				if (strpos(strtolower($this->request->server['HTTP_ACCEPT']), strtolower('ajax')) !== false) {
					$ajax = true;
				}

				if (strpos(strtolower($this->request->server['HTTP_ACCEPT']), strtolower('javascript')) !== false) {
					$ajax = true;
				}
			}

			if (isset($settings['ex_multilang_route']) && $settings['ex_multilang_route'] != '') {
				$ex_multilang_route = $settings['ex_multilang_route'];
				$ex_multilang_route_array = explode(PHP_EOL, $ex_multilang_route);
				if (isset($this->request->get['route'])) {
					foreach ($ex_multilang_route_array as $ex_route) {
						if (trim($ex_route) != '') {
							if (utf8_strpos(utf8_strtolower($this->request->get['route']), trim($ex_route)) !== false) {
								$ajax = true;
							}
						}
					}
				}
			}

			if (isset($settings['ex_multilang_uri']) && $settings['ex_multilang_uri'] != '') {
				$ex_multilang_uri = $settings['ex_multilang_uri'];
				$ex_multilang_uri_array = explode(PHP_EOL, $ex_multilang_uri);
				if (isset($this->request->server['REQUEST_URI'])) {
					foreach ($ex_multilang_uri_array as $ex_uri) {
						if (trim($ex_uri) != '') {
							if (utf8_strpos(utf8_strtolower($this->request->server['REQUEST_URI']), trim($ex_uri)) !== false) {
								$ajax = true;
							}
						}
					}
				}
			}

			if (isset($this->request->server['HTTP_X_REQUESTED_WITH']) && strtolower($this->request->server['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
				if (!$this->jetcache_buildcache) {
					$ajax = true;
				}
			}

			if ($code != '' && !$ajax) {

				$this->config->set('config_language_id', $language_id);
				$this->config->set('config_language', $code);
				$this->session->data['language'] = $code;

				if (isset($this->settings['jazz']) && $this->settings['jazz']) {
					setcookie('language', null, -1, '/', $this->host);
					setcookie('language', $code, time() + 60 * 60 * 24 * 30, '/', $this->request->server['HTTP_HOST']);
				}
			}
		}
		public function set_agoo_hreflang($output) {
			if (isset($output) && !$this->registry->get('admin_work')) {
				if (is_string($output) && strpos($output, '</head>') !== false && (strpos($output, '<link rel="alternate"') === false || strpos($output, "<link rel='alternate'") === false) && is_callable(array($this->document, 'getSCHreflang'))) {
					$sc_hreflang = $this->document->getSCHreflang();
					if (empty($sc_hreflang)) {
						$sc_hreflang = $this->registry->get('SCHreflang');
					}
					if ($sc_hreflang && !empty($sc_hreflang)) {
						foreach ($sc_hreflang as $sc_hreflang_code => $sc_hreflang_array) {

							$output = str_replace("</head>", '
<link rel="alternate" hreflang="' . $sc_hreflang_array['hreflang'] . '" href="' . $sc_hreflang_array['href'] . '" />
</head>', $output);
						}
						$this->document->setSCHreflang(array());
					}
				}
			}
			return $output;
		}

		public function shortcodes($output) {

			if (!isset($this->settings['access']) || !$this->settings['access']) {
				return $output;
			}

			$langmark_multi = $this->getMulti();

			if (isset($output) && is_string($output) && !$this->registry->get('admin_work')) {

				if (!empty($langmark_multi['shortcodes'])) {
					$output = str_replace(array("\r\n", "\r", "\n", PHP_EOL), '{{BR}}', $output);

					foreach ($langmark_multi['shortcodes'] as $num => $shortcode) {
						if ($shortcode['out'] == ' ') $shortcode['out'] = '';

						$shortcode['in'] = str_replace(array("\r\n", "\r", "\n", PHP_EOL), '{{BR}}', $shortcode['in']);
						$shortcode['out'] = str_replace(array("\r\n", "\r", "\n", PHP_EOL), '{{BR}}', $shortcode['out']);
						$output = str_replace(html_entity_decode($shortcode['in'], ENT_QUOTES, 'UTF-8'), html_entity_decode($shortcode['out'], ENT_QUOTES, 'UTF-8'), $output);
					}
					$output = str_replace('{{BR}}', PHP_EOL, $output);
				}
			}
			return $output;
		}


		public function after($seo_url, $seo_url_route = '') {

			if (!is_string($seo_url)) {
				return $seo_url;
			}

			if (!isset($this->settings['access']) || !$this->settings['access']) {
				return $seo_url;
			}

			if (isset($this->request->get['route'])) {
				$route = $this->request->get['route'];
			} else {
				$route = 'common/home';
			}

			if (SC_VERSION < 30) {
				if ($seo_url_route != '') {

					if (isset($this->settings['ex_url_route']) && $this->settings['ex_url_route'] != '') {
						$ex_url_route = $this->settings['ex_url_route'];
						$ex_url_route_array = explode(PHP_EOL, $ex_url_route);

						foreach ($ex_url_route_array as $ex_route) {
							if (trim($ex_route) != '') {
								if (utf8_strpos(utf8_strtolower($seo_url_route), trim($ex_route)) !== false) {
									$ajax = true;
									return $seo_url;
								}
							}
						}
					}
				}
			}

			if (isset($this->settings['ex_url_route']) && $this->settings['ex_url_route'] != '') {
				$ex_url_route = $this->settings['ex_url_route'];
				$ex_url_route_array = explode(PHP_EOL, $ex_url_route);

				if ($route != '') {
					foreach ($ex_url_route_array as $ex_route) {
						if (trim($ex_route) != '') {
							if (utf8_strpos(utf8_strtolower($route), trim($ex_route)) !== false) {
								$ajax = true;
								return $seo_url;
							}
						}
					}
				}
			}

			if (isset($this->settings['ex_url_uri']) && $this->settings['ex_url_uri'] != '') {
				$ex_url_uri = $this->settings['ex_url_uri'];
				$ex_url_uri_array = explode(PHP_EOL, $ex_url_uri);

				foreach ($ex_url_uri_array as $ex_uri) {
					if (trim($ex_uri) != '') {
						if (utf8_strpos(utf8_strtolower($seo_url), trim($ex_uri)) !== false) {
							$ajax = true;
							return $seo_url;
						}
					}
				}
			}

			$seo_url = $this->commonhome($seo_url);
			$seo_url = $this->seourl($seo_url);
			$seo_url = $this->pagination($seo_url);
			$seo_url = $this->twoslaches($seo_url);
			$this->settitledesc();

			return $seo_url;
		}


		private function settitledesc() {

			$langmark_multi = $this->getMulti();
			$langmark_settings = $this->config->get('asc_langmark_' . $this->config->get('config_store_id'));
			if (isset($langmark_settings['pagination']) && $langmark_settings['pagination']) {

				if ($this->registry->get('langmark_page') != '') {

					if (!$this->registry->get('langmark_title')) {
						$title = $this->document->getTitle();
						if ($title != '' && utf8_strpos($title, $langmark_multi['pagination_title'] . ' ' . $this->registry->get('langmark_page')) === false) {
							$this->document->setTitle($title .  ' ' . $langmark_multi['pagination_title'] . ' ' . $this->registry->get('langmark_page'));
							// $this->registry->set('langmark_title', true);
						}
					}

					if (!$this->registry->get('langmark_desc')) {
						$description = $this->document->getDescription();
						if ($description != '' && utf8_strpos($description, $langmark_multi['pagination_title'] . ' ' . $this->registry->get('langmark_page')) === false) {
							$this->document->setDescription($description .  ' ' . $langmark_multi['pagination_title'] . ' ' . $this->registry->get('langmark_page'));
							//$this->registry->set('langmark_desc', true);
						}
					}
				}
			}
		}


		private function pagination($seo_url) {

			$langmark_multi = $this->getMulti();
			$langmark_settings = $this->config->get('asc_langmark_' . $this->config->get('config_store_id'));
			if (isset($langmark_settings['pagination']) && $langmark_settings['pagination']) {

				$modules_params_1 = $seo_url;
				if (isset($modules_params_1) && is_string($modules_params_1) && strpos($modules_params_1, 'page=') !== false) {


					$component = parse_url(str_replace('&amp;', '&', $seo_url));

					if (isset($component['path'])) {
						$component['path'] = str_replace('/index.php', '', $component['path']);
					} else {
						$component['path'] = '';
					}
					if (substr($component['path'], -1) == '/') {
						$slash_close = '/';
					} else {
						$slash_close = '';
					}
					$this->registry->set('langmark_slash_close', $slash_close);
					$data_array = array();
					if (isset($component['query'])) {
						parse_str($component['query'], $data_array);
					}

					if (count($data_array)) {
						/*** added code seo paging http://opencartadmin.com ***/
						$seo_url = '';
						$paging = '';
						$devider = '/'; // :)
						foreach ($data_array as $key => $value) {

							if ($key == $langmark_settings['pagination_prefix'] || $key == 'page') {
								$key = $langmark_settings['pagination_prefix'];
								if ($devider != '/') {
									$paging = '/' . $key . "-" . $value;
								} else {
									$paging = $key . "-" . $value;
								}

								unset($data_array[$key]);
								if (isset($data_array['page'])) {
									unset($data_array['page']);
								}
							}
						}
						// WTF?

						if (isset($modules_params_1) && is_string($modules_params_1) && strpos($modules_params_1, 'page={page}') !== false) {
							//  $paging = '';
						}

						if (trim($paging, '/') == $langmark_settings['pagination_prefix'] . '-1') {
							$paging = '';
						}

						if (count($data_array)) {
							$seo_url .= $paging . '?' . urldecode(http_build_query($data_array, '', '&amp;'));
						} else {
							$seo_url .= $paging;
						}

						if (trim($component['path']) == '') $mydel = "";
						else $mydel = '/';
						$seo_url = $component['scheme'] . '://' . $component['host'] . '/' . trim($component['path'], '/') . $mydel . $seo_url;

						if ($paging != '') {
							$seo_url = rtrim($seo_url, '/');
						}
					}
				}
				/* for pagination */
			}
			return $seo_url;
		}

		private function seourl($seo_url) {

			if (isset($this->request->get['route'])) {
				$route = $this->request->get['route'];
			} else {
				$route = 'common/home';
			}

			$seo_url_parse = parse_url(str_replace('&amp;', '&', $seo_url));

			// From /system/library/agoo/multilang.php or from switch index()
			$langmark_multi = $this->getMulti();
			if (!is_array($langmark_multi)) {
				return $seo_url;
			}

			if (!isset($seo_url_parse['scheme']) || (isset($seo_url_parse['scheme']) && $seo_url_parse['scheme'] == 'https')) {
				$conf_ssl = $this->config->get('config_ssl');
				if (!$conf_ssl) $conf_ssl = HTTPS_SERVER;
				$config_url = $conf_ssl;
				if (!isset($seo_url_parse['scheme'])) {
					$seo_url_parse['scheme'] = 'https';
				}
			} else {
				$conf_url = $this->config->get('config_url');
				if (!$conf_url) $conf_url = HTTP_SERVER;
				$config_url = $conf_url;
			}

			$protocol = $seo_url_parse['scheme'] . '://';

			if (strlen($seo_url) < strlen($config_url)) {
				$seo_url = $config_url;
			}

			$seo_url_len = str_replace($config_url, '', $seo_url);

			if (isset($langmark_multi['prefix']) && strlen($seo_url_len) > 0 && substr($langmark_multi['prefix'], -1) != '/' && (isset($seo_url_len[0]) && $seo_url_len[0] != '?')) {
				$divider = '/';
			} else {
				$divider = '';
			}

			// Error fo generate URL other stores
			if ($seo_url == $config_url && $langmark_multi['main_prefix_status']) {
				if (isset($langmark_multi['store_id']) && $langmark_multi['store_id'] != $this->config->get('config_store_id') && isset($this->data['stores'][$langmark_multi['store_id']]['url'])) {
					$seo_url = $this->data['stores'][$langmark_multi['store_id']]['url'];
				} else {
					$seo_url = $config_url;
				}
			} else {
				if (isset($langmark_multi['prefix'])) {
					$seo_url = str_replace($config_url, $protocol . $langmark_multi['prefix'] . $divider, $seo_url);
				} else {
					$this->log->write($seo_url . ' -> ' . json_encode($langmark_multi));
				}
			}

			return $seo_url;
		}

		private function twoslaches($seo_url) {
			if (isset($this->settings['two_status']) && $this->settings['two_status']) {
				$seo_url = preg_replace('/(?<!^[http:]|[https:])[\/]{2,}/', '/', trim($seo_url));
			}
			return $seo_url;
		}

		private function commonhome($seo_url) {

			if (isset($this->settings['commonhome_status']) && $this->settings['commonhome_status']) {

				if (strpos(strtolower($seo_url), 'index.php?route=common/home') !== false) {
					if ((utf8_strpos(utf8_strtolower($seo_url), '&') !== false) || (utf8_strpos(utf8_strtolower($seo_url), '&amp;') !== false)) {
						$seo_url = str_replace('&amp;', '&', $seo_url);
						$seo_url = str_replace('&', '&amp;', $seo_url);
						$seo_url = str_replace('index.php?route=common/home&amp;', '?', $seo_url);
					} else {
						$seo_url = str_replace('index.php?route=common/home', '', $seo_url);
					}
				}
			}
			return $seo_url;
		}
	}
}
