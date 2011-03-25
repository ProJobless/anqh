<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Forum Private Area model
 *
 * @package    Forum
 * @author     Antti Qvickström
 * @copyright  (c) 2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_Forum_Private_Area extends Model_Forum_Area {

	protected $_has_many = array(
		'forum_private_topics'
	);


	/**
	 * Find active topics
	 *
	 * @static
	 * @param   integer  $limit
	 * @return  Jelly_Collection
	 *
	 * @todo  Remove
	 */
	public static function find_active($limit = 10, Model_User $user = null) {
		return null;
	}


	/**
	 * Find private areas
	 *
	 * @static
	 * @return  Jelly_Collection
	 */
	public static function find_areas() {
		return Jelly::query('forum_area')
			->where('type', '=', self::TYPE_PRIVATE)
			->select();
	}


	/**
	 * Find private message topics
	 *
	 * @static
	 * @param   Model_User  $user
	 * @param   Pagination  $paginatinon
	 * @param   string      $type
	 * @return  Jelly_Collection
	 */
	public static function find_topics(Model_User $user, Pagination $paginatinon, $type = null) {
		return Jelly::query('forum_private_topic')
			->join('forum_private_recipient')
			->on('forum_private_topic.:primary_key', '=', 'forum_private_recipient.forum_topic:foreign_key')
			->where('user_id', '=', $user->id)
			->order_by('last_post_id', 'DESC')
			->pagination($paginatinon)
			->select();
	}


	/**
	 * Check permission
	 *
	 * @param   string      $permission
	 * @param   Model_User  $user
	 * @return  boolean
	 */
	public function has_permission($permission, $user) {
		switch ($permission) {

			case self::PERMISSION_CREATE:
			case self::PERMISSION_DELETE:
			case self::PERMISSION_UPDATE:
		    return $user && $user->has_role('admin');

			case self::PERMISSION_POST:
			case self::PERMISSION_READ:
				return (bool)$user;

		}

		return false;
	}

}
