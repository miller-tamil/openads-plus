<?php // $Revision: 2.2 $

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



// Include required files
require ("config.php");
require ("lib-statistics.inc.php");


// Register input variables
phpAds_registerGlobal ('selection');


// Security check
phpAds_checkAccess(phpAds_Admin + phpAds_Agency + phpAds_Client + phpAds_Affiliate);


// Load translations
@include (phpAds_path.'/language/english/report.lang.php');
if ($phpAds_config['language'] != 'english' && file_exists(phpAds_path.'/language/'.$phpAds_config['language'].'/report.lang.php'))
	@include (phpAds_path.'/language/'.$phpAds_config['language'].'/report.lang.php');


/*********************************************************/
/* HTML framework                                        */
/*********************************************************/

phpAds_PageHeader("3");



/*********************************************************/
/* Get plugins list                                      */
/*********************************************************/

function phpAds_ReportPluginList($directory)
{
	$pluginDir = opendir ($directory);
	
	while ($pluginFile = readdir ($pluginDir))
	{
		if (ereg ('^([a-zA-Z0-9\-]*)\.plugin\.php$', $pluginFile, $matches))
		{
			$plugin = phpAds_ReportGetPluginInfo($directory.$pluginFile);
			
			if (phpAds_isUser ($plugin['plugin-authorize']))
				$plugins[$matches[1]] = $plugin;
		}
	}
	
	closedir ($pluginDir);
	
	return ($plugins);
}

function phpAds_ReportGetPluginInfo($filename)
{
	include ($filename);
	return  ($plugin_info_function());
}



/*********************************************************/
/* Functions to get plugin parameters                    */
/*********************************************************/

function phpAds_getCampaignArray()
{
	global $phpAds_config;
	
	if (phpAds_isUser(phpAds_Admin))
	{
		$query =
			"SELECT campaignid,campaignname".
			" FROM ".$phpAds_config['tbl_campaigns'];
	}
	elseif (phpAds_isUser(phpAds_Agency))
	{
		$query =
			"SELECT campaignid,campaignname".
			" FROM ".$phpAds_config['tbl_campaigns']." AS m".
			",".$phpAds_config['tbl_clients']." AS c".
			" WHERE c.clientid=m.clientid".
			" AND c.agencyid=".phpAds_getUserID();
	}
	elseif (phpAds_isUser(phpAds_Client))
	{
		$query =
			"SELECT campaignid,campaignname".
			" FROM ".$phpAds_config['tbl_campaigns'].
			" WHERE clientid=".phpAds_getUserID();
	}
	
	$res = phpAds_dbQuery($query);	
	while ($row = phpAds_dbFetchArray($res))
		$campaignArray[$row['campaignid']] = phpAds_buildName ($row['campaignid'], $row['campaignname']);
	
	return ($campaignArray);
}

function phpAds_getClientArray()
{
	global $phpAds_config;
	
	if (phpAds_isUser(phpAds_Admin))
	{
		$query =
			"SELECT clientid,clientname".
			" FROM ".$phpAds_config['tbl_clients'];
	}
	elseif (phpAds_isUser(phpAds_Agency))
	{
		$query =
			"SELECT clientid,clientname".
			" FROM ".$phpAds_config['tbl_clients'].
			" WHERE agencyid=".phpAds_getUserID();
	}
	elseif (phpAds_isUser(phpAds_Client))
	{
		$query =
			"SELECT clientid,clientname".
			" FROM ".$phpAds_config['tbl_clients'].
			" WHERE clientid=".phpAds_getUserID();
	}
	$res = phpAds_dbQuery($query);
	
	while ($row = phpAds_dbFetchArray($res))
		$clientArray[$row['clientid']] = phpAds_buildName ($row['clientid'], $row['clientname']);
	
	return ($clientArray);
}

function phpAds_getZoneArray()
{
	global $phpAds_config;
	
	if (phpAds_isUser(phpAds_Admin))
	{
		$query =
			"SELECT zoneid,zonename".
			" FROM ".$phpAds_config['tbl_zones'];
	}
	elseif (phpAds_isUser(phpAds_Agency))
	{
		$query =
			"SELECT zoneid,zonename".
			" FROM ".$phpAds_config['tbl_zones']." AS z".
			",".$phpAds_config['tbl_affiliates']." AS a".
			" WHERE a.affiliateid=z.affiliateid".
			" AND a.agencyid=".phpAds_getUserID();
	}
	elseif (phpAds_isUser(phpAds_Client))
	{
		$query =
			"SELECT zoneid,zonename".
			" FROM ".$phpAds_config['tbl_zones'].
			" WHERE affiliateid=".phpAds_getUserID();
	}
	$res = phpAds_dbQuery($query);
	
	while ($row = phpAds_dbFetchArray($res))
		$zoneArray[$row['zoneid']] = phpAds_buildName ($row['zoneid'], $row['zonename']);
	
	return ($zoneArray);
}

