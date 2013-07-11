<?php 
echo $this->Html->link('<span class="entypo-add-user"></span>', array('controller' => 'students', 'action' => 'add'), array('class' => 'entypo-button entypo-16 tip-s', 'title' => sprintf(__('Add a %s'), __d('app', 'Student')), 'escape' => false));
echo $this->Html->link('<span class="entypo-thumbs"></span>', array('controller' => 'grids', 'action' => 'add'), array('class' => 'entypo-button entypo-16 tip-s', 'title' => sprintf(__('Add a %s'), __d('app', 'Grid')), 'escape' => false));
echo $this->Html->link('<span class="entypo-mic"></span>', array('controller' => 'events', 'action' => 'add'), array('class' => 'entypo-button entypo-16 tip-s', 'title' => sprintf(__('Add a %s'), __d('app', 'Event')), 'escape' => false));
echo $this->Html->link('<span class="entypo-pencil"></span>', array('controller' => 'inscriptions', 'action' => 'add'), array('class' => 'entypo-button entypo-16 tip-s', 'title' => sprintf(__('Add a %s'), __d('app', 'Inscription')), 'escape' => false));
?>