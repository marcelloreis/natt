<?php
App::uses('EventsController', 'Controller');

/**
 * EventsController Test Case
 *
 */
class EventsControllerTest extends ControllerTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.event',
		'app.state',
		'app.country',
		'app.city',
		'app.student',
		'app.grid',
		'app.inscription',
		'app.marketing',
		'app.panelist',
		'app.events_panelist',
		'app.responsible',
		'app.events_responsible',
		'app.sponsor',
		'app.events_sponsor'
	);

}
