<?php
App::uses('AppModel', 'Model');
/**
 * Settings Model
 *
 * Esta classe é responsável ​​pela gestão de quase tudo o que acontece a respeito do(a) Estado, 
 * é responsável também pela validação dos seus dados.
 *
 * PHP 5
 *
 * @copyright     Copyright 2013-2013, Nasza Produtora
 * @link          http://www.nasza.com.br/ Nasza(tm) Project
 * @package       app.Model
 *
 * Settings Model
 *
 * @property Country $Country
 * @property City $City
 */
class Settings extends AppModel {
	public $useTable = '_settings';


	public function active($module){
		$isActive = $this->find('count', array(
			'conditions' => array('module' => $module, 'actived' => '1')
			));

		if(!$isActive){
			$content = "\n\n\n\n";
			$content .= "###################################################################\n";
			$content .= "Time: " . date('Y/m/d H:i:s') . "\n";
			$content .= "===================================================================\n";
			$content .= "Importação pausada.\n";
			$content .= "===================================================================\n";

			echo $content;			
		}
	}
}
