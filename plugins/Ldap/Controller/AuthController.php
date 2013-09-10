<?php
App::uses('Controller', 'Controller');

/**
 * Application level Controller
 *
 * Este arquivo contem todas as necessarias para que o usuario do 
 * sistema forneca suas credenciais de acesso a conta google
 *
 * @package       app.Controller.Controller
 */
class AuthController extends AppController {

	/**
	* Carrega os models que serao usados no controller
	*/
	public $uses = array('User', 'Social', 'Ldap.AuthUser');

	/**
	* Carrega os componentes que poderao ser usados em quaisquer controller desta framework
	*/
	public $components = array(
		'Session',
		);

	/**
	* Método authentication
	*
	* Este método faz a autenticacao do usuario no Active Directory (AD - Windows)
	*/
	public function authentication(){
		//Tenta efetuar o login no AD com as credenciais informadas na tela de login do sistema
		if ($this->AuthUser->login($this->Session->read('User.Social.user'), $this->Session->read('User.Social.password'))) {
			/**
			* Salva/Atualiza as credenciais do usuario no banco de dados
			*/
			$this->saveCredentials();
		}else{
			//Redireciona o usuario para a pagina de login novamente caso a pagina do google nao retorne o codigo de homologacao
			$this->Session->setFlash("Não foi possível validar as credenciais do seu usuário.", FLASH_TEMPLETE, array('class' => FLASH_CLASS_ERROR), FLASH_SESSION_LOGIN);
			$this->redirect($this->Auth->logout());
		}
	}

	/**
	* Método saveCredentials
	*
	* Este método é responsavel por salvar os dados contidos na rede social
	* apartir do token de autorizacao fornecido pelo proprio usuario
	*
	* @param array $token
	*/
	private function saveCredentials(){

		/**
		* Carrega os dados basicos do usuario AD
		*/
		$userAD = $this->AuthUser->getUser();

		/**
		* Verifica se o componente Profile retornou os dados do usuario
		*/
		if(!$userAD){
			//Redireciona o usuario para a pagina de login novamente caso haja erro no carregamento dos seus dados basicos
			$this->Session->setFlash("Não foi possível dados basicos do seu usuário.", FLASH_TEMPLETE, array('class' => FLASH_CLASS_ERROR), FLASH_SESSION_LOGIN);
			$this->redirect($this->Auth->logout());
		}else{
			//Carrega o ID do usuario AD
			$userAD['id'] = sprintf("%u", crc32($userAD['samaccountname'][0]));
			$userAD['token'] = sprintf("%u", crc32($userAD['samaccountname'][0]));
			$userAD['email'] = $userAD['userprincipalname'][0];

			/**
			* Verifica se o usuario já esta cadastrado na tabela SOCIALS
			*/
			$userAlrealyAdd = $this->Social->findById($userAD['id']);

			if($userAlrealyAdd){
				//Carrega o id do model com o id do usuario encontrado no AD
				$userSystem['id'] = $userAlrealyAdd['Social']['user_id'];

				//Carrega o ID do usuario com o ID encontrado na base de dados do sistema
				$userAD['id'] = $userAlrealyAdd['Social']['id'];
			}

			/**
			* Cadastra/Atualiza o usuario do AD na tabela USERS
			*/
			$userSystem['group_id'] = LDAP_GROUP;
			$userSystem['name'] = $userAD['displayname'][0];
			$userSystem['given_name'] = $userAD['givenname'][0];
			$userSystem['password'] = AuthComponent::password(substr(preg_replace('/[^0-9]/', '', $this->Session->read('User.Social.password')), -6));
			$userSystem['email'] = $userAD['email'];

			/**
			* Carrega o status do usuario do SISTEMA de acordo com o status do usuario na REDE SOCIAL
			*/
			$userSystem['status'] = 1;

			/**
			* Verifica se o usuario ja esta cadastrado no sistema, caso ja esteja, sera mantida a senha antiga do usuario
			*/
			$userSystemAlrealyAdd = $this->User->findByEmail($userAD['email']);

			if($userSystemAlrealyAdd){
				//Mantem o usuario no grupo de administradores caso ele seja um adm
				$userSystem['group_id'] = ($userSystemAlrealyAdd['User']['group_id'] == ADMIN_GROUP)?ADMIN_GROUP:LDAP_GROUP;
				//Carrega o ID do usuario do sistema encontrado para que os dados sejam atualizado ao invez de inseridos
				$userSystem['id'] = $userSystemAlrealyAdd['User']['id'];
				//Mantem a senha anterior criada pelo usuario
				unset($userSystem['password']);
			}

			$this->User->create($userSystem);
			$this->User->save($userSystem);


			/**
			* Carrega os dados do usuario
			*/
			$userAD['user_id'] = $this->User->id;
			$userAD['social_group'] = LDAP_GROUP;
			$userAD['name'] = $userAD['displayname'][0];
			$userAD['family_name'] = $userAD['sn'][0];
			$userAD['given_name'] = $userAD['givenname'][0];
			$userAD['link'] = "http://intra.wine.com.br/author/{$userAD['samaccountname'][0]}/";
			/**
			* Insere/Atualiza o usuario na tabela SOCIALS
			*/
			$data = array('Social' => $userAD);
			$this->Social->create($data);
			if(!$this->Social->save($data)){
				//Redireciona o usuario para a pagina de login novamente caso o cadastro nao seja bem sucedido
				$this->Session->setFlash("Não foi possível cadasta-lo em nossa base de dados, tente mais tarde.", FLASH_TEMPLETE, array('class' => FLASH_CLASS_ERROR), FLASH_SESSION_LOGIN);
				$this->redirect($this->Auth->logout());
			}
		}


		/**
		* Monta os dados de acesso ao sistema
		*/
		$user = $this->User->read();
		$login = $user['User'];
		$login['Group'] = $user['Group'];
		$login['Social'] = $data['Social'];

		/**
		* Efetua o login do usuario google no sistema
		*/
        if ($this->Auth->login($login)) {
        	//Carrega todas as permissoes do usuario/grupo em sessao
            parent::__loadPermissionsOnSessions();
        	//Carrega o token de permissao fornecido pelo google em sessao
        	$this->Session->write('User.Social.token', $userAD['token']);
        	//Carrega o id do usuario google
        	$this->Session->write('User.Social.id', $userAD['id']);
            //Redireciona o usuario para a pagina inicial do sistema
        	$this->Session->setFlash("Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec aliquam justo sit amet odio aliquam semper. Phasellus eget lobortis nisi. Vivamus in nulla ut justo convallis tincidunt. Etiam rutrum suscipit dolor, vitae facilisis eros tincidunt gravida. Fusce vulputate lorem sed lacus pellentesque egestas adipiscing ipsum fringilla. Proin scelerisque elementum dui, eu scelerisque dolor rhoncus non. Sed justo velit, sollicitudin ac adipiscing sit amet, iaculis a tortor.", FLASH_TEMPLETE_DASHBOARD, array('class' => FLASH_CLASS_INFO, 'title' => "Mensagem pro cara que veio do google"), FLASH_TEMPLETE_DASHBOARD);
            $this->redirect($this->Auth->redirect());
        } else {
            $this->Session->setFlash("Não foi possível logar no sistema com suas credenciais, tente mais tarde.", FLASH_TEMPLETE, array('class' => FLASH_CLASS_ERROR), FLASH_SESSION_LOGIN);
        }		
	}


}