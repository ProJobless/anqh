<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Anqh Blog controller
 *
 * @package    Blog
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Controller_Blog extends Controller_Template {

	/**
	 * Action: add new blog entry
	 */
	public function action_add() {
		return $this->_edit_entry();
	}


	/**
	 * Action: comment
	 */
	public function action_comment() {
		$comment_id = (int)$this->request->param('id');
		$action     = $this->request->param('commentaction');

		// Load blog_comment
		$comment = Model_Blog_Comment::factory($comment_id);
		if (($action == 'delete' || $action == 'private') && Security::csrf_valid() && $comment->loaded()) {
			$entry = $comment->blog_entry();
			switch ($action) {

				// Delete comment
				case 'delete':
			    if (Permission::has($comment, Model_Blog_Comment::PERMISSION_DELETE, self::$user)) {
				    $comment->delete();
				    $entry->comment_count--;
				    $entry->save();
			    }
			    break;

				// Set comment as private
			  case 'private':
				  if (Permission::has($comment, Model_Blog_Comment::PERMISSION_UPDATE, self::$user)) {
					  $comment->private = true;
					  $comment->save();
				  }
			    break;

			}
			if (!$this->ajax) {
				$this->request->redirect(Route::model($entry));
			}
		}

		if (!$this->ajax) {
			Request::back('blogs');
		}
	}


	/**
	 * Action: edit blog entry
	 */
	public function action_edit() {
		return $this->_edit_entry((int)$this->request->param('id'));
	}


	/**
	 * Action: blog entry
	 */
	public function action_entry() {
		$entry_id = (int)$this->request->param('id');

		// Load blog entry
		$entry = Model_Blog_Entry::factory($entry_id);
		if (!$entry->loaded()) {
			throw new Model_Exception($entry, $entry_id);
		}
		Permission::required($entry, Model_Blog_Entry::PERMISSION_READ, self::$user);

		// Set title
		$this->page_title    = HTML::chars($entry->name);
		$this->page_subtitle = __('By :user :ago', array(
			':user'  => HTML::user($entry->author()),
			':ago'   => HTML::time(Date::fuzzy_span($entry->created), $entry->created)
		));

		// Set actions
		if (Permission::has($entry, Model_Blog_Entry::PERMISSION_UPDATE, self::$user)) {
			$this->page_actions[] = array('link' => Route::model($entry, 'edit'), 'text' => __('Edit blog entry'), 'class' => 'blog-edit');
		}

		// Content
		Widget::add('main', View_Module::factory('blog/entry', array(
			'entry' => $entry
		)));

		// Comments section
		if (Permission::has($entry, Model_Blog_Entry::PERMISSION_COMMENTS, self::$user)) {
			$errors = array();
			$values = array();

			if ($_POST && Permission::has($entry, Model_Blog_Entry::PERMISSION_COMMENT, self::$user)) {

				// Handle comment
				try {
					$comment = Model_Blog_Comment::factory()->
						add(self::$user->id, $entry, Arr::get($_POST, 'comment') ,Arr::get($_POST, 'private'));

					$entry->comment_count++;
					$entry->new_comment_count++;
					$entry->save();

					// Newsfeed
					if (!$comment->private) {
						NewsfeedItem_Blog::comment(self::$user, $entry);
					}

					if (!$this->ajax) {
						$this->request->redirect(Route::model($entry));
					}
				} catch (Validation_Exception $e) {
					$errors = $e->array->errors('validation');
					$values = $comment;
				}

			}

			$view = View_Module::factory('generic/comments', array(
				'mod_title'  => __('Comments'),
				'delete'     => Route::get('blog_comment')->uri(array('id' => '%d', 'commentaction' => 'delete')) . '?' . Security::csrf_query(),
				'private'    => Route::get('blog_comment')->uri(array('id' => '%d', 'commentaction' => 'private')) . '?' . Security::csrf_query(),
				'comments'   => $entry->comments(self::$user),
				'errors'     => $errors,
				'values'     => $values,
				'pagination' => null,
				'user'       => self::$user,
			));

			if ($this->ajax) {
				echo $view;
				return;
			}
			Widget::add('main', $view);
		}

		// Update counts
		if (self::$user && self::$user->id == $entry->author_id) {

			// Clear new comment counts for owner
			if ($entry->new_comment_count) {
				$entry->new_comment_count = 0;
				$entry->save();
			}

		} else {
			$entry->view_count++;
			$entry->save();
		}

	}


	/**
	 * Controller default action
	 */
	public function action_index() {
		$this->page_title = __('Blogs');

		// Set actions
		if (Permission::has(new Model_Blog_Entry, Model_Blog_Entry::PERMISSION_CREATE, self::$user)) {
			$this->page_actions[] = array('link' => Route::get('blogs')->uri(array('action' => 'add')), 'text' => __('Add blog entry'), 'class' => 'blog-add');
		}

		Widget::add('main', View_Module::factory('blog/entries', array(
			'mod_class' => 'blog_entries',
			'entries'   => Model_Blog_Entry::factory()->find_new(20),
		)));
	}


	/**
	 * Edit entry
	 *
	 * @param  integer  $entry_id
	 */
	protected function _edit_entry($entry_id = null) {
		$this->history = false;

		if ($entry_id) {

			// Editing old
			$entry = Model_Blog_Entry::factory($entry_id);
			if (!$entry->loaded()) {
				throw new Model_Exception($entry, $entry_id);
			}
			Permission::required($entry, Model_Blog_Entry::PERMISSION_UPDATE, self::$user);
			$cancel = Route::model($entry);
			$this->page_title = HTML::chars($entry->name);

		} else {

			// Creating new
			$entry = Model_Blog_Entry::factory();
			Permission::required($entry, Model_Blog_Entry::PERMISSION_CREATE, self::$user);
			$cancel = Request::back(Route::get('blogs')->uri(), true);
			$newsfeed = true;
			$this->page_title = __('New blog entry');
			$entry->author = self::$user;

		}

		// Handle post
		$errors = array();
		if ($_POST && Security::csrf_valid()) {
			$entry->set_fields(Arr::intersect($_POST, Model_Blog_Entry::$editable_fields));
			try {
				$entry->save();

				// Newsfeed
				if (isset($newsfeed)) {
					NewsfeedItem_Blog::entry(self::$user, $entry);
				}

				$this->request->redirect(Route::model($entry));
			} catch (Validation_Exception $e) {
				$errors = $e->array->errors('validation');
			}
		}

		// Build form
		$form = array(
			'values' => $entry,
			'errors' => $errors,
			'cancel' => $cancel,
			'groups' => array(
				array(
					'fields' => array(
						'name'    => array(),
						'content' => array(),
					),
				),
			)
		);

		Widget::add('head', HTML::script('js/jquery.markitup.pack.js'));
		Widget::add('head', HTML::script('js/markitup.bbcode.js'));
		Widget::add('main', View_Module::factory('form/anqh', array('form' => $form)));
	}

}
