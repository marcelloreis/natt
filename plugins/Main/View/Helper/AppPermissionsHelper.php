<?php
App::uses('AppHelper', 'View/Helper');

class AppPermissionsHelper extends AppHelper {
    
    var $helpers = array('Session');
    
    /**
     * Verifica se o usuario logado tem permissao de acesso para o path passado por parametro
     * Ex.: $this->AppPermissions->check('Post.add');
     */
    function check($path){
        if($this->Session->check("Auth.Permissions.{$path}") && $this->Session->read("Auth.Permissions.{$path}") === true){
            return true;
        }

        return false;
    }
}