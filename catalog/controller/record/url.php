<?php
/* All rights reserved belong to the module, the module developers http://opencartadmin.com */
// http://opencartadmin.com © 2011-2019 All Rights Reserved
// Distribution, without the author's consent is prohibited
// Commercial license
if (!class_exists('ControllerRecordUrl', false)) {
class ControllerRecordUrl extends Controller
{
	public function url_construct() {

		$sc_ver = VERSION; if (!defined('SC_VERSION')) define('SC_VERSION', (int) substr(str_replace('.', '', $sc_ver), 0, 2));

		if ($this->config->get('ascp_settings') != '') {
			$this->seocms_settings = $this->config->get('ascp_settings');
		} else {
			$this->seocms_settings = Array();
		}

        if (SC_VERSION > 23) {
        	$this->token_name = 'user_token';
        } else {
        	$this->token_name = 'token';
        }

  		$this->setUrlRegistry($this->registry);

        return true;
	}

	public function index() {
		return true;
	}

	public function setUrlRegistry($registry) {
		if (is_callable(array('Url', 'seocms_setRegistry'))) {
			$this->url->seocms_setRegistry($registry);
		}
	}

	public function before($rewrite) {
		if ($this->config->get('sc_ar_'.strtolower(get_class($rewrite)))) {
			return;
		}
        $this->config->set('sc_ar_'.strtolower(get_class($rewrite)), true);
	}

	public function after($modules) {

        if (!is_string($modules)) {
        	return $modules;
        }

		if (
			isset($this->seocms_settings['latest_widget_status']) && $this->seocms_settings['latest_widget_status'] &&
			!$this->registry->get('seocms_url_alter') &&
			!class_exists('ControllerCommonSeoBlog', false) &&
			(class_exists('ControllerCommonSeoUrl', false) ||
			 class_exists('ControllerCommonSeoPro', false) ||
			 class_exists('ControllerStartupSeoUrl', false) ||
			 class_exists('ControllerStartupSeoPro', false))
			 && !$this->registry->get('admin_work')
             && !$this->config->get('sc_ar_'.strtolower('ControllerCommonSeoBlog'))
			 ) {
			agoo_cont('record/addrewrite', $this->registry);
			$this->controller_record_addrewrite->add_construct($this->registry);
		}

		$this->registry->set('url_work', false);

		return $modules;
	}

}
}
