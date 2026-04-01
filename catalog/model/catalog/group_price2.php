<?php
class ModelCatalogGroupPrice2 extends Model {
    private static $a864c0c0c91aef870ad147ebaa76a3508 = array();
    private static $a83843ee82a368e04a23ec1270a7504e5 = array();
    public function updatePrice($data, $price) {
        $group_price = $this->getMinPrice($data);
        if ($group_price['type'] == 2) {
            $price = $price + $group_price['price'];
        } else {
            $price = $price + $price * $group_price['price'] / 100;
        }
        if ($price < 0) {
            $price = 0;
        }
        return $price;
    }
    private function getMinPrice($data) {
        $product_id = $data['product_id'];
        $manufacturer_id = $data['manufacturer_id'];;
        $price = array('price' => 0, 'type' => 1);
        $categories = array();
        $this->load->model('catalog/product');
        $result = $this->model_catalog_product->getCategories($product_id);
        foreach ($result as $category) {
            $categories[] = $category['category_id'];
        }
        $minPrice = $this->getCategoryMinPrice($categories);
        if (!empty($minPrice)) {
            $price['category'] = $minPrice;
        }
        $a1327a4d52e6dc88f34efc7cc3bc72286 = $this->getManufacturerMinPrice($manufacturer_id);
        if (!empty($a1327a4d52e6dc88f34efc7cc3bc72286)) {
            $price['manufacturer'] = $a1327a4d52e6dc88f34efc7cc3bc72286;
        }
        if (isset($price['manufacturer']) && isset($price['category']) && $price['manufacturer']['type'] == $price['category']['type']) {
            return ($price['manufacturer']['price'] > $price['category']['price']) ? $price['manufacturer'] : $price['category'];
        } elseif (isset($price['manufacturer'])) {
            return $price['manufacturer'];
        } elseif (isset($price['category'])) {
            return $price['category'];
        }
        return $price;
    }
    private function getCategoryMinPrice($categories) {
        if (empty(self::$a864c0c0c91aef870ad147ebaa76a3508)) {

                $customer_group_id = $this->cache->get('kd_price_customer_group_id');
      
            $sql = "SELECT price, type, category_id FROM " . DB_PREFIX . "customer_group_price 
				WHERE customer_group_id = " . (int)$customer_group_id;
            $sql = $this->db->query($sql);
            foreach ($sql->rows as $abdc4adf3ae1690fe6e24ae335b346c16) {
                self::$a864c0c0c91aef870ad147ebaa76a3508[$abdc4adf3ae1690fe6e24ae335b346c16['category_id']] = $abdc4adf3ae1690fe6e24ae335b346c16;
            }
        }
        $group_price = NULL;
        foreach ($categories as $a38acabe832964d6f11a9e521b2c7d490) {
            if (isset(self::$a864c0c0c91aef870ad147ebaa76a3508[$a38acabe832964d6f11a9e521b2c7d490])) {
                if ($group_price == NULL) {
                    $group_price['price'] = self::$a864c0c0c91aef870ad147ebaa76a3508[$a38acabe832964d6f11a9e521b2c7d490]['price'];
                    $group_price['type'] = self::$a864c0c0c91aef870ad147ebaa76a3508[$a38acabe832964d6f11a9e521b2c7d490]['type'];
                } else if (self::$a864c0c0c91aef870ad147ebaa76a3508[$a38acabe832964d6f11a9e521b2c7d490]['price'] < $group_price['price']) {
                    $group_price['price'] = self::$a864c0c0c91aef870ad147ebaa76a3508[$a38acabe832964d6f11a9e521b2c7d490]['price'];
                    $group_price['type'] = self::$a864c0c0c91aef870ad147ebaa76a3508[$a38acabe832964d6f11a9e521b2c7d490]['type'];
                }
            }
        }
        if ($group_price == NULL && isset(self::$a864c0c0c91aef870ad147ebaa76a3508[0])) {
            $group_price['price'] = self::$a864c0c0c91aef870ad147ebaa76a3508[0]['price'];
            $group_price['type'] = self::$a864c0c0c91aef870ad147ebaa76a3508[0]['type'];
        }
        return $group_price;
    }
    private function getManufacturerMinPrice($manufacturer_id) {
        if (empty(self::$a83843ee82a368e04a23ec1270a7504e5)) {


                $customer_group_id = $this->cache->get('kd_price_customer_group_id');


            $sql = "SELECT price, type, manufacturer_id FROM " . DB_PREFIX . "manufacturer_group_price 
				WHERE customer_group_id = " . (int)$customer_group_id;
            $sql = $this->db->query($sql);
            foreach ($sql->rows as $abdc4adf3ae1690fe6e24ae335b346c16) {
                self::$a83843ee82a368e04a23ec1270a7504e5[$abdc4adf3ae1690fe6e24ae335b346c16['manufacturer_id']] = $abdc4adf3ae1690fe6e24ae335b346c16;
            }
        }
        $group_price = NULL;
        if (isset(self::$a83843ee82a368e04a23ec1270a7504e5[$manufacturer_id])) {
            $group_price['price'] = self::$a83843ee82a368e04a23ec1270a7504e5[$manufacturer_id]['price'];
            $group_price['type'] = self::$a83843ee82a368e04a23ec1270a7504e5[$manufacturer_id]['type'];
        }
        return $group_price;
    }
}
?>