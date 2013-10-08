<?php
App::uses('AppModelClean', 'Model');
/**
 * NattMovelTelefones Model
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
 * NattMovelTelefones Model
 *
 * @property Country $Country
 * @property City $City
 */
class NattMovelTelefones extends AppModelClean {
	public $useTable = false;
	public $useDbConfig = 'cel2010';
	public $primaryKey = 'CPF_CNPJ';
	public $displayField = 'NOME';

    public function addCelToPessoa(&$pessoa, $uf, $doc, $limit, $year_updated='1900'){
        $telefones = array();

        if(isset($uf) && is_array($uf)){
            /**
            * Percorre por todos os estados que a pessoa esta cadastrada
            */
            foreach ($uf as $k => $v) {
                //Carrega a tabela do estado da vez
                $this->setSource("{$v}");

                //Busca todos os telefones da pessoa no estado selecionado
                $map = $this->find('all', array(
                    'conditions' => array('NattMovelTelefones.CPF_CNPJ' => $doc),
                    ));

                //Agrupa os telefones encontrado por data de atualizacao e telefone
                foreach ($map as $k2 => $v2) {
                    $v2['NattMovelTelefones']['UF'] = $v;
                    $map2["{$v2['NattMovelTelefones']['ATUALIZACAO_SISTEMA']}{$v2['NattMovelTelefones']['TELEFONE']}"] = $v2;
                }
            }
            //Ordena os telefones encontrados pela data de atualizacao e sequencia
            krsort($map2);

            /**
            * Limita a quantidade de telefones do array de acordo com o limite passado pelo parametro
            */
            array_splice($map2, $limit);

            //Percorre por todos os telefones agora ordenados e agrupados
            foreach ($map2 as $k => $v) {
                //Verifica se o telefone ja existe no array
                if(!array_key_exists($v['NattMovelTelefones']['TELEFONE'], $telefones)){
                    //Carrega o ano de atualizacao do telefone
                    $year = substr($v['NattMovelTelefones']['ATUALIZACAO_SISTEMA'], 0, 4);
                    //Verifica se o ano é maior do que o limite passado pelo parametro
                    if($year >= $year_updated){
                        $telefones[$v['NattMovelTelefones']['TELEFONE']] = $v;
                    }
                }
            }


            /**
            * Mescla os telefones á pessoa passada por parametro
            */
            foreach ($telefones as $k => $v) {
                $pessoa['NattMovelTelefones'][$k] = $v['NattMovelTelefones'];
            }

            if(!isset($pessoa['NattMovelTelefones'])){
                $pessoa['NattMovelTelefones'] = false;
            }                
        }
    }
}
