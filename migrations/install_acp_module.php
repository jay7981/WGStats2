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

class install_acp_module extends \phpbb\db\migration\migration
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
			array('config.add', array('gr_wgstats_wgapikey', 0)),

			array('module.add', array(
				'acp',
				'ACP_CAT_DOT_MODS',
				'ACP_WGSTATS_TITLE'
			)),
			array('module.add', array(
				'acp',
				'ACP_WGSTATS_TITLE',
				array(
					'module_basename'	=> '\GhostRider\WGStats\acp\main_module',
					'modes'				=> array('settings'),
				),
			)),
		);
	}
}
