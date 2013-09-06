<?php
/**
 * Application level Component
 *
 * Este arquivo é uma extencao do webservice do facebook
 *
 * @link          https://developers.google.com/google-apps/calendar/instantiate
 * @package       app.Controller.Component
 */
App::uses('AppUtilsComponent', 'Main.Controller/Component');

/**
 * Application Component
 *
 */
class ImportComponent extends Component {
	/**
	* Carrega os componentes que poderao ser usados em quaisquer controller desta framework
	*/
	public $components = array(
		'Main.AppUtils', 
		);

	private $Log;
	private $City;
	private $Address;
	private $ModelCounter;
	private $Timing;
	private $counter;
	private $time_start;
	private $time_end;
	private $time_id;
	private $time_desc;
	private $female_names;
	private $male_names;
	public $sizeReload;

	public function __construct() {
	    $this->Log = ClassRegistry::init('Log');
	    $this->City = ClassRegistry::init('City');
	    $this->Address = ClassRegistry::init('Address');
	    $this->ModelCounter = ClassRegistry::init('Counter');
	    $this->Timing = ClassRegistry::init('Timing');
		$map = $this->Log->query('select @@foreign_key_checks, @@unique_checks, @@autocommit, @@query_cache_type, @@query_cache_size');

	    /**
	    * Desabilita as verificacoes de chave estrangeira
	    */
	    $map = $this->Log->query('SET foreign_key_checks = 0');
	    /**
	    * Desabilita as verificacoes de chave unica
	    */
	    $map = $this->Log->query('SET unique_checks = 0');
	    /**
	    * Desabilita o autocommit
	    */
	    $map = $this->Log->query('SET autocommit = 0');
	    /**
	    * Habilita o cache das consultas
	    */
	    $map = $this->Log->query('SET query_cache_type = 1');

	    /**
	    * Carrega o array de nomes femininos e masculinas para comparacao e deducao de sexo
	    */
	    $this->loadFemaleNames();
	    $this->loadMaleNames();
	}	

	/**
	* Gera o hash do nome passado por parametro
	*/
	public function getHash($name, $part=null){
		/**
		* Inicializa a variavel $hash que guardara todos os hashs do nome
		*/
		$hash = array();

		/**
		* Verifica se o nome passado passado por parametro é valido, caso contrario retorna 0
		*/
		if(is_null($name)){
			return 0;
		}

		if(empty($name)){
			return 0;
		}

		/**
		* Remove todos os acentos do nome
		*/
		$name = $this->removeAcentos($name);

		/**
		* Padroniza o hash por letras minusculas
		*/
		$name = strtolower($name);

		/**
		* Remove as siglas LTDA e suas derivadas
		*/
		$name = preg_replace('/ltda|ltdame|meltda|ltda-me|ltda_me|ltda\/me/si', '', $name);

		/**
		* Remove todas as palavras com menos de 4 letrase que estejam no meio do nome do nome
		* onde todas as preposicoes serao excluidas  de | do | da | dos | das 
		*/
		$name = preg_replace('/ [a-z]{1,3} /si', ' ', $name);

		/**
		* Remove todas as palavas com menos de 4 letras que possam estar no final da frase
		*/
		$name = preg_replace('/ [a-z]{1,3}$/si', '', $name);

		/**
		* explode o nome para gerar os hashes por parte
		*/
		$map_name = explode(' ', $name);
		foreach ($map_name as $k => $v) {
			$hash["h" . ($k+1)] = $this->__hash($v);
		}

		/**
		* Completa o array com a quantidade de hash maxima permitida no sistema (5 partes)
		*/
		for ($i=(count($hash) + 1); $i <= LIMIT_HASH; $i++) { 
			$hash["h{$i}"] = 0;
		}

		/**
		* Carrega o ultimo hash do nome
		*/
		$name_last = isset($map_name[(count($map_name) - 1)])?$map_name[(count($map_name) - 1)]:null;
		$h_last = $this->__hash($name_last);

		/**
		* Carrega o penultimo hash do nome
		*/
		$name_last_pre = isset($map_name[(count($map_name) - 2)])?$map_name[(count($map_name) - 2)]:null;
		$h_last_pre = $this->__hash($name_last_pre);

		/**
		* Gera o hash do nome completo (sem espaço entre os nomes)
		*/
		$hash['h_all'] = $this->__hash(str_replace(' ', '', $name));

		/**
		* Gera o hash do primeiro e do ultimo nome juntos (sem espaço entre os nomes)
		*/
		if(isset($map_name[0]) && $name_last && ($map_name[0] != $name_last)){
			$hash['h_first_last'] = $this->__hash("{$map_name[0]}{$name_last}");
		}else if(isset($map_name[0])){
			$hash['h_first_last'] = $this->__hash("{$map_name[0]}");
		}else{
			$hash['h_first_last'] = 0;
		}

		/**
		* Gera o hash do ultimo nome
		*/
		if($h_last){
			$hash['h_last'] = $h_last;
		}else if(isset($map_name[0])){
			$hash['h_last'] = $this->__hash("{$map_name[0]}");
		}else{
			$hash['h_last'] = 0;
		}

		/**
		* Gera o hash do primeiro e segundo nomes (sem espaço entre os nomes)
		*/
		if(isset($map_name[0]) && isset($map_name[1]) && ($map_name[0] != $map_name[1])){
			$hash['h_first1_first2'] = $this->__hash("{$map_name[0]}{$map_name[1]}");
		}else if(isset($map_name[0])){
			$hash['h_first1_first2'] = $this->__hash("{$map_name[0]}");
		}else{
			$hash['h_first1_first2'] = 0;
		}

		/**
		* Gera o hash dos dois ultimos nomes
		*/
		if($name_last_pre && $name_last){
			$hash['h_last1_last2'] = $this->__hash("{$name_last_pre}{$name_last}");
		}else if($name_last_pre){
			$hash['h_last1_last2'] = $this->__hash("{$name_last_pre}");
		}else{
			$hash['h_last1_last2'] = 0;
		}

		/**
		* Retorna uma parte em especial do hash
		*/
		if($part && array_key_exists($part, $hash)){
			return $hash[$part];
		}

		return $hash;
	}

	/**
	* Retorna o tipo do documento passado por parametro
	*/
	public function getTypeDoc($doc, $name=null){
		/**
		* Inicializa a variavel $type como tipo invalido
		*/
		$type = TP_INVALID;

		/**
		* Verifica se o documento passado consiste como CPF
		*/
		if($this->validateCpf($doc)){
			$type = TP_CPF;
		}
		/**
		* Verifica se o documento passado consiste como CNPJ
		*/
		if($this->validateCnpj($doc)){
			/**
			* Verifica se o documento passado é ambiguo
			*/
			if($type == TP_CPF){
				$type = TP_AMBIGUO;

				/**
				* Tenta descobrir se o documento é CNPJ atraves do nome
				*/
				if($name){
					if(preg_match('/[ _-]ltda$|[ _-]me$|[ _-]sa$|[ _-]exp$|[ _-]s\/a$/si', strtolower($this->clearName($name)))){
						$type = TP_CNPJ;
					}

					if(preg_match('/advogados|associados|industria|comercio|artigos|artigos/si', strtolower($this->clearName($name)))){
						$type = TP_CNPJ;
					}
				}
			}else{
				$type = TP_CNPJ;
			}
		}

		return $type;
	}

	/**
	* Trata e limpa o nome passado por parametro
	*/
	public function clearName($name){
		/**
		* Verifica se o texto é consistente
		*/
		if(!preg_match('/[a-z]*/si', strtolower($name))){
			$name = null;
		}else{
			/**
			* Remove qualquer caracter do nome que nao seja letras
			*/
			$name = ucwords(strtolower(trim(preg_replace('/[^a-zA-Z ]/si', '', $name))));
		}


		return $name;
	}

	/**
	* Retorna o sexo da entidade
	*/
	public function getGender($text_gender, $type_doc, $name){
		/**
		* Retorna null caso o nome nao seja valido
		*/
		if(empty($name)){
			return null;
		}

		/**
		* Inicializa a variavel $gender com null
		*/
		$gender = null;

		/**
		* Verifica se o tipo da entidade é CPF para gerar o seco da entidade
		*/
		if($type_doc != TP_CNPJ){
			/**
			* Verifica se o campo SEXO de origem esta setado e o converte para inteiro
			*/
			switch ($text_gender) {
				case 'F':
					$gender = FEMALE;
					break;
				
				case 'M':
					$gender = MALE;
					break;
				
				default:
					/**
					* Aplica regras para tentar descobrir o sexo da entidade apartir do primeiro nome
					*/
					$first_name = $this->removeAcentos(strtolower(substr($name, 0, strpos("{$name} ", ' '))));

					/**
					* Verifica se o primeiro nome é masculino
					*/
					if(in_array($first_name, $this->male_names)){
						$gender = MALE;
					}else if(in_array($first_name, $this->female_names)){
						/**
						* Verifica se o primeiro nome é feminino
						*/
						$gender = FEMALE;
					}

					break;
			}
		}

		return $gender;
	}

	/**
	* Trata a data de aniversario passada por parametro
	*/
	public function getBirthday($date){
		/**
		* Verifica se a data é consistente
		*/
		if(!preg_match('%[12][0-9]{3}-[01][0-9]-[0-3][0-9]/$%si', $date)){
			$date = null;
		}

		return $date;
	}

	/**
	* Trata a data de atualizacao passada por parametro e retorna somente o ANO da atualizacao
	*/
	public function getUpdated($date){
		/**
		* Verifica se a data é consistente
		*/
		if(!preg_match('%[0-3][0-9]/[01][0-9]/[12][0-9]{3}$%si', $date)){
			return null;
		}


		if(preg_match('%(0[1-9]|[12][0-9]|3[01])[\./-]?(0[1-9]|1[012])[\./-]?([12][0-9]{3})%si', $date, $dt)){
			$updated = $dt[3];
		}	

		return $updated;
	}

	/**
	* Trata o CEP passado por parametro
	*/
	public function getZipcode($zipcode, $zipcode_aux=false){
		/**
		* Remove tudo que nao for numeros do CEP
		*/
		$zipcode = preg_replace('/[^0-9]/si', '', $zipcode);

		/**
		* Completa com zeros a esquerda ate completar a quantidade de numeros do CEP
		*/
		$zipcode = str_pad(substr($zipcode, -8), 8, '0', STR_PAD_LEFT);

		/**
		* Verifica se nenhuma das sequências invalidas abaixo 
		* foi digitada.
		*/
		if(preg_match('/0{8}|1{8}|2{8}|3{8}|4{8}|5{8}|6{8}|7{8}|8{8}|9{8}/si', $zipcode)){
			$zipcode = null;
		}

		/**
		* Verifica se o CEP é inconsistente
		*/
		if(!preg_match('/^[0-9]{8}$/si', $zipcode)){
			$zipcode = null;
		}

		/**
		* Caso o CEP seja invaldo, verifica se foi passado um CEP alternativo e executa as verificacoes em cima dele
		*/
		if(!$zipcode && $zipcode_aux){
			$zipcode = $this->getZipcode($zipcode_aux);
		}

		return $zipcode;
	}

	/**
	* Carrega o ID do estado apartir da string do estado passada por parametro
	*/
	public function getState($state, $state_aux=false){
		/**
		* Carrega todos os estados do pais em uma array com o sigla => cod
		*/
		$map_states = $this->loadStates();

		$state_id = $map_states[strtoupper($state)];

		/**
		* Verifica se o estado informado é invalido
		*/
		if(!is_numeric($state_id)){
			$state_id = null;
		}

		/**
		* Caso o Estado seja invaldo, verifica se foi passado um estado alternativo e executa as verificacoes em cima dele
		*/
		if(!$state_id && $state_aux){
			$state_id = $this->getState($state_aux);
		}

		return $state_id;
	}

	/**
	* Carrega o ID da cidade apartir do nome da cidade e o codigo do estado passados pelo parametro
	*/
	public function getCityId($city, $state_id, $zipcode_id=null){
		/**
		* Inicializa a variavel $city_id com ZERO
		*/
		$city_id = null;

		/**
		* Carrega todos os estados do pais em uma array com o sigla => cod
		*/
		$map_states = $this->loadStates();

		/**
		* Verifica se o estado informado é valido
		*/
		if(in_array($state_id, $map_states)){
			/**
			* Remove tudo que nao for letas do nome da cidade
			*/
			$city = preg_replace('/[^a-z ]/si', ' ', strtolower($city));

			/**
			* Remove abreviacoes
			*/
			$city = $this->removeAbreviacoes($city);

			/**
			* Remove todos os caracteres especiais da cidade
			*/
			$city = $this->removeAcentos($city);

			/**
			* Verifica se algum endereço ja foi cadastrado com o mesmo CEP e clona a cidade do endereço
			*/
			if($zipcode_id){
				$hasCity = $this->Address->find('first', array(
					'recursive' => '-1',
					'conditions' => array(
						'zipcode_id' => $zipcode_id,
						'city_id NOT' => null
						)
					));
				if(count($hasCity)){
					$city_id = $hasCity['Address']['city_id'];
				}
			}

			if(!$city_id){
				/**
				* Busca pela cidade atravez do nome completo e do estado
				*/
				$hasCity = $this->City->find('first', array(
					'recursive' => '-1',
					'conditions' => array(
						'City.name' => $city,
						'City.state_id' => $state_id
						)
					));
				if(count($hasCity)){
					$city_id = $hasCity['City']['id'];
				}				

				/**
				* Busca pela cidade atravez de partes do nome e do estado
				*/
				if(!$city_id){
					$hasCity = $this->City->find('first', array(
						'recursive' => '-1',
						'conditions' => array(
							'City.name like' => "%" . str_replace(' ', '%', preg_replace('/ [a-z]{1,2} /si', ' ', $city)) . "%",
							'City.state_id' => $state_id
							)
						));

					if(count($hasCity)){
						$city_id = $hasCity['City']['id'];
					}
				}
			}
		}

		return $city_id;
	}

	/**
	* Trata o nome da cidade
	*/
	public function getCity($city){
		/**
		* Remove abreviacoes
		*/
		$city = $this->removeAbreviacoes($city);

		/**
		* Altera todas as iniciais para maisuculo
		*/
		$city = ucwords(strtolower($city));

		return $city;
	}

