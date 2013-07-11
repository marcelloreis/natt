<?php echo $this->Html->docType('html5');?>
<?php echo $this->element('ifs-ie');?>
<html lang="pt-br" class="no-js">
    <head>
        <title><?php echo TITLE_HEADER?></title>
        <?php
        echo $this->Html->charset();

        // Mobile meta/files
        echo $this->element('mobile-meta-files');
        // Internet Explore
        echo $this->element('ie9-pinned');
        //Favicon
        echo $this->Html->meta('icon', $this->Html->url('/img/logo.png'));

        // Metas
        echo $this->Html->meta(array('name' => 'base_url', 'content' => $this->Html->url('/', true)));
        echo $this->Html->meta(array('name' => 'description', 'content' => ''));
        echo $this->Html->meta(array('name' => 'keywords', 'content' => ''));
        echo $this->Html->meta(array('name' => 'author', 'content' => ''));
        echo $this->Html->meta(array('name' => 'robots', 'content' => 'index,follow'));
        echo $this->Html->meta(array('name' => 'content-language', 'content' => 'pt-br'));

        //Reservado para a inserção do CSS da pagina de login
        echo $this->fetch('css-login');

        //Reservado para a inserção do CSS das abas dos formularios com associacao HABTM
        echo $this->fetch('etabs', $this->Html->css('etabs'));

        //Styles da aplicacao
        echo $this->Html->css(array(
            'framework',
            'style',
            'ui/jquery.ui.base',
            //'theme/darkblue',
            'theme/lightgrey',
            'app',
            'app-project',
        ));

        
        //Scrips da aplicacao
        echo $this->Html->script(array(
            'jquery-1.7.2.min',
            '/Main/js/jquery-ui/js/jquery-ui-1.8.23.custom.min',

            //Touch helper  
            'jquery.ui.touch-punch.min',
            //MouseWheel  
            'jquery.mousewheel.min',
            //UI Spinner
            'jquery.ui.spinner',
            //Tooltip               
            'tipsy',
            //Treeview                         
            'treeview',
            //Calendar                         
            'fullcalendar.min', 
            //selectToUISlider                
            'selectToUISlider.jQuery', 
            //context Menu         
            'jquery.contextMenu', 
            //File Explore              
            'elfinder.min', 
            //AutoGrow Textarea                   
            'autogrow-textarea', 
            //Resizable Textarea               
            'textarearesizer.min',
            //HTML5 WYSIWYG  
            'wysiwyghtml5/parser_rules/advanced',
            'wysiwyghtml5/dist/wysihtml5-0.3.0',    
            //Lightbox                      
            'jquery.colorbox-min',
            //DataTables
            'jquery.dataTables.min',            
            //Masked inputs
            'jquery.maskMoney',
            'jquery.maskedinput-1.3.min', 
            //IE7 JSON FIX
            'json2',
            //HTML5 audio player
            'audiojs/audiojs/audio.min', 

            // Custom theme plugins //

            //Stylesheet switcher 
            // 'e_styleswitcher.1.0.min',                 
            'e_styleswitcher.1.1.min',                 
            //Widgets
            // 'powerwidgets.1.1.min',
            'powerwidgets.1.2.min',
            //Widgets panel
            'powerwidgetspanel.1.1.min',
            // 'powerwidgetspanel.1.2.min',
            //Select styling
            // 'e_select.1.0.min',    
            'e_select.2.0.min',    
            //Checkbox solution
            // 'e_checkbox.1.0.min',
            'e_checkbox.1.0.v_nasza',
            //Radio button replacement
            'e_radio.1.0.min',    
            //Tabs
            'e_tabs.1.1.min',
            //File styling
            'e_file.1.0.min',    
            //MainMenu
            'e_mainmenu.1.0.min',
            //Menu
            // 'e_menu.1.0.min',
            'e_menu.1.1.min.js',
            //Input popup box
            'e_inputexpand.1.0.min',
            //Progressbar
            'e_progressbar.1.0.min',
            //Scrollbar replacemt
            'e_scrollbar.1.0.min', 
            //Onscreen keyboard
            'e_oskeyboard.1.0.min',
            //Textarea limiter
            'e_textarealimiter.1.0.min',
            //Contact form with validation
            // 'e_contactform.1.0.min',
            'e_contactform.1.1.min',
            //Responsive table helper
            'e_responsivetable.1.0.min',
            //Gallery
            'e_gallery.1.0.min',
            //Live search
            'e_livesearch.1.0.min',
            //Notify
            'e_notify.1.0.min',  
            //Countdown  
            'e_countdown.1.0.min', 
            //Clone script
            'e_clone.1.0.min', 
            //Chained inputs
            'e_chainedinputs.1.0.min',
            //Show password     
            'e_showpassword.1.0.min',        
            //Flot charts
            'jquery.flot.min',        
            'excanvas.min.js',
            //All plugins are set here
            'modernizr.min',
            // HTML5/CSS3 support
            'main',

            //Script da aplicacao
            'app',
            'app-project'
        ));

        //Reservado para a inserção do Scripts da pagina de login
        echo $this->fetch('js-login');

        //Scripts dos plugins
        echo $this->Html->script(array(
            '/Main/js/main'
        ));

        //Imprime todas as mensagem que que estiverem fora dos templates padroes da aplicação
        echo __($this->Session->flash(), true);
        ?>

    </head>
    <body class="layout_fluid layout_responsive">

        <!-- Reservado para a exibição de themas -->
        <?php echo $this->fetch('themes')?>