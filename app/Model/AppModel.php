<?php
/**
 * Application model for Cake.
 *
 * This file is application-wide model file. You can put all
 * application-wide model-related methods here.
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Model
 * @since         CakePHP(tm) v 0.2.9
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

App::uses('Model', 'Model');


/**
 * Application model for Cake.
 *
 * Add your application-wide methods in the class below, your models
 * will inherit them.
 *
 * @package       app.Model
 */
class AppModel extends Model {
	/**
	* Método remove
	*
	* Este método altera para true o campo trashed|deleted do(s) registro contido no model
	*
	* @return boolean $updated
	*/
	public function remove($action, $cascade=true, $value=true, $_this=false){
		/**
		* Converte para NULL caso o parametro $value seja false
		*/
		$value = !$value?null:$value;


		$_this = $_this?$_this:$this;

		/**
		* Move o registro contido no model para a lixeira
		*/
		$updated = $_this->updateAll(
					array("{$_this->alias}.{$action}" => $value),
					array("{$_this->alias}.{$_this->primaryKey}" => $_this->id)
					);

		/**
		* Remove todos os registros associados para a lixeira
		*/
		if($updated && $cascade && count($_this->hasMany)){
			foreach ($_this->hasMany as $k => $v) {
				/**
				* Atualiza somente os models associados que estiverem com o campo 'dependent' setado como true
				* ou seja, PARA QUE OS REGISTROS ASSOCIADOS SEJAM ATUALIZADOS, SERA NECESSARIO ALTERAR O CAMPO
				* 'dependent' NO MODEL DO REGISTRO PRINCIPAL, NO ATRIBUTO $hasMany
				*/
				if($v['dependent'] === true){
					/**
					* Move o registro contido no model relacionado para a lixeira
					*/
					$hasMany = clone $_this->$k;

					$hasMany->id = $hasMany->find('default_list', array('fields' => array('id'), 'conditions' => array("{$hasMany->alias}.{$v['foreignKey']}" => $_this->id)));
					if(isset($hasMany->hasMany)){
						$updated = $this->remove($action, $cascade, $value, $hasMany);
					}

					/**
					* Retona false caso um dos registros nao tenha sido removido com sucesso
					*/
					if(!$updated){
						return false;
					}
				}
			}
		}

		return 1;
		// return $updated;
	}

	/**
	* O gatilho beforeSave prepara os dados recebidos do formulario de insercao/edicao
	* e os prepara para serem inseridos corretamente no banco de dados
	* 
	* Ex.:
	* A data que trazida do banco de dados no formato "yyyy-mm-dd [hh:ii:ss]" sera formatada para "dd/mm/yyyy [hh:ii:ss]"
	*
	* @param Array $results (contém as informações retornadas da busca.)
	* @param Boolean $primary
	* @return Array
	*/
	public function beforeSave($options=array()) {
		/**
		 * Percorre todos os campos da tabela/modelo preparando os dados de acordo com os seus tipos para serem inseridos no banco de dados
		 */
		foreach ($this->getColumnTypes() as $k => $v) {
			/**
			 * Carrega a variavel $value com o valor preenchido no campo do formulário frontend
			 */
			$value = isset($this->data[$this->name][$k])?$this->data[$this->name][$k]:null;
			/**
			 * Verifica se o campo nao esta vazio ou se é um campo booleano
			 */
			if(!empty($value) || $v == 'boolean'){
				/**
				 * Trata os valores dos campos de acordo com o seu tipo
				 */
				switch ($v) {
					case 'date':
					case 'datetime':
						/**
						 * Quebra a data para remontar no formato para inserção do banco de dados "yyyy-mm-dd [hh:ii:ss]"
						 */
						if(preg_match('%(0[1-9]|[12][0-9]|3[01])[\./-]?(0[1-9]|1[012])[\./-]?([12][0-9]{3})([ ].*)?([01][0-9]|2[03]:[05][09])?%si', $value, $dt)){
							$value = "{$dt[3]}-{$dt[2]}-{$dt[1]}";
							if (isset($dt[4])){
							}

							/**
							* Verifica se o campo faz parte de um intervao de datas
							*/
							if (preg_match('/^date_ini|data_inicio|ini|inicio|date_end|data_fim|end|fim$/si', $k)){
								/**
								* Concatena a data e a hora de inicio
								*/		
								if(isset($this->data[$this->name]["{$k}_time"]) && !empty($this->data[$this->name]["{$k}_time"])){
									$value .= " " . $this->data[$this->name]["{$k}_time"];
									unset($this->data[$this->name]["{$k}_time"]);
								}
							}else if(isset($dt[4])){
								/**
								 * Verifica se a data contem hh:ii:ss, caso tenha é concatenado a data
								 */
								$value .= ' ' . $dt[4];

							}						
						}
						break;

						break;
					
					case 'smallint':
					case 'integer':
					case 'biginteger':
						/**
						 * Retira todos os caracteres do campo que nao seja numerico
						 */
						$value = preg_replace('/[^0-9]/si', '', $value);
						break;
						
					case 'float':
						/**
						 * Formata o valor com a pontuacao padrao do banco
						 */
						if(strstr($value, ',')){
							$value = str_replace(',', '.', str_replace('.', '', $value));
						}
						break;
						
					case 'boolean':
						/**
						 * Insere o valor 0[ZERO] quando o campo for false
						 */
						if(!$value){
							switch ($k) {
								case 'trashed':
								case 'deleted':
									$value = null;
									break;
								
								default:
									$value = '0';
									break;
							}
						}
						break;
				}

				/**
				 * Trata os valores dos campos de acordo com o seu nome
				 */
				switch ($k) {
					case 'senha':
					case 'password':
					case 'pass':
						$value = AuthComponent::password($value);
						break;
					
					default:
						//code...
						break;
				}

				/**
				 * Devolve o valor tratado ao $this->data
				 */
				$this->data[$this->name][$k] = $value;
			}
		}
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
			if ($this->hasField(ACTION_TRASH)) {
				if (!isset($params['conditions']["{$this->alias}." . ACTION_TRASH])) {
					// $params['conditions']['AND']['OR'][]["{$this->alias}." . ACTION_TRASH] = array(null, '0');
					$params['conditions']["{$this->alias}." . ACTION_TRASH] = null;
				}
			}

			if ($this->hasField(ACTION_DELETE)) {
				if (!isset($params['conditions']["{$this->alias}." . ACTION_DELETE])) {
					// $params['conditions']['AND']['OR'][]["{$this->alias}." . ACTION_DELETE] = array(null, '0');
					$params['conditions']["{$this->alias}." . ACTION_DELETE] = null;
				}
			}
		}
		$type = str_replace('default_', '', $type);

