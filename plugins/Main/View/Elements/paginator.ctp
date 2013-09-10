<?php $this->Paginator->options(array('url' => array('controller' => $this->params['controller'], 'action' => $this->params['action'], join('/', $this->params['pass']))))?>
<ul class="widget-navigation">
    <?php echo $this->Paginator->first('Início', array('tag' => 'li'))?>

    <?php if($this->Paginator->hasPrev()):?>
        <?php echo $this->Paginator->prev('Ant', array('tag' => 'li'))?>
    <?php endif?>

    <?php echo $this->Paginator->numbers(array('tag' => 'li', 'separator' => false))?>

    <?php if($this->Paginator->hasNext()):?>
        <?php echo $this->Paginator->next('Próx', array('tag' => 'li'))?>
    <?php endif?>

    <?php echo $this->Paginator->last('Fim', array('tag' => 'li'))?>
</ul>