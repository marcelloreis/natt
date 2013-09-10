<?php

class AppGridHelper extends AppHelper {

	public $helpers = array('Main.AppForm', 'Form', 'Main.AppUtils', 'Paginator', 'Html');

    //Model
    private $model;

	//Table
    private $tableTemplate;
    private $tableIniTemplate;
    private $bottomTemplate;

    //TR
    private $rows = array();
    private $trTemplate;
    private $tdTemplate;

	/**
     * Retorna elementos HTML
     *
     * ### Options:
     *
     * @param string $model O objeto model para qual a grid está sendo exibida.
     * @param array $options Um array de atributos e opcoes da tabela
     * @return string Uma tag de abertura da tabela
     */
	public function create($model, $options = array()){
        $this->model = $model;

    	/**
    	* Monta o top do template da tabela
    	*/
        //Verifica se foi requisitado um template fora do padrao
        $templateName = (isset($options['template']))?$options['template']:'table-header';

        //Carrega o template do elemento
        $this->tableIniTemplate = $this->AppUtils->loadTemplate($templateName);

    	//Carrega os valores pdroes do input
        $defaults = array(
            'init_form' => $this->AppForm->create($this->model, $options),
            'classForm' => '',
            'tableClass' => 'grid',
            'tableWidth' => '100%',
            'tableCellspacing' => '0',
            'tableCellpadding' => '0',
            'tableBorder' => '0',
            'forceDisplay' => false
            );
        $attr = array_merge($defaults, $options);

        //Apaga a tabela caso nao tenha nenhum registro para ser exibido
        if(isset($this->Paginator->request->params['paging']) && !$this->Paginator->counter('{:count}') && !$attr['forceDisplay']){
            $attr['tableClass'] = 'hidden';
        }

    	//Altera as chaves do array para o padrao de variaveis do template
        $values = $this->AppUtils->key2var($attr);

        //Carrega as variaveis do template com os valores passados por parametro
        $this->tableIniTemplate = str_replace(array_keys($values), $values, $this->tableIniTemplate);

        //Uni as tags HTML
        $this->tableTemplate .= $this->tableIniTemplate;

        //Retona as tags HTML geradas
        return $this->tableTemplate;
    }

    /**
     * Retorna elementos HTML
     *
     * ### Options:
     *
     * @param string $columns array com as colunas que serao inseridas na tag TR
     * @param array $options Um array de atributos e opcoes da tr
     * @return string Uma tag de linha da tabela
     */
    public function tr($columns, $options=array()){
        //Verifica se foi requisitado um template fora do padrao
        $templateName = (isset($options['template']))?$options['template']:'table-tr';

        //Carrega o template do elemento
        $this->trTemplate = $this->AppUtils->loadTemplate($templateName);

        /**
         * Verifica se a TR adicionada é a primeira, caso seja é adicionado a tag TH e SORTABLE automaticamente
         */
        if(!count($this->rows)){
            if(!isset($options['tag'])){
                $options['tag'] = 'th';
            }
            if(!isset($options['sort'])){
                $options['sort'] = true;
            }
        }

        //Carrega os valores padroes do input
        $defaults = array(
            'tag' => 'td',
            'class' => '',
            'tableBorder' => '0',
            'sort' => false
            );
        $attr = array_merge($defaults, $options);


        //Altera as chaves do array para o padrao de variaveis do template
        $values = $this->AppUtils->key2var($attr);

        //Carrega as variaveis do template com os valores passados por parametro
        $this->trTemplate = str_replace(array_keys($values), $values, $this->trTemplate);

        //Extrai as colunas da linha
        preg_match('/(\<tr.*?>)(.*?)(<\/tr>)/si', $this->trTemplate, $map);
        $tr_ini = $map[1];
        $td = $map[2];
        $tr_end = $map[3];

        /**
         * Gera as TDs apartir dos valores passados pela variavel $columns
         */
        if(is_array($columns)){
            //Mantem a coluna "action" sempre no final da tabela
            if(isset($columns['action']) && !empty($columns['action'])){
                $action = $columns['action'];
                unset($columns['action']);
                $columns['action'] = $action;
            }
            foreach ($columns as $k => $v) {
                //Guarda as chaves dos cabeçalhos da tabela para casar exatamente com os valores que viram na tabela
                $v = __d('fields', $v);
                if($attr['tag'] == 'th'){
                    $this->rows[$k] = $v;
                }else if(!array_key_exists($k, $this->rows)){
                    continue;
                }

                //Carrega a largura da coluna
                $column_size = isset($columns["{$k}_width"])?$columns["{$k}_width"]:'';


                //Remove o tamanho setado para a coluna anterior
                $td = preg_replace('/\<t([dh])(.*?)width=\".*?\"(.*)>/si', '<t$1 $2 $3>', $td);

                if(preg_match('/^\<input.*?type\=\"checkbox\".*?\>/si', $v)){
                    //Insere a largura minima para a coluna caso a coluna seja checkbox
                    $td = preg_replace('/\<t([dh])>/si', '<t$1 width="30">', $td);
                }else{
                    //Insere a largura padrao para a coluna caso a coluna seja de actions
                    if($k == 'action'){
                        $size_default = '100';
                        if(isset($this->params['named']['fkbox'])){
                            switch ($this->params['named']['fkbox']) {
                                case 'belongsto':
                                case 'habtm':
                                    $size_default = '20';
                                    break;
                            }
                        }                        
                        $column_size = isset($columns["{$k}_width"])?$columns["{$k}_width"]:$size_default;
                    }

                    //Insere a largura da coluna, caso tenha sido passado algum valor por parametro
                    if(!empty($column_size)){
                        $td = preg_replace('/\<t([dh]).*?>/si', '<t$1 width="' . $column_size . '">', $td);
                    }
                }

                //Concatena o nome do model caso nao esteja concatenado
                $field = (strstr($k, '.'))?$k:$this->model . '.' . $k;
                
                if($attr['sort'] && !preg_match('/^<.*?>/si', $v)){
                    $this->tdTemplate[$k] = str_replace('%content%', $this->Paginator->sort($field, $v), $td);
                }else{
                    $this->tdTemplate[$k] = str_replace('%content%', $v, $td);
                }

            }
        }

        //Uni os templates gerados
        $this->trTemplate = $tr_ini . implode('', $this->tdTemplate) . $tr_end;

        //Retona as tags HTML geradas
        return $this->trTemplate;
    }

