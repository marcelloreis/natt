<?php
App::uses('AppModel', 'Model');
/**
 * Inscription Model
 *
 * Esta classe é responsável ​​pela gestão de quase tudo o que acontece a respeito do(a) Inscription, 
 * é responsável também pela validação dos seus dados.
 *
 * PHP 5
 *
 * @copyright     Copyright 2013-2013, Nasza Produtora
 * @link          http://www.nasza.com.br/ Nasza(tm) Project
 * @package       app.Model
 *
 * Inscription Model
 *
 * @property Student $Student
 * @property Event $Event
 * @property Grid $Grid
 */
class Inscription extends AppModel {
/**
 * Display field
 *
 * @var string
 */
	public $displayField = 'is_paid';

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'student_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				'message' => 'O campo Estudante deve ser preenchido corretamente.',
			),
		),
		'event_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				'message' => 'O campo Evento deve ser preenchido corretamente.',
			),
		),
		'is_paid' => array(
			'boolean' => array(
				'rule' => array('boolean'),
				'message' => 'O campo Pago deve ser preenchido corretamente.',
			),
		),
	);

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'Student' => array(
			'className' => 'Student',
			'foreignKey' => 'student_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Event' => array(
			'className' => 'Event',
			'foreignKey' => 'event_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
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
