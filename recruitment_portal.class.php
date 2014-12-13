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

if ( !defined('EQDKP_INC') ){
	header('HTTP/1.0 404 Not Found');exit;
}

class recruitment_portal extends portal_generic {
	public static function __shortcuts() {
		$shortcuts = array('crypt' => 'encrypt');
		return array_merge(parent::$shortcuts, $shortcuts);
	}

	protected static $path		= 'recruitment';
	protected static $data		= array(
		'name'			=> 'Recruitment Module',
		'version'		=> '1.1.1',
		'author'		=> 'GodMod',
		'icon'			=> 'fa-search-plus',
		'contact'		=> EQDKP_PROJECT_URL,
		'description'	=> 'Searching for Members',
		'lang_prefix'	=> 'recruitment_',
	);
	protected static $positions = array('middle', 'left1', 'left2', 'right', 'bottom');

	protected static $install	= array(
		'autoenable'		=> '0',
		'defaultposition'	=> 'left1',
		'defaultnumber'		=> '1',
	);
	
	protected $hooks = array(
		array('wrapper', 'recruitment_wrapper_hook')
	);
	
	protected static $apiLevel = 20;

	
	public function get_settings($state){	
		$priority_dropdown = array(
			''		=> '',
			'high'	=> $this->user->lang('recruit_priority_high'),
			'middle'=> $this->user->lang('recruit_priority_middle'),
			'low'	=> $this->user->lang('recruit_priority_low'),
		);
		// Load the classes
		$arrClasses = $this->game->get_recruitment_classes();

		$arrToDisplay = $arrClasses['todisplay'];
		
		$strPrimaryClass = $this->game->get_primary_class();

		
		$intStopLevel = 0;
		foreach($arrToDisplay as $key => $val){
			if ($val === $strPrimaryClass) $intStopLevel = $key;
		}
		
		$arrSettings = $this->build_settings($arrClasses['data'],  $arrToDisplay, $intStopLevel);
		
		foreach ($arrSettings as $key => $val){			
			if (!isset($val['options'])){
				$settings[$key] = array(
					'dir_lang'		=> $val['field']['icon'].' '.$val['field']['text'],
					'type'			=> 'plaintext',
				);			
			} else {
				$settings[$key] = array(
						'dir_lang'		=> $val['field']['icon'].' '.$val['field']['text'],
						'type'			=> 'multiselect',
						'options'		=> $val['options'],
						'class'			=> 'js_reload',
				);
				
				$arrSelected = $this->config($key);
				if (is_array($arrSelected)){
					foreach($arrSelected as $strKey){
						$settings[$strKey] = array(
								'dir_lang'		=> '   '.$val['field']['icon'].' '.$val['field']['text'].' - '.$val['options'][$strKey],
								'type'			=> ((int)$this->config('priority') == 1) ? 'dropdown' : 'text',
								'options'		=> $priority_dropdown,
						);
					}
				}
				
			}	
		}
		
		$a_linkMode= array(
			'0'				=> $this->user->lang('pk_set_link_type_self'),
			'1'				=> $this->user->lang('pk_set_link_type_link'),
			'2'				=> $this->user->lang('pk_set_link_type_iframe'),
			'4'				=> $this->user->lang('pk_set_link_type_D_iframe_womenues'),
		);
		$settings['priority']	= array(
			'type'			=> 'radio',
			'class'			=> 'js_reload'
		);
		$settings['url']	= array(
			'type'			=> 'text',
			'size'			=> 60,
		);
		$settings['embed']	= array(
			'type'			=> 'dropdown',
			'options'		=> $a_linkMode,
		);
		$settings['layout']	= array(
			'type'			=> 'dropdown',
			'options'		=> array('Classic', 'Tooltip'),
			'class'			=> 'js_reload'
		);

		return $settings;
	}
	
