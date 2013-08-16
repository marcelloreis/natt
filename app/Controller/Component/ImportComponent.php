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
	private static $startTime;

	public function __construct() {
	    $this->Log = ClassRegistry::init('Log');
	    $this->City = ClassRegistry::init('City');
	    $this->Address = ClassRegistry::init('Address');
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
					if(preg_match('/[ _-]ltda$|[ _-]me$|[ _-]sa$|[ _-]s\/a$/si', strtolower($this->clearName($name)))){
						$type = TP_CNPJ;
					}

					if(preg_match('/advogados|associados|industria|comercio|artigos/si', strtolower($this->clearName($name)))){
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
		* Remove qualquer caracter do nome que nao seja letras
		*/
		$name = ucwords(strtolower(trim(preg_replace('/[^a-zA-Z ]/si', '', $name))));

		return $name;
	}

	/**
	* Retorna o sexo da entidade
	*/
	public function getGender($text_gender, $type_doc, $name){
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
					* Aplica regras para tentar descobrir o sexo da entidade apartir do nome
					*/

					//Nomes que comecem com Maria
					if(preg_match('/^abigail|abna|acélia|acilina|açucena|ada|adalgisa|adália|adelaide|adélia|adelina|adelinda|ádila|adília|adosinda|adriana|afonsina|afra|africana|ágata|agna|agnes|agonia|águeda|aida|aidé|airiza|aixa|alaíde|alana|alba|alberta|albertina|albina|alcina|alcíone|alda|aldara|aldenir|aldenora|aldina|aldora|alegria|aleixa|aleta|alexa|alexandra|aléxia|alexina|aléxis|alfreda|ália|aliana|aliça|alice|alícia|alida|alina|aline|alisande|alita|alix|alma|almara|almerinda|almesinda|almira|altina|alva|alvarina|alzira|amada|amália|amanda|amandina|amara|amarílis|amélia|amelina|américa|amora|amorina|amorzinda|ana|ana arine|ana bela|ana da purificação|ana de são josé|ana do carmo|ana do mar|ana do rosário|ana flôr|ana lua|ana mar|ana rosário|ana viriato|anabel|anabela|anaíce|anaíde|anair|anaís|anaisa|anaísa|analdina|anália|analice|analisa|anamar|anastácia|anatilde|andrea|andreia|andreína|andrelina|andresa|ândria|anésia|ângela|angélica|angelina|ânia|aniana|anícia|aníria|anísia|anita|anquita|anteia|antera|antonela|antónia|antonieta|antonina|anunciação|anunciada|anuque|anusca|aparecida|apolónia|arabela|araci|aradna|argentina|ária|ariadna|ariadne|ariana|ariane|arinda|arlanda|arlete|arlinda|armanda|armandina|arménia|arminda|artemisa|artemísia|aruna|ásia|aspásia|assunção|assunta|astrid|astride|atenais|átina|audete|augusta|aura|áurea|aurélia|aureliana|aurete|aurora|ausenda|auta|auxília|ava|balbina|balduína|bárbara|bárbora|bartolina|basília|basilissa|beanina|beatriz|bebiana|bela|belarmina|belém|belina|belinda|belisa|belisária|belmira|benedita|benícia|benigna|benilde|benita|benjamina|benvinda|berengária|berenice|bernadete|bernardete|bérnia|berta|bertila|bertilde|bertina|betânia|bétia|betina|betsabé|bia|biana|bianca|bibiana|bibili|bijal|bina|bitia|blandina|blásia|bonifácia|branca|branca flor|brásia|brázia|brena|brenda|briana|brícia|brígida|brigite|briolanja|briosa|brizida|bruna|brunilde|cácia|cacilda|caetana|caia|calila|camélia|camila|candice|cândida|cânia|carela|cáren|cárin|carina|carisa|carísia|carissa|cárita|carla|carlinda|carlota|carmela|carmélia|carmelina|carmelinda|carmelita|cármen|carmezinda|carmina|carminda|carminho|carmo|carmorinda|carol|carole|carolina|carsta|cassandra|cássia|cassilda|casta|castelina|castorina|catalina|catarina|caterina|cátia|catila|catilina|cecília|celeste|célia|celina|celinia|celsa|cereja|ceres|cesaltina|cesária|cesarina|chantal|cheila|chema|cibele|cidália|cidalina|cidalisa|cinara|cínara|cinderela|cinira|cíntia|cipora|circe|círia|cirila|cizina|clara|clarina|clarinda|clarisse|claudemira|cláudia|claudiana|cleia|cleide|clélia|clemência|clementina|cléo|cleodice|cleonice|cleópatra|clésia|clícia|climénia|clívia|cloe|cloé|clorinda|clotilde|colete|conceição|concha|consolação|constança|constância|consuelo|cora|corália|coralina|cordélia|corina|córita|cornélia|cosete|cremilda|cremilde|crestila|cris|crisália|crisálida|crisanta|crisante|crisna|cristela|cristele|cristene|cristiana|cristina|cristolinda|custódia|dafne|dagmar|daina|daisi|dália|daliana|dalida|dalila|dalinda|dalva|dámaris|damiana|dana|dânia|daniana|daniela|danila|dara|darci|darcília|darlene|darnela|davina|davínia|de begonha|débora|décia|deise|dejanira|dele|delfina|délia|deliana|delisa|delmina|delminda|delmira|demelza|deméter|demétria|denisa|denise|deodata|deodete|deolinda|deonilde|deotila|deótila|derocila|diamantina|diana|dídia|didiana|digna|diliana|dilsa|dina|diná|dinarda|dinarta|dineia|dinora|dione|dionilde|dionísia|dirce|dircea|dircila|disa|ditza|diva|diza|djamila|do céu|dólique|dolores|domenica|domingas|domitila|domitília|dona|donatila|donzélia|donzília|dora|doralice|dores|doriana|dorina|dorinda|dorine|dóris|dorisa|doroteia|dos anjos|duartina|dulce|dulcelina|dulcídia|dulcina|dulcineia|dúlia|dúnia|earine|eda|edéria|edina|edine|edite|edith|edma|edmunda|edna|eduarda|eduina|eglantina|elana|elca|elda|electra|eleia|eleine|elena|eleonor|eleonora|élia|eliana|eliane|elícia|eliete|élin|elina|eline|elisa|elisabeta|elisabete|elisabeth|elisama|eliseba|elisete|elísia|elma|elmira|eloá|elodia|elódia|eloisa|elsa|elsinda|eluína|elva|elvina|elvira|elza|ema|emanuela|emídia|emília|emiliana|encarnação|engelécia|engrácia|énia|enide|enilda|éola|eponina|ercília|erica|érica|erika|éris|ermelinda|ermengarda|erméria|ernestina|ersília|esménia|esmeralda|esméria|especiosa|esperança|estéfana|estefânia|estela|ester|estrela|etel|étel|etelca|etelvina|etéria|eudora|eufémia|eugénia|eulália|eularina|eunice|eurica|eurídice|eutália|eva|evandra|evangelina|evangelista|evelina|eveline|évila|ezequiela|fábia|fabiana|fabíola|fabrícia|fani|fânia|fantina|fara|farida|fátima|feba|febe|fédora|fedra|felícia|feliciana|felicidade|felisbela|felisberta|felisbina|felismina|fernanda|fernandina|fiama|fidélia|filena|filipa|filomena|fiona|firmina|flamínia|flávia|flor|flor de ceres|flor de maria|flora|florbela|florença|florencia|florentina|flória|floriana|florinda|floripes|florisa|florisbela|formosa|formosinda|franca|francília|francina|francisca|frederica|gabi|gabriela|gaela|gaia|gáil|gala|gardela|geisa|genciana|generosa|genésia|genoveva|georgeta|georgete|geórgia|georgina|geraldina|gerda|germana|gerta|gertrudes|giana|gilberta|gilda|gileade|gilma|gina|gioconda|giovana|giraldina|girel|gisela|giselda|gisete|gislena|gislene|glaúcia|glenda|glicínia|glória|gloriosa|gonçala|gonzaga|goreti|graça|grácia|graciana|graciela|graciete|graciliana|gracinda|graciosa|gravelina|gregória|greta|grimanesa|guadalupe|guendolina|guida|guilhermina|guimar|guiomar|guislena|haidé|hália|hebe|heda|hédila|hedviges|helda|helena|helga|heli|hélia|heliana|heliodora|heloísa|henriqueta|herénia|hermana|hermânia|hermenegilda|hermínia|hersília|hilária|hilma|honorina|hortense|hortênsia|iana|ianesis|iara|iasmin|iasmina|iberina|ida|idalete|idália|idalina|idélia|idília|idrisse|ifigénia|igelcemina|ignez|ilca|ilda|ilídia|ilsa|ilse|ilundi|ima|inácia|indaleta|india|indira|inês|inga|ingeburga|íngride|inocência|inoi|iolanda|ionara|ioque|iracema|iráis|irene|ireneia|iria|iriana|irina|íris|irisalva|irma|isa|isabel|isabela|isabelina|isadora|isalda|isália|isalina|isaltina|isaura|isaurinda|isidora|isilda|isis|ismália|isolda|isolete|isolina|iva|ivana|ivânia|ivanoela|ivete|ivone|jacinta|jacira|jalmira|jamila|jamília|janaína|jandira|janete|jani|jânia|janice|janina|janine|jaquelina|jaqueline|jasmina|jeanete|jéni|jénifer|jerusa|jéssica|jezabel|jil|jitendra|jó|joana|joanina|joaninha|joaquina|jocelina|joela|joele|joelma|joice|joraci|jordana|jorgina|jorja|josabete|joscelina|josefa|joséfa|josefina|joselene|josélia|joselina|josete|josiana|josiane|josina|josselina|josuana|jovelina|judite|júlia|juliana|julieta|julinda|julita|juna|júnia|juraci|justa|justina|juvita|karen|katia|katie|kelly|kyara|laila|laira|lais|lana|lara|larissa|laura|laureana|laurina|laurina|laurinda|laurine|lavínia|lea|leandra|leanor|leena|leila|lénia|lenira|leocádia|leolina|leoménia|leonardina|leone|leónia|leonida|leonídia|leonila|leonilda|leonilde|leonília|leonisa|leonor|leonora|leontina|leopoldina|leta|letícia|letízia|levina|lia|liana|liane|lianor|liberalina|liberdade|libéria|libertária|líbia|lici|lícia|licínia|lídia|lidiana|liduína|liete|lígia|lila|lilá|lília|lilian|liliana|liliane|liliete|lilite|lina|linda|lineia|linete|línton|lira|lis|lisa|lisana|lisandra|lisdália|liseta|lisete|lívia|liz|lizélia|lízi|lízie|loela|loide|lólia|loredana|lorena|loreta|lorina|lorine|lótus|lourença|lua|luamar|luana|lubélia|lucélia|lucelinda|lucena|lucete|lúcia|lucialina|luciana|lucileine|lucília|lucilina|lucina|lucinda|lucíola|lucrécia|ludmila|luela|luena|luísa|luisete|luizete|lumena|luna|lurdes|lurdite|lusa|lutgarda|luz|luzia|luzinira|mabilda|mabília|macati|madalena|madalena do carmo|madel|madre de deus|mafalda|magali|magaline|magda|magna|magnólia|mágui|maia|maiara|maira|maísa|maitê|malena|malvina|manuela|mar|mara|marcela|marcelina|márcia|marciana|marcília|margareta|margarete|margarida|marguerita|maria|mária|maria antonieta|maria arlete|maria chantal|maria da aleluia|maria da assunção|maria da bonança|maria da guia|maria da liberdade|maria da paixão|maria da paz|maria da penha|maria da pureza|maria da saúde|maria da trindade|maria das dores|maria das graças|maria das neves|maria david|maria de begonha|maria de belém|maria de betânia|maria de deus|maria de fátima|maria de guadalupe|maria de inês|maria de jesus|maria de la salete|maria de lurdes|maria de monserrate|maria de são josé|maria de são pedro|maria de silmenho|maria de vandoma|maria delce|maria do carmo|maria do castelo|maria do livramento|maria do loreto|maria do mar|maria do pilar|maria do sacramento|maria do salvador|maria do sameiro|maria do sinai|maria do souto|maria do vale|maria dos anjos|maria dos prazeres|maria dos remédios|maria dos santos|maria estrela|maria flor|maria gabriel|maria goreti|maria impéria|maria joanina|maria joel|maria laginha|maria lua|maria maior|maria mar|maria maria|maria natal|maria perfeita|maria raul|maria victória|mariamar|mariame|marian |mariana|mariana de jesus|mariana do carmo|mariana lua|maribel|mariela|mariema|marieta|marilda|marília|marilina|mariline|marílio|marilisa|marilita|marilúcia|mariluz|marina|marinela|marinha|marisa|marise|marisela|marisol|marita|maritana|maritila|marizete|marjolene|marlene|marli|marlisa|marlise|marlita|marquesa|marsília|marta|marta verónica|martina|martinha|márvia|matilda|matilde|matilde de jesus|matilde de paula|matrosa|maude|maura|mavelete|mavilde|mavília|máxima|maximiliana|mécia|mecília|mei|meiline|melânia|mélanie|melida|melina|melinda|melissa|mercedes|mercês|mércia|mercília|merita|merrita|mia|micaela|miguela|mila|milagre|milagres|milena|milene|militana|militza|miliza|mimosa|mina|miquelina|mira|miraldina|miranda|mirandolina|mireie|mirela|miriam|modesta|mónia|mónia da luz|mónica|morgana|morgiana|morian|mourana|muna|muriela|naama|nádeja|nádia|nadina|nadine|nadja|naiara|naida|naíde|naíl|naíma|nair|naísa|nali|nalini|nami|nânci|nancy|nanete|nanina|nara|naraiana|narcisa|natacha|natali|natália|natalina|natanaela|natércia|natividade|nausica|nazaré|nazária|nazarina|neide|neireide|neise|neiza|neli|nélia|nelma|nelsa|nely|neotera|nércia|nessa|neuza|nicandra|nice|niceia|nícia|nicole|nicoleta|nicolina|nídia|nila|nilce|nilda|nilsa|nilza|nina|ninfa|nirina|nisa|nise|nísia|nivalda|noa|noame|noélia|noelma|noemi|noémi|noémia|noiala|nominanda|norma|norvinda|núbia|numénia|nuna|nureia|núria|obdúlia|oceana|ocília|ocridalina|octávia|odete|odília|ofélia|olália|olga|olímpia|olinda|olívia|omara|ondina|onélia|onófria|oriana|orieta|orlanda|orlandina|ornela|orquídea|oscarina|osvalda|osvaldina|otelina|otelinda|otília|otilina|oureana|palmira|paloma|pamela|pámela|pandora|papoila|pascualina|patrícia|patrocínia|paula|paulina|paz|pedrina|pégui|penélope|perla|perpétua|persília|peta|petra|petula|pia|piedade|pilar|plínia|poliana|porciana|prazeres|precília|preciosa|prisca|priscila|prudência|pulquéria|pureza|purificação|quaiela|quar|quélia|quessia|quirina|quitéria|radija|rafaela|ragendra|raissa|ramna|rania|raquel|raquelina|raqueline|rebeca|regina|reina|reinalda|renata|riana|ribca|ricarda|ricardina|rita|riva|rivca|roana|roberta|roena|rogélia|rolende|romana|romeia|romilda|romina|rómula|rondina|roquelina|rosa|rosália|rosalina|rosalinda|rosamar|rosana|rosandra|rosanete|rosarinho|rosário|rosaura|rosebel|rosebele|rosélia|rosina|rosinda|rossana|rúbia|rubina|rufina|rute|ruth|sabina|sabrina|safia|safira|salete|salima|salma|salomé|salomite|salúquia|salvação|salvadora|salvina|samanta|samara|samaritana|samira|sancha|sância|sandra|sandrina|santana|sara|sarah|sarai|sarina|sásquia|sássia|sátia|sátira|saula|saulina|sebastiana|séfora|selena|selene|selénia|selesa|selésia|selma|semíramis|sénia|seomara|serafina|serena|serenela|sesira|severa|severina|sextina|sheila|sibila|sidónia|silvana|silvandira|silvéria|sílvia|silviana|silvina|simone|simoneta|sira|síria|sirla|sofia|sol|solana|solange|soledade|solene|solôngia|sónia|soraia|stela|stelina|suati|suéli|sulamita|suri|susana|suse|susete|susi|tabita|taciana|taína|taís|taísa|taíssa|talia|talita|tamar|tamara|tamára|tanagra|tânia|tarina|tásia|tatiana|tejala|telma|teodora|teresa|teresca|teresina|teresinha|terezinha|tiara|ticiana|tirsa|tirza|tita|titânia|tolentina|tomásia|traciana|trasila|túlipa|umbelina|urânia|urbalina|urbiria|ursiciana|úrsula|ursulina|vaísa|valentina|valéria|valesca|valiana|valquíria|vanda|vanderleia|vanessa|vânia|vanina|vanusa|vanuza|vélia|velma|veneranda|venusa|vera|vera lis|verdiana|verena|verónica|vestina|vicência|vicenta|victória|vida|vilma|vinícia|violante|violeta|violinda|virgínia|virgolina|vitália|vitória|vitorina|vivalda|vivelinda|viviana|viviane|vivilde|vivina|xénia|xica|ximena|yara|yasmin|zahra|zaira|zamy|zara|zará|zarina|zeferina|zélia|zelinda|zena|zenaida|zenaide|zénia|zila|zilda|zília|zilma|zita|zoa|zobaida|zora|zoraida|zubaida|zubeida|zulaia|zuleica|zuleima|zulmira/si', strtolower($name))){
						$gender = FEMALE;
					}

					//nomes que comecem com maria
					if(preg_match('/^aarão|abdénago|abdias|abdul|abel|abelâmio|abelardo|abílio|abraão|abraim|abrão|absalão|abssilão|acácio|acilino|acílio|acúrsio|adail|adalberto|adalsindo|adalsino|adamantino|adamastor|adauto|adelindo|adelmiro|adelmo|ademar|ademir|adeodato|aderico|adério|adérito|adiel|adílio|adner|adolfo|adonai|adonias|adónias|adonilo|adónis|adoração|adorino|adriano|adriel|adrualdo|adruzilo|afonsino|afonso|afonso de são luís|afonso henriques|afrânio|afre|africano|agapito|agenor|agnelo|agostinho|aguinaldo|aires|airton|aitor|aladino|alamiro|alan|alano|alão|alarico|albano|alberico|albertino|alcibíades|alcides|alcindo|alcino|aldaír|aldemar|alder|aldo dino|aldónio|aleixo|aleu|alex|alexandre|alexandrino|alexandro|aléxio|aléxis|alfeu|alfredo|alípio|alírio|alítio|alito|alivar|almerindo|almiro|almirodo|almurtão|aloís|aloísio|alpoim|altino|alvarim|alvarino|alvário|álvaro|alvino|amadeu|amadis|amado|amador|amâncio|amândio|amarildo|amarílio|amaro|amauri|amável|ambrósio|américo|amílcar|aminadabe|amor|amorim|amós|anacleto|anael|anaim|analide|anania|ananias|anastácio|andré|andreo|andrés|angélico|ângelo|aníbal|aniceto|anielo|anísio|anolido|anselmo|antão|antelmo|antenor|antero|antonino|antónio|aparício|ápio|apolinário|apolo|apolónio|aprígio|aquil|aquila|áquila|aquiles|aquilino|aquino|aquira|aramis|arcádio|arcanjo|arcelino|arcélio|arcílio|ardingue|argemiro|argentino|ari|ariel|arine|ariosto|arisberto|aristides|aristóteles|arlindo|armandino|armando|armelim|arménio|armindo|arnaldo|arnoldo|aron|arquibaldo|arquimedes|arquimínio|arquimino|arsénio|artur|ary|ascenso|asdrúbal|asélio|áser|assis|ataíde|atanásio|atão|átila|aubri|augusto|aureliano|aurelino|áureo|ausendo|austrelino|avelino|aventino|axel|azélio|aziz|azuil|baguandas|balbino|baldemar|baldomero|balduíno|baltasar|baptista|baqui|barac|barão|bárbaro|barcino|barnabé|bartolomeu|bartolomeu perestrelo|basílio|bassarme|batista|bebiano|belarmino|belchior|belisário|belmiro|bendavid|benedito|benevenuto|benício|benjamim|bento|benvindo|berardo|berilo|bernardim|bernardino|bernardo|bertil|bertino|berto|bertoldo|bertolino|betino|beto|bianor|bibiano|boanerges|boaventura|boavida|bonifácio|bóris|brandão|brás|bráulio|breno|brian|brígido|briolanjo|bruce|bruno|caetano|caíco|caio|caleb|calisto|calvino|camilo|cândido|canto|carlo|carlos|carmim|casimiro|cassiano|cássio|castelino|castor|catarino|cecílio|cedrico|celestino|celino|célio|celísio|célsio|celso|celto|césar|cesário|césaro|charbel|cícero|cid|cidalino|cildo|cílio|cíntio|cipriano|cirilo|ciro|clarindo|claro|claudemiro|cláudio|clemêncio|clemente|clésio|clídio|clife|clodomiro|clóvis|conrado|constâncio|constantino|consulino|corsino|cosme|crispim|cristiano|cristofe|cristóforo|cristóvão|cursino|custódio|dácio|damas|damasceno|damião|daniel|danilo|dante|dárcio|dario|dário|davi|david|davide|david de assis|david santa cruz|décimo|décio|deivid|dejalme|délcio|delfim|delfino|délio|delmano|delmar|delmiro|demétrio|dener|denil|denis|deodato|deolindo|dércio|deusdedito|dhruva|diamantino|didaco|diego|dieter|dilan|dilermando|dimas|dinarte|dinis|dino|diogo|dionísio|dírio|dirque|divo|djalma|djalme|djalmo|domingos|domínico|donaldo|donato|donzílio|dóriclo|dositeu|druso|duarte|duílio|dulcínio|dúnio|durbalino|durval|durvalino|eberardo|eder|edgar|édi|édipo|edir|edmero|edmundo|edmur|edo|eduardo|eduartino|eduíno|edvaldo|edvino|egas|egídio|egil|eládio|eleazar|eleutério|elgar|eli|eliab|eliano|elias|eliezer|eliézer|élio|elioenai|eliseu|elisiário|elísio|elmano|elmar|elmer|elói|elpídio|élsio|élson|élton|elvino|elzeário|elzo|emanuel|emaús|emídio|emiliano|emílio|emo|eneias|enes|engrácio|enio|énio|enoque|enzo|erasmo|ercílio|eric|erico|érico|erik|erique|ermitério|ernâni|ernesto|esaú|esmeraldo|estanislau|estefânio|estéfano|estélio|estevão|estêvão|euclides|eugénio|eulógio|eurico|eusébio|eustácio|eustáquio|evaldo|evandro|evangelino|evaristo|evelácio|evelásio|evélio|evêncio|everaldo|everardo|expedito|ezequiel|fabrício|falcão|falco|faustino|fausto|feliciano|felício|felicíssimo|felisberto|felismino|félix|feliz|ferdinando|fernandino|fernando|fernão|fernão de magalhães|ferrer|fidélio|filémon|filino|filinto|filipe|filipo|filomeno|filoteu|firmino|firmo|flávio|florentino|floriano|florival|fradique|francisco|franclim|franco|franklim|franklin|franklino|fred|frede|frederico|fredo|fulgêncio|fúlvio|gabínio|gabino|gabriel|galiano|galileu|gamaliel|garcia|garibaldo|gascão|gaspar|gaudêncio|gávio|gedeão|genésio|gentil|georgino|geraldo|gerardo|gerberto|germano|gersão|gerson|gervásio|getúlio|giani|gil|gilberto|gildásio|gildo|gileade|gimeno|ginestal|gino|giovani|girão|glaúcia|godofredo|goma|gonçalo|graciano|graciliano|grácio|gregório|guadalberto|gualdim|gualter|guarani|gueir|gui|guido|guildo|guilherme|guilhermino|guimar|gumersindo|gumesindo|gusmão|gustavo|guterre|habacuc|habacuque|hamilton|haraldo|haroldo|hazael|héber|heitor|heldemaro|hélder|heldo|heleno|helier|hélio|heliodoro|hélmut|hélvio|hemaxi|hemetério|hemitério|henoch|henrique|heraldo|herberto|herculano|herédio|heriberto|herlander|hérman|hermano|hermenegildo|hermes|hermínio|hermitério|hernâni|hervê|higino|hilário|hildeberto|hildeberto|hildebrando|hildegardo|hipólito|hirondino|hólger|homero|honorato|honório|horácio|huberto|hugo|humberto|iag|iago|ian|ianis|ibérico|ícaro|idalécio|idálio|idário|idavide|idelso|igor|ildefonso|ildo|ilídio|inácio|indalécio|indra|indro|infante|ingo|inocêncio|ioque|irineu|irmino|isaac|isac|isael|isaí|isaías|isaltino|isandro|isaque|isauro|isidoro|isidro|isildo|ismael|isolino|israel|ítalo|iúri|ivaldo|ivan|ivanoel|íven|ivo|izalino|jabes|jabim|jacinto|jacó|jacob|jacob|jácome|jader|jadir|jaime|jair|jairo|james|jamim|janai|janardo|janique|jansénio|januário|jaque|jaques|jarbas|jardel|jasão|jasmim|jeremias|jerónimo|jessé|jesualdo|jesus|jetro|joabe|joão|joaquim|joás|job|jocelino|jociano|joel|jofre|jonas|jonatã|jónatas|jóni|jordano|jordão|jorge|jório|joscelino|josé|josefino|josefo|joselindo|joselino|josias|josselino|josué|jovelino|jovito|judá|judas|juliano|julião|júlio|júnio|juno|justiniano|justino|juvenal|juventino|kévim|ladislau|lael|laércio|laertes|laudelino|laureano|laurénio|laurentino|lauriano|laurindo|lauro|lázaro|leandro|leão|léccio|lécio|lemuel|lénio|leo|leoberto|leonardo|leôncio|leone|leonel|leonício|leónidas|leonídio|leonildo|leopoldo|liberal|libertário|liberto|lícidas|liciniano|licínio|lício|lídio|lidório|lígio|liliano|lindorfo|lindoro|lineu|lino|línton|lisandro|lisuarte|lito|livramento|lopo|loreto|lorival|lótus|lourenço|lourival|luca|lucas|luciano|lucílio|lucínio|lúcio|ludgero|ludovico|ludovino|luís|lupicino|lutero|luzio|macário|maciel|madail|madaleno|madate|madu|magdo|magno|mago|mair|mamede|manassés|manel|manuel|mapril|marcelino|marcelo|marcial|marcílio|márcio|marco|marcos|margarido|mariano|marílio|marinho|marino|mário|marito|marlon|márlon|marolo|martim|martinho|martiniano|martino|martins|marto|marvão|márvio|mateus|matias|mátio|maurício|mauro|max|maximiliano|maximino|máximo|mel|melchior|melco|melquisedeque|mélvin|mem|mendo|mesaque|messias|micael|miguel|milo|milton|mílton|mimon|mimoso|mimoso|miqueias|miquelina|mirco|miro|mis|misael|modesto|moisés|múcio|munir|muriel|murilo|nabor|nádege|nadir|naod|narsélio|nasser|natalício|natalino|natálio|natanael|nataniel|natão|natércio|nazário|nélio|nelmo|nelson|nélson|nembrode|nemésio|nemo|nenrode|neóteles|neotero|nereu|nero|nestor|neutel|nêuton|nicásio|nichal|nicodemos|nicola|nicolau|nídio|niete|níger|nil|nilo|nilson|nilton|nino|nísio|noah|nobre|noé|noel|nonato|norberto|norival|normano|nuno|núrio|oceano|octaviano|octávio|odair|odeberto|ódin|olavo|olegário|olímpio|olindo|olinto|olivar|olivério|olivier|omar|omer|ondino|onildo|onofre|orandino|orêncio|orestes|orlandino|orlando|orlindo|orósio|óscar|osmano|osmar|osório|osvaldo|otacílio|otelo|otniel|oto|otoniel|ovídio|pacal|parcidio|páris|pascoal|patrício|paulino|paulo|pedrino|pedro|pelaio|peniel|pepe|pépio|perfeito|péricles|perpétuo|pérsio|pio|pitágoras|plácido|plínio|policarpo|pompeu|porfírio|pracídio|priam|prião|primitivo|primo|principiano|priteche|procópio|próspero|prudêncio|querubim|quévin|quiliano|quim|quintino|quirilo|quirino|quírio|râdamas|rafael|rafaelo|rai|raimundo|ralfe|ramberto|ramiro|randolfo|raúl|ravi|reginaldo|regino|reinaldo|reis|remi|remígio|remízio|renato|reno|requerino|ricardo|rigoberto|ringo|riu|rivelino|roberto|robim|rodolfo|rodrigo|rogélio|rogério|rói|rolando|rolim|romano|romão|romarigo|romário|romeu|rómulo|ronaldo|roque|roriz|rosano|rosário|rosil|rossano|rúben|rubi|rubim|ruby|rudesindo|rúdi|rudolfo|rufino|rui|ruperto|rúpio|rurique|russel|sabino|sacramento|sadi|sadraque|sadrudine|saladino|salazar|salemo|sáli|salma|salomão|salustiano|salustiniano|salvação|salvador|salviano|samaritano|samir|samuel|sancho|sancler|sandrino|sandro|sansão|santana|santelmo|santiago|sário|sátiro|saúl|saulo|sauro|sávio|sávio|sebastião|secundino|selésio|seleso|selmo|sénio|serafim|sereno|sérgio|sertório|sesinando|severiano|severino|severo|siddártha|sidnei|sidónio|sidraque|sifredo|silas|silvano|silvério|silvestre|silviano|sílvio|simão|simauro|simplício|sindulfo|sinésio|sisenando|sisínio|sisínio|sisnando|sívio|sixto|sócrates|soeiro|solano|sotero|suraje|susano|taciano|tácio|tadeu|tálio|tâmiris|tarcísio|tarsício|tasso|tatiano|teliano|telmo|telo|teobaldo|teodemiro|teodomiro|teodoro|teodósio|teófilo|teotónio|tércio|tiago|tibério|ticiano|tierri|timóteo|tirso|tito|tobias|toledo|tomás|tomé|toni|torcato|torquato|trajano|tristão|tude|túlio|turgo|ubaldo|udo|ulisses|ulrico|urbano|urbino|urias|urias|uriel|urien|vaíse|valdemar|valdir|valdo|valdomiro|valente|valentim|valentino|valério|valgi|válter|vando|vânio|varo|vasco|venâncio|venceslau|vêndel|ventura|verdi|vergílio|veridiano|veríssimo|vero|vérter|vianei|vicêncio|vicente|victor|vidálio|vidaúl|vilar|vilator|vili|vílmar|vílson|vinício|virgílio|virgínio|virgulino|viriato|vitaliano|vitálio|vitiza|vito|vítor|vitorino|vitório|vivaldo|vladimiro|wilson|xavier|xénio|xenócrates|xénon|xerxes|xico|xisto|yuri|zacarias|zaido|zará|zarco|zardilaque|zeferino|zélio|zerá|rudiney/si', strtolower($name))){
						$gender = MALE;
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
		if(!preg_match('%[0-3][0-9]/[01][0-9]/[12][0-9]{3}$%si', $date)){
			return null;
		}


		if(preg_match('%(0[1-9]|[12][0-9]|3[01])[\./-]?(0[1-9]|1[012])[\./-]?([12][0-9]{3})([ ].*)?([01][0-9]|2[03]:[05][09])?%si', $date, $dt)){
			$date = $dt[3] . '-' . $dt[2] . '-' . $dt[1];
			/**
			 * Verifica se a data contem hh:ii:ss, caso tenha é concatenado a data
			 */
			if (isset($dt[4])){
				$date .= ' ' . $dt[4];
			}
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
		* Verifica se o CEP é inconsistente
		*/
		if(!preg_match('/[0-9]{8}/si', $zipcode)){
			$zipcode = null;
		}

		/**
		* Verifica se nenhuma das sequências invalidas abaixo 
		* foi digitada. Caso afirmativo, retorna null
		*/
		if(preg_match('/0{8}|1{8}|2{8}|3{8}|4{8}|5{8}|6{8}|7{8}|8{8}|9{8}/si', $zipcode)){
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
			* Verifica se algum endereçco ja foi cadastrado com o mesmo CEP e clona a cidade do endereço
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
	private function explodeTel($tel, $item){
		//Inicializa a variave $ddd com null
		$map = array(
			'ddd' => null,
			'tel' => null
			);

		/**
		* Verifica se o numero é consistente
		*/
		if(preg_match('/^0([1-9][0-9])([0-9]{8})$/si', $tel, $vet)){
			$ddd = (empty($vet[1]) || $vet[1] == '00')?null:$vet[1];
			$tel = (empty($vet[2]) || $vet[2] == '00000000')?null:$vet[2];
			$map = array(
				'ddd' => $ddd,
				'tel' => $tel
				);
		}

		return $map[$item];
	}
	/**
	* Extrai o DDD do telefone passado por parametro
	*/
	public function getDDD($tel){
		return $this->explodeTel($tel, 'ddd');
	}	

	/**
	* Extrai o Telefone separado do DDD
	*/
	public function getTelefone($tel){
		return $this->explodeTel($tel, 'tel');
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
		$txt = preg_replace("/ó|ò|ô|õ|º/s", "o", $txt);
		$txt = preg_replace("/ú|ù|û/s", "u", $txt);
		$txt = str_replace("ç","c",$txt);

		$txt = preg_replace("/Á|À|Â|Ã|ª/s", "A", $txt);
		$txt = preg_replace("/É|È|Ê/s", "E", $txt);
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

	public function progressBar($done, $total, $size=30) {
	    // if we go over our bound, just ignore it
	    if($done > $total){
	    	return false;
	    } 

	    if(empty($this->startTime)){
	    	$this->startTime=time();
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

	    $rate = ($now-$this->startTime)/$done;
	    $left = $total - $done;
	    $eta = round($rate * $left, 2);

	    $elapsed = $now - $this->startTime;
	    // $eta_minuts = $eta / 60;
	    // $eta_hors = $eta / 3600;
	    // $eta_days = $eta / 86400;
	    // $status_bar .= " remaining: " . number_format($eta) . " sec.  elapsed: ". number_format($elapsed)." sec.";

	    echo "$status_bar  ";
	    // when done, send a newline
	    if($done == $total) {
	        echo "\n";
	    }
	}

	public function __flush(){
		echo shell_exec('clear');
	}


}