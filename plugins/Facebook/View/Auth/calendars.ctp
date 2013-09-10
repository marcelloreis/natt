<div id="error-container">
    <div class="error-box">
        <hgroup>
            <?php echo $this->AppForm->separator()?>
            <?php echo $this->Html->image('google_logo_41.png')?>
            <h2>Calendários</h2>
        </hgroup>
        <h3>Escolha o calendario:</h3>
        <ul class="circle">
            <?php foreach ($calendars as $k => $v):?>
                <li><?php echo $this->Html->link($v, array('controller' => 'auth', 'action' => 'saveCredentials', 'plugin' => 'google', $k))?></li>
            <?php endforeach?>
        </ul>
    </div>
</div>