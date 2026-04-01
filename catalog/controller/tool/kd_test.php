<?php
/**
 * krumax dev
 */
class ControllerToolKdtest extends Controller
{
	

	public function index() {

		$this->config->set('price_customer_group_id', 3);

		$this->load->model('catalog/product');

		$product_info = $this->model_catalog_product->getProduct(3285);

		$this->load->model('catalog/group_price2');   

		echo '<pre>';
		print_r($product_info);
		echo '</pre>';

		$new_price = $this->model_catalog_group_price2->updatePrice($product_info, $product_info['discount'] ? $product_info['discount'] : $product_info['price']);

		echo 'customer_group_id = '. $this->config->get('price_customer_group_id');

		echo '<br>';

		echo $new_price;

	}
}