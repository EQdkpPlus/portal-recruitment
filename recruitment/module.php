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
			'version'		    => '1.0.4',
			'author'        	=> 'Corgan',
			'contact'		    => 'http://www.eqdkp-plus.com',
			'description'   	=> 'Searching for Members',
			'positions'     	=> array('left1', 'left2', 'right'),
      		'install'       	=> array(
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
  		global $conf_plus,$db,$user,$tpl,$eqdkp,$user,$eqdkp_root_path,$html, $plang,$pm,$pcache, $game;

		// RSS Feed
		include_once($eqdkp_root_path."libraries/UniversalFeedCreator/UniversalFeedCreator.class.php");
		$rss = new UniversalFeedCreator();

		$rss->title           = "Recruitment";
		$rss->description     = $eqdkp->config['main_title']." EQdkp-Plus - looking for members" ;
		$rss->link            = $pcache->BuildLink();
		$rss->syndicationURL  = $pcache->BuildLink().$_SERVER['PHP_SELF'];

    	$recruit = '<table width="100%" border="0" cellspacing="1" cellpadding="2" class="noborder">';

    	//show Link URL
  	   	if (strlen($conf_plus['pk_recruitment_url']) > 1)
  	   	{
  	   		if($conf_plus['pk_recruitment_url_emb']==1)
	  	   	{
	  	   		$path = $eqdkp_root_path.'wrapper.php?id=recemb' ;
	  	   	}else
	  	   	{
	  	   		$path = $conf_plus['pk_recruitment_url'] ;
	  	   	}

  	   	}
  	   	else //Link URL -> Email / guildrequest plugin
  	   	{
  	   		$path = "mailto:".$conf_plus['pk_contact_email'];

    		if ($pm->check(PLUGIN_INSTALLED, 'guildrequest'))
			{
				$path = $eqdkp_root_path.'plugins/guildrequest/writerequest.php' ;
			}
  	   	}

  	   	$url = '<a href="'.$path.'">' ;

		foreach($game->get('classes') as $class_id => $class_name) {
			if($game->icon_exists('talents')) {
				if(is_array($game->glang('talents'))) {
					$talents = $game->glang('talents');
					foreach($talents[$class_id] as $talent_id => $talent_name) {
	  					$classCount = $conf_plus['pk_recruitment_class['.$class_id.']['.$talent_id.']'] ;
	  					$rowcolor = $eqdkp->switch_row_class();
	  					$icon = $game->decorate('talents', array($class_id, $talent_id));
	  					$showntext = $html->ToolTip($talent_name.' '.$class_name, $icon.$game->decorate('classes', array($class_id)).' '.$class_name,$icon) ;
		   				$recruit .= '<tr class="'.$rowcolor.'" nowrap onmouseover="this.className=\'rowHover\';" onmouseout="this.className=\''.$rowcolor.'\';">';
		   	   			$recruit .= '<td class="class_'.$class_id.'">'.$showntext.'</td>';
			   			$recruit .= '<td>'. $classCount. '</td></tr>';
						//Create RSS
						$rssitem = new FeedItem();
						$rssitem->title        = $classCount. " " .$talent_name. " " .$class_name ;
						$rssitem->link         = preg_replace("/\.\//ms", $pcache->BuildLink(),$path,1);
						$rssitem->description  = $classCount. " " .$talent_name. " " .$class_name;

						$additionals = array(
							'class_name'  => $class_name,
					    	'class_count'  => $classCount,
					    	'class_spec'  => $talent_name,
					    	'class_icon'  =>  "<![CDATA[".str_replace('../','',preg_replace("/\.\//ms", $pcache->BuildLink(),$game->decorate('classes', array($class_id)),1))."]]>",
					    	'spec_icon'  =>  "<![CDATA[".preg_replace("/\.\//ms", $pcache->BuildLink(),$game->decorate('talents', array($class_id, $talent_id, true)),1)."]]>" ,
						);
						$rssitem->additionalElements = $additionals;
						$rss->addItem($rssitem);
			   		}
				}
			} else {
				$classCount = $conf_plus['pk_recruitment_class['.$class_id.']'];
				if ($classCount > 0)
			   {
					$rowcolor = $eqdkp->switch_row_class();
			  		$recruit .= '<tr class="'.$rowcolor.'"><td class="class_'.$class_id.'">'.$game->decorate('classes', array($class_id)).' '.$class_name.'</td>
			  						 					   <td>'. $conf_plus['pk_recruitment_class['.$class_id.']']. '</td>
			  					</tr>';
			  		$show =true ;
					//Create RSS
			    	$rssitem = new FeedItem();
			    	$rssitem->title        = $classCount. " " .$class_name;
			    	$rssitem->link         = preg_replace("/\.\//ms", $pcache->BuildLink(),$path,1);
			    	$rssitem->description  = $classCount. " " .$class_name;
			        
			    	$additionals = array(
			    		'class_name'  => $class_name,
			        	'class_count'  => $classCount,
			        	'class_icon'  =>  "<![CDATA[".str_replace('../','',preg_replace("/\.\//ms", $pcache->BuildLink(),$game->decorate('classes', array($class_id)),$class_id),1))."]]>",
			    	);

			    	$rssitem->additionalElements = $additionals;
			    	$rss->addItem($rssitem);
				}
	   		}
   		}
		$rss->saveFeed("RSS2.0", $pcache->FilePath('recruitment.xml', 'eqdkp'),false);
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