<?php 
$menu = array(
    /**
    * Eventos
    */
    array('label' => __d('fields', 'Events'), 'controller' => 'events', 'icon_left' => '<span class="square-16 plix-16"></span>'),

    /**
    * Grades
    */
    array('label' => __d('fields', 'Grids'), 'controller' => 'grids', 'icon_left' => '<span class="cells-16 plix-16"></span>'),

    /**
    * Estudantes
    */
    array('label' => __d('fields', 'Students'), 'controller' => 'students', 'icon_left' => '<span class="vcard-16 plix-16"></span>'),

    /**
    * Inscricoes
    */
    array('label' => __d('fields', 'Inscriptions'), 'controller' => 'inscriptions', 'icon_left' => '<span class="pencil-16 plix-16"></span>'),

    /**
    * Configuracoes
    */
    array(
        'label' => __d('app', 'Settings'),
        'icon_left' => '<span class="settings-16 plix-16"></span>', 
        'icon_right' => '<span class="button-icon"><span class="plix-10 plus-10"></span></span>',
        'children' => array(
            /**
            * Oficinas
            */
            array('label' => __d('fields', 'Workshops'), 'controller' => 'workshops', 'plugin' => false),
            /**
            * Patrocinadores
            */
            array('label' => __d('fields', 'Sponsors'), 'controller' => 'sponsors', 'plugin' => false),
            /**
            * Responsáveis
            */
            array('label' => __d('fields', 'Responsibles'), 'controller' => 'responsibles', 'plugin' => false),
            /**
            * Palestrantes
            */
            array('label' => __d('fields', 'Speakers'), 'controller' => 'speakers', 'plugin' => false),
            /**
            * Palestrantes
            */
            array('label' => __d('fields', 'Email Marketing'), 'controller' => 'marketings', 'plugin' => false),

            )
        ),

    /**
    * Localizacoes
    */
    array(
        'label' => __('Locales'),
        'icon_left' => '<span class="map-16 plix-16"></span>', 
        'icon_right' => '<span class="button-icon"><span class="plix-10 plus-10"></span></span>',
        'children' => array(
            /**
            * Países
            */
            array('label' => __('Countries'), 'controller' => 'countries', 'plugin' => false),
            /**
            * Estados
            */
            array('label' => __('States'), 'controller' => 'states', 'plugin' => false),
            /**
            * Cidades
            */
            array('label' => __('Cities'), 'controller' => 'cities', 'plugin' => false),
            )
        ),
    /**
    * Seguranca
    */
    array(
        'label' => __('Security'),
        'icon_left' => '<span class="lock-16 plix-16"></span>', 
        'icon_right' => '<span class="button-icon"><span class="plix-10 plus-10"></span></span>',
        'children' => array(
            /**
            * Seguranca/Usuarios
            */
            array('label' => __('Users'), 'controller' => 'users'),
            /**
            * Seguranca/Grupos
            */
            array('label' => __('Groups'), 'controller' => 'groups'),
            /**
            * Seguranca/Permissoes
            */
            array('label' => __('Permissions'), 'controller' => 'acl', 'action' => 'permissions', 'plugin' => 'main')
            )
        ),
    );
echo $this->AppUtils->buildMenu($menu, array('classActive' => 'page-active'));
