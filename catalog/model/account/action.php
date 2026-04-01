<?php

class ModelAccountAction extends Model {
    // Додає запис про дію клієнта до бази даних
	public function add($data) {
        // Вставляє новий запис з даними клієнта до таблиці client_action
		$this->db->query("INSERT INTO `" . DB_PREFIX . "client_action` SET `name` = '" . $this->db->escape($data['name']) . "', `tel` = '" . $this->db->escape($data['tel']) . "', `email` = '" . $this->db->escape($data['email']) . "', `uuid` = '" . $this->db->escape($data['uuid']) . "', `date_added` = NOW(), `date_updated` = NOW()");
        // Встановлює куку з UUID клієнта
        $this->createCookie($data['uuid']);
	}

    // Оновлює існуючий запис про дію клієнта
    public function update($id, $data){
        // Оновлює запис з новими даними клієнта за унікальним ідентифікатором uuid
        $this->db->query("UPDATE `" . DB_PREFIX . "client_action` SET `name` = '" . $this->db->escape($data['name']) . "', `tel` = '" . $this->db->escape($data['tel']) . "', `email` = '" . $this->db->escape($data['email']) . "', `date_updated` = NOW() WHERE `uuid` = '" . $this->db->escape($data['uuid']) . "'");
        // Встановлює куку з UUID клієнта
        $this->createCookie($data['uuid']);
    }

    // Перевіряє, чи існує запис про клієнта за uuid
    public function isExist($uuid){
        // Запит до бази даних для пошуку запису за uuid
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "client_action` WHERE uuid = '" . $this->db->escape($uuid) . "'");
        // Якщо запис знайдено, повертає його ідентифікатор
        if (isset($query->row) and !empty($query->row)) {
            if ($query->row['id']) {
                // Встановлює куку з UUID клієнта
                $this->createCookie($uuid);
                return $query->row['id'];
            }
        }
        // Якщо запис не знайдено, повертає false
        return false;
    }

    public function sendPostAndUpdate($uuid, $url = "https://dsn.gosport.biz.ua/") {
        // Ініціалізує cURL сесію
        $ch = curl_init();
        
        // Налаштовує параметри cURL запиту
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('uuid' => $uuid, 'secret_key' => '95d3ce50-bfb4-450e-943f-1b408ae328fd')));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        // Виконує запит та отримує відповідь
        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        // Закриває cURL сесію
        curl_close($ch);
    
        // Декодуємо відповідь
        $responseArray = json_decode($response, true);
    
            // Якщо відповідь успішна, оновлюємо запис у базі даних
            $this->db->query("UPDATE `" . DB_PREFIX . "client_action` SET `date_updated` = NOW() WHERE `uuid` = '" . $this->db->escape($uuid) . "'");
            // Встановлюємо куку
            $this->createCookie($uuid);
            
            echo json_encode($responseArray['message']);
            die;
    }

    // Встановлює куку з UUID клієнта
    public function createCookie($uuid){
        setcookie('site_client_uuid', $uuid, time() + 365 * 24 * 60 * 60 * 2, '/');
    }
}