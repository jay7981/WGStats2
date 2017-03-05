<?php
/**
 *
 * Wargaming.net Stats. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017, GhostRider, http://transformersfleet.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace GhostRider\WGStats\acp;

/**
 * Wargaming.net Stats ACP module info.
 */
class main_info
{
	public function module()
	{
		return array(
			'filename'	=> '\GhostRider\WGStats\acp\main_module',
			'title'		=> 'ACP_WGSTATS_TITLE',
			'modes'		=> array(
				'settings'	=> array(
					'title'	=> 'ACP_WGSTATS',
					'auth'	=> 'ext_GhostRider/WGStats && acl_a_board',
					'cat'	=> array('ACP_WGSTATS_TITLE')
				),
			),
		);
	}
}
