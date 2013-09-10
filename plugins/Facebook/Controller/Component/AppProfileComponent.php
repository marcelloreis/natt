<?php
/**
 * Application level Component
 *
 * Este arquivo contem todas as funcoes relacionadas ao Perfil do Usuário Facebook.
 *
 * @link          https://developers.google.com/google-apps/profiles/auth
 * @package       app.Controller.Component
 */
App::uses('Component', 'Controller');

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
	private $profile;

	/**
	* Carrega os componentes que poderao ser usados em quaisquer controller desta framework
	*/
	public $components = array(
		'Facebook.AppFacebook',
		);

	/**
	* Esta funcao retorna todos os dados autorizados pelo usuario conta facebook
	*/
	public function get(){
		/**
		* Carrega o id do usuario facebook
		*/
		$user_id = $this->AppFacebook->service->getUser();

		/**
		* Carrega todos os dados autorizados pelo usuario do facebook
		*/
		$this->profile = $this->AppFacebook->service->api("/$user_id");

		return $this->profile;
	}

}