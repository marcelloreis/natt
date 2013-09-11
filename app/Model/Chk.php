<?php
App::uses('AppModel', 'Model');
/**
 * Chk Model
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
 * Chk Model
 */
class Chk extends AppModel {
	public $useTable = 'chk';

	public $validate = array(
		'chk' => array(
		    'rule'    => array('extension', array('txt')),
		    'message' => 'Please supply a valid document (.txt).'
		)
	);	
}
