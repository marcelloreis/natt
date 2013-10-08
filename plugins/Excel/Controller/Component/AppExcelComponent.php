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
require_once PATH_APP . '/plugins/Excel/lib/Classes/PHPExcel.php';


/**
 * Application Component
 *
 * O componente "AppExcel" é responsavel pela conexao entre esta framework e o webservice do google
 */
class AppExcelComponent extends Component {

	/**
	* Declaracao dos atributos da classe
	*/
	public $obj;


	/**
	* Método startup
	*
	* O método startup é chamado depois do método beforeFilter do controle, 
	* mas antes do controller executar a action corrente.
	*
	* Aqui serao carregados os servicos da biblioteca PHPExcel
	*
	* @override Metodo app.Controller.Component.startup
	* @param Controller $controller
	* @return void
	*/
	public function startup($controller){
		/**
		* Carrega o objeto PHPExcel
		*/
		$this->obj = new PHPExcel();

		//@override
		parent::startup($controller);		
	}

	public function save($filename){
		$objWriter = PHPExcel_IOFactory::createWriter($this->obj, 'Excel5');
		$objWriter->save($filename);
	}


}