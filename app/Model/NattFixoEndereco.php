<?php
App::uses('AppModelClean', 'Model');
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
class NattFixoEndereco extends AppModelClean {
	public $useTable = false;
	public $useDbConfig = 'natt';
	public $primaryKey = 'COD_END';
	public $displayField = 'NOME_RUA';

    public function addEndToTelefones(&$pessoa){
        if(isset($pessoa['NattFixoTelefones']) && is_array($pessoa['NattFixoTelefones'])){
            foreach ($pessoa['NattFixoTelefones'] as $k => $v) {
                $this->setSource("ENDERECO_{$v['UF']}");

                $map = $this->find('first', array(
                    'conditions' => array('NattFixoEndereco.COD_END' => $v['COD_END']),
                    ));
                if(isset($map['NattFixoEndereco'])){
                    $pessoa['NattFixoTelefones'][$k]['NattFixoEndereco'] = $map['NattFixoEndereco'];
                }
            }
        }
    }
}
