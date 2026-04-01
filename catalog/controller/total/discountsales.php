<?php 
class ControllerTotalDiscountSales extends Controller { 

	public function renderDiscountSales() {
		$this->load->language('total/discountsales');
                $discountssales = $this->config->get('discountsales_discount');
                $data = FALSE;
                
                if($this->config->get('discountsales_status') && $discountssales && is_array($discountssales)){
                    $this->load->model('total/discountsales');
                    $data['results'] = $this->model_total_discountsales->getDiscountSalesForAllPayments($discountssales);
                    $data['text_discountsales_value_ot'] = $this->language->get('text_discountsales_value_ot');
                    $data['text_discountsales_value_do'] = $this->language->get('text_discountsales_value_do');
                    $data['text_discountsales_value_discount'] = $this->language->get('text_discountsales_value_discount');
                    $data['text_discountsales_programme'] = $this->language->get('text_discountsales_programme');
                }
                $discountsales_promo = $this->config->get('discountsales_promo');
                if(isset($discountsales_promo['to_product']['status']) && !$discountsales_promo['to_product']['status']){
                    exit();
                }
                if(!$data){
                    exit();
                }
                $data['data'] = $data;
                if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/total/discountsales_list.tpl')) {
                        echo $this->load->view($this->config->get('config_template') . '/template/total/discountsales_list.tpl', $data);
                } else {
                        echo $this->load->view('default/template/total/discountsales_list.tpl', $data);
                }
	}
        public function getDiscountsToProducts(){
            
            $links = json_decode(htmlspecialchars_decode($this->request->post['links']),TRUE);
            
            if(!$links){
                return;
            }
            
            $product_ids = array();
            
            foreach ($links as $link) {
                
                $product_id = $this->getProductId($link);
                if($product_id){
                    $product_ids[$link] = $product_id;
                }
                
            }
            
            if(!$product_ids || !$this->config->get('discountsales_status')){
                return;
            }
            $results = array();
            
            $this->load->model('total/discountsales');
            foreach ($product_ids as $link => $product_id) {
                $result = $this->model_total_discountsales->getDiscountInfo($product_id);
                if(get_object_vars($result)){
                    $result->link = $link;
                    $results[$product_id] = $result;
                }
            }
            echo json_encode($results);
        }
        
        public function getProductId($query) {
            $parts = explode('?', $query);
            $parts_result = array();
            $parts_result = array_merge($parts_result,$parts);
            $parts_2 = array();
            $parts_3 = array();
            $parts_4 = array();
            foreach ($parts as $part) {
                $parts_2 = explode('/', $part);
                $parts_result = array_merge($parts_result,$parts_2);
            }
            foreach ($parts as $part) {
                $parts_3 = explode('&', $part);
                $parts_result = array_merge($parts_result,$parts_3);
            }
            $product_id = 0;
            if($parts_result){
                $parts_result_query = $parts_result;
                foreach ($parts_result_query as $part){
                    if($part){
                        $query = $this->db->query(" SELECT query FROM ".DB_PREFIX."url_alias WHERE keyword='".$this->db->escape($part)."' ");
                        if($query->rows){
                            foreach ($query->rows as $query_url_alias) {
                                $parts_4[] = $query_url_alias['query'];
                            }
                        }
                    }
                }
                $parts_result = array_merge($parts_result,$parts_4);
                if($parts_result){
                    foreach ($parts_result as $part){
                        $product_id_parts = explode('=', $part);
                        if(isset($product_id_parts[0]) && isset($product_id_parts[1]) && $product_id_parts[0]=='product_id'){
                            $product_id = $product_id_parts[1];
                        }
                    }
                }
            }
            return $product_id;
        }
        
}
?>