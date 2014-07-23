<?php
/**
*
* @package editor_of_attachments
* @copyright (c) 2014 Татьяна5
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

namespace tatiana5\editor_of_attachments\acp;

class editor_of_attachments_module
{
	var $u_action;
	var $max_rep_id;
	var $step = 1000;

	function main($id, $mode)
	{
		global $cache, $config, $db, $user, $auth, $template, $request;
		global $phpbb_root_path, $phpEx, $phpbb_admin_path, $phpbb_container;

		$this->page_title = 'ACP_EDITOR_OF_ATTACHMENTS';
		$this->tpl_name = 'acp_editor_of_attachments';
		
		$submit = (isset($_POST['submit'])) ? true : false;
		$form_key = 'config_editor_of_attachments';
		add_form_key($form_key);
		
		$display_vars = array(
			'title'	=> 'ACP_EDITOR_OF_ATTACHMENTS',
			'vars'	=> array(
				'legend1'		=> 'ACP_ATTACH_RESIZE',
				'allow_attach_resize'	=> array('lang' => 'ACP_ALLOW_ATTACH_RESIZE', 'validate' => 'bool', 'type' => 'radio:yes_no', 'explain' => true),
				'attach_resize_width'	=> array('lang' => 'ACP_ATTACH_RESIZE_WIDHT', 'validate' => 'int:0', 'type' => 'number:0:99999', 'explain' => false,),
				'attach_resize_height'	=> array('lang' => 'ACP_ATTACH_RESIZE_HEIGHT', 'validate' => 'int:0', 'type' => 'number:0:99999', 'explain' => false,),
				
				'legend2'				=> 'ACP_QUOTE_ATTACH',
				'allow_quote_attach'	=> array('lang' => 'ACP_ALLOW_QUOTE_ATTACH', 'validate' => 'bool', 'type' => 'radio:yes_no', 'explain' => true),
				
				'legend3'				=> 'ACP_SUBMIT_CHANGES',
			),
		);
						
		if (isset($display_vars['lang']))
		{
			$user->add_lang($display_vars['lang']);
		}

		$this->new_config = $config;
		$cfg_array = (isset($_REQUEST['config'])) ? utf8_normalize_nfc(request_var('config', array('' => ''), true)) : $this->new_config;
		$error = array();

		// We validate the complete config if wished
		validate_config_vars($display_vars['vars'], $cfg_array, $error);

		if ($submit && !check_form_key($form_key))
		{
			$error[] = $user->lang['FORM_INVALID'];
		}
		// Do not write values if there is an error
		if (sizeof($error))
		{
			$submit = false;
		}
		
		// We go through the display_vars to make sure no one is trying to set variables he/she is not allowed to...
		foreach ($display_vars['vars'] as $config_name => $null)
		{
			if (!isset($cfg_array[$config_name]) || strpos($config_name, 'legend') !== false)
			{
				continue;
			}

			$this->new_config[$config_name] = $config_value = $cfg_array[$config_name];

			if ($submit)
			{
				set_config($config_name, $config_value);
			}
		}
		
		if ($submit)
		{
			trigger_error($user->lang['CONFIG_UPDATED'] . adm_back_link($this->u_action));
		}
		
		$this->page_title = $display_vars['title'];
		
		$template->assign_vars(array(
			'L_TITLE'			=> $user->lang[$display_vars['title']],
			'L_TITLE_EXPLAIN'	=> $user->lang[$display_vars['title'] . '_EXPLAIN'],

			'S_ERROR'			=> (sizeof($error)) ? true : false,
			'ERROR_MSG'			=> implode('<br />', $error),
		));
		
		// Output relevant page
		foreach ($display_vars['vars'] as $config_key => $vars)
		{
			if (!is_array($vars) && strpos($config_key, 'legend') === false)
			{
				continue;
			}

			if (strpos($config_key, 'legend') !== false)
			{
				$template->assign_block_vars('options', array(
					'S_LEGEND'		=> true,
					'LEGEND'		=> (isset($user->lang[$vars])) ? $user->lang[$vars] : $vars)
				);

				continue;
			}

			$type = explode(':', $vars['type']);

			$l_explain = '';
			if ($vars['explain'] && isset($vars['lang_explain']))
			{
				$l_explain = (isset($user->lang[$vars['lang_explain']])) ? $user->lang[$vars['lang_explain']] : $vars['lang_explain'];
			}
			else if ($vars['explain'])
			{
				$l_explain = (isset($user->lang[$vars['lang'] . '_EXPLAIN'])) ? $user->lang[$vars['lang'] . '_EXPLAIN'] : '';
			}

			$content = build_cfg_template($type, $config_key, $this->new_config, $config_key, $vars);

			if (empty($content))
			{
				continue;
			}

			$template->assign_block_vars('options', array(
				'KEY'			=> $config_key,
				'TITLE'			=> (isset($user->lang[$vars['lang']])) ? $user->lang[$vars['lang']] : $vars['lang'],
				'S_EXPLAIN'		=> $vars['explain'],
				'TITLE_EXPLAIN'	=> $l_explain,
				'CONTENT'		=> $content,
				)
			);

			unset($display_vars['vars'][$config_key]);
		}
	}
}

?>