<?php
class ControllerToolKdprice extends Controller
{
    public function index()
    {

        //ini_set("max_execution_time", "6000");

        ini_set('error_reporting', E_ALL);
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);

        set_time_limit(0);

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer_group cg LEFT JOIN " . DB_PREFIX . "customer_group_description cgd ON (cg.customer_group_id = cgd.customer_group_id) WHERE cgd.language_id = '" . (int) $this->config->get('config_language_id') . "' AND cg.customer_group_id != '1' ORDER BY cg.sort_order ASC, cgd.name ASC");

        $customer_groups = $query->rows;

        $currencys = array("EUR", "PLN");

        foreach ($customer_groups as $customer_group) {

            $customer_query = $this->db->query("SELECT customer_id FROM " . DB_PREFIX . "customer WHERE customer_group_id = '" . (int) $customer_group['customer_group_id'] . "' LIMIT 1");

            $customer_id = $customer_query->row['customer_id'];

            $this->session->data['customer_id'] = $customer_id;

            foreach ($currencys as $currency) {

                $current_name = $customer_group['customer_group_id'] . '_' . $currency . '.xlsx';
                $file_name    = DIR_PRICE . $current_name;

                if (file_exists($file_name)) {

                   // if (date("m.d.y", filemtime($file_name)) != date("m.d.y")) {
                        $this->price($customer_group['customer_group_id'], $currency); // перезаписываем существующий
                        echo "Ok!";
                      //  exit();
                   // }

                } else {
                    echo "Создаём новый: " . $file_name . "<br>";
                    $this->price($customer_group['customer_group_id'], $currency); // пишем новый
                    echo "Ok!";
                   // exit();
                }

            }

        }

