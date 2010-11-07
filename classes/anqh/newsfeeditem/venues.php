<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * NewsfeedItem Venue
 *
 * @package    Venues
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_NewsfeedItem_Venues extends NewsfeedItem {

	/**
	 * Add a new venue
	 *
	 * Data: venue_id
	 */
	const TYPE_VENUE = 'venue';

	/**
	 * Edit a venue
	 *
	 * Data: venue_id
	 */
	const TYPE_VENUE_EDIT = 'venue_edit';


	/**
	 * Get newsfeed item as HTML
	 *
	 * @static
	 * @param   Model_NewsfeedItem  $item
	 * @return  string
	 */
	public static function get(Model_NewsFeedItem $item) {
		$text = '';
		switch ($item->type) {

			case self::TYPE_VENUE:
		    $venue = Jelly::select('venue')->load($item->data['venue_id']);
		    if ($venue->loaded()) {
			    $text = __('added new venue :venue', array(
				    ':venue' => HTML::anchor(Route::model($venue), HTML::chars($venue->name))
			    ));
		    }
		    break;

			case self::TYPE_VENUE_EDIT:
		    $venue = Jelly::select('venue')->load($item->data['venue_id']);
		    if ($venue->loaded()) {
			    $text = __('updated venue :venue', array(
				    ':venue' => HTML::anchor(Route::model($venue), HTML::chars($venue->name))
			    ));
		    }
		    break;

		}

		return $text;
	}


	/**
	 * Add a new venue
	 *
	 * @param  Model_User   $user
	 * @param  Model_Venue  $venue
	 */
	public static function venue(Model_User $user = null, Model_Venue $venue = null) {
		if ($user && $venue) {
			parent::add($user, 'venues', self::TYPE_VENUE, array('venue_id' => (int)$venue->id));
		}
	}


	/**
	 * Edit a venue
	 *
	 * @param  Model_User   $user
	 * @param  Model_Venue  $venue
	 */
	public static function venue_edit(Model_User $user = null, Model_Venue $venue = null) {
		if ($user && $venue) {
			parent::add($user, 'venues', self::TYPE_VENUE_EDIT, array('venue_id' => (int)$venue->id));
		}
	}

}
