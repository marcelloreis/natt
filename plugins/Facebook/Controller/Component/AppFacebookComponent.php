<?php
/**
 * Application level Component
 *
 * Este arquivo é uma extencao do webservice do facebook
 *
 * @link          https://developers.google.com/google-apps/calendar/instantiate
 * @package       app.Controller.Component
 */
App::uses('Component', 'Controller');

//Incusao da biblioteca fornecida pelo google
require_once PATH_APP . '/plugins/Facebook/lib/src/facebook.php';


/**
 * Application Component
 *
 * O componente "AppFacebook" é responsavel pela conexao entre esta framework e o webservice do faceook
 */
class AppFacebookComponent extends Component {
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
	public $authUrl;



	/**
	* Método startup
	*
	* O método startup é chamado depois do método beforeFilter do controle, 
	* mas antes do controller executar a action corrente.
	*
	* Aqui serao carregados os servicos do webservice do facebook.
	*
	* @override Metodo app.Controller.Component.startup
	* @param Controller $controller
	* @return void
	*/
	public function startup(Controller $controller){
		/**
		* A sessao 'User.Social.api' é setada no users controller no momento em que o 
		* usuario seleciona a rede social que sera utilizada como login
		*/
		if($this->Session->read('User.Social.api') == FACEBOOK_GROUP){
			/**
			* Carrega os servicos da api facebook
			*/
			$this->loadServices();
		}


		//@override
		parent::startup($controller);
	}

	/**
	* Instancia os servicos fornecidos pelo webservice do facebook
	*/
	private function loadServices(){
		$config = array(
			"appId" => FACEBOOK_CLIENT_ID,
			"secret" => FACEBOOK_CLIENT_SECRET
		);

		$this->service = new Facebook($config);
	}	

	/**
	* Carrega o atributo authUrl com o link da pagina de autorizacao do facebook
	*/
	public function getAuthUrl(){
		/**
		* Carrega os servicos da api facebook
		*/
		$this->loadServices();

		$params = array(
			"scope" => 'photo_upload,' . FACEBOOK_SCOPE_EMAIL . ',' . FACEBOOK_SCOPE_BIRTHDAY . ',' . FACEBOOK_SCOPE_OFFLINE . ',' . FACEBOOK_SCOPE_PHOTOS,
			"redirect_uri" => FACEBOOK_REDIRECT_URI
		);

		return $this->service->getLoginUrl($params);		
	}
}