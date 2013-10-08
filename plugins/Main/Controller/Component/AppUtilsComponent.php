<?php
/**
 * Application level Component
 *
 * Este arquivo contem todas as funcoes relacionadas ao Calendario do Google.
 *
 * @link          https://developers.google.com/google-apps/calendar/v3/reference/
 * @package       app.Controller.Component
 */
App::uses('Component', 'Controller');

/**
 * Application Component
 *
 * O componente "AppCalendar" contem todas as regras de negocio 
 * necessarias para manipular o calendario da conta google associada ao sistema
 */
class AppUtilsComponent extends Component {

	/**
	* Método startup
	*
	* O método startup é chamado depois do método beforeFilter do controle, 
	* mas antes do controller executar a action corrente.
	*
	* Aqui serao carregados todos os atributos do componente
	*/
	public function startup($controller){
		parent::startup($controller);
	}

	
	/**
	* Método xml2array
	* Transforma uma string XML em array
	* recebe uma string XML bem formada (well-formed) e retorna uma array. 
	*
	* @param XML $xml
	* @return array
	*/
	public function xml2array($xml){
		return json_decode(json_encode((array)simplexml_load_string($xml)),1);
	}	

	/**
	* Método num2db
	* Retorna o valor passador por parametro no formado de banco
	* Ex.: $valor = $this->AppUtils->num2db('1.000,00');
	* No exemplo acima, a variavel $valor tera o numero formatado como: 1000.00
	*/
	public function num2db($number){
		if(strstr($number, ',')){
			return str_replace(',', '.', str_replace('.', '', $number));
		}
	}

	/**
	* Método num2br
	* Retorna o valor passador por parametro no formado de Real Brasileiro
	* Ex.: $valor = $this->AppUtils->num2br('1000.00');
	* No exemplo acima, a variavel $valor tera o numero formatado como: 1.000,00
	*/
	public function num2br($number){
		return number_format($number, 2, ',', '.');
	}

	/**
	* Método dt2br
	* Transforma uma data no formato americado para o formato brasileiro
	* Ex.: $data = $this->AppUtils->dt2br('20130130');
	* No exemplo acima, a variavel $data tera o a data formatada como: 30/01/2013
	*
	* @param string $date|eua/db
	* @return string $date|br
	*/
	public function dt2br($date=false, $hours=false){
		$date = ($date)?$date:date('Y-m-d');
		//Formata a data caso ela nao esteja formatada
		if(!preg_match('/[\-\/\.]/si', $date)){
			$data = substr($date, 0, 4) . '-' . substr($date, 4, 2) . '-' . substr($date, 6, 2);
		}

		if($hours){
			$date = date('d/m/Y H:i:s', strtotime($date));
		}else{
			$date = date('d/m/Y', strtotime($date));
		}

		return $date;
	}

	/**
	* Esta funcao calcula a idade com base na data passada por parametro
	*/
	function calcAge($date){
	  $idade = false;
	  if(preg_match('/[12][0-9]{3}-[01][0-9]-[0-3][0-9]/si', $date)){
	    //Data atual e ja convertendo em timestamp
	    $hoje = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
	    //Data do aniversário
	    $date = explode('-', $date);
	    $dia = ($date[2]);
	    $mes = ($date[1]);
	    $ano = ($date[0]);
	    // Data do aniversário em timestamp
	    $aniversario = mktime( 0, 0, 0, $mes, $dia, $ano);

	    // Depois é só calcular data de hoje – aniversário)
	    $idade = floor((((($hoje - $aniversario) / 60) / 60) / 24) / 365.25);
	  }

	  return $idade;
	}	

	/**
	* Método dt2db
	* Quebra a data para remontar no formato para inserção do banco de dados
	* Ex.: $data = $this->AppUtils->dt2db('31/01/2013');
	* No exemplo acima, a variavel $data tera o a data formatada como: yyyy-mm-dd [hh:ii:ss]
	*
	* @param string $date|br
	* @return string $date|eua/db
	*/
	public function dt2db($date=false, $hours=false){
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
}