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
		$shortcuts = array('user', 'pdc', 'core', 'html', 'game', 'tpl', 'pm', 'config', 'crypt' => 'encrypt', 'pdh');
		return array_merge(parent::$shortcuts, $shortcuts);
	}

	protected $path		= 'recruitment';
	protected $data		= array(
		'name'			=> 'Recruitment Module',
		'version'		=> '1.1.1',
		'author'		=> 'Corgan',
		'contact'		=> EQDKP_PROJECT_URL,
		'description'	=> 'Searching for Members',
	);
	protected $positions = array('middle', 'left1', 'left2', 'right', 'bottom');

	protected $install	= array(
		'autoenable'		=> '0',
		'defaultposition'	=> 'left1',
		'defaultnumber'		=> '1',
	);
	
	protected $hooks = array(
		array('wrapper', 'recruitment_wrapper_hook')
	);

	public function __construct($position=''){
		parent::__construct($position);	
	}
	

	
	public function get_settings(){
		$a_linkMode= array(
			'0'				=> $this->user->lang('pk_set_link_type_self'),
			'1'				=> $this->user->lang('pk_set_link_type_link'),
			'2'				=> $this->user->lang('pk_set_link_type_iframe'),
			'4'				=> $this->user->lang('pk_set_link_type_D_iframe_womenues'),
		);
		
		$settings	= array(
			'pm_recruitment_url'	=> array(
				'name'		=> 'pm_recruitment_url',
				'language'	=> 'recruitment_contact_type',
				'property'	=> 'text',
				'help'		=> 'recruitment_contact_type_help',
				'size'		=> 60,
			),
			'pm_recruitment_embed'	=> array(
				'name'		=> 'pm_recruitment_embed',
				'language'	=> 'recruit_embedded',
				'property'	=> 'dropdown',
				'help'		=> 'recruit_embedded_help',
				'options'	=> $a_linkMode,
			),
			'pm_recruitment_priority'	=> array(
				'name'		=> 'pm_recruitment_priority',
				'language'	=> 'recruit_priority',
				'property'	=> 'checkbox',
				'javascript'=> 'onchange="load_settings()"',
			),
			'pm_recruitment_talentsorroles'	=> array(
				'name'		=> 'pm_recruitment_talentsorroles',
				'language'	=> 'pm_recruitment_talentsorroles',
				'property'	=> 'dropdown',
				'options'	=> array('talents'=> $this->user->lang('pm_recruitment_talents'), 'roles'=>$this->user->lang('pm_recruitment_roles')),
				'javascript'=> 'onchange="load_settings()"',
			),
		);
	
		$priority_dropdown = array(
			''		=> '',
			'high'	=> $this->user->lang('recruit_priority_high'),
			'middle'=> $this->user->lang('recruit_priority_middle'),
			'low'	=> $this->user->lang('recruit_priority_low'),
		);
		// Load the classes
		$classes = $this->game->get('classes');
		foreach($classes as $class_id => $class_name) {
			if($class_id != 0) { //filter unknown
				if($this->game->icon_exists('talents') && $this->config->get('pm_recruitment_talentsorroles') == 'talents') {
					
					if ((int)$this->config->get('pm_recruitment_priority') == 1){
						$settings[] = array(
							'name'			=> 'pm_recruitment_class_'.$class_id,
							'language'		=> $this->game->decorate('classes', array($class_id)).' '.$this->game->get_name('classes', $class_id),
							'property'		=>	'dropdown',
							'options'		=> $priority_dropdown,
						);
					} else {
						$settings[] = array(
							'name'			=> 'pm_recruitment_class_'.$class_id,
							'language'		=> $this->game->decorate('classes', array($class_id)).' '.$this->game->get_name('classes', $class_id),
							'property'		=> 'text',
							'size'			=> 3,
						);
					}
					
					if(is_array($this->game->glang('talents'))) {
						$talents = $this->game->glang('talents');
						if(is_array($talents[$class_id])){
							foreach($talents[$class_id] as $talent_id => $talent_name) {
								
								if ((int)$this->config->get('pm_recruitment_priority') == 1){
									$settings[] = array(
										'name'		=>	'pm_recruitment_class_'.$class_id.'_'.$talent_id,
										'language'	=>	$this->game->decorate('classes', array($class_id)).' '.$this->game->get_name('classes', $class_id).' - '.$this->game->decorate('talents', array($class_id, $talent_id)).$talent_name,
										'property'	=>	'dropdown',
										'options'	=> $priority_dropdown,
									);
								} else {
									$settings[] = array(
										'name'		=>	'pm_recruitment_class_'.$class_id.'_'.$talent_id,
										'language'	=>	$this->game->decorate('classes', array($class_id)).' '.$this->game->get_name('classes', $class_id).' - '.$this->game->decorate('talents', array($class_id, $talent_id)).$talent_name,
										'property'	=>	'text',
										'size'		=> 3,
									);
								}
							} //close foreach
						} // end if is array
					} //close talents
					
				} elseif ($this->config->get('pm_recruitment_talentsorroles') == 'roles' && count($this->pdh->get('roles', 'id_list', array())) > 0){
				//Roles
					if ((int)$this->config->get('pm_recruitment_priority') == 1){
						$settings[] = array(
							'name'			=> 'pm_recruitment_class_'.$class_id,
							'language'		=> $this->game->decorate('classes', array($class_id)).' '.$this->game->get_name('classes', $class_id),
							'property'		=>	'dropdown',
							'options'		=> $priority_dropdown,
						);
					} else {
						$settings[] = array(
							'name'			=> 'pm_recruitment_class_'.$class_id,
							'language'		=> $this->game->decorate('classes', array($class_id)).' '.$this->game->get_name('classes', $class_id),
							'property'		=> 'text',
							'size'			=> 3,
						);
					}
					
					if($class_id != 0) { //filter unknown
						$arrRoles = $this->pdh->get('roles', 'memberroles', array($class_id));
						
						if(is_array($arrRoles)){
							foreach($arrRoles as $role_id => $role_name) {
								
								if ((int)$this->config->get('pm_recruitment_priority') == 1){
									$settings[] = array(
										'name'		=>	'pm_recruitment_class_'.$class_id.'_'.$role_id,
										'language'	=>	$this->game->decorate('classes', array($class_id)).' '.$this->game->get_name('classes', $class_id).' - '.$this->game->decorate('roles', array($role_id)).$role_name,
										'property'	=>	'dropdown',
										'options'	=> $priority_dropdown,
									);
								} else {
									$settings[] = array(
										'name'		=>	'pm_recruitment_class_'.$class_id.'_'.$role_id,
										'language'	=>	$this->game->decorate('classes', array($class_id)).' '.$this->game->get_name('classes', $class_id).' - '.$this->game->decorate('roles', array($role_id)).$role_name,
										'property'	=>	'text',
										'size'		=> 3,
									);
								}
							} //close foreach
						}
					}
				
				//Just plain classes
				} else {
					if ((int)$this->config->get('pm_recruitment_priority') == 1){
						$settings[] = array(
							'name'		=> 'pm_recruitment_class_'.$class_id,
							'language'	=> $this->game->decorate('classes', array($class_id)).' '.$this->game->get_name('classes', $class_id),
							'property'	=>	'dropdown',
							'options'	=> $priority_dropdown,
						);
					} else {
						$settings[] = array(
							'name'		=> 'pm_recruitment_class_'.$class_id,
							'language'	=> $this->game->decorate('classes', array($class_id)).' '.$this->game->get_name('classes', $class_id),
							'property'	=> 'text',
							'size'		=> 3,
						);
					}
				}
			}
		}
		return $settings;
	}

	public function output() {
		$this->tpl->add_css('.rec_middle{color:#ff7c0a;}');
			$recruit = '<table width="100%" border="0" cellspacing="1" cellpadding="2" class="noborder colorswitch hoverrows">';

			//show Link URL
			$target = '';
			if (strlen($this->config->get('pm_recruitment_url')) > 1) {
				switch ($this->config->get('pm_recruitment_embed')){
					case '0':  $path = $this->config->get('pm_recruitment_url');
						break ;
					case '1':  $target = ' target="_blank"';
							   $path = $this->config->get('pm_recruitment_url');
						break ;
					case '2':
					case '3':
					case '4':  $path = $this->root_path.'wrapper.php'.$this->SID.'&amp;id=recruitment';
						break ;
				}
							
			}else{		//Link URL -> Email / guildrequest plugin
				$path = "mailto:".$this->crypt->decrypt($this->config->get('admin_email'));
				if ($this->pm->check('guildrequest', PLUGIN_INSTALLED)){
					$path = $this->root_path.'plugins/guildrequest/addrequest.php'.$this->SID ;
				}
			}
			$url = '<a href="'.$path.'" '.$target.'>' ;
			$classes = $this->game->get('classes');
			$show = false;
			foreach($classes as $class_id => $class_name) {
				if($class_id != 0) { //filter unknown
					if($this->game->icon_exists('talents') && $this->config->get('pm_recruitment_talentsorroles') == 'talents') {
						if(is_array($this->game->glang('talents'))) {
							$talents = $this->game->glang('talents');
							$talentOut = '';
							if (isset($talents[$class_id])){
								foreach($talents[$class_id] as $talent_id => $talent_name) {
									if ((int)$this->config->get('pm_recruitment_priority') == 1){
										if (strlen($this->config->get('pm_recruitment_class_'.$class_id.'_'.$talent_id))){
											$talentOut .= '<tr>'.
															'<td class="class_'.$class_id.' nowrap small">&nbsp;&nbsp;&nbsp;&nbsp;'.$this->game->decorate('talents', array($class_id, $talent_id)).$talent_name.'</td>
															<td><span class="'.$this->handle_cssclass($this->config->get('pm_recruitment_class_'.$class_id.'_'.$talent_id)).'">'. $this->user->lang('recruit_priority_'.$this->config->get('pm_recruitment_class_'.$class_id.'_'.$talent_id)). '</span></td>
														</tr>';
											$show =true ;
										}
									} else {
										if ($this->config->get('pm_recruitment_class_'.$class_id.'_'.$talent_id)> 0){
											$talentOut .= '<tr>'.
															'<td class="class_'.$class_id.' nowrap">&nbsp;&nbsp;&nbsp;&nbsp;'.$this->html->ToolTip($talent_name.' '.$this->game->get_name('classes', $class_id), $this->game->decorate('talents', array($class_id, $talent_id)).' '.$this->game->decorate('classes', array($class_id)).' '.$this->game->get_name('classes', $class_id)).'</td>
															<td>'. $this->config->get('pm_recruitment_class_'.$class_id.'_'.$talent_id). '</td>
														</tr>';
											$show =true ;
										}
									}
								} //close foreach
							}
						} //close talents

						
						if ((int)$this->config->get('pm_recruitment_priority') == 1){
							if (strlen($this->config->get('pm_recruitment_class_'.$class_id)) || strlen($talentOut)){
								$recruit .= '<tr>'.
												'<td class="class_'.$class_id.' nowrap">'.$this->game->decorate('classes', array($class_id)).' '.$this->game->get_name('classes', $class_id).'</td>
												<td><span class="'.$this->handle_cssclass($this->config->get('pm_recruitment_class_'.$class_id)).'">'. $this->user->lang('recruit_priority_'.$this->config->get('pm_recruitment_class_'.$class_id)). '</span></td>
											</tr>'.$talentOut;
								$show =true ;
							}
						} else {
							if (($this->config->get('pm_recruitment_class_'.$class_id) > 0)  || strlen($talentOut)){
								$recruit .= '<tr>'.
												'<td class="class_'.$class_id.' nowrap">'.$this->game->decorate('classes', array($class_id)).' '.$this->game->get_name('classes', $class_id).'</td>
												<td>'.$this->config->get('pm_recruitment_class_'.$class_id).'</td>
											</tr>'.$talentOut;
								$show =true ;
							}
						}
					} elseif ($this->config->get('pm_recruitment_talentsorroles') == 'roles' && count($this->pdh->get('roles', 'id_list', array())) > 0){
						$arrRoles = $this->pdh->get('roles', 'memberroles', array($class_id));
						$rolesOut = '';
						if(is_array($arrRoles)){
							foreach($arrRoles as $role_id => $role_name) {
								
								if ((int)$this->config->get('pm_recruitment_priority') == 1){
											if (strlen($this->config->get('pm_recruitment_class_'.$class_id.'_'.$role_id))){
												$rolesOut .= '<tr>'.
																'<td class="class_'.$class_id.' nowrap small">&nbsp;&nbsp;&nbsp;&nbsp;'.$this->game->decorate('roles', array($role_id)).$role_name.'</td>
																<td><span class="'.$this->handle_cssclass($this->config->get('pm_recruitment_class_'.$class_id.'_'.$role_id)).'">'. $this->user->lang('recruit_priority_'.$this->config->get('pm_recruitment_class_'.$class_id.'_'.$role_id)). '</span></td>
															</tr>';
												$show =true ;
											}
								} else {
									if ($this->config->get('pm_recruitment_class_'.$class_id.'_'.$role_id)> 0){
										$rolesOut .= '<tr>'.
														'<td class="class_'.$class_id.' nowrap">&nbsp;&nbsp;&nbsp;&nbsp;'.$this->html->ToolTip($role_name.' '.$this->game->get_name('classes', $class_id), $this->game->decorate('roles', array($role_id)).' '.$role_name).'</td>
														<td>'. $this->config->get('pm_recruitment_class_'.$class_id.'_'.$role_id). '</td>
													</tr>';
										$show =true ;
									}
								}
							} //close foreach
						}
						
						if ((int)$this->config->get('pm_recruitment_priority') == 1){
							if (strlen($this->config->get('pm_recruitment_class_'.$class_id)) || strlen($rolesOut)){
								$recruit .= '<tr>'.
												'<td class="class_'.$class_id.' nowrap">'.$this->game->decorate('classes', array($class_id)).' '.$this->game->get_name('classes', $class_id).'</td>
												<td><span class="'.$this->handle_cssclass($this->config->get('pm_recruitment_class_'.$class_id)).'">'. $this->user->lang('recruit_priority_'.$this->config->get('pm_recruitment_class_'.$class_id)). '</span></td>
											</tr>'.$rolesOut;
								$show =true ;
							}
						} else {
							if (($this->config->get('pm_recruitment_class_'.$class_id) > 0)  || strlen($rolesOut)){
								$recruit .= '<tr>'.
												'<td class="class_'.$class_id.' nowrap">'.$this->game->decorate('classes', array($class_id)).' '.$this->game->get_name('classes', $class_id).'</td>
												<td>'.$this->config->get('pm_recruitment_class_'.$class_id).'</td>
											</tr>'.$rolesOut;
								$show =true ;
							}
						}
					
					} else {
						if ((int)$this->config->get('pm_recruitment_priority') == 1){
							if (strlen($this->config->get('pm_recruitment_class_'.$class_id))){
								$recruit .= '<tr>'.
												'<td class="class_'.$class_id.'">'.$this->html->ToolTip($this->game->get_name('classes', $class_id), $this->game->decorate('classes', array($class_id)).' '.$this->game->get_name('classes', $class_id)).'</td>
												<td><span class="'.$this->handle_cssclass($this->config->get('pm_recruitment_class_'.$class_id)).'">'. $this->user->lang('recruit_priority_'.$this->config->get('pm_recruitment_class_'.$class_id)). '</span></td>
											</tr>';
								$show =true ;
							}
						} else {
							if ($this->config->get('pm_recruitment_class_'.$class_id) > 0){
								$recruit .= '<tr>'.
												'<td class="class_'.$class_id.'">'.$this->html->ToolTip($this->game->get_name('classes', $class_id), $this->game->decorate('classes', array($class_id)).' '.$this->game->get_name('classes', $class_id)).'</td>
												<td>'. $this->config->get('pm_recruitment_class_'.$class_id). '</td>
											</tr>';
								$show =true ;
							}
						}
					}
				}
			}

			$recruit .= '<tr><td class="smalltitle" align="center" colspan="2"><b>'.$url.$this->user->lang('recruitment_contact').' </a></b></td></tr>';
			$recruit .= ' </table>';

		if ($show) {
			return $recruit;
		} else {
			$return = $this->user->lang('recruitment_noneed');
			return $return;
		}
	}

	public function reset() {
		$this->pdc->del('portal.modul.recruitment.'.$this->root_path);
		$this->pdc->del('portal.modul.recruitment.show');
	}

	private function handle_cssclass($priority){
		switch($priority){
			case 'high' : return 'negative';
			case 'low'	: return 'positive';
			default: return 'rec_middle';
		}
	}
}
if(version_compare(PHP_VERSION, '5.3.0', '<')) registry::add_const('short_recruitment_portal', recruitment_portal::__shortcuts());
?>