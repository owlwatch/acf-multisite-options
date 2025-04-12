<?php
/**
 * Adds support for saving/retrieving values from term meta.
 *
 * @package    AdvancedCustomFields
 * @subpackage Meta
 * @author     WP Engine
 */

namespace ACF\Multisite\Options\MetaLocation;

use ACF\Meta\MetaLocation;

/**
 * A class to add support for saving to term meta.
 */
class Blog extends MetaLocation {

	/**
	 * The unique slug/name of the meta location.
	 *
	 * @var string
	 */
	public string $location_type = 'blog';
}
