<?php
App::uses('AppModelClean', 'Model');
/**
 * NattEmpresas Model
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
 * NattEmpresas Model
 *
 * @property Country $Country
 * @property City $City
 */
class NattEmpresas extends AppModelClean {
	public $useTable = false;
	public $useDbConfig = 'emp2012';
	public $primaryKey = 'ID';
	public $displayField = 'NOME_SOCIO';

    public function addParticipacoes(&$pessoa, $doc, $limit){

        if(!$doc){
            $pessoa['NattSociedade'] = false;
        }else{
            /**
            * Busca as participacoes societarias da pessoa
            */
            $this->setSource('SOCIOS');
            $sociedades = $this->find('all', array(
                'conditions' => array('NattEmpresas.DOCUMENTO_SOCIO' => $doc)
                ));

            /**
            * Agrupa as participacoes por empresas
            */
            $this->setSource('EMPRESAS');
            foreach ($sociedades as $k => $v) {
                $empresa = $this->find('first', array(
                    'conditions' => array('NattEmpresas.CNPJ' => $v['NattEmpresas']['CNPJ'])
                    ));
                $empresa['NattEmpresas']['sociedade'] = $v['NattEmpresas'];
                $pessoa['NattSociedade'][$v['NattEmpresas']['CNPJ']] = $empresa['NattEmpresas'];
            }

            /**
            * Limita a quantidade de telefones do array de acordo com o limite passado pelo parametro
            */
            if(isset($pessoa['NattSociedade']) && is_array($pessoa['NattSociedade'])){
                array_splice($pessoa['NattSociedade'], $limit);
            }else{
                $pessoa['NattSociedade'] = false;
            }
        }
    }
}
