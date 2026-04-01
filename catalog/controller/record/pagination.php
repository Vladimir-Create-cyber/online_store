<?php
/* All rights reserved belong to the module, the module developers http://opencartadmin.com */
// http://opencartadmin.com © 2011-2017 All Rights Reserved
// Distribution, without the author's consent is prohibited
// Commercial license
class ControllerRecordPagination extends Controller
{
	protected $data;
	public $kzsv = false;

 	public function __construct($registry) 	{
			parent::__construct($registry);
	}

	public function index($params = '') {
			$output = '';

           	if (SC_VERSION > 15) {
	            //$pagination_object = (object) array('ControllerRecordPagination' => 'setPagination');
	           // $this->load->setreplacedata(array('' => array('pagination'=> $pagination_object )));
           } else {
             	$output = $this->setPagination($params);
           }
           return $output;
	}

	public function setPagination($params) {

		if (!is_string($params)) return $params;


        $langmark_multi = $this->registry->get('langmark_multi');
        $settings = $this->config->get('asc_langmark_' . $this->config->get('config_store_id'));

		if (isset($settings['pagination_prefix'])) {
			$pagination_prefix = $settings['pagination_prefix'];
		} else {
			$pagination_prefix = 'page';
		}

		if (isset($this->data['settings_general']['get_pagination']) && $this->data['settings_general']['get_pagination'] ) {
		  	$get_pagination = $this->data['settings_general']['get_pagination'];
		} else {
		   	$get_pagination = 'tracking';
		}

		if (strpos($params , '/' . $pagination_prefix . '-{page}') !== false) {
  			 $params = str_replace('/' . $pagination_prefix . '-{page}', '/' . $pagination_prefix . '-1', $params);
		}

		if (strpos($params , $get_pagination."=cmswidget") !== false) {
			$tok = "/(((\&|(\&amp\;))|(\?))".$get_pagination."=cmswidget-\d+.*?(_wpage-1|_wpage-\{page\}))(?=((\')|(\#)|(\")|(\?)|(\&)))/";
		    $params = preg_replace($tok , '', $params );
		}
        // From catalog/controller/record/langmark private function pagination($seo_url)
        if ($this->registry->get('langmark_slash_close')) {
        	$devider = $this->registry->get('langmark_slash_close');
        } else {
			if (isset($settings['url_close_slash']) && $settings['url_close_slash']) {
				$devider = '/';
			} else {
				$devider = '';
			}
		}

		if (strpos($params , '/' . $pagination_prefix . '-1') !== false) {

			if ($this->registry->get('blog_design')) {

	        	$blog_design = $this->registry->get('blog_design');

				if (isset($blog_design['blog_devider']) && $blog_design['blog_devider']) {
					if (isset($blog_design['end_url_category']) && $blog_design['end_url_category'] != '') {

						if (strpos('.', $blog_design['end_url_category']) !== false) {
							$devider = '';
						} else {
							$devider = '/';
						}

					} else {
						$devider = '/';
					}

				} else {
					$devider = '';
				}

				if (isset($blog_design['end_url_category']) && strpos($blog_design['end_url_category'], '.') !== false) {
					$devider = '';
				}
	    	}

	        $params = str_replace('/' . $pagination_prefix . "-1'", $devider ."'", $params);
	        $params = str_replace('/' . $pagination_prefix . '-1"', $devider .'"', $params);
	        $params = str_replace('/' . $pagination_prefix . "-1/", $devider , $params);
	        $params = str_replace('/' . $pagination_prefix . "-1?", $devider ."?", $params);
	        $params = str_replace('/' . $pagination_prefix . "-1/?", $devider ."?", $params);

		}

		$params = str_replace("//?", "/?", $params);

        if (SC_VERSION < 20) {
        	$params = $this->set_agoo_og_page($params);
        }
        return $params;
	}

	private function set_agoo_og_page($params) {
		if (isset($params) && !$this->registry->get('admin_work')) {

			if (isset($this->request->get['route']) && ($this->request->get['route'] == 'record/record' || $this->request->get['route'] == 'record/blog')) {

				if (is_string($params) && strpos($params, '</head>') !==false && strpos($params, '<meta name="robots"') === false && is_callable(array($this->document, 'getSCRobots'))) {
					$sc_robots = $this->document->getSCRobots();
					if ($sc_robots && $sc_robots != '')
						$params = str_replace("</head>", '
<meta name="robots" content="' . $sc_robots . '" />
</head>', $params);

				$this->document->setSCRobots('');
				}

				if (isset($this->seocms_settings['og']) && $this->seocms_settings['og']) {
					if (is_string($params) && strpos($params, '</head>') !==false && strpos($params, "og:image") === false && is_callable(array($this->document, 'getSCOgImage'))) {
						$og_image = $this->document->getSCOgImage();

						if ($og_image && $og_image != '')
							$params = str_replace("</head>", '
<meta property="og:image" content="' . $og_image . '" />
</head>', $params);
					$this->document->setSCOgImage('');
					}
					if (is_string($params) && strpos($params, '</head>') !==false  && strpos($params, "og:title") === false && is_callable(array($this->document, 'getSCOgTitle'))) {
						$og_title = $this->document->getSCOgTitle();
						if ($og_title && $og_title != '')
							$params = str_replace("</head>", '
<meta property="og:title" content="' . $og_title . '" />
</head>', $params);

					$this->document->setSCOgTitle('');
					}
					if (is_string($params) && strpos($params, '</head>') !==false  && strpos($params, "og:description") === false && is_callable(array($this->document, 'getSCOgDescription'))) {
						$og_description = $this->document->getSCOgDescription();
						if ($og_description && $og_description != '')
							$params = str_replace("</head>", '
<meta property="og:description" content="' . $og_description . '" />
</head>', $params);
					$this->document->setSCOgDescription('');
					}
					if (is_string($params) && strpos($params, '</head>') !==false  && strpos($params, "og:url") === false && is_callable(array($this->document, 'getSCOgUrl'))) {
						$og_url = $this->document->getSCOgUrl();
						if ($og_url && $og_url != '')
							$params = str_replace("</head>", '
<meta property="og:url" content="' . $og_url . '" />
</head>', $params);
					$this->document->setSCOgUrl('');
					}
					if (is_string($params) && strpos($params, '</head>') !==false  && strpos($params, "og:type") === false && is_callable(array($this->document, 'getSCOgType'))) {
						$og_type = $this->document->getSCOgType();
						if ($og_type && $og_type != '')
							$params = str_replace("</head>", '
<meta property="og:type" content="' . $og_type . '" />
</head>', $params);
					$this->document->setSCOgType('');
					}
				}
			}
		}
		return $params;
	}



	public function getHref($params) {

		//preg_match_all("/<[Aa][\s]{1}[^>]*[Hh][Rr][Ee][Ff][^=]*=[ '\"\s]*([^ \"'>\s#]+)[^>]*>/", $params, $matches);
         $this->kzsv = true;

  		preg_match_all('/href="([^"]+)"/', $params, $matches);
        foreach ($matches[1] as $href) {
        	$urls[$href]=$href;
        }

        foreach ($urls as $href) {

        	$href_new = '/';
          	$params = str_replace($href, $href_new);
        }

        return $params;
	}


}
