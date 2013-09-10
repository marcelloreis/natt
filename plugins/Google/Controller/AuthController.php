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
	public $uses = array('User', 'Social');

	/**
	* Carrega os componentes que poderao ser usados em quaisquer controller desta framework
	*/
	public $components = array(
		'Session',
		'Google.AppGoogle',
		'Google.AppProfile',
		'Google.AppCalendar',
		);

	/**
	* Método authentication
	*
	* Este método carrega o link que direcionara o usuario do sistema para 
	* a pagina de login do google
	*/
	public function authentication(){
		//Verifica se a pagina de login do google retornou o codigo de homologacao
		if (isset($this->params->query['code']) && !empty($this->params->query['code'])) {
			//Carrega o codigo de homologacao fornecido pelo google
			$this->AppGoogle->service->authenticate($this->params->query['code']);

			/**
			* Salva/Atualiza as credenciais do usuario no banco de dados
			*/
			$this->saveCredentials($this->AppGoogle->service->getAccessToken());
		}else{
			//Redireciona o usuario para a pagina de login novamente caso a pagina do google nao retorne o codigo de homologacao
			$this->Session->setFlash("Não foi possível validar as credenciais da sua conta google.", FLASH_TEMPLETE, array('class' => FLASH_CLASS_ERROR), FLASH_SESSION_LOGIN);
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
	private function saveCredentials($token){

		/**
		* Carrega os dados basicos do usuario google
		*/
		$userGoogle = $this->AppProfile->get();

		/**
		* Verifica se o componente Profile retornou os dados do usuario
		*/
		if(!$userGoogle){
			//Redireciona o usuario para a pagina de login novamente caso haja erro no carregamento dos seus dados basicos
			$this->Session->setFlash("Não foi possível dados basicos da sua conta google.", FLASH_TEMPLETE, array('class' => FLASH_CLASS_ERROR), FLASH_SESSION_LOGIN);
			$this->redirect($this->Auth->logout());
		}else{
			/**
			* Verifica se o usuario já esta cadastrado na tabela SOCIALS
			*/
			$userAlrealyAdd = $this->Social->findById($userGoogle['id']);

			if($userAlrealyAdd){
				//Carrega o id do model com o id do usuario encontrado na conta google
				$userSystem['id'] = $userAlrealyAdd['Social']['user_id'];

				//Carrega o ID do usuario com o ID encontrado na base de dados do sistema
				$userGoogle['id'] = $userAlrealyAdd['Social']['id'];
				$userGoogle['calendar'] = $userAlrealyAdd['Social']['calendar'];
			}

			/**
			* Cadastra/Atualiza o usuario da conta google na tabela USERS
			*/
			$userSystem['group_id'] = GOOGLE_GROUP;
			$userSystem['name'] = $userGoogle['name'];
			$userSystem['password'] = AuthComponent::password(substr(preg_replace('/[^0-9]/', '', uniqid()), -6));
			$userSystem['email'] = $userGoogle['email'];

			/**
			* Carrega a imagem/avatar do usuario na rede social
			*/
			$picture = (isset($userGoogle['picture']))?$userGoogle['picture']:'';
			$userSystem['picture'] = $picture;

			/**
			* Carrega o status do usuario do SISTEMA de acordo com o status do usuario na REDE SOCIAL
			*/
			$userSystem['status'] = $userGoogle['verified_email'];

			/**
			* Verifica se o usuario ja esta cadastrado no sistema, caso ja esteja, sera mantida a senha antiga do usuario
			*/
			$userSystemAlrealyAdd = $this->User->findByEmail($userGoogle['email']);

			if($userSystemAlrealyAdd){
				//Mantem o usuario no grupo de administradores caso ele seja um adm
				$userSystem['group_id'] = ($userSystemAlrealyAdd['User']['group_id'] == ADMIN_GROUP)?ADMIN_GROUP:GOOGLE_GROUP;
				//Carrega o ID do usuario do sistema entrado para que os dados sejam atualizado ao invez de inseridos
				$userSystem['id'] = $userSystemAlrealyAdd['User']['id'];
				//Mantem a senha anterior criada pelo usuario
				unset($userSystem['password']);
			}
			

			$this->User->create($userSystem);
			$this->User->save($userSystem);
			$userGoogle['user_id'] = $this->User->id;

			/**
			* Carrega o token do usuario
			*/
			$userGoogle['token'] = $token;

			/**
			* Carrega o grupo do usuario
			*/
			$userGoogle['social_group'] = GOOGLE_GROUP;

			/**
			* Insere/Atualiza o usuario na tabela SOCIALS
			*/
			$data = array('Social' => $userGoogle);
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
			/**
			* Verifica se existe um calendario pre-definido como padrao pelo usuario
			*/
			$userGoogle['calendar'] = isset($userGoogle['calendar']) && !empty($userGoogle['calendar'])?$userGoogle['calendar']:$userGoogle['email'];

        	//Carrega todas os calendarios disponiveis do usuario
        	$this->AppCalendar->loadCalendars($userGoogle['calendar']);
        	//Carrega todas as permissoes do usuario/grupo em sessao
            parent::__loadPermissionsOnSessions();
        	//Carrega o token de permissao fornecido pelo google em sessao
        	$this->Session->write('User.Social.token', $token);
        	//Carrega o id do usuario google
        	$this->Session->write('User.Social.id', $userGoogle['id']);
            //Redireciona o usuario para a pagina inicial do sistema
        	$this->Session->setFlash("Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec aliquam justo sit amet odio aliquam semper. Phasellus eget lobortis nisi. Vivamus in nulla ut justo convallis tincidunt. Etiam rutrum suscipit dolor, vitae facilisis eros tincidunt gravida. Fusce vulputate lorem sed lacus pellentesque egestas adipiscing ipsum fringilla. Proin scelerisque elementum dui, eu scelerisque dolor rhoncus non. Sed justo velit, sollicitudin ac adipiscing sit amet, iaculis a tortor.", FLASH_TEMPLETE_DASHBOARD, array('class' => FLASH_CLASS_INFO, 'title' => "Mensagem pro cara que veio do google"), FLASH_TEMPLETE_DASHBOARD);
            $this->redirect($this->Auth->redirect());
        } else {
            $this->Session->setFlash("Não foi possível logar no sistema com suas credenciais, tente mais tarde.", FLASH_TEMPLETE, array('class' => FLASH_CLASS_ERROR), FLASH_SESSION_LOGIN);
        }		
	}


}