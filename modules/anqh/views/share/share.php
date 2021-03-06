<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * AddThis
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2011-2013 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
$attributes = array();

// Custom URL
if ($url = Anqh::page_meta('url')) {
	$attributes['addthis:url'] = $url;
}

// Custom title
if ($title = Anqh::page_meta('title')) {
	$attributes['addthis:title'] = $title;
}

?>
<div class="addthis_toolbox addthis_pill_combo"<?= HTML::attributes($attributes) ?>>
	<a class="addthis_button_facebook_like"></a>
	<a class="addthis_button_tweet" tw:count="horizontal"></a>
	<a class="addthis_counter addthis_pill_style"></a>
</div>