	private function build_settings($arrData, $arrToDisplay, $stop_level, $level = 0, $string = ""){
		$arrOut = array();
		
		foreach ($arrData as $key => $val) {
			//Change Key to integer
			$key = intval($key);
			if ($key === 0) continue;
			
			if (is_array($val) && ($level < $stop_level)){
				
				$arrOut[$string.$key.'_']['field'] = array(
					'type'	=> 'plaintext',	
					'text'	=> $this->game->get_name($arrToDisplay[$level], $key),
					'icon'	=> $this->game->decorate($arrToDisplay[$level], $key),
					'icon_big' => $this->game->decorate($arrToDisplay[$level], $key, array(), 48),
					'level'	=> $level,
				);
	
				$arrResult = $this->build_settings($val, $arrToDisplay, $stop_level, $level+1, $string.$key.'_');
				$arrOut = array_merge($arrOut, $arrResult);
	
			} elseif($level == $stop_level) {
				
				$arrOut[$string.$key.'_']['field'] = array(
					'type'	=> 'multiselect',
					'text'	=>  $this->game->get_name($arrToDisplay[$level], ((!is_array($val)) ? $val : $key)),
					'icon'	=>  $this->game->decorate($arrToDisplay[$level], ((!is_array($val)) ? $val : $key)),
					'icon_big' => $this->game->decorate($arrToDisplay[$level], ((!is_array($val)) ? $val : $key), array(), 48),
					'level'	=> $level,
				);
				
				$arrOut[$string.$key.'_']['options'] = $this->build_dropdown($val, $arrToDisplay, $level+1, $string.$key.'_', $key);
				
				//Add Roles
				$arrRoles = $this->pdh->get('roles', 'memberroles', array($key));
				if(is_array($arrRoles)){
					foreach($arrRoles as $role_id => $role_name) {
						$arrOut[$string.$key.'_']['options'][$string.$key.'_role'.$role_id] = $role_name;
					} //close foreach
				}
				//End Roles
			}	
		}
		
		return $arrOut;
	}
	
	
	private function build_dropdown($arrData, $arrToDisplay, $level = 0, $string = "", $mykey = false){
		if ($mykey) $arrOut[$string.'_val'] = $this->game->get_name('primary', $mykey);
		foreach ($arrData as $key => $val) {
			if (is_array($val)){
	
				$arrOut[$string.$key.'_'] = $this->game->get_name($arrToDisplay[$level], $key);
	
				$arrResult = $this->build_dropdown($val, $arrToDisplay, $level+1, $string.$key.'_', false);
				$arrOut = array_merge($arrOut, $arrResult);
	
			} else {
				$arrOut[$string.$key.'_'] =  $this->game->get_name($arrToDisplay[$level], $val);
			}
		}
	
		return $arrOut;
	}
	
	
	private function build_count_array($arrData, $arrToDisplay, $stop_level, $level = 0, $string = ""){
		$arrOut = array();
	
		foreach ($arrData as $key => $val) {
			$key = intval($key);
			
			if (is_array($val) && ($level < $stop_level)){
	
				$arrOut[$string.$key.'_'] = array(
						'key'	=> $string.$key.'_',
						'type'	=> 'text',
						'name'	=> $this->game->get_name($arrToDisplay[$level], $key),
						'icon'	=> $this->game->decorate($arrToDisplay[$level], $key),
						'level'	=> $level,
				);
	
				$arrResult = $this->build_count_array($val, $arrToDisplay, $stop_level, $level+1, $string.$key.'_');
				$arrOut = array_merge($arrOut, $arrResult);
	
			} elseif($level == $stop_level) {
				if ($key == 0) continue;
				
				$arrSelected = $this->config($string.$key.'_');
				
				$arrOut[$string.$key.'_'] = array(
						'key'	=> $string.$key.'_',
						'type'	=> 'primary',
						'name'	=>  $this->game->get_name($arrToDisplay[$level], ((!is_array($val)) ? $val : $key)),
						'icon'	=>  $this->game->decorate($arrToDisplay[$level], ((!is_array($val)) ? $val : $key)),
						'level'	=> $level,
						'count'	=> ($this->config($string.$key.'__val')) ? $this->config($string.$key.'__val') : 0,
						'childs_count' => 0,
						'childs' => array(),
						'roles' => array(),
						'roles_count' => 0,
				);
				
				if (!$arrSelected || !in_array($string.$key.'_', $arrSelected)) $arrOut[$string.$key.'_']['count'] = 0;

				//Add Roles
				$arrRoles = $this->pdh->get('roles', 'memberroles', array($key));
				$intRoleCount = 0;
				if(is_array($arrRoles)){
					foreach($arrRoles as $role_id => $role_name) {
						$arrOut[$string.$key.'_']['roles'] [$string.$key.'_role'.$role_id] = array(
							'key'		=> $string.$key.'_role'.$role_id,
							'type'		=> 'role',
							'name'		=> $role_name,
							'icon'		=> $this->game->decorate('roles', $role_id),
							'count'		=> ($this->config($string.$key.'_role'.$role_id)) ? $this->config($string.$key.'_role'.$role_id) : 0,
						);
						
						if (!in_array($string.$key.'_role'.$role_id, $arrSelected)) {
							$arrOut[$string.$key.'_']['roles'] [$string.$key.'_role'.$role_id]['count'] = 0;
							continue;
						}
						
						if ((int)$this->config('priority')){
							if (strlen($this->config($string.$key.'_role'.$role_id))) $intRoleCount++;
						} else {
							$intRoleCount += $arrOut[$string.$key.'_']['roles'] [$string.$key.'_role'.$role_id]['count'];
						}
					} //close foreach
				}
				$arrOut[$string.$key.'_']['roles_count'] = $intRoleCount;
				//End Roles
				
				//Add childs
				$arrChilds = $this->build_childs($val, $arrToDisplay, $level+1, $string.$key.'_');
				$arrOut[$string.$key.'_']['childs_count'] = $arrChilds['count'];
				$arrOut[$string.$key.'_']['childs']		  = $arrChilds['childs'];
			}
		}
		
		return $arrOut;
	}
	
