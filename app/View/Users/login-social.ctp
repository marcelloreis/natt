<?php 
echo $this->assign('css-login', $this->Html->css('login'));
/**
* Remove o css das abas de formulario pois estao conflitando com o css do menu
*/
echo $this->assign('etabs', '');
echo $this->assign('js-login', $this->Html->script('login'));
?>


    <div id="login-sn">
    
        <!-- Put your logo here -->
        <div id="logo">
            <h1><?php echo TITLE_APP?></h1>
        </div>
        <!-- Show a dialog -->
        <?php echo $this->Session->flash(FLASH_SESSION_LOGIN)?>
        
        <!-- The main part -->                   
        <div id="login-outher">        
            <div id="login-inner">
                <header>
                    <h2>Algum texto sobre o sistema.</h2> 
                </header>
                
                <div id="login-content">
                    <div id="login-content-inner">
                        <ul id="tab-menu" class="etabs">
                            <li><a href="#etab1"><?php echo $this->Html->image('icons/social/16/howto.png')?> Como funciona</a></li>
                            <li><a href="#etab2"><?php echo $this->Html->image('icons/social/16/signup.png')?> Cadastre-se</a></li>
                            <li><a href="#etab3"><?php echo $this->Html->image('icons/social/16/login.png')?> Login</a></li>
                            <li><a href="#etab4"><?php echo $this->Html->image('icons/social/16/google.png')?> Google</a></li>
                            <li><a href="#etab5"><?php echo $this->Html->image('icons/social/16/facebook.png')?> Facebook</a></li>
                        </ul>        
                        <div id="etab1" class="etabs-content">
                            <div style="text-align:justify" class="g_1">
                                <h3>Como funciona o <?php echo TITLE_APP?>?</h3>
                                <p>
                                    Com o <?php echo TITLE_APP?> você terá a melhor ferramenta de controle financeiro online em uma versão completa e com os melhores padrões de segurança na internet. Construa sua independência financeira.
                                </p>
                            </div>
                        </div>

                        <div id="etab2" class="etabs-content">
                            <?php echo $this->AppForm->create('User', array('id' => 'validation-form')) ?>
                                <?php echo $this->Form->hidden('group_id', array('value' => COOPERATOR_GROUP)) ?>
                                <?php echo $this->Form->hidden('api', array('value' => 'system')) ?>

                                <?php echo $this->AppForm->input('name', array('template' => 'input-login', 'placeholder' => 'Nome', 'tabindex' => "1", 'data-validation-type' => "present")) ?>
                                <div class="spacer-10"><!-- spacer 20px --></div> 
                                <?php echo $this->AppForm->input('email', array('template' => 'input-login', 'class' => 'email', 'placeholder' => 'E-mail', 'tabindex' => "1", 'data-validation-type' => "present")) ?>
                                <div class="spacer-10"><!-- spacer 20px --></div> 
                                <?php echo $this->AppForm->input('password', array('template' => 'input-login', 'class' => 'password', 'placeholder' => 'Senha', 'tabindex' => "2", 'data-validation-type' => "present")) ?>
                                <div class="spacer-10"><!-- spacer 20px --></div> 

                                <div class="g_1">
                                    <?php echo $this->AppForm->btn('Entrar')?>
                                </div>      
                            <?php echo $this->AppForm->end(); ?>
                        </div>

                        <div id="etab3" class="etabs-content">
                            <?php echo $this->AppForm->create('User') ?>
                                <?php 
                                $loginHomolog = TITLE_APP == 'Homolagação'?'marcelo@nasza.com.br':'';
                                $passHomolog = TITLE_APP == 'Homolagação'?'123456':'';
                                ?>
                                <?php echo $this->AppForm->input('email', array('value' => $loginHomolog, 'template' => 'input-login', 'class' => 'email', 'placeholder' => 'E-mail', 'tabindex' => "1", 'data-validation-type' => "email")) ?>
                                <div class="spacer-10"><!-- spacer 20px --></div> 
                                <?php echo $this->AppForm->input('password', array('value' => $passHomolog, 'template' => 'input-login', 'class' => 'password', 'placeholder' => 'Senha', 'tabindex' => "2", 'data-validation-type' => "present")) ?>
                                <div class="spacer-10"><!-- spacer 20px --></div> 

                                <div class="g_1">
                                    <?php echo $this->AppForm->btn('Entrar')?>
                                </div>      
                            <?php echo $this->AppForm->end(); ?>
                        </div><!-- End tab -->

                        <div id="etab4" class="etabs-content">
                            <div style="text-align:justify" class="g_1">
                                <h3>O <?php echo TITLE_APP?> é sincronizado com o Google Agenda?</h3>
                                <p>
                                    Sim. As alterações feitas no <?php echo TITLE_APP?>, serão refletidas no Google Agenda da sua conta. Por exemplo, se você criar uma nova transação no <?php echo TITLE_APP?>, a transação será automaticamente exibida no seu Google Agenda.
                                </p>
                            </div>
                            <?php echo $this->Html->link('Conecte-se com ' . $this->Html->image('icons/social/signin/google_signin.png'), array('controller' => 'users', 'action' => 'login', 'api' => 'google', 'plugin' => false), array('class' => 'social-button', 'escape' => false))?>
                        </div><!-- End tab -->

                        <div id="etab5" class="etabs-content">
                            <div style="text-align:justify" class="g_1">
                                <h3>Comece agora, com apenas 1 clique</h3>
                                <p>
                                    O que você está esperando para aumentar seu poder de gestão? Nosso cadastro é gratuito, basta fornecer os dados da sua conta Facebook clicando no link abaixo. Você terá acesso a todas as areas do <?php echo TITLE_APP?>, com todas as funcionalidades do sistema. Não se preocupe: todos os dados inseridos são criptografados e você poderá fazer backups quando quiser de todas as suas movimenteções financeiras.
                                </p>
                            </div>
                            <?php echo $this->Html->link('Conecte-se com ' . $this->Html->image('icons/social/signin/facebook_signin.png'), array('controller' => 'users', 'action' => 'login', 'api' => 'facebook', 'plugin' => false), array('class' => 'social-button', 'escape' => false))?>
                        </div><!-- End tab -->

                    </div><!-- End #login-content-inner --> 
                </div><!-- End #login-content --> 
            </div><!-- End #login-inner -->                                  
        </div><!-- End #login-outher --> 
        

        <!-- place your copyright text here -->
        <footer id="footer">
            Copyright © <?php echo date('Y')?> <a target="_blank" href="<?php echo COPYRIGHT_LINK?>"><?php echo COPYRIGHT?></a>
        </footer> 
    </div><!-- End "#login" -->        
