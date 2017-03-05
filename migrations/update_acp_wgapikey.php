<?php
/**
 *
 * Wargaming.net Stats. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017, GhostRider, http://transformersfleet.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace GhostRider\WGStats\migrations;

class update_wgstats_wgapikey extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['gr_wgstats_wgapikey']);
	}

	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v31x\v314');
	}

	public function update_data()
	{
		return array(
			 array('config.update', array('gr_wgstats_wgapikey', '$_POST('gr_wgstats_wgapikey')')), // $config['gr_wgstats_wgapikey'] = 'What ever the form in ACP submits';
		);
	}
	
	public function revert_data()
	{
		array('config.remove', array('gr_wgstats_wgapikey')), // unset($config['gr_wgstats_wgapikey']);
	}
}