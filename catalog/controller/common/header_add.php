<?php


		$data['outdated_browser'] = $this->language->get('outdated_browser');
		$data['text_search'] = $this->language->get('text_search');
		$data['text_forgotten'] = $this->language->get('text_forgotten');
		$data['forgotten'] = $this->url->link('account/forgotten', '', true);
		$data['header_type'] = $this->config->get('theme_lightshop_header_type');
		$data['js_footorhead'] = $this->config->get('theme_lightshop_js_footorhead');
		$data['open_graph'] = $this->config->get('theme_lightshop_og');


			if(isset($main_navs)){
				foreach($main_navs as $main_nav){
					$data['main_navs'][$main_nav['sort']] = $main_nav;
				}
				ksort($data['main_navs']);	
				foreach($data['main_navs'] as $key => $main_nav){ 	
					foreach($main_nav['type'] as $key1 => $typeLink){  
						if (isset($main_nav['type'][$key1]['links'])){
							foreach($main_nav['type'][$key1]['links'] as $key2 => $link){ 
								if(strpos($link,':') !== false ){ $data['main_navs'][$key]['type'][$key1]['links'][$key2] = current($data['top_links'][$key2]);continue;}
								$data['main_navs'][$key]['type'][$key1]['links'][$key2] = $this->url->link($link);	
							}						
						}
					}	

				}		
			}		


		$data['theme_color'] = $this->config->get('theme_lightshop_color');
		$data['bootstrap_modal'] = $this->config->get('theme_lightshop_bootstrap_modal');
		$data['bootstrap_ttpo'] = $this->config->get('theme_lightshop_bootstrap_ttpo');
		$data['theme_color_1'] = $this->config->get('theme_lightshop_custom_color_1');
		$data['theme_color_2'] = $this->config->get('theme_lightshop_custom_color_2');