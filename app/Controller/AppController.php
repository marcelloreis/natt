<?php
/**
 * Application level Controller
 *
 * This file is application-wide controller file. You can put all
 * application-wide controller-related methods here.
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

App::uses('Controller', 'Controller');
/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller {
	/**
	* Declaracao dos atributos privados da classe
	*/
	private $Model;
	private $isRedirect = true;
	public $userLogged;

	/**
	* Carrega os componentes que poderao ser usados em quaisquer controller desta framework
	*/
	public $components = array(
		'Auth', 
		'Acl', 
		'Session', 
		'Main.AppUtils',
		'RequestHandler',
		'Google.AppGoogle',
		'DebugKit.Toolbar' => array('autoRun' => false),
		'Facebook.AppFacebook',
		);
	/**
	* Carrega os helpers que poderao ser usados em quaisquer view desta framework
	*/
	public $helpers = array(
		'Js' => array('Jquery'), 
		'Session',
		'Main.AppGrid', 
		'Main.AppForm', 
		'Main.AppUtils', 
		'Main.AppPaginator', 
		'Main.AppPermissions'
		);




	/**
	* Método beforeFilter
	* Esta função é executada antes de todas ações do controlador. 
	* E no caso da framework, esta sendo usado para checar uma sessão ativa e inspecionar permissões.
	*
	* @override Metodo Controller.beforeFilter
	* @return void
	*/
	public function beforeFilter() {
		//@override
		parent::beforeFilter();

		/**
		* AREA DESTINADA A FUNCOES ESPECIFICAS DO PROJETO, ESTAS FUNCOES NAO PERTENCEM A FRAMEWORK
		*/
		$this->projectVoid();

		/**
		 * Inicializa o atributo userLogged com os dados do usuario logado
		 */
		$this->userLogged = $this->Session->read('Auth.User');

		/**
		 * Inicializa a variavel de ambiente que guardara o tipo da requisicao
		 */
		$requestHandler = 'post';

		/**
		 * Carrega o atributo $this->Model com o objeto do model requisitado
		 */
		$this->Model = $this->{$this->modelClass};

		/**
		* Carrega o Helper AppForm com todos os campos do model e e suas regras de validacao configuradas
		*/
		if($this->Model->useTable){
			$this->helpers['Main.AppForm'] = array('fields' => $this->Model->getColumnTypes(), 'validate' => $this->Model->validate, 'modelClass' => $this->modelClass);
		}

	 	/**
	 	 * Regras de negocio executada quando a requisição é feita via Ajax
	 	 */
	 	if ($this->RequestHandler->isAjax()) {
			/**
			 * Carrega o layout ajax sem cabecalhos e rodape
			 */
			$this->layout = 'ajax';
	 		/**
	 		 * Carrega a variavel de ambiente '$requestHandler' com a string 'ajax' indicando que a requisicao é via ajax
	 		 */
	 		$requestHandler = 'ajax';
	 		/**
	 		 * Cancela o redirecionamento
	 		 */
	 		$this->isRedirect = false;
	 		/**
	 		 * Desabilita o cache do browser
	 		 */
	 		$this->disableCache();
	 	}

		/**
		 * Gera a variavel de ambiente '$requestHandler' indicando o tipo de requisicao efetuada
		 */
		$this->set('requestHandler', $requestHandler);

    	/**
    	 * Configurações do componente Auth
    	 */
		$this->Auth->authorize = array(
			// 'Controller',
			'Actions' => array('actionPath' => 'controllers')
		);
    	$this->Auth->userModel = 'User';
    	$this->Auth->authenticate = array('Form' => array('fields' => array('username' => 'email', 'password' => 'password')));
    	$this->Auth->loginAction = array('controller' => 'users', 'action' => 'login', 'admin' => false, 'plugin' => false);
    	$this->Auth->autoRedirect = true;
    	$this->Auth->logoutRedirect = array('controller' => 'users', 'action' => 'login', 'admin' => false, 'plugin' => false);
    	$this->Auth->loginRedirect = array('controller' => 'users', 'action' => 'dashboard', 'admin' => false, 'plugin' => false);

		/**
		 * Autorizações gerais
		 */
		$this->Auth->allow('login', 'logout', 'authentication', 'natt_fixo_2_landline');
	}

    /**
     * Chamado depois controlador com as regras de negócio, mas antes da visão ser renderizada.
	*
	* @override Metodo Controller.beforeRender
	* @return void
     */
    public function beforeRender(){
		//@override
    	parent::beforeRender();

    	//Carrega a variavel de ambiente userLogged com as informações do usuario logado
    	$this->set('userLogged', $this->userLogged);
		//Carrega o nome do model nas variaveis de ambiente para ser acessado na view
		$this->set('modelClass', $this->modelClass);
		/**
		* Carrega todas as associacoes no model em variavel de ambiente
		*/
		$this->set('belongsTo', $this->Model->belongsTo);
		$this->set('hasAndBelongsToMany', $this->Model->hasAndBelongsToMany);

		/**
		* Verifica se existe uma tabela relacionada ao model
		*/
		if($this->Model->useTable){
			/**
			* Verifica se o model tem acesso a funcao 'getColumnTypes'
			*/
			if($this->Model->useTable){
				/**
				* Concatena os campos virtuais com os campos reais
				*/
				$fields = array_merge(array_keys($this->Model->getColumnTypes()), array_keys($this->Model->virtualFields));
				foreach ($fields as $v) {
					switch ($v) {
						case 'created':
						case 'modified':
						case 'trashed':
						case 'deleted':
							continue;
						break;
						
						default:
							$columns[$v] = ucfirst(__($v));
						break;
					}
				}
				$this->set(compact('columns'));
			}

			/**
			 * Carrega as variaveis de ambiente necessarias
			 */
			if(method_exists($this->Model, 'getFieldText')){
				/**
				 * Gera a variavel de ambiente '$fieldText' com o nome do campo de texto do model/tabela
				 */
				$this->set('fieldText', $this->Model->getFieldText());
			}
		}
    }

	/**
	* Método isAuthorized
	* Regras de autorização configurados para verificar se o usuário esta autorizado para a pagina solicitada. 
	* Cada regra será verificada na sequência, se o usuario logado atender a todas, então o sera retornado TRUE ao final e
	* e será autorizado para a solicitação.
	*
	* @param array $user (Dados do usuario logado)
	* @return boolean
	*/
    public function isAuthorized($user) {
    	/**
    	* Libera o retorno TRUE quando a aplicacao estiver em ambiente de homologacao/testes
    	*/
    	// return true;

		/**
		 * Verifica se o usuário esta logado, caso nao esteja sera redirecionado a pagina de login com a mensagen sessao expirada
		 */
		if(!$this->Auth->loggedIn()){
			$this->Session->setFlash("{$user['given_name']}, " . __('Your session has expired. Make a new login!'), FLASH_TEMPLETE, array('class' => FLASH_CLASS_ALERT), FLASH_SESSION_FORM);
			$this->redirect(array('controller' => 'users', 'action' => 'login'));
		}

		/**
		 * Verifica se ha permissao em cada action do controlador na sequência, $this->redirect();
		 * se alguma delas retornar true, então o usuário será autorizado para a solicitação.
		 */
		switch ($this->params['action']) {
	    	/**
	    	 * Verifica se o usuário tem permissão para acessar o action INDEX
	    	 */
			case 'index':
		    	$this->redirectOnPermissionDeny('index', "{$user['given_name']}, " . __('you do not have permission to view') . ' ' . __(Inflector::pluralize($this->modelClass)) . ".", array('controller' => 'users', 'action' => 'dashboard'));
				break;
			
	    	/**
	    	 * Verifica se o usuário tem permissão para acessar o action EDIT
	    	 */
			case 'edit':

				/**
				* Libera o acesso para o usuario alterar os seus proprios dados
				*/
				if(isset($this->params['pass'][0]) && $this->params['pass'][0] == $this->Auth->User('id') && $this->action == 'edit' && $this->name == 'Users'){
					return true;
				}


		    	$this->redirectOnPermissionDeny('edit', "{$user['given_name']}, " . __('you do not have permission to edit') . ' ' . __(Inflector::pluralize($this->modelClass)) . ".");

		    	/**
		    	 * Verifica se o usuário tem permissão para visualizar o registro.
		    	 * se ele não tiver permissao para visualizar, entao não tem permissao para editar
		    	 */
		    	$this->redirectOnPermissionDeny('view', "{$user['given_name']}, " . __('you are not allowed to view the content of') . ' ' . __(Inflector::pluralize($this->modelClass)) . ".");

		    	/**
		    	 * Quando nao houver a chave primaria entao indica que a acao sera de adicionar o registro
		    	 * entao é verificado se o usuário tem permissão para adicionar registros
		    	 * se nao tiver ele sera redirecionado para o indice do controlador atual
		    	 */
		    	if(!count($this->params['pass'])){
		    		$this->redirectOnPermissionDeny('add', "{$user['given_name']}, " . __('you are not allowed to add') . ' ' . __(Inflector::pluralize($this->modelClass)) . ".");
		    	}
				break;
	    	/**
	    	 * Verifica se o usuário tem permissão para acessar o action ADD
	    	 */
			case 'add':
		    		$this->redirectOnPermissionDeny('add', "{$user['given_name']}, " . __('you are not allowed to add') . ' ' . __(Inflector::pluralize($this->modelClass)) . ".");
				break;
	    	/**
	    	 * Verifica se o usuário tem permissão para acessar o action VIEW
	    	 */
			case 'view':
		    	$this->redirectOnPermissionDeny('view', "{$user['given_name']}, " . __('you do not have permission to view') . ' ' . __(Inflector::pluralize($this->modelClass)) . ".");
				break;
	    	/**
	    	 * Verifica se o usuário tem permissão para acessar o action TRASH
	    	 */
			case 'trash':
		    	$this->redirectOnPermissionDeny('trash', "{$user['given_name']}, " . __('you are not allowed to trash') . ' ' . __(Inflector::pluralize($this->modelClass)) . ".");
				break;
	    	/**
	    	 * Verifica se o usuário tem permissão para acessar o action DELETE
	    	 */
			case 'delete':
		    	$this->redirectOnPermissionDeny('delete', "{$user['given_name']}, " . __('you are not allowed to delete') . ' ' . __(Inflector::pluralize($this->modelClass)) . ".");
				break;
			
		}


		/**
		* Checa se o parametro trashed esta setados, caso esteja
		* verifica se o usuario tem permissao para a visualizacao de registros da lixeira
		*/
		if(
			(isset($this->params['named'][ACTION_TRASH]) && !empty($this->params['named'][ACTION_TRASH])) ||
			(isset($this->params->query['data'][$this->modelClass][ACTION_TRASH]) && !empty($this->params->query['data'][$this->modelClass][ACTION_TRASH]))
			){
				$this->redirectOnPermissionDeny('trash', "{$user['given_name']}, " . __('you are not allowed to view trashed records') . ' ' . __(Inflector::pluralize($this->modelClass)) . ".");
			}

		/**
		* Checa se o parametro deleted esta setados, caso esteja
		* verifica se o usuario logado é o usuario MASTER, pois ele é o unico q tem permissao para visualizar registros DELETADOS
		*/
		if(
			(isset($this->params['named'][ACTION_DELETE]) && !empty($this->params['named'][ACTION_DELETE])) ||
			(isset($this->params->query['data'][$this->modelClass][ACTION_DELETE]) && !empty($this->params->query['data'][$this->modelClass][ACTION_DELETE]))
			){
				if(ADMIN_USER != $this->Auth->User('id')){
					$this->Session->setFlash(__('you are not allowed to view deleted records'), FLASH_TEMPLETE, array('class' => FLASH_CLASS_ALERT), FLASH_SESSION_FORM);
					$this->redirect($this->referer());
				}
			}


    	return true;
    }

	/**
	* Método index
	* Este método contem regras de negocios visualizar todos os registros contidos na entidade/tabela do controlador
	*
	* @param Array $params
	* @return void
	*/
    public function index($params=array()) {
		/**
		* Controle de encapsulamento.
		* Independente do action, sempre que a funcao "index" for invocada
		* sera carregado a view "app/View/[Actions]/index.ctp"
		* a menos que a funcao "$this->render('action', 'layout', 'file')" seja invocada
		* no Controller
		*/
		$this->view = 'index';
		$this->Model->recursive = 0;

		/**
		* Verifica se o index foi chamado apartir de uma grid de adicao de relacionamento de dados
		* Caso seja, sera excluido da consulta todos os registros que ja estao relacionado ao
		* id do model passado por parametro
		*/
		if(isset($this->params['named']['habtmModel']) && isset($this->params['named']['habtmId'])){
			//Carrega todos os atributos do relacionamento
			$habtm = $this->Model->hasAndBelongsToMany[$this->params['named']['habtmModel']];
			//Carrega todos os registros que ja estao relacionado entre os dois models
			$habtmList = $this->Model->$habtm['with']->find('list', array('fields' => array('id', $habtm['foreignKey']), 'conditions' => array($habtm['associationForeignKey'] => $this->params['named']['habtmId'])));
			//Retira da consulta os registros relacionados encontrados
			$params['conditions']["{$this->modelClass}.{$this->Model->primaryKey} NOT"] = $habtmList;
		}

		/**
		* Verifica se existe uma tabela relacionada ao model
		*/
		if($this->Model->useTable){
	    	/**
			 * Se o campo "q" for igual a 1, simula o envio do form por get
			 * redirecionando para http://[domain]/[controller]/[action]/seach:value1/namedN:valueN
	    	 */
	    	$this->__post2get();

	    	/**
	    	* Verifica se foi passado algum valor na variavel padrao de busca
	    	*/
			if(isset($this->params['named']['search']) && !empty($this->params['named']['search'])){
				$search = $this->params['named']['search'];
			}else if(isset($this->params->query['data'][$this->modelClass]['search']) && !empty($this->params->query['data'][$this->modelClass]['search'])){
				$search = $this->params->query['data'][$this->modelClass]['search'];
			}

			/**
			* Caso a variavel padrao de busca esteja setada, monta as condicoes de busca
			*
			* PARA QUE A BUSCA DINAMICA FUNCIONE, É NECESSARIO QUE TODAS AS ASSOCIACOES ESTEJAM DEVIDAMENTE
			* DECLARADAS EM Model/NomeDoModel.php
			*/
			if(!empty($search)){
				//Guarda as condicoes montadas com os campos de texto padrao dos models
				$searchMap = array();
				//Monta as condicoes de busca do campo de texto principal dos models associados
				foreach ($this->Model->belongsTo as $k => $v) {
					$searchMap[]["{$k}.{$this->Model->$k->getFieldText()} LIKE"] = "%{$search}%";
				}

				//Monta as condicoes de busca do campo de texto principal do modelo/tabela
				$searchMap[]["{$this->modelClass}.{$this->Model->getFieldText()} LIKE"] = "%{$search}%";

				//Verifica se existem mais de uma condicao montada, caso exista, insere a clausula OR
				if(count($searchMap) > 1){
					$searchMap = array('OR' => $searchMap);
				}

				//Carrega o parametro 'conditions' com as condicoes montadas dinamicamente
				if(isset($params['conditions']) && is_array($params['conditions'])){
					array_push($params['conditions'], $searchMap);
				}else{
					$params['conditions'] = $searchMap;
				}
			}

	    	/**
	    	* Verifica se foi passado algum valor na variavel trashed
	    	*/
			if(isset($this->params['named'][ACTION_TRASH]) && !empty($this->params['named'][ACTION_TRASH])){
				$params['conditions']["{$this->modelClass}." . ACTION_TRASH] = $this->params['named'][ACTION_TRASH];
			}else if(isset($this->params->query['data'][$this->modelClass][ACTION_TRASH]) && !empty($this->params->query['data'][$this->modelClass][ACTION_TRASH])){
				$params['conditions']["{$this->modelClass}." . ACTION_TRASH] = $this->params->query['data'][$this->modelClass][ACTION_TRASH];
			}			

	    	/**
	    	* Verifica se foi passado algum valor na variavel deleted
	    	*/
			if(isset($this->params['named'][ACTION_DELETE]) && !empty($this->params['named'][ACTION_DELETE])){
				$params['conditions']["{$this->modelClass}." . ACTION_DELETE] = $this->params['named'][ACTION_DELETE];
			}else if(isset($this->params->query['data'][$this->modelClass][ACTION_DELETE]) && !empty($this->params->query['data'][$this->modelClass][ACTION_DELETE])){
				$params['conditions']["{$this->modelClass}." . ACTION_DELETE] = $this->params->query['data'][$this->modelClass][ACTION_DELETE];
			}			

			//Configurações padrao da busca
			$defaults = array(
							'limit' => LIMIT
				);
			$params = array_merge($defaults, $params);
	    	$this->paginate = array($this->modelClass => $params);

	    	//Carrega os dados de acordo com os parametros montados ate aqui
	    	$map = $this->paginate();
	    	$this->set(Inflector::variable($this->modelClass), $map);

			/**
			* Conta quantos registros NAO foram enviados para a lixeiro e nem deletados
			*/
			unset($params['conditions']["{$this->modelClass}." . ACTION_TRASH]);
			unset($params['conditions']["{$this->modelClass}." . ACTION_DELETE]);
			$inbox = $this->Model->find('count', $params);
			$this->set(compact('inbox'));
			
			/**
			* Conta quantos registros enviados para lixeira existem com os mesmos parametros de busca
			*/
			$params['conditions']["{$this->modelClass}." . ACTION_TRASH] = true;
			$trashed = $this->Model->find('count', $params);
			$this->set(compact('trashed'));
			
			/**
			* Conta quantos registros deletados existem com os mesmos parametros de busca
			*/
			$params['conditions']["{$this->modelClass}." . ACTION_DELETE] = true;
			$deleted = $this->Model->find('count', $params);
			$this->set(compact('deleted'));

	    	return $map;
		}
    }

	/**
	* Método add
	* Encapsulamento da função EDIT para controle de acesso via ACL
	*
	* @return void
	*/
	public function add(){
		$this->edit();
	}

	/**
	* Método view
	* Encapsulamento da função EDIT para controle de acesso via ACL
	*
	* @param String $id
	* @return void
	*/
	public function view($id=null){
		$this->Model->id = $id;
		if (!$this->Model->exists()) {
			$this->Session->setFlash(sprintf(__("It was not possible to view the %s, or it does not exist in the database."), __d('fields', $this->modelClass)), FLASH_TEMPLETE, array('class' => FLASH_CLASS_ALERT), FLASH_SESSION_FORM);
			$this->redirect(array('action' => 'index'));
		}	

		$this->edit($id);
	}

	/**
	* Método edit
	*
	* Este método contem regras de negocios para adicionar e editar registros na base de dados
	*
	* @param String $id
	* @return void
	*/
	public function edit($id=null){
		/**
		* Controle de encapsulamento.
		* Independente do action, sempre q a funcao "edit" for invocada
		* sera carregado a view "app/View/Actions/edit.ctp"
		*/
		$this->view = 'edit';

		/**
		 * Carrega o campo ID do model com o ID passado pelo parametro 
		 * para que o registro dessa chave primaria seja atualizado
		 * e
		 * Verifica se o id passado por parametro existe, caso não exista redireciona para o index.
		 */
		if($id){
			$this->request->data[$this->modelClass]['id'] = $id;
			$this->Model->id = $id;

			if (!$this->Model->exists()) {
				$this->Session->setFlash(sprintf(__("It was not possible to edit the %s [%s], or it does not exist in the database."), __d('fields', $this->modelClass), $this->Model->id), FLASH_TEMPLETE, array('class' => FLASH_CLASS_ALERT), FLASH_SESSION_FORM);
				$this->redirect(array('action' => 'index'));
			}
		}

		/**
		 * Verifica se o formulário foi submetido por post
		 */
		if ($this->request->is('post') || $this->request->is('put')) {
			/**
			 * Reinicializa o estado do model para salvar novos dados.
			 * sem este metodo todas as funções do AppModel deixam de funcionar pois 
			 * o atributo $this->data só funciona quando este metodo é setado
			 */
			$this->Model->create($this->request->data);

			/**
			 * A criação ou atualização é controlada pelo campo id do model. 
			 * Se o $this->Model->id já estiver definido, o registro com esta chave primária será atualizado. 
			 * Caso contrário, um novo registro será criado.
			 */
			if($this->Model->save()){
				if($this->isRedirect){
					$this->Session->setFlash(__(FLASH_SAVE_SUCCESS), FLASH_TEMPLETE, array('class' => FLASH_CLASS_SUCCESS), FLASH_SESSION_FORM);
					$this->redirect(array('action' => 'edit', $this->Model->id));
				}
			}else{
				/**
				 * Carrega os erros encontrados ao tentar salvar o formulário
				 */
				$this->Model->set($this->request->data);
				$errors = $this->Model->invalidFields();
				$msgs = array();
				foreach ($errors as $k => $v) {
					if(isset($v[0])){
						$msgs[$k] = $v[0];
					}
				}
				$this->Session->setFlash(__(FLASH_SAVE_ERROR), FLASH_TEMPLETE, array('class' => FLASH_CLASS_ERROR, 'multiple' => $msgs), FLASH_SESSION_FORM);
			}

		} 

		/**
		 * Pupula os dados do registro apartir da chave primário encontrada
		 */
		if(!empty($this->Model->id)){
			$this->data = $this->Model->read();
		}
	}

	/**
	* Método __remove
	*
	* Este método altera para true o campo trashed|deleted do(s) registro passado por parametro.
	*
	* @param String $id
	* @param String $action
	* @return boolean $removed
	*/
	protected function __remove($id=null, $action, $cascade=true, $value=true) {
		/**
		* Carrega o model com os dados vindo do post
		*/
		$this->Model->set($this->request->data);

		/**
		* Carrega o id do model caso o ID do registro venho por GET
		*/
		if(isset($id) && is_numeric($id)){
			/**
			* Carrega o Model com o ID q sera movido para a lixeira
			*/
			$this->Model->id = $id;

			/**
			* Verifica se o ID passado existe na base de dados
			*/
			if ($value && !$this->Model->exists()) {
				$this->Session->setFlash(sprintf(__("The %s does not exist in the database."), __d('fields', $this->modelClass)), FLASH_TEMPLETE, array('class' => FLASH_CLASS_ERROR), FLASH_SESSION_FORM);
				$this->redirect($this->referer());
			}
		}

		/**
		* Remove os registros contidos no model
		*/
		$removed = $this->Model->remove($action, $cascade, $value);
		return $removed;
	}	

	/**
	* Método trash
	*
	* Esta funcao é um encapsulamento da funcao __remove, porem a funcao __remove sera chamada 
	* passando como parametro a acao ACTION_TRASH que força a atualizacao do campo trashed do registro
	*
	* @param String $id
	* @return void
	*/
	public function trash($id=null) {
		/**
		* Move os registros para a lixeira
		*/
		if ($this->__remove($id, ACTION_TRASH)) {
			$this->Session->setFlash(sprintf(__("%s moved to the trash."), __d('fields', $this->name)), FLASH_TEMPLETE, array('class' => FLASH_CLASS_SUCCESS), FLASH_SESSION_FORM);
		}else{
			$this->Session->setFlash(sprintf(__("Unable to move the %s to the trash."), __d('fields', $this->name)), FLASH_TEMPLETE, array('class' => FLASH_CLASS_SUCCESS), FLASH_SESSION_FORM);
		}

		$this->redirect($this->referer());
	}	

	/**
	* Método delete
	*
	* Esta funcao é um encapsulamento da funcao __remove, porem a funcao __remove sera chamada 
	* passando como parametro a acao ACTION_DELETE que força a atualizacao do campo deleted do registro
	*
	* @param String $id
	* @return void
	*/
	public function delete($id=null) {
		/**
		* Move os registros permanentemente
		*/
		if ($this->__remove($id, ACTION_DELETE)) {
			$this->Session->setFlash(sprintf(__("%s deleted."), __d('fields', $this->name)), FLASH_TEMPLETE, array('class' => FLASH_CLASS_SUCCESS), FLASH_SESSION_FORM);
		}else{
			$this->Session->setFlash(sprintf(__("Unable delete the %s."), __d('fields', $this->name)), FLASH_TEMPLETE, array('class' => FLASH_CLASS_SUCCESS), FLASH_SESSION_FORM);
		}
		$this->redirect($this->referer());
	}	

	/**
	* Método restore
	*
	* Esta funcao é um encapsulamento da funcao __remove, porem a funcao __remove sera chamada 
	* passando os parametros necessarios para que o registro seja removido da lixeira
	*
	* @param String $id
	* @return void
	*/
	public function restore($id=null) {
		/**
		* Restaura os registros deletados
		*/
		if ($this->__remove($id, ACTION_DELETE, true, false)) {
			$this->Session->setFlash(sprintf(__("%s restored."), __d('fields', $this->name)), FLASH_TEMPLETE, array('class' => FLASH_CLASS_SUCCESS), FLASH_SESSION_FORM);
		}else{
			$this->Session->setFlash(sprintf(__("Unable restore the %s."), __d('fields', $this->name)), FLASH_TEMPLETE, array('class' => FLASH_CLASS_SUCCESS), FLASH_SESSION_FORM);
		}

		/**
		* Restaura os registros movidos para a lixeira
		*/
		if ($this->__remove($id, ACTION_TRASH, true, false)) {
			$this->Session->setFlash(sprintf(__("%s restaured to the trash."), __d('fields', $this->name)), FLASH_TEMPLETE, array('class' => FLASH_CLASS_SUCCESS), FLASH_SESSION_FORM);
		}else{
			$this->Session->setFlash(sprintf(__("Unable restore the %s to the trash."), __d('fields', $this->name)), FLASH_TEMPLETE, array('class' => FLASH_CLASS_SUCCESS), FLASH_SESSION_FORM);
		}


		$this->redirect($this->referer());
	}	


	/**
	 * Verifica que se usuário logado tem acesso ao controlador/ação passada por parametro
	 */
	private function hasPermission($actionPath){
		return $this->Acl->check(array('model' => 'User', 'foreign_key' => $this->Session->read('Auth.User.id')), $actionPath);
	}

	/**
	 * Redireciona para index do controller quando o usuario não tiver permissao para acessar a funcao/action passado por paramtro
	 */
	private function redirectOnPermissionDeny($action, $msg, $redirect=null){
		if(!$this->hasPermission($this->name . "/{$action}")){
			$redirect = $redirect?$redirect:array('action' => 'index');
			$this->Session->setFlash($msg, FLASH_TEMPLETE, array('class' => FLASH_CLASS_ALERT), FLASH_SESSION_FORM);
			$this->redirect($redirect);
		}
	}

	/**
	* Monta a paginacao apartir do model passado pelo parametro
	*/
	protected function __paginationHabtm($habtmModel, $params=array()){
		/**
		* Carrega todos os atibutos da ligacao entre os models
		*/
		$habtm = $this->Model->hasAndBelongsToMany[$habtmModel];

		/**
    	* Verifica se foi passado alguma valor na variavel padrao de busca
    	*/
		if(isset($this->params['named']['search']) && !empty($this->params['named']['search'])){
			$search = $this->params['named']['search'];
		}

		/**
		* Caso a variavel padrao de busca esteja setada, monta as condicoes de busca
		*
		* PARA QUE A BUSCA DINAMICA FUNCIONE, É NECESSARIO QUE TODAS AS ASSOCIACOES ESTEJAM DEVIDAMENTE
		* DECLARADAS EM Model/NomeDoModel.php
		*/
		if(!empty($search)){
			//Guarda as condicoes montadas com os campos de texto padrao dos models
			$searchMap = array();
			//Monta as condicoes de busca do campo de texto principal dos models associados
			foreach ($this->Model->$habtm['className']->belongsTo as $k => $v) {
				$searchMap[]["{$k}.{$this->Model->$habtm['className']->$k->getFieldText()} LIKE"] = "%{$search}%";
			}

			//Monta as condicoes de busca do campo de texto principal do modelo/tabela
			$searchMap[]["{$habtm['className']}.{$this->Model->$habtm['className']->getFieldText()} LIKE"] = "%{$search}%";

			//Verifica se existem mais de uma condicao montada, caso exista, insere a clausula OR
			if(count($searchMap) > 1){
				$searchMap = array('OR' => $searchMap);
			}

			//Carrega o parametro 'conditions' com as condicoes montadas dinamicamente
			if(isset($params['conditions']) && is_array($params['conditions'])){
				array_push($params['conditions'], $searchMap);
			}else{
				$params['conditions'] = $searchMap;
			}
		}

		//Configurações padrao da busca
		$defaults = array(
			'recursive' => '-1',
		    'conditions' => array("{$habtm['with']}.{$habtm['foreignKey']}" => $this->Model->id),
		    'joins' => array(
		        array(
		            'alias' => $habtm['with'],
		            'table' => $habtm['joinTable'],
		            'type' => 'INNER',
		            'conditions' => "{$habtm['with']}.{$habtm['associationForeignKey']} = {$habtm['className']}.id"
		        )
		    ),
		    'limit' => LIMIT,
		    'order' => array(
		        "{$habtm['className']}.{$this->Model->$habtm['className']->getFieldText()}" => 'asc'
		    )
		);
		$params = array_merge($defaults, $params);

		$this->paginate = array($habtm['className'] => $params);
		$paginationData = $this->paginate($habtm['className']);     

	    /**
	    * Carrega os atributos do model associado
	    */
	    $columns['id'] = 'id';
	    $columns['displayName'] = $this->Model->$habtm['className']->getFieldText();
		$this->set("columns{$habtm['className']}", $columns);

		/**
		* Carrega as paginacoes criadas
		*/
		$this->set($habtm['className'], $paginationData);
	}

	/**
	 * Se o campo "q" for igual a 1, simula o envio do form por get
	 * redirecionando para http://domain/controller/action/seach:value1/namedN:valueN
	 */
	protected function __post2get(){
    	if(isset($this->request->data['q']) && $this->request->data['q'] == 'post'){
			unset($this->request->data['q']);
			$redirect = array(
				'controller' => $this->params['controller'],
				'action' => $this->params['action']
				);
			foreach ($this->data[$this->modelClass] as $k => $v) {
				$redirect[$k] = $v;
			}

	        foreach ($this->params['named'] as $k => $v) {
	        	if(!preg_match('/(page|search)/si', $k)){
	            	$redirect[$k] = $v;
	        	}
	        }

	        if(isset($this->params['pass'][0])){
	            array_push($redirect, $this->params['pass'][0]);
	        }

			$this->redirect($redirect);    		
    	}		
	}	

	/**
	* Método unjoin
	* Este método contem regras de negocios que permitem desassociar registros HasAndBelongsToMany
	*
	* @param string $id
	* @return void
	*/
	protected function __unjoin($id){
		/**
		 * Verifica se o formulário foi submetido por post
		 */
		if ($this->request->is('post') || $this->request->is('put')) {
			/**
			* Verifica se todos os parametros foram devidamentes setados
			*/
			if(!isset($this->request->data[$this->modelClass]['id'])){
				$this->Session->setFlash(sprintf("The %s code is not found.", __d('fields', $this->modelClass)), FLASH_TEMPLETE, array('class' => FLASH_CLASS_ERROR), FLASH_SESSION_FORM);
				$this->redirect($this->referer());
			}
			
			if(!isset($this->request->data[$this->modelClass]['habtm'])){
				$this->Session->setFlash('The name of the association was not found.', FLASH_TEMPLETE, array('class' => FLASH_CLASS_ERROR), FLASH_SESSION_FORM);
				$this->redirect($this->referer());
			}
			
			if(!is_array($this->request->data[$this->request->data[$this->modelClass]['habtm']]['id'])){
				$this->Session->setFlash(sprintf("The %s code is not found.", __d('fields', $this->request->data[$this->modelClass]['habtm'])), FLASH_TEMPLETE, array('class' => FLASH_CLASS_ERROR), FLASH_SESSION_FORM);
				$this->redirect($this->referer());
			}

			/**
			* Carrega o nome do registro associado
			*/
			$habtmModel = $this->request->data[$this->modelClass]['habtm'];
			/**
			* Carrega o codigo do registro associado
			*/
			$habtm_id = $this->request->data[$this->request->data[$this->modelClass]['habtm']]['id'];
			/**
			* Carrega o codigo do model
			*/
			$id = $this->request->data[$this->modelClass]['id'];


		}else{
			/**
			* Verifica se todos os parametros foram devidamentes setados
			*/
			if(!isset($id)){
				$this->Session->setFlash(sprintf("The %s code is not found.", __d('fields', $this->modelClass)), FLASH_TEMPLETE, array('class' => FLASH_CLASS_ERROR), FLASH_SESSION_FORM);
				$this->redirect($this->referer());
			}
			
			if(!isset($this->params['named']['habtm'])){
				$this->Session->setFlash('The name of the association was not found.', FLASH_TEMPLETE, array('class' => FLASH_CLASS_ERROR), FLASH_SESSION_FORM);
				$this->redirect($this->referer());
			}
			
			if(!isset($this->params['named']['habtm_id'])){
				$this->Session->setFlash(sprintf("The %s code is not found.", __($this->params['named']['habtm'])), FLASH_TEMPLETE, array('class' => FLASH_CLASS_ERROR), FLASH_SESSION_FORM);
				$this->redirect($this->referer());
			}

			/**
			* Carrega o nome do registro associado
			*/
			$habtmModel = $this->params['named']['habtm'];
			/**
			* Carrega o codigo do registro associado
			*/
			$habtm_id = $this->params['named']['habtm_id'];
		}

// debug($this->Model->hasAndBelongsToMany);die;
		/**
		* Verifica se existe uma associacao HABTM informada no model
		*/
		if(isset($this->Model->hasAndBelongsToMany[$habtmModel])){
			$habtm = $this->Model->hasAndBelongsToMany[$habtmModel];

			/**
			* Exclui a associacao apartir dos IDs passados por parametro
			*/
			$return = $this->Model->$habtm['with']->deleteAll(array("{$habtm['with']}.{$habtm['foreignKey']}" => $id, "{$habtm['with']}.{$habtm['associationForeignKey']}" => $habtm_id));
			if($return){
				$this->Session->setFlash(sprintf(__("The %s was disassociated %s successfully."), __d('fields', $this->name), __($habtmModel)), FLASH_TEMPLETE, array('class' => FLASH_CLASS_SUCCESS), FLASH_SESSION_FORM);
			}else{
				$this->Session->setFlash(sprintf(__("Could not unbind %s and %s."), __d('fields', $this->modelClass), __($habtmModel)), FLASH_TEMPLETE, array('class' => FLASH_CLASS_ERROR), FLASH_SESSION_FORM);
			}
		}

		$this->redirect($this->referer());
	}	

    /**
     * Carrega todas as permissoes do usuario logado em sessions
     */
    public function __loadPermissionsOnSessions(){
        //Limpa todas as sessions das permissoes
    	$this->Session->delete('Auth.Permissions');

        //Carrega os dados do usuario logado
    	$user = $this->Auth->user();

        //Verifica se o usuario se logou corretamente
    	if (isset($user)) {
            //Carrega o ID do ARO a qual o usuario pertence
    		$aro = $this->Acl->Aro->find('first', array(
    			'conditions' => array(
    				'Aro.model' => 'Group',
    				'Aro.foreign_key' => $user['group_id'],
    				),
    			));

            //Percorre por todos os ACOs(funcoes) existentes
    		$acos = $this->Acl->Aco->children();
    		foreach($acos as $aco){
    			$permission = $this->Acl->Aro->Permission->find('first', array(
    				'conditions' => array(
    					'Permission.aro_id' => $aro['Aro']['id'],
    					'Permission.aco_id' => $aco['Aco']['id'],
    					),
    				));

                //Verifica se o usuario tem permissao para o ACO(funcao) atual do foreach
    			if(isset($permission['Permission']['id'])){
    				if ($permission['Permission']['_create'] == 1 || $permission['Permission']['_read'] == 1 || $permission['Permission']['_update'] == 1 || $permission['Permission']['_delete'] == 1) {

                    //Carrega a funcao do controller que o usuario tem permissao na sessao
    					if(!empty($permission['Aco']['parent_id'])){
    						$parentAco = $this->Acl->Aco->find('first', array(
    							'conditions' => array(
    								'id' => $permission['Aco']['parent_id']
    								)   
    							));
    						$this->Session->write("Auth.Permissions.{$parentAco['Aco']['alias']}.{$permission['Aco']['alias']}", true);
    					}
    				}
    			}
    		}
    	}
    }	

    /**
    * AREA DESTINADA A FUNCOES ESPECIFICAS DO PROJETO, ESTAS FUNCOES NAO PERTENCEM A FRAMEWORK
    */
    private function projectVoid(){
    }

}