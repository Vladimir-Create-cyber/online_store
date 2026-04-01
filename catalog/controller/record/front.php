<?php
/* All rights reserved belong to the module, the module developers http://opencartadmin.com */
// https://opencartadmin.com © 2011-2019 All Rights Reserved
// Distribution, without the author's consent is prohibited
// Commercial license
if (!class_exists('ControllerRecordFront', false)) {
class ControllerRecordFront extends Controller
{
	public function __construct($registry) {
		parent::__construct($registry);

		if (version_compare(phpversion(), '5.3.0', '<') == true) {
			exit('PHP5.3+ Required');
		}
		if (isset($this->request->get['route']) && substr($this->request->get['route'], 0, 4) == 'api/') {
			return;
		}
		if (!defined('VERSION')) return; if (!defined('SC_VERSION')) define('SC_VERSION', (int)substr(str_replace('.','',VERSION), 0,2));
		require_once(DIR_SYSTEM . 'helper/seocmsprofunc.php');
	   	require_once(DIR_SYSTEM . 'library/exceptionizer.php');

		$this->install();

	}

	public function install() {

		if (defined('DIR_CATALOG')) {
			$path_catalog = DIR_CATALOG;
			$this->registry->set('admin_work', true);
		} else {
			$path_catalog = DIR_APPLICATION;
		}

		$file  = $path_catalog . 'controller/record/seocmslib.php';
		$original_file = $file;

		if (function_exists('modification')) {
			$file = modification($file);
		}

		if (class_exists('VQMod',false)) {
			if (!isset(VQMod::$_virtualMFP)) {
				if (VQMod::$directorySeparator) {
					if (strpos($file,'vq2-')!==FALSE) {
					} else {
						if (version_compare(VQMod::$_vqversion,'2.5.0','<')) {
							//trigger_error("You are using an old VQMod version '".VQMod::$_vqversion."', please upgrade your VQMod!");
							//exit;
						}
						if ($original_file != $file) {
							$file = VQMod::modCheck($file, $original_file);
						} else {
							$file = VQMod::modCheck($original_file);
						}
					}
				}
			}
		}

		require_once($file);


		$seocmslib = new ControllerRecordSeocmslib($this->registry);
        $this->registry->set('seocmslib', $seocmslib);
		if (SC_VERSION < 20) {
        	$this->config->sc_registry($this->registry);
        }


		if ($this->config->get('ascp_settings') != '') {
			$settings_general = $this->config->get('ascp_settings');
		} else {
			$settings_general = Array();
		}

		if (!$this->registry->get('admin_work')) {
				$this->seocmslib->cont('record/url');
				$this->controller_record_url->url_construct();
   		}

		if (isset($settings_general['seocms_url_alter']) && $settings_general['seocms_url_alter']) {
			$this->registry->set('seocms_url_alter', true);
		} else {
			$this->registry->set('seocms_url_alter', false);
		}

		if (!class_exists('ModelToolImage', false)) {
			$this->load->model('tool/image');
		}

		if (!class_exists('ModelDesignLayout', false)) {
			$this->load->model('design/layout');
		}

        $this->registry->set('config_ascp_settings', $settings_general);

		if (!$this->registry->get('admin_work')) {
			$loader_old = $this->registry->get('load');
			$this->registry->set('load_old', $loader_old);
			loadlibrary('agoo/loader');
			$agooloader = new agooLoader($this->registry);
			$this->registry->set('load', $agooloader);
		}

		if (!class_exists('agooCache', false)) {
			$Cache = $this->registry->get('cache');
			$this->registry->set('cache_old', $Cache);
			loadlibrary('agoo/cache');
			$jcCache = new agooCache($this->registry);
			$jcCache->agooconstruct($settings_general);
			$this->registry->set('cache', $jcCache);
		}
	}
}
}