<?php // $Revision: 2.7 $

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
phpAds_registerGlobal ('period', 'start', 'limit');


// Security check
phpAds_checkAccess(phpAds_Admin + phpAds_Agency + phpAds_Client);



/*********************************************************/
/* Client interface security                             */
/*********************************************************/

if (phpAds_isUser(phpAds_Client))
{
	$clientid = phpAds_getUserID();
	
	$query = "SELECT b.campaignid as campaignid".
		" FROM ".$phpAds_config['tbl_banners']." AS b".
		",".$phpAds_config['tbl_campaigns']." AS m".
		" WHERE b.campaignid=m.campaignid".
		" AND m.clientid=".$clientid;
	$res = phpAds_dbQuery($query)
		or phpAds_sqlDie();
	
	$row = phpAds_dbFetchArray($res);
	
	if (phpAds_dbNumRows($res) == 0)
	{
		phpAds_PageHeader("1");
		phpAds_Die ($strAccessDenied, $strNotAdmin);
	}
	else
	{
		$campaignid = $row["campaignid"];
	}
}
elseif (phpAds_isUser(phpAds_Agency))
{
	$query = "SELECT b.campaignid as campaignid".
		",m.clientid as clientid".
		" FROM ".$phpAds_config['tbl_banners']." AS b".
		",".$phpAds_config['tbl_campaigns']." AS m".
		",".$phpAds_config['tbl_clients']." AS c".
		" WHERE b.campaignid=m.campaignid".
		" AND m.clientid=c.clientid".
		" AND m.clientid=".$clientid;
	$res = phpAds_dbQuery($query)
		or phpAds_sqlDie();
	
	$row = phpAds_dbFetchArray($res);

	if (phpAds_dbNumRows($res) == 0)
	{
		phpAds_PageHeader("1");
		phpAds_Die ($strAccessDenied, $strNotAdmin);
	}
	else
	{
		$campaignid = $row["campaignid"];
	}
}


/*********************************************************/
/* HTML framework                                        */
/*********************************************************/

if (isset($Session['prefs']['stats-campaign-banners.php']['listorder']))
	$navorder = $Session['prefs']['stats-campaign-banners.php']['listorder'];
else
	$navorder = '';

if (isset($Session['prefs']['stats-campaign-banners.php']['orderdirection']))
	$navdirection = $Session['prefs']['stats-campaign-banners.php']['orderdirection'];
else
	$navdirection = '';


$res = phpAds_dbQuery("
	SELECT
		*
	FROM
		".$phpAds_config['tbl_banners']."
	WHERE
		campaignid = '$campaignid'
	".phpAds_getBannerListOrder($navorder, $navdirection)."
") or phpAds_sqlDie();

while ($row = phpAds_dbFetchArray($res))
{
	phpAds_PageContext (
		phpAds_buildBannerName ($row['bannerid'], $row['description'], $row['alt']),
		"stats-banner-history.php?clientid=".$clientid."&campaignid=".$campaignid."&bannerid=".$row['bannerid'],
		$bannerid == $row['bannerid']
	);
}

if (phpAds_isUser(phpAds_Admin) || phpAds_isUser(phpAds_Agency))
{
	phpAds_PageShortcut($strClientProperties, 'advertiser-edit.php?clientid='.$clientid, 'images/icon-advertiser.gif');
	phpAds_PageShortcut($strCampaignProperties, 'campaign-edit.php?clientid='.$clientid.'&campaignid='.$campaignid, 'images/icon-campaign.gif');
	phpAds_PageShortcut($strBannerProperties, 'banner-edit.php?clientid='.$clientid.'&campaignid='.$campaignid.'&bannerid='.$bannerid, 'images/icon-banner-stored.gif');
	phpAds_PageShortcut($strModifyBannerAcl, 'banner-acl.php?clientid='.$clientid.'&campaignid='.$campaignid.'&bannerid='.$bannerid, 'images/icon-acl.gif');
	
	if (phpAds_isUser(phpAds_Admin)) {
	$extra  = "<br><br><br>";
	$extra .= "<b>$strMaintenance</b><br>";
	$extra .= "<img src='images/break.gif' height='1' width='160' vspace='4'><br>";
	$extra .= "<a href='stats-reset.php?clientid=$clientid&campaignid=$campaignid&bannerid=$bannerid'".phpAds_DelConfirm($strConfirmResetBannerStats).">";
	$extra .= "<img src='images/".$phpAds_TextDirection."/icon-undo.gif' align='absmiddle' border='0'>&nbsp;$strResetStats</a>";
	$extra .= "<br><br>";
	}
	
	phpAds_PageHeader("2.1.2.2.1", $extra);
		echo "<img src='images/icon-advertiser.gif' align='absmiddle'>&nbsp;".phpAds_getParentClientName($campaignid);
		echo "&nbsp;<img src='images/".$phpAds_TextDirection."/caret-rs.gif'>&nbsp;";
		echo "<img src='images/icon-campaign.gif' align='absmiddle'>&nbsp;".phpAds_getCampaignName($campaignid);
		echo "&nbsp;<img src='images/".$phpAds_TextDirection."/caret-rs.gif'>&nbsp;";
		echo "<img src='images/icon-banner-stored.gif' align='absmiddle'>&nbsp;<b>".phpAds_getBannerName($bannerid)."</b><br><br>";
		echo phpAds_buildBannerCode($bannerid)."<br><br><br><br>";
		phpAds_ShowSections(array("2.1.2.2.1", "2.1.2.2.2"));
}
elseif (phpAds_isUser(phpAds_Client))
{
	$sections[] = "1.2.2.1";
	if (phpAds_isAllowed(phpAds_ModifyBanner)) $sections[] = "1.2.2.2";
	$sections[] = "1.2.2.4";
	
	phpAds_PageHeader("1.2.2.1");
		echo "<img src='images/icon-campaign.gif' align='absmiddle'>&nbsp;".phpAds_getCampaignName($campaignid);
		echo "&nbsp;<img src='images/".$phpAds_TextDirection."/caret-rs.gif'>&nbsp;";
		echo "<img src='images/icon-banner-stored.gif' align='absmiddle'>&nbsp;<b>".phpAds_getBannerName($bannerid)."</b><br><br>";
		echo phpAds_buildBannerCode($bannerid)."<br><br><br><br>";
		phpAds_ShowSections($sections);
}



/*********************************************************/
/* Main code                                             */
/*********************************************************/

$lib_history_hourlyurl = "stats-banner-daily.php";

$lib_history_where     = "bannerid = ".$bannerid;
$lib_history_params    = array ('clientid' => $clientid,
								'campaignid' => $campaignid,
								'bannerid' => $bannerid
						 );

include ("lib-history.inc.php");



/*********************************************************/
/* HTML framework                                        */
/*********************************************************/

phpAds_PageFooter();

?>