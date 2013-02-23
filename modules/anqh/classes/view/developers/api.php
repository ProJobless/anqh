<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Developers_API
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2013 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Developers_API extends View_Section {

	/**
	 * Create new view.
	 */
	public function __construct() {
		parent::__construct();
	}


	/**
	 * Print document table.
	 *
	 * @param   array  $parameters
	 * @return  string
	 */
	public static function parameter_table(array $parameters) {
		ob_start();

?>

<table class="table table-bordered">
	<thead>
		<tr>
			<th class="span1">Parameter</th>
			<th class="span2">Values</th>
			<th class="span5">Description</th>
		</tr>
	</thead>
	<tbody>

		<?php foreach ($parameters as $parameter => $document): ?>
		<tr>
			<td><code><?= $parameter ?></code></td>
			<td><?= $document[0] ?></td>
			<td><?= $document[1] ?></td>
		</tr>
		<?php endforeach ?>

	</tbody>
</table>

<?php

		return ob_get_clean();
	}


	/**
	 * Render view.
	 *
	 * @return  string
	 */
	public function content() {
		ob_start();

?>

<h2 id="api">API</h2>

<h3 id="api-overview">Overview</h3>

<p>
	You can fetch public read-only data with our <strong>REST API</strong> using simple <strong>HTTP GET requests</strong>.
	The response is formatted in <strong>JSON</strong>.
	<!--Response can be formatted in either <strong>JSON</strong> (recommended) or <strong>XML</strong>.<br />-->
	Parameters taking multiple values are separated with a colon <code>:</code>
</p>

<p>
	API base URL:
		<pre>http://api.klubitus.org/v1</pre>
	<!--or
		<pre>https://api.klubitus.org/v1</pre>-->
	<!--Use extension <code>.json</code> for JSON response and <code>.xml</code> for XML.-->
</p>

<hr />

<h3 id="api-examples">Example</h3>

<hr />

<h3 id="api-events">Events</h3>

<h4 id="api-events-common">Common parameters</h4>

<?= self::parameter_table(array(
	'field' => array('
<code>all</code><br />
<code>age</code><br />
<code>city</code><br />
<code>country</code><br />
<code>created</code><br />
<code>dj</code><br />
<code>favorite_count</code><br />
<code>flyer_front</code><br />
<code>flyer_front_icon</code><br />
<code>flyer_front_thumb</code><br />
<code>flyer_back</code><br />
<code>flyer_back_icon</code><br />
<code>flyer_back_thumb</code><br />
<code>homepage</code><br />
<code>id</code><br />
<code>info</code><br />
<code>modified</code><br />
<code>music</code><br />
<code>name</code><br />
<code>price</code><br />
<code>stamp_begin</code><br />
<code>stamp_end</code><br />
<code>url</code><br />
<code>venue</code>
', 'Fetchable fields.')
)) ?>

<hr />

<h4 id="api-events-browse">Browse</h4>

<pre>http://api.klubitus.org/v1/events/browse?<em>{parameters}</em></pre>

<?= self::parameter_table(array(

	'field' => array('', HTML::anchor('#api-events-common', 'See common parameters.')),

	'from' => array('
<code>today</code> (default)<br />
<em>unix timestamp</em><br />
<em>textual datetime description</em> parsed with <strong>strtotime</strong>, e.g. <code>yesterday</code>, <code>next monday</code>, <code>last month</code>
', 'Initial date to start browsing.'),

	'limit' => array('
<em>count</em>, max 500<br />
<em>date span</em>, e.g. <code>1m</code>, <code>1w</code> (default), <code>1d</code>
', 'How many events to load.'),

	'order' => array('
<code>asc</code> (default)<br />
<code>desc</code>
', 'Browsing order, <code>asc</code> for upcoming events, <code>desc</code> for past events.'),

)) ?>

<hr />

<h4 id="api-events-event">Event</h4>

<pre>http://api.klubitus.org/v1/events/event?<em>{parameter}</em></pre>

<?= self::parameter_table(array(
	'id' => array('
<em>numeric id</em>
', 'Load all data from given event, i.e. does <em>not</em> use <code>field</code> parameter.')
)) ?>

<hr />

<h4 id="api-events-search">Search</h4>

<pre>http://api.klubitus.org/v1/events/search?<em>{parameters}</em></pre>

<?= self::parameter_table(array(

	'field' => array('', HTML::anchor('#api-events-common', 'See common parameters.')),

	'filter' => array('
<code>upcoming</code><br />
<code>past</code><br />
<code>date:<em>from-to</em></code><br />
', 'Filter results by date. <code>from</code> and <code>to</code> are unix timestamps and you may leave either one empty to query for events up to or onwards.'),

	'limit' => array('
<em>count</em>, max 500<br />
', 'How many events to search.'),

	'order' => array('
<em>field.order</em>, e.g. <code>name.asc</code>, <code>city.asc:name.asc</code>
', 'Sort search results by this field, supports multiple fields with colon as separator'),

	'q' => array('
<em>search term</em>, minimum 3 characters
', 'Term to search for.'),

	'search' => array('
<code>name</code><br />
<code>venue</code><br />
<code>city</code><br />
<code>dj</code>
', 'Field(s) to search from.'),

)) ?>

<hr />

<h3 id="api-venues">Venues</h3>

<hr />

<h3 id="api-members">Members</h3>

<?php

		return ob_get_clean();
	}

}