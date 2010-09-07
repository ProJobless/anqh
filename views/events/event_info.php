<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Event info
 *
 * @package    Events
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */

if ($event->venue->id):

	// Venue found from db
	$venue = HTML::anchor(Route::model($event->venue), HTML::chars($event->venue->name));
	$address = HTML::chars($event->venue->address) . ', ' . HTML::chars($event->venue->city_name);
	$info = HTML::anchor(Route::model($event->venue), __('Venue info'));

	if ($event->venue->latitude):
		$map = array(
			'marker'     => HTML::chars($event->venue->name),
			'infowindow' => HTML::chars($event->venue->address) . '<br />' . HTML::chars($event->venue->city_name),
			'lat'        => $event->venue->latitude,
			'long'       => $event->venue->longitude
		);
		Widget::add('foot', HTML::script_source('
$(function() {
	$(".event-info a[href=#map]").click(function() { $("#map").toggle("fast", function() { $("#map").googleMap(' .  json_encode($map) . '); }); return false; });
});
'));
	endif;

elseif ($event->venue_name):

	// No venue in db
	$venue = HTML::chars($event->venue_name);
	$address = HTML::chars($event->city_name);
	$info = $event->venue_url ? HTML::anchor($event->venue_url, HTML::chars($event->venue_url)) : '';

else:

	// Venue not set
	$venue = __('(Unknown)');
	$address = HTML::chars($event->city_name);
	$info = '';

endif;
?>

<article class="info">
	<header>
		<h4><?php echo HTML::time(Date('l ', $event->stamp_begin) . ', ' . Date::format(Date::DMY_LONG, $event->stamp_begin), $event->stamp_begin, true) ?></h4>
	</header>

	<?php if ($event->stamp_begin != $event->stamp_end): ?>
		<?php echo $event->stamp_end ?
			__('From :from to :to', array(
				':from' => HTML::time(Date::format('HHMM', $event->stamp_begin), $event->stamp_begin),
				':to'   => HTML::time(Date::format('HHMM', $event->stamp_end), $event->stamp_end)
			)) :
			__('From :from onwards', array(
				':from' => HTML::time(Date::format('HHMM', $event->stamp_begin), $event->stamp_begin),
			)) ?><br />
	<?php endif; ?>

	<?php if ($event->price == 0): ?>
	<?php echo __('Free entry') ?><br />
	<?php elseif ($event->price > 0): ?>
	<?php echo __('Tickets :price', array(':price' => '<var>' . Num::format($event->price, 2, true) . '&euro;</var>')) ?>
	<?php echo $event->price2 !== null ? ', ' . __('presale :price', array(':price' => '<var>' . Num::format($event->price2, 2, true) . '&euro;</var>')) : '' ?><br />
	<?php endif; ?>

	<?php if ($event->age > 0)
		echo  __('Age limit'), ': ', __(':years years', array(':years' => '<var>' . $event->age . '</var>')), '<br />' ?>

<?php if (!empty($event->homepage))
	echo HTML::anchor($event->homepage) ?>

	<?php if (count($event->tags)): ?>
	<br /><br /><?php foreach ($event->tags as $tag) echo $tag->name, ' '; ?>
	<?php elseif (!empty($event->music)): ?>
	<br /><br /><?php echo $event->music ?>
	<?php endif; ?>

</article>

<article class="venue">
	<header>
		<h4><?php echo $venue ?></h4>
	</header>

	<?php echo $address ?><br />

	<?php if (isset($map)): ?>
	<?php echo HTML::anchor('#map', __('Toggle map')) ?><br />
	<div id="map" style="display: none"><?php echo __('Map loading') ?></div><br />
	<?php endif; ?>

	<?php echo $info ?>
</article>

<br />
<footer class="meta">
	<?php echo __('Added'), ' ', HTML::time(Date::format(Date::DMY_SHORT, $event->created), $event->created) ?>
	<?php if ($event->modified)
		echo __('last modified'), ' ', HTML::time(Date::short_span($event->modified, false), $event->modified); ?>
</footer>
