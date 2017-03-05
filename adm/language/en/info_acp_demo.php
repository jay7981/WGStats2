<?php
/**
 *
 * Wargaming.net Stats. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017, GhostRider, http://transformersfleet.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
	'ACP_WGSTATS_TITLE'			=> 'Demo Module',
));
