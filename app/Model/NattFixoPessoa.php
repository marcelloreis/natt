<?php
App::uses('AppModel', 'Model');
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
class NattFixoPessoa extends AppModel {
	public $useTable = false;
	public $useDbConfig = 'natt';
	public $primaryKey = 'CPF_CNPJ';
	public $displayField = 'NOME_RAZAO';
	public $order = 'NattFixoPessoa.CPF_CNPJ';

	public $hasMany = array(
        'NattFixoTelefone' => array(
            'className' => 'NattFixoTelefone',
            'foreignKey' => 'CPF_CNPJ',
            'type' => 'inner'
        )
    );

    public function next(){
    	$pessoa = $this->find('first', array(
    		'recursive' => '-1',
    		'conditions' => array(
    			'CPF_CNPJ !=' => '00000000000000000000',
    			'transf' => null
    			),
    		'order' => array('CPF_CNPJ' => 'ASC')
    		));
    	$map['pessoa'] = $pessoa['NattFixoPessoa'];

    	$telefone = $this->NattFixoTelefone->find('default_all', array(
    		'recursive' => '-1',
    		'conditions' => array('CPF_CNPJ' => $pessoa['NattFixoPessoa']['CPF_CNPJ'])
    		));

    	if(count($telefone)){
	    	foreach ($telefone as $k => $v) {
		    	$endereco = $this->NattFixoTelefone->NattFixoEndereco->find('first', array(
		    		'recursive' => '-1',
		    		'conditions' => array('COD_END' => $v['NattFixoTelefone']['COD_END'])
		    		));
	    		$map['telefone'][$k] = $v['NattFixoTelefone'];
	    		$map['telefone'][$k]['endereco'] = $endereco['NattFixoEndereco'];
			}
    	}

    	$this->offset($map['pessoa']['CPF_CNPJ']);

		return $map;    	
    }

    public function offset($doc){
    	$this->updateAll(
    		array('NattFixoPessoa.transf' => true),
    		array('NattFixoPessoa.CPF_CNPJ' => $doc)
    	);
    }

	/**
	* Método find
	* Consultas o banco de dados e retorna um array de resultados.
	*
	* @override Metodo Model.find
	* @param string $type tipod de operacoes (all / first / count / neighbors / list / threaded)
	* @param array $query parametros de busca (conditions / fields / joins / limit / offset / order / page / group / callbacks)
	* @return array Array de dados, ou Null em caso de falha.
	*/
	function find($type=null, $params=array()) {
		if(!strstr($type, 'default_')){
			$params['conditions']['AND']['NattFixoPessoa.CPF_CNPJ !='] = '00000000000000';
		}
		$type = str_replace('default_', '', $type);

		//@override
		return parent::find($type, $params);
	}	    
	
}
