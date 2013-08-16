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
		"EntityLandlineAddress"
		);

	public $components = array('Import');

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

	public function pb(){

$number = 4758352;
// French notation
$nombre_format_francais = number_format($number, 0, '', '.');
// 1 234,56
echo $nombre_format_francais;


		die('Marcelo');
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
			$this->NattFixoTelefone->useTable = $this->telefones_uf;
			$this->NattFixoPessoa->useTable = $this->pessoa_uf;
			$this->NattFixoEndereco->useTable = $this->endereco_uf;

			/**
			* Calcula o total de registros que sera importado
			*/
			$qt_reg = $this->NattFixoPessoa->find('count');

			/**
			* Adiciona a flag 'transf' nas tabelas que serao importadas
			*/
			// $this->Import->__log("Adicionando a flag [transf] na tabela {$telefones_uf}", $this->uf);
			// $this->Import->query("ALTER TABLE `NATT`.`{$telefones_uf}` ADD COLUMN `transf` TINYINT(1) NULL");

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

// $i=0;
			do{


				/**
				* Carrega o proximo registro das tabelas de pessoa, telefone e endereco q ainda nao foram importado
				*/
				$entity = $this->NattFixoPessoa->next();

				if(count($entity)){
					/**
					* Inicialiaza a transacao
					*/
					$this->db['entity']->begin();

					/**
					* Gera o hash do nome da entidade
					*/
					$hash = $this->Import->getHash($this->Import->clearName($entity['pessoa']['NOME_RAZAO']));

					/**
					* Trata os dados da entidade para a importacao
					*/
					//Carrega o tipo de documento
					$doc_type = $this->Import->getTypeDoc($entity['pessoa']['CPF_CNPJ'], $this->Import->clearName($entity['pessoa']['NOME_RAZAO']));
					$data = array(
						'Entity' => array(
							'doc' => $entity['pessoa']['CPF_CNPJ'],
							'name' => $this->Import->clearName($entity['pessoa']['NOME_RAZAO']),
							'mother' => $this->Import->clearName($entity['pessoa']['MAE']),
							'type' => $doc_type,
							'gender' => $this->Import->getGender($entity['pessoa']['SEXO'], $doc_type, $entity['pessoa']['NOME_RAZAO']),
							'birthday' => $this->Import->getBirthday($entity['pessoa']['DT_NASCIMENTO']),
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
							'h_mother' => $this->Import->getHash($entity['pessoa']['MAE'], 'h_all'),
							)
						);

					/**
					* Executa a importacao da tabela Entity
					* e carrega o id da entidade importada
					*/
					$this->importEntity($data);
// debug($data);

					/**
					* Exibe o status da importacao no console 
					*/
					$this->Import->__flush();
					$qt_imported++;
					$this->Import->progressBar($qt_imported, $qt_reg);

					/**
					* Inicializa a importacao dos telefones da entidade encontrada
					*/
					foreach ($entity['telefone'] as $k => $v) {
						/**
						* Inicializa a transacao
						*/
						$this->db['entity']->begin();
						$this->db['landline']->begin();
						$this->db['address']->begin();
						$this->db['zipcode']->begin();
						$this->db['entityLandlineAddress']->begin();

						/**
						* Desmembra o DDD do Telefone
						*/
						$ddd_telefone = $v['TELEFONE'];
						$ddd = $this->Import->getDDD($v['TELEFONE']);
						$telefone = $this->Import->getTelefone($v['TELEFONE']);
					
						/**
						* Extrai o ano de atualizacao do telefone
						*/
						$year = $this->Import->getUpdated($v['DATA_ATUALIZACAO']);

						/**
						* Trata os dados o telefone para a importacao
						*/
						$data = array(
							'Landline' => array(
								'year' => $year,
								'ddd' => $ddd,
								'tel' => $telefone,
								'tel_full' => "{$ddd}{$telefone}",
								'tel_original' => $v['TELEFONE'],
								)
							);
						
						/**
						* Executa a importacao do telefone
						* e carrega o id do telefone importado
						*/
						$this->importLandline($data, $v['TELEFONE']);
// debug($data);
						/**
						* Inicializa a importacao do CEP do telefone encontrado
						* Trata os dados do CEP para a importacao
						*/						
						$data = array(
							'Zipcode' => array(
								'code' => $this->Import->getZipcode($v['endereco']['CEP']),
								'code_original' => $v['endereco']['CEP']
								)
							);
						/**
						* Executa a importacao do CEP
						* e carrega o id do CEP importado
						*/
						$this->importZipcode($data);
// debug($data);
						/**
						* Inicializa a importacao do endereco do telefone encontrado
						* Trata os dados do endereço para a importacao
						*/	
						$state_id = $this->Import->getState($v['endereco']['UF'], $this->uf);

						/**
						* Trata o nome da rua
						*/
						$street = $this->Import->getStreet($v['endereco']['NOME_RUA']);

						/**
						* Gera o hash do nome da rua
						*/
						$hash = $this->Import->getHash($street);

						$data = array(
							'Address' => array(
								'state_id' => $state_id,
								'zipcode_id' => $this->Zipcode->id,
								'city_id' => $this->Import->getCityId($v['endereco']['CIDADE'], $state_id, $this->Zipcode->id),
								'city' => $this->Import->getCity($v['endereco']['CIDADE']),
								'type_address' => $this->Import->getTypeAddress($v['endereco']['RUA'], $v['endereco']['NOME_RUA']),
								'street' => $street,
								'number' => $this->Import->getStreetNumber($v['NUMERO'], $v['endereco']['NOME_RUA']),
								'neighborhood' => $this->Import->getNeighborhood($v['endereco']['BAIRRO']),
								'complement' => $this->Import->getComplement($v['COMPLEMENTO']),
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

						/**
						* Executa a importacao do Endereço
						* e carrega o id do Endereço importado
						*/
						$this->importAddress($data);

						/**
						* Amarra os registros Entidade, Telefone, CEP e Endereço na tabela entities_landlines_addresses
						*/

						/**
						* Carrega todos os id coletados ate o momento
						*/
						$data = array(
							'EntityLandlineAddress' => array(
								'entity_id' => $this->Entity->id,
								'landline_id' => $this->Landline->id,
								'address_id' => $this->Address->id,
								'year' => $year,
								)
							);
						if($this->importEntityLandlineAddress($data)){
							$this->db['entity']->commit();
							$this->db['landline']->commit();
							$this->db['address']->commit();
							$this->db['zipcode']->commit();
							$this->db['entityLandlineAddress']->commit();							
						}else{
							$this->db['entity']->rollback();
							$this->db['landline']->rollback();
							$this->db['address']->rollback();
							$this->db['zipcode']->rollback();
							$this->db['entityLandlineAddress']->rollback();							
						}

// $v['endereco']['COMPLEMENTO'] = $v['COMPLEMENTO'];
// $v['endereco']['NUMERO'] = $v['NUMERO'];
// debug($data);
					}

					/**
					* Finaliza todas as transacoes
					*/
					$this->db['entity']->commit();					
				}

// $i++;
// if($i > 3){
// 	die;
// }

			}while($entity && count($entity));
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
		$hasEntity = $this->Entity->find('first', array(
			'recursive' => '-1',
			'conditions' => array('doc' => $entity['Entity']['doc'])
			));				

		if(count($hasEntity)){
			$this->Entity->id = $hasEntity['Entity']['id'];
		}else{
			$this->Entity->create($entity);
			if($this->Entity->save()){
				$this->Import->__log("Entidade importada com sucesso", $this->uf, true, $this->Entity->useTable, $this->Entity->id, $entity['Entity']['doc']);
			}else{
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
					$this->Import->__log("Telefone importado com sucesso.", $this->uf, true, $this->Landline->useTable, $this->Landline->id, $landline['Landline']['tel_full']);
				}else{
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
					$this->Import->__log("CEP importado com sucesso.", $this->uf, true, $this->Zipcode->useTable, $this->Zipcode->id, $zipcode['Zipcode']['code_original']);
				}else{
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
				'state_id' => $address['Address']['state_id'],
				'city_id' => $address['Address']['city_id'],
				'zipcode_id' => $address['Address']['zipcode_id'],
				'number' => $address['Address']['number'],
				// 'number NOT' => null,
				)
			));		


		if(count($hasAddress)){
			$this->Address->id = $hasAddress['Address']['id'];
		}else{
			$this->Address->create($address);
			if($this->Address->save()){
				$this->Import->__log("Endereço importado com sucesso.", $this->uf, true, $this->Address->useTable, $this->Address->id);
			}else{
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
			if(!$hasCreated){
				$this->Import->__log("Falha ao importar os dados da tabela entities_landlines_addresses", $this->uf, false, $this->EntityLandlineAddress->useTable, $this->Entity->id);
			}
		}	

		return $hasCreated;
	}

}