	/**
	* Trata o nome dos bairros
	*/
	public function getNeighborhood($neighborhood){
		/**
		* Remove tudo que nao for letas e numeros do nome do bairro
		*/
		$neighborhood = preg_replace('/[^a-z0-9 ]/si', '', strtolower($neighborhood));

		/**
		* Remove abreviacoes
		*/
		$neighborhood = $this->removeAbreviacoes($neighborhood);

		/**
		* Formata o nome com as primeiras letras em maiusculo
		*/
		$neighborhood = ucwords(strtolower($neighborhood));


		return $neighborhood;
	}

	/**
	* Trata o complemento do endereço
	*/
	public function getComplement($complement){
		/**
		* Remove tudo que nao for letas e numeros do nome do bairro
		*/
		$complement = preg_replace('/[^a-z0-9 ]/si', '', strtolower($complement));

		/**
		* Remove abreviacoes
		*/
		$complement = $this->removeAbreviacoes($complement);

		/**
		* Formata o nome com as primeiras letras em maiusculo
		*/
		$complement = ucwords(strtolower($complement));


		return $complement;
	}	

	/**
	* Carrega o Logradouro apartir do nome da rua passado pelo parametro
	*/
	public function getTypeAddress($type_address, $street=false){
		/**
		* Inicializa a variavel $type com null
		*/
		$type = null;

		/**
		* Aplica regras para tentar extrair o logrdoura
		*/
		if(preg_match('/^al\.? .*|alameda .*/si', strtolower($type_address))){
			$type = 'Alameda';
		}
		
		if(preg_match('/^av\.? .*|avenida .*/si', strtolower($type_address))){
			$type = 'Avenida';
		}
		
		if(preg_match('/^b[c]?\.? .*|beco .*/si', strtolower($type_address))){
			$type = 'Beco';
		}
		
		if(preg_match('/^cal[cç]?\.? .*|cal[cç]ada .*/si', strtolower($type_address))){
			$type = 'Calçada';
		}
		
		if(preg_match('/^con[d]?\.? .*|condom[ií]nio .*/si', strtolower($type_address))){
			$type = 'Condomínio';
		}
		
		if(preg_match('/^cj\.? .*|conj\.? .*|conju\.? .*|conjunto .*/si', strtolower($type_address))){
			$type = 'Conjunto';
		}
		
		if(preg_match('/^esc\.? .*|esd\.? .*|escad\.? .*|escadaria .*/si', strtolower($type_address))){
			$type = 'Escadaria';
		}
		
		if(preg_match('/^es[t]?\.? .*|estrada .*/si', strtolower($type_address))){
			$type = 'Estrada';
		}
		
		if(preg_match('/^ga[l]?\.? .*|galeria .*/si', strtolower($type_address))){
			$type = 'Galeria';
		}
		
		if(preg_match('/^jd\.? .*|jardim .*/si', strtolower($type_address))){
			$type = 'Jardim';
		}
		
		if(preg_match('/^l[g]?\.? .*|largo .*/si', strtolower($type_address))){
			$type = 'Largo';
		}
		
		if(preg_match('/^p[cç]?[a]??\.? .*|pra[cç]a .*/si', strtolower($type_address))){
			$type = 'Praça';
		}
		
		if(preg_match('/^r\.? .*|rua .*/si', strtolower($type_address))){
			$type = 'Rua';
		}
		
		if(preg_match('/^rod\.? .*|rodovia .*/si', strtolower($type_address))){
			$type = 'Rodovia';
		}
		
		if(preg_match('/^tv\.? .*|travessa .*/si', strtolower($type_address))){
			$type = 'Travessa';
		}
		
		if(preg_match('/^trv\.? .*|trevo .*/si', strtolower($type_address))){
			$type = 'Trevo';
		}
		
		if(preg_match('/^vl\.? .*|vila .*/si', strtolower($type_address))){
			$type = 'Vila';
		}

		if(preg_match('/^vd\.? .*|viaduto .*/si', strtolower($type_address))){
			$type = 'Viaduto';
		}

		/**
		* Caso o logradouro nao atenda a nenhum criterio acima, tenta extrair o logradouro do nome da rua
		*/
		if(!$type && $street){
			$type = $this->getTypeAddress($street);
		}		

		return $type;
	}

	/**
	* Trata o nome da rua passado por parametro
	*/
	public function getStreet($street){
		/**
		* Remove tudo que nao for letas e numeros do nome da rua
		*/
		$street = preg_replace('/[^a-z0-9 ]/si', '', strtolower($street));

		/**
		* Remove qualquer combinacao de logradouro que encontrar no endereço
		*/
		$street = preg_replace('/^bairro /si', '', $street);
		$street = preg_replace('/^al\.? |alameda /si', '', $street);
		$street = preg_replace('/^av\.? |avenida /si', '', $street);
		$street = preg_replace('/^b[c]?\.? |beco /si', '', $street);
		$street = preg_replace('/^cal[cç]?\.? |cal[cç]ada /si', '', $street);
		$street = preg_replace('/^con[d]?\.? |condom[ií]nio /si', '', $street);
		$street = preg_replace('/^cj\.? |conj\.? |conju\.? |conjunto /si', '', $street);
		$street = preg_replace('/^esc\.? |esd\.? |escad\.? |escadaria /si', '', $street);
		$street = preg_replace('/^es[t]?\.? |estrada /si', '', $street);
		$street = preg_replace('/^ga[l]?\.? |galeria /si', '', $street);
		$street = preg_replace('/^jd\.? |jardim /si', '', $street);
		$street = preg_replace('/^l[g]?\.? |largo /si', '', $street);
		$street = preg_replace('/^p[cç]?[a]?\.? |pra[cç]a /si', '', $street);
		$street = preg_replace('/^r\.? |rua /si', '', $street);
		$street = preg_replace('/^rod\.? |rodovia /si', '', $street);
		$street = preg_replace('/^tv\.? |travessa /si', '', $street);
		$street = preg_replace('/^trv\.? |trevo /si', '', $street);
		$street = preg_replace('/^vl\.? |vila /si', '', $street);
		$street = preg_replace('/^vd\.? |viaduto /si', '', $street);
		$street = preg_replace('/cento e um/si', '101', $street);

		/**
		* Remove qualquer numero residencial que esteja no meio do endereço
		*/
		$street = preg_replace('/.*? (n [0-9]*?)[a-z ].*/si', '', $street);

		/**
		* Remove as abreviacoes
		*/
		$street = $this->removeAbreviacoes($street);

		/**
		* Remove os possiveis espaços antes e depois do endereço
		*/
		$street = trim($street);

		/**
		* Formata o nome com as primeiras letras em maiusculo
		*/
		$street = ucwords(strtolower($street));

		return $street;
	}

	/**
	* Tenta carregar o numero da rua a partir do parametro $number, caso nao consiga, tenta carregar a partir do parametro $street
	*/
	public function getStreetNumber($number, $street=false){
		/**
		* Inicializa a variavel $street_number com null
		*/
		$street_number = null;

		/**
		* Verifica se o numero passado por parametro é valido, caso nao seja, tenta carregar o numero a partir do nome da rua
		*/
		if(is_numeric($number) && $number > '0'){
			$street_number = $number;
		}

		/**
		* Caso o numero nao tenha sido carregado ainda, tenta carrega-lo a partir do nome da rua
		*/
		if(!$street_number && $street){
			if(preg_match('/.*? (n [0-9]*?)[a-z ].*/si', '', $vet)){
				$street_number = $vet[1];
			}
		}

		/**
		* Remove tudo que nao for numero
		*/
		$street_number = preg_replace('/[^0-9]/', '', $street_number);

		return $street_number;
	}

	/**
	* Explode o telefone separando o ddd do telefone
	*/
	private function explodeTelNatt($tel, $item){
		/**
		* Analisa a situacao do telefone a partir da quantidade de zeros iniciais
		*/
		preg_match('/^(0*)/si', $tel, $vet);
		$qt_zeros = strlen($vet[1]);

		/**
		* Trata o numero de acordo com os zeros iniciais
		*/
		switch ($qt_zeros) {
			/**
			* 1 Zero: Indica que o numero contem 8 digitos e esta acompanhado do DDD
			*/
			case 1:
				$ddd = substr($tel, 1, 2);
				$tel = substr($tel, -8);
				break;

			/**
			* 2 Zeros: Indica que o numero contem 7 digitos e esta acompanhado do DDD
			*/
			case 2:
				$ddd = substr($tel, 2, 2);
				$tel = substr($tel, -7);
				/**
				* Adiciona o numero 3 na frente do telefone
				*/
				$tel = "3{$tel}";
				break;
			
			/**
			* 3 Zeros: Indica que o telefone tem 8 digitos e nao esta acompanhado do DDD
			*/
			case 3:
				$ddd = null;
				$tel = substr($tel, -8);
				break;
			
			/**
			* 4 Zeros: Indica que o telefone tem 7 digitos e nao esta acompanhado do DDD
			*/
			case 4:
				$ddd = null;
				$tel = substr($tel, -7);
				/**
				* Adiciona o numero 3 na frente do telefone
				*/
				$tel = "3{$tel}";
				break;
			
			default:
				/**
				* Caso nao atenda a nenhuma das opcoes acima, o telefone sera considerado como nulo
				*/
				$ddd = null;
				$tel = null;
				break;
		}

		$map = array(
			'ddd' => $ddd,
			'tel' => $tel
			);

		return $map[$item];
	}
	/**
	* Extrai o DDD do telefone passado por parametro
	*/
	public function getDDD($tel){
		return $this->explodeTelNatt($tel, 'ddd');
	}	

	/**
	* Extrai o Telefone separado do DDD
	*/
	public function getTelefone($tel){
		return $this->explodeTelNatt($tel, 'tel');
	}	

	/**
	* Verifica se o documento passado é valido, seja como CPF ou como CNPJ
	*/
	public function validateDoc($doc){
		$validate = $this->validateCpf($doc);
		$validate = $validate?$validate:$this->validateCnpj($doc);

		return $validate;
	}

	/**
	* Verifica se o cpf passado é valido
	*/
	public function validateCpf($cpf){
		// Verifica se um número foi informado
		if(empty($cpf)) {
		    return false;
		}

		// Elimina possivel mascara
		$cpf = ereg_replace('[^0-9]', '', $cpf);
		$cpf = str_pad(substr($cpf, -11), 11, '0', STR_PAD_LEFT);
	 
		// Verifica se o numero de digitos informados é igual a 11 
		if (strlen($cpf) != 11) {
		    return false;
		}

		// Verifica se nenhuma das sequências invalidas abaixo 
		// foi digitada. Caso afirmativo, retorna falso
		else if (preg_match('/0{11}|1{11}|2{11}|3{11}|4{11}|5{11}|6{11}|7{11}|8{11}|9{11}/si', $cpf)) {
		    return false;
		 // Calcula os digitos verificadores para verificar se o
		 // CPF é válido
		 } else {   
		     
		    for ($t = 9; $t < 11; $t++) {
		         
		        for ($d = 0, $c = 0; $c < $t; $c++) {
		            $d += $cpf{$c} * (($t + 1) - $c);
		        }
		        $d = ((10 * $d) % 11) % 10;
		        if ($cpf{$c} != $d) {
		            return false;
		        }
		    }

		    return true;
		}		
	}

	/**
	* Verifica se o cnpj passado é valido
	*/
	public function validateCnpj($cnpj){
		// Verifica se um número foi informado
		if(empty($cnpj)) {
		    return false;
		}

		// Elimina possivel mascara
		$cnpj = ereg_replace('[^0-9]', '', $cnpj);
		$cnpj = str_pad(substr($cnpj, -14), 14, '0', STR_PAD_LEFT);
		 
		// Verifica se o numero de digitos informados é igual a 11 
		if (strlen($cnpj) != 14) {
		    return false;
		}
		// Verifica se nenhuma das sequências invalidas abaixo 
		// foi digitada. Caso afirmativo, retorna falso
		else if (preg_match('/0{14}|1{14}|2{14}|3{14}|4{14}|5{14}|6{14}|7{14}|8{14}|9{14}/si', $cnpj)) {
		    return false;
		 // Calcula os digitos verificadores para verificar se o
		 // CPF é válido
		 }


        $calcular = 0;
        $calcularDois = 0;
        for ($i = 0, $x = 5; $i <= 11; $i++, $x--) {
            $x = ($x < 2) ? 9 : $x;
            $number = substr($cnpj, $i, 1);
            $calcular += $number * $x;
        }
        for ($i = 0, $x = 6; $i <= 12; $i++, $x--) {
            $x = ($x < 2) ? 9 : $x;
            $numberDois = substr($cnpj, $i, 1);
            $calcularDois += $numberDois * $x;
        }
 
        $digitoUm = (($calcular % 11) < 2) ? 0 : 11 - ($calcular % 11);
        $digitoDois = (($calcularDois % 11) < 2) ? 0 : 11 - ($calcularDois % 11);
 
        if ($digitoUm <> substr($cnpj, 12, 1) || $digitoDois <> substr($cnpj, 13, 1)) {
            return false;
        }


        return true;		
	}

	/**
	* Método removeAcentos
	* Remove todos os caracteres com acentos do texto passado pelo parametro
	* Ex.: $desc = $this->AppUtils->removeAcentos('Méto que remóvê acêntòs');
	* No exemplo acima, a variavel $desc tera o a texto formatada como: Metodo que remove acentos
	*
	* @param string $txt|com acentos
	* @return string $txt|sem acentos
	*/
	public function removeAcentos($txt){
		$txt = preg_replace("/á|à|â|ã|ª/s", "a", $txt);
		$txt = preg_replace("/é|è|ê/s", "e", $txt);
		$txt = preg_replace("/í|ì|î/s", "i", $txt);
		$txt = preg_replace("/ó|ò|ô|õ|º/s", "o", $txt);
		$txt = preg_replace("/ú|ù|û/s", "u", $txt);
		$txt = str_replace("ç","c",$txt);

		$txt = preg_replace("/Á|À|Â|Ã|ª/s", "A", $txt);
		$txt = preg_replace("/É|È|Ê/s", "E", $txt);
		$txt = preg_replace("/Í|Ì|Î/s", "I", $txt);
		$txt = preg_replace("/Ó|Ò|Ô|Õ|º/s", "O", $txt);
		$txt = preg_replace("/Ú|Ù|Û/s", "U", $txt);
		$txt = str_replace("Ç","C",$txt);		

		return $txt;
	}	

