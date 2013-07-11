<?php
App::uses('AppModel', 'Model');
/**
 * Event Model
 *
 * Esta classe é responsável ​​pela gestão de quase tudo o que acontece a respeito do(a) Event, 
 * é responsável também pela validação dos seus dados.
 *
 * PHP 5
 *
 * @copyright     Copyright 2013-2013, Nasza Produtora
 * @link          http://www.nasza.com.br/ Nasza(tm) Project
 * @package       app.Model
 *
 * Event Model
 *
 * @property State $State
 * @property City $City
 * @property Grid $Grid
 * @property Inscription $Inscription
 * @property Marketing $Marketing
 * @property Responsible $Responsible
 * @property Speaker $Speaker
 * @property Sponsor $Sponsor
 */
class Event extends AppModel {

	/**
	* Campos virtuais permitem criar expressões SQL arbitrárias e atribuí-los como campos em um modelo. 
	* Esses campos não podem ser salvos, mas serão tratados como campos de modelo para outras operações de leitura. 
	*/
	public $virtualFields = array(
	    'date_ini_time' => "DATE_FORMAT(Event.date_ini, '%H:%i:%s')",
	    'date_end_time' => "DATE_FORMAT(Event.date_end, '%H:%i:%s')"
	);

/**
 * Display field
 *
 * @var string
 */
	public $displayField = 'name';
	
/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'name' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				'message' => 'O campo Nome deve ser preenchido corretamente.',
			),
		),
		'about' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				'message' => 'O campo Sobre deve ser preenchido corretamente.',
			),
		),
		'date_ini' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				'message' => 'O campo Início deve ser preenchido corretamente.',
			),
		),
		'date_end' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				'message' => 'O campo Fim deve ser preenchido corretamente.',
			),
		),
	);

	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'City' => array(
			'className' => 'City',
			'foreignKey' => 'city_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

/**
 * hasMany associations
 *
 * @var array
 */
	public $hasMany = array(
		'Grid' => array(
			'className' => 'Grid',
			'foreignKey' => 'event_id',
			'dependent' => true,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
		'Inscription' => array(
			'className' => 'Inscription',
			'foreignKey' => 'event_id',
			'dependent' => true,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
		'Marketing' => array(
			'className' => 'Marketing',
			'foreignKey' => 'event_id',
			'dependent' => true,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		)
	);


/**
 * hasAndBelongsToMany associations
 *
 * @var array
 */
	public $hasAndBelongsToMany = array(
		'Responsible' => array(
			'className' => 'Responsible',
			'joinTable' => 'events_responsibles',
			'foreignKey' => 'event_id',
			'associationForeignKey' => 'responsible_id',
			'unique' => 'keepExisting',
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'finderQuery' => '',
			'deleteQuery' => '',
			'insertQuery' => ''
		),
		'Speaker' => array(
			'className' => 'Speaker',
			'joinTable' => 'events_speakers',
			'foreignKey' => 'event_id',
			'associationForeignKey' => 'speaker_id',
			'unique' => 'keepExisting',
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'finderQuery' => '',
			'deleteQuery' => '',
			'insertQuery' => ''
		),
		'Sponsor' => array(
			'className' => 'Sponsor',
			'joinTable' => 'events_sponsors',
			'foreignKey' => 'event_id',
			'associationForeignKey' => 'sponsor_id',
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
