<?php 
$defaults = array(
        'css' => '',
		'img' => 'home-32',
		'title' => 'title',
		'content' => 'content'
	);
$params = isset($params)?$params:array();
$attr = array_merge($defaults, $params);
?>
<div style="<?php echo $attr['css'];?>" class="icon-text-block">
    <a href="javascript:void(0);">
        <div>
            <span class="<?php echo $attr['img']?> plix-32"></span>
        </div>
        <div>
            <h3><?php echo $attr['title']?></h3>
            <span><?php echo $attr['content']?></span>
        </div>
    </a>
</div>