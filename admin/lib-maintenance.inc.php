<?php // $Revision: 2.5 $

/************************************************************************/
/* phpAdsNew 2                                                          */
/* ===========                                                          */
/*                                                                      */
/* Copyright (c) 2000-2002 by the phpAdsNew developers                  */
/* For more information visit: http://www.phpadsnew.com                 */
/*                                                                      */
/* This program is free software. You can redistribute it and/or modify */
/* it under the terms of the GNU General Public License as published by */
/* the Free Software Foundation; either version 2 of the License.       */
/************************************************************************/


// Load translations
@include (phpAds_path.'/language/english/maintenance.lang.php');
if ($phpAds_config['language'] != 'english' && file_exists(phpAds_path.'/language/'.$phpAds_config['language'].'/maintenance.lang.php'))
	@include (phpAds_path.'/language/'.$phpAds_config['language'].'/maintenance.lang.php');



function phpAds_MaintenanceSelection($section)
{
	global 
		$phpAds_config
		,$phpAds_TextDirection
		,$strBanners
		,$strCache
		,$strChooseSection
		,$strPriority
		,$strSourceEdit
		,$strStats
		,$strStorage
	;

?>
<script language="JavaScript">
<!--
function maintenance_goto_section()
{
	s = document.maintenance_selection.section.selectedIndex;

	s = document.maintenance_selection.section.options[s].value;
	document.location = 'maintenance-' + s + '.php';
}
// -->
</script>
<?php
	echo "<table border='0' width='100%' cellpadding='0' cellspacing='0'>";
    echo "<tr><form name='maintenance_selection'><td height='35'>";
	echo "<b>".$strChooseSection.":&nbsp;</b>";
    echo "<select name='section' onChange='maintenance_goto_section();'>";
	
	echo "<option value='banners'".($section == 'banners' ? ' selected' : '').">".$strBanners."</option>";
	echo "<option value='priority'".($section == 'priority' ? ' selected' : '').">".$strPriority."</option>";
	
	if ($phpAds_config['type_web_allow'] == true && (($phpAds_config['type_web_mode'] == 0 && 
	    $phpAds_config['type_web_dir'] != '') || ($phpAds_config['type_web_mode'] == 1 && 
	    $phpAds_config['type_web_ftp'] != '')) && $phpAds_config['type_web_url'] != '')
		echo "<option value='storage'".($section == 'storage' ? ' selected' : '').">".$strStorage."</option>";
	
	if ($phpAds_config['delivery_caching'] != 'none')
		echo "<option value='cache'".($section == 'zones' ? ' selected' : '').">".$strCache."</option>";
    
	echo "<option value='source-edit'".($section == 'source-edit' ? ' selected' : '').">".$strSourceEdit."</option>";
	echo "</select>&nbsp;<a href='javascript:void(0)' onClick='maintenance_goto_section();'>";
	echo "<img src='images/".$phpAds_TextDirection."/go_blue.gif' border='0'></a>";
    echo "</td></form></tr>";
  	echo "</table>";
	
	phpAds_ShowBreak();
}

?>