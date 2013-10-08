<?php
/**
 * Import content controller.
 *
 * Este arquivo ira renderizar as visões contidas em views/States/
 *
 * PHP 5
 *
 * @copyright     Copyright 2013-2013, Nasza Produtora
 * @link          http://www.nasza.com.br/ Nasza(tm) Project
 * @package       app.Controller
 */

App::uses('AppController', 'Controller');

/**
 * Import content controller
 *
 * Este controlador contem regras de negócio aplicadas ao model State
 *
 * @package       app.Controller
 * @link http://.framework.nasza.com.br/2.0/controller/States.html
 */
class ImportController extends AppController {
	/**
	* Controller name
	*
	* @var string
	*/
	public $name = 'Import';

	public $uses = array(
		"Import", 
		"NattFixoTelefone", 
		"NattFixoPessoa", 
		"NattFixoEndereco",
		"Landline",
		"Entity",
		"Zipcode",
		"Address",
		"EntityLandlineAddress",
		"Settings"
		);

	public $components = array('Import', 'Main.AppUtils');

	/**
	* Atributos da classe
	*/
	private $db;
	private $uf;
	private $telefones_uf;
	private $pessoa_uf;
	private $endereco_uf;
	private $qt_reg = 0;
	private $qt_imported = 0;

	/**
	* Método beforeFilter
	* Esta função é executada antes de todas ações do controlador. 
	* E no caso da framework, esta sendo usado para checar uma sessão ativa e inspecionar permissões.
	*
	* @override Metodo Controller.beforeFilter
	* @return void
	*/
	public function beforeFilter() {
		//@override
	    parent::beforeFilter();

		//Verifica se a acao foi chamada apartir da linha de comando
		if (!defined('CRON_DISPATCHER')) { 
			// $this->Session->setFlash("{$user['given_name']}, " . __('This page can not be executed on browser'), FLASH_TEMPLETE, array('class' => FLASH_CLASS_ALERT), FLASH_SESSION_FORM);
			// $this->redirect($this->Auth->loginRedirect); 
			// exit(); 
		} 

	    $this->layout=null;
	}	

