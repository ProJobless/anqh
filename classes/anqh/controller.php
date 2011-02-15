<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Abstract Anqh controller
 *
 * @abstract
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
abstract class Anqh_Controller extends Kohana_Controller {

	/**
	 * @var  boolean  AJAX-like request
	 */
	protected $ajax = false;

	/**
	 * @var  boolean  Internal request?
	 */
	protected $internal = false;

	/**
	 * @var  string  Current language
	 */
	protected $language = 'en';

	/**
	 * @var  Model_User  Current user
	 */
	protected static $user = false;


	/**
	 * Construct controller
	 */
	public function before() {
		parent::before();

		Cache::$default = 'default';

		// Check if this was an interna request or direct
		$this->internal = !Request::current()->is_initial();

		// Ajax request?
		$this->ajax = Request::current()->is_ajax();

		// Load current user, null if none
		if (self::$user === false) {
			self::$user = Visitor::instance()->get_user();
		}

	}

}
