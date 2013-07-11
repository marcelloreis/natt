<?php
/**
 * Static content controller.
 *
 * Este arquivo ira renderizar as visões contidas em views/Users/
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
 * Este controlador contem regras de negócio aplicadas ao model User
 *
 * @package       app.Controller
 * @link http://.framework.nasza.com.br/2.0/controller/Users.html
 */
class UsersController extends AppController {

	public function dashboard(){
        if(!strstr($this->referer(), '/users/login')){
            $this->Session->setFlash("Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec aliquam justo sit amet odio aliquam semper. Phasellus eget lobortis nisi. Vivamus in nulla ut justo convallis tincidunt. Etiam rutrum suscipit dolor, vitae facilisis eros tincidunt gravida. Fusce vulputate lorem sed lacus pellentesque egestas adipiscing ipsum fringilla. Proin scelerisque elementum dui, eu scelerisque dolor rhoncus non. Sed justo velit, sollicitudin ac adipiscing sit amet, iaculis a tortor.", FLASH_TEMPLETE_DASHBOARD, array('class' => FLASH_CLASS_INFO, 'title' => "Big info dialog"), FLASH_TEMPLETE_DASHBOARD);
        }
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
        /**
         * Verifica se o formulário foi submetido por post
         */
        if ($this->request->is('post') || $this->request->is('put')) {
            /**
            * Caso o campo password esteja seta, porem vazio, ele sera removido do request->data para q nao seja atualizado
            */
            if(isset($this->request->data['User']['password']) && empty($this->request->data['User']['password'])){
                unset($this->request->data['User']['password']);
            }
        }

        //@override
        parent::edit($id);
    }    


    public function login() {
        /**
        * Deleta a sessao que guarda o codigo da rede social q o usuario escolheu para usar como login
        */
        $this->Session->delete('User.Social.api');

        /**
        * Verifica qual tipo de login foi escolhido pelo usuário
        */
        $api = isset($this->params['named']['api'])?$this->params['named']['api']:null;
        $api = isset($this->request->data['User']['api'])?$this->request->data['User']['api']:$api;
        if(isset($api)){
            switch ($api) {
                case 'system':
                    $this->saveCredentials();
                    break;
                case 'google':
                    $this->Session->write('User.Social.api', GOOGLE_GROUP);
                    $this->redirect($this->AppGoogle->getAuthUrl());
                    break;
                case 'facebook':
                    $this->Session->write('User.Social.api', FACEBOOK_GROUP);
                    $this->redirect($this->AppFacebook->getAuthUrl());
                    break;
            }
        }


        $this->layout = 'login';
        if ($this->request->is('post')) {
            if ($this->Auth->login()) {
                $this->Session->setFlash("Seja bem vindo ao " . TITLE_APP . ".", FLASH_TEMPLETE_DASHBOARD, array('class' => FLASH_CLASS_SUCCESS, 'title' => "Olá " . $this->Auth->User('name')), FLASH_TEMPLETE_DASHBOARD);
                parent::__loadPermissionsOnSessions();
                $this->redirect($this->Auth->redirect());
            } else {
                $this->Session->setFlash("Seu email ou senha estao incorretos.", FLASH_TEMPLETE, array('class' => FLASH_CLASS_ERROR), FLASH_SESSION_LOGIN);
            }
        }
    }

    /**
    * Método saveCredentials
    *
    * Este método é responsavel por salvar os dados contidos no 
    * formulário de cadastro disponivel na tela de login
    */
    private function saveCredentials(){
        $data = $this->request->data['User'];
        /**
        * Verifica se o usuario ja esta cadastrado no sistema
        */
        $userSystemAlrealyAdd = $this->User->findByEmail($data['email']);

        if($userSystemAlrealyAdd){
            //Carrega o ID do usuario do sistema entrado para que os dados sejam atualizado ao invez de inseridos
            $data['id'] = $userSystemAlrealyAdd['User']['id'];
            $this->Session->setFlash("Você já estava cadastrado no " . TITLE_APP . " desde " . substr($userSystemAlrealyAdd['User']['created'], 0, 10) . ", atualizamos a sua senha com a que acabou de cadastrar. Seja bem vindo [de novo].", FLASH_TEMPLETE_DASHBOARD, array('class' => FLASH_CLASS_ALERT, 'title' => "Olá " . $userSystemAlrealyAdd['User']['given_name']), FLASH_TEMPLETE_DASHBOARD);
        }else{
            $data['given_name'] = ucfirst(substr($data['name'], 0, strpos($data['name'], ' ')));
            $this->Session->setFlash("Seja bem vindo ao " . TITLE_APP . ".", FLASH_TEMPLETE_DASHBOARD, array('class' => FLASH_CLASS_SUCCESS, 'title' => "Olá " . $data['given_name']), FLASH_TEMPLETE_DASHBOARD);
        }

        /**
        * Insere/Atualiza o usuario na tabela SOCIALS
        */
        $data['status'] = STATUS_ACTIVE;
        $data['password'] = AuthComponent::password($data['password']);
        $this->User->create($data);
        if(!$this->User->save()){
            /**
             * Carrega os erros encontrados ao tentar salvar o formulário
             */
            $this->User->set($this->request->data);
            $errors = $this->User->invalidFields();
            $msgs = array();
            foreach ($errors as $k => $v) {
                if(isset($v[0])){
                    $msgs[$k] = $v[0];
                }
            }

            //Redireciona o usuario para a pagina de login novamente caso o cadastro nao seja bem sucedido
            $this->Session->setFlash(FLASH_SAVE_ERROR, FLASH_TEMPLETE, array('class' => FLASH_CLASS_ERROR, 'multiple' => $msgs), FLASH_SESSION_LOGIN);
            $this->redirect($this->Auth->logout());
        }

        $user = $this->User->read();
        $login = $user['User'];
        $login['Group'] = $user['Group'];
        $login['Social'] = isset($user['Social'])?$user['Social']:false;

        /**
        * Efetua o login do usuario sistema
        */
        if ($this->Auth->login($login)) {
            //Carrega todas as permissoes do usuario/grupo em sessao
            parent::__loadPermissionsOnSessions();
            //Redireciona o usuario para a pagina inicial do sistema
            $this->redirect($this->Auth->redirect());
        } else {
            $this->Session->setFlash("Não foi possível logar no sistema com suas credenciais, tente mais tarde.", FLASH_TEMPLETE, array('class' => FLASH_CLASS_ERROR), FLASH_SESSION_LOGIN);
        }        

        debug($login);die;
    }    

    /**
     * Exclui todas as sessoes do usuario logado e o redireciona para a tela de login
     */
    public function logout() {
        $this->Session->setFlash("Sessão Encerrada.", FLASH_TEMPLETE, array('class' => FLASH_CLASS_SUCCESS), FLASH_SESSION_LOGIN);
        $this->Session->delete('Auth');
        $this->Session->delete('User');
        $this->redirect($this->Auth->logout());
    }
}
