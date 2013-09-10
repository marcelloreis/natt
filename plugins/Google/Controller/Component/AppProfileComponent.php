<?php
/**
 * Application level Component
 *
 * Este arquivo contem todas as funcoes relacionadas ao Perfil do Usuário Google.
 *
 * @link          https://developers.google.com/google-apps/profiles/auth
 * @package       app.Controller.Component
 */
App::uses('Component', 'Controller');

//Incusao da biblioteca fornecida pelo google
require_once PATH_APP . '/plugins/Google/lib/src/contrib/Google_Oauth2Service.php';


/**
 * Application Component
 *
 * O componente "AppProfile" contem todas as regras de negocio 
 * necessarias para manipular e carregar os dados do usuario google associada ao sistema
 */
class AppProfileComponent extends Component {

	/**
	* Declaracao dos atributos
	*/
	private $profileService;

	/**
	* Método startup
	*
	* O método startup é chamado depois do método beforeFilter do controle, 
	* mas antes do controller executar a action corrente.
	*
	* Aqui serao carregados os servicos do webservice do google ja instnciado no component AppGoogle
	*/
	public function startup($controller){
		/**
		* Instância dos objetos
		*/
		$this->profileService = new Google_Oauth2Service($controller->AppGoogle->service);

		parent::startup($controller);
	}

	public function get(){
		$user = $this->profileService->userinfo->get();

		return $user;
	}

}