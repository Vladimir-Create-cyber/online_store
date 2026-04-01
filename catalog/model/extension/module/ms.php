<?php
class ModelExtensionModuleMS extends Model {
	
	public function getOrderStatusById($id) {
		$query = $this->db->query("SELECT name FROM `" . DB_PREFIX . "order_status` WHERE order_status_id = '" . (int)$id . "'");

		return $query->rows;
	}

	public function changeSmsStatus($customer_id, $status) {
		$this->db->query("UPDATE " . DB_PREFIX . "customer SET sms_status = '". (int)$status ."', date_update_status = NOW() WHERE customer_id = '" . (int)$customer_id . "'");
	}
}