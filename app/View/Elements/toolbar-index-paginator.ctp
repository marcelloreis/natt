<?php 
/**
* Carrega as configuracoes necessarias para a paginacao
*/
if(isset($config) && $config === true){
    /**
    * Carrega as opcoes necessarias quando o index for chamado por uma requisicao Ajax
    * Ex.: Quando o Box de arquivos relacionados for chamado, a grid funcionará via ajax
    */
    if($requestHandler == 'ajax'){
        $this->Paginator->options(array(
        'update' => '#content-grid',
        'before' => $this->Js->get('#index-loader')->effect('fadeIn', array('buffer' => false)),
        'complete' => ""
        ));
        /**
        * Recarrega as funcionalidade da grid
        */
        echo $this->Html->script('app-ajax');
    }else{
        /**
        * Carrega as opcoes da paginacao
        */
        $url = array(
            'controller' => $this->params['controller'],
            'action' => $this->params['action'],
            );

        foreach ($this->params['named'] as $k => $v) {
            $url[$k] = $v;
        }

        if(isset($this->params['pass'][0])){
            array_push($url, $this->params['pass'][0]);
        }

        $this->Paginator->options(array('url' => $url));
    }
}
?>


<ul class="widget-navigation">
    <?php echo $this->Paginator->first(__('First'), array('tag' => 'li'))?>
    <?php if($this->Paginator->hasPrev()):?>
        <?php echo $this->Paginator->prev(__('Prev'), array('tag' => 'li'))?>
    <?php endif?>
    <li>&nbsp;</li>
    <?php echo $this->Paginator->numbers(array('tag' => 'li', 'separator' => false, 'modulus' => 3))?>
    <li>&nbsp;</li>
    <?php if($this->Paginator->hasNext()):?>
        <?php echo $this->Paginator->next(__('Next'), array('tag' => 'li'))?>
    <?php endif?>
    <?php echo $this->Paginator->last(__('Last'), array('tag' => 'li'))?>
</ul>