<?php
App::uses('AppModelImport', 'Model');
/**
 * NattFixoTelefone Model
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
 * NattFixoTelefone Model
 *
 * @property Country $Country
 * @property City $City
 */
class NattFixoTelefone extends AppModelImport {
	/**
	* O atributo $useTable sera carregado em tempo de execucao poi varia por estado
	*/
	public $useTable = false;
	public $useDbConfig = 'natt';
	public $primaryKey = 'TELEFONE';

    public $belongsTo = array(
        'NattFixoPessoa' => array(
            'className' => 'NattFixoPessoa',
            'foreignKey' => 'CPF_CNPJ'
        )
    );

	public $hasMany = array(
        'NattFixoEndereco' => array(
            'className' => 'NattFixoEndereco',
            'foreignKey' => 'COD_END',
            'type' => 'inner'
        )
    );
}
