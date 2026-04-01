<?php
class ModelTotalDiscountSales extends Model {
    private $max_interval;
    private $max_interval_all_payments;
    private $max_interval_category;
    private $max_interval_manufacturer;
    private $max_interval_complect;
    private $status_together;
    private $products_whis_base_discounts = array();
    private $other_discount_status = array();
    private $disable_data = array();
    private $new_prices_to_complects = array();
    private $new_total = 0;
    private $opencart_version = 0;
    public $registry;






    public function __construct($registry) {
        
        $this->registry = $registry;
        
        $this->setOpencartVersion();
        
    }
    
    

    
        public function setOpencartVersion() {
            
            $opencart_version = VERSION;
            
            $opencart_version_parts = explode('.', $opencart_version);
            
            if(isset($opencart_version_parts[1])){
             
                $this->opencart_version = $opencart_version_parts[0].$opencart_version_parts[1];
                
            }
            
        }
        
    public function setQuantityForNewPriceIntoComplects($product_id,$quantity_to_cart) {
        if(!isset($this->new_prices_to_complects[$product_id])){
            $this->new_prices_to_complects[$product_id]['quantity_to_cart'] = $quantity_to_cart;
            $this->new_prices_to_complects[$product_id]['quantity_for_check'] = 1;
        }else{
            $this->new_prices_to_complects[$product_id]['quantity_for_check'] += 1;
        }
    }
    
    public function setNewTotal($newTotal) {
        $this->new_total += $newTotal;
    }

    public function getQuantityForNewPriceIntoComplects($product_id) {
        if(isset($this->new_prices_to_complects[$product_id])){
            return $this->new_prices_to_complects[$product_id];
        }else{
            return FALSE;
        }
    }

    public function setDisableData() {
        $disable_data['categories'] = $this->config->get('discountsales_disable_data_categories');
        if($disable_data['categories']){
            foreach ($disable_data['categories'] as $key => $categories) {
                if($categories['data']){
                    foreach ($categories['data'] as $category_id) {
                        $products = $this->getProductsIdsByCategoryId($category_id); 
                        if(!isset($disable_data['categories'][$key]['products'])){
                            $disable_data['categories'][$key]['products'] = $products;
                        }else{
                            $disable_data['categories'][$key]['products'] += $products;
                        }
                    }
                }
            }
            $this->disable_data['categories'] = $disable_data['categories'];
        }else{
            $this->disable_data['categories'] = array();
        }
    }
    
    public function getDisableDataForNewTotal($id_list) {
        $products_to_cart = $this->getProductsToCart();
        $new_products_to_cart = $this->getOtherDiscountStatus('new_products_to_cart');
        $disable_data['total_disable'] = 0;
        $disable_data['categories'] = $this->disable_data['categories'];
        if($disable_data['categories']){
            foreach ($disable_data['categories'] as $key=>$categories) {
                if($categories['products']){
                    foreach ($categories['products'] as $product_id) {
                        foreach ($products_to_cart as $key_to_cart => $product_to_cart) {
                            //вычетаем то, что в комплектах и не должно учитываться, т.к. уже будет вычтено из базы по скидкам на объем
                            if(!isset($new_products_to_cart[$key_to_cart]) && $product_id==$product_to_cart['product_id']){
                                $this->setDisableDataTotal(((float)$product_to_cart['price']*$product_to_cart['quantity']),$key);
                            }
                        }
                    }
                }
            }
        }
        if(isset($this->disable_data['disable_total'][$id_list])){
            return $this->disable_data['disable_total'][$id_list];
        }else{
            return 0;
        }
    }
    
    private function setDisableDataTotal($disable_total,$id_list) {
        if(!isset($this->disable_data['disable_total'][$id_list])){
            $this->disable_data['disable_total'][$id_list] = $disable_total;
        }else{
            $this->disable_data['disable_total'][$id_list] += $disable_total;
        }
    }
    
    public function setOtherDiscountStatus($type, $value_for_total) {
        
        $this->other_discount_status[$type] = $value_for_total;
        
        if($type=='total_other_discount'){
            $this->setNewTotal($value_for_total);
        }
        
    }
    
    public function getOtherDiscountStatus($type) {
        
        if(isset($this->other_discount_status[$type])){
            return $this->other_discount_status[$type];
        }elseif($type=='total_other_discount'){
            $this->other_discount_status[$type] = 0;
            return $this->other_discount_status[$type];
        }elseif($type=='new_products_to_cart'){
            $this->other_discount_status[$type] = array();
            return $this->other_discount_status[$type];
        }
    }
    
    public function getProductsWhisBaseDiscounts($product_id) {
        
        if(isset($this->products_whis_base_discounts[$product_id]) && $this->products_whis_base_discounts[$product_id]){
            return TRUE;
        }else{
            return FALSE;
        }
        
    }
    //__constr
    public function setProductsWhisBaseDiscounts() {
        
        $products_to_cart = $this->getProductsToCart();
        
        if($products_to_cart){
            foreach ($products_to_cart as $row){
                if($row['base_discount_price']){
                    $this->products_whis_base_discounts[$row['product_id']] = TRUE;
                }else{
                    $this->products_whis_base_discounts[$row['product_id']] = FALSE;
                }
            }
        }
        
        $this->setDisableData();
        
    }
    
    public function getStatusTogether() {
        
        return $this->status_together;
        
    }

    public function setStatusTogether($status_together) {
        
        $this->status_together = $status_together;
        
    }
    
    public function getTotal($total_data) {
        
        $this->status_together = FALSE;
        
        $discountssales = $this->config->get('discountsales_discount');
        $discountssales_category = $this->config->get('discountsales_category_discount');
        $discountssales_manufacturer = $this->config->get('discountsales_manufacturer_discount');
        $discountssales_complect = $this->config->get('discountsales_complect_discount');
        
        if(isset($this->session->data['payment_method'])){
            $payment_method = $this->session->data['payment_method'];
            $payment_title = $this->session->data['payment_method']['title'];
        }else{
            $payment_method = FALSE;
            $payment_title = '';
        }
        
        $sort_discounts = array();
        $sort_position = 0;
        if($this->config->get('discountsales_sort_order')){
            $sort_position = $this->config->get('discountsales_sort_order');
            $sort_discounts[ 'discountsales_sort_order' ] = (int)$sort_position;
        }else{
            $sort_discounts[ 'discountsales_sort_order' ] = $sort_position;
        }
        $sort_position = 0;
        if($this->config->get('discountsales_category_sort_order')){
            $sort_position = $this->config->get('discountsales_category_sort_order');
            $sort_discounts[ 'discountsales_category_sort_order' ] = (int)$sort_position;
        }else{
            $sort_discounts[ 'discountsales_category_sort_order' ] = $sort_position;
        }
        $sort_position = 0;
        if($this->config->get('discountsales_manufacturer_sort_order')){
            $sort_position = $this->config->get('discountsales_manufacturer_sort_order');
            $sort_discounts[ 'discountsales_manufacturer_sort_order' ] = (int)$sort_position;
        }else{
            $sort_discounts[ 'discountsales_manufacturer_sort_order' ] = $sort_position;
        }
        
        $sort_discounts[ 'discountsales_complect_sort_order' ] = 0;
        
        asort($sort_discounts);
        
        foreach ($sort_discounts as $key_sort_position => $tmp) {
            
            if ($key_sort_position=='discountsales_complect_sort_order' && $discountssales_complect) {
                $this->deleteDisableRowsComlect($discountssales_complect);
                $this->setIntervalComplect($discountssales_complect);
                foreach($discountssales_complect as $complect_id=>$discountsales){
                    $complect_id = '';
                    $products = $discountsales['products'];
                    ksort($products);
                    $product_quantity = $discountsales['product_quantity'];
                    foreach ($products as $product_id => $product) {
                        $quantity = (int)$product_quantity[$product_id]['quantity'];
                        $complect_id .= '_'.$product_id.'_'.$quantity;
                    }
                    if (isset($this->max_interval_complect[$complect_id]) && $discountsales['sales_limitation']==$this->max_interval_complect[$complect_id]['max_interval']){
                        $total_complect = $this->max_interval_complect[$complect_id]['total_complect'];
                        $value = $this->getTotalWhisDiscountPlus($total_complect, $discountsales);
                        $name = $this->max_interval_complect[$complect_id]['name'];
                        if($value!=0){
                            $title = $this->getTitleTotalPlus($discountsales,'text_discountsales_complect',$name);
                            
                            if($this->opencart_version==22){
                                    
                                $text_whis_currency = $this->currency->format($value,$this->session->data['currency']);

                            }else{

                                $text_whis_currency = $this->currency->format($value);

                            }
                            
                            $total_data['totals'][] = array( 
                                    'code'       => 'discountsales',
                                    'title'      => $title,
                                    'text'       => $text_whis_currency,
                                    'value'      => $value,
                                    'sort_order' => $this->config->get('discountsales_complect_sort_order')
                            );
                            if ($discountsales['tax_class_id']) {
                                $tax_rates = $this->tax->getRates($value, $discountsales['tax_class_id']);
                                foreach ($tax_rates as $tax_rate) {
                                    if (!isset($taxes[ $tax_rate['tax_rate_id'] ])) {
                                        $taxes[ $tax_rate['tax_rate_id'] ] = $tax_rate['amount'];
                                    } else {
                                        $taxes[ $tax_rate['tax_rate_id'] ] += $tax_rate['amount'];
                                    }
                                }
                            }
                            $total_data['total'] += $value;
                            $this->setStatusTogether(TRUE);
                        }
                    }
                }
            }elseif ($key_sort_position=='discountsales_category_sort_order' && $discountssales_category) {
                ///Применияем скидка по категориям
                $this->deleteDisableRowsCategory($discountssales_category);
                $this->setIntervalCategory($discountssales_category);
                foreach($discountssales_category as $discountsales){
                    $category_id = $discountsales['category_id'];
                    if (isset($this->max_interval_category[$category_id]) && $discountsales['sales_limitation']==$this->max_interval_category[$category_id]['max_interval']){
                        $total_category = $this->max_interval_category[$category_id]['total_category'];
                        $value = $this->getTotalWhisDiscountPlus($total_category, $discountsales);
                        $name = $this->max_interval_category[$category_id]['name'];
                        if($value!=0){
                            $title = $this->getTitleTotalPlus($discountsales,'text_discountsales_category',$name);
                            
                            if($this->opencart_version==22){
                                    
                                $text_whis_currency = $this->currency->format($value,$this->session->data['currency']);

                            }else{

                                $text_whis_currency = $this->currency->format($value);

                            }
                            
                            $total_data['totals'][] = array( 
                                    'code'       => 'discountsales',
                                    'title'      => $title,
                                    'text'       => $text_whis_currency,
                                    'value'      => $value,
                                    'sort_order' => $this->config->get('discountsales_category_sort_order')
                            );
                            if ($discountsales['tax_class_id']) {
                                $tax_rates = $this->tax->getRates($value, $discountsales['tax_class_id']);
                                foreach ($tax_rates as $tax_rate) {
                                    if (!isset($taxes[ $tax_rate['tax_rate_id'] ])) {
                                        $taxes[ $tax_rate['tax_rate_id'] ] = $tax_rate['amount'];
                                    } else {
                                        $taxes[ $tax_rate['tax_rate_id'] ] += $tax_rate['amount'];
                                    }
                                }
                            }
                            $total_data['total'] += $value;
                            $this->setStatusTogether(TRUE);
                        }
                    }
                }
            }elseif ($key_sort_position=='discountsales_manufacturer_sort_order' && $discountssales_manufacturer) {
                $this->deleteDisableRowsManufacturer($discountssales_manufacturer);
                $this->setIntervalManufacturer($discountssales_manufacturer);   
                foreach($discountssales_manufacturer as $discountsales){
                    $manufacturer_id = $discountsales['manufacturer_id'];
                    if (isset($this->max_interval_manufacturer[$manufacturer_id]) && $discountsales['sales_limitation']==$this->max_interval_manufacturer[$manufacturer_id]['max_interval']){
                        $total_manufacturer = $this->max_interval_manufacturer[$manufacturer_id]['total_manufacturer'];
                        $value = $this->getTotalWhisDiscountPlus($total_manufacturer, $discountsales);
                        $name = $this->max_interval_manufacturer[$manufacturer_id]['name'];
                        if($value!=0){
                            $title = $this->getTitleTotalPlus($discountsales,'text_discountsales_manufacturer',$name);
                            
                            if($this->opencart_version==22){
                                    
                                $text_whis_currency = $this->currency->format($value,$this->session->data['currency']);

                            }else{

                                $text_whis_currency = $this->currency->format($value);

                            }
                            
                            $total_data['totals'][] = array( 
                                    'code'       => 'discountsales',
                                    'title'      => $title,
                                    'text'       => $text_whis_currency,
                                    'value'      => $value,
                                    'sort_order' => $this->config->get('discountsales_manufacturer_sort_order')
                            );
                            if ($discountsales['tax_class_id']) {
                                $tax_rates = $this->tax->getRates($value, $discountsales['tax_class_id']);
                                foreach ($tax_rates as $tax_rate) {
                                    if (!isset($taxes[ $tax_rate['tax_rate_id'] ])) {
                                        $taxes[ $tax_rate['tax_rate_id'] ] = $tax_rate['amount'];
                                    } else {
                                        $taxes[ $tax_rate['tax_rate_id'] ] += $tax_rate['amount'];
                                    }
                                }
                            }
                            $total_data['total'] += $value;
                            $this->setStatusTogether(TRUE);
                        }
                    }
                }
            }elseif($key_sort_position=='discountsales_sort_order' && $discountssales){
                //Удалим строчки, которые выключилены, чтобы не мешались
                $this->deleteDisableRows($discountssales);
                //Если есть то, что нужно убрать из базы для расчета скидки на объем
                $total_other_discount = 0;
                $total_for_calculate = $this->cart->getTotal();
                if($this->getOtherDiscountStatus('total_other_discount')){
                    $total_other_discount = $this->getOtherDiscountStatus('total_other_discount');
                    if($total_other_discount<=$total_for_calculate){
                        $total_for_calculate -= $total_other_discount;
                        if($total_for_calculate<0){
                            $total_for_calculate = 0;
                        }
                    }
                }
                //Определяем верхний предел, в котором сейчас тотал корзины (для выбранного метода, и для всех методов)
                $this->setInterval($discountssales, $total_for_calculate);
                //Если есть скидка вне зависимости от метода, делаем её первой
                foreach($discountssales as $discountsales){
                    if (!$discountsales['payment_code'] && $discountsales['sales_limitation']==$this->max_interval_all_payments){
                        //новый тотал, если вычеркнуты категории
                        $disable_total = 0;
                        if(isset($discountsales['disable_categories']) && $discountsales['disable_categories']){
                            $disable_total = $this->getDisableDataForNewTotal($discountsales['disable_categories']);
                        }
                        if($disable_total){
                            $total_for_calculate -= $disable_total;
                        }
                        
                        $value = $this->getTotalWhisDiscount($total_for_calculate, $discountssales, $payment_method, TRUE);
                        if($value!=0){
                            $title = $this->getTitleTotal($total_for_calculate, $discountssales, $payment_title, $payment_method, TRUE);
                            
                            if($this->opencart_version==22){
                                    
                                $text_whis_currency = $this->currency->format($value,$this->session->data['currency']);

                            }else{

                                $text_whis_currency = $this->currency->format($value);

                            }
                            
                            $total_data['totals'][] = array( 
                                    'code'       => 'discountsales',
                                    'title'      => $title,
                                    'text'       => $text_whis_currency,
                                    'value'      => $value,
                                    'sort_order' => $this->config->get('discountsales_sort_order')
                            );
                            if ($discountsales['tax_class_id']) {
                                $tax_rates = $this->tax->getRates($value, $discountsales['tax_class_id']);
                                foreach ($tax_rates as $tax_rate) {
                                    if (!isset($taxes[ $tax_rate['tax_rate_id'] ])) {
                                        $taxes[ $tax_rate['tax_rate_id'] ] = $tax_rate['amount'];
                                    } else {
                                        $taxes[ $tax_rate['tax_rate_id'] ] += $tax_rate['amount'];
                                    }
                                }
                            }
                            $total_data['total'] += $value;
                            $this->setStatusTogether(TRUE);
                        }
                    }

                    if (isset($payment_method['code']) && $discountsales['payment_code']==$payment_method['code'] && $discountsales['sales_limitation']==$this->max_interval){
                        
                        //новый тотал, если вычеркнуты категории
                        $disable_total = 0;
                        if(isset($discountsales['disable_categories']) && $discountsales['disable_categories']){
                            $disable_total = $this->getDisableDataForNewTotal($discountsales['disable_categories']);
                        }
                        if($disable_total){
                            $total_for_calculate -= $disable_total;
                        }
                        
                        $value = $this->getTotalWhisDiscount($total_for_calculate, $discountssales, $payment_method);
                        $title = $this->getTitleTotal($total_for_calculate, $discountssales, $payment_title, $payment_method);
                        
                        if($this->opencart_version==22){
                                    
                            $text_whis_currency = $this->currency->format($value,$this->session->data['currency']);

                        }else{

                            $text_whis_currency = $this->currency->format($value);

                        }
                        
                        if($value!=0){
                            $total_data['totals'][] = array( 
                                'code'       => 'discountsales',
                                'title'      => $title,
                                'text'       => $text_whis_currency,
                                'value'      => $value,
                                'sort_order' => $this->config->get('discountsales_sort_order')
                            );
                            if ($discountsales['tax_class_id']) {
                                $tax_rates = $this->tax->getRates($value, $discountsales['tax_class_id']);
                                foreach ($tax_rates as $tax_rate) {
                                    if (!isset($taxes[ $tax_rate['tax_rate_id'] ])) {
                                        $taxes[ $tax_rate['tax_rate_id'] ] = $tax_rate['amount'];
                                    } else {
                                        $taxes[ $tax_rate['tax_rate_id'] ] += $tax_rate['amount'];
                                    }
                                }
                            }
                            $this->setStatusTogether(TRUE);
                            $total_data['total'] += $value;
                        }
                    }
                }
            }
            
            
        }
        
    }

        
    
    
    
