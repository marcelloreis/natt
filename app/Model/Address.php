<?php
App::uses('AppModel', 'Model');
/**
 * Address Model
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
 * Address Model
 *
 * @property Country $Country
 * @property City $City
 */
class Address extends AppModel {
	public $useTable = 'import_addresses';
}
