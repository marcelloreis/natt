<?php
App::uses('AppModel', 'Model');
/**
 * Grid Model
 *
 * Esta classe é responsável ​​pela gestão de quase tudo o que acontece a respeito do(a) Grid, 
 * é responsável também pela validação dos seus dados.
 *
 * PHP 5
 *
 * @copyright     Copyright 2013-2013, Nasza Produtora
 * @link          http://www.nasza.com.br/ Nasza(tm) Project
 * @package       app.Model
 *
 * Grid Model
 *
 * @property Workshop $Workshop
 * @property Event $Event
 * @property Speaker $Speaker
 */
class Grid extends AppModel {

	/**
	* Campos virtuais permitem criar expressões SQL arbitrárias e atribuí-los como campos em um modelo. 
	* Esses campos não podem ser salvos, mas serão tratados como campos de modelo para outras operações de leitura. 
	*/
	public $virtualFields = array(
	    'date_ini_time' => "DATE_FORMAT(Grid.date_ini, '%H:%i:%s')",
	    'date_end_time' => "DATE_FORMAT(Grid.date_end, '%H:%i:%s')"
	);

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'workshop_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				'message' => 'O campo Workshop deve ser preenchido corretamente.',
			),
		),
		'event_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				'message' => 'O campo Event deve ser preenchido corretamente.',
			),
		),
		'speaker_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				'message' => 'O campo Palestrante deve ser preenchido corretamente.',
			),
		),
	);

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'Workshop' => array(
			'className' => 'Workshop',
			'foreignKey' => 'workshop_id',
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
		),
		'Speaker' => array(
			'className' => 'Speaker',
			'foreignKey' => 'speaker_id',
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
		'Inscription' => array(
			'className' => 'Inscription',
			'joinTable' => 'inscriptions_grids',
			'foreignKey' => 'grid_id',
			'associationForeignKey' => 'inscription_id',
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
