<?php
App::uses('AppController', 'Controller');
/**
 * Marketings Controller
 *
 * O controller 'Marketings' é responsável por gerenciar 
 * toda a lógica do model 'Marketing' e renderizar o seu retorno na interface da aplicação.
 *
 * PHP 5
 *
 * @copyright     Copyright 2013-2013, Nasza Produtora
 * @link          http://www.nasza.com.br/ Nasza(tm) Project
 * @package       app.Controller
 *
 * @property Marketing $Marketing
 */
class MarketingsController extends AppController {

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
				* Student Associados ao Event
				*/
				case 'Student':
					$this->__paginationHabtm('Student');
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
