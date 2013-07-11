<?php
/**
 * Static content controller.
 *
 * Este arquivo ira renderizar as visões contidas em views/States/
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
 * Este controlador contem regras de negócio aplicadas ao model State
 *
 * @package       app.Controller
 * @link http://.framework.nasza.com.br/2.0/controller/States.html
 */
class StatesController extends AppController {

	/**
	* Controller name
	*
	* @var string
	*/
	public $name = 'States';

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
	* Este método carrega uma lista de estados apartir dos parametros de busca montado
	*
	* @param string $country_id
	* @return void
	*/
	public function options ($country_id){
		if(isset($country_id) && !empty($country_id)){
			$params['conditions'] = array('State.country_id' => $country_id);
		}

		$states = $this->State->find('list', $params);
		$model = isset($this->params['named']['model'])?$this->params['named']['model']:$this->modelClass;
		$this->set(compact('states', 'model'));
	}
}
