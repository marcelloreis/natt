<?php
App::uses('AppModelClean', 'Model');
/**
 * NattObito Model
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
 * NattObito Model
 *
 * @property Country $Country
 * @property City $City
 */
class NattObito extends AppModelClean {
	public $useTable = 'DADOS';
	public $useDbConfig = 'obito';
	public $primaryKey = 'ID';
	public $displayField = 'NOME';

    public function obito(&$pessoa, $doc){
        if(!$doc){
            $pessoa['NattObito'] = false;
        }else{
            /**
            * Busca as participacoes societarias da pessoa
            */
            $obito = $this->find('first', array(
                'conditions' => array('NattObito.CPF14' => $doc)
                ));

            $pessoa['NattObito'] = count($obito)?$obito['NattObito']:false;
        }
    }
}
