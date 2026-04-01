<?php
/* All rights reserved belong to the module, the module developers http://opencartadmin.com */
// https://opencartadmin.com © 2011-2019 All Rights Reserved
// Distribution, without the author's consent is prohibited
// Commercial license
if (!class_exists('ControllerRecordAddrewrite', false)) {
class ControllerRecordAddrewrite extends Controller
{
	protected $data;
	private  $ascp_settings;

    public function add_construct($registry) {

        parent::__construct($registry);

        $this->ascp_settings = $this->config->get('ascp_settings');

        if (isset($this->ascp_settings['latest_widget_status']) && $this->ascp_settings['latest_widget_status']) {
			if (!class_exists('ControllerCommonSeoBlog', false) &&
				(class_exists('ControllerCommonSeoUrl', false) ||
				 class_exists('ControllerCommonSeoPro', false) ||
				 class_exists('ControllerStartupSeoUrl', false) ||
				 class_exists('ControllerStartupMultimerchSeoUrl', false) ||
				 class_exists('ControllerStartupSeoPro', false))
				 && !$this->registry->get('admin_work')
				 ) {

					if ($this->registry->get('url_old')) {
						$url = $this->registry->get('url_old');
						$class = get_class($url);
					} else {
						$url = $this->url;
						$class = get_class($url);
					}

					$reflection = new ReflectionClass($class);
					$priv_attr  = $reflection->getProperties(ReflectionProperty::IS_PRIVATE);

					if ($reflection->hasProperty('rewrite')) {
						$hook = 'rewrite';
					} else {
						$hook = 'hook';
					}

	                if ($reflection->hasProperty($hook)) {
						$reflectionProperty = $reflection->getProperty($hook);
						$reflectionProperty->setAccessible(true);
						$data_private = $reflectionProperty->getValue($url);

	                    if (is_array($data_private) && count($data_private) > 0) {
							$this->addrouter();
	                    }
	                    unset($data_private);
	                    unset($reflectionProperty);
					} else {
	                   $this->addrouter();
					}
	                unset($url);
	                unset($class);
	                unset($reflection);
			}
		}
		unset($this->ascp_settings);
    }

	public function addrouter() {

        $file = DIR_APPLICATION . 'controller/common/seoblog.php';

        $original_file = $file;

        if (function_exists('modification')) {
        	$file = modification($file);
        }
		if (class_exists('VQMod',false)) {
			if (!isset(VQMod::$_virtualMFP)) {
				if (VQMod::$directorySeparator) {
					if (strpos($file,'vq2-')!==FALSE) {
					} else {
						if ($original_file != $file) {
							$file = VQMod::modCheck($file, $original_file);
						} else {
							$file = VQMod::modCheck($original_file);
						}
					}
				}
			}
		}

		if (file_exists($file)) {
			require_once($file);
	    }

		//require_once(DIR_APPLICATION . 'controller/common/seoblog.php');

		$seoBlog = new ControllerCommonSeoBlog($this->registry);
		if ($this->config->get('config_seo_url') && !$this->config->get('sc_ar_'.strtolower('ControllerCommonSeoBlog'))) {
			$this->url->addRewrite($seoBlog);
			$this->config->set('sc_ar_'.strtolower('ControllerCommonSeoBlog'), true);
		}

		if (!isset($this->ascp_settings['safe_loading']) || !$this->ascp_settings['safe_loading']) {
        	$seoBlog->router();
		}
		unset($seoBlog);
	}
}
}
