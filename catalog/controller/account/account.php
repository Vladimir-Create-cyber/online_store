<?php
// *	@copyright	OPENCART.PRO 2011 - 2017.
// *	@forum	http://forum.opencart.pro
// *	@source		See SOURCE.txt for source and other copyright.
// *	@license	GNU General Public License version 3; see LICENSE.txt

class ControllerAccountAccount extends Controller {
	public function index() {
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/account', '', true);

			$this->response->redirect($this->url->link('account/login', '', true));
		}

		$this->load->language('account/account');

		$this->document->setTitle($this->language->get('heading_title'));
		$this->document->setRobots('noindex,follow');

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_account'),
			'href' => $this->url->link('account/account', '', true)
		);

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		} 

		$data['heading_title'] = $this->language->get('heading_title');
		$this->document->setRobots('noindex,follow');

		$data['text_my_account'] = $this->language->get('text_my_account');
		$data['text_my_orders'] = $this->language->get('text_my_orders');
		$data['text_my_newsletter'] = $this->language->get('text_my_newsletter');
		$data['text_edit'] = $this->language->get('text_edit');
		$data['text_password'] = $this->language->get('text_password');
		$data['text_address'] = $this->language->get('text_address');
		$data['text_credit_card'] = $this->language->get('text_credit_card');
		$data['text_wishlist'] = $this->language->get('text_wishlist');
		$data['text_order'] = $this->language->get('text_order');
		$data['text_download'] = $this->language->get('text_download');
		$data['text_reward'] = $this->language->get('text_reward');
		$data['text_return'] = $this->language->get('text_return');
		$data['text_transaction'] = $this->language->get('text_transaction');
		$data['text_newsletter'] = $this->language->get('text_newsletter');
		$data['text_recurring'] = $this->language->get('text_recurring');

		$data['edit'] = $this->url->link('account/edit', '', true);
		$data['password'] = $this->url->link('account/password', '', true);
		$data['address'] = $this->url->link('account/address', '', true);
		
		$data['credit_cards'] = array();
		
		$files = glob(DIR_APPLICATION . 'controller/extension/credit_card/*.php');
		
		foreach ($files as $file) {
			$code = basename($file, '.php');
			
			if ($this->config->get($code . '_status') && $this->config->get($code . '_card')) {
				$this->load->language('extension/credit_card/' . $code);

				$data['credit_cards'][] = array(
					'name' => $this->language->get('heading_title'),
					'href' => $this->url->link('extension/credit_card/' . $code, '', true)
				);
			}
		}
		
		$data['wishlist'] = $this->url->link('account/wishlist');
		$data['order'] = $this->url->link('account/order', '', true);
		// $data['download'] = $this->url->link('account/download', '', true);
		$data['download'] = $this->url->link('account/account/price', '', true);
		
		if ($this->config->get('reward_status')) {
			$data['reward'] = $this->url->link('account/reward', '', true);
		} else {
			$data['reward'] = '';
		}		
		
		$data['return'] = $this->url->link('account/return', '', true);
		$data['transaction'] = $this->url->link('account/transaction', '', true);
		$data['newsletter'] = $this->url->link('account/newsletter', '', true);
		$data['recurring'] = $this->url->link('account/recurring', '', true);
		
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');
		
		$this->response->setOutput($this->load->view('account/account', $data));
	}

	public function country() {
		$json = array();

		$this->load->model('localisation/country');

		$country_info = $this->model_localisation_country->getCountry($this->request->get['country_id']);

		if ($country_info) {
			$this->load->model('localisation/zone');

			$json = array(
				'country_id'        => $country_info['country_id'],
				'name'              => $country_info['name'],
				'iso_code_2'        => $country_info['iso_code_2'],
				'iso_code_3'        => $country_info['iso_code_3'],
				'address_format'    => $country_info['address_format'],
				'postcode_required' => $country_info['postcode_required'],
				'zone'              => $this->model_localisation_zone->getZonesByCountryId($this->request->get['country_id']),
				'status'            => $country_info['status']
			);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}




	// krumax dev



	private function getDownloadXlsFile($name = null){
		// Redirect output to a client’s web browser (Excel2007)
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="Price_'.$name.'_'.date("d-m-Y").'.xlsx"');
		header('Cache-Control: max-age=0');

		$objWriter = PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel2007');
		$objWriter->save('php://output');
		exit();
	}



	public function price() {


		$customer_group_id = $this->customer->getGroupId();


		$currency = $this->session->data['currency'];


		$this->load->model('account/customer_group');

		$customer_group_info = $this->model_account_customer_group->getCustomerGroup($customer_group_id);

		// print_r($customer_group_info);

		// exit();

		//ini_set("max_execution_time", "60");

		// Loading Excel Library
		require_once DIR_SYSTEM . 'library/excel/PHPExcel.php';
		require_once DIR_SYSTEM . 'library/excel/PHPExcel/IOFactory.php';


		// Loading model
		$this->load->model('catalog/product');

		$products = array();

		$filter_data = array(
			'sort' => 'pd.name',
			'order'   => 'ASC',
			'filter_status' => 1,
			'start'   => 0,
			'limit'	  => 5000
		);


		ini_set('max_execution_time', 900);

		if(!$products) {

		$results = $this->model_catalog_product->getProducts($filter_data);

		foreach ($results as $result) {

		$main_category_name = $this->model_catalog_product->getMainCategory($result['product_id']);

		$price = $this->currency->format($result['price'], $currency, '', false);

		         
		$products[] = array(
					'manufacturer'=> $result['manufacturer'],
					'product_id'  => $result['product_id'],
					'name'        => $result['name'],
					'category'	  => $main_category_name,
					'price'       => $price,
					'base_price'  => $result['base_price'],
					'base_currency_code' => $result['base_currency_code'],
					'quantity'    => $result['quantity']
					// 'special'     => $special,

				);


		}


		$brands = array();
			foreach( $products as $product ) {
				if($product['manufacturer']) {
					$brands[$product['manufacturer']]['products'][] = $product;
				} else {
					$brands['None']['products'][] = $product;
				}
			}
			ksort($brands);
			$products = $brands;


			$this->cache->set('allproducts_' . $customer_group_id . '_' . $currency, $products);

		}


		// 	echo "<pre>";
		// 	print_r($products);
		// 	echo "</pre>";

		// exit();



		// $this->objPHPExcel = (object)$this->cache->get('price_' . $customer_group_id . '_' . $currency);

		// if (!$this->objPHPExcel) {

		// Creating a new instance of Excel with properties
		$this->objPHPExcel = new PHPExcel();
		$this->objPHPExcel->getProperties()->setCreator("krumax")
										   ->setLastModifiedBy("krumax")
										   ->setTitle("Office 2007 XLSX")
										   ->setSubject("Office 2007 XLSX")
										   ->setDescription("Document for Office 2007 XLSX, generated by krumax")
										   ->setKeywords("office 2007 excel")
										   ->setCategory("krumax.info");


		// Create a first sheet, representing order data
		$this->objPHPExcel->setActiveSheetIndex(0);

		// Set thin black border outline around column
		$styleThinBlackBorderOutline = array(
			'borders' => array(
				'outline' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					//'color' => array('rgb' => 'cedcdd'),
					'color' => array('rgb' => '000000'),
				),
			),
		);

		$headerStyleArray = array(
		    	'font'  => array(
		        'color' => array('rgb' => 'FFFFFF'),
		        'size'  => 11,
		        'name'  => 'Verdana'
		    ));


		$manufacturerStyleArray = array(
			'borders' => array(
				'outline' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					'color' => array('rgb' => '000000'),
				),
			),
		);


		// Product header
		$this->objPHPExcel->getActiveSheet()->getStyle('A1:B1')->applyFromArray($styleThinBlackBorderOutline);
		$this->objPHPExcel->getActiveSheet()->getStyle('A1:B1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
		$this->objPHPExcel->getActiveSheet()->getStyle('A1:B1')->getFill()->getStartColor()->setARGB('303232');
				
		$this->objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleThinBlackBorderOutline);
		$this->objPHPExcel->getActiveSheet()->setCellValue('A1', '№');
		$this->objPHPExcel->getActiveSheet()->getStyle('A1:G1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$this->objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($headerStyleArray);

		$this->objPHPExcel->getActiveSheet()->setCellValue('B1', 'Product Name');
		$this->objPHPExcel->getActiveSheet()->getStyle('B1')->applyFromArray($headerStyleArray);

		$this->objPHPExcel->getActiveSheet()->getStyle('C1')->applyFromArray($styleThinBlackBorderOutline);
		$this->objPHPExcel->getActiveSheet()->getStyle('C1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
		$this->objPHPExcel->getActiveSheet()->getStyle('C1')->getFill()->getStartColor()->setARGB('303232');
		$this->objPHPExcel->getActiveSheet()->getStyle('C1:G1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$this->objPHPExcel->getActiveSheet()->setCellValue('C1', 'Category');
		$this->objPHPExcel->getActiveSheet()->getStyle('C1')->applyFromArray($headerStyleArray);
		
		$this->objPHPExcel->getActiveSheet()->getStyle('D1')->applyFromArray($styleThinBlackBorderOutline);
		$this->objPHPExcel->getActiveSheet()->getStyle('D1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
		$this->objPHPExcel->getActiveSheet()->getStyle('D1')->getFill()->getStartColor()->setARGB('303232');
		$this->objPHPExcel->getActiveSheet()->getStyle('D1:G1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$this->objPHPExcel->getActiveSheet()->setCellValue('D1', 'Price (' . $currency . ')');
		$this->objPHPExcel->getActiveSheet()->getStyle('D1')->applyFromArray($headerStyleArray);

		$this->objPHPExcel->getActiveSheet()->getStyle('E1')->applyFromArray($styleThinBlackBorderOutline);
		$this->objPHPExcel->getActiveSheet()->getStyle('E1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
		$this->objPHPExcel->getActiveSheet()->getStyle('E1')->getFill()->getStartColor()->setARGB('303232');
		$this->objPHPExcel->getActiveSheet()->setCellValue('E1', 'Available stock');
		$this->objPHPExcel->getActiveSheet()->getStyle('E1')->applyFromArray($headerStyleArray);

		$this->objPHPExcel->getActiveSheet()->getStyle('F1')->applyFromArray($styleThinBlackBorderOutline);
		$this->objPHPExcel->getActiveSheet()->getStyle('F1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
		$this->objPHPExcel->getActiveSheet()->getStyle('F1')->getFill()->getStartColor()->setARGB('303232');
		$this->objPHPExcel->getActiveSheet()->setCellValue('F1', 'Order Quantity');
		$this->objPHPExcel->getActiveSheet()->getStyle('F1')->applyFromArray($headerStyleArray);

		$this->objPHPExcel->getActiveSheet()->getStyle('G1')->applyFromArray($styleThinBlackBorderOutline);
		$this->objPHPExcel->getActiveSheet()->getStyle('G1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
		$this->objPHPExcel->getActiveSheet()->getStyle('G1')->getFill()->getStartColor()->setARGB('303232');
		$this->objPHPExcel->getActiveSheet()->setCellValue('G1', 'Amount');
		$this->objPHPExcel->getActiveSheet()->getStyle('G1')->applyFromArray($headerStyleArray);

		$this->objPHPExcel->getActiveSheet()->getStyle('A1:G1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

		//$this->objPHPExcel->getActiveSheet()->freezePane('D2');

		$this->objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(25);

		// Writing product details
		$counter = 2;
		$number_pp = 1;

		foreach( $products as $brand=>$items ) { 

			$this->objPHPExcel->getActiveSheet()->getStyle('A'.$counter)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$this->objPHPExcel->getActiveSheet()->mergeCells('A'.$counter.':G'.$counter);
		   	$this->objPHPExcel->getActiveSheet()->getStyle('A'.$counter)->getFont()->setBold(true);
		   	$this->objPHPExcel->getActiveSheet()->getStyle('A'.$counter)->getFont()->setSize(15);

		   	$this->objPHPExcel->getActiveSheet()->getStyle('A'.$counter . ':G'.$counter)->applyFromArray($styleThinBlackBorderOutline);
			$this->objPHPExcel->getActiveSheet()->getStyle('A'.$counter . ':G'.$counter)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
			$this->objPHPExcel->getActiveSheet()->getStyle('A'.$counter)->getFill()->getStartColor()->setARGB('CCCCCC');
		   	$this->objPHPExcel->getActiveSheet()->setCellValue('A'.$counter, $brand);

		   	$counter++;

				foreach ($items['products'] as $product) {

					$this->objPHPExcel->getActiveSheet()->getStyle('A2:A'. $counter)->applyFromArray($styleThinBlackBorderOutline);			
				 	$this->objPHPExcel->getActiveSheet()->setCellValue('A' . $counter, $number_pp);
				 	$this->objPHPExcel->getActiveSheet()->getStyle('A'.$counter)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

					$this->objPHPExcel->getActiveSheet()->getStyle('B2:B'. $counter)->applyFromArray($styleThinBlackBorderOutline);			
					$this->objPHPExcel->getActiveSheet()->setCellValue('B' . $counter, html_entity_decode($product['name'], ENT_QUOTES, 'UTF-8'));

					$this->objPHPExcel->getActiveSheet()->getStyle('C2:C'. $counter)->applyFromArray($styleThinBlackBorderOutline);
					$this->objPHPExcel->getActiveSheet()->getStyle('C'.$counter)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
					$this->objPHPExcel->getActiveSheet()->setCellValue('C' . $counter, htmlspecialchars_decode($product['category']));

					$this->objPHPExcel->getActiveSheet()->getStyle('D2:D'. $counter)->applyFromArray($styleThinBlackBorderOutline);
					$this->objPHPExcel->getActiveSheet()->getStyle('D'.$counter)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
					$this->objPHPExcel->getActiveSheet()->setCellValue('D' . $counter, $product['price']);

					$this->objPHPExcel->getActiveSheet()->getStyle('E2:E'. $counter)->applyFromArray($styleThinBlackBorderOutline);
					$this->objPHPExcel->getActiveSheet()->getStyle('E'.$counter)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
					$this->objPHPExcel->getActiveSheet()->setCellValue('E' . $counter, $product['quantity']);

					$this->objPHPExcel->getActiveSheet()->getStyle('F2:F'. $counter)->applyFromArray($styleThinBlackBorderOutline);
					$this->objPHPExcel->getActiveSheet()->getStyle('F'.$counter)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
					$this->objPHPExcel->getActiveSheet()->setCellValue('F' . $counter, '');

					$formula_total = "=F".$counter."*D".$counter;

					$this->objPHPExcel->getActiveSheet()->getStyle('G2:G'. $counter)->applyFromArray($styleThinBlackBorderOutline);
					$this->objPHPExcel->getActiveSheet()->getStyle('G'.$counter)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
					$this->objPHPExcel->getActiveSheet()->setCellValue('G' . $counter, $formula_total);


				 $counter++;
				 $number_pp ++;

				}

				//$counter++;

		}

			$this->objPHPExcel->getActiveSheet()->getStyle('F'.$counter)->applyFromArray($styleThinBlackBorderOutline);
			$this->objPHPExcel->getActiveSheet()->setCellValue('F'.$counter, 'Total');
			$this->objPHPExcel->getActiveSheet()->getStyle('F'.$counter)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
			$this->objPHPExcel->getActiveSheet()->getStyle('F'.$counter)->getFont()->setBold(true);

		 	$formula_sum = '=SUM(G2:G'.($counter-1).')';
			
			$this->objPHPExcel->getActiveSheet()->getStyle('G'.$counter)->applyFromArray($styleThinBlackBorderOutline);
			$this->objPHPExcel->getActiveSheet()->getStyle('G'.$counter)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$this->objPHPExcel->getActiveSheet()->getStyle('G'.$counter)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
			$this->objPHPExcel->getActiveSheet()->setCellValue('G'.$counter, $formula_sum);
			$this->objPHPExcel->getActiveSheet()->getStyle('G'.$counter)->getFont()->setBold(true);
			
			
		// Write this under the product list on the right
		$this->objPHPExcel->getActiveSheet()->setTitle('Price ' . date("d-m-Y"));


		//Set paper size to A6
        $this->objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
		
		// Set the column in size				
		$this->objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(5);
		$this->objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(60);
		$this->objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);	
		$this->objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);	
		$this->objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15); 					
		$this->objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15); // количество
		$this->objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15); // total
	
		$this->objPHPExcel->getActiveSheet()->getStyle('A:H')->getFont()->setSize(11);

		// $array = json_decode(json_encode($this->objPHPExcel),TRUE); 

		// $this->cache->set('price_' . $customer_group_id . '_' . $currency, $array);

		//}

		$this->getDownloadXlsFile($customer_group_info['name'] . '_'.$currency);


	}

	// krumax dev END


}