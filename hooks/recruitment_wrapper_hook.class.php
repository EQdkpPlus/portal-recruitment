<?php
 /*
 * Project:		EQdkp-Plus
 * License:		Creative Commons - Attribution-Noncommercial-Share Alike 3.0 Unported
 * Link:		http://creativecommons.org/licenses/by-nc-sa/3.0/
 * -----------------------------------------------------------------------
 * Began:		2009
 * Date:		$Date: 2012-11-11 19:07:23 +0100 (So, 11. Nov 2012) $
 * -----------------------------------------------------------------------
 * @author		$Author: wallenium $
 * @copyright	2006-2011 EQdkp-Plus Developer Team
 * @link		http://eqdkp-plus.com
 * @package		eqdkp-plus
 * @version		$Rev: 12435 $
 *
 * $Id: recruitment_wrapper_hook.class.php 12435 2012-11-11 18:07:23Z wallenium $
 */

if (!defined('EQDKP_INC')){
	die('Do not access this file directly.');
}

if (!class_exists('recruitment_wrapper_hook')){
	class recruitment_wrapper_hook extends gen_class{
		public static $shortcuts = array('user', 'config');
		
		
		public function wrapper_hook($arrParams){
			if ($arrParams['id'] != 'recruitment') return false;
			
			$out = array(
				'url'	=> $this->config->get('pm_recruitment_url'),
				'title'	=> $this->user->lang('recruitment'),
				'window'=> $this->config->get('pm_recruitment_embed'),
				'height'=> '4024',
			);
			
			return array('id'=>'recruitment', 'data'=> $out);
		}
	}
}

if(version_compare(PHP_VERSION, '5.3.0', '<')) {
	registry::add_const('short_recruitment_wrapper_hook', recruitment_wrapper_hook::$shortcuts);
}