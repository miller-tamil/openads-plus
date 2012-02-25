<?php // $Revision: 1.5 $

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
require ("lib-storage.inc.php");
require ("lib-zones.inc.php");
require ("lib-statistics.inc.php");
require ("../libraries/lib-priority.inc.php");


// Register input variables
phpAds_registerGlobal ('returnurl');


// Security check
phpAds_checkAccess(phpAds_Admin + phpAds_Agency);

if (phpAds_isUser(phpAds_Agency))
{
	$query = "SELECT clientid".
		" FROM ".$phpAds_config['tbl_clients'].
		" WHERE clientid=".$clientid.
		" AND agencyid=".phpAds_getUserID();
	$res = phpAds_dbQuery($query) or phpAds_sqlDie();
	if (phpAds_dbNumRows($res) == 0)
	{
		phpAds_PageHeader("2");
		phpAds_Die ($strAccessDenied, $strNotAdmin);
	}
}


/*********************************************************/
/* Main code                                             */
/*********************************************************/

if (isset($clientid) && $clientid != '')
{
	// Loop through each campaign
	$res_campaign = phpAds_dbQuery(
		"SELECT campaignid".
		" FROM ".$phpAds_config['tbl_campaigns'].
		" WHERE clientid=".$clientid
	) or phpAds_sqlDie();
	
	
	while ($row_campaign = phpAds_dbFetchArray($res_campaign))
	{
		$campaignid = $row_campaign['campaignid'];
				
		// Delete Campaign/Tracker links
		$res = phpAds_dbQuery("DELETE FROM ".$phpAds_config['tbl_campaigns_trackers'].
			" WHERE campaignid=".$campaignid
		) or phpAds_sqlDie();
		
		// Delete Conversions Logged to this Campaign
		$res = phpAds_dbQuery("DELETE FROM ".$phpAds_config['tbl_conversionlog'].
			" WHERE campaignid=".$campaignid
		) or phpAds_sqlDie();
		
		// Loop through each banner
		$res_banners = phpAds_dbQuery(
			"SELECT".
			" bannerid".
			",storagetype".
			",filename".
			" FROM ".$phpAds_config['tbl_banners'].
			" WHERE campaignid=".$row_campaign['campaignid']."
			") or phpAds_sqlDie();
		
		while ($row_banners = phpAds_dbFetchArray($res_banners))
		{
			$bannerid = $row_banners['bannerid'];
			
			// Cleanup stored images for each banner
			if (($row_banners['storagetype'] == 'web' || $row_banners['storagetype'] == 'sql') && $row_banners['filename'] != '')
				phpAds_ImageDelete ($row_banners['storagetype'], $row_banners['filename']);
			
			// Delete Banner ACLs
			phpAds_dbQuery(
				"DELETE FROM ".$phpAds_config['tbl_acls'].
				" WHERE bannerid=".$bannerid
			) or phpAds_sqlDie();
			
			// Delete stats for each banner
			phpAds_deleteStatsByBannerID($bannerid);
		}
		
		// Delete Banners
		phpAds_dbQuery(
			"DELETE FROM ".$phpAds_config['tbl_banners'].
			" WHERE campaignid=".$campaignid
		) or phpAds_sqlDie();
	}
	
	// Loop through each tracker
	$res_tracker = phpAds_dbQuery(
		"SELECT trackerid".
		" FROM ".$phpAds_config['tbl_trackers'].
		" WHERE clientid=".$clientid
	) or phpAds_sqlDie();
	
	while ($row_tracker = phpAds_dbFetchArray($res_tracker))
	{
		$trackerid = $row_tracker['trackerid'];
				
		// Delete Campaign/Tracker links
		$res = phpAds_dbQuery("DELETE FROM ".$phpAds_config['tbl_campaigns_trackers'].
			" WHERE trackerid=".$trackerid
		) or phpAds_sqlDie();
		
		// Delete stats for each tracker
		phpAds_deleteStatsByTrackerID($trackerid);
	}
	// Delete Clients
	$res = phpAds_dbQuery(
		"DELETE FROM ".$phpAds_config['tbl_clients'].
		" WHERE clientid=".$clientid
	) or phpAds_sqlDie();
	
	// Delete Campaigns
	$res = phpAds_dbQuery(
		"DELETE FROM ".$phpAds_config['tbl_campaigns'].
		" WHERE clientid=".$clientid
	) or phpAds_sqlDie();

	// Delete Trackers
	$res = phpAds_dbQuery(
		"DELETE FROM ".$phpAds_config['tbl_trackers'].
		" WHERE clientid=".$clientid
	) or phpAds_sqlDie();
}

// Rebuild priorities
phpAds_PriorityCalculate ();


// Rebuild cache
if (!defined('LIBVIEWCACHE_INCLUDED')) 
	include (phpAds_path.'/libraries/deliverycache/cache-'.$phpAds_config['delivery_caching'].'.inc.php');

phpAds_cacheDelete();


if (!isset($returnurl) && $returnurl == '')
	$returnurl = 'advertiser-index.php';

header("Location: ".$returnurl);

?>