<?php
class ModelCatalogLightshop extends Model {

	public function getFields($customer_group_id,$type = 0) {
		if(!isset($customer_group_id)){ $customer_group_id = $this->config->get('config_customer_group_id') ;}
		$sql = "SELECT * FROM " . DB_PREFIX . "lightshop_custom_field cf  LEFT JOIN " . DB_PREFIX . "lightshop_custom_field_description cfd ON (cf.custom_field_id = cfd.custom_field_id) ";
		$sql .= "LEFT JOIN " . DB_PREFIX . "lightshop_custom_field_customer_group cfcg ON (cf.custom_field_id = cfcg.custom_field_id)";
		$sql .= "WHERE cfd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND cfcg.customer_group_id = '" . (int)$customer_group_id . "'";
		if($type){
			$sql .= " AND  cf.type = '" . (int)$type . "'";			
		}
		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getIgnoredFields($customer_group_id = 0,$type = 0) {
		if(!isset($customer_group_id)){ $customer_group_id = $this->config->get('config_customer_group_id') ;}
		$data = array();
		$sql = "SELECT name FROM " . DB_PREFIX . "lightshop_custom_field cf  LEFT JOIN " . DB_PREFIX . "lightshop_custom_field_description cfd ON (cf.custom_field_id = cfd.custom_field_id) ";
		$sql .= "LEFT JOIN " . DB_PREFIX . "lightshop_custom_field_customer_group cfcg ON (cf.custom_field_id = cfcg.custom_field_id)";
		$sql .= "WHERE cfd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND cfcg.customer_group_id = '" . (int)$customer_group_id . "'";
		if($type){
			$sql .= " AND (cfcg.required  = 0 OR cfcg.is_show  = 0 OR cf.type  != '" . (int)$type . "') ";			
		}else{
			$sql .= " AND (cfcg.required  = 0 OR cfcg.is_show  = 0) ";
		}		
		$query = $this->db->query($sql);

		foreach($query->rows as $fieldName){
			$data[] = $fieldName['name'];
		}
		return $data;
	}

	public function getAllFields($type = 0) {
		$data = array();
		$sql = "SELECT * FROM " . DB_PREFIX . "lightshop_custom_field cf  LEFT JOIN " . DB_PREFIX . "lightshop_custom_field_description cfd ON (cf.custom_field_id = cfd.custom_field_id) ";
		$sql .= "LEFT JOIN " . DB_PREFIX . "lightshop_custom_field_customer_group cfcg ON (cf.custom_field_id = cfcg.custom_field_id)";
		$sql .= " WHERE cfd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		$sql .= " GROUP BY cf.name  ORDER BY cf.sort_order ASC";
        $query = $this->db->query($sql);

		foreach($query->rows as $fieldName){
			$data[] = $fieldName['name'];
		}
		return $data;
	}

	public function getFields4Product($product_id) {
		$tabs = array();
		$output = array();

		$sql = "SELECT * FROM " . DB_PREFIX . "lightshop_custom_tabs ct  LEFT JOIN " . DB_PREFIX . "lightshop_custom_tabs_description ctd ON (ct.cust_tab_id = ctd.cust_tab_id) ";
		$sql .= "LEFT JOIN " . DB_PREFIX . "lightshop_custom_tabs_to_store ct2s ON (ct.cust_tab_id = ct2s.cust_tab_id) ";
		$sql .= "WHERE ctd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND ct2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";

		$tabs = $query->rows;

		foreach ($tabs as $key => $tab) {
			$inTab = false;
			$query = $this->db->query("SELECT instanses FROM " . DB_PREFIX . "lightshop_custom_tabs_instanses WHERE cust_tab_id='" . (int) $tab["cust_tab_id"] . "'");
			if($query->num_rows){ 
				$instanses = array();
				foreach ($query->rows as $instanse) {
					$instanses[] =  $instanse['instanses'];
				}			
				if($tab["mode"] == 'categories'){ 
					$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_category p2c LEFT JOIN " . DB_PREFIX . "category_path cp ON (p2c.category_id = cp.category_id) WHERE cp.path_id  IN (".implode(',', $instanses).") AND product_id='" . (int) $product_id . "'");
					if($query->num_rows){ $inTab = true; }
				}
				if($tab["mode"] == 'products'){
					if(in_array($product_id, $instanses)){ $inTab = true;}
				}
			}else{
				$inTab = true;  //Если нет инстансов - таб применяется ко всем продуктам
			}
			if($inTab){
				$output[$tab["view"]][] = $tab;
			}			
		}
		if(!isset($queryss->row['value']) || !$queryss->row['value']){$output = array();}
		return $output;
	}

	public function createCatLinks($language_id ) {
		$data = array();	
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id) LEFT JOIN " . DB_PREFIX . "category_to_store c2s ON (c.category_id = c2s.category_id) WHERE cd.language_id = '" . (int)$language_id  . "' AND c2s.store_id = '" . (int)$this->config->get('config_store_id') . "'  ORDER BY c.parent_id, c.sort_order, cd.name");
		$results = $query->rows;
		foreach ($results as $result) {
			$data['c'.$result['category_id']] = array(
				$result['name']  => 'product/category&path=' .  $result['category_id']
			);			
		}	
		return 	$data ;
	}

