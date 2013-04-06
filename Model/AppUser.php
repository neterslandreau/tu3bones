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