    public function setNewPrice($product_id_distrib,&$price,&$option_price,$cart_option=array(),$cart_quantity) {
        $complect_id_to_cart = 0;
        if(is_object($cart_option)){
            $cart_option = (array)$cart_option;
            $complect_id_to_cart = $this->getIdComplect($cart_option);
        }elseif(is_array($cart_option)){
            $complect_id_to_cart = $this->getIdComplect($cart_option);
        }
        
        $this->status_together = $this->getStatusTogether();
        
        $discountsales_status = $this->config->get('discountsales_status');
        $discountssales_complect = $this->config->get('discountsales_complect_discount');
        
        if($discountssales_complect && $discountsales_status){
            
            $this->deleteDisableRowsComlect($discountssales_complect);
            
            $this->setIntervalComplect($discountssales_complect);
            
            foreach($discountssales_complect as $complect_id=>$discountsales){
                $complect_id = '';
                $products = $discountsales['products'];
                ksort($products);
                $product_quantity = $discountsales['product_quantity'];
                $product_ids = array();
                //для проверки количества - должно соответствовать количеству комплекта, менять количество комплекта нельзя, чтобы сохранились новые цены в комплекте
                $product_ids_quantity = array();
                foreach ($products as $product_id => $product) {
                    $quantity = (int)$product_quantity[$product_id]['quantity'];
                    $complect_id .= '_'.$product_id.'_'.$quantity;
                    $product_ids[$product_id] = $product_quantity[$product_id]['new_price'];
                    $product_ids_quantity[$product_id] = $product_quantity[$product_id]['quantity'];
                }
                if (isset($this->max_interval_complect[$complect_id]) && $discountsales['sales_limitation']==$this->max_interval_complect[$complect_id]['max_interval']){
                    $total_complect = $this->max_interval_complect[$complect_id]['total_complect'];
                    $value = $this->getTotalWhisDiscountPlus($total_complect, $discountsales);
                    $name = $this->max_interval_complect[$complect_id]['name'];
                    
                    if(isset($product_ids[$product_id_distrib]) && isset($product_ids_quantity[$product_id]) && $product_ids[$product_id_distrib]!=='' && $product_ids_quantity[$product_id_distrib]==$cart_quantity && $complect_id_to_cart && $complect_id_to_cart==$complect_id){
                        $new_price = $this->getFloat($product_ids[$product_id_distrib]);
                        
                        if($new_price){
                            $price = $new_price;
                        }else{
                            $price = 0;
                            $option_price = 0;
                        }
                        $this->setStatusTogether(TRUE);
                    }
                }
            }
        }
        
    }
    
    private function getMiddlePriceForProductsIntoSets($cart_quantity,$complect_quantity,$product_id,&$new_price,$price) {
        $quantity = 0;
        if(isset($complect_quantity['quantity'])){
            $quantity = $complect_quantity['quantity'];
        }
        $quantity_to_cart = 0;
        if(isset($cart_quantity[$product_id])){
            $quantity_to_cart = $cart_quantity[$product_id];
        }
    }


    private function getTotalWhisDiscount($total,$discountssales,$payment_method=FALSE,$all_payments=FALSE){
        $result = 0.00;
        foreach ($discountssales as $discountsales) {
            if($all_payments && !$discountsales['payment_code'] && $discountsales['sales_limitation']==$this->max_interval_all_payments){
                //Если больше нуля, то 
                if($discountsales['above_zero']){
                    //Если в процентах
                    if($discountsales['type_data']){
                        $result = -$total * (float)$discountsales['value_data']/100;    
                    }
                    //Если в ден. единицах
                    else{
                        $result = -(float)$discountsales['value_data'];
                    }
                }else{
                    if($discountsales['type_data']){
                        $result = $total * (float)$discountsales['value_data'] / 100;
                    }else{
                        $result = (float)$discountsales['value_data'];
                    }
                }
            }
            if(!$all_payments && isset($payment_method['code']) && $discountsales['payment_code']==$payment_method['code'] && $discountsales['sales_limitation']==$this->max_interval){
                //Если больше нуля, то
                if($discountsales['above_zero']){
                    //Если в процентах
                    if($discountsales['type_data']){
                        $result = -$total * (float)$discountsales['value_data']/100;    
                    }
                    //Если в ден. единицах
                    else{
                        $result = -(float)$discountsales['value_data'];
                    }
                }else{
                    if($discountsales['type_data']){
                        $result = $total * (float)$discountsales['value_data'] / 100;
                    }else{
                        $result = (float)$discountsales['value_data'];
                    }
                }
            }
        }
        return $result;
    }
    
    
    private function getTotalWhisDiscountPlus($total,$discountsales){
        $result = 0.00;
        if($discountsales['above_zero']){
            //Если в процентах
            if($discountsales['type_data']){
                $result = -$total * (float)$discountsales['value_data']/100;    
            }
            //Если в ден. единицах
            else{
                $result = -(float)$discountsales['value_data'];
            }
        }else{
            if($discountsales['type_data']){
                $result = $total * (float)$discountsales['value_data'] / 100;
            }else{
                $result = (float)$discountsales['value_data'];
            }
        }
        return $result;
    }
    
    private function getTitleTotalPlus($discountsales,$type_discount='',$name=''){
        $this->load->language('total/discountsales');
        $result = '';
        if($discountsales['above_zero']){
            if($discountsales['type_data']){
                $title = round($discountsales['value_data'],2).'%';
            }else{
                
                if($this->opencart_version==22){
                                    
                    $text_whis_currency = $this->currency->format($discountsales['value_data'],$this->session->data['currency']);

                }else{

                    $text_whis_currency = $this->currency->format($discountsales['value_data']);

                }
                
                $title = $text_whis_currency;
            }
            $result = $this->language->get('text_discount').sprintf($this->language->get('text_discountsales_value'),$title);
            if($type_discount && $name){
                $result = $this->language->get('text_discount').sprintf($this->language->get($type_discount),$name).sprintf($this->language->get('text_discountsales_value'),$title);
            }
        }else{
            if($discountsales['type_data']){
                $title = round($discountsales['value_data'],2).'%';
            }else{
                
                if($this->opencart_version==22){
                                    
                    $text_whis_currency = $this->currency->format($discountsales['value_data'],$this->session->data['currency']);

                }else{

                    $text_whis_currency = $this->currency->format($discountsales['value_data']);

                }
                
                $title = $text_whis_currency;
            }
            $result = $this->language->get('text_margin').sprintf($this->language->get('text_discountsales_value'),$title);
            if($type_discount && $name){
                $result = $this->language->get('text_margin').sprintf($this->language->get($type_discount),$name).sprintf($this->language->get('text_discountsales_value'),$title);
            }
        }
        
        return $result;
    }
    private function getTitleTotalPlusDoscountInfo($discountsales,$type_discount='',$name='',$class='discountsales',$text_new='',$colour=''){
        $this->load->language('total/discountsales');
        $result_title['html'] = '';
        $result_title['string'] = '';
        if($discountsales['above_zero']){
            if($discountsales['type_data']){
                $title = round($discountsales['value_data'],2).'%';
            }else{
                
                if($this->opencart_version==22){
                                    
                    $text_whis_currency = $this->currency->format($discountsales['value_data'],$this->session->data['currency']);

                }else{

                    $text_whis_currency = $this->currency->format($discountsales['value_data']);

                }
                
                $title = $text_whis_currency;
            }
            $result = $text_new.sprintf($this->language->get('text_discountsales_value_info'),$title);
            if($type_discount && $name){
                $result = $text_new.sprintf($this->language->get($type_discount),$name).sprintf($this->language->get('text_discountsales_value_info'),$title);
            }
        }else{
            if($discountsales['type_data']){
                $title = round($discountsales['value_data'],2).'%';
            }else{
                
                if($this->opencart_version==22){
                                    
                    $text_whis_currency = $this->currency->format($discountsales['value_data'],$this->session->data['currency']);

                }else{

                    $text_whis_currency = $this->currency->format($discountsales['value_data']);

                }
                
                $title = $text_whis_currency;
            }
            $result = $this->language->get('text_margin').sprintf($this->language->get('text_discountsales_value_info'),$title);
            if($type_discount && $name){
                $result = $this->language->get('text_margin').sprintf($this->language->get($type_discount),$name).sprintf($this->language->get('text_discountsales_value_info'),$title);
            }
        }
        
        if($result){
            $result_title['html'] = '<div  style="background:'  . $colour .  '"  class="'.$class.'">' . $result . '</div>';
            $result_title['string'] = $result;
        }
        
        return $result_title;
    }
    
