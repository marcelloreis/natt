<?php
App::uses('AppController', 'Controller');
/**
 * Events Controller
 *
 * O controller 'Events' é responsável por gerenciar 
 * toda a lógica do model 'Event' e renderizar o seu retorno na interface da aplicação.
 *
 * PHP 5
 *
 * @copyright     Copyright 2013-2013, Nasza Produtora
 * @link          http://www.nasza.com.br/ Nasza(tm) Project
 * @package       app.Controller
 *
 * @property Event $Event
 */
class EventsController extends AppController {

	public function beforeRender(){
		parent::beforeRender();
	}	

	/**
	* Método index
	* Este método contem regras de negocios que permitem visualizar todos os registros contidos na entidade do controlador
	*
	* @override Metodo AppController.index
	* @param string $period (Periodo das movimentacoes q serao listadas)
	* @return void
	*/
	public function index($params=array()){
		//@override
		parent::index($params);
	}		

	/**
	* Método edit
	* Este método contem regras de negocios que permitem adicionar e editar registros na base de dados
	*
	* @override Metodo AppController.edit
	* @param string $id
	* @return void
	*/
	public function edit($id=null){
		//@override
		parent::edit($id);

		// $countries = $this->Event->City->State->Country->find('list');
		// $states = $this->Event->City->State->find('list', array('conditions' => array('country_id' => 'BR')));

		// $brasil = array('BR' => $countries['BR']);
		// unset($countries['BR']);
	 // 	$countries = array_merge($brasil, $countries);

		// $this->set(compact('countries', 'states'));
		// if(isset($this->request->data['City']['state_id']) && !empty($this->request->data['City']['state_id'])){
		// 	$this->request->data['Event']['state_id'] = $this->request->data['City']['state_id'];
		// 	$citiesPerUf = $this->Event->City->find('list', array('conditions' => array('state_id' => $this->request->data['City']['state_id'])));
		// 	$this->set(compact('citiesPerUf'));
		// }	

		/**
		* Verifica se o $id do registro foi setado
		*/
		if(isset($id) && !empty($id)){
			/**
			* Carrega o nome do model associado
			*/
			$habtm = isset($this->params['named']['habtm'])?$this->params['named']['habtm']:false;
			switch ($habtm) {
					
				/**
				* Responsible Associados ao Event
				*/
				case 'Responsible':
					$this->__paginationHabtm('Responsible');
					break;
								
				/**
				* Speaker Associados ao Event
				*/
				case 'Speaker':
					$this->__paginationHabtm('Speaker');
					break;
								
				/**
				* Sponsor Associados ao Event
				*/
				case 'Sponsor':
					$this->__paginationHabtm('Sponsor');
					break;
			}
		}
	}

	/**
	* Método unjoin
	* Este método contem regras de negocios que permitem desassociar registros HasAndBelongsToMany
	*
	* @override Metodo AppController.__unjoin
	* @param string $id
	* @return void
	*/
	public function unjoin($id=null){
		//@override
		parent::__unjoin($id);
	}
}
