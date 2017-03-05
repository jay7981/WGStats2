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
	'WGSTATS_PAGE'			=> 'Demo',
	'WGSTATS_HELLO'		=> 'Hello %s!',
	'WGSTATS_GOODBYE'		=> 'Goodbye %s!',

	'ACP_WGSTATS'					=> 'Settings',
	'ACP_WGSTATS_GOODBYE'			=> 'Should say goodbye?',
	'ACP_WGSTATS_SETTING_SAVED'	=> 'Settings have been saved successfully!',

	'GR_WGSTATS_NOTIFICATION'	=> 'Acme demo notification',

	'VIEWING_GR_WGSTATS'			=> 'Viewing Acme Demo',
));