		//@override
		return parent::find($type, $params);
	}	

	/**
	* Método afterFind
	* O gatilho afterFund modifica os resultados de qualquer operação de busca realizada,
	* preparando os dados para serem exibidos corretamente no frontend da aplicacao
	* 
	* Esta função 
	* Ex.:
	* A data que trazida do banco de dados no formato "yyyy-mm-dd [hh:ii:ss]" sera formatada para "dd/mm/yyyy [hh:ii:ss]"
	*
	* @param array $results (contém as informações retornadas da busca.)
	* @param boolean $primary
	* @return array
	*/
	public function afterFind($results, $primary=false){
		foreach ($results as $modelClass => $data) {
			/**
			 * Prepara os dados para serem exibidos corretamente no frontend da aplicacao
			 */
			if(is_array($data)){
				/**
				 * Percorre todos os campos da tabela/modelo preparando os dados de acordo com os seus tipos para serem inseridos no banco de dados
				 */
				foreach ($this->getColumnTypes() as $k => $v) {
					/**
					 * Carrega a variavel $value com o valor preenchido no campo do formulário frontend
					 */
					$value = isset($data[key($data)][$k])?$data[key($data)][$k]:null;

					/**
					 * Verifica se o campo nao esta vazio
					 */
					if(!empty($value)){
						/**
						 * Trata os valores dos campos de acordo com o seu tipo
						 */
						switch ($v) {
							/**
							 * Formata a data trazida do banco para o formato "dd/mm/YYYY"
							 */
							case 'date':
								if($value != '0000-00-00'){
									$value = date('d/m/Y', strtotime($value));
								}
								break;
							/**
							 * Formata a data trazida do banco para o formato "dd/mm/YYYY hh:ii:ss"
							 */
							case 'datetime':
								/**
								* Verifica se o campo faz parte de um intervao de datas
								*/
								if (
									preg_match('/^date_ini|data_inicio|ini|inicio|date_end|data_fim|end|fim$/si', $k) &&
									isset($data[$this->name]["{$k}_time"]) &&
									!empty($data[$this->name]["{$k}_time"])
									){
									/**
									* Concatena a data e a hora de inicio
									*/		
									$value = date('d/m/Y', strtotime($value));
								}else{

									/**
									* Formatacao padrao para o tipo datetime
									*/
									$value = date('d/m/Y H:i:s', strtotime($value));
								}
								break;
								/**
								 * Formata o valor float trazido do banco para o formato padrao real brasileiro
								 */
							case 'float':
								$value = number_format($value, 2, ',', '.');
								break;
						}

						/**
						 * Trata os valores dos campos de acordo com o seu nome
						 */
						switch ($k) {
							case 'senha':
							case 'password':
							case 'pass':
								$value = '';
								break;
							
							default:
								//code...
								break;
						}
					}

					/**
					 * Devolve o valor tratado ao $data
					 */
					$data[key($data)][$k] = $value;
				}

				/**
				* Recarrega os resultados já formatados
				*/
				$results[$modelClass] = $data;
			}
		}

		return $results;
	}


	/**
	* Método getFieldText
	* Retorna o nome do primeiro campo do tipo string da tabela/model
	*
	* @return array
	*/
	public function getFieldText() {
		$field = 'name';
		//Verifica se o nome do campo de texto foi setado no model
		if(isset($this->displayField) && !empty($this->displayField)){
			$field = $this->displayField;
		}else{
			//Percorre por todas as colunas do Model(tabela)
			foreach ($this->getColumnTypes() as $k => $v) {
				//Retorna o nome do primeiro campo do tipo string|text|char para setar como principal campo de texto que sera usado para as buscas
				switch ($v) {
					case 'string':
					case 'text':
					case 'char':
						return $k;
					break;
				}
			}
		}

		//Caso nao encontre nenhum campo do tipo string, sera retornado o nome padrao "name"
		return $field;
	}
}