	/**
	* Remove abreviacoes
	*/
	private function removeAbreviacoes($txt){
		$txt = preg_replace('/ res /si', ' Residencial ', strtolower($txt));
		$txt = preg_replace('/^res /si', 'Residencial ', strtolower($txt));
		$txt = preg_replace('/^n s /si', 'Nossa Senhora ', strtolower($txt));
		$txt = preg_replace('/^n sra /si', 'Nossa Senhora ', strtolower($txt));
		$txt = preg_replace('/^al\.? |alameda /si', 'Alameda', $txt);
		$txt = preg_replace('/^av\.? |avenida /si', 'Avenida', $txt);
		$txt = preg_replace('/^b[c]?\.? |beco /si', 'Beco', $txt);
		$txt = preg_replace('/^cal[cç]?\.? |cal[cç]ada /si', 'Calçada ', $txt);
		$txt = preg_replace('/^cj\.? |conj\.? |conju\.? |conjunto /si', 'Conjunto ', $txt);
		$txt = preg_replace('/^con[d]?\.? |condom[ií]nio /si', 'Condomínio ', $txt);
		$txt = preg_replace('/^es[t]?\.? |estrada /si', 'Estrada ', $txt);
		$txt = preg_replace('/^esc\.? |esd\.? |escad\.? |escadaria /si', 'Escadaria ', $txt);
		$txt = preg_replace('/^ga[l]?\.? |galeria /si', 'Galeria ', $txt);
		$txt = preg_replace('/^j /si', 'Jardim ', $txt);
		$txt = preg_replace('/^jd\.? |jardim /si', 'Jardim ', $txt);
		$txt = preg_replace('/^l[g]?\.? |largo /si', 'Largo ', $txt);
		$txt = preg_replace('/^n rosa penha /si', 'nova rosa da penha ', strtolower($txt));
		$txt = preg_replace('/^p[cç]?\.? |pra[cç]a /si', 'Praça ', $txt);
		$txt = preg_replace('/^pq /si', 'Parque ', strtolower($txt));
		$txt = preg_replace('/^pr\.? |praia /si', 'Praia ', $txt);
		$txt = preg_replace('/^r\.? |rua /si', 'Rua ', $txt);
		$txt = preg_replace('/^rod\.? |rodovia /si', 'Rodovia ', $txt);
		$txt = preg_replace('/^s /si', 'São ', strtolower($txt));
		$txt = preg_replace('/^sta /si', 'Santa ', strtolower($txt));
		$txt = preg_replace('/^sto /si', 'Santo ', strtolower($txt));
		$txt = preg_replace('/^trv\.? |trevo /si', 'Trevo ', $txt);
		$txt = preg_replace('/^tv\.? |travessa /si', 'Travessa ', $txt);
		$txt = preg_replace('/^vd\.? |viaduto /si', 'Viaduto ', $txt);
		$txt = preg_replace('/^vl\.? |vila /si', 'Vila ', $txt);

		return $txt;
	}

	/**
	* Carrega um array com os codigos e siglas de todos os estados
	*/	
	private function loadStates(){
		$states = array(
					'AC' => '1',
					'AL' => '2',
					'AM' => '3',
					'AP' => '4',
					'BA' => '5',
					'CE' => '6',
					'DF' => '7',
					'ES' => '8',
					'GO' => '9',
					'MA' => '10',
					'MG' => '11',
					'MS' => '12',
					'MT' => '13',
					'PA' => '14',
					'PB' => '15',
					'PE' => '16',
					'PI' => '17',
					'PR' => '18',
					'RJ' => '19',
					'RN' => '20',
					'RO' => '21',
					'RR' => '22',
					'RS' => '23',
					'SC' => '24',
					'SE' => '25',
					'SP' => '26',
					'TO' => '27'
				);

		return $states;	
	}

	/**
	* Retorna o valor da funcao CRC32 sempre positivo, nunca negativo
	*/
	private function __hash($str){
		$hash = crc32($str);

		/**
		* Converte o sinal do hash caso ele seja negativo
		*/
		return ($hash < 0)?($hash*(-1)):$hash;
	}

	/**
	* Método __log
	* Este método alimenta o __log da operacao
	*
	* @override Metodo AppController.__log
	* @param string $content
	* @return void
	*/
	public function __log($log, $uf, $status=true, $table=null, $pk=null, $data=null, $mysql_error=null){	
		$Log['Log'] = array(
			'log' => $log,
			'mysql_error' => $mysql_error,
			'uf' => $uf,
			'table' => $table,
			'pk' => $pk,
			'data' => $data,
			'status' => $status,
			);
		$this->Log->create($Log);
		$this->Log->save();

		// $file = dirname(dirname(dirname(dirname(__FILE__)))) . "/_logs/{$uf}.txt";

		// $content = "\n\n\n\n";
		// $content .= "###################################################################\n";
		// $content .= "Time: " . date('Y/m/d H:i:s') . "\n";
		// $content .= "===================================================================\n";
		// $content .= "{$log}\n";
		// $content .= "===================================================================\n";

		// // echo $content;

		// $f = fopen($file,'a+'); 
		// fwrite($f, $content, strlen($content)); 
		// fclose($f); 
	}

	/**
	* Método __counter
	* Este método alimenta o __counter da operacao
	*
	* @override Metodo AppController.__counter
	* @param string $content
	* @return void
	*/
	public function __counter($table){
		$values = array();
		$conditions = array(
				'table' => $table,
				'active' => '1'
				);
		if(isset($this->counter[$table]['success'])){
			$values['success'] = $this->counter[$table]['success'];
		}
		
		if(isset($this->counter[$table]['fails'])){
			$values['fails'] = $this->counter[$table]['fails'];
		}

		if(count($values)){
			$this->ModelCounter->updateAll($values, $conditions);
		}

	}

	/**
	* Método reload
	* Este método conta quantas vezes o sistema de importacao efetuou a busca na base de extracao
	*
	* @override Metodo AppController.reload
	* @param string $content
	* @return void
	*/
	public function reloadCount(){
		if(!isset($this->counter['realods'])){
			$this->counter['realods'] = 1;
		}else{
			$this->counter['realods']++;
		}
	}

	/**
	* Contabiliza uma insercao finalizada com sucesso
	*/
	public function success($table){
		if(!isset($this->counter[$table]['success'])){
			$map = $this->ModelCounter->find('first', array(
				'recursive' => '-1',
				'conditions' => array('table' => $table, 'active' => '1'),
				'limit' => '1'
				));

			if(isset($map['Counter']['success']) && $map['Counter']['success'] > 0){
				$this->counter[$table]['success'] = $map['Counter']['success'];
			}else{
				$this->counter[$table]['success'] = 1;
			}
		}else{
			$this->counter[$table]['success']++;
		}
	}

	/**
	* Contabiliza uma falha de insercao
	*/
	public function fail($table){
		if(!isset($this->counter[$table]['fails'])){
			$map = $this->ModelCounter->find('first', array(
				'recursive' => '-1',
				'conditions' => array('table' => $table, 'active' => '1'),
				'limit' => '1'
				));

			if(isset($map['Counter']['fails']) && $map['Counter']['fails'] > 0){
				$this->counter[$table]['fails'] = $map['Counter']['fails'];
			}else{
				$this->counter[$table]['fails'] = 1;
			}
		}else{
			$this->counter[$table]['fails']++;
		}
	}


	/**
	 * show a status bar in the console
	 * 
	 * <code>
	 * for($x=1;$x<=100;$x++){
	 * 
	 *     progressBar($x, 100);
	 * 
	 *     usleep(100000);
	 *                           
	 * }
	 * </code>
	 *
	 * @param   int     $done   how many items are completed
	 * @param   int     $total  how many items are to be done total
	 * @param   int     $size   optional size of the status bar
	 * @return  void
	 *
	 */
	public function progressBar($done, $total, $uf, $size=50) {
	    // if we go over our bound, just ignore it
	    static $startTime;
	    static $date_begin;

	    if($done > $total){
		    /**
		    * Desabilita as verificacoes de chave estrangeira
		    */
		    $map = $this->Log->query('SET foreign_key_checks = 1');
		    /**
		    * Desabilita as verificacoes de chave unica
		    */
		    $map = $this->Log->query('SET unique_checks = 1');
		    /**
		    * Desabilita o autocommit
		    */
		    $map = $this->Log->query('SET autocommit = 1');
		    /**
		    * Habilita o cache das consultas
		    */
		    $map = $this->Log->query('SET query_cache_type = 0');

	    	return false;
	    } 

	    if(empty($startTime)){
	    	$startTime=time();
	    } 

	    if(empty($date_begin)){
	    	$date_begin=date('d/m/Y H:i:s');
	    } 

	    $now = time();
	    $perc=(double)($done/$total);
	    $bar=floor($perc*$size);

	    $status_bar="\r[";
	    $status_bar.=str_repeat("=", $bar);

	    if($bar<$size){
	        $status_bar.=">";
	        $status_bar.=str_repeat(" ", $size-$bar);
	    } else {
	        $status_bar.="=";
	    }

	    $disp=number_format($perc*100, 0);

	    $status_bar.="] $disp%  " . number_format($done, 0, '', '.') . "/" . number_format($total, 0, '', '.');

	    $rate = ($now-$startTime)/$done;
	    $left = $total - $done;
	    $eta = round($rate * $left, 2);

	    $elapsed = $now - $startTime;
	    $elapsed_minuts = $elapsed / 60;
	    $elapsed_day = floor($elapsed / 86400);

	    /**
	    * Tempo percorrido
	    */
		$day = str_pad(floor($elapsed/86400), 2, '0', STR_PAD_LEFT);
		$hour = str_pad(floor(($elapsed/3600) - ($day*24)), 2, '0', STR_PAD_LEFT);
		$min = str_pad(floor(($elapsed/60) - (($day*1440) + ($hour*60))), 2, '0', STR_PAD_LEFT);
		$sec = str_pad(floor($elapsed - (($day*86400) + ($hour*3600) + ($min*60))), 2, '0', STR_PAD_LEFT);
		$elapsed = "{$hour}:{$min}:{$sec}";
		if($day != '00'){
			$elapsed = "{$day}d {$hour}:{$min}:{$sec}";
		}

	    /**
	    * Tempo Restante
	    */
		$day = str_pad(floor($eta/86400), 2, '0', STR_PAD_LEFT);
		$hour = str_pad(floor(($eta/3600) - ($day*24)), 2, '0', STR_PAD_LEFT);
		$min = str_pad(floor(($eta/60) - (($day*1440) + ($hour*60))), 2, '0', STR_PAD_LEFT);
		$sec = str_pad(floor($eta - (($day*86400) + ($hour*3600) + ($min*60))), 2, '0', STR_PAD_LEFT);
		$eta = "{$hour}:{$min}:{$sec}";
		if($day != '00'){
			$eta = "{$day}d {$hour}:{$min}:{$sec}";
		}


		$map = $this->Log->query('select @@foreign_key_checks');
		$foreign_key_checks = $map[0][0]['@@foreign_key_checks'];

		$map = $this->Log->query('select @@unique_checks');
		$unique_checks = $map[0][0]['@@unique_checks'];

		$map = $this->Log->query('select @@autocommit');
		$autocommit = $map[0][0]['@@autocommit'];

		$map = $this->Log->query('select @@query_cache_type');
		$query_cache_type = $map[0][0]['@@query_cache_type'];

		$map = $this->Log->query('select @@query_cache_size');
		$query_cache_size = ($map[0][0]['@@query_cache_size']/1024)/1024;

		$reloads_eta = ceil($total/$this->sizeReload) - $this->counter['realods'];

	    $status_bar .= "\n";
		$status_bar .= "###################################################################\n";
		$status_bar .= "Start: {$date_begin}\n";
	    $status_bar .= "Tempo Restante:\t\t{$eta}\n";
	    $status_bar .= "Tempo percorrido:\t{$elapsed}\n";
		$status_bar .= "===================================================================\n";
		$status_bar .= "Estado processado: {$uf}\n";
		$status_bar .= "===================================================================\n";
		$status_bar .= "Qtd buscas/Buscas Restante: {$this->counter['realods']}/{$reloads_eta}\n";
		$status_bar .= "===================================================================\n";
	    $status_bar .= "Status do processo de importacao\n";
		$status_bar .= "___________________________________________________________________\n";
	    $status_bar .= "Dia/Min\t\t\tImport\t\tTable\n";
		$status_bar .= "___________________________________________________________________\n";
	    if(isset($this->counter) && count($this->counter)){
		    foreach ($this->counter as $k => $v) {
		    	if(isset($v['success'])){
			    	$per_minuts = ($v['success'] == 0 || $elapsed_minuts == 0)?0:round($v['success'] / $elapsed_minuts);
			    	$per_day = $per_minuts;
			    	if($elapsed_day){
			    		$per_day = ($v['success'] / $elapsed_day);
			    	}
			    	if($per_day > 999){
			    		$status_bar .= number_format($per_day, 0, '', '.') . '|' . number_format($per_minuts, 0, '', '.') . "\t\t" . number_format($v['success'], 0, '', '.') . "\t\t{$k}\n";
			    	}else{
			    		$status_bar .= number_format($per_day, 0, '', '.') . '|' . number_format($per_minuts, 0, '', '.') . "\t\t\t" . number_format($v['success'], 0, '', '.') . "\t\t{$k}\n";
			    	}
		    	}
		    }
	    }
		$status_bar .= "\n";
		$status_bar .= "===================================================================\n";
	    $status_bar .= "Configuracao do banco\n";
		$status_bar .= "___________________________________________________________________\n";
		$status_bar .= "fk\tunique\tautocommit\tquery_cache\tcache_size\n";
		$status_bar .= "___________________________________________________________________\n";
		$status_bar .= "{$foreign_key_checks}\t{$unique_checks}\t{$autocommit}\t\t{$query_cache_type}\t\t{$query_cache_size}M\n";

	    echo "$status_bar  ";
	    // when done, send a newline
	    if($done == $total) {
	        echo "\n";
	    }
	}