	private function  build_childs($arrData, $arrToDisplay, $level = 0, $string = "", $orig_string = false){
		$arrOut = array('childs' => array(), 'count' => 0);
		
		$string = ($orig_string) ? $orig_string : $string;
		
		$arrSelected = $this->config($string);
		if (!is_array($arrSelected)) $arrSelected = array();
		
		if (!is_array($arrData)) return $arrOut;
		
		foreach ($arrData as $key => $val) {
			if (is_array($val)){
					
				$arrOut['childs'][$string.$key.'_'] = array(
						'key'		=> $string.$key.'_',
						'type'		=> 'child',
						'name'		=> $this->game->get_name($arrToDisplay[$level], $key),
						'icon'		=> $this->game->decorate($arrToDisplay[$level], $key),
						'count'		=> ($this->config($string.$key.'_')) ? $this->config($string.$key.'_') : 0,
				);
				if (!in_array($string.$key.'_', $arrSelected)) {
					$arrOut['childs'][$string.$key.'_']['count'] = 0;
				} else {
					if ((int)$this->config('priority')){
						$arrOut['count']++;
					} else {
						$arrOut['count'] += $arrOut['childs'][$string.$key.'_']['count'];	
					}
				}
					
				$arrResult = $this->build_child($val, $arrToDisplay, $level+1, $string.$key.'_', $orig_string);
				$arrOut['childs'] = array_merge($arrOut['childs'], $arrResult['childs']);
				$arrOut['count'] += $arrResult['count'];
		
			} else {
				$arrOut['childs'][$string.$key.'_'] = array(
						'key'		=> $string.$key.'_',
						'type'		=> 'child',
						'name'		=> $this->game->get_name($arrToDisplay[$level], $val),
						'icon'		=> $this->game->decorate($arrToDisplay[$level], $val),
						'count'		=> ($this->config($string.$key.'_')) ? $this->config($string.$key.'_') : 0,
				);
				
				if (!in_array($string.$key.'_', $arrSelected)) {
					$arrOut['childs'][$string.$key.'_']['count'] = 0;
				} else {
					if ((int)$this->config('priority')){
						if (strlen($this->config($string.$key.'_'))) $arrOut['count']++;
					} else {
						$arrOut['count'] += $arrOut['childs'][$string.$key.'_']['count'];
					}
				}
			}
		}
		
		return $arrOut;	
	}

