<?php
/*
Created, designed, and implemented by Michael Latman 
© Copyright 2012 Dugley Labs. This product was made under contract of Surreal estates and should not be distributed to 3rd parties without the prior consent of the creator.
*/

//SELECT * FROM `visitors` WHERE NOW() > DATE_ADD(`last_seen`, INTERVAL 36 MINUTE) LIMIT 0, 30

include('sql.php');


$db = $GLOBALS;
	$headers 		= $db['HTTP_ENV_VARS'];
	$objectgrid 	= $headers["HTTP_X_SECONDLIFE_SHARD"]; 
	$objectname 	= $headers["HTTP_X_SECONDLIFE_OBJECT_NAME"];
	$objectkey     	= $headers["HTTP_X_SECONDLIFE_OBJECT_KEY"];
	$ownerkey     	= $headers["HTTP_X_SECONDLIFE_OWNER_KEY"];
	$objectpos     	= $headers["HTTP_X_SECONDLIFE_LOCAL_POSITION"];
	$ownername 	= $headers["HTTP_X_SECONDLIFE_OWNER_NAME"];
	$regiondata     = $headers["HTTP_X_SECONDLIFE_REGION"];
	$regiontmp 	= explode ("(",$regiondata); 
	$regionpos 	= explode (")",$regiontmp[1]);
	$regionname 	= substr($regiontmp[0],0,-1);

$result = mysql_query("SELECT * FROM visitors WHERE uuid='".$_REQUEST['uuid']."'") or die(mysql_error()); 
$row = mysql_fetch_array($result);
if($row['uuid'] != $_REQUEST['uuid']){
	$sql = mysql_query("INSERT INTO `visitors`(`name`, `location`, `uuid`) VALUES ('".$_REQUEST['name']."', '".$_REQUEST['location']."', '".$_REQUEST['uuid']."')");
		//mysql_fetch_array($sql);
	echo('Welcome, a sales representative should be with you shortly.');
}
else{
	mysql_query("UPDATE visitors SET location = '".$_REQUEST['location']."', uuid = '".$_REQUEST['uuid']."', last_seen = CURRENT_TIMESTAMP  WHERE uuid = '".$_REQUEST['uuid']."'") or die(mysql_error());
}




?>