    private function getTitleForDoscountInfo($total,$discountssales,$title,$payment_method=FALSE,$all_payments=FALSE,$class='discountsales discount',$text_new='',$colour='red'){
        $this->load->language('total/discountsales');
        $result_title['html'] = '';
        $result_title['string'] = '';
        foreach ($discountssales as $discountsales) {
            
            if($all_payments && !$discountsales['payment_code'] && $discountsales['sales_limitation']==$this->max_interval_all_payments){
                if($discountsales['above_zero']){
                    if($discountsales['type_data']){
                        $title = round($discountsales['value_data'],2).'%';
                    }else{
                        
                        if($this->opencart_version==22){
                                    
                            $text_whis_currency = $this->currency->format($discountsales['value_data'],$this->session->data['currency']);

                        }else{

                            $text_whis_currency = $this->currency->format($discountsales['value_data']);

                        }
                        
                        $title = $text_whis_currency;
                    }
                    
                    $result = $text_new.sprintf($this->language->get('text_discountsales_value_info'),$title);
                }else{
                    if($discountsales['type_data']){
                        $title = round($discountsales['value_data'],2).'%';
                    }else{
                        
                        if($this->opencart_version==22){
                                    
                            $text_whis_currency = $this->currency->format($discountsales['value_data'],$this->session->data['currency']);

                        }else{

                            $text_whis_currency = $this->currency->format($discountsales['value_data']);

                        }
                        
                        $title = $text_whis_currency;
                    }
                    $result = $this->language->get('text_margin').sprintf($this->language->get('text_discountsales_value_info'),$title);
                }
            }
            if(!$all_payments && isset($payment_method['code']) && $discountsales['payment_code']==$payment_method['code'] && $discountsales['sales_limitation']==$this->max_interval){
                
                if($discountsales['above_zero']){
                    $result = $text_new.sprintf($this->language->get('text_discountsales'),$title);
                }else{
                    $result = $this->language->get('text_margin').sprintf($this->language->get('text_discountsales'),$title);
                }
                
            }
        }
        
        if($result){
            $result_title['html'] = '<div style="background:'  . $colour .  '" class="'.$class.'">' . $result . '</div>';
            $result_title['string'] = $result;
        }
        
        return $result_title;
    }
    
    private function getTitleTotal($total,$discountssales,$title,$payment_method=FALSE,$all_payments=FALSE){
        $this->load->language('total/discountsales');
        $result = '';
        foreach ($discountssales as $discountsales) {
            
            if($all_payments && !$discountsales['payment_code'] && $discountsales['sales_limitation']==$this->max_interval_all_payments){
                if($discountsales['above_zero']){
                    if($discountsales['type_data']){
                        $title = round($discountsales['value_data'],2).'%';
                    }else{
                        
                        if($this->opencart_version==22){
                                    
                            $text_whis_currency = $this->currency->format($discountsales['value_data'],$this->session->data['currency']);

                        }else{

                            $text_whis_currency = $this->currency->format($discountsales['value_data']);

                        }
                        
                        $title = $text_whis_currency;
                    }
                    $result = $this->language->get('text_discount').sprintf($this->language->get('text_discountsales_value'),$title);
                }else{
                    if($discountsales['type_data']){
                        $title = round($discountsales['value_data'],2).'%';
                    }else{
                        
                        
                        if($this->opencart_version==22){
                                    
                            $text_whis_currency = $this->currency->format($discountsales['value_data'],$this->session->data['currency']);

                        }else{

                            $text_whis_currency = $this->currency->format($discountsales['value_data']);

                        }
                        
                        $title = $text_whis_currency;
                    }
                    $result = $this->language->get('text_margin').sprintf($this->language->get('text_discountsales_value'),$title);
                }
            }
            if(!$all_payments && isset($payment_method['code']) && $discountsales['payment_code']==$payment_method['code'] && $discountsales['sales_limitation']==$this->max_interval){
                
                if($discountsales['above_zero']){
                    $result = $this->language->get('text_discount').sprintf($this->language->get('text_discountsales'),$title);
                }else{
                    $result = $this->language->get('text_margin').sprintf($this->language->get('text_discountsales'),$title);
                }
                
            }
        }
        
        return $result;
    }
    function cmp_obj($a, $b){
        if ($a["sales_limitation"] == $b["sales_limitation"]) {
        return 0;
        }
        return ($a["sales_limitation"] < $b["sales_limitation"]) ? -1 : 1;
    }
    
    private function checkQuantity($products,$product_id){
        foreach ($products as $key => $value) {
            if($value['product_id'] == $product_id && $value['quantity']){
                return TRUE;
            }else{
                return FALSE;
            }
        }
    }

    private function setInterval($discountsales,$total){
        $discountssales_sort_value_sales[] = 0;
        $discountssales_sort_value_sales_all_payments[] = 0;
        usort($discountsales, array('ModelTotalDiscountSales','cmp_obj'));
        foreach ($discountsales as $key => $value) {
            if(!$value['payment_code']){
                $discountssales_sort_value_sales_all_payments[] = $value['sales_limitation'];
            }
            //Только для выбранного метода нужен интервал
            elseif(isset ($this->session->data['payment_method']['code']) && $value['payment_code']==$this->session->data['payment_method']['code']){
                $discountssales_sort_value_sales[] = $value['sales_limitation'];
            }
        }
        asort($discountssales_sort_value_sales);
        $this->max_interval = 0;
        for ($j=0;$j<count($discountssales_sort_value_sales);$j++) {
            if($total >= $discountssales_sort_value_sales[$j] && isset($discountssales_sort_value_sales[$j+1]) && $total < $discountssales_sort_value_sales[$j+1]){
                
                $this->max_interval = $discountssales_sort_value_sales[$j+1];
            }
        }
        asort($discountssales_sort_value_sales_all_payments);
        $this->max_interval_all_payments = 0;
        for ($j=0;$j<count($discountssales_sort_value_sales_all_payments);$j++) {
            if($total >= $discountssales_sort_value_sales_all_payments[$j] && isset($discountssales_sort_value_sales_all_payments[$j+1]) && $total < $discountssales_sort_value_sales_all_payments[$j+1]){
                $this->max_interval_all_payments = $discountssales_sort_value_sales_all_payments[$j+1];
            }
        }
    }
    private function setIntervalCategory($discountssales_category){
        $this->max_interval_category = array();
        $products = array();
        //продукты в корзинке
        $products_to_cart = $this->cart->getProducts();
        
        //проверяем, есть то, что нужно вычесть - комплекты
        $new_products_to_cart = array();
        if($this->getOtherDiscountStatus('new_products_to_cart')){
            $new_products_to_cart = $this->getOtherDiscountStatus('new_products_to_cart');
        }
        //все продукты, на которые распространяется скидка по категории
        if($discountssales_category and is_array($discountssales_category)){
            $products_tmp = array();
            foreach ($discountssales_category as $discountssales_category_row) {
                $products_tmp = $this->getProductsByCategoryId($discountssales_category_row['category_id']);
                if($products_tmp && is_array($products_tmp)){
                    foreach ($products_tmp as $key => $value) {
                        $products[ $value['category_id'] ][ $value['product_id'] ] = $value;
                    }
                }
            }
        }
        //продукты, на которые распространяется скидка по категории и которые есть в корзинке
        $products_filter = array();
        if($products_to_cart && is_array($products_to_cart)){
            foreach ($products_to_cart as $product_to_cart_row_key=>$product_to_cart_row) {
                foreach ($products as $category_id => $products_row) {
                    if(isset($products_row[ $product_to_cart_row['product_id'] ])){
                        $products_filter[$category_id] = $products_row;
                    }
                }
            }
        }
        //если таких нет, сразу выходим скидка тут не нужна
        if(!$products_filter){
            return;
        }
        $discountssales_category_tmp = $discountssales_category;
        $discountssales_category = array();
        //оставляет только те строчки в параметрах скидки, которые нужны для отобранных продуктов
        foreach ($products_filter as $category_id => $product_filter) {
            foreach ($discountssales_category_tmp as $key_discountssales_category => $discountssales_category_row) {
                if($discountssales_category_row['category_id'] == $category_id ){
                    $discountssales_category[$key_discountssales_category] = $discountssales_category_row;
                    $name_category = end($product_filter);
                    $discountssales_category[$key_discountssales_category]['name'] = $name_category['name'];
                }
            }
            //посчитаем тотал для продуктов из скидочной категории
            $products_filter[$category_id]['total_category'] = 0;
            foreach ($products_to_cart as $product_to_cart_row_key=>$product_to_cart_row) {
                if(isset($product_filter[$product_to_cart_row['product_id']])  && !isset($new_products_to_cart[$product_to_cart_row_key])){
                    $products_filter[$category_id]['total_category'] += $product_to_cart_row['price']*$product_to_cart_row['quantity'];
                }
            }
        }
        //если таких нет, сразу выходим скидка тут не нужна
        if(!$discountssales_category || !$product_filter){
            return;
        }
        
        $discountssales_category_tmp = $discountssales_category;
        $discountssales_category = array();
        
        foreach ($discountssales_category_tmp as $key => $value) {
            $discountssales_category[$value['category_id']][] = $value;
        }
        
        foreach ($discountssales_category as $key => $value) {
            usort($discountssales_category[$key], array('ModelTotalDiscountSales','cmp_obj'));
        }
        foreach ($discountssales_category as $category_id => $discountsales) {
            $discountssales_sort_category = array();
            $discountssales_sort_category[] = 0;
            $this->max_interval_category[$category_id]['name'] = '';
            foreach ($discountsales as $key => $value) {
                $discountssales_sort_category[] = $value['sales_limitation'];
                $this->max_interval_category[$category_id]['name'] = $value['name'];
            }
            asort($discountssales_sort_category);
            $this->max_interval_category[$category_id]['max_interval'] = 0;
            $this->max_interval_category[$category_id]['total_category'] = 0;
            
            $total = $products_filter[$category_id]['total_category'];
            for ($j=0;$j<count($discountssales_sort_category);$j++) {
                if($total >= $discountssales_sort_category[$j] && isset($discountssales_sort_category[$j+1]) && $total < $discountssales_sort_category[$j+1]){
                    $this->max_interval_category[$category_id]['max_interval'] = $discountssales_sort_category[$j+1];
                    $this->max_interval_category[$category_id]['total_category'] = $total;
                }
            }
        }
        return;
    }
    
