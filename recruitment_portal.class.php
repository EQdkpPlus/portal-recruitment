<?php
 /*
 * Project:		EQdkp-Plus
 * License:		Creative Commons - Attribution-Noncommercial-Share Alike 3.0 Unported
 * Link:		http://creativecommons.org/licenses/by-nc-sa/3.0/
 * -----------------------------------------------------------------------
 * Began:		2008
 * Date:		$Date: 2012-11-11 18:36:16 +0100 (So, 11. Nov 2012) $
 * -----------------------------------------------------------------------
 * @author		$Author: godmod $
 * @copyright	2006-2011 EQdkp-Plus Developer Team
 * @link		http://eqdkp-plus.com
 * @package		eqdkp-plus
 * @version		$Rev: 12434 $
 *
 * $Id: recruitment_portal.class.php 12434 2012-11-11 17:36:16Z godmod $
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

	
	public function get_settings($state){	
		$priority_dropdown = array(
			''		=> '',
			'high'	=> $this->user->lang('recruit_priority_high'),
			'middle'=> $this->user->lang('recruit_priority_middle'),
			'low'	=> $this->user->lang('recruit_priority_low'),
		);
		// Load the classes
		$classes = $this->game->get('classes');
		foreach($classes as $class_id => $class_name) {
			if($class_id == 0) continue;
			
			$arrClassDropdown = array(
				'class'.$class_id => $class_name
			);

			//Talents
			if($this->game->icon_exists('talents')){
				$talents = $this->game->glang('talents');
				if(is_array($talents[$class_id])){
					foreach($talents[$class_id] as $talent_id => $talent_name) {
						$arrClassDropdown['class'.$class_id.'_talent'.$talent_id] = $class_name.' - '.$talent_name;
							
						//Roles
						$arrRoles = $this->pdh->get('roles', 'memberroles', array($class_id));
						if(is_array($arrRoles)){
							foreach($arrRoles as $role_id => $role_name) {
								$arrClassDropdown['class'.$class_id.'_talent'.$talent_id.'_role'.$role_id] = $class_name.' - '.$talent_name.' - '.$role_name;
							} //close foreach
						}
					}
				}
			}

			//Roles
			$arrRoles = $this->pdh->get('roles', 'memberroles', array($class_id));
			if(is_array($arrRoles)){
				foreach($arrRoles as $role_id => $role_name) {
					$arrClassDropdown['class'.$class_id.'_role'.$role_id] = $class_name.' - '.$role_name;
				} //close foreach
			}
			
			$settings['class_'.$class_id.'_enabled'] = array(
				'dir_lang'		=> $this->game->decorate('classes', array($class_id)).' '.$this->game->get_name('classes', $class_id),
				'type'			=> 'multiselect',
				'options'		=> $arrClassDropdown,
				'class'			=> 'js_reload',
			);
			
			$arrSelected = $this->config('class_'.$class_id.'_enabled');
			foreach($arrSelected as $strKey){
				$settings[$strKey] = array(
					'dir_lang'		=> $arrClassDropdown[$strKey],
					'type'			=> ((int)$this->config('priority') == 1) ? 'dropdown' : 'text',
					'options'		=> $priority_dropdown,
				);
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
			'options'		=> array('Classic', 'Tooltip', 'Mini-Icons'),
			'class'			=> 'js_reload'
		);

		if ((int)$this->config('layout') == 2){
			$settings['2columns']	= array(
				'type'		=> 'radio',
			);
		}
		return $settings;
	}

	public function output() {
		// Load the classes
		$classes = $this->game->get('classes');
		$conf = array();
		
		//Build Language Array
		// Load the classes
		$classes = $this->game->get('classes');
		foreach($classes as $class_id => $class_name) {
			if($class_id == 0) continue;

			$arrLang[$class_id] = array(
				'key'				=> 'class'.$class_id,
				'count'				=> 0,
				'name' 				=> $class_name,
				'decorate' 			=> $this->game->decorate('classes', array($class_id)),
				'decorate_big'		=> ($this->game->icon_exists('classes_big')) ? '<img src="'.$this->game->decorate('classes', array($class_id, true, null, true)).'" alt="'.$this->game->get_name('classes', $class_id).'" />' : false,
				'roles'				=> array(),
				'talents'			=> array(),
				'roles_count'		=> 0,
				'talents_count' 	=> 0,
				'talents_roles_count' => 0,
			);
		
			//Talents
			if($this->game->icon_exists('talents')){
				$talents = $this->game->glang('talents');
				if(is_array($talents[$class_id])){
					foreach($talents[$class_id] as $talent_id => $talent_name) {
						$arrLang[$class_id]['talents'][$talent_id] = array(
							'key'		=> 'class'.$class_id.'_talent'.$talent_id,
							'name'		=> $talent_name,
							'count'		=> 0,
							'decorate'	=> $this->game->decorate('talents', array($class_id, $talent_id)),
							'roles'		=> array(),
							'roles_count' => 0,
						);
						
						//Roles
						$arrRoles = $this->pdh->get('roles', 'memberroles', array($class_id));
						if(is_array($arrRoles)){
							foreach($arrRoles as $role_id => $role_name) {
								$arrLang[$class_id]['talents'][$talent_id]['roles'][$role_id] = array(
									'key'		=> 'class'.$class_id.'_talent'.$talent_id.'_role'.$role_id,
									'name'		=> $role_name,
									'decorate'	=> $this->game->decorate('roles', array($role_id)),
									'count'		=> 0,
								);
							}
						}
					}
				}
			}
		
			//Roles
			$arrRoles = $this->pdh->get('roles', 'memberroles', array($class_id));
			if(is_array($arrRoles)){
				foreach($arrRoles as $role_id => $role_name) {
					$arrLang[$class_id]['roles'][$role_id] = array(
							'key'		=> 'class'.$class_id.'_role'.$role_id,
							'name'		=> $role_name,
							'decorate'	=> $this->game->decorate('roles', array($role_id)),
							'count'		=> 0,
					);
				} //close foreach
			}
		}
		
		foreach($classes as $class_id => $class_name) {
			if($class_id == 0) continue;
			$arrSelected = $this->config('class_'.$class_id.'_enabled');
			foreach($arrSelected as $strKey){
				$conf = $this->config($strKey);
				//Split Key
				$arrSplitted = explode("_", $strKey);
				
				//Class
				$class_id = (int)substr($arrSplitted[0], 5);

				if (isset($arrSplitted[1]) && strpos($arrSplitted[1], "role") === 0){
					//Role
					$role_id = (int)substr($arrSplitted[1], 4);
					$arrLang[$class_id]['roles'][$role_id]['count'] = $conf;
					$arrLang[$class_id]['roles_count'] = $arrLang[$class_id]['roles_count']+1;
				} elseif(isset($arrSplitted[1]) && strpos($arrSplitted[1], "talent") === 0){
					//Talent
					$talent_id = (int)substr($arrSplitted[1], 6);
					
					//Talent-Role
					if (isset($arrSplitted[2])){
						$role_id = (int)substr($arrSplitted[2], 4);
						$arrLang[$class_id]['talents'][$talent_id]['roles'][$role_id]['count'] = $conf;
						$arrLang[$class_id]['talents_roles_count'] = $arrLang[$class_id]['talents_roles_count']+1;
						$arrLang[$class_id]['talents'][$talent_id]['roles_count'] = $arrLang[$class_id]['talents'][$talent_id]['roles_count']+1;
					} else {
						$arrLang[$class_id]['talents'][$talent_id]['count'] = $conf;
						$arrLang[$class_id]['talents_count'] = $arrLang[$class_id]['talents_count']+1;
					}
				} else {
					$arrLang[$class_id]['count'] = $conf;
				}

			}		
		}
				
		$arrStyles = array(0 => 'classic', 1 => 'tooltip', 2 => 'mini_icons');
		$intStyle = (int)$this->config('layout');
		
		$strMethod = "output_".$arrStyles[$intStyle];
		
		$strContent = $this->$strMethod($arrLang, (int)$this->config('priority'));
		
		return $strContent;
	}
	
	private function output_classic($arrContent, $blnPriorities){
		$this->tpl->add_css('.rec_middle{color:#ff7c0a;}');
		$out = '<table width="100%" class="colorswitch hoverrows">';
		foreach($arrContent as $classid => $val){
			if ($val['count'] !== 0 || $val['roles_count'] !== 0 || $val['talents_count'] !== 0 || $val['talents_roles_count'] !== 0){
				$out .= '<tr>';
				$out .= '	<td>'.$val['decorate'].' '.$val['name'].'</td><td>';
						
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
							$out .= '	<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$rval['decorate'].' '.$rval['name'].'</td><td>';
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
				
				//Talents
				foreach($val['talents'] as $talentid => $rval){
					if ($rval['count'] !== 0){
						$out .= '<tr>';
						$out .= '	<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$rval['decorate'].' '.$rval['name'].'</td><td>';
						if($blnPriorities){
							$out .= '<span class="'.$this->handle_cssclass($rval['count']).'">'. $this->user->lang('recruit_priority_'.$rval['count']). '</span>';
						} else {
							$out .= $rval['count'];
						}
						$out .= '	</td>';
						$out .= '</tr>';
					}
					
					//Talent-Roles
					if ($rval['roles_count'] !== 0){
						foreach($rval['roles'] as $roleid => $rrval){
							if ($rrval['count'] !== 0){
								$out .= '<tr>';
								$out .= '	<td class="nowrap">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$rval['decorate'].' '.$rrval['decorate'].' '.$rrval['name'].'</td><td>';
								if($blnPriorities){
									$out .= '<span class="'.$this->handle_cssclass($rrval['count']).'">'. $this->user->lang('recruit_priority_'.$rrval['count']). '</span>';
								} else {
									$out .= $rrval['count'];
								}
								$out .= '	</td>';
								$out .= '</tr>';
							}
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
			$tooltip = array();
			if ($val['count'] !== 0 || $val['roles_count'] !== 0 || $val['talents_count'] !== 0 || $val['talents_roles_count'] !== 0){
		
				if($blnPriorities && $val['count'] !== 0){
					$tooltip[] = $val['decorate'].' '.$val['name'].': <span class="'.$this->handle_cssclass($val['count']).'">'. $this->user->lang('recruit_priority_'.$val['count']). '</span>';
				} elseif($val['count'] !== 0) {
					$tooltip[] = $val['decorate'].' '.$val['name'].': '.$val['count'];
				}

				//Roles
				if ($val['roles_count'] !== 0){
					foreach($val['roles'] as $roleid => $rval){
						if ($rval['count'] !== 0){
							if($blnPriorities){
								$tooltip[] = $rval['decorate'].' '.$rval['name'].': <span class="'.$this->handle_cssclass($rval['count']).'">'. $this->user->lang('recruit_priority_'.$rval['count']). '</span>';
							} else {
								$tooltip[] = $rval['decorate'].' '.$rval['name'].': '.$rval['count'];
							}
						}	
					}
				}
		
				//Talents
				foreach($val['talents'] as $talentid => $rval){
					if ($rval['count'] !== 0){
							if($blnPriorities){
								$tooltip[] = $rval['decorate'].' '.$rval['name'].': <span class="'.$this->handle_cssclass($rval['count']).'">'. $this->user->lang('recruit_priority_'.$rval['count']). '</span>';
							} else {
								$tooltip[] = $rval['decorate'].' '.$rval['name'].': '.$rval['count'];
							}
					}
						
					//Talent-Roles
					if ($rval['roles_count'] !== 0){
						foreach($rval['roles'] as $roleid => $rrval){
							if ($rrval['count'] !== 0){
								if($blnPriorities){
									$tooltip[] = $rval['decorate'].' '.$rval['name'].' - '.$rrval['decorate'].' '.$rrval['name'].': <span class="'.$this->handle_cssclass($rrval['count']).'">'. $this->user->lang('recruit_priority_'.$rrval['count']). '</span>';
								} else {
									$tooltip[] = $rval['decorate'].' '.$rval['name'].' - '.$rrval['decorate'].' '.$rrval['name'].': '.$rrval['count'];
								}
							}
						}
					}
				}
				
				$strTooltip = implode("<br />", $tooltip);
				$out .= new htooltip('tt_recrui1', array('content' => $strTooltip, 'label' => '<span class="rc_class">'.$this->get_recruitment_link().(($val['decorate_big']) ? $val['decorate_big'] : $val['decorate']).'</a></span>', "my" => $ttpos));
				//$out = '<div class="rc_class tt_rc_class_'.$classid.'">'.(($val['decorate_big']) ? $val['decorate_big'] : $val['decorate']).'</div>';
				//$this->jquery->qtip(".tool_rc_class_".$classid, $strTooltip);
				
			} else {
				$out .= '<span class="rc_class rc_gray">'.(($val['decorate_big']) ? $val['decorate_big'] : $val['decorate']).'</span>';
			}
		}
		$out .='<div class="clear"></div></div>';
		return $out;
	}
	
	private function output_mini_icons($arrContent, $blnPriorities){
		$this->tpl->add_css('.rec_middle{color:#ff7c0a;}
			.rc_class_ct {
				float: left;
				margin-bottom: 4px;
				margin-right: 8px;
			}
				
			.rc_class, .rc_talent {
				float: left;
			}
		
			.rc_class img {
				max-height: 36px;
			}
			
			.rc_talent {
				margin-top: 10px;
				margin-left: 4px;
			}	
				
			.rc_talent img {
				max-height: 20px;
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
		
		$out = '<div'.(($this->config('2columns') == 1) ? ' style="width: 255px;"' : '').'>';
		foreach($arrContent as $classid => $val){
			$tooltip = array();
			if ($val['count'] !== 0 || $val['roles_count'] !== 0 || $val['talents_count'] !== 0 || $val['talents_roles_count'] !== 0){
				$out .= '<div class="rc_class_ct">';
				
				$tooltip = false;
				if($blnPriorities && $val['count'] !== 0){
					$tooltip[] = $val['decorate'].' '.$val['name'].': <span class="'.$this->handle_cssclass($val['count']).'">'. $this->user->lang('recruit_priority_'.$val['count']). '</span>';
				} elseif($val['count'] !== 0) {
					$tooltip[] = $val['decorate'].' '.$val['name'].': '.$val['count'];
				}

				//Roles
				if ($val['roles_count'] !== 0 && count($val['talents']) != 0){
					foreach($val['roles'] as $roleid => $rval){
						if ($rval['count'] !== 0){
							if($blnPriorities){
								$tooltip[] = $rval['decorate'].' '.$rval['name'].': <span class="'.$this->handle_cssclass($rval['count']).'">'. $this->user->lang('recruit_priority_'.$rval['count']). '</span>';
							} else {
								$tooltip[] = $rval['decorate'].' '.$rval['name'].': '.$rval['count'];
							}
						}
					}
				}
				
				if (count($tooltip)){
					$out .= new htooltip('tt_recrui2', array('content' => implode("<br />", $tooltip), 'label' => '<div class="rc_class">'.$this->get_recruitment_link().(($val['decorate_big']) ? $val['decorate_big'] : $val['decorate']).'</a></div>', "my" => $ttpos));
				} else {
					$out .= '<div class="rc_class"><a href="'.$this->get_recruitment_link().'">'.(($val['decorate_big']) ? $val['decorate_big'] : $val['decorate']).'</a></div>';
				}
				
		
				//Roles
				if (count($val['talents']) == 0){
					foreach($val['roles'] as $roleid => $rval){
						$tooltip = array();
						if ($rval['count'] !== 0){
							if($blnPriorities){
								$tooltip[] = $rval['decorate'].' '.$rval['name'].': <span class="'.$this->handle_cssclass($rval['count']).'">'. $this->user->lang('recruit_priority_'.$rval['count']). '</span>';
							} else {
								$tooltip[] = $rval['decorate'].' '.$rval['name'].': '.$rval['count'];
							}
						}
						
						if (count($tooltip)){
							$out .= new htooltip('tt_recrui3', array('content' => implode("<br />", $tooltip), 'label' => '<div class="rc_talent">'.$this->get_recruitment_link().$rval['decorate'].'</a></div>', "my" => $ttpos));
						} else {
							$out .= '<div class="rc_talent rc_gray">'.$rval['decorate'].'</div>';
						}
						
					}
				}
		
				//Talents
				foreach($val['talents'] as $talentid => $rval){
					$tooltip = array();
					if ($rval['count'] !== 0){
						if($blnPriorities){
							$tooltip[] = $rval['decorate'].' '.$rval['name'].': <span class="'.$this->handle_cssclass($rval['count']).'">'. $this->user->lang('recruit_priority_'.$rval['count']). '</span>';
						} else {
							$tooltip[] = $rval['decorate'].' '.$rval['name'].': '.$rval['count'];
						}
					}
					
					//Talent-Roles
					if ($rval['roles_count'] !== 0){
						foreach($rval['roles'] as $roleid => $rrval){
							if ($rrval['count'] !== 0){
								if($blnPriorities){
									$tooltip[] = $rval['decorate'].' '.$rval['name'].' - '.$rrval['decorate'].' '.$rrval['name'].': <span class="'.$this->handle_cssclass($rrval['count']).'">'. $this->user->lang('recruit_priority_'.$rrval['count']). '</span>';
								} else {
									$tooltip[] = $rval['decorate'].' '.$rval['name'].' - '.$rrval['decorate'].' '.$rrval['name'].': '.$rrval['count'];
								}
							}
						}
					}
					if (count($tooltip)){
						$out .= new htooltip('tt_recrui4', array('content' => implode("<br />", $tooltip), 'label' => '<div class="rc_talent">'.$this->get_recruitment_link().$rval['decorate'].'</a></div>', "my" => $ttpos));
					} else {
						$out .= '<div class="rc_talent rc_gray">'.$rval['decorate'].'</div>';
					}
					
				}

		
				$out .= '</div>';
			} else {
				$out .= '<div class="rc_class_ct">';
				$out .= '	<div class="rc_class rc_gray">'.(($val['decorate_big']) ? $val['decorate_big'] : $val['decorate']).'</div>';
				if (count($val['talents'])) {
					foreach($val['talents'] as $talentid => $rval){
						$out .= '<div class="rc_talent rc_gray">'.$rval['decorate'].'</div>';
					}
				} else {
					foreach($val['roles'] as $talentid => $rval){
						$out .= '<div class="rc_talent rc_gray">'.$rval['decorate'].'</div>';
					}
				}
				$out .= '</div>';
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