function phpAds_getAffiliateArray()
{
	global $phpAds_config;
	
	if (phpAds_isUser(phpAds_Admin))
	{
		$query =
			"SELECT affiliateid,name".
			" FROM ".$phpAds_config['tbl_affiliates'];
	}
	elseif (phpAds_isUser(phpAds_Agency))
	{
		$query =
			"SELECT affiliateid,name".
			" FROM ".$phpAds_config['tbl_affiliates'].
			" WHERE agencyid=".phpAds_getUserID();
	}
	elseif (phpAds_isUser(phpAds_Client))
	{
		$query =
			"SELECT affiliateid,name".
			" FROM ".$phpAds_config['tbl_affiliates'].
			" WHERE affiliateid=".phpAds_getUserID();
	}
	$res = phpAds_dbQuery($query);
	
	while ($row = phpAds_dbFetchArray($res))
		$affiliateArray[$row['affiliateid']] = phpAds_buildAffiliateName ($row['affiliateid'], $row['name']);
	
	return ($affiliateArray);
}


function phpAds_getKeywordsArray()
{
	global $phpAds_config;

	if (phpAds_isUser(phpAds_Admin))
	{
		$query =
			"SELECT affiliateid,name".
			" FROM ".$phpAds_config['tbl_affiliates'];
	}
	elseif (phpAds_isUser(phpAds_Agency))
	{
		$query =
			"SELECT affiliateid,name".
			" FROM ".$phpAds_config['tbl_affiliates'].
			" WHERE agencyid=".phpAds_getUserID();
	}
	elseif (phpAds_isUser(phpAds_Client))
	{
		$query =
			"SELECT affiliateid,name".
			" FROM ".$phpAds_config['tbl_affiliates'].
			" WHERE affiliateid=".phpAds_getUserID();
	}
	$res = phpAds_dbQuery($query);
	
	while ($row = phpAds_dbFetchArray($res))
		$affiliateArray[$row['affiliateid']] = phpAds_buildAffiliateName ($row['affiliateid'], $row['name']);
	
	return ($affiliateArray);
}
/*********************************************************/
/* Main code                                             */
/*********************************************************/

$tabindex = 1;

echo "<table border='0' width='100%' cellpadding='0' cellspacing='0'>";	
echo "<tr><td height='25' colspan='2'><b>".$strSelectReport."</b></td></tr>";
//echo "<tr height='1'><td colspan='2' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%'></td></tr>";

echo "<form name='report_selection' action='report-index.php'>";
echo "<tr><td height='35' colspan='2'>";
echo "<select name='selection' onChange='this.form.submit();' accesskey='".$keyList."' tabindex='".($tabindex++)."'>";

$plugins = phpAds_ReportPluginList ("report-plugins/");

if (!isset($selection) || $selection == '')
{
	reset($plugins);
	$selection = key($plugins);
}

for (reset($plugins);$key=key($plugins);next($plugins))
{
	echo "<option value='".$key."' ".($selection==$key?"selected":"").">".$plugins[$key]['plugin-name']."</option>";
}

echo "</select>";
echo "&nbsp;<a href='javascript:document.report_selection.submit();'><img src='images/".$phpAds_TextDirection."/go_blue.gif' border='0'></a>";
echo "</td></tr>";
echo "</form>";
echo "</table>";

phpAds_ShowBreak();
echo "<br><br>";

$plugin = $plugins[$selection];
echo "<table border='0' width='100%' cellpadding='0' cellspacing='0'>";	
echo "<tr><td height='25' colspan='3'>";

if ($plugin['plugin-export'] == 'csv')
	echo "<img src='images/excel.gif' align='absmiddle'>&nbsp;";

echo "<b>".$plugin['plugin-name']."</b></td></tr>";
echo "<tr height='1'><td colspan='3' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%'></td></tr>";

echo "<tr><td height='10' colspan='3'>&nbsp;</td></tr>";
echo "<tr><td width='30'>&nbsp;</td>";
echo "<td height='25' colspan='2'>";
echo nl2br($plugin['plugin-description']);
echo "</td></tr>";
echo "<tr><td height='10' colspan='3'>&nbsp;</td></tr>";

