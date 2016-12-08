tu3bones
========

CakePHP 2.3 skelton

The purpose of this is to record the steps taken create an extendable and maintainable foundation for a CakePHP 2.3+ application that can be deployed in under 10 minutes.

Every application I know of has restricted areas so to be able to identify a user is a requirement. This foundation should also be able to provide a base for SEO work.

Not to mention absolute control over database structure.

Here’s what I did:

1. Create empty database
1. Create empty repo on github using CakePHP .gitignore
1. Clone the repo locally
1. Switch to repo dir
```
cake bake project --empty .
git add .
git add --force tmp
git commit
Console/cake bake db_config
```
I always create a default as well as test configurations.
```
git submodule add https://github.com/cakephp/debug_kit.git Plugin/DebugKit
git submodule add https://github.com/CakeDC/migrations.git Plugin/Migrations
git submodule add https://github.com/CakeDC/users.git Plugin/Users
git submodule add https://github.com/CakeDC/utils.git Plugin/Utils
git submodule add https://github.com/CakeDC/search.git Plugin/Search
git submodule add https://github.com/CakeDC/tags.git Plugin/Tags
git submodule add https://github.com/predominant/goodies.git Plugin/Goodies
```
Edit Config/bootstrap.php. Add:
```
CakePlugin::load('DebugKit');
CakePlugin::load('Goodies');
CakePlugin::load('Migrations');
CakePlugin::load('Users');
CakePlugin::load('Utils');
CakePlugin::load('Tags');
CakePlugin::load('Search');
```
Edit Controller/AppController.php. Add:
```
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
```
Check the site is green and debug kit loads correctly. And it does! Now let’s move on to getting the other plugins configured and the database structure set up.

Trying to run the migrations show problems. With 2.3 the trick is to use the develop branch of the repo.
```
cd Plugin/Migrations
git checkout -b develop
```
Now we’re ready.
```
Console/cake migrations.migration run all -p Migrations
Console/cake migrations.migration run all -p Users
Console/cake migrations.migration run all -p Tags
```
Edit Controller/AppController.php. Add:
```
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
		'Html',
		'Form',
		'Session',
		'Js',
	);
```
With the Auth Component, I like to add this also:
```
/**
 * publically accessible controllers - all methods are allowed by all
 */
	public $publicControllers = array('pages');
```
And now..
```
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
```
Ok. The site still loads, so we’re looking good.

Let’s tackle the extending CakeDC’s Users plugin. I want to log in!

Create Controller/AppUsersController.php like so:
```
<?php
App::uses('UsersController', 'Users.Controller');
class AppUsersController extends UsersController {
/**
 *
 */
	public function beforeFilter() {
		parent::beforeFilter();
		$this->User = ClassRegistry::init('AppUser');
	}
/**
 *
 */
	protected function _setupAuth() {
		parent::_setupAuth();
		$this->Auth->loginRedirect = array('plugin' => null, 'admin' => false, 'controller' => 'app_users', 'action' => 'login');
	}
/**
 *
 */
	public function render($view = null, $layout = null) {
		if (is_null($view)) {
			$view = $this->action;
		}
		$viewPath = substr(get_class($this), 0, strlen(get_class($this)) - 10);
		if (!file_exists(APP . 'View' . DS . $viewPath . DS . $view . '.ctp')) {
			$this->plugin = 'Users';
		} else {
			$this->viewPath = $viewPath;
		}
		return parent::render($view, $layout);
	}
}
```
Create Model/AppUser.php like so:
```
<?php
App::import('Model', 'Users.User');
class AppUser extends User {
	public $useTable = 'users';
	public $alias = 'User';

/**
 * Constructor
 *
 * @param type $id
 * @param type $table
 * @param type $ds
 */
	public function __construct($id = false, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);
	}
}
```
Edit Config/routes.php. 
Add:
```
/**
 * Routes for the AppUsers extensions of Users plugin. app_users isn't as pretty as users
 */
	Router::connect('/users', array('plugin' => null, 'controller' => 'AppUsers'));
	Router::connect('/users/:action/*', array('plugin' => null, 'controller' => 'AppUsers'));
	Router::connect('/users/users/:action/*', array('plugin' => null, 'controller' => 'AppUsers'));
	Router::connect('/admin/users', array('plugin' => null, 'controller' => 'AppUsers', 'admin' => true));
	Router::connect('/admin/users/:action/*', array('plugin' => null, 'controller' => 'AppUsers', 'admin' => true));
	Router::connect('/admin/users/users/:action/*', array('plugin' => null, 'controller' => 'AppUsers', 'admin' => true));
```
Create a directory for user views:
```
mkdir View/AppUsers
```
 
Next up is to add a user. But first we want to tag the user and that means some more plugin extending. That will come in a future installment of this series.
 
Happy Baking!
