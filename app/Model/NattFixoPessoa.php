<?php
App::uses('AppModelClean', 'Model');
/**
 * NattFixoPessoa Model
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
 * NattFixoPessoa Model
 *
 * @property Country $Country
 * @property City $City
 */
class NattFixoPessoa extends AppModelClean {
	public $useTable = false;
	public $useDbConfig = 'natt';
	public $primaryKey = 'CPF_CNPJ';
	public $displayField = 'NOME_RAZAO';
	public $order = 'NattFixoPessoa.CPF_CNPJ';

    public function next($indice, $size, $uf){
    	$map = $this->find('all', array(
            'recursive' => '-1',
            'fields' => array(
                'NattFixoPessoa.CPF_CNPJ',
                'NattFixoPessoa.NOME_RAZAO',
                'NattFixoPessoa.MAE',
                'NattFixoPessoa.SEXO',
                'NattFixoPessoa.DT_NASCIMENTO',
                'NattFixoTelefone.TELEFONE',
                'NattFixoTelefone.CEP',
                'NattFixoTelefone.COMPLEMENTO',
                'NattFixoTelefone.NUMERO',
                'NattFixoTelefone.DATA_ATUALIZACAO',
                'NattFixoEndereco.RUA',
                'NattFixoEndereco.NOME_RUA',
                'NattFixoEndereco.BAIRRO',
                'NattFixoEndereco.CIDADE',
                'NattFixoEndereco.UF',
                'NattFixoEndereco.CEP',
                ),
            'joins' => array(
                array(
                    'table' => "TELEFONES_{$uf}",
                    'alias' => 'NattFixoTelefone',
                    'type' => 'inner',
                    'conditions' => array(
                        'NattFixoPessoa.CPF_CNPJ = NattFixoTelefone.CPF_CNPJ',
                                )
                    ),
                array(
                    'table' => "ENDERECO_{$uf}",
                    'alias' => 'NattFixoEndereco',
                    'type' => 'inner',
                    'conditions' => array(
                        'NattFixoTelefone.COD_END = NattFixoEndereco.COD_END',
                                )
                    )
                ),
            'order' => array(
                'NattFixoPessoa.CPF_CNPJ',
                ),
    		'limit' => "{$indice},{$size}",
    		));

		return $map;    	
    }
}
