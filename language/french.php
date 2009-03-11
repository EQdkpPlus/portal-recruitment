<?php
/*
 * Project:     EQdkp-Plus
 * License:     Creative Commons - Attribution-Noncommercial-Share Alike 3.0 Unported
 * Link:		http://creativecommons.org/licenses/by-nc-sa/3.0/
 * -----------------------------------------------------------------------
 * Began:       2008
 * Date:        $Date: 2008-09-04 21:05:57 +0200 (Do, 04 Sep 2008) $
 * -----------------------------------------------------------------------
 * @author      $Author: wallenium $
 * @copyright   2006-2008 Corgan - Stefan Knaak | Wallenium & the EQdkp-Plus Developer Team
 * @link        http://eqdkp-plus.com
 * @package     eqdkp-plus
 * @version     $Rev: 2676 $
 * 
 * $Id: english.php 2676 2008-09-04 19:05:57Z wallenium $
 */


if ( !defined('EQDKP_INC') ){
    header('HTTP/1.0 404 Not Found');exit;
}

$plang = array_merge($plang, array(
  'recruitment'             => 'Recrutement',
  'recruitment_open'        => 'Recrutement ouvert',
  'recruitment_contact'     => 'Postuler',
  'recruitment_noneed'      => 'Le recrutement est actuellement fermé.',
));
?>