	public function output() {
		$arrClasses = $this->game->get_recruitment_classes();
		$arrToDisplay = $arrClasses['todisplay'];
		
		$strPrimaryClass = $this->game->get_primary_class();
		
		$intStopLevel = 0;
		foreach($arrToDisplay as $key => $val){
			if ($val === $strPrimaryClass) $intStopLevel = $key;
		}
		
		$arrSettings = $this->build_count_array($arrClasses['data'],  $arrToDisplay, $intStopLevel);

		$arrStyles = array(0 => 'classic', 1 => 'tooltip');
		$intStyle = (int)$this->config('layout');
		
		$strMethod = "output_".$arrStyles[$intStyle];
		
		if (method_exists($this, $strMethod)){
			$strContent = $this->$strMethod($arrSettings, (int)$this->config('priority'));
		}
		
		return $strContent;
	}
	
	private function output_classic($arrContent, $blnPriorities){
		$this->tpl->add_css('.rec_middle{color:#ff7c0a;}');
		$out = '<table width="100%" class="colorswitch hoverrows">';
		foreach($arrContent as $key => $val){
			if ($val['type'] == 'text'){
				$out.= '<tr><th colspan="2">'.$val['name'].'</th></tr>';
				continue;
			}
			
			if ($val['count'] !== 0 || $val['childs_count'] !== 0 || $val['roles_count'] !== 0){
				$out .= '<tr>';
				$out .= '	<td>'.$val['icon'].' '.$val['name'].'</td><td>';
				
				if($blnPriorities && $val['count'] !== 0){
					$out .= '<span class="'.$this->handle_cssclass($val['count']).'">'. $this->user->lang('recruit_priority_'.$val['count']). '</span>';
				} elseif($val['count'] !== 0) {
					$out .= $val['count'];
				}
				$out .= '	</td>';
				$out .= '</tr>';
				
				//Roles
				if ($val['roles_count'] !== 0){
					foreach($val['roles'] as $roleid => $rval){
						if ($rval['count'] !== 0){
							$out .= '<tr>';
							$out .= '	<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$rval['icon'].' '.$rval['name'].'</td><td>';
							if($blnPriorities){
								$out .= '<span class="'.$this->handle_cssclass($rval['count']).'">'. $this->user->lang('recruit_priority_'.$rval['count']). '</span>';
							} else {
								$out .= $rval['count'];
							}
							$out .= '	</td>';
							$out .= '</tr>';
						}			
					}
				}
				
				//Childs
				if ($val['childs_count'] !== 0){
					foreach($val['childs'] as $childid => $rval){
						if ($rval['count'] !== 0){
							$out .= '<tr>';
							$out .= '	<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$rval['icon'].' '.$rval['name'].'</td><td>';
							if($blnPriorities){
								$out .= '<span class="'.$this->handle_cssclass($rval['count']).'">'. $this->user->lang('recruit_priority_'.$rval['count']). '</span>';
							} else {
								$out .= $rval['count'];
							}
							$out .= '	</td>';
							$out .= '</tr>';
						}
						
					}
					
				}
			
			}
		}
		$out .='<tr><td colspan="2">'.$this->get_recruitment_link().'<i class="fa fa-chevron-right"></i>'.$this->user->lang('recruitment_contact').'</a></td></tr></table>';
		return $out;
	}
	
