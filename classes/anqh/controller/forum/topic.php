<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Anqh Forum Topic controller
 *
 * @package    Forum
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Controller_Forum_Topic extends Controller_Forum {

	/**
	 * Construct controller
	 */
	public function before() {
		parent::before();

		$this->tabs = null;
		Widget::add('head', HTML::script('js/jquery.markitup.pack.js'));
		Widget::add('head', HTML::script('js/markitup.bbcode.js'));
	}


	/**
	 * Action: delete
	 */
	public function action_delete() {
		$id = (int)$this->request->param('id');

		// Are we deleting a post, if so we have a topic_id
		$topic_id = (int)$this->request->param('topic_id');
		if ($topic_id) {
			return $this->_delete_post($topic_id, $id);
		}

		return $this->_delete_topic($id);

	}


	/**
	 * Action: edit
	 */
	public function action_edit() {
		$id = (int)$this->request->param('id');

		// Are we editing a post, if so we have a topic_id
		$topic_id = (int)$this->request->param('topic_id');
		if ($topic_id) {
			return $this->_edit_post($topic_id, $id);
		}

		return $this->_edit_topic(null, $id);
	}


	/**
	 * Action: index
	 */
	public function action_index() {

		// Go to post?
		$topic_id = (int)$this->request->param('topic_id');
		if ($topic_id) {
			$post_id = (int)$this->request->param('id');
		} else {
			$topic_id = (int)$this->request->param('id');
		}

		// Load topic
		$topic = Jelly::select('forum_topic')->load($topic_id);
		if (!$topic->loaded()) {
			throw new Model_Exception($topic, $topic_id);
		}
		Permission::required($topic, Model_Forum_Topic::PERMISSION_READ, self::$user);

		// Did we request single post with ajax?
		if ($this->ajax && isset($post_id)) {
			$this->history = false;
			$post = Jelly::select('forum_post')->load($post_id);
			if (!$post->loaded()) {
				throw new Model_Exception($topic, $topic_id);
			}

			// Permission is already checked by the topic, no need to check for post

			echo View::factory('forum/post', array('topic' => $topic, 'post'  => $post, 'user'  => self::$user));
			return;
		}

		// Update read counter if not owner
		if (!self::$user || $topic->author != self::$user) {
			$topic->num_reads++;
			$topic->save();
		}

		// Set title
		$this->_set_title($topic);

		// Set actions
		if (Permission::has($topic, Model_Forum_Topic::PERMISSION_UPDATE, self::$user)) {
			$this->page_actions[] = array('link' => Route::model($topic, 'edit'), 'text' => __('Edit topic'), 'class' => 'topic-edit');
		}
		if (Permission::has($topic, Model_Forum_Topic::PERMISSION_POST, self::$user)) {
			$this->page_actions[] = array('link' => '#reply', 'text' => __('Reply to topic'), 'class' => 'topic-post');
		}

		// Pagination
		$per_page = 20;
		$pagination = Pagination::factory(array(
			'items_per_page' => $per_page,
			'total_items'    => max(1, $topic->num_posts),
		));
		if (Arr::get($_GET, 'page') == 'last') {
			$pagination->last();
		}

		// Posts
		Widget::add('main', View_Module::factory('forum/topic', array(
			'mod_class'  => 'topic articles topic-' . $topic->id,
			'user'       => self::$user,
			'topic'      => $topic,
			'posts'      => $topic->get('posts')->pagination($pagination)->execute(),
			'pagination' => $pagination
		)));

		// Reply
		if (Permission::has($topic, Model_Forum_Topic::PERMISSION_POST, self::$user)) {
			$form = array(
				'action' => Route::model($topic, 'reply'),
				'values' => Jelly::factory('forum_post'),
				'groups' => array(
					array(
						'fields' => array(
							'post' => array('label' => __('Reply')),
						),
					),
				)
			);

			Widget::add('main', View_Module::factory('form/anqh', array(
				'mod_id' => 'reply',
				'form'   => $form
			)));
		}

		$this->side_views();
	}


	/**
	 * Action: post
	 */
	public function action_post() {
		return $this->_edit_topic((int)$this->request->param('id'));
	}


	/**
	 * Action: quote
	 */
	public function action_quote() {
		return $this->_edit_post((int)$this->request->param('topic_id'), null, (int)$this->request->param('id'));
	}


	/**
	 * Action: reply
	 */
	public function action_reply() {
		return $this->_edit_post((int)$this->request->param('id'));
	}


	/**
	 * Delete forum post
	 *
	 * @param  integer  $topic_id
	 * @param  integer  $post_id
	 */
	protected function _delete_post($topic_id, $post_id) {
		$this->history = false;

		// Topic is always loaded, avoid haxing attempts to edit posts from wrong topics
		$topic = Jelly::select('forum_topic')->load($topic_id);
		if (!$topic->loaded()) {
			throw new Model_Exception($topic, $topic_id);
		}

		// Editing a post
		$post = Jelly::select('forum_post')->load($post_id);
		if (!$post->loaded() || $post->topic->id != $topic->id) {
			throw new Model_Exception($post, $post_id);
		}
		Permission::required($post, Model_Forum_Post::PERMISSION_DELETE, self::$user);

		$post->delete();
		$topic->refresh();
		$topic->area->num_posts--;
		$topic->area->save();

		if ($this->ajax) {
			return;
		}

		$this->request->redirect(Route::model($topic));
	}


	/**
	 * Delete forum topic
	 *
	 * @param  integer  $topic_id
	 */
	protected function _delete_topic($topic_id) {
		$this->history = false;

		// Topic is always loaded, avoid haxing attempts to edit posts from wrong topics
		$topic = Jelly::select('forum_topic')->load($topic_id);
		if (!$topic->loaded()) {
			throw new Model_Exception($topic, $topic_id);
		}

		Permission::required($topic, Model_Forum_Topic::PERMISSION_DELETE, self::$user);

		$area  = $topic->area;
		$posts = $topic->num_posts;
		$topic->delete();
		$area->num_posts -= $posts;
		$area->num_topics--;
		$area->save();

		$this->request->redirect(Route::model($area));
	}


	/**
	 * Edit forum post
	 *
	 * @param  integer  $topic_id   When replying to a topic
	 * @param  integer  $post_id    When editing a post
	 * @param  integer  $quote_id   When quoting a post
	 */
	protected function _edit_post($topic_id, $post_id = null, $quote_id = null) {
		$this->history = false;

		// Topic is always loaded, avoid haxing attempts to edit posts from wrong topics
		$topic = Jelly::select('forum_topic')->load($topic_id);
		if (!$topic->loaded()) {
			throw new Model_Exception($topic, $topic_id);
		}
		Permission::required($topic, Model_Forum_Topic::PERMISSION_POST, self::$user);

		if ($post_id) {

			// Editing a post
			$post = Jelly::select('forum_post')->load($post_id);
			if (!$post->loaded() || $post->topic->id != $topic->id) {
				throw new Model_Exception($post, $post_id);
			}
			Permission::required($post, Model_Forum_Post::PERMISSION_UPDATE, self::$user);
			$label = __('Edit post');

		} else {

			// New reply
			$post = Jelly::factory('forum_post');
			$label = __('Reply');

		}

		// Quoting a post
		if ($quote_id) {
			$quote = Jelly::select('forum_post')->load($quote_id);
			if (!$quote->loaded() || $quote->topic->id != $topic->id) {
				throw new Model_Exception($quote, $quote_id);
			}
			Permission::required($quote, Model_Forum_Post::PERMISSION_READ, self::$user);

			$label = __('Quote');
			if (!$post->loaded()) {
				$post->post = '[quote author="' . $quote->author_name . '" post="' . $quote->id . '"]' . $quote->post . "[/quote]\n\n";
			}
		}

		$this->_set_title($topic);

		// Handle post
		$errors = array();
		if ($_POST && Security::csrf_valid()) {

			$post->post        = $_POST['post'];
			$post->author_ip   = Request::$client_ip;
			$post->author_host = Request::host_name();
			if (!$post->loaded()) {

				// New post
				$post->topic       = $topic;
				$post->area        = $topic->area;
				$post->author      = self::$user;
				$post->author_name = self::$user->username;
				if ($quote_id) {
					$post->parent = $quote;
				}
				$increase = true;

			} else {

				// Old post
				$post->modifies++;
				$post->modified = time();
				$increase = false;

			}

			try {
				$post->save();
				if ($increase) {

					// Topic
					$topic->num_posts++;
					$topic->last_post   = $post;
					$topic->last_posted = $post->created;
					$topic->last_poster = $post->author_name;
					$topic->save();

					// Area
					$area = $topic->area;
					$area->num_posts++;
					$area->last_topic = $topic;
					$area->save();

					// User
					self::$user->num_posts++;
					self::$user->save();

					// News feed
					NewsfeedItem_Forum::reply(self::$user, $post);

				}

				if ($this->ajax) {
					return Request::factory(Route::get('forum_post')->uri(array('topic_id' => Route::model_id($topic), 'id' => $post->id)))->execute();
				}

				$this->request->redirect(Route::model($topic, '?page=last#last'));
			} catch (Validate_Exception $e) {
				$errors = $e->array->errors('validate');
			}
		}

		// Build form
		$form = array(
			'values' => $post,
			'errors' => $errors,
			'cancel' => $this->ajax
				? Route::get('forum_post')->uri(array('topic_id' => Route::model_id($topic), 'id' => $quote_id ? $quote->id : $post->id))
				: Request::back(Route::model($topic), true),
			'groups' => array(
				array(
					'fields' => array(
						'post' => array('label' => $label),
					),
				),
			)
		);

		if ($this->ajax) {

			// Needed for cancel ajax
			if ($quote_id) {
				$form['attributes'] = array('id' => 'quote');
			}

			echo View::factory('form/anqh', array('form' => $form));
			return;
		}

		Widget::add('main', View_Module::factory('form/anqh', array('form' => $form)));
	}


	/**
	 * Edit forum topic
	 *
	 * @param  integer  $area_id
	 * @param  integer  $topic_id
	 */
	protected function _edit_topic($area_id = null, $topic_id = null) {
		$this->history = false;

		if ($area_id) {

			// Start new topic
			$area = Jelly::select('forum_area')->load($area_id);
			if (!$area->loaded()) {
				throw new Model_Exception($area, $area_id);
			}
			Permission::required($area, Model_Forum_Area::PERMISSION_POST, self::$user);
			$this->page_title = HTML::chars($area->name);
			$topic = Jelly::factory('forum_topic');
			$post  = Jelly::factory('forum_post');
			$cancel = Route::model($area);

			// Build form
			$form = array(
				'values' => $topic,
				'save'   => array(
					'attributes' => array('tabindex' => 3)
				),
				'cancel' => $cancel,
				'groups' => array(
					array(
						'fields' => array(
							'name' => array(
								'attributes' => array('tabindex' => 1)
							),
							'post' => array(
								'attributes' => array('tabindex' => 2),
								'model' => $post
							),
						),
					),
				)
			);

		} else {

			// Edit old topic
			$topic = Jelly::select('forum_topic')->load($topic_id);
			if (!$topic->loaded()) {
				throw new Model_Exception($topic, $topic_id);
			}
			Permission::required($topic, Model_Forum_Topic::PERMISSION_UPDATE, self::$user);
			$this->_set_title($topic);
			$cancel = Route::model($topic);

			// Set actions
			if (Permission::has($topic, Model_Forum_Topic::PERMISSION_DELETE, self::$user)) {
				$this->page_actions[] = array('link' => Route::model($topic, 'delete'), 'text' => __('Delete topic'), 'class' => 'topic-delete');
			}

			// Build form
			$form = array(
				'values' => $topic,
				'cancel' => $cancel,
				'groups' => array(
					array(
						'fields' => array(
							'name'   => array(),
							'status' => array(),
						),
					),
				),
			);
		}

		$errors = array();
		if ($_POST && Security::csrf_valid()) {
			if (isset($post)) {

				// New topic
				$post->post        = $_POST['post'];
				$post->area        = $area;
				$post->author      = self::$user;
				$post->author_name = self::$user->username;
				$post->author_ip   = Request::$client_ip;
				$post->author_host = Request::host_name();
				try {
					$post->validate();
				} catch (Validate_Exception $e) {
					$errors += $e->array->errors('validate');
				}

				$topic->name = $_POST['name'];
				$topic->area = $area;
				try {
					$topic->validate();
				} catch (Validate_Exception $e) {
					$errors += $e->array->errors('validate');
				}

				// If no errors found, save models
				if (empty($errors)) {
					$topic->save();

					// Post
					$post->topic = $topic;
					$post->save();

					// Topic
					$topic->first_post  = $topic->last_post   = $post;
					$topic->last_poster = self::$user->username;
					$topic->last_posted = time();
					$topic->num_posts   = 1;
					$topic->save();

					// Area
					$area->last_topic = $topic;
					$area->num_posts++;
					$area->num_topics++;
					$area->save();

					// User
					self::$user->num_posts++;
					self::$user->save();

					// News feed
					NewsfeedItem_Forum::topic(self::$user, $topic);

					$this->request->redirect(Route::model($topic));
				}

			} else {

				// Old topic
				$topic->set(Arr::extract($_POST, array('name', 'status')));
				try {
					$topic->save();
					$this->request->redirect(Route::model($topic));
				} catch (Validate_Exception $e) {
					$errors = $e->array->errors('validate');
				}

			}
		}
		$form['errors'] = $errors;

		Widget::add('main', View_Module::factory('form/anqh', array('form' => $form)));
	}


	/**
	 * Set page title
	 *
	 * @param  Model_Forum_Topic  $topic
	 */
	protected function _set_title(Model_Forum_Topic $topic = null) {
		switch ($topic->status) {
			case Model_Forum_Topic::STATUS_LOCKED: $prefix = '<span class="locked">' . __('[Locked]') . '</span> '; break;
			case Model_Forum_Topic::STATUS_SINK:   $prefix = '<span class="sink">' . __('[Sink]') . '</span> '; break;
			default: $prefix = '';
		}
		$this->page_title = $prefix . HTML::chars($topic->name());
		$this->page_subtitle = __('Area :area. ', array(
			':area' => HTML::anchor(Route::model($topic->area), HTML::chars($topic->area->name), array('title' => strip_tags($topic->area->description)))
		));
		$this->page_subtitle .= HTML::icon_value(array(':views' => $topic->num_reads), ':views view', ':views views', 'views');
		$this->page_subtitle .= HTML::icon_value(array(':replies' => $topic->num_posts - 1), ':replies reply', ':replies replies', 'posts');
	}

}
