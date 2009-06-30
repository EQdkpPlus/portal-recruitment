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
  		global $conf_plus,$db,$user,$tpl,$eqdkp,$user,$eqdkp_root_path,$html, $plang,$pm,$pcache, $pdc;
	  		
		$recruit = $pdc->get('portal.modul.recruitment',false,true);
		if (!$recruit) 
		{   		
  		
			// RSS Feed
			include_once($eqdkp_root_path."libraries/UniversalFeedCreator/UniversalFeedCreator.class.php");
			$rss = new UniversalFeedCreator();
			
			$rss->title           = "Recruitment";
			$rss->description     = $eqdkp->config['main_title']." EQdkp-Plus - looking for members" ;
			$rss->link            = $pcache->BuildLink();
			$rss->syndicationURL  = $pcache->BuildLink().$_SERVER['PHP_SELF'];  		
	  
	   		$sql = 'SELECT class_name , class_id
	        	   	FROM __classes group by class_name ORDER BY class_name';
	  
	    	$result = $db->query($sql);  
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
	
	    	
			while ( $row = $db->fetch_record($result) )
			{
				if($eqdkp->config['default_game'] == 'WoW' and ($row['class_name'] <> 'Unknown' ))
				{
					$i = 0 ;
					$specs = $user->lang['talents'][renameClasstoenglish($row['class_name'])] ;
					$specs[] = "";
					
	  				if(is_array($specs))
	  				{
		          		foreach ($specs as $specname)
		  				{
		  					$i++;
		  					$classCount = $conf_plus['pk_recruitment_class['.$row['class_id'].']['.$i.']'] ;
		  			   		if ($classCount > 0)
		  			   	  	{
		  			   	  		$rowcolor = $eqdkp->switch_row_class();
		  			   	  		$c_color = str_replace(' ','',renameClasstoenglish($row['class_name']));				   		
		  				   		$img = $eqdkp_root_path."games/WoW/talents/".str_replace(' ', '',strtolower(renameClasstoenglish($row['class_name']))).($i-1).".png" ;  				   		
		  				   		$icon = (file_exists($img)) ? "<img src='".$img."'>" : "" ;	  				   			  				   		
		  				   		$showntext = $html->ToolTip($specname.' '.$row['class_name'],$icon.get_ClassIcon($row['class_name']).' '.$row['class_name'],$icon) ;
		  			   	  		$recruit .=
		  			   	  					'<tr class="'.$rowcolor.'" nowrap onmouseover="this.className=\'rowHover\';" onmouseout="this.className=\''.$rowcolor.'\';">'.
		  			   	  		 			'<td class="'.$c_color.'">'.$showntext.'</td>
		  			   	  						 					   <td>'. $classCount. '</td>
		  			   	  					</tr>';
		  			   	  		$show =true ;
		  			   	  		
							    //Create RSS
						          $rssitem = new FeedItem();
						          $rssitem->title        = $classCount. " " .$specname. " " .stripslashes($row['class_name']) ;
						          $rssitem->link         = preg_replace("/\.\//ms", $pcache->BuildLink(),$path,1);
						          $rssitem->description  = $classCount. " " .$specname. " " .stripslashes($row['class_name']) ;
						          
						          $additionals = array('class_name'  => stripslashes($row['class_name']),	        					 
						          					  'class_count'  => $classCount,	        					       					 	       
						          					  'class_spec'  => $specname,	        					       					 	       
						          					  'class_icon'  =>  "<![CDATA[".str_replace('../','',preg_replace("/\.\//ms", $pcache->BuildLink(),get_ClassIcon($row['class_name']),1))."]]>",	        					       					 	       
						          					  'spec_icon'  =>  "<![CDATA[".preg_replace("/\.\//ms", $pcache->BuildLink(),$img,1)."]]>" ,	        					       					 	       
						          );
						          					  					 
						          $rssitem->additionalElements = $additionals;						          
						          $rss->addItem($rssitem);    	  		
		  			   	  	}
		  				}
					}								
				}else // all other games
				{	
					if ($conf_plus['pk_recruitment_class['.$row['class_id'].']'] > 0)
				  	{
				  		$rowcolor = $eqdkp->switch_row_class();
				  		$c_color = str_replace(' ','',renameClasstoenglish($row['class_name']));
				  		$recruit .= '<tr class="'.$rowcolor.'"><td class="'.$c_color.'">'.get_ClassIcon($row['class_name'],$row['class_id']).' '.$row['class_name'].'</td>
				  						 					   <td>'. $conf_plus['pk_recruitment_class['.$row['class_id'].']']. '</td>
				  					</tr>';
				  		$show =true ;
					    //Create RSS
				          $rssitem = new FeedItem();
				          $rssitem->title        = $classCount. " " .stripslashes($row['class_name']) ;
				          $rssitem->link         = preg_replace("/\.\//ms", $pcache->BuildLink(),$path,1);
				          $rssitem->description  = $classCount. " " .stripslashes($row['class_name']) ;
				          
				          $additionals = array('class_name'  => stripslashes($row['class_name']),	        					 
				          					  'class_count'  => $classCount,	        					       					 	       
				          					  'class_icon'  =>  "<![CDATA[".str_replace('../','',preg_replace("/\.\//ms", $pcache->BuildLink(),get_ClassIcon($row['class_name'],$row['class_id']),1))."]]>", 		 
				          );			          					  			
				          
				          $rssitem->additionalElements = $additionals;						          
				          $rss->addItem($rssitem);  			  		
				  	}
				}						
			}
			$rss->saveFeed("RSS2.0", $pcache->FilePath('recruitment.xml', 'eqdkp'),false);  
	  	   	$recruit .= '<tr class="'.$rowcolor.'"><td colspan=2 class="smalltitle" align="center">'.$url.$plang['recruitment_contact'].' </a></td></tr>';
	  	   	$recruit .= ' </table>';
		}else{
			$show = true;
		}
		
    	if ($show) 
    	{    		
    		$pdc->put('portal.modul.recruitment',$recruit,86400,false,true);
      		return $recruit;
    	}else{
      		return $plang['recruitment_noneed'];
    	}
    	
    	
  }# end function
}
?>
