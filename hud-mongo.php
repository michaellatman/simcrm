<?php
/*
Created, designed, and implemented by Michael Latman 
© Copyright 2012 Dugley Labs. This product was made under contract of Kraft estates and should not be distributed to 3rd parties without the prior consent of the creator.
*/
MongoCursor::$slaveOkay = true;
if( !function_exists('apache_request_headers') ) {
///
function apache_request_headers() {
  $arh = array();
  $rx_http = '/\AHTTP_/';
  foreach($_SERVER as $key => $val) {
    if( preg_match($rx_http, $key) ) {
      $arh_key = preg_replace($rx_http, '', $key);
      $rx_matches = array();
      // do some nasty string manipulations to restore the original letter case
      // this should work in most cases
      $rx_matches = explode('_', $arh_key);
      if( count($rx_matches) > 0 and strlen($arh_key) > 2 ) {
        foreach($rx_matches as $ak_key => $ak_val) $rx_matches[$ak_key] = ucfirst($ak_val);
        $arh_key = implode('-', $rx_matches);
      }
      $arh[$arh_key] = $val;
    }
  }
  return( $arh );
}
///
}
///
$env = json_decode(file_get_contents("/home/dotcloud/environment.json"), true);
//echo $env['DOTCLOUD_DATA_MONGODB_URL'];
$conn = new Mongo("mongodb://root:dsXfFEVg4d20eSEtCj6o@simcrmsurreal-mrl4214-data-4.azva.dotcloud.net:1335,simcrmsurreal-mrl4214-data-5.azva.dotcloud.net:1229,simcrmsurreal-mrl4214-data-6.azva.dotcloud.net:1348");

$db = $conn->SimCRM;
//include('sql.php');
//SELECT * FROM `visitors` WHERE NOW() > DATE_ADD(`last_seen`, INTERVAL 36 MINUTE) LIMIT 0, 30


$motd = 'Connected to network. Welcome to the new DugleyCloud.';
		
		$headers 		= apache_request_headers();
		//var_dump($headers);
		$objectgrid 	= $headers["X-SECONDLIFE-SHARD"]; 
		$objectname 	= $headers["X-SECONDLIFE-OBJECT-NAME"];
		$objectkey     	= $headers["X-SECONDLIFE-OBJECT-KEY"];
		$ownerkey     	= $headers["X-SECONDLIFE-OWNER-KEY"];
		$objectpos     	= $headers["X-SECONDLIFE-LOCAL-POSITION"];
		$ownername 	= $headers["X-SECONDLIFE-OWNER-NAME"];
		$regiondata     = $headers["X-SECONDLIFE-REGION"];
		$regiontmp 	= explode ("(",$regiondata); 
		$regionpos 	= explode (")",$regiontmp[1]);
		$regionname 	= substr($regiontmp[0],0,-1);

$bdoc = $db->teams->findOne(array('agents' => $ownername));


$pdoc['disabled'] = false;

//echo $ownername;

function generate_people($headers){
MongoCursor::$slaveOkay = true;
global $conn;

		$objectgrid 	= $headers["X-SECONDLIFE-SHARD"]; 
		$objectname 	= $headers["X-SECONDLIFE-OBJECT-NAME"];
		$objectkey     	= $headers["X-SECONDLIFE-OBJECT-KEY"];
		$ownerkey     	= $headers["X-SECONDLIFE-OWNER-KEY"];
		$objectpos     	= $headers["X-SECONDLIFE-LOCAL-POSITION"];
		$ownername 	= $headers["X-SECONDLIFE-OWNER-NAME"];
		$regiondata     = $headers["X-SECONDLIFE-REGION"];
		$regiontmp 	= explode ("(",$regiondata); 
		$regionpos 	= explode (")",$regiontmp[1]);
		$regionname 	= substr($regiontmp[0],0,-1);


//$conn = new Mongo("mongodb://kraft:awdadw34@localhost/Kraft");

$db = $conn->SimCRM;
global $pdoc;

echo('people|');
	if($pdoc['disabled']==FALSE){
		
	 
		
		
			$db = $conn->SimCRM;
			$collection = $db->visitors;
			//$bdoc = $db->teams->findOne(array('agents' => $ownername));
			$bdoc = $db->teams->findOne(array('lead' => "Allyson Breumann"));

			$criteria = array(
				'$or' => array(array('locked_by' => $_REQUEST['ownerkey']),array('locked_by' => null)),
				 'estate' => array('$in' => $bdoc['estates'])
			);
		
			$cursor = $collection->find($criteria);
			$cursor = $cursor->sort(array('locked_by'=>-1));
			//echo $doc->count() . ' document(s) found. <br/>';
			$i=0;
			foreach ($cursor as $doc) {
				$i+=1;
				if($i<=15){
				//List all 	
				if($doc['name'] != ""){
				if($doc['locked_by'] ==null) $doc['locked_by'] = " ";
				$pieces = explode("&&", $doc['location']);
				if($doc['estate'] != '') $doc['estate'] .= '';
				echo($doc['name'].'//'.$doc['uuid'].'// //'.$doc['locked_by'].'//'.$doc['location'].'&&'.$doc['estate'].'*');					
				}
				}
			}
	}
	else{
		echo('unauthorized|Can\'t connect. Balance unpaid please contact your team leader..');			
	}
}

