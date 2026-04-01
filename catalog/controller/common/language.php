<?php
// Копирайты и лицензионные данные
// *	@copyright	OPENCART.PRO 2011 - 2017.
// *	@forum	http://forum.opencart.pro
// *	@source		See SOURCE.txt for source and other copyright.
// *	@license	GNU General Public License version 3; see LICENSE.txt

// Определение класса ControllerCommonLanguage
class ControllerCommonLanguage extends Controller {
	
    // Метод для отображения доступных языков
	public function index() {
		// Загрузка языкового файла
		$this->load->language('common/language');

		// Получение языковой строки
		$data['text_language'] = $this->language->get('text_language');

		// Установка URL для отправки данных формы
		$data['action'] = $this->url->link('common/language/language', '', $this->request->server['HTTPS']);

		// Получение текущего языка из сессии
		$data['code'] = $this->session->data['language'];

		// Загрузка модели для работы с языками
		$this->load->model('localisation/language');

		$data['languages'] = array();

		// Получение всех доступных языков
		$results = $this->model_localisation_language->getLanguages();

		// Добавление каждого активного языка в массив
		foreach ($results as $result) {
			if ($result['status']) {
				$data['languages'][] = array(
					'name' => $result['name'],
					'code' => $result['code'],
                    'url' => '',
                    'link' => ''
				);
			}
		}

		// Если маршрут не установлен, перенаправить на главную страницу
		if (!isset($this->request->get['route'])) {	
			$data['redirect'] = $this->url->link('common/home');
		} else {
			// В противном случае, создать URL для перенаправления
			$url_data = $this->request->get;
			
			unset($url_data['_route_']);
			
			$route = $url_data['route'];

			unset($url_data['route']);

			$url = '';

			if ($url_data) {
				$url = '&' . urldecode(http_build_query($url_data, '', '&'));
			}

			$data['redirect'] = $this->url->link($route, $url, $this->request->server['HTTPS']);
		}

		// Возвращение видовой части с языковыми параметрами
		return $this->load->view('common/language', $data);
	}

    private function addLanguageToUrl($language_code) {
        $url = $this->request->server['REQUEST_URI'];

        // Replace the current language code with the new language code
        $pattern = '/\/language\/[^\/]+/';
        $replacement = '/language/' . $language_code;
        $new_url = preg_replace($pattern, $replacement, $url, 1);

        return $this->url->link($new_url);
    }

	// Метод для смены языка
	public function language() {
		// Если код языка отправлен, установить его в сессии
		if (isset($this->request->post['code'])) {
			$this->session->data['language'] = $this->request->post['code'];
		}

		// Если URL перенаправления отправлен, перенаправить на него
		if (isset($this->request->post['redirect'])) {
			$this->response->redirect($this->request->post['redirect']);
		} else {
			// В противном случае, перенаправить на главную страницу
			$this->response->redirect($this->url->link('common/home'));
		}
	}
}
