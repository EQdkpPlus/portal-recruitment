<?php
/*	Project:	EQdkp-Plus
 *	Package:	Recreuitment Portal Module
 *	Link:		http://eqdkp-plus.eu
 *
 *	Copyright (C) 2006-2015 EQdkp-Plus Developer Team
 *
 *	This program is free software: you can redistribute it and/or modify
 *	it under the terms of the GNU Affero General Public License as published
 *	by the Free Software Foundation, either version 3 of the License, or
 *	(at your option) any later version.
 *
 *	This program is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *	GNU Affero General Public License for more details.
 *
 *	You should have received a copy of the GNU Affero General Public License
 *	along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

if (!defined('EQDKP_INC')){
	die('Do not access this file directly.');
}

if (!class_exists('recruitment_wrapper_hook')){
	class recruitment_wrapper_hook extends gen_class{

		public function __construct($moduleID){

		}
		
		public function wrapper_hook($arrParams){
			if ($arrParams['link'] != 'recruitment') return false;
			$moduleID = intval($arrParams['id']);
			
			$out = array(
				'url'	=> $this->portal->get_module_config($moduleID,'url' ),
				'title'	=> $this->user->lang('recruitment'),
				'window'=> $this->portal->get_module_config($moduleID,'embed' ),
				'height'=> '4024',
			);

			return array('id' => $moduleID, 'data'=> $out, 'link' => 'recruitment');
		}
	}
}