	/**
	* Método natt_fixo_2_landline
	* Este método importa os telefones Fixos no modelo da base de dados do Natt para o Sistema
	*
	* @return void
	*/
	public function natt_fixo_2_landline($uf=null){
		/**
		* Verifica se foi passado algum estado por parametro
		*/
		if($uf){
			$this->uf = strtoupper($uf);
			
			/**
			* Carrega as tabelas do estado que sera importado
			*/
			$this->telefones_uf = "TELEFONES_{$this->uf}";
			$this->pessoa_uf = "PESSOA_{$this->uf}";
			$this->endereco_uf = "ENDERECO_{$this->uf}";

			/**
			* Carrega os models com o nome das tabelas
			*/
			// $this->NattFixoTelefone->useTable = $this->telefones_uf;
			$this->NattFixoPessoa->useTable = $this->pessoa_uf;
			// $this->NattFixoEndereco->useTable = $this->endereco_uf;

			/**
			* Calcula o total de registros que sera importado
			*/
			$this->qt_reg = $this->NattFixoPessoa->find('count', array('conditions' => array('NattFixoPessoa.CPF_CNPJ >' => '5716558747')));

			/**
			* Inicia o processo de importacao
			*/
			$this->Import->__log("Iniciando a importacao do Estado [$this->uf]", $this->uf);

			/**
			* Inicializa a transacao das tabelas
			*/
			$this->db['entity'] = $this->Entity->getDataSource();
			$this->db['landline'] = $this->Landline->getDataSource();
			$this->db['address'] = $this->Landline->getDataSource();
			$this->db['zipcode'] = $this->Landline->getDataSource();
			$this->db['entityLandlineAddress'] = $this->EntityLandlineAddress->getDataSource();

			/**
			* Carrega os limites iniciais da importacao
			*/
			$indice = 0;
			$this->Import->sizeReload = 10000;

			do{
				/**
				* Registra o recarregamento dos dados no log
				*/
				$this->Import->reloadCount();

				/**
				* Carrega o proximo registro das tabelas de pessoa, telefone e endereco q ainda nao foram importado
				*/
				$this->Import->timing_ini(3, 'Carrega o proximo registro das tabelas de pessoa, telefone e endereco q ainda nao foram importado');
				$entities = $this->NattFixoPessoa->next($indice, $this->Import->sizeReload, $this->uf);
				$this->Import->timing_end();

				/**
				* Calcula o intervalo do proximo select q trara os dados para serem importados
				*/
				$indice+=$this->Import->sizeReload;

				foreach ($entities as $k => $v) {
					/**
					* Verifica se a chave do modulo de importacao esta ativa
					*/
					$this->Import->timing_ini(2, 'Verifica se a chave do modulo de importacao esta ativa');
					$this->Settings->active($this->action);
					$this->Import->timing_end();

debug($v['NattFixoPessoa']['DT_NASCIMENTO']);
debug($this->Import->getBirthday($v['NattFixoPessoa']['DT_NASCIMENTO']));
die;
					/**
					* Verifica se nenhuma das sequências invalidas abaixo 
					* foi digitada. Caso afirmativo, retorna falso
					*/
					if ($v['NattFixoPessoa']['CPF_CNPJ'] == '00000000000' || $v['NattFixoPessoa']['CPF_CNPJ'] == '00000000000000') {
					    continue;
					 }

					/**
					* Exibe o status da importacao no console 
					*/
					$this->Import->__flush();
					$this->qt_imported++;
					$this->Import->progressBar($this->qt_imported, $this->qt_reg, $this->uf);

					/**
					* Inicialiaza a transacao
					*/
					$this->db['entity']->begin();

					/**
					* Gera o hash do nome da entidade
					*/
					$hash = $this->Import->getHash($this->Import->clearName($v['NattFixoPessoa']['NOME_RAZAO']));

					/**
					* Trata os dados da entidade para a importacao
					*/
					//Carrega o tipo de documento
					$doc_type = $this->Import->getTypeDoc($v['NattFixoPessoa']['CPF_CNPJ'], $this->Import->clearName($v['NattFixoPessoa']['NOME_RAZAO'], $v['NattFixoPessoa']['MAE']));
					$this->Import->timing_ini(4, 'Trata os dados da entidade para a importacao');
					$data = array(
						'Entity' => array(
							'doc' => $v['NattFixoPessoa']['CPF_CNPJ'],
							'name' => $this->Import->clearName($v['NattFixoPessoa']['NOME_RAZAO']),
							'mother' => $this->Import->clearName($v['NattFixoPessoa']['MAE']),
							'type' => $doc_type,
							'gender' => $this->Import->getGender($v['NattFixoPessoa']['SEXO'], $doc_type, $v['NattFixoPessoa']['NOME_RAZAO']),
							'birthday' => $this->Import->getBirthday($v['NattFixoPessoa']['DT_NASCIMENTO']),
							'h1' => $hash['h1'],
							'h2' => $hash['h2'],
							'h3' => $hash['h3'],
							'h4' => $hash['h4'],
							'h5' => $hash['h5'],
							'h_all' => $hash['h_all'],
							'h_first_last' => $hash['h_first_last'],
							'h_last' => $hash['h_last'],
							'h_first1_first2' => $hash['h_first1_first2'],
							'h_last1_last2' => $hash['h_last1_last2'],
							'h_mother' => $this->Import->getHash($v['NattFixoPessoa']['MAE'], 'h_all'),
							)
						);
					$this->Import->timing_end();

					/**
					* Executa a importacao da tabela Entity
					* e carrega o id da entidade importada
					*/
					$this->Import->timing_ini(5, 'Executa a importacao da tabela Entity');
					$this->importEntity($data);
					$this->Import->timing_end();


					/**
					* Inicializa a importacao dos telefones da entidade encontrada
					*/

					/**
					* Desmembra o DDD do Telefone
					*/
					$ddd_telefone = $v['NattFixoTelefone']['TELEFONE'];
					$ddd = $this->Import->getDDD($v['NattFixoTelefone']['TELEFONE']);
					$telefone = $this->Import->getTelefone($v['NattFixoTelefone']['TELEFONE']);
					$tel_full = "{$ddd}{$telefone}";

					/**
					* Extrai o ano de atualizacao do telefone
					*/
					$year = $this->Import->getUpdated($this->AppUtils->dt2br($v['NattFixoTelefone']['DATA_ATUALIZACAO']));					
					 
					$this->Import->timing_ini(6, 'Trata os dados o telefone para a importacao');

					/**
					* Inicializa a transacao
					*/
					$this->db['landline']->begin();

					/**
					* Trata os dados o telefone para a importacao
					*/
					$data = array(
						'Landline' => array(
							'year' => $year,
							'ddd' => $ddd,
							'tel' => $telefone,
							'tel_full' => "{$ddd}{$telefone}",
							'tel_original' => $v['NattFixoTelefone']['TELEFONE'],
							)
						);
					$this->Import->timing_end();
					
					/**
					* Executa a importacao do telefone
					* e carrega o id do telefone importado
					*/
					$this->Import->timing_ini(7, 'Executa a importacao do telefone');
					$this->importLandline($data, $v['NattFixoTelefone']['TELEFONE']);
					$this->Import->timing_end();

					/**
					* Inicializa a transacao
					*/
					$this->db['zipcode']->begin();

					/**
					* Inicializa a importacao do CEP do telefone encontrado
					* Trata os dados do CEP para a importacao
					*/				
					$this->Import->timing_ini(8, 'Trata os dados do CEP para a importacao');		
					$data = array(
						'Zipcode' => array(
							'code' => $this->Import->getZipcode($v['NattFixoEndereco']['CEP']),
							'code_original' => $v['NattFixoEndereco']['CEP']
							)
						);
					$this->Import->timing_end();

					/**
					* Executa a importacao do CEP
					* e carrega o id do CEP importado
					*/
					$this->Import->timing_ini(9, 'Executa a importacao do CEP');
					$this->importZipcode($data);
					$this->Import->timing_end();

					/**
					* Inicializa a transacao
					*/
					$this->db['address']->begin();
				
					/**
					* Inicializa a importacao do endereco do telefone encontrado
					* Trata os dados do endereço para a importacao
					*/	
					$this->Import->timing_ini(10, 'Trata os dados do endereço para a importacao');
					$state_id = $this->Import->getState($v['NattFixoEndereco']['UF'], $this->uf);

					/**
					* Trata o nome da rua
					*/
					$street = $this->Import->getStreet($v['NattFixoEndereco']['NOME_RUA']);

					/**
					* Gera o hash do nome da rua
					*/
					$hash = $this->Import->getHash($street);

					$data = array(
						'Address' => array(
							'state_id' => $state_id,
							'zipcode_id' => $this->Zipcode->id,
							'city_id' => $this->Import->getCityId($v['NattFixoEndereco']['CIDADE'], $state_id, $this->Zipcode->id),
							'city' => $this->Import->getCity($v['NattFixoEndereco']['CIDADE']),
							'type_address' => $this->Import->getTypeAddress($v['NattFixoEndereco']['RUA'], $v['NattFixoEndereco']['NOME_RUA']),
							'street' => $street,
							'number' => $this->Import->getStreetNumber($v['NattFixoTelefone']['NUMERO'], $v['NattFixoEndereco']['NOME_RUA']),
							'neighborhood' => $this->Import->getNeighborhood($v['NattFixoEndereco']['BAIRRO']),
							'complement' => $this->Import->getComplement($v['NattFixoTelefone']['COMPLEMENTO'], $v['NattFixoEndereco']['NOME_RUA']),
							'h1' => $hash['h1'],
							'h2' => $hash['h2'],
							'h3' => $hash['h3'],
							'h4' => $hash['h4'],
							'h5' => $hash['h5'],
							'h_all' => $hash['h_all'],
							'h_first_last' => $hash['h_first_last'],
							'h_last' => $hash['h_last'],
							'h_first1_first2' => $hash['h_first1_first2'],
							'h_last1_last2' => $hash['h_last1_last2'],
							)
						);
					$this->Import->timing_end();

					/**
					* Executa a importacao do Endereço
					* e carrega o id do Endereço importado
					*/
					$this->Import->timing_end(11, 'Executa a importacao do Endereço');
					$this->importAddress($data);
					$this->Import->timing_end();

					/**
					* Inicializa a transacao
					*/
					$this->db['entityLandlineAddress']->begin();

					/**
					* Amarra os registros Entidade, Telefone, CEP e Endereço na tabela entities_landlines_addresses
					*/

					/**
					* Carrega todos os id coletados ate o momento
					*/
					$this->Import->timing_ini(12, 'Carrega todos os id coletados ate o momento');
					$data = array(
						'EntityLandlineAddress' => array(
							'entity_id' => $this->Entity->id,
							'landline_id' => $this->Landline->id,
							'address_id' => $this->Address->id,
							'year' => $year,
							)
						);
					$this->Import->timing_end();
					
					$this->Import->timing_ini(13, 'Comita todas as transacoes realizadas');
					if($this->importEntityLandlineAddress($data)){
						$this->db['entity']->commit();
						$this->db['landline']->commit();
						$this->db['zipcode']->commit();
						$this->db['address']->commit();
						$this->db['entityLandlineAddress']->commit();
					}else{
						$this->db['entity']->rollback();
						$this->db['landline']->rollback();
						$this->db['zipcode']->rollback();
						$this->db['address']->rollback();
						$this->db['entityLandlineAddress']->rollback();
					}
					$this->Import->timing_end();

					/**
					* Salva as contabilizacoes na base de dados
					*/					
					$this->Import->__counter('entities');
					$this->Import->__counter('landlines');
					$this->Import->__counter('zipcodes');
					$this->Import->__counter('addresses');
					$this->Import->__counter('entities_landlines_addresses');	

				}
			}while($entities && count($entities));
		}
	}	

