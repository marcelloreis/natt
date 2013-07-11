<?php
App::uses('AppController', 'Controller');
/**
 * Students Controller
 *
 * O controller 'Students' é responsável por gerenciar 
 * toda a lógica do model 'Student' e renderizar o seu retorno na interface da aplicação.
 *
 * PHP 5
 *
 * @copyright     Copyright 2013-2013, Nasza Produtora
 * @link          http://www.nasza.com.br/ Nasza(tm) Project
 * @package       app.Controller
 *
 * @property Student $Student
 */
class StudentsController extends AppController {

/**
	* Método index
	* Este método contem regras de negocios que permitem visualizar todos os registros contidos na entidade do controlador
	*
	* @override Metodo AppController.index
	* @param string $period (Periodo das movimentacoes q serao listadas)
	* @return void
	*/
	public function index($params=array()){
		//@override
		parent::index($params);
	}		

	/**
	* Método edit
	* Este método contem regras de negocios que permitem adicionar e editar registros na base de dados
	*
	* @override Metodo AppController.edit
	* @param string $id
	* @return void
	*/
	public function edit($id=null){
		//@override
		parent::edit($id);

		$sex = array(FEMALE => __d('app', 'Female'), MALE => __d('app', 'Male'));
		$shirt_size = array(BABY_PEQUENA => __d('app', 'Baby Pequena'), BABY_MEDIA => __d('app', 'Baby Média'), BABY_GRANDE => __d('app', 'Baby Grande'), PEQUENA => __d('app', 'Pequena'), MEDIA => __d('app', 'Média'), GRANDE => __d('app', 'Grande'), EXTRA_GRANDE => __d('app', 'Extra Grande'));
		$study_level = array(SUPERIOR_COMPLETO => __d('app', 'Superior completo'), SUPERIOR_INCOMPLETO => __d('app', 'Superior incompleto'), MEDIO_COMPLETO => __d('app', 'Medio completo'), MEDIO_INCOMPLETO => __d('app', 'Medio incompleto'));
		$this->set(compact('sex', 'shirt_size', 'study_level'));
	}

	public function import_students(){
		ini_set('max_execution_time', 10000);
		ini_set('memory_limit', '128M');		
		$this->loadModel('pStudent');
		$this->loadModel('Event');
		$participantes = $this->pStudent->find('default_all');
		foreach ($participantes as $k => $v) {
			$v['pStudent']['doc'] = preg_replace('/[^0-9]/si', '', $v['pStudent']['doc']);
			$v['pStudent']['telephone'] = preg_replace('/[^0-9]/si', '', $v['pStudent']['telephone']);
			$v['pStudent']['zipcode'] = preg_replace('/[^0-9]/si', '', $v['pStudent']['zipcode']);
			$v['pStudent']['course_period'] = preg_replace('/[^0-9]/si', '', $v['pStudent']['course_period']);
			$v['pStudent']['sex'] = ($v['pStudent']['sex'] == 'F')?FEMALE:MALE;
			$v['pStudent']['study_level'] = ((int)substr($v['pStudent']['course_end'], 2, 4) > 2013)?SUPERIOR_INCOMPLETO:SUPERIOR_COMPLETO;
			
			switch ($v['pStudent']['shirt_size']) {
				case 'BP':
					$v['pStudent']['shirt_size'] = BABY_PEQUENA;
					break;
				case 'BM':
					$v['pStudent']['shirt_size'] = BABY_MEDIA;
					break;
				case 'BG':
					$v['pStudent']['shirt_size'] = BABY_GRANDE;
					break;
				case 'P':
					$v['pStudent']['shirt_size'] = PEQUENA;
					break;
				case 'M':
					$v['pStudent']['shirt_size'] = MEDIA;
					break;
				case 'G':
					$v['pStudent']['shirt_size'] = GRANDE;
					break;
				case 'GG':
					$v['pStudent']['shirt_size'] = EXTRA_GRANDE;
					break;
			}

			$hasCreated = $this->Student->findByDoc($v['pStudent']['doc']);
			if(!count($hasCreated)){
				$this->Student->create();
				if($this->Student->save(array('Student' => $v['pStudent']))){
					$event = $this->Event->find('first', array('conditions' => array("date_format(Event.date_ini, '%Y')" => $v['pStudent']['edition'])));
					$data['Inscription']['student_id'] = $this->Student->id;
					$data['Inscription']['event_id'] = $event['Event']['id'];
					$v['pStudent']['is_paid'] = ($v['pStudent']['indPago'] == 'S');
					$data['Inscription']['print_invoice'] = $v['pStudent']['datInscricao'];

					$this->Student->Inscription->create();
					$this->Student->Inscription->save($data);
				}
			}
		}
	}
}