	/**
	* Limpa a tela do console linux
	*/
	public function __flush(){
		echo shell_exec('clear');
	}

	/**
	* Mensura o tempo gasto nas consultas
	*/
	public function timing_ini($query_id, $query_desc){
		$this->time_start = microtime(true);
		$this->time_id = $query_id;
		$this->time_desc = $query_desc;
	}

	/**
	* Mensura o tempo gasto nas consultas
	*/
	public function timing_end(){
		$this->time_end = microtime(true);
		$time = $this->time_end - $this->time_start;
		$data = array(
			'Timing' => array(
				'query_id' => $this->time_id,
				'query_desc' => $this->time_desc,
				'time' => $time,
				)
			);
		$this->Timing->create($data);
		$this->Timing->save();
	}

	/**
	* Carrega todos os nomes femininos
	*/
	private function loadFemaleNames(){
		$this->female_names = array(
					'abelia',
					'abelina',
					'abelita',
					'abigail',
					'abna',
					'acelia',
					'acilina',
					'acucena',
					'ada',
					'adalgisa',
					'adalia',
					'adelaide',
					'adelia',
					'adelina',
					'adelinda',
					'adila',
					'adilia',
					'adosinda',
					'adriana',
					'adriane',
					'adriani',
					'advania',
					'afonsina',
					'afra',
					'africana',
					'agata',
					'agda',
					'agna',
					'agnes',
					'agonia',
					'agueda',
					'aida',
					'aide',
					'airiza',
					'aixa',
					'alaide',
					'alana',
					'alba',
					'alberta',
					'albertina',
					'albina',
					'alcilene',
					'alcina',
					'alcione',
					'alda',
					'aldara',
					'aldenir',
					'aldenora',
					'aldina',
					'aldora',
					'alegria',
					'aleixa',
					'alessandra',
					'aleta',
					'alexa',
					'alexandra',
					'alexandra',
					'alexia',
					'alexina',
					'alexis',
					'alfreda',
					'alia',
					'aliana',
					'alica',
					'alice',
					'alicia',
					'alida',
					'alina',
					'aline',
					'aliny',
					'alisande',
					'alita',
					'alix',
					'alma',
					'almara',
					'almerinda',
					'almesinda',
					'almira',
					'altina',
					'alva',
					'alvarina',
					'alzira',
					'amada',
					'amalia',
					'amanda',
					'amandina',
					'amara',
					'amarilis',
					'amelia',
					'amelina',
					'america',
					'amora',
					'amorina',
					'amorzinda',
					'ana arine',
					'ana bela',
					'ana da purificacao',
					'ana de sao jose',
					'ana do carmo',
					'ana do mar',
					'ana do rosario',
					'ana flor',
					'ana lua',
					'ana mar',
					'ana rosario',
					'ana viriato',
					'ana',
					'anabel',
					'anabela',
					'anaice',
					'anaide',
					'anair',
					'anais',
					'anaisa',
					'analdina',
					'analia',
					'analice',
					'analisa',
					'anamar',
					'anastacia',
					'anatilde',
					'andrea',
					'andreia',
					'andreina',
					'andrelina',
					'andresa',
					'andressa',
					'andreza',
					'andria',
					'anesia',
					'angela',
					'angelica',
					'angelina',
					'angelita',
					'ania',
					'aniana',
					'anicia',
					'aniria',
					'anisia',
					'anita',
					'anna',
					'anne',
					'anquita',
					'anteia',
					'antera',
					'antonela',
					'antonia',
					'antonieta',
					'antonina',
					'anunciacao',
					'anunciada',
					'anuque',
					'anusca',
					'aparecida',
					'apolonia',
					'arabela',
					'araci',
					'aradna',
					'argentina',
					'aria',
					'ariadna',
					'ariadne',
					'ariana',
					'ariane',
					'arinda',
					'arlanda',
					'arlene',
					'arlete',
					'arlinda',
					'armanda',
					'armandina',
					'armenia',
					'arminda',
					'artemisa',
					'artemisia',
					'aruna',
					'asia',
					'aspasia',
					'assuncao',
					'assunta',
					'astrid',
					'astride',
					'atenais',
					'atina',
					'audete',
					'augusta',
					'aura',
					'aurea',
					'aurelia',
					'aureliana',
					'aurete',
					'aurora',
					'ausenda',
					'auta',
					'auxilia',
					'auxiliadora',
					'ava',
					'balbina',
					'balduina',
					'barbara',
					'barbora',
					'bartolina',
					'basilia',
					'basilissa',
					'beanina',
					'beatriz',
					'bebiana',
					'bela',
					'belarmina',
					'belem',
					'belina',
					'belinda',
					'belisa',
					'belisaria',
					'belmira',
					'benedita',
					'benicia',
					'benigna',
					'benilde',
					'benita',
					'benjamina',
					'benvinda',
					'berengaria',
					'berenice',
					'bernadete',
					'bernardete',
					'bernia',
					'berta',
					'bertila',
					'bertilde',
					'bertina',
					'betania',
					'betia',
					'betina',
					'betsabe',
					'bia',
					'biana',
					'bianca',
					'bibiana',
					'bibili',
					'bijal',
					'bina',
					'bitia',
					'blandina',
					'blasia',
					'bonifacia',
					'branca flor',
					'branca',
					'brasia',
					'brazia',
					'brena',
					'brenda',
					'briana',
					'bricia',
					'brigida',
					'brigite',
					'briolanja',
					'briosa',
					'brizida',
					'bruna',
					'brunilde',
					'cacia',
					'cacilda',
					'caetana',
					'caia',
					'calila',
					'camelia',
					'camila',
					'candice',
					'candida',
					'cania',
					'carela',
					'carem',
					'caren',
					'carin',
					'carina',
					'carisa',
					'carisia',
					'carissa',
					'carita',
					'carla',
					'carlinda',
					'carlota',
					'carmela',
					'carmelia',
					'carmelina',
					'carmelinda',
					'carmelita',
					'carmem',
					'carmen',
					'carmezinda',
					'carmina',
					'carminda',
					'carminho',
					'carmo',
					'carmorinda',
					'carol',
					'carole',
					'carolina',
					'caroline',
					'caroliny',
					'carsta',
					'cassandra',
					'cassia',
					'cassiana',
					'cassilda',
					'casta',
					'castelina',
					'castorina',
					'catalina',
					'catarina',
					'caterina',
					'catharina',
					'catia',
					'catila',
					'catilina',
					'cecilia',
					'celeste',
					'celia',
					'celina',
					'celinia',
					'celma',
					'celsa',
					'cereja',
					'ceres',
					'cesaltina',
					'cesaria',
					'cesarina',
					'chantal',
					'cheila',
					'chema',
					'chirlei',
					'chirley',
					'christiane',
					'christiani',
					'christiany',
					'cibele',
					'cidalia',
					'cidalina',
					'cidalisa',
					'cilene',
					'cileni',
					'cileny',
					'cinara',
					'cinderela',
					'cinira',
					'cinthia',
					'cintia',
					'cipora',
					'circe',
					'ciria',
					'cirila',
					'cirlene',
					'cizina',
					'clara',
					'clarice',
					'clarina',
					'clarinda',
					'clarisse',
					'claudemira',
					'claudete',
					'claudeth',
					'claudia',
					'claudiana',
					'claudiane',
					'claudiani',
					'claudiceia',
					'claudineia',
					'clea',
					'cleia',
					'cleide',
					'clelia',
					'clemencia',
					'clementina',
					'clemilda',
					'cleo',
					'cleodice',
					'cleonice',
					'cleopatra',
					'cleria',
					'clesia',
					'cleunice',
					'cleusa',
					'cleuza',
					'clicia',
					'climenia',
					'clivia',
					'cloe',
					'clorinda',
					'clotilde',
					'colete',
					'conceicao',
					'concha',
					'consolacao',
					'constanca',
					'constancia',
					'consuelo',
					'cora',
					'coralia',
					'coralina',
					'cordelia',
					'corina',
					'corita',
					'cornelia',
					'cosete',
					'cremilda',
					'cremilde',
					'crestila',
					'creusa',
					'creuza',
					'cris',
					'crisalia',
					'crisalida',
					'crisanta',
					'crisante',
					'crisna',
					'cristela',
					'cristele',
					'cristene',
					'cristiana',
					'cristiane',
					'cristiany',
					'cristina',
					'cristolinda',
					'custodia',
					'dafne',
					'dagmar',
					'daiana',
					'daina',
					'daise',
					'daisi',
					'daisy',
					'dalia',
					'daliana',
					'dalida',
					'dalila',
					'dalinda',
					'dalva',
					'dalzira',
					'damaris',
					'damiana',
					'dana',
					'dania',
					'daniana',
					'daniela',
					'daniele',
					'daniella',
					'danielle',
					'danielli',
					'danielly',
					'danila',
					'dara',
					'darci',
					'darcilia',
					'darlene',
					'darnela',
					'davina',
					'davinia',
					'dayana',
					'dayse',
					'de begonha',
					'debora',
					'deborah',
					'decia',
					'deise',
					'dejanira',
					'dele',
					'delfina',
					'delia',
					'deliana',
					'delisa',
					'delma',
					'delmina',
					'delminda',
					'delmira',
					'delsa',
					'delza',
					'demelza',
					'demeter',
					'demetria',
					'denilda',
					'denisa',
					'denise',
					'denize',
					'deodata',
					'deodete',
					'deolinda',
					'deonilde',
					'deotila',
					'derocila',
					'diamantina',
					'diana',
					'didia',
					'didiana',
					'digna',
					'dilceia',
					'diliana',
					'dilma',
					'dilsa',
					'dina',
					'dinah',
					'dinarda',
					'dinarta',
					'dineia',
					'dinora',
					'dioceia',
					'dione',
					'dioneia',
					'dionilde',
					'dionisia',
					'dirce',
					'dircea',
					'dircila',
					'disa',
					'ditza',
					'diva',
					'divina',
					'diza',
					'djamila',
					'djanira',
					'do ceu',
					'dolique',
					'dolores',
					'domenica',
					'domingas',
					'domitila',
					'domitilia',
					'dona',
					'donatila',
					'donzelia',
					'donzilia',
					'dora',
					'doralice',
					'dores',
					'doriana',
					'dorina',
					'dorinda',
					'dorine',
					'doris',
					'dorisa',
					'doroteia',
					'dorvalina',
					'dos anjos',
					'duartina',
					'dulce',
					'dulcelina',
					'dulcidia',
					'dulcimar',
					'dulcina',
					'dulcinea',
					'dulcineia',
					'dulia',
					'dunia',
					'earine',
					'eda',
					'ederia',
					'ediane',
					'edilene',
					'edileni',
					'edileny',
					'edileusa',
					'edileuza',
					'edilsa',
					'edilza',
					'edina',
					'edinalva',
					'edine',
					'edinea',
					'edineia',
					'edite',
					'edith',
					'edma',
					'edmara',
					'edmunda',
					'edna',
					'ednalva',
					'ednea',
					'edneia',
					'eduarda',
					'eduina',
					'efigenia',
					'eglantina',
					'elaine',
					'elana',
					'elba',
					'elca',
					'elda',
					'electra',
					'eleia',
					'eleine',
					'elen',
					'elena',
					'elenice',
					'elenir',
					'elenita',
					'eleonor',
					'eleonora',
					'elia',
					'eliana',
					'eliane',
					'elicia',
					'elida',
					'eliene',
					'elieny',
					'eliete',
					'elin',
					'elina',
					'eline',
					'elis',
					'elisa',
					'elisabeta',
					'elisabete',
					'elisabeth',
					'elisama',
					'elisandra',
					'elisangela',
					'eliseba',
					'elisete',
					'elisia',
					'eliz',
					'eliza',
					'elizabete',
					'elizabeth',
					'elizabethe',
					'elizandra',
					'elizangela',
					'elizete',
					'elizeth',
					'ellen',
					'elma',
					'elmira',
					'eloa',
					'elodia',
					'eloisa',
					'eloiza',
					'elsa',
					'elsinda',
					'elsira',
					'eluina',
					'elva',
					'elvina',
					'elvira',
					'elza',
					'elzira',
					'ema',
					'emanuela',
					'emidia',
					'emilia',
					'emiliana',
					'encarnacao',
					'enedina',
					'engelecia',
					'engracia',
					'enia',
					'enide',
					'enilda',
					'eola',
					'eponina',
					'ercilia',
					'erica',
					'erika',
					'eris',
					'ermelinda',
					'ermengarda',
					'ermeria',
					'ernestina',
					'ersilia',
					'esmenia',
					'esmeralda',
					'esmeria',
					'especiosa',
					'esperanca',
					'estefana',
					'estefania',
					'estela',
					'estelita',
					'ester',
					'esther',
					'estrela',
					'etel',
					'etelca',
					'etelvina',
					'eteria',
					'eudora',
					'eufemia',
					'eugenia',
					'eulalia',
					'eularina',
					'eulina',
					'eunice',
					'eurica',
					'euridice',
					'eutalia',
					'euza',
					'eva',
					'evandra',
					'evangelina',
					'evangelista',
					'evanilda',
					'evelina',
					'eveline',
					'evila',
					'ezequiela',
					'fabia',
					'fabiana',
					'fabiane',
					'fabiani',
					'fabiany',
					'fabiola',
					'fabricia',
					'fani',
					'fania',
					'fantina',
					'fara',
					'farida',
					'fatima',
					'feba',
					'febe',
					'fedora',
					'fedra',
					'felicia',
					'feliciana',
					'felicidade',
					'felisbela',
					'felisberta',
					'felisbina',
					'felismina',
					'fernanda',
					'fernandina',
					'fiama',
					'fidelia',
					'filena',
					'filipa',
					'filomena',
					'fiona',
					'firmina',
					'flaminia',
					'flavia',
					'flaviane',
					'flor de ceres',
					'flor de maria',
					'flor',
					'flora',
					'florbela',
					'florenca',
					'florencia',
					'florentina',
					'floria',
					'floriana',
					'florinda',
					'floripes',
					'florisa',
					'florisbela',
					'formosa',
					'formosinda',
					'franca',
					'franciane',
					'francilia',
					'francina',
					'francine',
					'francisca',
					'frederica',
					'gabi',
					'gabriela',
					'gaela',
					'gaia',
					'gail',
					'gala',
					'gardela',
					'geane',
					'geani',
					'geisa',
					'geiza',
					'genciana',
					'generosa',
					'genesia',
					'genilda',
					'genoveva',
					'geny',
					'georgeta',
					'georgete',
					'georgia',
					'georgina',
					'geovana',
					'geralda',
					'geraldina',
					'gercina',
					'gerda',
					'germana',
					'gersina',
					'gerta',
					'gertrudes',
					'gerusa',
					'geruza',
					'giana',
					'gilberta',
					'gilceia',
					'gilcelia',
					'gilcilene',
					'gilcimara',
					'gilda',
					'gildete',
					'gileade',
					'gilma',
					'gilselia',
					'gina',
					'gioconda',
					'giovana',
					'giraldina',
					'girel',
					'girlene',
					'gisela',
					'giselda',
					'gisele',
					'giseli',
					'giselle',
					'giselli',
					'gisete',
					'gislaine',
					'gislena',
					'gislene',
					'glaucia',
					'glenda',
					'glicia',
					'glicinia',
					'gloria',
					'gloriosa',
					'goncala',
					'gonzaga',
					'goreti',
					'graca',
					'gracia',
					'graciana',
					'graciela',
					'graciete',
					'graciliana',
					'gracinda',
					'graciosa',
					'gravelina',
					'graziela',
					'graziele',
					'graziella',
					'grazielle',
					'gregoria',
					'greta',
					'grimanesa',
					'guadalupe',
					'guendolina',
					'guida',
					'guilhermina',
					'guimar',
					'guiomar',
					'guislena',
					'haide',
					'halia',
					'hebe',
					'heda',
					'hedila',
					'hedviges',
					'helaine',
					'helda',
					'helen',
					'helena',
					'helenice',
					'helga',
					'heli',
					'helia',
					'heliana',
					'heliodora',
					'hellen',
					'heloisa',
					'henriqueta',
					'herenia',
					'herica',
					'hermana',
					'hermania',
					'hermenegilda',
					'herminia',
					'hersilia',
					'hilaria',
					'hilda',
					'hilma',
					'honorina',
					'hortense',
					'hortensia',
					'hosana',
					'hozana',
					'iana',
					'ianesis',
					'iara',
					'iasmin',
					'iasmina',
					'iberina',
					'ida',
					'idalete',
					'idalia',
					'idalina',
					'idelia',
					'idilia',
					'idrisse',
					'ieda',
					'ifigenia',
					'igelcemina',
					'ignez',
					'ilca',
					'ilda',
					'ildete',
					'ilidia',
					'ilma',
					'ilsa',
					'ilse',
					'ilundi',
					'ilza',
					'ima',
					'inacia',
					'indaleta',
					'india',
					'indira',
					'ines',
					'inez',
					'inga',
					'ingeburga',
					'ingrid',
					'ingride',
					'inocencia',
					'inoi',
					'iolanda',
					'ionara',
					'ione',
					'ioque',
					'iracema',
					'iraci',
					'iracilda',
					'iracy',
					'irais',
					'irani',
					'irene',
					'ireneia',
					'ireny',
					'iria',
					'iriana',
					'irina',
					'irineia',
					'iris',
					'irisalva',
					'irma',
					'isa',
					'isabel',
					'isabela',
					'isabelina',
					'isadora',
					'isalda',
					'isalia',
					'isalina',
					'isaltina',
					'isaura',
					'isaura',
					'isaurinda',
					'isidora',
					'isilda',
					'isis',
					'ismalia',
					'isolda',
					'isolete',
					'isolina',
					'iva',
					'ivana',
					'ivanete',
					'ivaneth',
					'ivani',
					'ivania',
					'ivanilda',
					'ivanilde',
					'ivanildi',
					'ivanoela',
					'ivany',
					'ivete',
					'ivone',
					'ivonete',
					'ivoneth',
					'iza',
					'izabel',
					'izaura',
					'jaciara',
					'jacimara',
					'jacinta',
					'jacira',
					'jackelaine',
					'jackeleine',
					'jacqueline',
					'jalmira',
					'jamila',
					'jamilia',
					'janaina',
					'jandira',
					'jane',
					'janete',
					'jani',
					'jania',
					'janice',
					'janina',
					'janine',
					'jaquelina',
					'jaqueline',
					'jasmina',
					'jeane',
					'jeanete',
					'jeani',
					'jeany',
					'jeni',
					'jenifer',
					'jerusa',
					'jessica',
					'jezabel',
					'jil',
					'jitendra',
					'jo',
					'joana',
					'joanina',
					'joaninha',
					'joaquina',
					'jocelia',
					'jocelina',
					'jocimara',
					'joela',
					'joele',
					'joelma',
					'joice',
					'joraci',
					'jordana',
					'jorgina',
					'jorja',
					'josabete',
					'joscelina',
					'joseane',
					'josefa',
					'josefina',
					'joselene',
					'joselia',
					'joselina',
					'joselita',
					'josete',
					'josiana',
					'josiane',
					'josilene',
					'josimara',
					'josina',
					'josselina',
					'josuana',
					'jovelina',
					'joziane',
					'jucelia',
					'jucilene',
					'jucileni',
					'jucileny',
					'judite',
					'judith',
					'julia',
					'juliana',
					'julieta',
					'julinda',
					'julita',
					'juna',
					'junia',
					'juraci',
					'jurema',
					'juscelia',
					'jussara',
					'justa',
					'justina',
					'juvita',
					'karen',
					'karina',
					'karine',
					'karla',
					'katia',
					'katiane',
					'katiani',
					'katiany',
					'katie',
					'keila',
					'kelen',
					'keli',
					'kellen',
					'kelly',
					'kely',
					'keni',
					'kenia',
					'kenni',
					'kenny',
					'keny',
					'keyla',
					'kyara',
					'laila',
					'laira',
					'lais',
					'lana',
					'lara',
					'larissa',
					'laudiceia',
					'laura',
					'laureana',
					'laurina',
					'laurinda',
					'laurine',
					'laurita',
					'lavinia',
					'lea',
					'leandra',
					'leanor',
					'leci',
					'lecy',
					'leda',
					'lediane',
					'leena',
					'leia',
					'leida',
					'leide',
					'leidiane',
					'leila',
					'leiliane',
					'leiliani',
					'lena',
					'leni',
					'lenia',
					'lenilda',
					'lenir',
					'lenira',
					'lenita',
					'leny',
					'leocadia',
					'leolina',
					'leomenia',
					'leonardina',
					'leone',
					'leonia',
					'leonice',
					'leonida',
					'leonidia',
					'leonila',
					'leonilda',
					'leonilde',
					'leonilia',
					'leonisa',
					'leonor',
					'leonora',
					'leontina',
					'leopoldina',
					'leta',
					'leticia',
					'letizia',
					'leureni',
					'levina',
					'lia',
					'liana',
					'liane',
					'lianor',
					'liberalina',
					'liberdade',
					'liberia',
					'libertaria',
					'libia',
					'lici',
					'licia',
					'licinia',
					'lidia',
					'lidiana',
					'lidiane',
					'liduina',
					'liege',
					'liete',
					'ligia',
					'lila',
					'lilia',
					'liliam',
					'lilian',
					'liliana',
					'liliane',
					'liliete',
					'lilite',
					'lina',
					'linda',
					'lindalva',
					'lindaura',
					'lindinalva',
					'lineia',
					'linete',
					'linton',
					'lira',
					'lis',
					'lisa',
					'lisana',
					'lisandra',
					'lisdalia',
					'liseta',
					'lisete',
					'livia',
					'liz',
					'lizelia',
					'lizi',
					'lizie',
					'loela',
					'loide',
					'lolia',
					'loredana',
					'lorena',
					'loreta',
					'lorina',
					'lorine',
					'lotus',
					'lourdes',
					'lourenca',
					'lua',
					'luamar',
					'luana',
					'lubelia',
					'lucelena',
					'luceli',
					'lucelia',
					'lucelinda',
					'lucena',
					'lucete',
					'luci',
					'lucia',
					'lucialina',
					'luciana',
					'luciane',
					'luciene',
					'lucileine',
					'lucilene',
					'lucilia',
					'lucilina',
					'lucimar',
					'lucimara',
					'lucimere',
					'lucina',
					'lucinda',
					'lucinea',
					'lucineia',
					'lucineide',
					'lucineidi',
					'lucinete',
					'lucineth',
					'luciola',
					'lucrecia',
					'lucy',
					'ludimila',
					'ludmila',
					'luela',
					'luena',
					'luisa',
					'luisete',
					'luiza',
					'luizete',
					'lumena',
					'luna',
					'lurdes',
					'lurdite',
					'lusa',
					'lutgarda',
					'luz',
					'luzia',
					'luziane',
					'luziane',
					'luziani',
					'luzimara',
					'luzinete',
					'luzineth',
					'luzinira',
					'lydia',
					'mabilda',
					'mabilia',
					'macati',
					'madalena do carmo',
					'madalena',
					'madel',
					'madre de deus',
					'mafalda',
					'magali',
					'magaline',
					'magda',
					'magna',
					'magnolia',
					'magui',
					'maia',
					'maiara',
					'maira',
					'maisa',
					'maite',
					'malena',
					'malvina',
					'manoela',
					'manuela',
					'mar',
					'mara',
					'maraise',
					'maraize',
					'marcela',
					'marcelina',
					'marcella',
					'marcelle',
					'marcia',
					'marciana',
					'marcilene',
					'marcileni',
					'marcileny',
					'marcilia',
					'margareta',
					'margarete',
					'margareth',
					'margarida',
					'marguerita',
					'maria antonieta',
					'maria arlete',
					'maria chantal',
					'maria da aleluia',
					'maria da assuncao',
					'maria da bonanca',
					'maria da guia',
					'maria da liberdade',
					'maria da paixao',
					'maria da paz',
					'maria da penha',
					'maria da pureza',
					'maria da saude',
					'maria da trindade',
					'maria das dores',
					'maria das gracas',
					'maria das neves',
					'maria david',
					'maria de begonha',
					'maria de belem',
					'maria de betania',
					'maria de deus',
					'maria de fatima',
					'maria de guadalupe',
					'maria de ines',
					'maria de jesus',
					'maria de la salete',
					'maria de lurdes',
					'maria de monserrate',
					'maria de sao jose',
					'maria de sao pedro',
					'maria de silmenho',
					'maria de vandoma',
					'maria delce',
					'maria do carmo',
					'maria do castelo',
					'maria do livramento',
					'maria do loreto',
					'maria do mar',
					'maria do pilar',
					'maria do sacramento',
					'maria do salvador',
					'maria do sameiro',
					'maria do sinai',
					'maria do souto',
					'maria do vale',
					'maria dos anjos',
					'maria dos prazeres',
					'maria dos remedios',
					'maria dos santos',
					'maria estrela',
					'maria flor',
					'maria gabriel',
					'maria goreti',
					'maria imperia',
					'maria joanina',
					'maria joel',
					'maria laginha',
					'maria lua',
					'maria maior',
					'maria mar',
					'maria maria',
					'maria natal',
					'maria perfeita',
					'maria raul',
					'maria victoria',
					'maria',
					'mariamar',
					'mariame',
					'marian ',
					'mariana de jesus',
					'mariana do carmo',
					'mariana lua',
					'mariana',
					'mariangela',
					'maribel',
					'mariela',
					'mariema',
					'mariene',
					'marieni',
					'marienusa',
					'marienuza',
					'marieta',
					'marilda',
					'marileia',
					'marilena',
					'marilene',
					'marilete',
					'marileth',
					'marilia',
					'marilina',
					'mariline',
					'marilio',
					'marilisa',
					'marilita',
					'marilsa',
					'mariluce',
					'marilucia',
					'mariluz',
					'marilza',
					'marina',
					'marinalva',
					'marineide',
					'marineidi',
					'marinela',
					'marines',
					'marinete',
					'marineth',
					'marinez',
					'marinha',
					'marisa',
					'marise',
					'marise',
					'marisela',
					'marisol',
					'maristela',
					'marita',
					'maritana',
					'maritila',
					'mariursa',
					'mariuza',
					'mariza',
					'marize',
					'marizete',
					'marjolene',
					'marleide',
					'marlene',
					'marlete',
					'marleth',
					'marli',
					'marlisa',
					'marlise',
					'marlita',
					'marluce',
					'marlucia',
					'marly',
					'marquesa',
					'marsilia',
					'marta veronica',
					'marta',
					'martha',
					'martina',
					'martinha',
					'marvia',
					'mary',
					'matilda',
					'matilde de jesus',
					'matilde de paula',
					'matilde',
					'matrosa',
					'maude',
					'maura',
					'mavelete',
					'mavilde',
					'mavilia',
					'maxima',
					'maximiliana',
					'mecia',
					'mecilia',
					'mei',
					'meiline',
					'meire',
					'melania',
					'melanie',
					'melida',
					'melina',
					'melinda',
					'melissa',
					'mercedes',
					'merces',
					'mercia',
					'mercilia',
					'merita',
					'merrita',
					'meyre',
					'mia',
					'micaela',
					'michela',
					'michele',
					'micheli',
					'micheline',
					'michelle',
					'michelly',
					'miguela',
					'mila',
					'milagre',
					'milagres',
					'milena',
					'milene',
					'militana',
					'militza',
					'miliza',
					'mimosa',
					'mina',
					'miquelina',
					'mira',
					'miraldina',
					'miralva',
					'miranda',
					'mirandolina',
					'mireie',
					'mirela',
					'miriam',
					'mirian',
					'mirtes',
					'modesta',
					'monia da luz',
					'monia',
					'monica',
					'morgana',
					'morgiana',
					'morian',
					'mourana',
					'muna',
					'muriela',
					'myrian',
					'naama',
					'nadeja',
					'nadia',
					'nadina',
					'nadine',
					'nadja',
					'naiara',
					'naida',
					'naide',
					'nail',
					'naima',
					'nair',
					'naisa',
					'nali',
					'nalini',
					'nami',
					'nanci',
					'nancy',
					'nanete',
					'nanina',
					'nara',
					'naraiana',
					'narcisa',
					'natacha',
					'natali',
					'natalia',
					'natalina',
					'natanaela',
					'natercia',
					'natividade',
					'nausica',
					'nazare',
					'nazaria',
					'nazarina',
					'neida',
					'neide',
					'neireide',
					'neise',
					'neiva',
					'neiza',
					'nelci',
					'nelcy',
					'neli',
					'nelia',
					'nelma',
					'nelsa',
					'nely',
					'nelza',
					'nelzi',
					'nelzy',
					'neotera',
					'nercia',
					'nessa',
					'neusa',
					'neuza',
					'neyla',
					'nicandra',
					'nice',
					'niceia',
					'nicia',
					'nicole',
					'nicoleta',
					'nicolina',
					'nidia',
					'nila',
					'nilce',
					'nilcea',
					'nilceia',
					'nilda',
					'nilma',
					'nilsa',
					'nilza',
					'nilzete',
					'nilzeth',
					'nina',
					'ninfa',
					'nirina',
					'nisa',
					'nise',
					'nisia',
					'nivalda',
					'nivia',
					'noa',
					'noame',
					'noelia',
					'noelma',
					'noemi',
					'noemia',
					'noiala',
					'nominanda',
					'norma',
					'norvinda',
					'nubia',
					'numenia',
					'nuna',
					'nureia',
					'nuria',
					'obdulia',
					'oceana',
					'ocilia',
					'ocridalina',
					'octavia',
					'odete',
					'odila',
					'odilia',
					'ofelia',
					'olalia',
					'olga',
					'olimpia',
					'olinda',
					'olivia',
					'omara',
					'ondina',
					'onelia',
					'onofria',
					'oriana',
					'orieta',
					'orlanda',
					'orlandina',
					'ornela',
					'orquidea',
					'oscarina',
					'osvalda',
					'osvaldina',
					'otelina',
					'otelinda',
					'otilia',
					'otilina',
					'oureana',
					'ozana',
					'palmira',
					'paloma',
					'pamela',
					'pandora',
					'papoila',
					'pascualina',
					'patricia',
					'patrocinia',
					'paula',
					'paulina',
					'paz',
					'pedrina',
					'pegui',
					'penelope',
					'penha',
					'perla',
					'perpetua',
					'persilia',
					'peta',
					'petra',
					'petula',
					'pia',
					'piedade',
					'pilar',
					'plinia',
					'poliana',
					'porciana',
					'prazeres',
					'precilia',
					'preciosa',
					'prisca',
					'priscila',
					'priscilla',
					'prudencia',
					'pulqueria',
					'pureza',
					'purificacao',
					'quaiela',
					'quar',
					'quelia',
					'quessia',
					'quirina',
					'quiteria',
					'rachel',
					'radija',
					'rafaela',
					'ragendra',
					'raimunda',
					'raissa',
					'ramna',
					'rania',
					'raquel',
					'raquelina',
					'raqueline',
					'rebeca',
					'regiane',
					'regiani',
					'regiany',
					'regina',
					'reina',
					'reinalda',
					'rejane',
					'rejany',
					'renata',
					'renilda',
					'riana',
					'ribca',
					'ricarda',
					'ricardina',
					'rita',
					'riva',
					'rivca',
					'roana',
					'roberta',
					'roena',
					'rogelia',
					'rogeria',
					'rolende',
					'romana',
					'romeia',
					'romilda',
					'romina',
					'romula',
					'rondina',
					'roquelina',
					'rosa',
					'rosali',
					'rosalia',
					'rosalina',
					'rosalinda',
					'rosamar',
					'rosana',
					'rosandra',
					'rosane',
					'rosanete',
					'rosangela',
					'rosani',
					'rosania',
					'rosarinho',
					'rosario',
					'rosaura',
					'rose',
					'roseane',
					'rosebel',
					'rosebele',
					'roselene',
					'roseleni',
					'roseli',
					'roselia',
					'rosely',
					'rosemari',
					'rosemary',
					'rosemeire',
					'rosemere',
					'roseni',
					'roseny',
					'rosiana',
					'rosiane',
					'rosiene',
					'rosilane',
					'rosilda',
					'rosilea',
					'rosileia',
					'rosilene',
					'rosimari',
					'rosimary',
					'rosimeire',
					'rosimeiry',
					'rosimere',
					'rosimeri',
					'rosina',
					'rosinda',
					'rosineide',
					'rosinete',
					'rosineth',
					'rossana',
					'rosy',
					'rubia',
					'rubina',
					'rufina',
					'rute',
					'ruth',
					'sabina',
					'sabrina',
					'safia',
					'safira',
					'salete',
					'salima',
					'salma',
					'salome',
					'salomite',
					'saluquia',
					'salvacao',
					'salvadora',
					'salvina',
					'samanta',
					'samara',
					'samaritana',
					'samira',
					'sancha',
					'sancia',
					'sandra',
					'sandrina',
					'santa',
					'santana',
					'santina',
					'sara',
					'sarah',
					'sarai',
					'sarina',
					'sasquia',
					'sassia',
					'satia',
					'satira',
					'saula',
					'saulina',
					'sayonara',
					'scheila',
					'scheyla',
					'sebastiana',
					'sefora',
					'selena',
					'selene',
					'selenia',
					'selesa',
					'selesia',
					'selma',
					'semiramis',
					'senia',
					'seomara',
					'serafina',
					'serena',
					'serenela',
					'sesira',
					'severa',
					'severina',
					'sextina',
					'sheila',
					'sheyla',
					'shirlei',
					'shirlene',
					'shirleni',
					'shirleny',
					'shirley',
					'sibila',
					'sidneia',
					'sidonia',
					'silvana',
					'silvandira',
					'silvane',
					'silvani',
					'silvania',
					'silveria',
					'silvia',
					'silviana',
					'silvina',
					'simone',
					'simoneta',
					'simoni',
					'simony',
					'sinara',
					'sintia',
					'sira',
					'siria',
					'sirla',
					'sirlei',
					'sirleide',
					'sirlene',
					'sirley',
					'sofia',
					'sol',
					'solana',
					'solange',
					'soledade',
					'solene',
					'solimar',
					'solongia',
					'sonia',
					'soraia',
					'soraya',
					'stela',
					'stelina',
					'stella',
					'suati',
					'sueli',
					'suely',
					'sulamita',
					'suri',
					'susana',
					'suse',
					'susete',
					'susi',
					'suzana',
					'sylvia',
					'tabita',
					'taciana',
					'taina',
					'tais',
					'taisa',
					'taissa',
					'talia',
					'talita',
					'tamar',
					'tamara',
					'tanagra',
					'tania',
					'tarina',
					'tasia',
					'tatiana',
					'tatiane',
					'tatiani',
					'tatiany',
					'tejala',
					'telma',
					'teodora',
					'teresa',
					'teresca',
					'teresina',
					'teresinha',
					'tereza',
					'terezinha',
					'thais',
					'thays',
					'thereza',
					'therezinha',
					'tiara',
					'ticiana',
					'tirsa',
					'tirza',
					'tita',
					'titania',
					'tolentina',
					'tomasia',
					'traciana',
					'trasila',
					'tulipa',
					'umbelina',
					'urania',
					'urbalina',
					'urbiria',
					'ursiciana',
					'ursula',
					'ursulina',
					'vaisa',
					'valdete',
					'valdeth',
					'valdilene',
					'valdineia',
					'valdirene',
					'valdireni',
					'valdireny',
					'valentina',
					'valeria',
					'valesca',
					'valeska',
					'valiana',
					'valquiria',
					'vanda',
					'vanderleia',
					'vanessa',
					'vania',
					'vanilda',
					'vanilsa',
					'vanilza',
					'vanina',
					'vanusa',
					'vanuse',
					'vanuza',
					'velia',
					'velma',
					'veneranda',
					'venusa',
					'vera lis',
					'vera',
					'verdiana',
					'verena',
					'veronica',
					'vestina',
					'vicencia',
					'vicenta',
					'vicentina',
					'victoria',
					'vida',
					'vilma',
					'vinicia',
					'violante',
					'violeta',
					'violinda',
					'virgilia',
					'virginia',
					'virgolina',
					'vitalia',
					'vitalina',
					'vitoria',
					'vitorina',
					'vivalda',
					'vivelinda',
					'vivia',
					'viviam',
					'vivian',
					'viviana',
					'viviane',
					'vivilde',
					'vivina',
					'waldete',
					'waldeti',
					'waldiceia',
					'waldirene',
					'walesca',
					'waleska',
					'wallesca',
					'walleska',
					'wanda',
					'wanderleia',
					'wanessa',
					'wani',
					'wania',
					'wannessa',
					'wilma',
					'xenia',
					'xica',
					'ximena',
					'yara',
					'yasmin',
					'yeda',
					'yngrid',
					'yolanda',
					'yvonete',
					'zahra',
					'zaira',
					'zamy',
					'zara',
					'zarina',
					'zeferina',
					'zeli',
					'zelia',
					'zelinda',
					'zelita',
					'zena',
					'zenaida',
					'zenaide',
					'zeni',
					'zenia',
					'zenilda',
					'zenilda',
					'zenita',
					'zenite',
					'zenith',
					'zeny',
					'zila',
					'zilanda',
					'zilar',
					'zilda',
					'zildete',
					'zilia',
					'zilma',
					'zilmara',
					'zita',
					'zoa',
					'zobaida',
					'zora',
					'zoraida',
					'zubaida',
					'zubeida',
					'zulaia',
					'zuleica',
					'zuleide',
					'zuleika',
					'zuleima',
					'zulina',
					'zulmira',
					'zumira',
					'zunara',
					'zuneide',
		);	
	}

