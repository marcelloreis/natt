<?php
/**
 * Application level Component
 *
 * Este arquivo é uma extencao do webservice do google
 *
 * @link          https://developers.google.com/google-apps/calendar/instantiate
 * @package       app.Controller.Component
 */
App::uses('Component', 'Controller');

//Incusao da biblioteca fornecida pelo google
require_once PATH_APP . '/plugins/Google/lib/src/Google_Client.php';


/**
 * Application Component
 *
 * O componente "AppGoogle" é responsavel pela conexao entre esta framework e o webservice do google
 */
class AppGoogleComponent extends Component {
	/**
	* Carrega os componentes que poderao ser usados em quaisquer controller desta framework
	*/
	public $components = array(
		'Session', 
		);

	/**
	* Declaracao dos atributos da classe
	*/
	public $service;
	public $scope;
	public $authUrl;



	/**
	* Método startup
	*
	* O método startup é chamado depois do método beforeFilter do controle, 
	* mas antes do controller executar a action corrente.
	*
	* Aqui serao carregados os servicos do webservice do google.
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
			* Carrega os servicos da api google
			*/
			$this->loadServices();

			/**
			* Carrega o token de acesso aos servicos
			*/
			if($this->Session->check('User.Social.token')){
				$this->service->setAccessToken($this->Session->read('User.Social.token'));
			}
		}
		
		//@override
		parent::startup($controller);		
	}

	/**
	* Instancia os servicos fornecidos pelo webservice do google
	*/
	private function loadServices(){
		$this->service = new Google_Client();
		$this->service->setApplicationName(GOOGLE_APP_NAME);
		$this->service->setClientId(GOOGLE_CLIENT_ID);
		$this->service->setClientSecret(GOOGLE_CLIENT_SECRET);
		$this->service->setRedirectUri(GOOGLE_REDIRECT_URI);
		$this->service->setDeveloperKey(GOOGLE_DEVELOPER_KEY);
		$this->service->setScopes(GOOGLE_SCOPE_CALENDAR . ' ' . GOOGLE_SCOPE_PROFILE . ' ' . GOOGLE_SCOPE_EMAIL);
	}

	/**
	* Carrega o atributo authUrl com o link da pagina de login do google
	*/
	public function getAuthUrl(){
		/**
		* Carrega os servicos da api google
		*/
		$this->loadServices();

		/**
		* Retorna o link para a pagina de login do google
		*/
		return $this->service->createAuthUrl();
	}

}