<?php
/* All rights reserved belong to the module, the module developers http://opencartadmin.com */
// https://opencartadmin.com © 2011-2022 All Rights Reserved
// Distribution, without the author's consent is prohibited
// Commercial license
if (!class_exists('ControllerRecordSeocmslib', false)) {
class ControllerRecordSeocmslib extends Controller
{
	public $theme_folder;
    protected $api_seocmslib = false;
    protected $server_protocol;
    protected $route;
    protected $tempalte;
    protected $data;

	public function __construct($registry) {
		parent::__construct($registry);
		if (SC_VERSION > 15 && SC_VERSION < 30) {
			$this->load->model('extension/module');
		}
		$this->theme_folder = $this->get_theme_folder();
	}

	private function seocmslib_api() {
		if (!$this->api_seocmslib) {
			if (isset($this->registry->get('request')->get['route'])) {
				$this->route = preg_replace('/[^a-zA-Z0-9_\/]/', '', (string)$this->registry->get('request')->get['route']);
			} else {
				$this->route = 'common/home';
			}
			if (isset($this->route) && stripos($this->route, 'record/seocmslib') !== false) {
				$this->api_seocmslib = false;
				if (isset($this->registry->get('request')->server['SERVER_PROTOCOL']) && $this->registry->get('request')->server['SERVER_PROTOCOL'] != '') {
					$this->server_protocol = $this->registry->get('request')->server['SERVER_PROTOCOL'];
				} else {
					$this->server_protocol = 'HTTP/1.1';
				}
				header($this->server_protocol . ' ' . '404 Not Found');
				header('Status: 404 Not Found');
				exit();
			}
			$this->api_seocmslib = true;
		}
	}

	public function cont($cont) {
		$this->seocmslib_api();
		$cont = preg_replace('/[^a-zA-Z0-9_\/]/', '', (string)$cont);
		if (!defined('DIR_CATALOG')) {
			$dir_catalog = DIR_APPLICATION;
		} else {
			$dir_catalog = DIR_CATALOG;
		}
       	$file  = $dir_catalog . 'controller/' . str_replace('..', '', $cont) . '.php';
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

		if (file_exists($file)) {
           $this->cont_loading($cont, $file);
           return true;
		} else {
			$file  = DIR_APPLICATION . 'controller/' . str_replace('..', '', $cont) . '.php';
            if (file_exists($file)) {
             	$this->cont_loading($cont, $file);
             	return true;
            } else {
				return false;
			}
		}
	}

	private function cont_loading($cont, $file) {
			$class = 'Controller' . preg_replace('/[^a-zA-Z0-9]/', '', $cont);
			include_once($file);
			$this->registry->set('controller_' . str_replace('/', '_', $cont), new $class($this->registry));
	}

	public function get_theme_folder() {
		$this->seocmslib_api();
		if (SC_VERSION > 21 && (!$this->config->get('config_template') || is_array($this->config->get('config_template')) || $this->config->get('config_template') == '')) {
             if (SC_VERSION > 23) {
             	$theme_folder = $this->config->get('theme_' . $this->config->get('config_theme').'_directory');
             } else {
             	$theme_folder = $this->config->get($this->config->get('config_theme').'_directory');
             }
			return $theme_folder;
		} else {
			return $this->config->get('config_template');
		}
	}

	public function template($name = '') {
		$this->seocmslib_api();
		if ($name != '') {
			if (SC_VERSION > 23) {
				$ext = '.twig';
			} else {
				$ext = '.tpl';
			}
			if (file_exists(DIR_TEMPLATE . $this->get_theme_folder() . '/template/' . $name . $ext)) {
				if (SC_VERSION > 23) {
					//$template = $this->get_theme_folder() . '/template/' . $name;
					$template =  $name;
				} else {
					if (SC_VERSION < 23 && SC_VERSION != 22) {
						$template = $this->get_theme_folder() . '/template/' . $name . $ext;
					} else {
						$template = $name . $ext;
					}
				}
			} else {
				if (SC_VERSION > 23) {
					//$template = 'default/template/' . $name;
					$template = $name;
				} else {
					if (SC_VERSION < 23 && SC_VERSION != 22) {
						$template = 'default/template/' . $name . $ext;
					} else {
						$template = $name . $ext;
					}
				}
			}

         	return str_replace('..', '', $template);
		} else {
			return '';
		}
	}

    public function content($template, $data, $twig = true) {
    	$this->seocmslib_api();
		$this->template = $template;
		$this->data = $data;

		if (SC_VERSION < 20) {
			$content = $this->render();
		} else {

			if (SC_VERSION > 23 && !$twig) {
		        $template_engine = $this->config->get('template_engine');
			    $this->config->set('template_engine', 'template');
			    $template_directory = $this->registry->get('config')->get('template_directory');
                if (!file_exists(DIR_TEMPLATE . $template_directory . $this->template)) {
				    $this->registry->get('config')->set('template_directory', 'default/template/');
			    }
		    }

			$content = $this->load->view($this->template, $this->data);

			if (SC_VERSION > 23 && !$twig) {
				$this->config->set('template_engine', $template_engine);
				$this->registry->get('config')->set('template_directory', $template_directory);
		    }

		}

        return $content;
    }

	public function resizeme($filename, $width, $height, $type = true, $copy = true) {
		$this->seocmslib_api();
 		if (!class_exists('PhpThumbFactory', false)) {
			require_once(DIR_SYSTEM . 'library/image/ThumbLib.php');
		}
		if (!class_exists('PHP_Exceptionizer', false)) {
			if (function_exists('modification')) {
				require_once(modification(DIR_SYSTEM . 'library/exceptionizer.php'));
			} else {
				require_once(DIR_SYSTEM . 'library/exceptionizer.php');
			}
		}
        $filename = str_replace('../', '', $filename);
        $http_image = getHttpImage($this);

		if ($type) {
			$asaptive_path = 'adaptive/';
		} else {
			$asaptive_path = '';
		}

        $dir_image = DIR_IMAGE;
        $ok = false;

		if (!file_exists($dir_image . $filename) || !is_file($dir_image . $filename)) {
			return false;
		}

		$info      = pathinfo($filename);
		$extension = $info['extension'];
		$old_image = $filename;
		$new_image = 'cache/'.$asaptive_path . utf8_substr($filename, 0, utf8_strrpos($filename, '.')) . '-' . $width . 'x' . $height . '.' . $extension;
		if (!file_exists($dir_image . $new_image) || (filemtime($dir_image . $old_image) > filemtime($dir_image . $new_image))) {
			$path = '';
			$directories = explode('/', dirname(str_replace('../', '', $new_image)));
			foreach ($directories as $directory) {
				$path = $path . '/' . $directory;
				if (!file_exists($dir_image . $path)) {
					@mkdir($dir_image . $path, 0777);
				}
			}

			list($width_orig, $height_orig) = getimagesize($dir_image . $old_image);
			if (($width_orig != $width || $height_orig != $height) || !$copy) {
		  		//********* code *************
            	$exceptionizer = new PHP_Exceptionizer(E_ALL);
				try {
					if ($type) {
						$thumb = PhpThumbFactory::create($dir_image . $old_image, Array('resizeUp' => true));
						$ok = $thumb->adaptiveResize($width, $height)->save($dir_image . $new_image);
					} else {
						//$ok = $thumb->resize($width, $height)->save($dir_image . $new_image);
	                    // opencart standart
						$image = new Image($dir_image . $old_image);
						$image->resize($width, $height);
						$image->save($dir_image . $new_image);
						$ok = true;
					}
				}  catch (E_WARNING $e) {
					return '';
			    }


		        //********* code *************
			} else {
				$ok =  copy($dir_image . $old_image, $dir_image . $new_image);
			}

			if ($ok) {
				 return $http_image.$new_image;
			}  else {
				 return '';
			}
		} else {
			 return $http_image.$new_image;
		}

    }

	public function resizeavatar($filename, $filename_original, $width, $height, $type = true, $copy = false) {
		$this->seocmslib_api();
 		$filename = str_replace('../', '', $filename);
 		$filename_original = str_replace('../', '', $filename_original);


 		if (!class_exists('PhpThumbFactory', false)) {
			require_once(DIR_SYSTEM . 'library/image/ThumbLib.php');
		}
		if (!class_exists('PHP_Exceptionizer', false)) {
			if (function_exists('modification')) {
				require_once(modification(DIR_SYSTEM . 'library/exceptionizer.php'));
			} else {
				require_once(DIR_SYSTEM . 'library/exceptionizer.php');
			}
		}
        $http_image = getHttpImage($this);
        $ok = false;
		if (!file_exists(DIR_IMAGE . $filename) || !is_file(DIR_IMAGE . $filename)) {
			return $ok;
		}

		$info      = pathinfo($filename_original);
		$extension = $info['extension'];
		$old_image = $filename;
        $new_image = $filename_original;

		if (!file_exists(DIR_IMAGE . $new_image) || (filemtime(DIR_IMAGE . $old_image) > filemtime(DIR_IMAGE . $new_image))) {
				$path        = '';
				$directories = explode('/', dirname(str_replace('../', '', $new_image)));
				foreach ($directories as $directory) {
					$path = $path . '/' . $directory;
					if (!file_exists(DIR_IMAGE . $path)) {
						@mkdir(DIR_IMAGE . $path, 0777);
					}
				}

				list($width_orig, $height_orig) = getimagesize(DIR_IMAGE . $old_image);
				if (($width_orig != $width || $height_orig != $height) || !$copy) {
                    $exceptionizer = new PHP_Exceptionizer(E_ALL);
		 			try {
			  			//********* code *************
			  			$thumb = PhpThumbFactory::create(DIR_IMAGE . $old_image, Array('resizeUp'=> true));
						if ($type)
							$ok = $thumb->adaptiveResize($width, $height)->save(DIR_IMAGE . $new_image);
						else
							$ok = $thumb->resize($width, $height)->save(DIR_IMAGE . $new_image);
			            //********* code *************

		            }  catch (E_WARNING $e) {
						if (file_exists(DIR_IMAGE . $filename)) {
							unlink(DIR_IMAGE . $filename);
						}
						if (file_exists(DIR_IMAGE . $new_image)) {
							unlink(DIR_IMAGE . $new_image);
						}
						return '';
			        }

				} else {
					copy(DIR_IMAGE . $old_image, DIR_IMAGE . $new_image);
					$ok = true;
				}

			 if ($ok)
			 return $http_image.$new_image;
			 else
			 return '';
		}

    }

	public function getLayoutIdSeocms($route) {
		$this->seocmslib_api();
        $layout_id = false;

		$this->load->model('design/bloglayout');

		if ($route == 'record/blog' && isset($this->request->get['blog_id'])) {
			$path = explode('_', (string) $this->request->get['blog_id']);
			$layout_id = $this->model_design_bloglayout->getBlogLayoutId(end($path));
			$layout_id = (int)$layout_id;
		}
		if ($route == 'record/record' && isset($this->request->get['record_id'])) {
			$layout_id = $this->model_design_bloglayout->getRecordLayoutId((int)$this->request->get['record_id']);
		}
        if ($layout_id) {
        	return $layout_id;
        } else {
	        $sql = "SELECT * FROM " . DB_PREFIX . "layout_route WHERE '" . $this->db->escape($route) . "' LIKE CONCAT(route, '%') AND `route`<>'' AND store_id = '" . (int) $this->config->get('config_store_id') . "' ORDER BY route DESC LIMIT 1";
            $query = $this->db->query($sql);
			if ($query->num_rows) {
				return $query->row['layout_id'];
			} else {
				return false;
			}
        }
	}

	public function sc_getLayout($layout_id) {
		$this->seocmslib_api();
		if ($this->registry->get('sc_layout_id')) {
			$layout_id = (int)$this->registry->get('sc_layout_id');
		}
		return $layout_id;
	}


	public function sc_getLayoutModules($layout_id, $position, $data) {
		$this->seocmslib_api();
		$blog_module = $this->config->get('blog_module');
		$modules = $blog_module;
		if ($this->registry->get('sc_layout_id')) {
			$layout_id = $this->registry->get('sc_layout_id');
		}
		$flag_layout = false;
		$request_url = ltrim($this->request->server['REQUEST_URI'], '/');
		if ($modules && is_array($modules)) {
			foreach ($modules as $num => $module) {
				if (isset($module['layout_id']) && is_array($module['layout_id'])) {
					if (is_array($module['layout_id']) || !isset($module['layout_id'])) {

						if (isset($module['url']) && trim($module['url']) != '') {
							if (isset($module['url_template'])) {
								$url_status = $module['url_template'];
							} else {
								$url_status = false;
							}
							if ($url_status == 1) {
								$pos = utf8_strpos($request_url, trim($module['url']));
								if ($pos === false) {
								} else {
									$modules[$num]['layout_id'] = $layout_id;
								}
							} else {
								if (trim($module['url']) == $request_url) {
									$modules[$num]['layout_id'] = $layout_id;
								}
							}
						} else {
							if ($module['status'] > 0) {
								if ($layout_id) {
									foreach ($module['layout_id'] as $key => $value) {
										if ($value == $layout_id) {
											$modules[$num]['layout_id'] = $layout_id;
										}
									}
								}
							}
						}
					}
				}
			}
		}
		$this->config->set('blog_module', $modules);
		$blog_module = $modules;

		if (isset($position)) {
			$position  = $position;
		} else {
			$position  = false;
		}

		if (is_array($blog_module) && !empty($blog_module)) {
		    foreach ($blog_module as $num => $val) {
		    	if (isset($val['position'])) {
		        	if ($position == $val['position']) {
		        	  	if (!is_array($val['layout_id'])) {
		        	     	if ($layout_id == $val['layout_id'] && $val['status']) {
		                       $val['code'] = 'blog';
		                       $this->registry->get('config')->set('blog_status', true);
		                       $this->registry->set('blog_position', $val['position'] );
		            	   	   $data[] = $val;
		        	   		}
		        	  	}
		        	}
		    	}
		    }
		}

     	if (is_array($data)) usort($data, 'commd');

    	return $data;

	}


/*
в config php

  	после
  	public function get($key) {

    вставить
		if ($key == 'blog_module') {
			if (isset($this->data[$key])) {
				$modules = $this->data[$key];
			} else  {
				$modules = null;
			}
            if ($modules != null && isset($this->data['seocmslib'])) {
            	$modules = $this->data['seocmslib']->config_15($modules);
            	return $modules;
            }
        }

*/
	public function config_15($modules) {
            $this->seocmslib_api();
			$settings_general = $this->registry->get('config_ascp_settings');
			/*
			if (isset($settings_general['layout_url_status'])) {
				$url_status = $settings_general['layout_url_status'];
			} else {
				$url_status = false;
			}
			*/
			unset($settings_general);
            $request_url = ltrim($this->request->server['REQUEST_URI'], '/');
			$layout_id = $this->registry->get('agoo_layout_id');

			if (!$layout_id) {
				$layout_id = $this->getLayoutId_15();
			}

			if ($modules && is_array($modules)) {
				foreach ($modules as $num => $module) {
					if (isset($module['layout_id']) && is_array($module['layout_id'])) {
						if (is_array($module['layout_id']) || !isset($module['layout_id'])) {

							if (isset($module['url']) && trim($module['url']) != '') {
								if (isset($module['url_template'])) {
									$url_status = $module['url_template'];
								} else {
									$url_status = false;
								}
								if ($url_status == "1") {
									$pos = utf8_strpos($request_url, trim($module['url']));
									if ($pos === false) {
									} else {
										$modules[$num]['layout_id'] = $layout_id;
									}
								} else {
									if (trim($module['url']) == $request_url) {
										$modules[$num]['layout_id'] = $layout_id;
									}
								}
							} else {
								if ($module['status'] > 0) {
									if ($layout_id) {
										foreach ($module['layout_id'] as $key => $value) {
											if ($value == $layout_id) {
												$modules[$num]['layout_id'] = $layout_id;
											}
										}
									}
								}
							}
						}
					}
				}
			}

		return $modules;
	}

	private function getLayoutId_15($route = '') {
		$this->load->model('design/layout');
		$this->load->model('design/bloglayout');
		$this->load->model('catalog/category');
		$this->load->model('catalog/product');
		$this->load->model('catalog/information');

        $layout_id = 0;

		if ($route == '') {
			if (isset($this->request->get['route'])) {
				$route = (string) $this->request->get['route'];
			} else {
				$route = 'common/home';
			}
		}

		if ($route == 'product/category' && isset($this->request->get['path'])) {
			$path      = explode('_', (string) $this->request->get['path']);
			$layout_id = $this->model_catalog_category->getCategoryLayoutId((int)end($path));
		}
		if ($route == 'product/product' && isset($this->request->get['product_id'])) {
			$layout_id = $this->model_catalog_product->getProductLayoutId((int)$this->request->get['product_id']);
		}
		if ($route == 'information/information' && isset($this->request->get['information_id'])) {
			$layout_id = $this->model_catalog_information->getInformationLayoutId((int)$this->request->get['information_id']);
		}
		if ($route == 'product/manufacturer/info' && isset($this->request->get['manufacturer_id'])) {
			//if (is_callable(array($this->model_catalog_manufacturer, 'getManufacturerLayoutId'))) {
			if (method_exists($this->model_catalog_manufacturer,'getManufacturerLayoutId')) {
				$this->load->model('catalog/manufacturer');
				$layout_id = $this->model_catalog_manufacturer->getManufacturerLayoutId($this->request->get['manufacturer_id']);
			}
		}
		if ($route == 'record/blog' && isset($this->request->get['blog_id'])) {
			$path      = explode('_', (string) $this->request->get['blog_id']);
			$layout_id = $this->model_design_bloglayout->getBlogLayoutId((int)end($path));
		}
		if ($route == 'record/record' && isset($this->request->get['record_id'])) {
			$layout_id = $this->model_design_bloglayout->getRecordLayoutId((int)$this->request->get['record_id']);
		}
		if (!$layout_id) {
			$layout_id = $this->model_design_layout->getLayout($route);
		}

		if (!$layout_id) {
			$layout_id = $this->config->get('config_layout_id');
		}
		if ($route == '' && $layout_id) {
			$this->registry->set('agoo_layout_id', $layout_id);
		}

		return $layout_id;
	}

	public function getRouteByLayoutId($layout_id) {
   		$this->seocmslib_api();
   		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "layout_route WHERE layout_id='" . (int)$layout_id . "' AND store_id = '" . (int) $this->config->get('config_store_id') . "' ORDER BY route ASC LIMIT 1");
        if ($query->num_rows) {
			return $query->row['route'];
		} else {
			return false;
		}

	}

	public function model($model) {
        $this->seocmslib_api();
	    $dir_application = DIR_APPLICATION;

		$model = str_replace('../', '', (string) $model);
		$file  = $dir_application . 'model/agoo/' . $model . '.php';

        if (!file_exists($file)) {
			$file = $dir_application . 'model/' . $model . '.php';
			if (!file_exists($file)) {
				if (defined('DIR_CATALOG')) {
					$dir_application = DIR_CATALOG;
				}
			}
		}

        $file  = $dir_application . 'model/agoo/' . $model . '.php';
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

		$class = 'agooModel' . preg_replace('/[^a-zA-Z0-9]/', '', $model);
		if (!file_exists($file)) {
			$file  = $dir_application . 'model/' . $model . '.php';
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

			$class = 'Model' . preg_replace('/[^a-zA-Z0-9]/', '', $model);
		}
		if (file_exists($file)) {
			include_once($file);
			$this->registry->set('model_' . str_replace('/', '_', $model), new $class($this->registry));
		}
	}

    public function clearhtml($text) {
    	$this->seocmslib_api();
    	$text = strip_tags(html_entity_decode($text, ENT_QUOTES, 'UTF-8'));

    	$text = str_replace('\\\\\"', '&quot;', $text);
		$text = str_replace("\\\\\'", '&quot;', $text);

    	$text = str_replace('\\\"', '&quot;', $text);
		$text = str_replace("\\\'", '&quot;', $text);

    	$text = str_replace('\"', '&quot;', $text);
		$text = str_replace("\'", '&quot;', $text);

        $text = str_replace(PHP_EOL, '<br>', $text);

		$text = str_replace('\\\r\\\n', '<br>', $text);
        $text = str_replace('\\\r', '<br>', $text);
        $text = str_replace('\\\t', '&nbsp;&nbsp;&nbsp;&nbsp;', $text);;

		$text = str_replace('\\r\\n', '<br>', $text);
        $text = str_replace('\\r', '<br>', $text);
        $text = str_replace('\\t', '&nbsp;&nbsp;&nbsp;&nbsp;', $text);

		$text = str_replace('\r\n', '<br>', $text);
        $text = str_replace('\r', '<br>', $text);
        $text = str_replace('\t', '&nbsp;&nbsp;&nbsp;&nbsp;', $text);

		$text = nl2br($text);

        return $text;

    }

	public function bbcode($text, $width) {
		$this->seocmslib_api();
		require_once(DIR_SYSTEM . 'library/bbcode/Parser.php');
		$parser = new JBBCode\Parser();

        $parser->addBBCode("b", '<b>{param}</b>', false, true);
		$parser->addBBCode("quote", '<div class="quote">{param}</div>', false, true);
		$parser->addBBCode("size", '<span style="font-size:{option}%;">{param}</span>', true, true);
		$parser->addBBCode("code", '<pre class="code">{param}</pre>', false, false, 1);
		$parser->addBBCode("video", '<div style="overflow:hidden; "><iframe width="300" height="200" src="https://www.youtube.com/embed/{param}" frameborder="0" allowfullscreen></iframe></div>', false, false, 1);
		$parser->addBBCode("img", '<a href="{param}" rel="imagebox" class="imagebox" style="overflow: hidden;"><img class="bbimage" alt="" width="' . $width . '" src="{param}"></a>');
		$parser->addBBCode("url", '<a href="{param}" target="_blank" rel="nofollow">{param}</a>', false, false);
		$parser->addBBCode("url", '<a href="{option}" target="_blank" rel="nofollow">{param}</a>', true, true);
		$parser->addBBCode("u", '<u>{param}</u>', false, false);

		$parser->addCodeDefinitionSet(new JBBCode\DefaultCodeDefinitionSet());
		$parser->parse($text);
		$text = $parser->getAsHtml();

		$text = str_replace(':)', '<img src="catalog/view/javascript/wysibb/theme/default/img/smiles/sm1.png">', $text);
		$text = str_replace(':D', '<img src="catalog/view/javascript/wysibb/theme/default/img/smiles/sm2.png">', $text);
		$text = str_replace(';)', '<img src="catalog/view/javascript/wysibb/theme/default/img/smiles/sm3.png">', $text);
		$text = str_replace(':up:', '<img src="catalog/view/javascript/wysibb/theme/default/img/smiles/sm4.png">', $text);
		$text = str_replace(':down:', '<img src="catalog/view/javascript/wysibb/theme/default/img/smiles/sm5.png">', $text);
		$text = str_replace(':shock:', '<img src="catalog/view/javascript/wysibb/theme/default/img/smiles/sm6.png">', $text);
		$text = str_replace(':angry:', '<img src="catalog/view/javascript/wysibb/theme/default/img/smiles/sm7.png">', $text);
		$text = str_replace(':(', '<img src="catalog/view/javascript/wysibb/theme/default/img/smiles/sm8.png">', $text);
		$text = str_replace(':sick:', '<img src="catalog/view/javascript/wysibb/theme/default/img/smiles/sm9.png">', $text);
        /*
		{title:CURLANG.sm1, img: '<img src="{themePrefix}{themeName}/img/smiles/sm1.png" class="sm">', bbcode:":)"},
		{title:CURLANG.sm2, img: '<img src="{themePrefix}{themeName}/img/smiles/sm2.png" class="sm">', bbcode:":D"},
		{title:CURLANG.sm3, img: '<img src="{themePrefix}{themeName}/img/smiles/sm3.png" class="sm">', bbcode:";)"},
		{title:CURLANG.sm4, img: '<img src="{themePrefix}{themeName}/img/smiles/sm4.png" class="sm">', bbcode:":up:"},
		{title:CURLANG.sm5, img: '<img src="{themePrefix}{themeName}/img/smiles/sm5.png" class="sm">', bbcode:":down:"},
		{title:CURLANG.sm6, img: '<img src="{themePrefix}{themeName}/img/smiles/sm6.png" class="sm">', bbcode:":shock:"},
		{title:CURLANG.sm7, img: '<img src="{themePrefix}{themeName}/img/smiles/sm7.png" class="sm">', bbcode:":angry:"},
		{title:CURLANG.sm8 ,img: '<img src="{themePrefix}{themeName}/img/smiles/sm8.png" class="sm">', bbcode:":("},
		{title:CURLANG.sm9, img: '<img src="{themePrefix}{themeName}/img/smiles/sm9.png" class="sm">', bbcode:":sick:"}
        */

		if ((isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) == 'on' || $_SERVER['HTTPS'] == '1')) || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && (strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'https') || (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && strtolower($_SERVER['HTTP_X_FORWARDED_SSL']) == 'on'))) {
			$text = str_replace('http://', '//', $text);
		}

    	return $text;

	}
}
}
