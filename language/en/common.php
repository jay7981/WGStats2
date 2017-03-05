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
	'ACP_WGSTATS_WGAPIKEY'		=> 'Wargaming.net Developer API Key:',
	'ACP_WGSTATS_INSTRUCTIONS'		=> 'Goto <a href="https://developers.wargaming.net/"><b>Developers\'s Room</b></a> and create a new app to get this apikey.',
	'ACP_WGSTATS_SETTING_SAVED'	=> 'Settings have been saved successfully!',
));