	/**
	* Método importEntity
	* Este método importa os dados da entidade
	*
	* @return void
	*/
	private function importEntity($entity){
		/**
		* Verifica se a entidade que sera importada já existe na base de dados
		*/
		$hasEntity = array();
		if ($entity['Entity']['doc'] != '00000000000' && $entity['Entity']['doc'] != '00000000000000') {
			$hasEntity = $this->Entity->find('first', array(
				'recursive' => '-1',
				'conditions' => array('doc' => $entity['Entity']['doc'])
				));				
		 }


		if(count($hasEntity)){
			$this->Entity->id = $hasEntity['Entity']['id'];
		}else{
			$this->Entity->create($entity);
			if($this->Entity->save()){
				$this->Import->success('entities');
				// $this->Import->__log("Entidade importada com sucesso", $this->uf, true, $this->Entity->useTable, $this->Entity->id, $entity['Entity']['doc']);
			}else{
				$this->Import->fail('entities');
				$this->Import->__log("Falha ao importar a entidade", $this->uf, false, $this->Entity->useTable, null, $entity['Entity']['doc'], $this->db['entity']->error);
			}
		}	
	}

	/**
	* Método importLandline
	* Este método importa os telefones relacionados a entidade
	*
	* @return void
	*/
	private function importLandline($landline){
		/**
		* Aborta a insercao caso o telefone seja null (inconsistente)
		*/		
		if(!$landline['Landline']['tel']){
			$this->Import->fail('landlines');
			$this->Import->__log("Telefone inconsistente", $this->uf, false, $this->Landline->useTable, null, $landline['Landline']['tel_original']);
		}else{
			/**
			* Verifica se o telefone que sera importado já existe na base de dados
			*/
			$hasLandline = $this->Landline->find('first', array(
				'recursive' => '-1',
				'conditions' => array(
					'tel_full' => $landline['Landline']['tel_full'],
					)
				));		

			if(count($hasLandline)){
				$this->Landline->id = $hasLandline['Landline']['id'];
			}else{
				$this->Landline->create($landline);
				if($this->Landline->save()){
					$this->Import->success('landlines');
					// $this->Import->__log("Telefone importado com sucesso.", $this->uf, true, $this->Landline->useTable, $this->Landline->id, $landline['Landline']['tel_full']);
				}else{
					$this->Import->fail('landlines');
					$this->Import->__log("Falha ao importar o telefone.", $this->uf, false, $this->Landline->useTable, null, $landline['Landline']['tel_full'], $this->db['Landline']->error);
				}
			}	
		}
	}

