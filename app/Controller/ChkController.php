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
class ChkController extends AppController {

	/**
	* Carrega todos os modelos que serao usados no controller
	*/
	public $uses = array('Chk', 'Cliente');

	/**
	* Controller name
	*
	* @var string
	*/
	public $name = 'Chk';

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

		/**
		* Carrega o nome do index
		*/
		$title_view = 'Files in queue';
		if(isset($this->params['named']['processed']) && $this->params['named']['processed'] == '1'){
			$title_view = 'Processed files';
		}


		// $this->Chk->useTable = 'TELEFONE';

		// $map = $this->Chk->find('first');

// debug($map);		
		$this->set(compact('title_view'));
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

		/**
		* Carrega todos os clientes cadastrados no sistema
		*/
		$clientes = $this->Cliente->find('list', array('fields' => array('ID', 'NOME_RAZAO')));

// debug($this->request->data);		
// die;






		$this->set(compact('clientes'));
	}
}
