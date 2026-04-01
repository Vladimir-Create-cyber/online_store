<?php

class ModelExtensionTotalEzDiscountAmount extends Model
{

    public function getTotal($total) {
        if ($this->config->get('ez_discount_amount_status')) {

            $this->load->language('extension/total/ez_discount_amount');

            $this->load->model('catalog/product');

            $black_list = $this->config->get('ez_discount_amount_black_list');
            $white_list = $this->config->get('ez_discount_amount_white_list');

            $products = $this->cart->getProducts();
            $total_sum = 0;

            foreach ($products as $product) {

                $unit_price = $this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax'));
                $product_price = $unit_price * $product['quantity'];

                $manufacturer_id = $this->getManufacturerByProductId($product['product_id']);
                $product_attributes = $this->model_catalog_product->getProductAttributes($product['product_id']);


                // Checking white list

                if (isset($white_list['products']) && $white_list['products'] && is_array($white_list['products']) && in_array($product['product_id'], $white_list['products'])) {
                    $total_sum += $product_price;
                    continue;
                }

                if (isset($white_list['manufacturers']) && $white_list['manufacturers'] && is_array($white_list['manufacturers']) && in_array($manufacturer_id, $white_list['manufacturers'])) {
                    $total_sum += $product_price;
                    continue;
                }

                if (isset($white_list['categories']) && $white_list['categories'] && is_array($white_list['categories'])) {
                    $product_categories = $this->model_catalog_product->getCategories($product['product_id']);

                    $isAllowed = false;

                    foreach ($product_categories as $category) {
                        if (in_array($category['category_id'], $white_list['categories'])) {
                            $isBanned = true;
                            break;
                        }
                    }

                    if ($isAllowed) {
                        $total_sum += $product_price;
                        continue;
                    }
                }

                if (isset($black_list['attributes']) && $black_list['attributes'] && is_array($black_list['attributes'])) {
                    $isAllowed = false;

                    foreach ($product_attributes as $attribute_group) {
                        foreach ($attribute_group['attribute'] as $attribute) {
                            foreach ($black_list['attributes'] as $excluded_attribute) {
                                if ($excluded_attribute['attribute_id'] == $attribute['attribute_id']) {
                                    foreach ($excluded_attribute['discount_attribute_description'] as $excluded_attribute_text) {
                                        if ($excluded_attribute_text['text'] == $attribute['text']) {
                                            $isAllowed = true;
                                            break;
                                        }
                                    }
                                }

                                if ($isAllowed) {
                                    break;
                                }
                            }

                            if ($isAllowed) {
                                break;
                            }
                        }

                        if ($isAllowed) {
                            break;
                        }
                    }

                    if ($isAllowed) {
                        $total_sum += $product_price;
                        continue;
                    }
                }


                // Checking black list

                if (isset($black_list['products']) && $black_list['products'] && is_array($black_list['products']) && in_array($product['product_id'], $black_list['products'])) {
                    continue;
                }

                if (isset($black_list['manufacturers']) && $black_list['manufacturers'] && is_array($black_list['manufacturers']) && in_array($manufacturer_id, $black_list['manufacturers'])) {
                    continue;
                }

                if (isset($black_list['categories']) && $black_list['categories'] && is_array($black_list['categories'])) {
                    $product_categories = $this->model_catalog_product->getCategories($product['product_id']);

                    $isBanned = false;

                    foreach ($product_categories as $category) {
                        if (in_array($category['category_id'], $black_list['categories'])) {
                            $isBanned = true;
                            break;
                        }
                    }

                    if ($isBanned) {
                        continue;
                    }
                }

                if (isset($black_list['attributes']) && $black_list['attributes'] && is_array($black_list['attributes'])) {
                    $isBanned = false;

                    foreach ($product_attributes as $attribute_group) {
                        foreach ($attribute_group['attribute'] as $attribute) {
                            foreach ($black_list['attributes'] as $excluded_attribute) {
                                if ($excluded_attribute['attribute_id'] == $attribute['attribute_id']) {
                                    foreach ($excluded_attribute['discount_attribute_description'] as $excluded_attribute_text) {
                                        if ($excluded_attribute_text['text'] == $attribute['text']) {
                                            $isBanned = true;
                                            break;
                                        }
                                    }
                                }

                                if ($isBanned) {
                                    break;
                                }
                            }

                            if ($isBanned) {
                                break;
                            }
                        }

                        if ($isBanned) {
                            break;
                        }
                    }

                    if ($isBanned) {
                        continue;
                    }
                }

                $total_sum += $product_price;
            }

            if ($total_sum > $total['total']) {
                $total_sum = $total['total'];
            }

            $discount_programs = $this->config->get('ez_discount_amount_programs');

            $customer_groups = array('all');

            if ($this->customer->isLogged()) {
                $customer_groups[] = 'register';
            } else {
                $customer_groups[] = 'guest';
            }

            if ($this->customer->getGroupId() && $this->customer->isLogged()) {
                $customer_groups[] = $this->customer->getGroupId();
            }

            $matched_discount = array();

            if ($discount_programs) {
                foreach ($discount_programs as $discount_program) {
                    if (in_array($discount_program['customer_group_id'], $customer_groups) && $total_sum >= $discount_program['min_price'] && $total_sum < $discount_program['max_price']) {

                        if ($discount_program['type'] == 'percent') {
                            $value = ($total_sum * $discount_program['value']) / 100;
                            $discount_value_text = $discount_program['value'] . '%';
                        } else if ($discount_program['type'] == 'fixed_value') {
                            $value = $discount_program['value'];
                            $discount_value_text = $this->currency->format($discount_program['value'] ,$this->session->data['currency']);
                        } else {
                            continue;
                        }

                        $matched_discount[] = array(
                            'min_price' => $discount_program['min_price'],
                            'value' => $value,
                            'discount_value_text' => $discount_value_text
                        );
                    }
                }

                array_multisort(array_column($matched_discount, 'min_price'), SORT_DESC, array_column($matched_discount, 'value'), SORT_DESC, $matched_discount);
            }

            $discount_applied = false;

            if ($this->config->get('ez_discount_amount_text_color')) {
                $color = 'color: ' . $this->config->get('ez_discount_amount_text_color');
            } else {
                $color = '';
            }

            if ($matched_discount) {
                $total['totals'][] = array(
                    'code' => 'ez_discount_amount',
                    'title' => sprintf($this->language->get('text_current_discount'), $color, $matched_discount[0]['discount_value_text']),
                    'value' => -1 * $matched_discount[0]['value'],
                    'sort_order' => $this->config->get('ez_discount_amount_sort_order')
                );

                $total['total'] -= $value;

                $discount_applied = true;
            }

            if (!$discount_applied) {
                $total['totals'][] = array(
                    'code' => 'ez_discount_amount',
                    'title' => sprintf($this->language->get('text_current_discount'), $color, '0%'),
                    'value' => 0,
                    'sort_order' => $this->config->get('ez_discount_amount_sort_order')
                );
            }
        }
    }

    public function getManufacturerByProductId($product_id) {
        $query = $this->db->query("SELECT manufacturer_id FROM " . DB_PREFIX . "product WHERE product_id = '" . (int)$product_id . "'");

        return isset($query->row['manufacturer_id']) ? $query->row['manufacturer_id'] : 0;
    }
}
