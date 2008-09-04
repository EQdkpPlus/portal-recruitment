<?php
/*
 * Project:     EQdkp-Plus
 * License:     Creative Commons - Attribution-Noncommercial-Share Alike 3.0 Unported
 * Link:		http://creativecommons.org/licenses/by-nc-sa/3.0/
 * -----------------------------------------------------------------------
 * Began:       2008
 * Date:        $Date$
 * -----------------------------------------------------------------------
 * @author      $Author$
 * @copyright   2006-2008 Corgan - Stefan Knaak | Wallenium & the EQdkp-Plus Developer Team
 * @link        http://eqdkp-plus.com
 * @package     eqdkp-plus
 * @version     $Rev$
 * 
 * $Id$
 */


if ( !defined('EQDKP_INC') ){
    header('HTTP/1.0 404 Not Found');exit;
}

$portal_module['recruitment'] = array(
			'name'			    => 'Recruitment Module',
			'path'			    => 'recruitment',
			'version'		    => '1.0.3',
			'author'        => 'Corgan',
			'contact'		    => 'http://www.eqdkp-plus.com',
			'description'   => 'Searching for Members',
			'positions'     => array('left1', 'left2', 'right'),
      'install'       => array(
	                          'autoenable'        => '0',
	                          'defaultposition'   => 'left1',
	                          'defaultnumber'     => '1',
                          ),
    );

$portal_settings['recruitment'] = array(

);

if(!function_exists(recruitment_module))
{
	function recruitment_module()
  	{
  		global $conf_plus,$db,$user,$tpl,$eqdkp,$user,$eqdkp_root_path,$html, $plang;
  
   		$sql = 'SELECT class_name , class_id
        	   	FROM '.CLASS_TABLE.' group by class_name ORDER BY class_name';
  
    	$result = $db->query($sql);  
    	$recruit = '<table width="100%" border="0" cellspacing="1" cellpadding="2" class="noborder">';
  
		while ( $row = $db->fetch_record($result) )
		{
			if($eqdkp->config['default_game'] == 'WoW' and ($row['class_name'] <> 'Unknown' ))
			{
				$i = 0 ;
				$specs = $user->lang['talents'][renameClasstoenglish($row['class_name'])] ;
  			if(is_array($specs))
  			{
          foreach ($specs as $specname)
  				{
  					$i++;
  					$classCount = $conf_plus['pk_recruitment_class['.$row['class_id'].']['.$i.']'] ;
  			   		if ($classCount > 0)
  			   	  	{
  			   	  		$rowcolor = $eqdkp->switch_row_class();
  			   	  		$c_color = renameClasstoenglish($row['class_name']);				   		
  				   		$img = $eqdkp_root_path."games/WoW/talents/".strtolower(renameClasstoenglish($row['class_name'])).($i-1).".png" ;
  				   		$icon= "<img src='".$img."'>" ;
  				   		$showntext = $html->ToolTip($specname.' - '.$row['class_name'],$icon.get_ClassIcon($row['class_name']).' '.$row['class_name'],$icon) ;
  			   	  		$recruit .=
  			   	  					'<tr class="'.$rowcolor.'" nowrap onmouseover="this.className=\'rowHover\';" onmouseout="this.className=\''.$rowcolor.'\';">'.
  			   	  		 			'<td class="'.$c_color.'">'.$showntext.'</td>
  			   	  						 					   <td>'. $classCount. '</td>
  			   	  					</tr>';
  			   	  		$show =true ;
  			   	  	}
  				}
				}
			}else
			{			
				if ($conf_plus['pk_recruitment_class['.$row['class_id'].']'] > 0)
			  	{
			  		$rowcolor = $eqdkp->switch_row_class();
			  		$c_color = renameClasstoenglish($row['class_name']);
			  		$recruit .= '<tr class="'.$rowcolor.'"><td class="'.$c_color.'">'.get_ClassIcon($row['class_name'],$row['class_id']).' '.$row['class_name'].'</td>
			  						 					   <td>'. $conf_plus['pk_recruitment_class['.$row['class_id'].']']. '</td>
			  					</tr>';
			  		$show =true ;
			  	}
			}
		}
  
  	   	if (strlen($conf_plus['pk_recruitment_url']) > 1) 
  	   	{
  	   		if($conf_plus['pk_recruitment_url_emb']==1)
	  	   	{
	  	   		$url = '<a href="'.$eqdkp_root_path.'wrapper.php?id=recemb">' ;
	  	   	}else {
	  	   		$url = '<a href="'.$conf_plus['pk_recruitment_url'].'">' ;	
	  	   	}
  	   		
  	   	}  	   	
  	   	else
  	   	{
  	   		$url = '<a href="mailto:'.$conf_plus['pk_contact_email'].'">';
  	   	}
  
  	   	$recruit .= '<tr class="'.$rowcolor.'"><td colspan=2 class="smalltitle" align="center">'.$url.$plang['recruitment_contact'].' </a></td></tr>';
  	   	$recruit .= ' </table>';
    if ($show) {
      return $recruit;
    }else{
      return $plang['recruitment_noneed'];
    }
  }
}
?>
