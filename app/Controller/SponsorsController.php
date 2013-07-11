<?php
App::uses('AppController', 'Controller');
/**
 * Sponsors Controller
 *
 * O controller 'Sponsors' é responsável por gerenciar 
 * toda a lógica do model 'Sponsor' e renderizar o seu retorno na interface da aplicação.
 *
 * PHP 5
 *
 * @copyright     Copyright 2013-2013, Nasza Produtora
 * @link          http://www.nasza.com.br/ Nasza(tm) Project
 * @package       app.Controller
 *
 * @property Sponsor $Sponsor
 */
class SponsorsController extends AppController {

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

		$sponsor_type = array(SPONSOR_DIAMOND => __d('app', 'Diamond'), SPONSOR_GOLD => __d('app', 'Gold'), SPONSOR_SILVER => __d('app', 'Silver'));
		$this->set(compact('sponsor_type'));
		
	}
}
