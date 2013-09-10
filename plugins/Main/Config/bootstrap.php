<?php
/**
 * Todas as definicoes necessarias para rodar o plugin
 *
 * Um Plugin CakePHP para acelerar o desenvolvimento de sistemas web
 */

/**
 * Diretorios 
 */
define('PATH_PLUGIN', dirname(dirname(__FILE__)));
define('PATH_APP', dirname(dirname(dirname(dirname(__FILE__)))));
define('PATH_TEMPLATE', PATH_APP . DS . 'View' . DS . 'Elements' . DS . 'Templates');
define('PATH_TEMPLATE_DEFAULT', PATH_PLUGIN . DS . 'View' . DS . 'Elements' . DS . 'Templates');









/**
 * Gerenciador de ACL
 */

/**
 * List of AROs (Class aliases)
 * Order is important! Parent to Children
 */
Configure::write('AclManager.aros', array('Group', 'User'));

/**
 * Limit used to paginate AROs
 * Replace {alias} with ARO alias
 * Configure::write('AclManager.{alias}.limit', 3)
 */
// Configure::write('AclManager.Role.limit', 3);

/**
 * Routing Prefix
 * Set the prefix you would like to restrict the plugin to
 * @see Configure::read('Routing.prefixes')
 */
// Configure::write('AclManager.prefix', 'admin');

/**
 * Ugly identation?
 * Turn off when using CSS
 */
Configure::write('AclManager.uglyIdent', true);
				
/**
 * Actions to ignore when looking for new ACOs
 * Format: 'action', 'Controller/action' or 'Plugin.Controller/action'
 */
Configure::write('AclManager.ignoreActions', array('isAuthorized'));

/**
 * List of ARO models to load
 * Use only if AclManager.aros aliases are different than model name
 */
// Configure::write('AclManager.models', array('Group', 'Customer'));

/*
 * The users table field used as username in the views
 */
Configure::write('acl.user.display_name', "username");
Configure::write('acl.rule.display_name', "name");

/**
 * END OF USER SETTINGS
 */


Configure::write("AclManager.version", "1.2.4");
if (!is_array(Configure::read('AclManager.aros'))) {
	Configure::write('AclManager.aros', array(Configure::read('AclManager.aros')));
}
if (!is_array(Configure::read('AclManager.ignoreActions'))) {
	Configure::write('AclManager.ignoreActions', array(Configure::read('AclManager.ignoreActions')));
}
if (!Configure::read('AclManager.models')) {
	Configure::write('AclManager.models', Configure::read('AclManager.aros'));
}