	/**
	* Método importZipcode
	* Este método importa CEP relacionados ao telefone
	*
	* @return void
	*/
	private function importZipcode($zipcode){
		/**
		* Aborta a insercao caso o CEP seja null (inconsistente)
		*/		
		if(!$zipcode['Zipcode']['code']){
			$this->Import->fail('zipcodes');
			$this->Import->__log("CEP inconsistente ou null", $this->uf, false, $this->Zipcode->useTable, null, $zipcode['Zipcode']['code_original']);
		}else{
			/**
			* Verifica se o telefone que sera importado já existe na base de dados
			*/
			$hasZipcode = $this->Zipcode->find('first', array(
				'recursive' => '-1',
				'conditions' => array(
					'code' => $zipcode['Zipcode']['code'],
					)
				));		

			if(count($hasZipcode)){
				$this->Zipcode->id = $hasZipcode['Zipcode']['id'];
			}else{
				$this->Zipcode->create($zipcode);
				if($this->Zipcode->save()){
					$this->Import->success('zipcodes');
					// $this->Import->__log("CEP importado com sucesso.", $this->uf, true, $this->Zipcode->useTable, $this->Zipcode->id, $zipcode['Zipcode']['code_original']);
				}else{
					$this->Import->fail('zipcodes');
					$this->Import->__log("Falha ao importar o CEP.", $this->uf, false, $this->Zipcode->useTable, null, $zipcode['Zipcode']['code_original'], $this->db['Zipcode']->error);
				}
			}	
		}
	}