    private function setIntervalCategoryDiscountInfo($discountssales_category,$product,$total_whis_cart){
        $this->max_interval_category = array();
        $products = array();
        $products_to_cart = array($product);
        //все продукты, на которые распространяется скидка по категории
        if($discountssales_category and is_array($discountssales_category)){
            $products_tmp = array();
            foreach ($discountssales_category as $discountssales_category_row) {
                $products_tmp = $this->getProductsByCategoryId($discountssales_category_row['category_id']);
                if($products_tmp && is_array($products_tmp)){
                    foreach ($products_tmp as $key => $value) {
                        $products[ $value['category_id'] ][ $value['product_id'] ] = $value;
                    }
                }
            }
        }
        //продукты, на которые распространяется скидка по категории и которые есть в корзинке
        $products_filter = array();
        if($products_to_cart && is_array($products_to_cart)){
            foreach ($products_to_cart as $product_to_cart_row) {
                foreach ($products as $category_id => $products_row) {
                    if(isset($products_row[ $product_to_cart_row['product_id'] ])){
                        $products_filter[$category_id] = $products_row;
                    }
                }
            }
        }
        //если таких нет, сразу выходим скидка тут не нужна
        if(!$products_filter){
            return;
        }
        $discountssales_category_tmp = $discountssales_category;
        $discountssales_category = array();
        //оставляет только те строчки в параметрах скидки, которые нужны для отобранных продуктов
        foreach ($products_filter as $category_id => $product_filter) {
            foreach ($discountssales_category_tmp as $key_discountssales_category => $discountssales_category_row) {
                if($discountssales_category_row['category_id'] == $category_id ){
                    $discountssales_category[$key_discountssales_category] = $discountssales_category_row;
                    $name_category = end($product_filter);
                    $discountssales_category[$key_discountssales_category]['name'] = $name_category['name'];
                }
            }
            //посчитаем тотал для продуктов из скидочной категории
            $products_filter[$category_id]['total_category'] = 0;
            foreach ($products_to_cart as $product_to_cart_row) {
                if(isset($product_filter[$product_to_cart_row['product_id']])){
                    $products_filter[$category_id]['total_category'] += $total_whis_cart;
                }
            }
        }
        //если таких нет, сразу выходим скидка тут не нужна
        if(!$discountssales_category){
            return;
        }
        
        $discountssales_category_tmp = $discountssales_category;
        $discountssales_category = array();
        
        foreach ($discountssales_category_tmp as $key => $value) {
            $discountssales_category[$value['category_id']][] = $value;
        }
        
        foreach ($discountssales_category as $key => $value) {
            usort($discountssales_category[$key], array('ModelTotalDiscountSales','cmp_obj'));
        }
        foreach ($discountssales_category as $category_id => $discountsales) {
            $discountssales_sort_category = array();
            $discountssales_sort_category[] = 0;
            $this->max_interval_category[$category_id]['name'] = '';
            foreach ($discountsales as $key => $value) {
                $discountssales_sort_category[] = $value['sales_limitation'];
                $this->max_interval_category[$category_id]['name'] = $value['name'];
            }
            asort($discountssales_sort_category);
            $this->max_interval_category[$category_id]['max_interval'] = 0;
            $this->max_interval_category[$category_id]['total_category'] = 0;
            
            $total = $products_filter[$category_id]['total_category'];
            for ($j=0;$j<count($discountssales_sort_category);$j++) {
                if($total >= $discountssales_sort_category[$j] && isset($discountssales_sort_category[$j+1]) && $total < $discountssales_sort_category[$j+1]){
                    $this->max_interval_category[$category_id]['max_interval'] = $discountssales_sort_category[$j+1];
                    $this->max_interval_category[$category_id]['total_category'] = $total;
                }
            }
        }
        return;
    }
    
    private function setIntervalManufacturerDiscountInfo($discountssales_manufacturer,$product,$total_whis_cart){
        $this->max_interval_manufacturer = array();
        $products = array();
        //продукты в корзинке
        $products_to_cart = array($product);
        //все продукты, на которые распространяется скидка по категории
        if($discountssales_manufacturer and is_array($discountssales_manufacturer)){
            $products_tmp = array();
            
            foreach ($discountssales_manufacturer as $discountssales_manufacturer_row) {
                $products_tmp = $this->getProductsByManufacturerId($discountssales_manufacturer_row['manufacturer_id']);
                
                //убираем продукты, которые вычеркнуты
                $disable_products = array();
                if(isset($discountssales_manufacturer_row['disable_categories']) && $discountssales_manufacturer_row['disable_categories'] && isset($this->disable_data['categories'][$discountssales_manufacturer_row['disable_categories']])){
                    $disable_products = $this->disable_data['categories'][$discountssales_manufacturer_row['disable_categories']]['products'];
                }
                
                if($products_tmp && is_array($products_tmp)){
                    foreach ($products_tmp as $key => $value) {
                        if(!isset($disable_products[$value['product_id']])){
                            $products[ $value['manufacturer_id'] ][ $value['product_id'] ] = $value;
                        }
                    }
                }
            }
        }
        //продукты, на которые распространяется скидка по категории и которые есть в корзинке
        $products_filter = array();
        if($products_to_cart && is_array($products_to_cart)){
            foreach ($products_to_cart as $product_to_cart_row) {
                foreach ($products as $manufacturer_id => $products_row) {
                    if(isset($products_row[ $product_to_cart_row['product_id'] ])){
                        $products_filter[$manufacturer_id] = $products_row;
                    }
                }
            }
        }
        //если таких нет, сразу выходим скидка тут не нужна
        if(!$products_filter){
            return;
        }
        $discountssales_manufacturer_tmp = $discountssales_manufacturer;
        $discountssales_manufacturer = array();
        //оставляет только те строчки в параметрах скидки, которые нужны для отобранных продуктов
        foreach ($products_filter as $manufacturer_id => $product_filter) {
            foreach ($discountssales_manufacturer_tmp as $key_discountssales_manufacturer => $discountssales_manufacturer_row) {
                if($discountssales_manufacturer_row['manufacturer_id'] == $manufacturer_id ){
                    $discountssales_manufacturer[$key_discountssales_manufacturer] = $discountssales_manufacturer_row;
                    $name_manufacturer = end($product_filter);
                    $discountssales_manufacturer[$key_discountssales_manufacturer]['name'] = $name_manufacturer['name'];
                }
            }
            //посчитаем тотал для продуктов из скидочной категории
            $products_filter[$manufacturer_id]['total_manufacturer'] = 0;
            foreach ($products_to_cart as $product_to_cart_row) {
                if(isset($product_filter[$product_to_cart_row['product_id']])){
                    $products_filter[$manufacturer_id]['total_manufacturer'] += $total_whis_cart;
                }
            }
        }
        //если таких нет, сразу выходим скидка тут не нужна
        if(!$discountssales_manufacturer){
            return;
        }
        
        $discountssales_manufacturer_tmp = $discountssales_manufacturer;
        $discountssales_manufacturer = array();
        
        foreach ($discountssales_manufacturer_tmp as $key => $value) {
            $discountssales_manufacturer[$value['manufacturer_id']][] = $value;
        }
        
        foreach ($discountssales_manufacturer as $key => $value) {
            usort($discountssales_manufacturer[$key], array('ModelTotalDiscountSales','cmp_obj'));
        }
        
        foreach ($discountssales_manufacturer as $manufacturer_id => $discountsales) {
            $discountssales_sort_manufacturer = array();
            $discountssales_sort_manufacturer[] = 0;
            $this->max_interval_manufacturer[$manufacturer_id]['name'] = '';
            foreach ($discountsales as $key => $value) {
                $discountssales_sort_manufacturer[] = $value['sales_limitation'];
                $this->max_interval_manufacturer[$manufacturer_id]['name'] = $value['name'];
            }
            asort($discountssales_sort_manufacturer);
            $this->max_interval_manufacturer[$manufacturer_id]['max_interval'] = 0;
            $this->max_interval_manufacturer[$manufacturer_id]['total_manufacturer'] = 0;
            $total = $products_filter[$manufacturer_id]['total_manufacturer'];
            for ($j=0;$j<count($discountssales_sort_manufacturer);$j++) {
                if($total >= $discountssales_sort_manufacturer[$j] && isset($discountssales_sort_manufacturer[$j+1]) && $total < $discountssales_sort_manufacturer[$j+1]){
                    $this->max_interval_manufacturer[$manufacturer_id]['max_interval'] = $discountssales_sort_manufacturer[$j+1];
                    $this->max_interval_manufacturer[$manufacturer_id]['total_manufacturer'] = $total;
                }
            }
        }
        return;
    }
    
    private function setIntervalManufacturer($discountssales_manufacturer){
        $this->max_interval_manufacturer = array();
        $products = array();
        //продукты в корзинке
        $products_to_cart = $this->cart->getProducts();
        
        //проверяем, есть то, что нужно вычесть - комплекты
        $new_products_to_cart = array();
        if($this->getOtherDiscountStatus('new_products_to_cart')){
            $new_products_to_cart = $this->getOtherDiscountStatus('new_products_to_cart');
        }
        //все продукты, на которые распространяется скидка по категории
        if($discountssales_manufacturer and is_array($discountssales_manufacturer)){
            $products_tmp = array();
            foreach ($discountssales_manufacturer as $discountssales_manufacturer_row) {
                $products_tmp = $this->getProductsByManufacturerId($discountssales_manufacturer_row['manufacturer_id']);
                //убираем продукты, которые вычеркнуты
                $disable_products = array();
                if(isset($discountssales_manufacturer_row['disable_categories']) && $discountssales_manufacturer_row['disable_categories'] && isset($this->disable_data['categories'][$discountssales_manufacturer_row['disable_categories']])){
                    $disable_products = $this->disable_data['categories'][$discountssales_manufacturer_row['disable_categories']]['products'];
                }
                
                if($products_tmp && is_array($products_tmp)){
                    foreach ($products_tmp as $key => $value) {
                        
                        if(!isset($disable_products[$value['product_id']])){
                            $products[ $value['manufacturer_id'] ][ $value['product_id'] ] = $value;
                        }
                    }
                }
            }
        }
        //продукты, на которые распространяется скидка по категории и которые есть в корзинке
        $products_filter = array();
        if($products_to_cart && is_array($products_to_cart)){
            foreach ($products_to_cart as $product_to_cart_row_key => $product_to_cart_row) {
                foreach ($products as $manufacturer_id => $products_row) {
                    if(isset($products_row[ $product_to_cart_row['product_id'] ])){
                        $products_filter[$manufacturer_id] = $products_row;
                    }
                }
            }
        }
        //если таких нет, сразу выходим скидка тут не нужна
        if(!$products_filter){
            return;
        }
        $discountssales_manufacturer_tmp = $discountssales_manufacturer;
        $discountssales_manufacturer = array();
        //оставляет только те строчки в параметрах скидки, которые нужны для отобранных продуктов
        foreach ($products_filter as $manufacturer_id => $product_filter) {
            foreach ($discountssales_manufacturer_tmp as $key_discountssales_manufacturer => $discountssales_manufacturer_row) {
                if($discountssales_manufacturer_row['manufacturer_id'] == $manufacturer_id ){
                    $discountssales_manufacturer[$key_discountssales_manufacturer] = $discountssales_manufacturer_row;
                    $name_manufacturer = end($product_filter);
                    $discountssales_manufacturer[$key_discountssales_manufacturer]['name'] = $name_manufacturer['name'];
                }
            }
            //посчитаем тотал для продуктов из скидочной категории
            $products_filter[$manufacturer_id]['total_manufacturer'] = 0;
            foreach ($products_to_cart as $product_to_cart_row_key=>$product_to_cart_row) {
                if(isset($product_filter[$product_to_cart_row['product_id']])   && !isset($new_products_to_cart[$product_to_cart_row_key])){
                    $products_filter[$manufacturer_id]['total_manufacturer'] += $product_to_cart_row['price']*$product_to_cart_row['quantity'];
                }
            }
        }
        //если таких нет, сразу выходим скидка тут не нужна
        if(!$discountssales_manufacturer || !$products_filter){
            return;
        }
        
        $discountssales_manufacturer_tmp = $discountssales_manufacturer;
        $discountssales_manufacturer = array();
        
        foreach ($discountssales_manufacturer_tmp as $key => $value) {
            $discountssales_manufacturer[$value['manufacturer_id']][] = $value;
        }
        
        foreach ($discountssales_manufacturer as $key => $value) {
            usort($discountssales_manufacturer[$key], array('ModelTotalDiscountSales','cmp_obj'));
        }
        
        foreach ($discountssales_manufacturer as $manufacturer_id => $discountsales) {
            $discountssales_sort_manufacturer = array();
            $discountssales_sort_manufacturer[] = 0;
            $this->max_interval_manufacturer[$manufacturer_id]['name'] = '';
            foreach ($discountsales as $key => $value) {
                $discountssales_sort_manufacturer[] = $value['sales_limitation'];
                $this->max_interval_manufacturer[$manufacturer_id]['name'] = $value['name'];
            }
            asort($discountssales_sort_manufacturer);
            $this->max_interval_manufacturer[$manufacturer_id]['max_interval'] = 0;
            $this->max_interval_manufacturer[$manufacturer_id]['total_manufacturer'] = 0;
            $total = $products_filter[$manufacturer_id]['total_manufacturer'];
            for ($j=0;$j<count($discountssales_sort_manufacturer);$j++) {
                if($total >= $discountssales_sort_manufacturer[$j] && isset($discountssales_sort_manufacturer[$j+1]) && $total < $discountssales_sort_manufacturer[$j+1]){
                    $this->max_interval_manufacturer[$manufacturer_id]['max_interval'] = $discountssales_sort_manufacturer[$j+1];
                    $this->max_interval_manufacturer[$manufacturer_id]['total_manufacturer'] = $total;
                }
            }
        }
        return;
    }
    
    private function checkIdComplect($all_option,$id_complect) {
        if($all_option && is_array($all_option)){
            foreach ($all_option as $key => $value) {
                if($value==$id_complect){
                    return TRUE;
                }
            }
        }
        return FALSE;
    }
    
    private function getIdComplect($all_option) {
        if($all_option && is_array($all_option)){
            foreach ($all_option as $key => $value) {
                if($key==1000000){
                    return $value;
                }
            }
        }
        return FALSE;
    }
    
