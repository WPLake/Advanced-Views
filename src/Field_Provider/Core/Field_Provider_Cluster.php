<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Core;

defined( 'ABSPATH' ) || exit;

interface Field_Provider_Cluster {
	/**
	 * 1. must be more than the default 10, so it's executed after the data vendor plugins fully loaded themselves (e.g. MetaBox has loading inside this hook)
	 * 2. '15' gives the ability to shift back when it needs, while still been after the default one.
	 */
	public const PLUGINS_LOADED_HOOK_PRIORITY = 15;
}