	/**
	* carrega todos os nomes masculinos
	*/
	private function loadmalenames(){
		$this->male_names = array(
				'aarao',
				'abdenago',
				'abdias',
				'abdul',
				'abediel',
				'abel',
				'abelamio',
				'abelardo',
				'abilio',
				'abner',
				'abraao',
				'abraham',
				'abraim',
				'abrao',
				'absalao',
				'abssilao',
				'acacio',
				'acilino',
				'acilio',
				'acursio',
				'adail',
				'adailton',
				'adair',
				'adalberto',
				'adalsindo',
				'adalsino',
				'adalto',
				'adalton',
				'adamantino',
				'adamastor',
				'adao',
				'adauto',
				'adeilson',
				'adeilton',
				'adeir',
				'adelindo',
				'adelmiro',
				'adelmo',
				'adelso',
				'adelson',
				'ademar',
				'ademilson',
				'ademir',
				'adeni',
				'adenilson',
				'adenir',
				'adeodato',
				'aderbaldo',
				'aderico',
				'aderio',
				'aderito',
				'adevaldo',
				'adiel',
				'adilio',
				'adilson',
				'adilton',
				'adimilson',
				'adir',
				'admilson',
				'admilson',
				'adner',
				'adolfo',
				'adonai',
				'adonias',
				'adonilo',
				'adonis',
				'adoracao',
				'adorino',
				'adriano',
				'adriel',
				'adroaldo',
				'adrualdo',
				'adruzilo',
				'aecio',
				'aelson',
				'afonsino',
				'afonso de sao luis',
				'afonso henriques',
				'afonso',
				'afranio',
				'afre',
				'africano',
				'agapito',
				'agenor',
				'agnaldo',
				'agnelo',
				'agostinho',
				'aguinaldo',
				'ailson',
				'ailton',
				'aires',
				'airton',
				'aitor',
				'aladino',
				'aladir',
				'alair',
				'alamiro',
				'alan',
				'alano',
				'alao',
				'alaor',
				'alarico',
				'albano',
				'alberico',
				'albertino',
				'alberto',
				'albino',
				'alcebiades',
				'alcemar',
				'alcemir',
				'alcenir',
				'alcibiades',
				'alcides',
				'alcimar',
				'alcindo',
				'alcino',
				'aldair',
				'aldeir',
				'aldemar',
				'aldemir',
				'alder',
				'alderico',
				'aldo dino',
				'aldo',
				'aldonio',
				'alecio',
				'alecsandro',
				'aleixo',
				'alesio',
				'alessandro',
				'aleu',
				'alex',
				'alexander',
				'alexandre',
				'alexandrino',
				'alexandro',
				'alexandro',
				'alexio',
				'alexis',
				'alexsander',
				'alexssander',
				'alexssandro',
				'alezio',
				'alfeu',
				'alfredo',
				'alipio',
				'alirio',
				'alisson',
				'alitio',
				'alito',
				'alivar',
				'allan',
				'almerindo',
				'almir',
				'almiro',
				'almirodo',
				'almurtao',
				'alois',
				'aloisio',
				'aloizio',
				'aloy',
				'alpoim',
				'altair',
				'altamir',
				'altamiro',
				'altino',
				'aluizio',
				'alvarim',
				'alvarino',
				'alvario',
				'alvaro',
				'alvimar',
				'alvino',
				'alysson',
				'amadeu',
				'amadis',
				'amado',
				'amador',
				'amancio',
				'amandio',
				'amantino',
				'amarildo',
				'amarilio',
				'amaro',
				'amauri',
				'amaury',
				'amavel',
				'ambrosio',
				'americo',
				'amilcar',
				'amilton',
				'aminadabe',
				'amor',
				'amorim',
				'amos',
				'anacleto',
				'anadir',
				'anael',
				'anaim',
				'analide',
				'anania',
				'ananias',
				'anastacio',
				'andersom',
				'anderson',
				'andre',
				'andreo',
				'andres',
				'anezio',
				'angelico',
				'angelo',
				'anibal',
				'aniceto',
				'anielo',
				'anilson',
				'anilton',
				'anisio',
				'anolido',
				'anselmo',
				'antao',
				'antelmo',
				'antenor',
				'antero',
				'antonino',
				'antonio',
				'aparicio',
				'apio',
				'apolinario',
				'apolo',
				'apolonio',
				'aprigio',
				'aquil',
				'aquila',
				'aquiles',
				'aquilino',
				'aquino',
				'aquira',
				'aramis',
				'arcadio',
				'arcanjo',
				'arcelino',
				'arcelio',
				'arcilio',
				'ardingue',
				'argemiro',
				'argentino',
				'argeu',
				'ari',
				'ariel',
				'arildo',
				'arilton',
				'arine',
				'ariosto',
				'arisberto',
				'aristeu',
				'aristides',
				'aristoteles',
				'arlindo',
				'armandino',
				'armando',
				'armelim',
				'armenio',
				'armindo',
				'arnaldo',
				'arnoldo',
				'aroldo',
				'aron',
				'arquibaldo',
				'arquimedes',
				'arquiminio',
				'arquimino',
				'arsenio',
				'artemio',
				'arthur',
				'artur',
				'ary',
				'ascenso',
				'asdrubal',
				'aselio',
				'aser',
				'assis',
				'ataide',
				'atanasio',
				'atao',
				'atila',
				'atilio',
				'attila',
				'attilio',
				'aubri',
				'augusto',
				'aureliano',
				'aurelino',
				'aurelio',
				'aureo',
				'ausendo',
				'austrelino',
				'avelino',
				'aventino',
				'axel',
				'aylton',
				'ayres',
				'ayrton',
				'azelio',
				'aziz',
				'azuil',
				'baguandas',
				'balbino',
				'baldemar',
				'baldomero',
				'balduino',
				'baltasar',
				'baptista',
				'baqui',
				'barac',
				'barao',
				'barbaro',
				'barcino',
				'barnabe',
				'bartolomeu perestrelo',
				'bartolomeu',
				'basilio',
				'bassarme',
				'batista',
				'bebiano',
				'belarmino',
				'belchior',
				'belisario',
				'belmiro',
				'bendavid',
				'benedito',
				'benevenuto',
				'benicio',
				'benito',
				'benjamim',
				'bento',
				'benvindo',
				'berardo',
				'berilo',
				'bernardim',
				'bernardino',
				'bernardo',
				'bertil',
				'bertino',
				'berto',
				'bertoldo',
				'bertolino',
				'betino',
				'beto',
				'bianor',
				'bibiano',
				'boanerges',
				'boaventura',
				'boavida',
				'bonifacio',
				'boris',
				'brandao',
				'bras',
				'braulio',
				'braz',
				'breno',
				'brian',
				'brigido',
				'briolanjo',
				'bruce',
				'brunno',
				'bruno',
				'caetano',
				'caico',
				'caio',
				'caleb',
				'calisto',
				'calvino',
				'camilo',
				'candido',
				'canto',
				'carlindo',
				'carlito',
				'carlitos',
				'carlo',
				'carlos',
				'carmelito',
				'carmelitto',
				'carmim',
				'casimiro',
				'cassiano',
				'cassio',
				'castelino',
				'castor',
				'catarino',
				'cecilio',
				'cedrico',
				'celestino',
				'celino',
				'celio',
				'celisio',
				'celsio',
				'celso',
				'celto',
				'cesar',
				'cesario',
				'cesaro',
				'cezar',
				'charbel',
				'charles',
				'christian',
				'christiano',
				'cicero',
				'cid',
				'cidalino',
				'cildo',
				'cilio',
				'cintio',
				'cipriano',
				'cirilo',
				'ciro',
				'clabson',
				'clarindo',
				'claro',
				'claudemir',
				'claudemiro',
				'claudenir',
				'claudinei',
				'claudiney',
				'claudio',
				'claudiomar',
				'claudionor',
				'clayton',
				'cleber',
				'cleberson',
				'cledson',
				'cleidimar',
				'cleidson',
				'clemencio',
				'clemente',
				'clemison',
				'cleomar',
				'clerio',
				'clesio',
				'cleto',
				'cleverson',
				'clidio',
				'clife',
				'clodoaldo',
				'clodomiro',
				'cloves',
				'clovis',
				'conrado',
				'constancio',
				'constantino',
				'consulino',
				'corsino',
				'cosme',
				'crispim',
				'cristian',
				'cristiano',
				'cristofe',
				'cristoforo',
				'cristovao',
				'cursino',
				'custodio',
				'dacio',
				'dalton',
				'dalvo',
				'damas',
				'damasceno',
				'damiao',
				'daniel',
				'danilo',
				'dante',
				'darcio',
				'darcy',
				'dario',
				'darlan',
				'darli',
				'darly',
				'davi',
				'david de assis',
				'david santa cruz',
				'david',
				'davide',
				'decimo',
				'decio',
				'degmar',
				'deivid',
				'dejair',
				'dejalme',
				'delcio',
				'delfim',
				'delfino',
				'delio',
				'delmano',
				'delmar',
				'delmiro',
				'delson',
				'demetrio',
				'demeval',
				'denair',
				'dener',
				'denil',
				'denilson',
				'denis',
				'deodato',
				'deolindo',
				'deraldo',
				'dercio',
				'derli',
				'derly',
				'deusdedito',
				'devair',
				'devani',
				'devanir',
				'dhruva',
				'diamantino',
				'didaco',
				'diego',
				'dieter',
				'dilan',
				'dilermando',
				'dilson',
				'dimas',
				'dinarte',
				'dinis',
				'dino',
				'diogenes',
				'diogo',
				'diomar',
				'diones',
				'dionisio',
				'dirceu',
				'dirio',
				'dirque',
				'divino',
				'divo',
				'djalma',
				'djalme',
				'djalmo',
				'domingos',
				'dominico',
				'donaldo',
				'donato',
				'donzilio',
				'doriclo',
				'dorival',
				'dositeu',
				'douglas',
				'druso',
				'duarte',
				'duilio',
				'dulcinio',
				'dunio',
				'durbalino',
				'durval',
				'durvalino',
				'dylio',
				'eberardo',
				'edemilson',
				'edenilson',
				'eder',
				'ederson',
				'edesio',
				'edevaldo',
				'edezio',
				'edgar',
				'edgard',
				'edi',
				'edilson',
				'edimilson',
				'edinaldo',
				'edipo',
				'edir',
				'edison',
				'edival',
				'edivaldo',
				'edivan',
				'edmar',
				'edmero',
				'edmilson',
				'edmundo',
				'edmur',
				'ednaldo',
				'edo',
				'edsom',
				'edson',
				'eduardo',
				'eduartino',
				'eduino',
				'edval',
				'edvaldo',
				'edvan',
				'edvino',
				'egas',
				'egidio',
				'egil',
				'eladio',
				'elcio',
				'elder',
				'elderlucio',
				'eleazar',
				'elenilson',
				'elessandro',
				'eleuterio',
				'elgar',
				'eli',
				'eliab',
				'eliano',
				'elias',
				'eliel',
				'eliezer',
				'elimar',
				'elio',
				'elioenai',
				'eliomar',
				'eliseu',
				'elisiario',
				'elisio',
				'elizeu',
				'elmano',
				'elmar',
				'elmer',
				'eloi',
				'eloir',
				'elpidio',
				'elsio',
				'elson',
				'elton',
				'elvino',
				'elvis',
				'ely',
				'elzeario',
				'elzo',
				'emanoel',
				'emanuel',
				'emaus',
				'emerson',
				'emidio',
				'emiliano',
				'emilio',
				'emo',
				'eneas',
				'eneias',
				'enes',
				'engracio',
				'enildo',
				'enilson',
				'enio',
				'enoque',
				'eny',
				'enzo',
				'eraldo',
				'erasmo',
				'ercilio',
				'eric',
				'erick',
				'erico',
				'erik',
				'erildo',
				'erique',
				'erivaldo',
				'erivelton',
				'erli',
				'erlon',
				'erly',
				'ermiterio',
				'ernandes',
				'ernani',
				'ernesto',
				'esau',
				'esequias',
				'esmeraldo',
				'estanislau',
				'estefanio',
				'estefano',
				'estelio',
				'estevao',
				'etevaldo',
				'euclides',
				'eudes',
				'eugenio',
				'euler',
				'eulogio',
				'eurico',
				'eusebio',
				'eustacio',
				'eustaquio',
				'evaldo',
				'evando',
				'evandro',
				'evangelino',
				'evani',
				'evanildo',
				'evanilton',
				'evaristo',
				'evelacio',
				'evelasio',
				'evelio',
				'evencio',
				'everaldo',
				'everardo',
				'everson',
				'everton',
				'ewerton',
				'expedito',
				'ezequias',
				'ezequiel',
				'ezio',
				'fabiano',
				'fabio',
				'fabricio',
				'fagner',
				'falcao',
				'falco',
				'faustino',
				'fausto',
				'feliciano',
				'felicio',
				'felicissimo',
				'felipe',
				'felipi',
				'felisberto',
				'felismino',
				'felix',
				'feliz',
				'ferdinando',
				'fernandino',
				'fernando',
				'fernao de magalhaes',
				'fernao',
				'ferrer',
				'fidelio',
				'filemon',
				'filino',
				'filinto',
				'filipe',
				'filipe',
				'filipi',
				'filipo',
				'filomeno',
				'filoteu',
				'firmino',
				'firmo',
				'flavio',
				'florentino',
				'floriano',
				'florisvaldo',
				'florival',
				'fortunato',
				'fradique',
				'francisco',
				'franclim',
				'franco',
				'frank',
				'franklim',
				'franklin',
				'franklino',
				'fred',
				'frede',
				'frederico',
				'fredo',
				'fulgencio',
				'fulvio',
				'gabinio',
				'gabino',
				'gabriel',
				'galiano',
				'galileu',
				'gamaliel',
				'garcia',
				'garibaldo',
				'gascao',
				'gaspar',
				'gaudencio',
				'gavio',
				'gean',
				'gedeao',
				'gelcimar',
				'gelson',
				'genair',
				'geneci',
				'genecy',
				'genesio',
				'genildo',
				'genilson',
				'genivaldo',
				'gentil',
				'george',
				'georgino',
				'geovane',
				'geovani',
				'geraldino',
				'geraldo',
				'gerardo',
				'gerberto',
				'gercino',
				'germano',
				'geronimo',
				'gersao',
				'gersino',
				'gerson',
				'gervasio',
				'gesse',
				'gessi',
				'gessy',
				'getulio',
				'giani',
				'gil',
				'gilberto',
				'gilcimar',
				'gildasio',
				'gildazio',
				'gildo',
				'gileade',
				'gilmar',
				'gilmarques',
				'gilson',
				'gilton',
				'gilvan',
				'gimeno',
				'ginestal',
				'gino',
				'giovane',
				'giovani',
				'giovanni',
				'girao',
				'giuliano',
				'givaldo',
				'gladson',
				'gladys',
				'glaucia',
				'glaucio',
				'glauco',
				'gledson',
				'gleidison',
				'gleidson',
				'godofredo',
				'goma',
				'goncalo',
				'graciano',
				'graciliano',
				'gracio',
				'gregorio',
				'guadalberto',
				'gualdim',
				'gualter',
				'guarani',
				'gueir',
				'gui',
				'guido',
				'guildo',
				'guilherme',
				'guilhermino',
				'guimar',
				'gumersindo',
				'gumesindo',
				'gusmao',
				'gustavo',
				'gutemberg',
				'guterre',
				'habacuc',
				'habacuque',
				'hamilton',
				'haraldo',
				'haroldo',
				'hazael',
				'heber',
				'hebert',
				'heitor',
				'helber',
				'heldemaro',
				'helder',
				'helderlucio',
				'heldo',
				'heleno',
				'helier',
				'helio',
				'heliodoro',
				'heliomar',
				'helmut',
				'helvecio',
				'helvio',
				'hemaxi',
				'hemeterio',
				'hemiterio',
				'henoch',
				'henri',
				'henrique',
				'henry',
				'heraldo',
				'herbert',
				'herberto',
				'herculano',
				'hercules',
				'heredio',
				'heriberto',
				'herlander',
				'herman',
				'hermano',
				'hermenegildo',
				'hermes',
				'herminio',
				'hermiterio',
				'hernani',
				'herve',
				'heverton',
				'higino',
				'hilario',
				'hildeberto',
				'hildebrando',
				'hildegardo',
				'hilton',
				'hipolito',
				'hirondino',
				'holger',
				'homero',
				'honorato',
				'honorio',
				'horacio',
				'huberto',
				'hudson',
				'hugo',
				'humberto',
				'iag',
				'iago',
				'ian',
				'ianis',
				'iberico',
				'icaro',
				'idalecio',
				'idalio',
				'idario',
				'idavide',
				'idelso',
				'igor',
				'ildefonso',
				'ildo',
				'ilidio',
				'ilson',
				'ilton',
				'inacio',
				'indalecio',
				'indra',
				'indro',
				'infante',
				'ingo',
				'inocencio',
				'ioque',
				'iran',
				'irineu',
				'irmino',
				'isaac',
				'isac',
				'isael',
				'isai',
				'isaias',
				'isaltino',
				'isandro',
				'isaque',
				'isauro',
				'isidoro',
				'isidro',
				'isildo',
				'ismael',
				'isolino',
				'israel',
				'italo',
				'itamar',
				'iuri',
				'ivair',
				'ivaldo',
				'ivan',
				'ivanildo',
				'ivanoel',
				'iven',
				'ivo',
				'izaias',
				'izalino',
				'jabes',
				'jabim',
				'jaci',
				'jacinto',
				'jackson',
				'jaco',
				'jacob',
				'jacome',
				'jader',
				'jadilson',
				'jadir',
				'jael',
				'jailson',
				'jailton',
				'jaime',
				'jair',
				'jairo',
				'james',
				'jamim',
				'janai',
				'janardo',
				'janio',
				'janique',
				'jansenio',
				'januario',
				'jaque',
				'jaques',
				'jarbas',
				'jardel',
				'jasao',
				'jasmim',
				'jasson',
				'jayme',
				'jayro',
				'jean',
				'jeferson',
				'jefferson',
				'jeremias',
				'jeronimo',
				'jesse',
				'jesualdo',
				'jesus',
				'jetro',
				'joabe',
				'joaci',
				'joacir',
				'joao',
				'joaquim',
				'joas',
				'job',
				'jobson',
				'jocelino',
				'jociano',
				'jocimar',
				'joel',
				'joelson',
				'jofre',
				'john',
				'joilson',
				'jomar',
				'jonaci',
				'jonacir',
				'jonacy',
				'jonair',
				'jonas',
				'jonata',
				'jonatas',
				'jonatha',
				'jonathan',
				'jonathas',
				'jones',
				'joni',
				'jordano',
				'jordao',
				'jorge',
				'jorio',
				'joscelino',
				'jose',
				'josefino',
				'josefo',
				'joselindo',
				'joselino',
				'joselio',
				'joselito',
				'josemar',
				'josenildo',
				'josias',
				'josiel',
				'josimar',
				'josselino',
				'josue',
				'jovani',
				'jovelino',
				'jovenil',
				'jovito',
				'jozelio',
				'joziel',
				'juares',
				'juarez',
				'jucimar',
				'juda',
				'judas',
				'judson',
				'juliano',
				'juliao',
				'julio',
				'junio',
				'junior',
				'juno',
				'juracy',
				'jurandi',
				'jurandir',
				'juscelino',
				'juscimar',
				'justiniano',
				'justino',
				'juvenal',
				'juvenil',
				'juventino',
				'kennedi',
				'kennedy',
				'kevim',
				'klaus',
				'kleber',
				'ladislau',
				'lael',
				'laercio',
				'laertes',
				'laudelino',
				'laudir',
				'laureano',
				'laurenio',
				'laurentino',
				'lauriano',
				'laurindo',
				'lauro',
				'lazaro',
				'leandro',
				'leao',
				'leccio',
				'lecio',
				'lemuel',
				'lenio',
				'leo',
				'leoberto',
				'leomar',
				'leonardo',
				'leoncio',
				'leone',
				'leonel',
				'leonicio',
				'leonidas',
				'leonidio',
				'leonildo',
				'leopoldo',
				'levi',
				'levy',
				'liberal',
				'libertario',
				'liberto',
				'licidas',
				'liciniano',
				'licinio',
				'licio',
				'lidio',
				'lidorio',
				'ligio',
				'liliano',
				'lincoln',
				'lindemberg',
				'lindiomar',
				'lindomar',
				'lindorfo',
				'lindoro',
				'lineu',
				'lino',
				'linton',
				'lisandro',
				'lisuarte',
				'lito',
				'livramento',
				'lopo',
				'loreto',
				'lorival',
				'lotus',
				'lourenco',
				'lourival',
				'luca',
				'lucas',
				'luciano',
				'lucilio',
				'lucinio',
				'lucio',
				'ludgero',
				'ludovico',
				'ludovino',
				'luis',
				'luiz',
				'lupicino',
				'lutero',
				'luzimar',
				'luzio',
				'macario',
				'maciel',
				'madail',
				'madaleno',
				'madate',
				'madu',
				'magdo',
				'magno',
				'mago',
				'mair',
				'mamede',
				'manasses',
				'manel',
				'manoel',
				'manuel',
				'mapril',
				'marcelino',
				'marcello',
				'marcelo',
				'marcial',
				'marciano',
				'marciel',
				'marcilio',
				'marcio',
				'marco',
				'marcos',
				'marcus',
				'margarido',
				'mariano',
				'marilio',
				'marinaldo',
				'marinho',
				'marino',
				'mario',
				'marito',
				'marivaldo',
				'marlon',
				'marlos',
				'marolo',
				'martim',
				'martinho',
				'martiniano',
				'martino',
				'martins',
				'marto',
				'marvao',
				'marvio',
				'mateus',
				'matheus',
				'matias',
				'matio',
				'mauricio',
				'maurilio',
				'mauro',
				'max',
				'maximiliano',
				'maximino',
				'maximo',
				'maxuel',
				'maxwel',
				'maxwell',
				'mel',
				'melchior',
				'melco',
				'melquisedeque',
				'melvin',
				'mem',
				'mendo',
				'mesaque',
				'messias',
				'micael',
				'michel',
				'miguel',
				'milo',
				'milson',
				'milton',
				'mimon',
				'mimoso',
				'miqueias',
				'miquelina',
				'mirco',
				'miro',
				'mis',
				'misael',
				'moacir',
				'moacyr',
				'modesto',
				'moises',
				'mucio',
				'munir',
				'muriel',
				'murilo',
				'nabor',
				'nadege',
				'nadir',
				'nagib',
				'nailton',
				'naod',
				'narciso',
				'narselio',
				'nasser',
				'natalicio',
				'natalino',
				'natalio',
				'natanael',
				'nataniel',
				'natao',
				'natercio',
				'nazario',
				'neemias',
				'nelio',
				'nelmo',
				'nelson',
				'nembrode',
				'nemesio',
				'nemo',
				'nenrode',
				'neoteles',
				'neotero',
				'nereu',
				'nero',
				'nestor',
				'neutel',
				'neuton',
				'newton',
				'ney',
				'nicasio',
				'nichal',
				'nicodemos',
				'nicola',
				'nicolau',
				'nidio',
				'niete',
				'niger',
				'nil',
				'nildo',
				'nilo',
				'nilson',
				'nilton',
				'nino',
				'nisio',
				'nivaldo',
				'noah',
				'nobre',
				'noe',
				'noel',
				'nonato',
				'norberto',
				'norival',
				'normano',
				'nuno',
				'nurio',
				'oceano',
				'octaviano',
				'octavio',
				'odair',
				'odeberto',
				'odilon',
				'odin',
				'olavo',
				'oldair',
				'olegario',
				'olimpio',
				'olindo',
				'olinto',
				'olivar',
				'oliverio',
				'olivier',
				'olivio',
				'omar',
				'omer',
				'ondino',
				'onildo',
				'onofre',
				'orandino',
				'orencio',
				'orestes',
				'orlandino',
				'orlando',
				'orlindo',
				'orosio',
				'oscar',
				'oseas',
				'oseias',
				'osmano',
				'osmar',
				'osorio',
				'osvaldo',
				'oswaldo',
				'otacilio',
				'otavio',
				'otelo',
				'otniel',
				'oto',
				'otoniel',
				'otto',
				'ovidio',
				'ozeas',
				'ozeias',
				'ozenir',
				'oziel',
				'pablo',
				'pacal',
				'parcidio',
				'paris',
				'pascoal',
				'patricio',
				'patrick',
				'paulino',
				'paulo',
				'pedrino',
				'pedro',
				'pelaio',
				'peniel',
				'pepe',
				'pepio',
				'perfeito',
				'pericles',
				'perpetuo',
				'persio',
				'peterson',
				'pio',
				'pitagoras',
				'placido',
				'plinio',
				'policarpo',
				'pompeu',
				'porfirio',
				'pracidio',
				'priam',
				'priao',
				'primitivo',
				'primo',
				'principiano',
				'priteche',
				'procopio',
				'prospero',
				'prudencio',
				'querubim',
				'quevin',
				'quiliano',
				'quim',
				'quintino',
				'quirilo',
				'quirino',
				'quirio',
				'radamas',
				'rafael',
				'rafaelo',
				'rai',
				'raimundo',
				'ralfe',
				'ramberto',
				'ramiro',
				'ramon',
				'randolfo',
				'raphael',
				'raul',
				'raulino',
				'ravi',
				'raymundo',
				'reginaldo',
				'regino',
				'reinaldo',
				'reis',
				'remi',
				'remigio',
				'remizio',
				'renan',
				'renato',
				'rene',
				'renildo',
				'reno',
				'requerino',
				'ricardo',
				'richardson',
				'richarlison',
				'rigoberto',
				'rildo',
				'ringo',
				'riu',
				'rivelino',
				'robert',
				'roberto',
				'roberval',
				'robim',
				'robinson',
				'robson',
				'rodiney',
				'rodney',
				'rodolfo',
				'rodrigo',
				'rogelio',
				'roger',
				'rogerio',
				'roi',
				'rolando',
				'rolim',
				'romano',
				'romao',
				'romarigo',
				'romario',
				'romerio',
				'romero',
				'romeu',
				'romildo',
				'romulo',
				'ronald',
				'ronaldo',
				'ronan',
				'roney',
				'roni',
				'ronildo',
				'ronilson',
				'ronivaldo',
				'rony',
				'roosevelt',
				'roque',
				'roriz',
				'rosano',
				'rosario',
				'rosemar',
				'rosil',
				'rosival',
				'rosivaldo',
				'rossano',
				'rubem',
				'ruben',
				'rubens',
				'rubi',
				'rubim',
				'ruby',
				'rudesindo',
				'rudi',
				'rudiney',
				'rudney',
				'rudolfo',
				'rufino',
				'rui',
				'ruperto',
				'rupio',
				'rurique',
				'russel',
				'ruy',
				'sabino',
				'sacramento',
				'sadi',
				'sadraque',
				'sadrudine',
				'saladino',
				'salazar',
				'salemo',
				'sali',
				'salma',
				'salomao',
				'salustiano',
				'salustiniano',
				'salvacao',
				'salvador',
				'salviano',
				'samaritano',
				'samir',
				'samuel',
				'sancho',
				'sancler',
				'sandrino',
				'sandro',
				'sansao',
				'santana',
				'santelmo',
				'santiago',
				'santo',
				'santos',
				'sario',
				'satiro',
				'saul',
				'saulo',
				'sauro',
				'savio',
				'sebastiao',
				'secundino',
				'selesio',
				'seleso',
				'selmo',
				'senio',
				'serafim',
				'sereno',
				'sergio',
				'sertorio',
				'sesinando',
				'severiano',
				'severino',
				'severo',
				'siddartha',
				'sidinei',
				'sidiney',
				'sidnei',
				'sidney',
				'sidonio',
				'sidraque',
				'sifredo',
				'silas',
				'silvano',
				'silverio',
				'silvestre',
				'silviano',
				'silvio',
				'simao',
				'simauro',
				'simplicio',
				'sindulfo',
				'sinesio',
				'sinval',
				'sisenando',
				'sisinio',
				'sisnando',
				'sivaldo',
				'sivio',
				'sixto',
				'socrates',
				'soeiro',
				'solano',
				'sotero',
				'suraje',
				'susano',
				'sylvio',
				'taciano',
				'tacio',
				'tadeu',
				'talio',
				'tamiris',
				'tarcisio',
				'tarsicio',
				'tasso',
				'tatiano',
				'teliano',
				'telmo',
				'telo',
				'teobaldo',
				'teodemiro',
				'teodomiro',
				'teodoro',
				'teodosio',
				'teofilo',
				'teotonio',
				'tercio',
				'thiago',
				'tiago',
				'tiberio',
				'ticiano',
				'tierri',
				'timoteo',
				'tirso',
				'tito',
				'tobias',
				'toledo',
				'tomas',
				'tome',
				'toni',
				'torcato',
				'torquato',
				'trajano',
				'tristao',
				'tude',
				'tulio',
				'turgo',
				'ubaldo',
				'ubirajara',
				'ubiratan',
				'udo',
				'udson',
				'ueliton',
				'ulisses',
				'ulrico',
				'ulysses',
				'urbano',
				'urbino',
				'urias',
				'uriel',
				'urien',
				'vagner',
				'vaise',
				'valci',
				'valcir',
				'valcy',
				'valdeci',
				'valdecy',
				'valdemar',
				'valdemir',
				'valdemiro',
				'valdeni',
				'valdenir',
				'valdir',
				'valdivino',
				'valdnei',
				'valdo',
				'valdomiro',
				'valente',
				'valentim',
				'valentino',
				'valerio',
				'valgi',
				'valtair',
				'valter',
				'vander',
				'vanderlei',
				'vanderley',
				'vanderli',
				'vanderly',
				'vanderson',
				'vando',
				'vanildo',
				'vanio',
				'varo',
				'vasco',
				'venancio',
				'venceslau',
				'vendel',
				'ventura',
				'verdi',
				'vergilio',
				'veridiano',
				'verissimo',
				'vero',
				'verter',
				'vianei',
				'vicencio',
				'vicente',
				'victor',
				'vidalio',
				'vidaul',
				'vilar',
				'vilator',
				'vili',
				'vilmar',
				'vilson',
				'vinicio',
				'vinicios',
				'vinicius',
				'vinnicius',
				'virgilio',
				'virginio',
				'virgulino',
				'viriato',
				'vitaliano',
				'vitalio',
				'vitiza',
				'vito',
				'vitor',
				'vitorino',
				'vitorio',
				'vittorio',
				'vivaldo',
				'vladimir',
				'vladimiro',
				'wadson',
				'wagner',
				'wagno',
				'walace',
				'walber',
				'walcenir',
				'walcenyr',
				'walcimar',
				'walcymar',
				'waldeci',
				'waldecir',
				'waldecy',
				'waldecyr',
				'waldei',
				'waldeir',
				'waldemar',
				'waldemir',
				'waldemiro',
				'waldir',
				'waldivino',
				'waldnei',
				'waldney',
				'waldo',
				'waldomiro',
				'walker',
				'wallace',
				'walmir',
				'waltair',
				'walteir',
				'walter',
				'wander',
				'wanderlan',
				'wanderlei',
				'wanderley',
				'wanderly',
				'wanderson',
				'wandir',
				'wando',
				'wantuil',
				'wantuir',
				'warlen',
				'warley',
				'warley',
				'washington',
				'weber',
				'weberson',
				'webson',
				'wedson',
				'weksley',
				'wekslley',
				'welber',
				'welerson',
				'weligton',
				'welington',
				'welison',
				'welisson',
				'weliton',
				'welligton',
				'wellington',
				'welson',
				'welton',
				'wemerson',
				'wenceslau',
				'wendel',
				'wendell',
				'wender',
				'wenderson',
				'werles',
				'werley',
				'wescley',
				'weslei',
				'wesley',
				'weslley',
				'wesly',
				'western',
				'weversom',
				'whashigton',
				'wildes',
				'wildson',
				'wiler',
				'wilian',
				'willan',
				'willes',
				'william',
				'willian',
				'willians',
				'willie',
				'willie',
				'wilmar',
				'wilson',
				'wilton',
				'wladimir',
				'wolmar',
				'wuarley',
				'xavier',
				'xenio',
				'xenocrates',
				'xenon',
				'xerxes',
				'xico',
				'xisto',
				'yure',
				'yuri',
				'zacarias',
				'zaido',
				'zaquel',
				'zaqueu',
				'zara',
				'zarco',
				'zardilaque',
				'zeferino',
				'zelio',
				'zera',
				'zildo',
				'zilmar',
		);		
	}

}