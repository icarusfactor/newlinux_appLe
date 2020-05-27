<?php
/**
 * @package  Feed App
 */

class newlinuxAppDeactivate
{
	public static function deactivate() {
		flush_rewrite_rules();
	}
}
