<?php 
$menu = array(
    /**
    * Imports
    */
    array('label' => __d('fields', 'Import'), 'controller' => 'import', 'icon_left' => '<span class="square-16 plix-16"></span>'),
    /**
    * Eventos
    */
    array('label' => __d('fields', 'Checkinlist'), 'controller' => 'chk', 'icon_left' => '<span class="square-16 plix-16"></span>'),

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