    private function setIntervalComplect($discountssales_complect){
        //return;
        $this->max_interval_complect = array();
        $products = array();
        //продукты в корзинке
        $products_to_cart = $this->getProductsToCart();
        $products_to_cart_other_discount = array();
        $other_discount_status = FALSE;
        $discountssales_complect_tmp = $discountssales_complect;
        $discountssales_complect = array();
        if($discountssales_complect_tmp and is_array($discountssales_complect_tmp)){
            foreach ($discountssales_complect_tmp as $key_complect => $complect) {
                if($complect['products'] && is_array($complect['products'])){
                    ksort($complect['products']);
                    $products = $complect['products'];
                    $product_quantity = $complect['product_quantity'];
                    $total_complect = array();
                    $id_complect = '';
                    $quantity_other_discount = array();
                    foreach ($products as $product_id => $product) {
                        
                        foreach ($products_to_cart as $key_product_to_cart => $product_to_cart) {
                            
                            $check_id_complect = $this->getIdComplect($product_to_cart['all_option']);
                            
                            if($product_id==$product_to_cart['product_id'] && $check_id_complect && $product_to_cart['quantity']==$product_quantity[$product_id]['quantity']){
                                unset($products[$product_id]);
                                $quantity = (int)$product_quantity[$product_id]['quantity'];
                                $id_complect = $check_id_complect;
                                
                                //изменим цену у товара, если она другая в этом комплекте
                                $product_quantity[$product_id]['new_price'] = trim($product_quantity[$product_id]['new_price']);
                                $new_price = $this->getFloat($product_quantity[$product_id]['new_price']);
                                if(isset($new_price) && $new_price){
                                    $product_to_cart['price'] = $new_price;
                                }elseif ($product_quantity[$product_id]['new_price']!='') {
                                    $product_to_cart['price'] = 0.0;
                                }
                                if(!isset($total_complect[$id_complect])){
                                    $total_complect[$id_complect] = $quantity*$product_to_cart['price'];
                                }else{
                                    $total_complect[$id_complect] += $quantity*$product_to_cart['price'];
                                }
                                if(isset($complect['other_discount']) && $complect['other_discount'] && $this->checkIdComplect($product_to_cart['all_option'],$id_complect)){
                                    $quantity_other_discount[$key_product_to_cart]['quantity'] = $quantity;
                                    $quantity_other_discount[$key_product_to_cart]['all_option'] = $product_to_cart['all_option'];
                                }
                            }
                        }
                    }
                    if(isset($total_complect[$id_complect]) && $total_complect[$id_complect] && $quantity_other_discount){
                        $this->setOtherDiscountStatus('total_other_discount', ($this->getOtherDiscountStatus('total_other_discount') + $total_complect[$id_complect]) );
                        //собираем остатки в корзине, чтобы убрать эти товары из других скидок
                        foreach ($quantity_other_discount as $key_product_to_cart => $quantity_other_discount_row){
                            $products_to_cart_other_discount[$key_product_to_cart] = $products_to_cart[$key_product_to_cart];
                            $products_to_cart_other_discount[$key_product_to_cart]['quantity'] = $quantity_other_discount_row['quantity'];
                            $products_to_cart_other_discount[$key_product_to_cart]['all_option'] = $quantity_other_discount_row['all_option'];
                        }
                        $other_discount_status = TRUE;
                    }
                    if($other_discount_status){
                        $this->setOtherDiscountStatus('new_products_to_cart',$products_to_cart_other_discount);
                    }
                    //если $products остался, то комплект не соответствует корзинке по количеству и / по набору
                    if( (!isset($products) || !$products) && $id_complect && isset($total_complect[$id_complect])){
                        $complect['total_complect'] = $total_complect[$id_complect];
                        $discountssales_complect[$id_complect][] = $complect;
                    }
                }
            }
        }
        if(!$discountssales_complect){
            return;
        }
        
        foreach ($discountssales_complect as $key => $value) {
            usort($discountssales_complect[$key], array('ModelTotalDiscountSales','cmp_obj'));
        }
        
        foreach ($discountssales_complect as $id_complect => $discountsales) {
            $discountssales_sort_complect = array();
            $discountssales_sort_complect[] = 0;
            $this->max_interval_complect[$id_complect]['name'] = '';
            $this->max_interval_complect[$id_complect]['total_complect'] = 0;
            foreach ($discountsales as $key => $value) {
                $discountssales_sort_complect[] = $value['sales_limitation'];
                $this->max_interval_complect[$id_complect]['name'] = $value['name_complect'];
                $total = $value['total_complect'];
            }
            asort($discountssales_sort_complect);
            $this->max_interval_complect[$id_complect]['max_interval'] = 0;
            for ($j=0;$j<count($discountssales_sort_complect);$j++) {
                if($total >= $discountssales_sort_complect[$j] && isset($discountssales_sort_complect[$j+1]) && $total < $discountssales_sort_complect[$j+1]){
                    $this->max_interval_complect[$id_complect]['max_interval'] = $discountssales_sort_complect[$j+1];
                    $this->max_interval_complect[$id_complect]['total_complect'] = $total;
                }
            }
        }
        
        
        foreach ($discountssales_complect as $key_complect => $discountsales) {
            $discountssales_sort_complect = array();
            $discountssales_sort_complect[] = 0;
            $this->max_interval_complect[$key_complect]['name'] = '';
            foreach ($discountsales as $key => $discountsales) {
                $discountssales_sort_complect[] = $discountsales['sales_limitation'];
                $this->max_interval_complect[$key_complect]['name'] = trim($discountsales['name_complect']);
            }
            asort($discountssales_sort_complect);
            $this->max_interval_complect[$key_complect]['max_interval'] = 0;
            $this->max_interval_complect[$key_complect]['total_complect'] = 0;
            $total = $discountsales['total_complect'];
            for ($j=0;$j<count($discountssales_sort_complect);$j++) {
                if($total >= $discountssales_sort_complect[$j] && isset($discountssales_sort_complect[$j+1]) && $total < $discountssales_sort_complect[$j+1]){
                    $this->max_interval_complect[$key_complect]['max_interval'] = $discountssales_sort_complect[$j+1];
                    $this->max_interval_complect[$key_complect]['total_complect'] = $total;
                    $this->max_interval_complect[$key_complect]['id_complect'] = $key_complect;
                }
            }
        }
        return;
    }
    
    private function getTimeStamp($date) {
        $timestamp = FALSE;
        if($date){
            $date = explode('-', $date);
            if(isset($date[0]) && isset($date[1]) && isset($date[2]) ){
                $timestamp = mktime(0, 0, 0, (int)$date[1], (int)$date[2], (int)$date[0]);
            }
        }
        return $timestamp;
    }
    
    
    private function deleteDisableRows(&$discountssales){
        
        if(!$this->products_whis_base_discounts){
            $this->setProductsWhisBaseDiscounts();
        }
        
        if(!$this->config->get('discountsales_discount_status') || ($this->status_together && !$this->config->get('discountsales_status_together'))){
            $discountssales = array();
            return;
        }
        foreach ($discountssales as $key => $value) {
            $time = $this->getTimeStamp(date('Y-m-d', time()));
            $date_start = 0;
            $date_finish = FALSE;
            if(isset($value['date_start']) && $value['date_start']){
                $date_start = $this->getTimeStamp($value['date_start']);
            }
            if(isset($value['date_finish']) && $value['date_finish']){
                $date_finish = $this->getTimeStamp($value['date_finish']);
            }
            $time_interval = TRUE;
            if($time<$date_start){
                $time_interval = FALSE;
            }
            if($date_finish && $time>$date_finish){
                $time_interval = FALSE;
            }
            if(!$value['status'] || !$time_interval){
                unset($discountssales[$key]);
            }
        }
    }
    private function deleteDisableRowsCategory(&$discountssales_category){
        
        if(!$this->products_whis_base_discounts){
            $this->setProductsWhisBaseDiscounts();
        }
        
        if(!$this->config->get('discountsales_category_status') || ($this->status_together && !$this->config->get('discountsales_category_status_together'))){
            $discountssales_category = array();
            return;
        }
        
        foreach ($discountssales_category as $key => $value) {
            $time = $this->getTimeStamp(date('Y-m-d', time()));
            $date_start = 0;
            $date_finish = FALSE;
            if(isset($value['date_start']) && $value['date_start']){
                $date_start = $this->getTimeStamp($value['date_start']);
            }
            if(isset($value['date_finish']) && $value['date_finish']){
                $date_finish = $this->getTimeStamp($value['date_finish']);
            }
            $time_interval = TRUE;
            if($time<$date_start){
                $time_interval = FALSE;
            }
            if($date_finish && $time>$date_finish){
                $time_interval = FALSE;
            }
            if(!$value['status'] || !$time_interval){
                unset($discountssales_category[$key]);
            }
        }
    }
    private function deleteDisableRowsManufacturer(&$discountssales_manufacturer){
        
        if(!$this->products_whis_base_discounts){
            $this->setProductsWhisBaseDiscounts();
        }
        
        if(!$this->config->get('discountsales_manufacturer_status') || ($this->status_together && !$this->config->get('discountsales_manufacturer_status_together'))){
            $discountssales_manufacturer = array();
            return;
        }
        foreach ($discountssales_manufacturer as $key => $value) {
            $time = $this->getTimeStamp(date('Y-m-d', time()));
            $date_start = 0;
            $date_finish = FALSE;
            if(isset($value['date_start']) && $value['date_start']){
                $date_start = $this->getTimeStamp($value['date_start']);
            }
            if(isset($value['date_finish']) && $value['date_finish']){
                $date_finish = $this->getTimeStamp($value['date_finish']);
            }
            $time_interval = TRUE;
            if($time<$date_start){
                $time_interval = FALSE;
            }
            if($date_finish && $time>$date_finish){
                $time_interval = FALSE;
            }
            if(!$value['status'] || !$time_interval){
                unset($discountssales_manufacturer[$key]);
            }
        }
    }
    private function deleteDisableRowsComlect(&$discountssales_complect){
        
        if(!$this->products_whis_base_discounts){
            $this->setProductsWhisBaseDiscounts();
        }
        
        if(!$this->config->get('discountsales_complect_status') || ($this->status_together && !$this->config->get('discountsales_complect_status_together'))){
            $discountssales_complect = array();
            return;
        }
        foreach ($discountssales_complect as $key => $value) {
            $time = $this->getTimeStamp(date('Y-m-d', time()));
            $date_start = 0;
            $date_finish = FALSE;
            if(isset($value['date_start']) && $value['date_start']){
                $date_start = $this->getTimeStamp($value['date_start']);
            }
            if(isset($value['date_finish']) && $value['date_finish']){
                $date_finish = $this->getTimeStamp($value['date_finish']);
            }
            $time_interval = TRUE;
            if($time<$date_start){
                $time_interval = FALSE;
            }
            if($date_finish && $time>$date_finish){
                $time_interval = FALSE;
            }
            if(!$value['status'] || !$time_interval){
                unset($discountssales_complect[$key]);
            }
        }
    }
    
