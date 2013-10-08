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
	public $virtualFields = array(
	    'filename' => 'CONCAT("/webroot/checkinlist/", Chk.client_id, "/", Chk.id, "/", Chk.id)',
	    'filename_source' => 'CONCAT("/webroot/checkinlist/", Chk.client_id, "/", Chk.id, "/source")',
	    'filename_excel' => 'CONCAT("/webroot/checkinlist/", Chk.client_id, "/", Chk.id, "/resultado.xls")',
	);	

	public $validate = array(
		'filename' => array(
		    'rule'    => array('extension', array('txt')),
		    'message' => 'Please supply a valid document (.txt).'
		),
	    'client_id' => array(
	        'rule'    => 'notEmpty',
	        'message' => 'This field cannot be left blank'
	    )		
	);	
}
