<?php // $Revision: 2.3 $

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
require ("lib-size.inc.php");


// Security check
phpAds_checkAccess(phpAds_Admin + phpAds_Agency + phpAds_Affiliate);



/*********************************************************/
/* Affiliate interface security                          */
/*********************************************************/

if (phpAds_isUser(phpAds_Affiliate))
{
	$affiliateid = phpAds_getUserID();
}
elseif (phpAds_isUser(phpAds_Agency))
{
	if (isset($affiliateid) && ($affiliateid != ''))
	{
		$query = "SELECT affiliateid".
			" FROM ".$phpAds_config['tbl_affiliates'].
			" WHERE affiliateid=".$affiliateid.
			" AND agencyid=".phpAds_getUserID();

		$res = phpAds_dbQuery($query) or phpAds_sqlDie();
		if (phpAds_dbNumRows($res) == 0)
		{
			phpAds_PageHeader("2");
			phpAds_Die ($strAccessDenied, $strNotAdmin);
		}
	}
}



/*********************************************************/
/* HTML framework                                        */
/*********************************************************/

if (phpAds_isUser(phpAds_Admin) || phpAds_isUser(phpAds_Agency))
{
	$res = phpAds_dbQuery("
		SELECT
			*
		FROM
			".$phpAds_config['tbl_zones']."
		WHERE
			affiliateid = '".$affiliateid."'
	") or phpAds_sqlDie();
	
	while ($row = phpAds_dbFetchArray($res))
	{
		phpAds_PageContext (
			phpAds_buildZoneName ($row['zoneid'], $row['zonename']),
			"stats-zone-linkedbanners.php?affiliateid=".$affiliateid."&zoneid=".$row['zoneid'],
			$zoneid == $row['zoneid']
		);
	}
	
	phpAds_PageShortcut($strAffiliateProperties, 'affiliate-edit.php?affiliateid='.$affiliateid, 'images/icon-affiliate.gif');	
	phpAds_PageShortcut($strZoneProperties, 'zone-edit.php?affiliateid='.$affiliateid.'&zoneid='.$zoneid, 'images/icon-zone.gif');	
	phpAds_PageShortcut($strIncludedBanners, 'zone-include.php?affiliateid='.$affiliateid.'&zoneid='.$zoneid, 'images/icon-zone-linked.gif');	
	
	
	phpAds_PageHeader("2.4.2.2");
		echo "<img src='images/icon-affiliate.gif' align='absmiddle'>&nbsp;".phpAds_getAffiliateName($affiliateid);
		echo "&nbsp;<img src='images/".$phpAds_TextDirection."/caret-rs.gif'>&nbsp;";
		echo "<img src='images/icon-zone.gif' align='absmiddle'>&nbsp;<b>".phpAds_getZoneName($zoneid)."</b><br><br><br>";
		phpAds_ShowSections(array("2.4.2.1", "2.4.2.2"));
}
else
{
	phpAds_PageHeader("1.1.2");
		echo "<img src='images/icon-zone.gif' align='absmiddle'>&nbsp;<b>".phpAds_getZoneName($zoneid)."</b><br><br><br>";
		phpAds_ShowSections(array("1.1.1", "1.1.2"));
}



/*********************************************************/
/* Main code                                             */
/*********************************************************/

$totalviews = 0;
$totalclicks = 0;

// Get the zone information
$res_stats = phpAds_dbQuery("
	SELECT
		what
	FROM 
		".$phpAds_config['tbl_zones']."
	WHERE
		zoneid = '".$zoneid."'
");

if ($row_zone = phpAds_dbFetchArray($res_stats))
{
	$zone = $row_zone;
	
	// Get the adviews/clicks for each banner
	$res_stats = phpAds_dbQuery("
		SELECT
			bannerid,
			SUM(views) AS views,
			sum(clicks) AS clicks
		FROM 
			".$phpAds_config['tbl_adstats']."
		WHERE
			zoneid = '".$zoneid."'
		GROUP BY
			zoneid, bannerid
	");
	
	while ($row_stats = phpAds_dbFetchArray($res_stats))
	{
		$linkedbanners[$row_stats['bannerid']]['bannerid'] = $row_stats['bannerid'];
		$linkedbanners[$row_stats['bannerid']]['clicks'] = $row_stats['clicks'];
		$linkedbanners[$row_stats['bannerid']]['views'] = $row_stats['views'];
		
		$totalclicks += $row_stats['clicks'];
		$totalviews  += $row_stats['views'];
	}
}


if ($totalviews > 0 || $totalclicks > 0)
{
	echo "<br><br>";
	echo "<table border='0' width='100%' cellpadding='0' cellspacing='0'>";	
	
	echo "<tr height='25'>";
	echo '<td height="25"><b>&nbsp;&nbsp;'.$GLOBALS['strName'].'</b></td>';
	echo '<td height="25"><b>'.$GLOBALS['strID'].'</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>';
	echo "<td height='25' align='".$phpAds_TextAlignRight."'><b>".$GLOBALS['strViews']."</b></td>";
	echo "<td height='25' align='".$phpAds_TextAlignRight."'><b>".$GLOBALS['strClicks']."</b></td>";
	echo "<td height='25' align='".$phpAds_TextAlignRight."'><b>".$GLOBALS['strCTRShort']."</b>&nbsp;&nbsp;</td>";
	echo "</tr>";
	
	echo "<tr height='1'><td colspan='5' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%'></td></tr>";
	
	
	$i=0;
	while (list($key,) = each($linkedbanners))
	{
		$linkedbanner = $linkedbanners[$key];
		
		echo "<tr height='25' ".($i%2==0?"bgcolor='#F6F6F6'":"").">";
		
		// Icon & name
		echo "<td height='25'>";
		
		if (ereg ('bannerid:'.$linkedbanner['bannerid'], $zone['what']))
			echo "&nbsp;&nbsp;<img src='images/icon-zone-linked.gif' align='absmiddle'>&nbsp;";
		else
			echo "&nbsp;&nbsp;<img src='images/icon-banner-stored.gif' align='absmiddle'>&nbsp;";
		
		echo "<a href='stats-linkedbanner-history.php?affiliateid=".$affiliateid."&zoneid=".$zoneid."&bannerid=".$linkedbanner['bannerid']."'>".phpAds_getBannerName($linkedbanner['bannerid'], 30, false)."</a>";
		echo "</td>";
		
		echo "<td height='25'>".$linkedbanner['bannerid']."</td>";
		echo "<td height='25' align='".$phpAds_TextAlignRight."'>".phpAds_formatNumber($linkedbanner['views'])."</td>";
		echo "<td height='25' align='".$phpAds_TextAlignRight."'>".phpAds_formatNumber($linkedbanner['clicks'])."</td>";
		echo "<td height='25' align='".$phpAds_TextAlignRight."'>".phpAds_buildCTR($linkedbanner['views'], $linkedbanner['clicks'])."&nbsp;&nbsp;</td>";
		echo "</tr>";
		
		echo "<tr height='1'><td colspan='5' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%'></td></tr>";
		$i++;
	}
	
	// Total
	echo "<tr height='25'><td height='25'>&nbsp;&nbsp;<b>".$strTotal."</b></td>";
	echo "<td height='25'>&nbsp;</td>";
	echo "<td height='25' align='".$phpAds_TextAlignRight."'>".phpAds_formatNumber($totalviews)."</td>";
	echo "<td height='25' align='".$phpAds_TextAlignRight."'>".phpAds_formatNumber($totalclicks)."</td>";
	echo "<td height='25' align='".$phpAds_TextAlignRight."'>".phpAds_buildCTR($totalviews, $totalclicks)."&nbsp;&nbsp;</td>";
	echo "</tr>";
	
	echo "</table>";
	echo "<br><br>";
}
else
{
	echo "<br><div class='errormessage'><img class='errormessage' src='images/info.gif' width='16' height='16' border='0' align='absmiddle'>";
	echo $strNoStats.'</div>';
}



/*********************************************************/
/* HTML framework                                        */
/*********************************************************/

phpAds_PageFooter();

?>