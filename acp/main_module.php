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
 * Wargaming.net Stats ACP module.
 */
class main_module
{
	public $page_title;
	public $tpl_name;
	public $u_action;

	public function main($id, $mode)
	{
		global $config, $request, $template, $user;

		$user->add_lang_ext('GhostRider/WGStats', 'common');
		$this->tpl_name = 'acp_wgstats_body';
		$this->page_title = $user->lang('ACP_WGSTATS_TITLE');
		add_form_key('gr/wgstats');

		if ($request->is_set_post('submit'))
		{
			if (!check_form_key('gr/wgstats'))
			{
				trigger_error('FORM_INVALID');
			}

			$config->set('gr_wgstats_wgapikey', $request->variable('gr_wgstats_wgapikey', 0));

			trigger_error($user->lang('ACP_WGSTATS_SETTING_SAVED') . adm_back_link($this->u_action));
		}

		$template->assign_vars(array(
			'U_ACTION'				=> $this->u_action,
			'GR_WGSTATS_WGAPIKEY'		=> $config['gr_wgstats_wgapikey'],
		));
	}
}
