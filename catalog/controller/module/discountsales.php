<?php
class ControllerModuleDiscountSales extends Controller {
	public function index($setting) {
            
            
                $opencart_version = VERSION;
            
                $opencart_version_parts = explode('.', $opencart_version);

                if(isset($opencart_version_parts[1])){

                   $opencart_version = $opencart_version_parts[0].$opencart_version_parts[1];

                }
                
            
                $this->document->addStyle('catalog/view/javascript/jquery/owl-carousel/owl.carousel.css');
		$this->document->addScript('catalog/view/javascript/jquery/owl-carousel/owl.carousel.min.js');
            
		$this->load->language('module/discountsales');

		$data['heading_title'] = $this->language->get('heading_title');
                
                if(isset($setting['name']) && $setting['name']){
                    $data['name'] = $setting['name'];
                }

		$data['text_tax'] = $this->language->get('text_tax');

		$data['button_cart'] = $this->language->get('button_cart');
		$data['button_wishlist'] = $this->language->get('button_wishlist');
		$data['button_compare'] = $this->language->get('button_compare');

		$this->load->model('catalog/product');
                
                $this->load->model('total/discountsales');

		$this->load->model('tool/image');

		$data['discountssales_complect'] = $this->model_total_discountsales->getComplectsToModule();
                $produtcs_by_manufacturer_ids = array();
                if(isset($setting['status_show_manufacturer_discount_complect']) && $setting['status_show_manufacturer_discount_complect'] && isset($this->request->get['manufacturer_id'])){
                    $produtcs_by_manufacturer_ids = $this->model_total_discountsales->getProductsIdsByManufacturerId($this->request->get['manufacturer_id']);
                    if($data['discountssales_complect']){
                        foreach ($data['discountssales_complect'] as $id_complect => $value_discountssales_complect) {
                            $product_show = FALSE;
                            if($value_discountssales_complect['products_data']){
                                foreach ($value_discountssales_complect['products_data'] as $product_id_products_data => $value_products_data) {
                                    if(isset($produtcs_by_manufacturer_ids[ $product_id_products_data ])){
                                        $product_show = TRUE;
                                    }
                                }
                            }
                            if(!$product_show){
                                unset($data['discountssales_complect'][$id_complect]);
                            }
                        }
                    }
                }
                
                $produtcs_by_categories_ids = array();
                if(isset($setting['status_show_category_product_discount_complect']) && $setting['status_show_category_product_discount_complect'] && ( isset($this->request->get['path']) || isset($this->request->get['product_id']) )){
                    if(isset($this->request->get['product_id'])){
                        $produtcs_by_categories_ids[$this->request->get['product_id']] = $this->request->get['product_id'];
                    }elseif(isset($this->request->get['path'])){
                        $categories = explode('_', $this->request->get['path']);
                        $produtcs_by_categories_ids = $this->model_total_discountsales->getProductsIdsByCategoryId(end($categories));
                    }
                    if($data['discountssales_complect']){
                        foreach ($data['discountssales_complect'] as $id_complect => $value_discountssales_complect) {
                            $product_show = FALSE;
                            if($value_discountssales_complect['products_data']){
                                foreach ($value_discountssales_complect['products_data'] as $product_id_products_data => $value_products_data) {
                                    if(isset($produtcs_by_categories_ids[ $product_id_products_data ])){
                                        $product_show = TRUE;
                                    }
                                }
                            }
                            if(!$product_show){
                                unset($data['discountssales_complect'][$id_complect]);
                            }
                        }
                    }
                }
                
                if(isset($setting['status_complect'])){
                    foreach ($setting['status_complect'] as $id_complect=>$status_complect) {
                        if(isset($data['discountssales_complect'][$id_complect]) && !$status_complect){
                            unset($data['discountssales_complect'][$id_complect]);
                        }
                    }
                }
                
                $data['module'] = md5(rand(0, 30));
                
		if (isset($setting['limit']) && !$setting['limit']) {
			$setting['limit'] = 4;
		}
		if (!empty($data['discountssales_complect']) && isset($setting['status_complect'])) {
			$discountssales_complect = array_slice($data['discountssales_complect'], 0, (int)$setting['limit']);

			foreach ($discountssales_complect as $complect_id=>$complect_data) {
                            if($complect_data['products_data']){
                                
                                $total_complect_price_text_old_price[$complect_id] = 0;
                                foreach ($complect_data['products_data'] as $product_info) {
                                    
                                    if ($product_info) {
                                            if ($product_info['image']) {
                                                    $image = $this->model_tool_image->resize($product_info['image'], $setting['width'], $setting['height']);
                                            } else {
                                                    $image = $this->model_tool_image->resize('placeholder.png', $setting['width'], $setting['height']);
                                            }
                                            
                                            $total_complect_price_text_old_price[$complect_id] += $product_info['price']*$product_info['complect_quantity'];
                                            
                                            $product_info['special'] = '';
                                            if($product_info['price'] && $product_info['price']>$product_info['new_price'] && $product_info['new_price']!=0.0){
                                                $product_info['special'] = $product_info['new_price'];
                                            }elseif($product_info['new_price']==0.0){
                                                $product_info['special'] = $product_info['new_price'];
                                            }else{
                                                $product_info['price'] = $product_info['new_price'];
                                            }
                                            
                                            
            
                                            if($opencart_version==22){

                                                $price_currency = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')),$this->session->data['currency']);

                                            }else{

                                                $price_currency = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')));

                                            }
                                            
                                            if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
                                                    $price = $price_currency;
                                            } else {
                                                    $price = false;
                                            }

                                            if($opencart_version==22){

                                                $special_currency = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')),$this->session->data['currency']);

                                            }else{

                                                $special_currency = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')));

                                            }
                                            
                                            
                                            if (isset($product_info['special']) && $product_info['special']!=='') {
                                                    $special = $special_currency;
                                            } else {
                                                    $special = false;
                                            }
                                            
                                            if($opencart_version==22){

                                                $tax_currency = $this->currency->format(((float)$product_info['special'] ? $product_info['special'] : $product_info['price']),$this->session->data['currency']);

                                            }else{

                                                $tax_currency = $this->currency->format((float)$product_info['special'] ? $product_info['special'] : $product_info['price']);

                                            }
                                            

                                            if ($this->config->get('config_tax')) {
                                                    $tax = $tax_currency;
                                            } else {
                                                    $tax = false;
                                            }

                                            if ($this->config->get('config_review_status')) {
                                                    $rating = $product_info['rating'];
                                            } else {
                                                    $rating = false;
                                            }

                                            $data['complects'][$complect_id]['pruducts'][] = array(
                                                    'product_id'  => $product_info['product_id'],
                                                    'thumb'       => $image,
                                                    'name'        => $product_info['name'],
                                                    'description' => utf8_substr(strip_tags(html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8')), 0, $this->config->get('config_product_description_length')) . '..',
                                                    'price'       => $price,
                                                    'special'     => $special,
                                                    'tax'         => $tax,
                                                    'rating'      => $rating,
                                                    'href'        => $this->url->link('product/product', 'product_id=' . $product_info['product_id']),
                                                    'complect_quantity'=>$product_info['complect_quantity']
                                            );
                                            $data['complects'][$complect_id]['info'] = $complect_data;
                                            
                                            if($opencart_version==22){

                                                $total_complect_price_text = $this->currency->format($this->tax->calculate($complect_data['total_complect_price'], $product_info['tax_class_id'], $this->config->get('config_tax')),$this->session->data['currency']);
                                                $total_complect_old_price_text = $this->currency->format($this->tax->calculate($total_complect_price_text_old_price[$complect_id], $product_info['tax_class_id'], $this->config->get('config_tax')),$this->session->data['currency']);

                                            }else{

                                                $total_complect_price_text = $this->currency->format($this->tax->calculate($complect_data['total_complect_price'], $product_info['tax_class_id'], $this->config->get('config_tax')));
                                                $total_complect_old_price_text = $this->currency->format($this->tax->calculate($total_complect_price_text_old_price[$complect_id], $product_info['tax_class_id'], $this->config->get('config_tax')));

                                            }
                                            
                                            $data['complects'][$complect_id]['info']['total_complect_price_text'] = $total_complect_price_text;
                                            $data['complects'][$complect_id]['info']['total_complect_old_price_text'] = $total_complect_old_price_text;
                                    }
                                }
                            }
			}
		}
		if (isset($data['complects']) && $data['complects']) {
			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/module/discountsales.tpl')) {
				return $this->load->view($this->config->get('config_template') . '/template/module/discountsales.tpl', $data);
			} else {
				return $this->load->view('module/discountsales.tpl', $data);
			}
		}
	}
}