if ($fields = $plugin['plugin-import'])
{
	echo "<tr><td width='30'><img src='images/spacer.gif' height='1' width='100%'></td>";
	echo "<td colspan='2'><img src='images/break-l.gif' height='1' width='200' vspace='6'></td></tr>";
	
	echo "<form action='report-execute.php' method='get'>";
	
	for (reset($fields);$key=key($fields);next($fields))
	{
		// Text field
		if ($fields[$key]['type'] == 'edit')
		{
			echo "<tr><td width='30'>&nbsp;</td>";
			echo "<td width='200'>".$fields[$key]['title']."</td>";
			echo "<td width='370'><input type='text' name='".$key."' size='";
			echo isset($fields[$key]['size']) ? $fields[$key]['size'] : "";
			echo "' value='";
			echo isset($fields[$key]['default']) ? $fields[$key]['default'] : "";
			echo "' tabindex='".($tabindex++)."'></td>";
			echo "</tr>";
			echo "<tr><td width='30'><img src='images/spacer.gif' height='1' width='100%'></td>";
			echo "<td colspan='2'><img src='images/break-l.gif' height='1' width='200' vspace='6'></td></tr>";
		}
		
		// CampaignID-dropdown
		elseif ($fields[$key]['type'] == 'campaignid-dropdown')
		{
			echo "<tr><td width='30'>&nbsp;</td>";
			echo "<td width='200'>".$fields[$key]['title']."</td>";
			echo "<td width='370'><select name='".$key."' tabindex='".($tabindex++)."'>";
			
			$campaignArray = phpAds_getCampaignArray();
			for (reset($campaignArray);$ckey=key($campaignArray);next($campaignArray))
				echo "<option value='".$ckey."'>".$campaignArray[$ckey]."</option>";
			echo "</select></td>";
			echo "</tr>";
			echo "<tr><td width='30'><img src='images/spacer.gif' height='1' width='100%'></td>";
			echo "<td colspan='2'><img src='images/break-l.gif' height='1' width='200' vspace='6'></td></tr>";
		}
		
		// ClientID-dropdown
		elseif ($fields[$key]['type'] == 'clientid-dropdown')
		{
			if (phpAds_isUser(phpAds_Client))
			{
				echo "<input type='hidden' name='".$key."' value='".phpAds_getUserID()."'>";
			}
			else
			{
				echo "<tr><td width='30'>&nbsp;</td>";
				echo "<td width='200'>".$fields[$key]['title']."</td>";
				echo "<td width='370'><select name='".$key."' tabindex='".($tabindex++)."'>";
				
				$clientArray = phpAds_getClientArray();
				for (reset($clientArray);$ckey=key($clientArray);next($clientArray))
					echo "<option value='".$ckey."'>".$clientArray[$ckey]."</option>";
				echo "</select></td>";
				echo "</tr>";
				echo "<tr><td width='30'><img src='images/spacer.gif' height='1' width='100%'></td>";
				echo "<td colspan='2'><img src='images/break-l.gif' height='1' width='200' vspace='6'></td></tr>";
			}
		}
		
		// AffiliateID-dropdown
		elseif ($fields[$key]['type'] == 'affiliateid-dropdown')
		{
			if (phpAds_isUser(phpAds_Affiliate))
			{
				echo "<input type='hidden' name='".$key."' value='".phpAds_getUserID()."'>";
			}
			else
			{
				echo "<tr><td width='30'>&nbsp;</td>";
				echo "<td width='200'>".$fields[$key]['title']."</td>";
				echo "<td width='370'><select name='".$key."' tabindex='".($tabindex++)."'>";
				
				$affiliateArray = phpAds_getAffiliateArray();
				for (reset($affiliateArray);$ckey=key($affiliateArray);next($affiliateArray))
					echo "<option value='".$ckey."'>".$affiliateArray[$ckey]."</option>";
				echo "</select></td>";
				echo "</tr>";
				echo "<tr><td width='30'><img src='images/spacer.gif' height='1' width='100%'></td>";
				echo "<td colspan='2'><img src='images/break-l.gif' height='1' width='200' vspace='6'></td></tr>";
			}
		}
		
		// ZoneID-dropdown
		elseif ($fields[$key]['type'] == 'zoneid-dropdown')
		{
			echo "<tr><td width='30'>&nbsp;</td>";
			echo "<td width='200'>".$fields[$key]['title']."</td>";
			echo "<td width='370'><select name='".$key."' tabindex='".($tabindex++)."'>";
			
			$zoneArray = phpAds_getZoneArray();
			for (reset($zoneArray);$ckey=key($zoneArray);next($zoneArray))
				echo "<option value='".$ckey."'>".$zoneArray[$ckey]."</option>";
			echo "</select></td>";
			echo "</tr>";
			echo "<tr><td width='30'><img src='images/spacer.gif' height='1' width='100%'></td>";
			echo "<td colspan='2'><img src='images/break-l.gif' height='1' width='200' vspace='6'></td></tr>";
		}
	}
	
	echo "<tr><td height='25' colspan='3'>";
	echo "<br><br>";
	echo "<input type='hidden' name='plugin' value='".$selection."'>";
	echo "<input type='submit' value='".$strGenerate."' tabindex='".($tabindex++)."'>";
	echo "</td></tr>";
	
	echo "</form>";
}

echo "</table>";




/*********************************************************/
/* HTML framework                                        */
/*********************************************************/

phpAds_PageFooter();







?>