	/**
	* Método importAddress
	* Este método importa Endereço relacionados ao telefone
	*
	* @return void
	*/
	private function importAddress($address){
		/**
		* Verifica se o telefone que sera importado já existe na base de dados
		*/
		$hasAddress = $this->Address->find('first', array(
			'recursive' => '-1',
			'conditions' => array(
				'zipcode_id' => $address['Address']['zipcode_id'],
				'number' => $address['Address']['number'],
				'complement' => $address['Address']['complement'],
				// 'number NOT' => null,
				)
			));		


		if(count($hasAddress)){
			$this->Address->id = $hasAddress['Address']['id'];
		}else{
			$this->Address->create($address);
			if($this->Address->save()){
				$this->Import->success('addresses');
				// $this->Import->__log("Endereço importado com sucesso.", $this->uf, true, $this->Address->useTable, $this->Address->id);
			}else{
				$this->Import->fail('addresses');
				$this->Import->__log("Falha ao importar o endereço.", $this->uf, false, $this->Address->useTable, null, $address['Address']['state_id'], $this->db['Address']->error);
			}
		}	
	}

	/**
	* Método importEntityLandlineAddress
	* Amarra os registros Entidade, Telefone, CEP e Endereço na tabela entities_landlines_addresses
	*
	* @return bool $hasCreated
	*/
	private function importEntityLandlineAddress($entityLandlineAddress){
		/**
		* Inicializa a variavel $asCreated com false
		*/
		$hasCreated = false;

		if(
			(!empty($entityLandlineAddress['EntityLandlineAddress']['entity_id']) && $entityLandlineAddress['EntityLandlineAddress']['entity_id'] != '0')
			&& 
			(
				(!empty($entityLandlineAddress['EntityLandlineAddress']['landline_id']) && $entityLandlineAddress['EntityLandlineAddress']['landline_id'] != '0') 
				|| 
				(!empty($entityLandlineAddress['EntityLandlineAddress']['address_id']) && $entityLandlineAddress['EntityLandlineAddress']['address_id'] != '0'))
			){

			/**
			* Verifica se a junção já existe
			*/
			$hasEntityLandlineAddress = $this->EntityLandlineAddress->find('first', array(
				'recursive' => '-1',
				'conditions' => array(
					'entity_id' => $entityLandlineAddress['EntityLandlineAddress']['entity_id'],
					'landline_id' => $entityLandlineAddress['EntityLandlineAddress']['landline_id'],
					'address_id' => $entityLandlineAddress['EntityLandlineAddress']['address_id'],
					'year' => $entityLandlineAddress['EntityLandlineAddress']['year'],
					)
				));	

		}


		if(isset($hasEntityLandlineAddress) && count($hasEntityLandlineAddress)){
			$this->EntityLandlineAddress->id = $hasEntityLandlineAddress['EntityLandlineAddress']['id'];
		}else{
			$this->EntityLandlineAddress->create($entityLandlineAddress);
			$hasCreated = $this->EntityLandlineAddress->save(); 
			if($hasCreated){
				$this->Import->success('entities_landlines_addresses');
			}else{
				$this->Import->fail('entities_landlines_addresses');
				$this->Import->__log("Falha ao importar os dados da tabela entities_landlines_addresses", $this->uf, false, $this->EntityLandlineAddress->useTable, $this->Entity->id);
			}
		}	

		return $hasCreated;
	}

}
