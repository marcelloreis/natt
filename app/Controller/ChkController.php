<?php
/**
 * Static content controller.
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
 * Static content controller
 *
 * Este controlador contem regras de negócio aplicadas ao model State
 *
 * @package       app.Controller
 * @link http://.framework.nasza.com.br/2.0/controller/States.html
 */
class ChkController extends AppController {

	/**
	* Atributos da classe
	*/
	private $uf;
	private $limit_reg;
	private $year_updated;
	private $pessoa;
	private $columns = array(
					'A%row%' => 'REG',
					'B%row%' => 'CPF/CNPJ',
					'C%row%' => 'TELEFONE FIXO',
					'D%row%' => 'TELEFONE MOVEL',
					'E%row%' => 'NOME/RAZAO',
					'F%row%' => 'ANIVERSARIO',
					'G%row%' => 'IDADE',
					'H%row%:O%row%' => 'ENDERECO',
					'P%row%' => 'VIZINHOS',
					'Q%row%:R%row%' => 'PART. SOCIETARIA',
						);
	private $counter = array(
	    'qt_processed' => 0,
	    'qt_landline' => 0,
	    'qt_inconsistent' => 0,
	    'qt_not_found' => 0,
	    'qt_mobile' => 0,
	    'qt_obito' => 0,
	    'qt_sociedade' => 0,
	    'qt_pessoas' => 0,
		);
	/**
	* Carrega os componentes que poderao ser usados em quaisquer controller desta framework
	*/
	public $components = array(
		'Excel.AppExcel',
		'Main.AppUtils',
		);

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
		if (defined('CRON_DISPATCHER')) { 
	    	$this->layout=null;
		}
	}		

	/**
	* Carrega todos os modelos que serao usados no controller
	*/
	public $uses = array(
		'Chk', 
		'Cliente', 
		'NattFixoPessoa', 
		'NattFixoTelefones', 
		'NattTelefone', 
		'NattFixoEndereco', 
		'NattMovel',
		'NattMovelTelefones',
		'NattEmpresas',
		'NattObito',
		);

	/**
	* Controller name
	*
	* @var string
	*/
	public $name = 'Chk';

	/**
	* Método index
	* Este método contem regras de negocios visualizar todos os registros contidos na entidade do controlador
	*
	* @override Metodo AppController.index
	* @param string $period (Periodo das movimentacoes q serao listadas)
	* @return void
	*/
	public function index($params=array()){

		if(isset($this->params['named']['processed']) && $this->params['named']['processed'] == '1'){
			$params['conditions'] = array('Chk.status' => '1');
		}

		//@override
		parent::index($params);

		/**
		* Carrega o nome do index
		*/
		$title_view = 'Files in queue';
		if(isset($this->params['named']['processed']) && $this->params['named']['processed'] == '1'){
			$title_view = 'Processed files';
		}

		/**
		* Carrega todos os clientes cadastrados no sistema
		*/
		$map = $this->Cliente->find('list', array('fields' => array('ID', 'NOME_RAZAO')));

		foreach ($map as $k => $v) {
			$clientes[ltrim($k, '0')] = $v;
		}
		
		$this->set(compact('title_view', 'clientes'));
	}	

	/**
	* Método edit
	* Este método contem regras de negocios para adicionar e editar registros na base de dados
	*
	* @override Metodo AppController.edit
	* @param string $id
	* @return void
	*/
	public function edit($id=null){

		if($id){
			$this->redirect(array('action' => 'index'));
		}

		/**
		 * Verifica se o formulário foi submetido por post
		 */
		if ($this->request->is('post') || $this->request->is('put')) {
			/**
			* Faz o upload do arquivo
			*/
			$this->request->data['Chk']['qt_processed'] = $this->upload_file($this->request->data);
		}

		//@override
		parent::edit($id);


		/**
		* Carrega todos os clientes cadastrados no sistema
		*/
		$clientes = $this->Cliente->find('list', array('fields' => array('ID', 'NOME_RAZAO')));
		$this->set(compact('clientes'));
	}

	private function getResultsType(){
		$results = array(
					'NattFixoPessoa' => false,
					'NattFixoTelefones' => false,
					'NattMovelTelefones' => false,
					'NattSociedade' => false,
					'NattObito' => false,
					'NattBacen' => false,
					);

		return $results;
	}

	public function process(){	
		/**
		* Carrega o checkinlist da fila
		*/
		$chk = $this->loadChk();

		/**
		* Define o prazo da data de atualizacao dos dadoa que serao trazidos
		*/
		$this->year_updated = (date('Y') - 10);

		/**
		* Define o limite de telefones que sera retornado
		*/
		$this->limit_reg = 3;


		/**
		* Verifica se retornou algum registro e inicializa o processo de montagem do checkinlist
		*/
		if($chk){
			$chk = $chk['Chk'];

			/**
			* Cria o arquivo EXCEL
			*/
			$this->AppExcel->obj->getProperties()->setCreator(TITLE_APP)
										 ->setLastModifiedBy(COPYRIGHT)
										 ->setTitle(TITLE_APP)
										 ->setSubject(TITLE_APP)
										 ->setCategory("Relatorio");		

			/**
			* Ativa a primeira planilha do excel
			*/									
			$this->AppExcel->obj->setActiveSheetIndex(0);	 

			/**
			* Cria o cabeçalho do arquivo excel
			*/									
			foreach ($this->columns as $k => $v) {
				$this->setValue(1, $k, $v);
			}

			/**
			* Inicializa o contador de linhas (Comeca no 2 pois o primeiro é o cabeçalho)
			*/
			$ln = 2;

			do{
				/**
				* Inicializa a variavel pessoa
				*/
				$this->pessoa = $this->getResultsType();

				$doc = $this->nextDoc($chk['filename_source']);

				/**
				* Contabiliza o registro processado
				*/
				if($doc){
					$this->counter['qt_processed']++;
				}

				/**
				* Carrega todos os dados do documento na tabela de telefones fixo
				*/
				$this->uf = $this->NattTelefone->getUfFromDoc($doc);
				/**
				* Verifica se o documento pesquisado foi encontrado em algum estado na base de dados de telefone fixo
				*/
				if($this->uf){
					/**
					* Carrega os dados da entidade portadora do documento
					*/
					$map = $this->NattFixoPessoa->getPessoa($this->uf, $doc);
					$this->pessoa = array_merge($this->pessoa, $map);

					/**
					* Carrega os telefones fixos pertencentes ao portador do documento
					*/
					$this->NattFixoTelefones->addTelToPessoa($this->pessoa, $this->uf, $doc, $this->limit_reg, $this->year_updated);

					/**
					* Carrega os endereços correspondentes aos telefones passados por parametro
					*/
					$this->NattFixoEndereco->addEndToTelefones($this->pessoa);

					/**
					* Carrega os telefones fixos pertencentes aos VIZINHOS do portador do documento
					*/
					$this->NattFixoTelefones->addTelVizinhosToPessoa($this->pessoa, $this->limit_reg);
				}

				/**
				* Carrega todos os dados do documento na tabela de telefones moveis
				*/
				$this->uf = $this->NattMovel->getUfFromDoc($doc);

				/**
				* Verifica se o documento pesquisado foi encontrado em algum estado na base de dados de telefone fixo
				*/
				if($this->uf){
					/**
					* Carrega os telefones moveis pertencentes ao portador do documento
					*/
					$this->NattMovelTelefones->addCelToPessoa($this->pessoa, $this->uf, $doc, $this->limit_reg, $this->year_updated);
				}

				/**
				* Carrega as participacoes societarias da pessoa portadora do documento
				*/
				$this->NattEmpresas->addParticipacoes($this->pessoa, $doc, $this->limit_reg);

				/**
				* Carrega as informacoes de obito da pessoa consultadda
				*/
				$this->NattObito->obito($this->pessoa, $doc);

				/**
				* Carrega dados do BACEN
				*/
				// $this->Bacen->restricao($this->pessoa);


				// 'A%row%' => 'REG',
				// 'B%row%' => 'CPF/CNPJ',
				// 'C%row%' => 'TELEFONE FIXO',
				// 'D%row%' => 'TELEFONE MOVEL',
				// 'E%row%' => 'NOME/RAZAO',
				// 'F%row%' => 'ANIVERSARIO',
				// 'G%row%' => 'IDADE',
				// 'H%row%:O%row%' => 'ENDERECO',
				// 'P%row%' => 'VIZINHOS',
				// 'Q%row%:R%row%' => 'PART. SOCIETARIA',
debug($this->pessoa);

    // 'qt_landline' => 0,
    // 'qt_inconsistent' => 0,
    // 'qt_mobile' => 0,
    // 'qt_obito' => 0,
				
				/**
				* Calcula a terceira linha ate onde as celulas serao mescladas
				*/
				$ln_merge = $ln + ($this->limit_reg - 1);

				/**
				* Inverte a posicao do array de colunas
				*/
				$columns = array_flip($this->columns);

				/**
				* Popula o numero do registro
				*/
				if($doc){
					$this->setValue($ln, $columns['REG'], $this->counter['qt_processed']);
					$this->AppExcel->obj->getActiveSheet()->mergeCells(str_replace('%row%', '', "{$columns['REG']}{$ln}:{$columns['REG']}{$ln_merge}"));

					/**
					* Popula o documento do registro
					*/
					$this->setValue($ln, $columns['CPF/CNPJ'], $doc);
					$this->AppExcel->obj->getActiveSheet()->mergeCells(str_replace('%row%', '', "{$columns['CPF/CNPJ']}{$ln}:{$columns['CPF/CNPJ']}{$ln_merge}"));
				}

				/**
				* Contabiliza os registros que nao retornaram nenhum tipo de dado
				*/
				$hasContent = false;
				foreach ($this->getResultsType() as $k => $v) {
					if($this->pessoa[$k]){
						$hasContent = true;
					}
				}

				if(!$hasContent && $doc){
					$this->counter['qt_not_found']++;
				}else if($doc){

					/**
					* Contabiliza as pessoas encontradas
					*/
					if($this->pessoa['NattFixoPessoa'] || $this->pessoa['NattObito']){
						$this->counter['qt_pessoas']++;
					}

					/**
					* Contabiliza os telefones fixos
					*/
					if($this->pessoa['NattFixoTelefones']){
						$this->counter['qt_landline'] += count($this->pessoa['NattFixoTelefones']);
					}

					/**
					* Contebiliza os telefones moveis
					*/
					if($this->pessoa['NattMovelTelefones']){
						$this->counter['qt_mobile'] += count($this->pessoa['NattMovelTelefones']);
					}

					/**
					* Contebiliza os obitos
					*/
					if($this->pessoa['NattObito']){
						$this->counter['qt_obito']++;
					}

					/**
					* Contebiliza as sociedades encontradas
					*/
					if($this->pessoa['NattSociedade']){
						$this->counter['qt_sociedade'] += count($this->pessoa['NattSociedade']);
					}



					/**
					* Popula o telefone fixo do registro
					*/
					if($this->pessoa['NattFixoTelefones']){
						$ln_aux = $ln;
						foreach ($this->pessoa['NattFixoTelefones'] as $k => $v) {
							$this->setValue($ln_aux, $columns['TELEFONE FIXO'], $v['TELEFONE']);
							$ln_aux++;
						}
					}

					/**
					* Popula o telefone movel do registro
					*/
					if($this->pessoa['NattMovelTelefones']){
						$ln_aux = $ln;
						foreach ($this->pessoa['NattMovelTelefones'] as $k => $v) {
							$this->setValue($ln_aux, $columns['TELEFONE MOVEL'], $v['TELEFONE']);
							$ln_aux++;
						}
					}

					/**
					* Popula o nome do registro
					*/
					$nome = false;
					$nome = ($this->pessoa['NattObito'])?$this->pessoa['NattObito']['NOME']:$this->pessoa['NattFixoPessoa']['NOME_RAZAO'];
					if($nome){
						$this->setValue($ln, $columns['NOME/RAZAO'], $nome);
					}	
					$this->AppExcel->obj->getActiveSheet()->mergeCells(str_replace('%row%', '', "{$columns['NOME/RAZAO']}{$ln}:{$columns['NOME/RAZAO']}{$ln_merge}"));

					/**
					* Popula o aniversario do registro
					*/
					$aniversario = false;
					if(isset($this->pessoa['NattObito']['NASCIMENTO']) && $this->pessoa['NattObito']['NASCIMENTO'] != '0000-00-00'){
						$aniversario = $this->pessoa['NattObito']['NASCIMENTO'];
					}else if(isset($this->pessoa['NattFixoPessoa']['DT_NASCIMENTO']) && $this->pessoa['NattFixoPessoa']['DT_NASCIMENTO'] != '0000-00-00'){
						$aniversario = $this->pessoa['NattFixoPessoa']['DT_NASCIMENTO'];
					}

					if($aniversario){
						$this->setValue($ln, $columns['ANIVERSARIO'], $this->AppUtils->dt2br($aniversario));
						/**
						* Popula a idade do registro
						*/
						$this->setValue($ln, $columns['IDADE'], $this->AppUtils->calcAge($aniversario));

					}					
					$this->AppExcel->obj->getActiveSheet()->mergeCells(str_replace('%row%', '', "{$columns['ANIVERSARIO']}{$ln}:{$columns['ANIVERSARIO']}{$ln_merge}"));
					$this->AppExcel->obj->getActiveSheet()->mergeCells(str_replace('%row%', '', "{$columns['IDADE']}{$ln}:{$columns['IDADE']}{$ln_merge}"));

					/**
					* Popula o endereco do registro
					*/


				}


				$ln+=$this->limit_reg;
				$ln_merge = 0;
			}while($doc);

			$this->AppExcel->save($chk['filename_excel']);

		}
debug($this->counter);
		die;
	}


	private function setValue($line, $column, $value){
		$pos = str_replace('%row%', $line, $column);
		if(strstr($column, ':')){
			$this->AppExcel->obj->getActiveSheet()->getCell(substr($pos, 0, strpos($pos, ':')))->setValue($value);
			$this->AppExcel->obj->getActiveSheet()->mergeCells($pos);
		}else{
			$this->AppExcel->obj->getActiveSheet()->getCell($pos)->setValue($value);
		}
	}

	private function loadChk(){
		/**
		* Carrega o proximo checkinlist da fila para ser processado
		*/
		$map = $this->Chk->find('first', array('conditions' => array('Chk.status' => CHK_STATUS_QUEUE), 'order' => 'created'));
		if(isset($map['Chk']['id'])){
			$map['Chk']['filename'] = dirname(dirname(__FILE__)) . $map['Chk']['filename'];
			$map['Chk']['filename_source'] = dirname(dirname(__FILE__)) . $map['Chk']['filename_source'];
			$map['Chk']['filename_excel'] = dirname(dirname(__FILE__)) . $map['Chk']['filename_excel'];

			/**
			* Gera uma cópia do arquivo com os docs para servir de Source
			*/
			if(!copy($map['Chk']['filename'], $map['Chk']['filename_source'])){
				$this->regLog($map['Chk']['id'], CHK_STATUS_FAILED, 'Não foi possivel gerar a copia do arquivo para inicialização do processo.');
			}else{
				/**
				* Atualiza o status do checkinlist
				*/
				// $this->Chk->updateAll(array('Chk.status' => CHK_STATUS_INPROCESS, 'Chk.ini_process' => 'NOW()'), array('Chk.id' => $map['Chk']['id']));
			}


		}else{
			$map = false;
		}

		return $map;
	}

	private function regLog($id, $status, $log){
		/**
		* Registro o log e o status passado por parametro
		*/
		$this->Chk->updateAll(array('Chk.status' => $status, 'Chk.log' => $log), array('Chk.id' => $id));
	}

	/**
	* Carrega o proximo doc do checkinlist passado pelo parametro
	*/
	private function nextDoc($source){
		$doc = false;

		if(is_file($source)){
			if($this->getQtLines($source)){
				/**
				* Pega a primeira linha do arquivo
				*/
				$doc = $this->getFirstLine($source);
				
				/**
				* Remove a linha lida
				*/
				$this->removeFirstLine($source);
			}
		}		


		return $doc;
	}

	private function getQtLines($source){
		$qt_lines = shell_exec("wc -L {$source}");
		$qt_lines = substr($qt_lines, 0, strpos($qt_lines, ' '));
		$qt_lines = trim($qt_lines);
		
		return $qt_lines;
	}

	private function removeFirstLine($source){
		$source_temp = str_replace('source', 'source_temp', $source);
		shell_exec("sed '1d' {$source} > {$source_temp}");
		shell_exec("mv {$source_temp} {$source}");
	}

	private function getFirstLine($source){
		$line = shell_exec("head -1 {$source}");
		$line = preg_replace('/[^0-9]/si', '', $line);
		$line = trim($line);
		
		return $line;
	}

	private function upload_file($data){
		$qtProcess = false;

		/**
		* Carrega os dados do arquivo anexado
		*/
		$file = $data['Files']['filename'];

		/**
		* Carrega o id do checkinlist que sera inserido
		*/
		$chk_id = $this->Chk->getNextId();

		/**
		* Carrega o id do cliente
		*/
		$client_id = ltrim($data['Chk']['client_id'], '0');

		/**
		* Upload de anexos
		*/
        if($file['error']){
        	$this->Session->setFlash('O arquivo esta corrompido.', FLASH_TEMPLETE, array('class' => FLASH_CLASS_ERROR), FLASH_SESSION_FORM);
        	$this->Chk->delete($chk_id);
        	$this->redirect($this->referer());
        }else{
	        //move it to where we want it to be
	        $new_folder_name = dirname(dirname(__FILE__)) . "/webroot/checkinlist/{$client_id}/{$chk_id}";
	        @mkdir($new_folder_name, 0777, true);
	        $new_file_name = "{$new_folder_name}/{$chk_id}";
	        $qtProcess = move_uploaded_file($file['tmp_name'], $new_file_name);

	        if(!$qtProcess){
	        	$this->Session->setFlash('Não foi possivel salvar o arquivo.', FLASH_TEMPLETE, array('class' => FLASH_CLASS_ERROR), FLASH_SESSION_FORM);
	        	$this->Chk->delete($chk_id);
	        	$this->redirect($this->referer());
	        }else{
	        	$qtProcess = $this->getQtProcess($new_file_name);
	        }
        }

        return $qtProcess;
	}

	private function getQtProcess($file){
		$linecount = 0;
		if(is_file($file)){
			$handle = fopen($file, "r");
			while(!feof($handle)){
			  	$line = preg_replace('/[^0-9]/', '', fgets($handle));
				if(preg_match('/[0-9]/', $line)){
			  		$linecount++;
				}
			}

			fclose($handle);
		}

		return $linecount;
	}
}
