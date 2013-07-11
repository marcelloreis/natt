<?php
/**
 * Static content controller.
 *
 * Este arquivo ira renderizar as visões contidas em views/Cities/
 *
 * PHP 5
 *
 * @copyright     Copyright 2013-2013, Nasza Produtora
 * @link          http://www.nasza.com.br/ Nasza(tm) Project
 * @package       app.Controller
 */

App::uses('AppController', 'Controller');

/**
 * Static content controller
 *
 * Este controlador contem regras de negócio aplicadas ao model City
 *
 * @package       app.Controller
 * @link http://.framework.nasza.com.br/2.0/controller/Cities.html
 */
class CitiesController extends AppController {

	/**
	* Controller name
	*
	* @var string
	*/
	public $name = 'Cities';

	/**
	* Método index
	* Este método contem regras de negocios visualizar todos os registros contidos na entidade do controlador
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
	* Este método contem regras de negocios para adicionar e editar registros na base de dados
	*
	* @override Metodo AppController.edit
	* @param string $id
	* @return void
	*/
	public function edit($id=null){
		//@override
		parent::edit($id);
	}

	/**
	* Método options
	* Este método carrega uma lista de cidades apartir dos parametros de busca montado
	*
	* @param string $state_id
	* @return void
	*/
	public function options ($state_id){
		if(isset($state_id) && !empty($state_id)){
			$params['conditions'] = array('City.state_id' => $state_id);
		}

		$cities = $this->City->find('list', $params);
		$model = isset($this->params['named']['model'])?$this->params['named']['model']:$this->modelClass;
		$this->set(compact('cities', 'model'));
	}
}
