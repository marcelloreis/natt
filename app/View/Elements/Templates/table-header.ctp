	<div class="powerwidget">
		<header>
			<h2><?php echo $this->Paginator->counter('{:count}')?> <?php echo __('record(s) found')?></h2>  
			<span style="margin-right:20px;" id="index-loader" class="powerwidget-loader" style="display: none;"></span>
		</header>
		<div>
			<?php echo $this->fetch('toolbar-index-top', $this->element('toolbar-index', array('config' => true, 'search' => true)))?>
			%init_form%
			<div class="table-wrapper">
				<table id="%id%" class="%tableClass%" width="%tableWidth%" cellspacing="%tableCellspacing%" cellpadding="%tableCellpadding%" border="%tableBorder%">