$method = $_REQUEST['method'];
//echo "$method";
if($method == "register"){
	//$ownername = $_REQUEST['owner'];
	$db = $conn->SimCRM;
	$collection = $db->users;
	
	
	$criteria = array(
		'name' => new MongoRegex("/^".$ownername."/i"),
	);
	$doc = $collection->findOne($criteria);
	//$bdoc = $db->teams->findOne(array('agents' => new MongoRegex("/^".$ownername."/i")));
	//$pdoc = $db->payments->findOne(array('account' => $bdoc['_id']));
	//echo $doc->count() . ' document(s) found. <br/>';

	if($bdoc['lead']!=null){
		if(count($doc) == 0){
			$doc['name'] = $ownername;
			//$db->teams->save($bdoc);
		}
		//$collection->update($criteria,array('$set' => array('title' => 'My last post')));
		//Person is registered
		$doc['key'] = $ownerkey;
		$now = new DateTime();
		$doc['updated'] = $now;
		
  		$collection->save($doc);
  		echo('registered|CRMSuite|'.$motd.""); 	
	}
	else{
		echo('unauthorized|Can\'t connect. Unauthorized. '.$ownername);
		var_dump($bdoc);

	}

}
else if($method == "lock"){
	$db = $conn->SimCRM;
	$collection = $db->visitors;
	$mcollection = $db->users;
	$time = new DateTime();
	$search = array('uuid' => $_REQUEST['id']);
	
	$row = $collection->findOne($search);
	if($_REQUEST['id'] != ""){
		if($row['locked_by'] != null){
			if($row['locked_by'] == $ownerkey){
				$row['locked_by'] = null;
				$collection->save($row);
				generate_people($headers);
			}
			else{
				$search = array('key' => $row['locked_by']);
				
				$mrow = $mcollection->findOne($search);
				echo('say|Sorry, this customer is locked by '.$mrow['name']);
			}
		}
		else{
			$row['locked_by'] = $ownerkey;
			$collection->save($row);
			generate_people($headers);
		}
	}
}
else if($method == "land-board"){
	//$db = $conn->SimCRM;
	$team = $db->teams->findOne(array("agents" => $ownername));
	$i;
	$a = $team['agents'];
	$num=count($a);
	for($i=0;$i!=$num+1;$i++){
		$user = $db->users->findOne(array("name" => $a[$i]));
		//$d = $user['photo'];
		//if($d!="")$d='*'.$d;
		echo $user["key"].'|';
	
	}
	
}
else if($method == "get-people"){ 
	$collection = $db->users;
	$criteria = array(
		'name' => $ownername,
	);

	$doc = $collection->findOne($criteria);
	if($doc['name'] != ""){
		$now = new DateTime();
		$doc['updated'] = $now;	
		//$doc['last_seen'] = null;	
	  	$collection->save($doc);
	  	
		generate_people($headers);
	}
	else{
		
	}
}
else if($method == "remove-person"){
	$collection = $db->visitors;
	$mcollection = $db->users;
	$time = new DateTime();
	$search = array('uuid' => $_REQUEST['id']);
	 
	$row = $collection->findOne($search);
 
	if($_REQUEST['id'] != ""){
			if($row['locked_by'] == $ownerkey){
				$collection->remove($row);
				generate_people($headers);
			}
	}
	//generate_people();
}
else if($method == "sensor-dropped"){
	$estate = $db->estates->findOne(array('groups'=>$_REQUEST['landgroup']));
	if($estate['estate'] != "") $estate['drops']+=1; $db->estates->save($estate);
}
else if($method == "admin-add"){
	$team = $db->teams->findOne(array("agents" => trim($ownername)));
	//array_push($team['agents'], trim($_REQUEST['person']));
	$db->teams->update(array("agents" => $ownername),array('$push' => array("agents"=>trim($_REQUEST['person']))));

	echo(trim($_REQUEST['person']));
}
else if($method == "admin-remove"){
	//array_push($team['agents'], trim($_REQUEST['person']));
	$db->teams->update(array("agents" => trim($_REQUEST['person'])),array('$unset' => array('agents.$'=>1)));
	$db->teams->update(array("agents" => null),array('$pull' => array('agents'=>null)));

	echo(trim($_REQUEST['person']));
}
else if($method == "sensor-person"){
	$collection = $db->visitors;
	$estate = $db->estates->findOne(array('groups'=>$_REQUEST['landgroup']));
	
	$time = new DateTime();
	$search = array('uuid' => $_REQUEST['uuid']);
	$row = $collection->findOne($search);
	if($row['uuid'] == ""){
		if($estate['estate']!=""){
			$estate['newleads']+=1; $db->estates->save($estate);
		}
		$collection->update(array("uuid" => $_REQUEST['uuid']), array('name' => $_REQUEST['name'], 'estate' => $estate['estate'], 'location' => $_REQUEST['location'], 'uuid' => $_REQUEST['uuid'],'locked_by' => null, 'updated' => $time), array("upsert" => true));
		echo('say|Welcome, a sales representative should be with you shortly.');
	}
	else{
		$agentname = $db->users->findOne(array('key'=>$row['locked_by']));

		$bdoc = $db->teams->findOne(array('agents' => new MongoRegex("/^".$agentname['name']."/i")));
		
		//$pdoc = $db->payments->findOne(array('account' => $bdoc['lead']));
		//echo('sss'.$bdoc['lead']);
		//echo('say|'. );
		$row['updated'] = $time;
		$row['location'] = $_REQUEST['location'];
		if($row['estate']!=$estate['estate']){ 
			$row['estate'] = $estate['estate'];
			//if($estate['estate'])
			if($bdoc['estates'] != null){ 
				if(in_array($estate['estate'], $bdoc['estates']) == FALSE){ 
					$row['locked_by'] = null;
					$estate['newleads']+=1; $db->estates->save($estate);
				}
			
			}
		}

		$collection->save($row);
		//mysql_query("UPDATE visitors SET location = '".$_REQUEST['location']."', uuid = '".$_REQUEST['uuid']."', last_seen = CURRENT_TIMESTAMP  WHERE uuid = '".$_REQUEST['uuid']."'") or die(mysql_error());
	}
	 
}


$conn->close();
?>