        echo "Ok!";

    }

    public function price($customer_group_id, $currency)
    {

        set_time_limit(0);

        $this->config->set('price_customer_group_id',  $customer_group_id);

        // $this->session->data['kd_price_customer_group_id'] =  $customer_group_id;

        $this->cache->set('kd_price_customer_group_id', $customer_group_id);

        // ini_set('error_reporting', E_ALL);
        // ini_set('display_errors', 1);
        // ini_set('display_startup_errors', 1);

        // Loading Excel Library
        require_once DIR_SYSTEM . 'library/excel/PHPExcel.php';
        require_once DIR_SYSTEM . 'library/excel/PHPExcel/IOFactory.php';

        $this->load->model('catalog/product');

        $products = array();

        $filter_data = array(
            'sort'          => 'pd.name',
            'order'         => 'ASC',
            'filter_status' => 1,
            'start'         => 0,
            'limit'         => 10000,
        );

        ini_set('max_execution_time', 900);

        $cache_hash = md5(serialize($filter_data));

        $results = $this->cache->get('price_data_products_'. $customer_group_id . '_' . $cache_hash);

        if (!$results) {

            $results = $this->model_catalog_product->getProducts2($filter_data);

            $this->cache->set('price_data_products_' . $customer_group_id . '_' . $cache_hash, $results);

        }

        // echo '<pre>';
        // print_r($results);
        // echo '</pre>';


        // exit();


        $products = $this->cache->get('price_products_' . $currency . '_' . $customer_group_id . '_'. $cache_hash);

        $products = false; // debug

        if (!$products) {

        $this->load->model('catalog/group_price2');  

        foreach ($results as $result) {

            //if ($result['base_currency_code'] == $currency && $result['base_price'] > 0) $result['price'] = $result['base_price'];

            $price = $this->model_catalog_group_price2->updatePrice($result, $result['special'] ? $result['special'] : $result['price']);

            $promej_price = $result['special'];


            $main_category_name = $this->model_catalog_product->getMainCategory($result['product_id']);

            $price = $this->currency->format($price, $currency, '', false);


            $base_price = $this->currency->format($this->tax->calculate($result['base_price'], $result['tax_class_id'], $this->config->get('config_tax')), $currency, '', false);

            //$price = $this->currency->format($result['price'], $currency, '', false);

            $products[] = array(
                'manufacturer'       => $result['manufacturer'],
                'product_id'         => $result['product_id'],
                'name'               => html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8'),
                'category'           => htmlspecialchars_decode($main_category_name),
                'price'              => $price,
                'base_price'         => $base_price,
                'base_currency_code' => $result['base_currency_code'],
                'quantity'           => $result['quantity'],
                'promej_price'       => $promej_price

            );
        }

        $brands = array();
        foreach ($products as $product) {
            if ($product['manufacturer']) {
                $brands[$product['manufacturer']]['products'][] = $product;
            } else {
                $brands['None']['products'][] = $product;
            }
        }
        ksort($brands);
        $products = $brands;

         $this->cache->set('price_products_' . $currency . '_' . $customer_group_id . '_'. $cache_hash, $products);

        }


        // echo '<br>'.$this->cache->get('kd_price_customer_group_id');
        // echo '<br>'. $currency;
        // echo '<pre>';
        // print_r($products);
        // echo '</pre>';

        // exit();

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
            'font' => array(
                'color' => array('rgb' => 'FFFFFF'),
                'size'  => 11,
                'name'  => 'Verdana',
            ));

        $manufacturerStyleArray = array(
            'borders' => array(
                'outline' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('rgb' => '000000'),
                ),
            ),
        );

        $sheet = $this->objPHPExcel->getActiveSheet(); // активная страница

        // Product header
        $sheet->getStyle('A1:B1')->applyFromArray($styleThinBlackBorderOutline);
        $sheet->getStyle('A1:B1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $sheet->getStyle('A1:B1')->getFill()->getStartColor()->setARGB('303232');

        $sheet->getStyle('A1')->applyFromArray($styleThinBlackBorderOutline);
        $sheet->setCellValue('A1', '№');
        $sheet->getStyle('A1:G1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1')->applyFromArray($headerStyleArray);

        $sheet->setCellValue('B1', 'Product Name');
        $sheet->getStyle('B1')->applyFromArray($headerStyleArray);

        $sheet->getStyle('C1')->applyFromArray($styleThinBlackBorderOutline);
        $sheet->getStyle('C1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $sheet->getStyle('C1')->getFill()->getStartColor()->setARGB('303232');
        $sheet->getStyle('C1:G1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->setCellValue('C1', 'Category');
        $sheet->getStyle('C1')->applyFromArray($headerStyleArray);

        $sheet->getStyle('D1')->applyFromArray($styleThinBlackBorderOutline);
        $sheet->getStyle('D1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $sheet->getStyle('D1')->getFill()->getStartColor()->setARGB('303232');
        $sheet->getStyle('D1:G1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->setCellValue('D1', 'Price (' . $currency . ')');
        $sheet->getStyle('D1')->applyFromArray($headerStyleArray);

        $sheet->getStyle('E1')->applyFromArray($styleThinBlackBorderOutline);
        $sheet->getStyle('E1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $sheet->getStyle('E1')->getFill()->getStartColor()->setARGB('303232');
        $sheet->setCellValue('E1', 'Available stock');
        $sheet->getStyle('E1')->applyFromArray($headerStyleArray);

        $sheet->getStyle('F1')->applyFromArray($styleThinBlackBorderOutline);
        $sheet->getStyle('F1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $sheet->getStyle('F1')->getFill()->getStartColor()->setARGB('303232');
        $sheet->setCellValue('F1', 'Order Quantity');
        $sheet->getStyle('F1')->applyFromArray($headerStyleArray);

        $sheet->getStyle('G1')->applyFromArray($styleThinBlackBorderOutline);
        $sheet->getStyle('G1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $sheet->getStyle('G1')->getFill()->getStartColor()->setARGB('303232');
        $sheet->setCellValue('G1', 'Amount');
        $sheet->getStyle('G1')->applyFromArray($headerStyleArray);

        $sheet->getStyle('A1:G1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        //$sheet->freezePane('D2');

        $sheet->getRowDimension('1')->setRowHeight(25);

        $counter   = 2;
        $number_pp = 1;

        foreach ($products as $brand => $items) {

            $sheet->getStyle('A' . $counter)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->mergeCells('A' . $counter . ':G' . $counter);
            $sheet->getStyle('A' . $counter)->getFont()->setBold(true);
            $sheet->getStyle('A' . $counter)->getFont()->setSize(15);

            $sheet->getStyle('A' . $counter . ':G' . $counter)->applyFromArray($styleThinBlackBorderOutline);
            $sheet->getStyle('A' . $counter . ':G' . $counter)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
            $sheet->getStyle('A' . $counter)->getFill()->getStartColor()->setARGB('CCCCCC');
            $sheet->setCellValue('A' . $counter, $brand);

            $counter++;

            foreach ($items['products'] as $product) {

                $sheet->getStyle('A' . $counter. ':F'.$counter)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                $sheet->setCellValue('A' . $counter, $number_pp);

                $sheet->setCellValue('B' . $counter, $product['name']);

                $sheet->setCellValue('C' . $counter, $product['category']);

                $sheet->setCellValue('D' . $counter, $product['price']);

                $sheet->setCellValue('E' . $counter, $product['quantity']);

                $formula_total = "=F" . $counter . "*D" . $counter;

                $sheet->getStyle('G' . $counter)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $sheet->setCellValue('G' . $counter, $formula_total);

                $counter++;
                $number_pp++;

            }

            // optimisation for style
             $sheet->getStyle('A2:A' . $counter)->applyFromArray($styleThinBlackBorderOutline);
             $sheet->getStyle('B2:B' . $counter)->applyFromArray($styleThinBlackBorderOutline);
             $sheet->getStyle('C2:C' . $counter)->applyFromArray($styleThinBlackBorderOutline);
             $sheet->getStyle('D2:D' . $counter)->applyFromArray($styleThinBlackBorderOutline);
             $sheet->getStyle('E2:E' . $counter)->applyFromArray($styleThinBlackBorderOutline);
             $sheet->getStyle('F2:F' . $counter)->applyFromArray($styleThinBlackBorderOutline);
             $sheet->getStyle('G2:G' . $counter)->applyFromArray($styleThinBlackBorderOutline);



        }

        $sheet->getStyle('F' . $counter)->applyFromArray($styleThinBlackBorderOutline);
        //$sheet->getStyle('F' . $counter)->getFill()->getStartColor()->setARGB('CCCCCC');
        $sheet->setCellValue('F' . $counter, 'Total');
        $sheet->getStyle('F' . $counter)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('F' . $counter)->getFont()->setBold(true);

        $formula_sum = '=SUM(G2:G' . ($counter - 1) . ')';

        $sheet->getStyle('G' . $counter)->applyFromArray($styleThinBlackBorderOutline);
        //$sheet->getStyle('G' . $counter)->getFill()->getStartColor()->setARGB('CCCCCC');
        $sheet->getStyle('G' . $counter)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('G' . $counter)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
        $sheet->setCellValue('G' . $counter, $formula_sum);
        $sheet->getStyle('G' . $counter)->getFont()->setBold(true);

        $sheet->setTitle('Price ' . date("d-m-Y"));

        //Set paper size to A6
        $sheet->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

        // Set the column in size
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(60);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(15); // количество
        $sheet->getColumnDimension('G')->setWidth(15); // total

        $sheet->getStyle('A:H')->getFont()->setSize(11);

        // сохраняем в файл
        $current_name = $customer_group_id . '_' . $currency . '.xlsx';
        $file_name    = DIR_PRICE . $current_name;
        $objWriter    = PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel2007');
        $objWriter->save($file_name);

    }

}