    public function getProductsByCategoryId($category_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_category ptc LEFT JOIN " . DB_PREFIX . "category_description cd ON (ptc.category_id = cd.category_id) WHERE ptc.category_id = '" . (int)$category_id . "' AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "' ");
        return $query->rows;
    }
    
    public function getProductsByManufacturerId($manufacturer_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "manufacturer m ON (p.manufacturer_id = m.manufacturer_id) WHERE p.manufacturer_id = '" . (int)$manufacturer_id . "'");
        return $query->rows;
    }
    

    public function getDiscountSalesForAllPayments($discountsales){
        $discountssales_sort_value_sales_all_payments[] = 0;
        $result = array();
        //Сортируем по лимиту
        usort($discountsales, array('ModelTotalDiscountSales','cmp_obj'));
        
        foreach ($discountsales as $key => $value) {
            if(!$value['payment_code'] && $value['status']){
                $discountssales_sort_value_sales_all_payments[] = $value['sales_limitation'];
                $result[$value['sales_limitation']] = $value;
            }
        }
        asort($discountssales_sort_value_sales_all_payments);
        for ($j=0;$j<count($discountssales_sort_value_sales_all_payments);$j++) {
            if(isset($discountssales_sort_value_sales_all_payments[$j]) && isset($discountssales_sort_value_sales_all_payments[$j+1]) && isset($result[ $discountssales_sort_value_sales_all_payments[$j+1] ])){
                
                if($this->opencart_version==22){
                                    
                    $text_whis_currency_min = $this->currency->format($discountssales_sort_value_sales_all_payments[$j],$this->session->data['currency']);
                    $text_whis_currency_max = $this->currency->format($discountssales_sort_value_sales_all_payments[$j+1],$this->session->data['currency']);

                }else{

                    $text_whis_currency_min = $this->currency->format($discountssales_sort_value_sales_all_payments[$j]);
                    $text_whis_currency_max = $this->currency->format($discountssales_sort_value_sales_all_payments[$j+1]);

                }
                
                $result[ $discountssales_sort_value_sales_all_payments[$j+1] ]['min'] = $text_whis_currency_min;
                $result[ $discountssales_sort_value_sales_all_payments[$j+1] ]['max'] = $text_whis_currency_max;
                $discountsale = $result[ $discountssales_sort_value_sales_all_payments[$j+1] ];
                if($discountsale['type_data']){
                    $value_data = round($discountsale['value_data'],2).'%';$text = $this->language->get('text_discount');
                }else{
                    
                    if($this->opencart_version==22){
                                    
                        $text_whis_currency = $this->currency->format($discountsale['value_data'],$this->session->data['currency']);

                    }else{

                        $text_whis_currency = $this->currency->format($discountsale['value_data']);

                    }
                    
                    $value_data = $text_whis_currency;
                }
                
                
                if($this->opencart_version==22){
                                    
                    $text_whis_currency = $this->currency->format($discountsale['sales_limitation'],$this->session->data['currency']);

                }else{

                    $text_whis_currency = $this->currency->format($discountsale['sales_limitation']);

                }
                
                $sales_limitation = $text_whis_currency;
                $sales_limitation_float = (float)$discountsale['sales_limitation'];
                $value_data_float = (float)$discountsale['value_data'];
                if($discountsale['above_zero']){
                    $text = $this->language->get('text_discount');
                }else{
                    $text = $this->language->get('text_margin');
                }
                $result[ $discountssales_sort_value_sales_all_payments[$j+1] ]['value_data'] = $value_data;
                $result[ $discountssales_sort_value_sales_all_payments[$j+1] ]['value_data_float'] = $value_data_float;
                $result[ $discountssales_sort_value_sales_all_payments[$j+1] ]['sales_limitation_float'] = $sales_limitation_float;
                $result[ $discountssales_sort_value_sales_all_payments[$j+1] ]['text'] = $text;
                $result[ $discountssales_sort_value_sales_all_payments[$j+1] ]['sales_limitation'] = $sales_limitation;
            }
        }
        ksort($result);
        return $result;
    }
    
        private function getFloat($string){
            $find = array('-',',',' ');
            $replace = array('.','.','');
            $result = (float)str_replace($find, $replace, $string);
            return $result;
        }
        
        public function getProduct($product_id) {
		$product_data = array();

		$stock = true;

			$product_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_store p2s LEFT JOIN " . DB_PREFIX . "product p ON (p2s.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND p2s.product_id = '" . (int)$product_id . "' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.date_available <= NOW() AND p.status = '1'");

			if ($product_query->row) {

				$price = $product_query->row['price'];

				// Product Discounts
				$discount_quantity = 0;

                                if($this->showTable('cart', DB_PREFIX)){
                                    
                                        $cart_2_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "cart WHERE customer_id = '" . (int)$this->customer->getId() . "' AND session_id = '" . $this->db->escape($this->session->getId()) . "'");

                                        foreach ($cart_2_query->rows as $cart_2) {
                                                if ($cart_2['product_id'] == $product_id) {
                                                        $discount_quantity += $cart_2['quantity'];
                                                }
                                        }
                                    
                                }

				$product_discount_query = $this->db->query("SELECT price FROM " . DB_PREFIX . "product_discount WHERE product_id = '" . (int)$product_id . "' AND customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND quantity <= '" . (int)$discount_quantity . "' AND ((date_start = '0000-00-00' OR date_start < NOW()) AND (date_end = '0000-00-00' OR date_end > NOW())) ORDER BY quantity DESC, priority ASC, price ASC LIMIT 1");

				if ($product_discount_query->num_rows) {
					$price = $product_discount_query->row['price'];
				}

				// Product Specials
				$product_special_query = $this->db->query("SELECT price FROM " . DB_PREFIX . "product_special WHERE product_id = '" . (int)$product_id . "' AND customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((date_start = '0000-00-00' OR date_start < NOW()) AND (date_end = '0000-00-00' OR date_end > NOW())) ORDER BY priority ASC, price ASC LIMIT 1");

				if ($product_special_query->num_rows && $price>$product_special_query->row['price']) {
					$price = $product_special_query->row['price'];
				}

				// Reward Points
				$product_reward_query = $this->db->query("SELECT points FROM " . DB_PREFIX . "product_reward WHERE product_id = '" . (int)$product_id . "' AND customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "'");

				if ($product_reward_query->num_rows) {
					$reward = $product_reward_query->row['points'];
				} else {
					$reward = 0;
				}

				// Stock
				if (!$product_query->row['quantity']) {
					$stock = false;
				}

				$recurring_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "recurring r LEFT JOIN " . DB_PREFIX . "product_recurring pr ON (r.recurring_id = pr.recurring_id) LEFT JOIN " . DB_PREFIX . "recurring_description rd ON (r.recurring_id = rd.recurring_id) WHERE pr.product_id = '" . (int)$product_id . "' AND rd.language_id = " . (int)$this->config->get('config_language_id') . " AND r.status = 1 AND pr.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "'");

				if ($recurring_query->num_rows) {
					$recurring = array(
						'name'            => $recurring_query->row['name'],
						'frequency'       => $recurring_query->row['frequency'],
						'price'           => $recurring_query->row['price'],
						'cycle'           => $recurring_query->row['cycle'],
						'duration'        => $recurring_query->row['duration'],
						'trial'           => $recurring_query->row['trial_status'],
						'trial_frequency' => $recurring_query->row['trial_frequency'],
						'trial_price'     => $recurring_query->row['trial_price'],
						'trial_cycle'     => $recurring_query->row['trial_cycle'],
						'trial_duration'  => $recurring_query->row['trial_duration']
					);
				} else {
					$recurring = false;
				}
                                $minimum = 1;
                                if($product_query->row['minimum']){
                                    $minimum = (int)$product_query->row['minimum'];
                                }
                                
				$product_data[] = array(
					'product_id'      => $product_query->row['product_id'],
					'name'            => $product_query->row['name'],
					'model'           => $product_query->row['model'],
					'shipping'        => $product_query->row['shipping'],
					'image'           => $product_query->row['image'],
					'quantity'        => $product_query->row['quantity'],
					'minimum'         => $minimum,
					'subtract'        => $product_query->row['subtract'],
					'stock'           => $stock,
					'price'           => $price,
                                        'special'           => 0,
                                    'rating'           => 0,
					'total'           => $price * $minimum,
					'reward'          => $reward * $minimum,
					'tax_class_id'    => $product_query->row['tax_class_id'],
					'weight_class_id' => $product_query->row['weight_class_id'],
					'length'          => $product_query->row['length'],
					'width'           => $product_query->row['width'],
					'height'          => $product_query->row['height'],
					'length_class_id' => $product_query->row['length_class_id'],
                                    'description'   =>$product_query->row['description'],
					'recurring'       => $recurring
				);
			}

		return $product_data[0];
	}
        
        public function getProductsToCart() {
		$product_data = array();
                $cart_query_rows = array();
                
                if($this->showTable('cart', DB_PREFIX)){
                    $cart_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "cart WHERE customer_id = '" . (int)$this->customer->getId() . "' AND session_id = '" . $this->db->escape($this->session->getId()) . "'");
                    if($cart_query->rows){
                        $cart_query_rows = $cart_query->rows;
                    }
                }else{
                    if (isset($this->session->data['cart']) && is_array($this->session->data['cart'])) {
			foreach ($this->session->data['cart'] as $key_row_to_cart => $quantity_row_to_cart) {
                            $product_row_to_cart = unserialize(base64_decode($key_row_to_cart));
                            if(!isset($product_row_to_cart['option'])){
                                $product_row_to_cart['option'] = json_encode(array());
                            }else{
                                $product_row_to_cart['option'] = json_encode($product_row_to_cart['option']);
                            }
                            if(!isset($product_row_to_cart['recurring_id'])){
                                $product_row_to_cart['recurring_id'] = 0;
                            }
                            $cart_query_rows[] = array(
                                'product_id'=>$product_row_to_cart['product_id'],
                                'option'=>$product_row_to_cart['option'],
                                'recurring_id'=>$product_row_to_cart['recurring_id'],
                                'quantity'=>$quantity_row_to_cart,
                                'cart_id'=>$key_row_to_cart,
                                'key'=>$key_row_to_cart
                            );
                        }
                    }
                }
		foreach ($cart_query_rows as $cart) {
			$stock = true;

			$product_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_store p2s LEFT JOIN " . DB_PREFIX . "product p ON (p2s.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND p2s.product_id = '" . (int)$cart['product_id'] . "' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.date_available <= NOW() AND p.status = '1'");

			if ($product_query->num_rows && ($cart['quantity'] > 0)) {
				$option_price = 0;
				$option_points = 0;
				$option_weight = 0;

				$option_data = array();
				foreach (json_decode($cart['option']) as $product_option_id => $value) {
					$option_query = $this->db->query("SELECT po.product_option_id, po.option_id, od.name, o.type FROM " . DB_PREFIX . "product_option po LEFT JOIN `" . DB_PREFIX . "option` o ON (po.option_id = o.option_id) LEFT JOIN " . DB_PREFIX . "option_description od ON (o.option_id = od.option_id) WHERE po.product_option_id = '" . (int)$product_option_id . "' AND po.product_id = '" . (int)$cart['product_id'] . "' AND od.language_id = '" . (int)$this->config->get('config_language_id') . "'");

					if ($option_query->num_rows) {
						if ($option_query->row['type'] == 'select' || $option_query->row['type'] == 'radio' || $option_query->row['type'] == 'image') {
							$option_value_query = $this->db->query("SELECT pov.option_value_id, ovd.name, pov.quantity, pov.subtract, pov.price, pov.price_prefix, pov.points, pov.points_prefix, pov.weight, pov.weight_prefix FROM " . DB_PREFIX . "product_option_value pov LEFT JOIN " . DB_PREFIX . "option_value ov ON (pov.option_value_id = ov.option_value_id) LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) WHERE pov.product_option_value_id = '" . (int)$value . "' AND pov.product_option_id = '" . (int)$product_option_id . "' AND ovd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

							if ($option_value_query->num_rows) {
								if ($option_value_query->row['price_prefix'] == '+') {
									$option_price += $option_value_query->row['price'];
								} elseif ($option_value_query->row['price_prefix'] == '-') {
									$option_price -= $option_value_query->row['price'];
								}

								if ($option_value_query->row['points_prefix'] == '+') {
									$option_points += $option_value_query->row['points'];
								} elseif ($option_value_query->row['points_prefix'] == '-') {
									$option_points -= $option_value_query->row['points'];
								}

								if ($option_value_query->row['weight_prefix'] == '+') {
									$option_weight += $option_value_query->row['weight'];
								} elseif ($option_value_query->row['weight_prefix'] == '-') {
									$option_weight -= $option_value_query->row['weight'];
								}

								if ($option_value_query->row['subtract'] && (!$option_value_query->row['quantity'] || ($option_value_query->row['quantity'] < $cart['quantity']))) {
									$stock = false;
								}

								$option_data[] = array(
									'product_option_id'       => $product_option_id,
									'product_option_value_id' => $value,
									'option_id'               => $option_query->row['option_id'],
									'option_value_id'         => $option_value_query->row['option_value_id'],
									'name'                    => $option_query->row['name'],
									'value'                   => $option_value_query->row['name'],
									'type'                    => $option_query->row['type'],
									'quantity'                => $option_value_query->row['quantity'],
									'subtract'                => $option_value_query->row['subtract'],
									'price'                   => $option_value_query->row['price'],
									'price_prefix'            => $option_value_query->row['price_prefix'],
									'points'                  => $option_value_query->row['points'],
									'points_prefix'           => $option_value_query->row['points_prefix'],
									'weight'                  => $option_value_query->row['weight'],
									'weight_prefix'           => $option_value_query->row['weight_prefix']
								);
							}
						} elseif ($option_query->row['type'] == 'checkbox' && is_array($value)) {
							foreach ($value as $product_option_value_id) {
								$option_value_query = $this->db->query("SELECT pov.option_value_id, ovd.name, pov.quantity, pov.subtract, pov.price, pov.price_prefix, pov.points, pov.points_prefix, pov.weight, pov.weight_prefix FROM " . DB_PREFIX . "product_option_value pov LEFT JOIN " . DB_PREFIX . "option_value ov ON (pov.option_value_id = ov.option_value_id) LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) WHERE pov.product_option_value_id = '" . (int)$product_option_value_id . "' AND pov.product_option_id = '" . (int)$product_option_id . "' AND ovd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

								if ($option_value_query->num_rows) {
									if ($option_value_query->row['price_prefix'] == '+') {
										$option_price += $option_value_query->row['price'];
									} elseif ($option_value_query->row['price_prefix'] == '-') {
										$option_price -= $option_value_query->row['price'];
									}

									if ($option_value_query->row['points_prefix'] == '+') {
										$option_points += $option_value_query->row['points'];
									} elseif ($option_value_query->row['points_prefix'] == '-') {
										$option_points -= $option_value_query->row['points'];
									}

									if ($option_value_query->row['weight_prefix'] == '+') {
										$option_weight += $option_value_query->row['weight'];
									} elseif ($option_value_query->row['weight_prefix'] == '-') {
										$option_weight -= $option_value_query->row['weight'];
									}

									if ($option_value_query->row['subtract'] && (!$option_value_query->row['quantity'] || ($option_value_query->row['quantity'] < $cart['quantity']))) {
										$stock = false;
									}

									$option_data[] = array(
										'product_option_id'       => $product_option_id,
										'product_option_value_id' => $product_option_value_id,
										'option_id'               => $option_query->row['option_id'],
										'option_value_id'         => $option_value_query->row['option_value_id'],
										'name'                    => $option_query->row['name'],
										'value'                   => $option_value_query->row['name'],
										'type'                    => $option_query->row['type'],
										'quantity'                => $option_value_query->row['quantity'],
										'subtract'                => $option_value_query->row['subtract'],
										'price'                   => $option_value_query->row['price'],
										'price_prefix'            => $option_value_query->row['price_prefix'],
										'points'                  => $option_value_query->row['points'],
										'points_prefix'           => $option_value_query->row['points_prefix'],
										'weight'                  => $option_value_query->row['weight'],
										'weight_prefix'           => $option_value_query->row['weight_prefix']
									);
								}
							}
						} elseif ($option_query->row['type'] == 'text' || $option_query->row['type'] == 'textarea' || $option_query->row['type'] == 'file' || $option_query->row['type'] == 'date' || $option_query->row['type'] == 'datetime' || $option_query->row['type'] == 'time') {
							$option_data[] = array(
								'product_option_id'       => $product_option_id,
								'product_option_value_id' => '',
								'option_id'               => $option_query->row['option_id'],
								'option_value_id'         => '',
								'name'                    => $option_query->row['name'],
								'value'                   => $value,
								'type'                    => $option_query->row['type'],
								'quantity'                => '',
								'subtract'                => '',
								'price'                   => '',
								'price_prefix'            => '',
								'points'                  => '',
								'points_prefix'           => '',
								'weight'                  => '',
								'weight_prefix'           => ''
							);
						}
					}
				}

				$price = $product_query->row['price'];

				// Product Discounts
				$discount_quantity = 0;
                                
                                
                                if($this->showTable('cart', DB_PREFIX)){
                                    $cart_2_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "cart WHERE customer_id = '" . (int)$this->customer->getId() . "' AND session_id = '" . $this->db->escape($this->session->getId()) . "'");
                                    foreach ($cart_2_query->rows as $cart_2) {
					if ($cart_2['product_id'] == $cart['product_id']) {
						$discount_quantity += $cart_2['quantity'];
					}
                                    }
                                }

				$product_discount_query = $this->db->query("SELECT price FROM " . DB_PREFIX . "product_discount WHERE product_id = '" . (int)$cart['product_id'] . "' AND customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND quantity <= '" . (int)$discount_quantity . "' AND ((date_start = '0000-00-00' OR date_start < NOW()) AND (date_end = '0000-00-00' OR date_end > NOW())) ORDER BY quantity DESC, priority ASC, price ASC LIMIT 1");
                                
                                $base_discount_price = FALSE;
                                
				if ($product_discount_query->num_rows) {
					$price = $product_discount_query->row['price'];
                                        $base_discount_price = TRUE;
				}

				// Product Specials
				$product_special_query = $this->db->query("SELECT price FROM " . DB_PREFIX . "product_special WHERE product_id = '" . (int)$cart['product_id'] . "' AND customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((date_start = '0000-00-00' OR date_start < NOW()) AND (date_end = '0000-00-00' OR date_end > NOW())) ORDER BY priority ASC, price ASC LIMIT 1");

				if ($product_special_query->num_rows) {
					$price = $product_special_query->row['price'];
                                        $base_discount_price = TRUE;
				}

				// Reward Points
				$product_reward_query = $this->db->query("SELECT points FROM " . DB_PREFIX . "product_reward WHERE product_id = '" . (int)$cart['product_id'] . "' AND customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "'");

				if ($product_reward_query->num_rows) {
					$reward = $product_reward_query->row['points'];
				} else {
					$reward = 0;
				}

				// Downloads
				$download_data = array();

				$download_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_download p2d LEFT JOIN " . DB_PREFIX . "download d ON (p2d.download_id = d.download_id) LEFT JOIN " . DB_PREFIX . "download_description dd ON (d.download_id = dd.download_id) WHERE p2d.product_id = '" . (int)$cart['product_id'] . "' AND dd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

				foreach ($download_query->rows as $download) {
					$download_data[] = array(
						'download_id' => $download['download_id'],
						'name'        => $download['name'],
						'filename'    => $download['filename'],
						'mask'        => $download['mask']
					);
				}

				// Stock
				if (!$product_query->row['quantity'] || ($product_query->row['quantity'] < $cart['quantity'])) {
					$stock = false;
				}

				$recurring_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "recurring r LEFT JOIN " . DB_PREFIX . "product_recurring pr ON (r.recurring_id = pr.recurring_id) LEFT JOIN " . DB_PREFIX . "recurring_description rd ON (r.recurring_id = rd.recurring_id) WHERE r.recurring_id = '" . (int)$cart['recurring_id'] . "' AND pr.product_id = '" . (int)$cart['product_id'] . "' AND rd.language_id = " . (int)$this->config->get('config_language_id') . " AND r.status = 1 AND pr.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "'");

				if ($recurring_query->num_rows) {
					$recurring = array(
						'recurring_id'    => $cart['recurring_id'],
						'name'            => $recurring_query->row['name'],
						'frequency'       => $recurring_query->row['frequency'],
						'price'           => $recurring_query->row['price'],
						'cycle'           => $recurring_query->row['cycle'],
						'duration'        => $recurring_query->row['duration'],
						'trial'           => $recurring_query->row['trial_status'],
						'trial_frequency' => $recurring_query->row['trial_frequency'],
						'trial_price'     => $recurring_query->row['trial_price'],
						'trial_cycle'     => $recurring_query->row['trial_cycle'],
						'trial_duration'  => $recurring_query->row['trial_duration']
					);
				} else {
					$recurring = false;
				}
                                
                                
                                $this->load->model('total/discountsales');
                                
                                $all_option = array();
                                if($cart['option']){
                                    $all_option = (array)json_decode($cart['option']);
                                }
                                
				$product_data[] = array(
					'cart_id'         => $cart['cart_id'],
					'product_id'      => $product_query->row['product_id'],
					'name'            => $product_query->row['name'],
					'model'           => $product_query->row['model'],
					'shipping'        => $product_query->row['shipping'],
					'image'           => $product_query->row['image'],
					'option'          => $option_data,
                                        'all_option'          => $all_option,
					'download'        => $download_data,
					'quantity'        => $cart['quantity'],
					'minimum'         => $product_query->row['minimum'],
					'subtract'        => $product_query->row['subtract'],
					'stock'           => $stock,
					'price'           => ($price + $option_price),
					'total'           => ($price + $option_price) * $cart['quantity'],
					'reward'          => $reward * $cart['quantity'],
					'points'          => ($product_query->row['points'] ? ($product_query->row['points'] + $option_points) * $cart['quantity'] : 0),
					'tax_class_id'    => $product_query->row['tax_class_id'],
					'weight'          => ($product_query->row['weight'] + $option_weight) * $cart['quantity'],
					'weight_class_id' => $product_query->row['weight_class_id'],
					'length'          => $product_query->row['length'],
					'width'           => $product_query->row['width'],
					'height'          => $product_query->row['height'],
					'length_class_id' => $product_query->row['length_class_id'],
					'recurring'       => $recurring,
                                        'base_discount_price' => $base_discount_price
				);
			} else {
				$this->remove($cart['cart_id']);
			}
		}

		return $product_data;
	}
        /*
         * @all_categories - общая для всех категорий, т.к. товар может быть в нескольних. Для расчета цены товара в карточке товара
         */
    public function getDiscountInfo($product_id,$all_categories=FALSE) {
        
        
        $this->status_together = FALSE;
        
        $product = $this->getProduct($product_id);
        
        $total_data = new stdClass();
        
        if(!$product){
            return $total_data;
        }
        
        $discountssales = $this->config->get('discountsales_discount');
        $discountssales_category = $this->config->get('discountsales_category_discount');
        $discountssales_manufacturer = $this->config->get('discountsales_manufacturer_discount');
        
        $payment_method = FALSE;
        $payment_title = '';
        
        $total_whis_cart = $product['total'] + $this->cart->getTotal();
        
        
        
        
        $sort_discounts = array();
        $sort_position = 0;
        if($this->config->get('discountsales_sort_order')){
            $sort_position = $this->config->get('discountsales_sort_order');
            $sort_discounts[ 'discountsales_sort_order' ] = (int)$sort_position;
        }else{
            $sort_discounts[ 'discountsales_sort_order' ] = $sort_position;
        }
        $sort_position = 0;
        if($this->config->get('discountsales_category_sort_order')){
            $sort_position = $this->config->get('discountsales_category_sort_order');
            $sort_discounts[ 'discountsales_category_sort_order' ] = (int)$sort_position;
        }else{
            $sort_discounts[ 'discountsales_category_sort_order' ] = $sort_position;
        }
        $sort_position = 0;
        if($this->config->get('discountsales_manufacturer_sort_order')){
            $sort_position = $this->config->get('discountsales_manufacturer_sort_order');
            $sort_discounts[ 'discountsales_manufacturer_sort_order' ] = (int)$sort_position;
        }else{
            $sort_discounts[ 'discountsales_manufacturer_sort_order' ] = $sort_position;
        }
        
        asort($sort_discounts);
        
        $discountsales_promo = $this->config->get('discountsales_promo');
        foreach ($sort_discounts as $key_sort_discounts => $tmp_sort_discounts) {
            if($key_sort_discounts=='discountsales_sort_order'){
                
                if($discountssales && $discountsales_promo['discount']['status']){
                    //Удалим строчки, которые выключилены, чтобы не мешались
                    $this->deleteDisableRows($discountssales);
                    //Определяем верхний предел, в котором сейчас тотал корзины (для выбранного метода, и для всех методов)
                    $this->setInterval($discountssales, $total_whis_cart);

                    //Если есть скидка вне зависимости от метода, делаем её первой
                    if(isset($discountssales) && $discountssales){

                        foreach($discountssales as $discountsales){

                            if (!$discountsales['payment_code'] && $discountsales['sales_limitation']==$this->max_interval_all_payments){

                                $value = $this->getTotalWhisDiscount($total_whis_cart, $discountssales, $payment_method, TRUE);
                                $value_real = $this->getTotalWhisDiscount($product['total'], $discountssales, $payment_method, TRUE);

                                //убираем продукты, которые вычеркнуты
                                $disable_products = array();
                                if(isset($discountsales['disable_categories']) && $discountsales['disable_categories'] && isset($this->disable_data['categories'][$discountsales['disable_categories']])){
                                    $disable_products = $this->disable_data['categories'][$discountsales['disable_categories']]['products'];
                                }
                                
                                if($value!=0 && $value_real!=0 && !isset($disable_products[$product['product_id']])){
                                    $class = 'discountsales discount';
                                    $title = $this->getTitleForDoscountInfo($value_real, $discountssales, $payment_title, $payment_method, TRUE,$class,$discountsales_promo['discount']['text'],$discountsales_promo['discount']['colour']);
                                    
                                    if($this->opencart_version==22){
                                    
                                        $text_whis_currency = $this->currency->format($value_real,$this->session->data['currency']);

                                    }else{

                                        $text_whis_currency = $this->currency->format($value_real);

                                    }
                                    
                                    $total_data->discount = (object)array(
                                            'title'      => $title['html'],
                                            'title_string'      => $title['string'],
                                            'price'      => $product['price'],
                                            'total_price'      => $product['total'],
                                            'text'       => $text_whis_currency,
                                            'value'      => $value_real,
                                            'sort_order' => $this->config->get('discountsales_sort_order'),
                                            'position' => $discountsales_promo['discount']['position'],
                                            'colour'          => $discountsales_promo['discount']['colour']
                                    );
                                    $this->status_together = TRUE;
                                }

                            }
                        }

                    }
                }
            }elseif($key_sort_discounts=='discountsales_category_sort_order'){
                if($discountssales_category && $discountsales_promo['category']['status']){
                    ///Применияем скидка по категориям
                    $this->deleteDisableRowsCategory($discountssales_category);
                    $this->setIntervalCategoryDiscountInfo($discountssales_category,$product,$total_whis_cart);

                    if(isset($discountssales_category) && $discountssales_category){

                            foreach($discountssales_category as $discountsales){
                                $category_id = $discountsales['category_id'];
                                if (isset($this->max_interval_category[$category_id]) && $discountsales['sales_limitation']==$this->max_interval_category[$category_id]['max_interval']){
                                    $total_category = $this->max_interval_category[$category_id]['total_category'];
                                    $value = $this->getTotalWhisDiscountPlus($total_category, $discountsales);
                                    $value_real = $this->getTotalWhisDiscountPlus($product['total'], $discountsales);
                                    $name = $this->max_interval_category[$category_id]['name'];
                                    if($value!=0 && $value_real!=0){
                                        $class = 'discountsales discount_category';
                                        $title = $this->getTitleTotalPlusDoscountInfo($discountsales,'text_discountsales_category',$name,$class,$discountsales_promo['category']['text'],$discountsales_promo['category']['colour']);
                                        
                                        if($this->opencart_version==22){
                                    
                                            $text_whis_currency = $this->currency->format($value_real,$this->session->data['currency']);

                                        }else{

                                            $text_whis_currency = $this->currency->format($value_real);

                                        }
                                        
                                        if(!$all_categories){
                                            $total_data->discount_category = (object)array(
                                                'title'      => $title['html'],
                                                'title_string'      => $title['string'],
                                                'total_price'      => $product['total'],
                                                'text'       => $text_whis_currency,
                                                'value'      => $value_real,
                                                'sort_order' => $this->config->get('discountsales_category_sort_order'),
                                                'position' => $discountsales_promo['category']['position'],
                                                'colour'          => $discountsales_promo['category']['colour']
                                            );
                                        }else{
                                            $total_data->discount_category[$category_id] = (object)array(
                                                'title'      => $title['html'],
                                                'title_string'      => $title['string'],
                                                'total_price'      => $product['total'],
                                                'text'       => $text_whis_currency,
                                                'value'      => $value_real,
                                                'sort_order' => $this->config->get('discountsales_category_sort_order'),
                                                'position' => $discountsales_promo['category']['position'],
                                                'colour'          => $discountsales_promo['category']['colour']
                                            );
                                        }
                                        $this->status_together = TRUE;

                                    }
                                }
                            }
                        }

                }
            }elseif($key_sort_discounts=='discountsales_manufacturer_sort_order'){
                if($discountssales_manufacturer && $discountsales_promo['manufacturer']['status']){
                    $this->deleteDisableRowsManufacturer($discountssales_manufacturer);
                    $this->setIntervalManufacturerDiscountInfo($discountssales_manufacturer,$product,$total_whis_cart);
                    foreach($discountssales_manufacturer as $discountsales){
                        $manufacturer_id = $discountsales['manufacturer_id'];
                        if (isset($this->max_interval_manufacturer[$manufacturer_id]) && $discountsales['sales_limitation']==$this->max_interval_manufacturer[$manufacturer_id]['max_interval']){
                            $total_manufacturer = $this->max_interval_manufacturer[$manufacturer_id]['total_manufacturer'];
                            $value = $this->getTotalWhisDiscountPlus($total_manufacturer, $discountsales);
                            $value_real = $this->getTotalWhisDiscountPlus($product['total'], $discountsales);
                            
                            $name = $this->max_interval_manufacturer[$manufacturer_id]['name'];
                            if($value!=0 && $value_real!=0){
                                $class = 'discountsales discount_manufacturer';
                                $title = $this->getTitleTotalPlusDoscountInfo($discountsales,'text_discountsales_manufacturer',$name,$class,$discountsales_promo['manufacturer']['text'],$discountsales_promo['manufacturer']['colour']);
                                
                                if($this->opencart_version==22){
                                    
                                    $text_whis_currency = $this->currency->format($value_real,$this->session->data['currency']);
                                    
                                }else{
                                    
                                    $text_whis_currency = $this->currency->format($value_real);
                                    
                                }
                                
                                $total_data->discount_manufacturer = (object)array(
                                    'title'      => $title['html'],
                                    'title_string'      => $title['string'],
                                    'price'      => $product['price'],
                                    'total_price'      => $product['total'],
                                    'text'       => $text_whis_currency,
                                    'value'      => $value_real,
                                    'sort_order' => $this->config->get('discountsales_manufacturer_sort_order'),
                                            'position' => $discountsales_promo['manufacturer']['position'],
                                            'colour'          => $discountsales_promo['manufacturer']['colour']
                                );
                                
                                
                                $this->status_together = TRUE;
                            }
                        }
                    }
                }
            }
        }
        return $total_data;
    }
    
    public function getComplectsToModule() {
        $discountssales_complect = $this->config->get('discountsales_complect_discount');
        $result_discountssales_complect = array();
        if($discountssales_complect){
            $this->deleteDisableRowsComlect($discountssales_complect);
            $this->setIntervalComplectToModule($discountssales_complect);
            foreach($discountssales_complect as $complect_id=>$discountsales){
                $complect_id = '';
                $products = $discountsales['products'];
                $products_data = array();
                ksort($products);
                $product_quantity = $discountsales['product_quantity'];
                foreach ($products as $product_id => $product) {
                    $quantity = (int)$product_quantity[$product_id]['quantity'];
                    $complect_id .= '_'.$product_id.'_'.$quantity;
                    $products_data[$product_id] = $this->getProduct($product_id);
                    if($products_data[$product_id] && $product_quantity[$product_id]['new_price']!=''){
                        $products_data[$product_id]['new_price'] = (float)$product_quantity[$product_id]['new_price'];
                    }elseif($products_data[$product_id]){
                        $products_data[$product_id]['new_price'] = $products_data[$product_id]['price'];
                    }
                    
                    if($products_data[$product_id]){
                        $products_data[$product_id]['complect_quantity'] = $quantity;
                    }
                }
                if (isset($this->max_interval_complect[$complect_id]) && $discountsales['sales_limitation']==$this->max_interval_complect[$complect_id]['max_interval']){
                    $total_complect = $this->max_interval_complect[$complect_id]['total_complect'];
                    $value = $this->getTotalWhisDiscountPlus($total_complect, $discountsales);
                    $name = $this->max_interval_complect[$complect_id]['name'];
                    if($value!=0){
                        $title = $this->getTitleTotalPlus($discountsales,'text_discountsales_complect',$name);
                        
                        if($this->opencart_version==22){
                                    
                            $text_whis_currency = $this->currency->format($value,$this->session->data['currency']);

                        }else{

                            $text_whis_currency = $this->currency->format($value);

                        }
                        
                        $result_discountssales_complect[$complect_id] = array(
                                'name_complect'      => $discountsales['name_complect'],
                                'title'      => $title,
                                'text'       => $text_whis_currency,
                                'value'      => $value,
                                'sort_order' => $this->config->get('discountsales_complect_sort_order'),
                                'products_data' => $products_data,
                                'total_complect_price' => $total_complect + $value
                        );
                    }
                }
            }
        }
        return $result_discountssales_complect;
    }
    
    private function setIntervalComplectToModule($discountssales_complect){
        $this->max_interval_complect = array();
        $products = array();
        $products_to_cart = $this->getProducts($discountssales_complect);
        $discountssales_complect_tmp = $discountssales_complect;
        $discountssales_complect = array();
        if($discountssales_complect_tmp and is_array($discountssales_complect_tmp)){
            foreach ($discountssales_complect_tmp as $key_complect => $complect) {
                if($complect['products'] && is_array($complect['products'])){
                    ksort($complect['products']);
                    $products = $complect['products'];
                    $product_quantity = $complect['product_quantity'];
                    $total_complect = 0;
                    $id_complect = '';
                    foreach ($products as $product_id => $product) {
                        foreach ($products_to_cart as $product_to_cart) {
                            
                            if(isset($product_quantity[$product_id]['new_price']) && $product_quantity[$product_id]['new_price']!=''){
                                $product_to_cart['price'] = (float)$product_quantity[$product_id]['new_price'];
                            }
                            
                            if($product_id==$product_to_cart['product_id'] && $product_to_cart['quantity']>=$product_quantity[$product_id]['quantity']){
                                unset($products[$product_id]);
                                $total_complect += $product_quantity[$product_id]['quantity']*$product_to_cart['price'];
                                $quantity = (int)$product_quantity[$product_id]['quantity'];
                                $id_complect .= '_'.$product_id.'_'.$quantity;
                            }
                            
                        }
                        
                    }
                    //если $products остался, то комплект не соответствует корзинке по количеству и / по набору
                    if( (!isset($products) || !$products) && $id_complect){
                        $complect['total_complect'] = $total_complect;
                        $discountssales_complect[$id_complect][] = $complect;
                    }
                }
            }
        }
        if(!$discountssales_complect){
            return;
        }
        
        foreach ($discountssales_complect as $key => $value) {
            usort($discountssales_complect[$key], array('ModelTotalDiscountSales','cmp_obj'));
        }
        
        foreach ($discountssales_complect as $id_complect => $discountsales) {
            $discountssales_sort_complect = array();
            $discountssales_sort_complect[] = 0;
            $this->max_interval_complect[$id_complect]['name'] = '';
            $this->max_interval_complect[$id_complect]['total_complect'] = 0;
            foreach ($discountsales as $key => $value) {
                $discountssales_sort_complect[] = $value['sales_limitation'];
                $this->max_interval_complect[$id_complect]['name'] = $value['name_complect'];
                $total = $value['total_complect'];
            }
            asort($discountssales_sort_complect);
            $this->max_interval_complect[$id_complect]['max_interval'] = 0;
            for ($j=0;$j<count($discountssales_sort_complect);$j++) {
                if($total >= $discountssales_sort_complect[$j] && isset($discountssales_sort_complect[$j+1]) && $total < $discountssales_sort_complect[$j+1]){
                    $this->max_interval_complect[$id_complect]['max_interval'] = $discountssales_sort_complect[$j+1];
                    $this->max_interval_complect[$id_complect]['total_complect'] = $total;
                }
            }
        }
        
        
        foreach ($discountssales_complect as $key_complect => $discountsales) {
            $discountssales_sort_complect = array();
            $discountssales_sort_complect[] = 0;
            $this->max_interval_complect[$key_complect]['name'] = '';
            foreach ($discountsales as $key => $discountsales) {
                $discountssales_sort_complect[] = $discountsales['sales_limitation'];
                $this->max_interval_complect[$key_complect]['name'] = trim($discountsales['name_complect']);
            }
            asort($discountssales_sort_complect);
            $this->max_interval_complect[$key_complect]['max_interval'] = 0;
            $this->max_interval_complect[$key_complect]['total_complect'] = 0;
            $total = $discountsales['total_complect'];
            for ($j=0;$j<count($discountssales_sort_complect);$j++) {
                if($total >= $discountssales_sort_complect[$j] && isset($discountssales_sort_complect[$j+1]) && $total < $discountssales_sort_complect[$j+1]){
                    $this->max_interval_complect[$key_complect]['max_interval'] = $discountssales_sort_complect[$j+1];
                    $this->max_interval_complect[$key_complect]['total_complect'] = $total;
                    $this->max_interval_complect[$key_complect]['id_complect'] = $key_complect;
                }
            }
        }
        return;
    }
    
    public function getProducts($discountssales_complect) {
        $product_data = array();
        
        $set = array();
        if($discountssales_complect){
            foreach ($discountssales_complect as $complect) {
                if($complect['products']){
                    foreach ($complect['products'] as $product_id => $tmp) {
                        $set[] = " p.product_id='"  .   $product_id ."' ";
                    }
                }
            }
        }
        if($set){
            $set = 'AND ('.implode(' OR ', $set).') ';
            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_store p2s LEFT JOIN " . DB_PREFIX . "product p ON (p2s.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' ".$set." AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.date_available <= NOW() AND p.status = '1'");
            if($query->rows){
                foreach ($query->rows as $product_row) {
                    $product_data[$product_row['product_id']] = $this->getProduct($product_row['product_id']);
                    $product_data[$product_row['product_id']]['quantity'] = 100000;
                }
            }
        }
        return $product_data;
    }
    
    public function showTable($table,$prefix) {
        $query = $this->db->query('SHOW TABLES from `'.DB_DATABASE.'` like "'.$prefix.$table.'" ');
        if($query->num_rows){
            return TRUE;
        }else{
            return FALSE;
        }
    }
    
    public function getProductsIdsByManufacturerId($manufacturer_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");
        $result = array();
        if(isset($query->rows)){
            foreach ($query->rows as $value) {
                $result[ $value['product_id'] ] = $value['product_id'];
            }
        }
        return $result;
    }
    
    public function getProductsIdsByCategoryId($category_id) {
            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p2c.category_id = '" . (int)$category_id . "' ORDER BY pd.name ASC");
            $result = array();
            if(isset($query->rows)){
                foreach ($query->rows as $value) {
                    $result[ $value['product_id'] ] = $value['product_id'];
                }
            }
            return $result;
    }
}
?>