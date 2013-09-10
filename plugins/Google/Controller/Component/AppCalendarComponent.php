<?php
/**
 * Application level Component
 *
 * Este arquivo contem todas as funcoes relacionadas ao Calendario do Google.
 *
 * @link          https://developers.google.com/google-apps/calendar/v3/reference/
 * @package       app.Controller.Component
 */
App::uses('Component', 'Controller');

//Incusao da biblioteca fornecida pelo google
require_once PATH_APP . '/plugins/Google/lib/src/contrib/Google_CalendarService.php';


/**
 * Application Component
 *
 * O componente "AppCalendar" contem todas as regras de negocio 
 * necessarias para manipular o calendario da conta google associada ao sistema
 */
class AppCalendarComponent extends Component {
	/**
	* Carrega os componentes que poderao ser usados em quaisquer controller desta framework
	*/
	public $components = array(
		'Session', 
		);

	/**
	* Declaracao dos atributos
	*/
	private $calendar;
	private $calendarService;
	private $event;
	private $createdEvent;

	/**
	* Método startup
	*
	* O método startup é chamado depois do método beforeFilter do controle, 
	* mas antes do controller executar a action corrente.
	*
	* Aqui serao carregados os servicos do webservice do google ja instnciado no component AppGoogle
	*
	* @override Metodo app.Controller.Component.startup
	* @param Controller $controller
	* @return void
	*/
	public function startup($controller){
		/**
		* A sessao 'User.Social.api' é setada no users controller no momento em que o 
		* usuario seleciona a rede social que sera utilizada como login
		*/
		if($this->Session->read('User.Social.api') == GOOGLE_GROUP){
			/**
			* Instância dos objetos
			*/
			$this->calendarService = new Google_CalendarService($controller->AppGoogle->service);
			$this->event = new Google_Event($controller->AppGoogle->service);
		}

		//@override
		parent::startup($controller);
	}

	/**
	* Método loadCalendars
	* Esta funcao é responsavel por listar todas os calendarios do usuario logado
	* e as carregar em session para serem enxergadas em todo escopo do sistema
	*
	* @return void
	*/
	public function loadCalendars($default){
		//Carrega os dados do calendario
		$calList = $this->calendarService->calendarList->listCalendarList();
		$size = 0;	
		//Percorre por todas os calendarios do usuario
		foreach ($calList['items'] as $k => $v) {
			//Carrega somente os calendarios que o usuario tem permissao para alterar
			if($v['accessRole'] != 'reader'){

	            if(strstr($v['summary'], '@')){
	                $map = explode('@', $v['summary']);
	            }else{
	                $map = explode(' ', $v['summary']);
	            }
                $name = isset($map[1])?ucwords("{$map[0]} " . preg_replace('/(\..*)/si', '', $map[1])):ucfirst($map[0]);
                $name = preg_replace('/[^a-zA-Z ]/', '', $name);

                if($size < floor(abs(strlen($name) * 8.4))){
	                $size = (int)floor(abs(strlen($name) * 8.4));
					$this->Session->write('User.Calendar.size', $size);
                }
                $calendars[str_replace('.', GOOGLE_PONTO, $v['id'])] = $name;
				if($v['id'] == $default){
					$this->Session->write('User.Calendar.default', str_replace('.', GOOGLE_PONTO, $v['id']));
				}
			}
		}

		asort($calendars);
		$this->Session->write("User.Calendar.hasCalendars", count($calendars));
		$this->Session->write("User.Calendar.items", $calendars);
	}

	/**
	* Método eventInsert
	*
	* Este método é responsavel pela insercao de eventos do calendario
	*
	* @param array $params
	* @return array $this->createdEvent
	*/
	public function eventInsert($params){
		$createdEvent = false;

		/**
		* Carrega os valores padrao do calendario
		*/
		$defaults = array(
			'summary' => 'Titulo',
			'location' => '',
			'description' => 'Evento criado em ' . date('d/m/Y'),
			'date_ini' => date('Y-m-d'),
			'hr_ini' => date('H:i:s', mktime(date('H'), 0, 0, date('m'), date('d'), date('Y'))),
			'date_end' => date('Y-m-d'),
			'hr_end' => date('H:i:s', mktime((date('H')+1), 0, 0, date('m'), date('d'), date('Y'))),
			);
		$params = array_merge($defaults, $params);

		/**
		* Informacoes do evento
		*/
		$this->event->setSummary($params['summary']);
		$this->event->setLocation($params['location']);
		$this->event->setDescription($params['description']);

		/**
		* Agendamento do evento
		*/
		$start = new Google_EventDateTime();
		$start->setDateTime("{$params['date_ini']}T{$params['hr_ini']}.000-03:00");
		$this->event->setStart($start);
		$end = new Google_EventDateTime();
		$end->setDateTime("{$params['date_end']}T{$params['hr_end']}.000-03:00");
		$this->event->setEnd($end);
		//Cria o evento
		$this->createdEvent = $this->calendarService->events->insert($params['calendar'], $this->event);

		/**
		* Retorna um array com os dados do evento criado
		*/
		return $this->createdEvent;
	}    

	/**
	* Método eventDelete
	*
	* Este método é responsavel por deletar eventos no calendario
	*
	* @param String $id
	* @param String $calendar
	* @return boolean
	*/
	public function eventDelete($id, $calendar){
		return $this->calendarService->events->delete($calendar, $id);
	}
	
	/**
	* Método eventExists
	*
	* @param String $id
	* @param String $calendar
	* @return boolean
	*/
	public function eventExists($id, $calendar){
		try{
			$return = $this->calendarService->events->get($calendar, $id);
			
			if($return['status'] == 'cancelled'){
				$return = false;
			}
		} catch (Exception $e) {
			$return = false;
		}

		return $return;
	}	
}