<?php
App::uses('AppModel', 'Model');
/**
 * NattFixoEndereco Model
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
 * NattFixoEndereco Model
 *
 * @property Country $Country
 * @property City $City
 */
class NattFixoEndereco extends AppModel {
	public $useTable = false;
	public $useDbConfig = 'natt';
	public $primaryKey = 'COD_END';

    public $belongsTo = array(
        'NattFixoPessoa' => array(
            'className' => 'NattFixoTelefone',
            'foreignKey' => 'CPF_CNPJ'
        )
    );	
}