	private function output_tooltip($arrContent, $blnPriorities){
		$this->tpl->add_css('.rec_middle{color:#ff7c0a;}
			.rc_class {
				margin: 4px 4px 4px 4px;
			}
				
			.rc_class img {
				max-height: 36px;
				margin: 4px 0px 4px 0px;
			}
				
			.rc_gray img {
				-moz-opacity: 0.30;
				opacity: 0.30;
				-ms-filter:"progid:DXImageTransform.Microsoft.Alpha"(Opacity=30);
			}
		');
		
		//Tooltip position:
		switch($this->position){
			case 'left': $ttpos = 'top left';
			break;
			case 'right': $ttpos = 'top right';
			break;
			default: $ttpos = 'top bottom';
		}
		
		$out = '<div>';
		foreach($arrContent as $classid => $val){
			if ($val['type'] == 'text'){
				$out.= '<div><h3>'.$val['name'].'</h3></div>';
				continue;
			}
			
			$tooltip = array();	
			if ($val['count'] !== 0 || $val['childs_count'] !== 0 || $val['roles_count'] !== 0){
				if($blnPriorities && $val['count'] !== 0){
					$tooltip[] = $val['icon'].' '.$val['name'].': <span class="'.$this->handle_cssclass($val['count']).'">'. $this->user->lang('recruit_priority_'.$val['count']). '</span>';
				} elseif($val['count'] !== 0) {
					$tooltip[] = $val['icon'].' '.$val['name'].': '.$val['count'];
				}

				//Roles
				if ($val['roles_count'] !== 0){
					foreach($val['roles'] as $roleid => $rval){
						if ($rval['count'] !== 0){
							if($blnPriorities){
								$tooltip[] = $rval['icon'].' '.$rval['name'].': <span class="'.$this->handle_cssclass($rval['count']).'">'. $this->user->lang('recruit_priority_'.$rval['count']). '</span>';
							} else {
								$tooltip[] = $rval['icon'].' '.$rval['name'].': '.$rval['count'];
							}
						}	
					}
				}
				
				//Childs
				if ($val['childs_count'] !== 0){
					foreach($val['childs'] as $childid => $rval){
						if ($rval['count'] !== 0){
							if($blnPriorities){
								$tooltip[] = $rval['icon'].' '.$rval['name'].': <span class="'.$this->handle_cssclass($rval['count']).'">'. $this->user->lang('recruit_priority_'.$rval['count']). '</span>';
							} else {
								$tooltip[] = $rval['icon'].' '.$rval['name'].': '.$rval['count'];
							}
						}	
					}		
				}
				
				$strTooltip = implode("<br />", $tooltip);
				$out .= new htooltip('tt_recrui1', array('content' => $strTooltip, 'label' => '<span class="rc_class">'.$this->get_recruitment_link().(($val['icon_big']) ? $val['icon_big'] : $val['icon']).'</a></span>', "my" => $ttpos));
				//$out = '<div class="rc_class tt_rc_class_'.$classid.'">'.(($val['icon_big']) ? $val['icon_big'] : $val['icon']).'</div>';
				//$this->jquery->qtip(".tool_rc_class_".$classid, $strTooltip);
				
			} else {
				if ($val['name'] == "") continue;
				$out .= '<span class="rc_class rc_gray">'.(($val['icon_big']) ? $val['icon_big'] : $val['icon']).'</span>';
			}
		}
		$out .='<div class="clear"></div></div>';
		return $out;
	}

	
	private function handle_cssclass($priority){
		switch($priority){
			case 'high' : return 'negative';
			case 'low'	: return 'positive';
			default: return 'rec_middle';
		}
	}
	
	private function get_recruitment_link(){
		//show Link URL
		$target = '';
		if (strlen($this->config('url')) > 1) {
			switch ($this->config('embed')){
				case '0':  $path = $this->config('url');
				break ;
				case '1':  $target = ' target="_blank"';
				$path = $this->config('url');
				break ;
				case '2':
				case '3':
				case '4':  $path = $this->routing->build('external', 'recruitment');
				break ;
			}
		
		}else{		//Link URL -> Email / guildrequest plugin
			$path = "mailto:".$this->crypt->decrypt($this->config->get('admin_email'));
			if ($this->pm->check('guildrequest', PLUGIN_INSTALLED)){
				$path = $this->routing->build('WriteApplication');
			}
		}
		$url = '<a href="'.$path.'" '.$target.'>' ;
		return $url;
	}

	public static function reset() {
	}
}
?>