	public function checkLCustomFields($error) {

		$this->load->language('account/register');
		$this->load->language('extension/theme/lightshop');

		$fields = array();
		foreach ($this->getFields($this->config->get('config_customer_group_id')) as $key => $field) {
			if($field['description']){
				$fields[$field['name']] = $field['description'];
			}else{
				$fields[$field['name']] = $this->language->get('entry_'.$field['name']);
			}			
		}

		if(isset($this->request->post['firstname'])){
			if ((utf8_strlen(trim($this->request->post['firstname'])) < 1) || (utf8_strlen(trim($this->request->post['firstname'])) > 64)) {
				$error['firstname'] = sprintf($this->language->get('error_firstname'),$fields['firstname']);
			}else{
				unset($error['firstname']);
			}
		}

		if(isset($this->request->post['lastname'])){
			if ((utf8_strlen(trim($this->request->post['lastname'])) < 1) || (utf8_strlen(trim($this->request->post['lastname'])) > 64)) {
				$error['lastname'] = sprintf($this->language->get('error_lastname'),$fields['lastname']);
			}else{
				unset($error['lastname']);
			}
		}

		if($this->config->get('config_mail_regexp')){
			$mail_regexp = $this->config->get('config_mail_regexp');
		}else{
			$mail_regexp = '/^[^@]+@.*.[a-z]{2,15}$/i';
		}

		if(isset($this->request->post['email'])){
			if ((utf8_strlen($this->request->post['email']) > 96) || !preg_match($mail_regexp, $this->request->post['email'])) {
				$error['email'] = sprintf($this->language->get('error_email'),$fields['email']);
			}else{
				unset($error['email']);
			}
		}

		if(isset($this->request->post['telephone'])){
			if ((utf8_strlen($this->request->post['telephone']) < 3) || (utf8_strlen($this->request->post['telephone']) > 32)) {
				$error['telephone'] = sprintf($this->language->get('error_telephone'),$fields['telephone']);
			}else{
				unset($error['telephone']);
			}
		}

		if(isset($this->request->post['address_1'])){		
			if ((utf8_strlen(trim($this->request->post['address_1'])) < 3) || (utf8_strlen(trim($this->request->post['address_1'])) > 128)) {
				$error['address_1'] = sprintf($this->language->get('error_address_1'),$fields['address_1']);
			}else{
				unset($error['address_1']);
			}
		}

		if(isset($this->request->post['address_2'])){		
			if ((utf8_strlen(trim($this->request->post['address_2'])) < 3) || (utf8_strlen(trim($this->request->post['address_2'])) > 128)) {
				$error['address_2'] = sprintf($this->language->get('error_address_1'),$fields['address_2']);
			}else{
				unset($error['address_2']);
			}
		}

		if(isset($this->request->post['company'])){
			if ((utf8_strlen($this->request->post['company']) < 3) || (utf8_strlen($this->request->post['company']) > 32)) {
				$error['company'] = sprintf($this->language->get('error_company'),$fields['company']);
			}else{
				unset($error['company']);
			}
		}

		if(isset($this->request->post['city'])){
			if ((utf8_strlen(trim($this->request->post['city'])) < 2) || (utf8_strlen(trim($this->request->post['city'])) > 128)) {
				$error['city'] = sprintf($this->language->get('error_city'),$fields['city']);
			}else{
				unset($error['city']);
			}
		}

		if(isset($this->request->post['fax'])){
			if ((utf8_strlen($this->request->post['fax']) < 3) || (utf8_strlen($this->request->post['fax']) > 32)) {
				$error['fax'] = sprintf($this->language->get('error_fax'),$fields['fax']);
			}else{
				unset($error['fax']);
			}
		}
		

		if(isset($this->request->post['postcode'])){
			if (utf8_strlen(trim($this->request->post['postcode'])) < 2 || utf8_strlen(trim($this->request->post['postcode'])) > 10) {
				$error['postcode'] = sprintf($this->language->get('error_postcode'),$fields['postcode']);
			}else{
				unset($error['postcode']);
			}
		}

		if(isset($this->request->post['country_id'])){
			if (!isset($this->request->post['country_id']) || $this->request->post['country_id'] == '' || !is_numeric($this->request->post['country_id'])) {
				$error['country'] = sprintf($this->language->get('error_country'),$fields['country']);
			}else{
				unset($error['country']);
			}
		}

		if(isset($this->request->post['zone_id'])){
			if (!isset($this->request->post['zone_id']) || $this->request->post['zone_id'] == '' || !is_numeric($this->request->post['zone_id'])) {
				$error['zone'] = sprintf($this->language->get('error_zone'),$fields['zone']);
			}else{
				unset($error['zone']);
			}
		}


		return $error ;
	}

