<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Anqh Forum Area controller
 *
 * @package    Forum
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2012 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Controller_Forum_Area extends Controller_Forum {

	/**
	 * Construct controller
	 */
	public function before() {
		parent::before();

		$this->tab_id = 'areas';
	}


	/**
	 * Action: add
	 */
	public function action_add() {
		$this->action_edit();
	}


	/**
	 * Action: delete
	 */
	public function action_delete() {
		$this->history = false;

		$area_id = (int)$this->request->param('id');
		$area = Model_Forum_Area::factory($area_id);
		if (!$area->loaded()) {
			throw new Model_Exception($area, $area_id);
		}
		Permission::required($area, Model_Forum_Area::PERMISSION_DELETE, self::$user);

		$group = $area->group();
		$area->delete();

		$this->request->redirect(Route::model($group));
	}


	/**
	 * Action: edit
	 */
	public function action_edit() {
		$this->history = false;

		// Load area
		$area_id = (int)$this->request->param('id');
		if ($area_id) {
			$area = Model_Forum_Area::factory($area_id);
			if (!$area->loaded()) {
				throw new Model_Exception($area, $area_id);
			}
			Permission::required($area, Model_Forum_Area::PERMISSION_UPDATE, self::$user);
		} else {
			$area = new Model_Forum_Area();
			$area->author_id = self::$user->id;
			$area->created   = time();
		}

		// Load group
		if ($area->loaded()) {
			$group = $area->group();
		} else if ($group_id = (int)$this->request->param('group_id')) {
			$group = Model_Forum_Group::factory($group_id);
			$area->forum_group_id = $group->id;
			if (!$group->loaded()) {
				throw new Model_Exception($group, $group_id);
			}
			Permission::required($group, Model_Forum_Group::PERMISSION_CREATE_AREA, self::$user);
		}

		// Handle post
		$errors = array();
		if ($_POST) {
			$area->set_fields(Arr::extract($_POST, Model_Forum_Area::$editable_fields));
			try {
				$area->save();
				$this->request->redirect(Route::model($area));
			} catch (Validation_Exception $e) {
				$errors = $e->array->errors('validate');
			}
		}


		// Build page
		$this->view = new View_Page(__('Forum area') . ($area->name ? ': ' . HTML::chars($area->name) : ''));

		// Set actions
		if ($area->loaded() && Permission::has($area, Model_Forum_Area::PERMISSION_DELETE, self::$user)) {
			$this->page_actions[] = array(
				'link'  => Route::model($area, 'delete'),
				'text'  => '<i class="icon-trash icon-white"></i> ' . __('Delete area'),
				'class' => 'btn btn-danger area-delete',
			);
		}

		$this->view->add(View_Page::COLUMN_MAIN, $this->section_edit($area, $errors));
	}


	/**
	 * Action: index
	 */
	public function action_index() {

		// Load area
		$area_id = $this->request->param('id');

		// Private area?
		if ($area_id == 'private') {
			return $this->action_messages();
		}

		/** @var  Model_Forum_Area  $area */
		$area = Model_Forum_Area::factory((int)$area_id);
		if (!$area->loaded()) {
			throw new Model_Exception($area, (int)$area_id);
		}
		Permission::required($area, Model_Forum_Area::PERMISSION_READ, self::$user);


		// Build page
		$this->view = new View_Page($area->name);
		$this->view->subtitle  = $area->description;

		// Set actions
		if (Permission::has($area, Model_Forum_Area::PERMISSION_POST, self::$user)) {
			$this->page_actions[] = array(
				'link'  => Route::model($area, 'post'),
				'text'  => '<i class="icon-plus-sign icon-white"></i> ' . __('New topic'),
				'class' => 'btn btn-primary topic-add'
			);
		}
		if (Permission::has($area, Model_Forum_Area::PERMISSION_UPDATE, self::$user)) {
			$this->page_actions[] = array(
				'link'  => Route::model($area, 'edit', false),
				'text'  => '<i class="icon-edit"></i> ' . __('Edit area'),
				'class' => 'btn area-edit',
			);
		}

		// Pagination
		$pagination = $this->section_pagination($area->topic_count);
		$this->view->add(View_Page::COLUMN_MAIN, $pagination);

		// Posts
		$this->view->add(View_Page::COLUMN_MAIN, $this->section_topics($area->find_active_topics($pagination->offset, $pagination->items_per_page)));

		// Pagination
		$this->view->add(View_Page::COLUMN_MAIN, $pagination);

		$this->_side_views();
	}


	/**
	 * Action: private
	 */
	public function action_messages() {
		Permission::required(new Model_Forum_Private_Area, Model_Forum_Private_Area::PERMISSION_READ, self::$user);

		// Build page
		$this->view = new View_Page(__('Private messages'));
		$this->view->subtitle  = __('Personal and group messages');

		// Set actions
		if (Permission::has(new Model_Forum_Private_Area, Model_Forum_Private_Area::PERMISSION_POST, self::$user)) {
			$this->page_actions[] = array(
				'link'  => Route::url('forum_private_topic_add', array('action' => 'post')),
				'text'  => '<i class="icon-plus-sign icon-white"></i> ' . __('New message'),
				'class' => 'btn btn-primary topic-add'
			);
		}

		// Pagination
		$pagination = $this->section_pagination(Model_Forum_Private_Topic::factory()->get_count(self::$user));
		$this->view->add(View_Page::COLUMN_MAIN, $pagination);

		// Posts
		$this->view->add(
			View_Page::COLUMN_MAIN,
			$this->section_topics_private(Model_Forum_Private_Area::factory()->find_topics(self::$user, $pagination->offset, $pagination->items_per_page))
		);

		// Pagination
		$this->view->add(View_Page::COLUMN_MAIN, $pagination);

		$this->_side_views();
	}


	/**
	 * Get forum area edit form.
	 *
	 * @param  Model_Forum_Area  $area
	 * @param  array             $errors
	 */
	public function section_edit($area, $errors) {
		$section = new View_Forum_AreaEdit($area);
		$section->errors = $errors;

		return $section;
	}


	/**
	 * Get pagination.
	 *
	 * @param   integer  $topics
	 * @return  View_Generic_Pagination
	 */
	public function section_pagination($topics = 0) {
		return new View_Generic_Pagination(array(
			'previous_text'  => '&laquo; ' . __('Previous page'),
			'next_text'      => __('Next page') . ' &raquo;',
			'items_per_page' => Kohana::config('forum.topics_per_page'),
			'total_items'    => $topics,
		));
	}


	/**
	 * Get bigger private topic list view.
	 *
	 * @param   Model_Forum_Private_Topic[]  $topics
	 * @return  View_Topics_Private
	 */
	public function section_topics_private($topics) {
		return new View_Topics_Private($topics);
	}

}
