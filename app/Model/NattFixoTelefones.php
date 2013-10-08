<?php
App::uses('AppModelClean', 'Model');
/**
 * NattFixoTelefones Model
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
 * NattFixoTelefones Model
 *
 * @property Country $Country
 * @property City $City
 */
class NattFixoTelefones extends AppModelClean {
	public $useTable = false;
	public $useDbConfig = 'natt';
	public $primaryKey = 'CPF_CNPJ';
	public $displayField = 'NOME_RAZAO';
	public $order = 'NattFixoTelefones.CPF_CNPJ';

    public function addTelToPessoa(&$pessoa, $uf, $doc, $limit, $year_updated='1900'){
        $telefones = array();

        if(isset($uf) && is_array($uf)){
            /**
            * Percorre por todos os estados que a pessoa esta cadastrada
            */
            foreach ($uf as $k => $v) {
                //Carrega a tabela do estado da vez
                $this->setSource("TELEFONES_{$v}");

                //Busca todos os telefones da pessoa no estado selecionado
                $map = $this->find('all', array(
                    'conditions' => array('NattFixoTelefones.CPF_CNPJ' => $doc),
                    ));

                //Agrupa os telefones encontrado por data de atualizacao e sequencia
                foreach ($map as $k2 => $v2) {
                    $v2['NattFixoTelefones']['UF'] = $v;
                    $map2["{$v2['NattFixoTelefones']['DATA_ATUALIZACAO']}{$v2['NattFixoTelefones']['SEQ']}"] = $v2;
                }
            }

            //Ordena os telefones encontrados pela data de atualizacao e sequencia
            krsort($map2);
            
            //Percorre por todos os telefones agora ordenados e agrupados
            foreach ($map2 as $k => $v) {
                //Verifica se o telefone ja existe no array
                if(!array_key_exists($v['NattFixoTelefones']['TELEFONE'], $telefones)){
                    //Carrega o ano de atualizacao do telefone
                    $year = substr($v['NattFixoTelefones']['DATA_ATUALIZACAO'], 0, 4);
                    //Verifica se o ano é maior do que o limite passado pelo parametro
                    if($year >= $year_updated){
                        $telefones[$v['NattFixoTelefones']['TELEFONE']] = $v;
                    }
                }
            }

            /**
            * Limita a quantidade de telefones do array de acordo com o limite passado pelo parametro
            */
            array_splice($telefones, $limit);

            /**
            * Mescla os telefones á pessoa passada por parametro
            */
            foreach ($telefones as $k => $v) {
                $pessoa['NattFixoTelefones'][$k] = $v['NattFixoTelefones'];
            }

            if(!isset($pessoa['NattFixoTelefones'])){
                $pessoa['NattFixoTelefones'] = false;
            }            
        }
    }

    public function addTelVizinhosToPessoa(&$pessoa, $limit){
        $vizinhos = array();

        if(isset($pessoa['NattFixoPessoa']['CPF_CNPJ']) && isset($pessoa['NattFixoTelefones']) && is_array($pessoa['NattFixoTelefones'])){
            /**
            * Percorre por todos os estados que a pessoa esta cadastrada
            */
            foreach ($pessoa['NattFixoTelefones'] as $k => $v) {
                /**
                * Nao busca os vizinhos caso o CEP seja generico
                */
                if(substr($v['CEP'], -3) != '000'){
                    //Carrega a tabela do estado da vez
                    $this->setSource("TELEFONES_{$v['UF']}");

                    //Busca todos os vizinhos da pessoa no estado
                    $map = $this->find('all', array(
                        'conditions' => array(
                            'NattFixoTelefones.CEP' => $v['CEP'],
                            'NattFixoTelefones.CPF_CNPJ NOT' => $pessoa['NattFixoPessoa']['CPF_CNPJ'],
                            'NattFixoTelefones.CPF_CNPJ NOT' => $v['TELEFONE'],
                            ),
                        'order' => array('NattFixoTelefones.DATA_ATUALIZACAO' => 'DESC')
                        ));

                    //Remove os telefones repetidos encontrados
                    foreach ($map as $k2 => $v2) {
                        $v2['NattFixoTelefones']['UF'] = $v['UF'];
                        $map2["{$v2['NattFixoTelefones']['TELEFONE']}"] = $v2['NattFixoTelefones'];
                    }

                    /**
                    * Limita a quantidade de telefones do array de acordo com o limite passado pelo parametro
                    */
                    array_splice($map2, $limit);
                    $pessoa['NattFixoTelefones'][$k]['NattFixoTelefonesVizinhos'] = $map2;

                    unset($map2);
                }
            }
        }
    }
}
