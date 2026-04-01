<?php
define('CP_CBRF', 'http://www.cbr.ru/scripts/XML_daily.asp');
define('CP_KZT', 'http://www.nationalbank.kz/rss/rates_all.xml');
define('CP_PRIVAT_BANK', 'https://api.privatbank.ua/p24api/pubinfo?exchange&coursid=5');
define('CP_BANK_UA', 'http://bank-ua.com/export/currrate.xml');
define('CP_NBRB', 'http://www.nbrb.by/Services/XmlExRates.aspx');
 
class ControllerWgiCurrencyPlus extends Controller {

    public function index() {
        $this->load->model('wgi/currency_plus');

        if (isset($this->request->get['type']) and ($this->request->get['type'] == 'all'
                or $this->request->get['type'] == 'product' or $this->request->get['type'] == 'currency')) {
            
            $type = $this->request->get['type'];
        }
        else {
            $type = 'currency';
        }
		
		
		if (isset($this->request->get['data'])) {
            $data = $this->request->get['data'];
        }
        else {
            $data = array();
        }


        $this->model_wgi_currency_plus->updateCurrencies(true, $type, $data);
    }

}
?>