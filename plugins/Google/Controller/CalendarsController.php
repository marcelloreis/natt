<?php
App::uses('Controller', 'Controller');

/**
 * Application level Controller
 *
 * Este arquivo contem todas as necessarias para manipulacao dos calendarios 
 * contidos na conta google associada ao usuario do sistema
 *
 * @package       app.Controller.Controller
 */
class CalendarsController extends AppController {

	/**
	* Carrega os models que serao usados no controller
	*/
	public $uses = array('User', 'Social');

	/**
	* Método change
	* O ID do calendario ativo esta armazenado em cookies e 
	* este método é responsavel por alterar o ID do calendario
	* de acordo com o ID passado por parametro
	*
	* @param string $id
	* @return void
	*/
	public function change($id){
		/**
		* Atualiza o calendario padrao escolhido pelo usuario
		*/
		$data['Social']['id'] = $this->Session->read('User.Social.id');
		$data['Social']['calendar'] = $id;
		$this->Social->create($data);
		$this->Social->save($data);

		if($id == 'disable'){
			$this->Session->delete('User.Calendar.default');
		}else{
			$this->Session->write('User.Calendar.default', str_replace('.', GOOGLE_PONTO, $id));
		}

		$this->redirect($this->referer());
	}

}