	public function getPriceLimits($data) {

		$customer_group_id = $this->getCustomerGroup();

		$sql = "SELECT max(coalesce((SELECT price FROM " . DB_PREFIX . "product_discount pd2 WHERE pd2.product_id = p.product_id AND pd2.customer_group_id = '" . (int)$customer_group_id . "' AND pd2.quantity = '1' AND ((pd2.date_start = '0000-00-00' OR pd2.date_start < NOW()) AND (pd2.date_end = '0000-00-00' OR pd2.date_end > NOW())) ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1), " .
			   "(SELECT price FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$customer_group_id . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1), " .
			   "p.price) ) AS max_price, min(coalesce((SELECT price FROM " . DB_PREFIX . "product_discount pd2 WHERE pd2.product_id = p.product_id AND pd2.customer_group_id = '" . (int)$customer_group_id . "' AND pd2.quantity = '1' AND ((pd2.date_start = '0000-00-00' OR pd2.date_start < NOW()) AND (pd2.date_end = '0000-00-00' OR pd2.date_end > NOW())) ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1), " .
			   "(SELECT price FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$customer_group_id . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1), " .
			   "p.price) ) AS min_price  FROM ";

		if ($this->config->get('theme_lightshop_subcategory')) {
			$sql .=  DB_PREFIX . "category_path cp LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (cp.category_id = p2c.category_id) LEFT JOIN " . DB_PREFIX . "product p ON (p2c.product_id = p.product_id)";
		}else{
			$sql .=  DB_PREFIX . "product p ";
		}

		$sql .= " LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p2s.product_id=p.product_id) ";

		if (!$this->config->get('theme_lightshop_subcategory')) {
			$sql .= " LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p2c.product_id=p.product_id) ";
		}


		$sql .= " WHERE p.status = '1' AND p.date_available <= NOW( ) AND p2s.store_id = " . (int)$this->config->get('config_store_id');

		if($data['category_id']) {
			if ($this->config->get('theme_lightshop_subcategory')) {
				$sql .= " AND cp.path_id = '" . (int)$data['category_id'] . "'";
			}else{
				$sql .= " AND p2c.category_id = '" . (int)$data['category_id'] . "'";
			}
			
		}

		$query = $this->db->query($sql);

		if(isset($query->row)){
			$prices = $query->row;
			$tempMax = $this->currency->format($prices['max_price'], $this->session->data['currency'],'',false);
			$tempMin = $this->currency->format($prices['min_price'], $this->session->data['currency'],'',false);
			$query->row['max_price'] = $tempMax;
			$query->row['min_price'] = $tempMin;
		}

		return $query->row;
	}

	private function getCustomerGroup() {
		if($this->customer->isLogged()) {
			$customer_group_id = $this->customer->getGroupId();
			return $customer_group_id;
		} else {
			$customer_group_id = $this->config->get('config_customer_group_id');
			return $customer_group_id;
		}
	}
	
}

?>
