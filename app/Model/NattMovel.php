<?php
App::uses('AppModelClean', 'Model');
/**
 * NattMovel Model
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
 * NattMovel Model
 *
 * @property Country $Country
 * @property City $City
 */
class NattMovel extends AppModelClean {
	public $useTable = 'TELEFONE';
	public $useDbConfig = 'cel2010';
	public $primaryKey = 'TELEFONE';
	public $displayField = 'ESTADO';

    public function getUfFromDoc($doc){
        //Inicializa a variavel $uf com false
        $uf = false;
        
        if($doc){
            /**
            * Busca o estado do doc
            */
            $map = $this->find('all', array(
                'fields' => array('CPF_CNPJ', 'ESTADO'),
                'conditions' => array('NattMovel.CPF_CNPJ' => $doc)
                ));
            /**
            * Agrupa os estados encontrados
            */
            foreach ($map as $k => $v) {
                $uf[$v['NattMovel']['ESTADO']] = $v['NattMovel']['ESTADO'];
            }
        }

        return $uf;        
    }
}
