<?php
/**
 * Application level Controller
 *
 * This file is application-wide controller file. You can put all
 * application-wide controller-related methods here.
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
App::uses('Controller', 'Controller');

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @package		app.Controller
 * @link		http://book.cakephp.org/2.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller {
/**
 * Components
 *
 * @var array
 */
	public $components = array(
		'Auth',
		'Session',
		'Cookie',
		'Paginator',
		'Security',
		'Email',
		'RequestHandler',
	);
/**
 * Helpers
 *
 * @var array
 */
	public $helpers = array(
		'Goodies.AutoJavascript',
		'Goodies.GoogleAnalytics',
		'Goodies.Gravatar',
		'Html',
		'Form',
		'Session',
		'Js',
	);
/**
 * publically accessible controllers - all methods are allowed by all
 */
	public $publicControllers = array('pages');
/**
 * Constructor
 *
 * @param mixed $request
 * @param mixed $response
 */
	public function __construct($request = null, $response = null) {
		parent::__construct($request, $response);
		if (Configure::read('debug')) {
			$this->components[] = 'DebugKit.Toolbar';
		}
	}
/**
 * beforeFilter callback
 */
	public function beforeFilter() {
		$this->Auth->authorize = array('Controller');
		if (in_array(strtolower($this->params['controller']), $this->publicControllers)) {
			$this->Auth->allow();
		}

		$this->Cookie->name = 'tu3bonesRememberMe';
		$this->Cookie->time = '1 Month';
		$cookie = $this->Cookie->read('User');

		if (!empty($cookie) && !$this->Auth->user()) {
			$data['User']['username'] = '';
			$data['User']['password'] = '';
			if (is_array($cookie)) {
				$data['User']['username'] = $cookie['username'];
				$data['User']['password'] = $cookie['password'];
			}
			if (!$this->Auth->login($data)) {
				$this->Cookie->destroy();
				$this->Auth->logout();
			}
		}
	}
/**
 * isAuthorized
 *
 * @return boolean
 */
	public function isAuthorized() {
		if ($this->Auth->user() && $this->params['prefix'] != 'admin') {
			return true;
		}
		if ($this->params['prefix'] == 'admin' && $this->Auth->user('is_admin')) {
			return true;
		}
		return false;
	}
}
