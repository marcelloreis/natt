<?php
App::uses('AppModel', 'Model');
/**
 * Workshop Model
 *
 * Esta classe é responsável ​​pela gestão de quase tudo o que acontece a respeito do(a) Workshop, 
 * é responsável também pela validação dos seus dados.
 *
 * PHP 5
 *
 * @copyright     Copyright 2013-2013, Nasza Produtora
 * @link          http://www.nasza.com.br/ Nasza(tm) Project
 * @package       app.Model
 *
 * Workshop Model
 *
 * @property Grid $Grid
 */
class Workshop extends AppModel {

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'name' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				'message' => 'O campo Name deve ser preenchido corretamente.',
			),
		),
		'status' => array(
			'boolean' => array(
				'rule' => array('boolean'),
				'message' => 'O campo Status deve ser preenchido corretamente.',
			),
		),
	);


/**
 * hasAndBelongsToMany associations
 *
 * @var array
 */
	public $hasAndBelongsToMany = array(
		'Grid' => array(
			'className' => 'Grid',
			'joinTable' => 'inscriptions_grids',
			'foreignKey' => 'inscription_id',
			'associationForeignKey' => 'grid_id',
			'unique' => 'keepExisting',
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'finderQuery' => '',
			'deleteQuery' => '',
			'insertQuery' => ''
		)
	);	

}