    public function btn($value='Salvar', $options=array()){
        //Verifica se foi requisitado um template fora do padrao
        $templateName = (isset($options['template']))?$options['template']:'table-tr';

        //Carrega o template do elemento
        $this->trTemplate = $this->AppUtils->loadTemplate($templateName);

        //Carrega os valores padroes do input
        $defaults = array(
            'tag' => 'td',
            'class' => '',
            'tableBorder' => '0',
            'sort' => false
            );
        $attr = array_merge($defaults, $options);


        //Altera as chaves do array para o padrao de variaveis do template
        $values = $this->AppUtils->key2var($attr);

        //Carrega as variaveis do template com os valores passados por parametro
        $this->trTemplate = str_replace(array_keys($values), $values, $this->trTemplate);
        //Extrai as colunas da linha
        preg_match('/(\<tr.*?>)(.*?)(<\/tr>)/si', $this->trTemplate, $map);
        $tr_ini = $map[1];
        $td = $map[2];
        $tr_end = $map[3];
        $qt_cols = count($this->rows);

        //Mescla/Fundi as colunas para inserir o botao na linha/tr
        $td = preg_replace('/^(<td)/', "<td colspan='{$qt_cols}'", $td);

        //Insere o botao na coluna já mesclada/fundida
        $td = str_replace('%content%', $this->AppForm->btn($value), $td);

        // debug(count($this->rows));
        // debug($td);
        // die;

        //Uni os templates gerados
        $this->trTemplate = $tr_ini . $td . $tr_end;

        //Retona as tags HTML geradas
        return $this->trTemplate;
    }

    /**
     * Retorna elementos HTML
     *
     * ### Options:
     *
     * @param array $options Um array de atributos e opcoes da tabela
     * @return string Uma tag de fechamento da tabela
     */
    public function end($options=array()){
        /**
        * Finaliza o top do template da tabela
        */
        //Verifica se foi requisitado um template fora do padrao
        $templateName = (isset($options['template']))?$options['template']:'table-footer';

        //Carrega o template do elemento
        $this->bottomTemplate = $this->AppUtils->loadTemplate($templateName);

        //Carrega os valores pdroes do input
        $defaults = array(
            'end_form' => $this->AppForm->end($options),
            'qtRows' => count($this->rows),
            'labelLeft' => date('d/m/Y - H:i:s'),
            'labelRight' => 'Quantidade listada aqui'
            );
        $vet = array_merge($defaults, $options);

        //Altera as chaves do array para o padrao de variaveis do template
        $values = $this->AppUtils->key2var($vet);

        //Carrega as variaveis do template com os valores passados por parametro
        $this->bottomTemplate = str_replace(array_keys($values), $values, $this->bottomTemplate);

        //Retona as tags HTML geradas
        return $this->bottomTemplate;
    }

}