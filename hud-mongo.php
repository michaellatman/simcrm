<?php
/*
Created, designed, and implemented by Michael Latman 
© Copyright 2012 Dugley Labs. This product was made under contract of Kraft estates and should not be distributed to 3rd parties without the prior consent of the creator.
*/

$conn = new Mongo("mongodb://kraft:awdadw34@flame.mongohq.com:27103/CRMSimLegacy");

$db = $conn->CRMSimLegacy;
//include('sql.php');
//SELECT * FROM `visitors` WHERE NOW() > DATE_ADD(`last_seen`, INTERVAL 36 MINUTE) LIMIT 0, 30


$motd = 'Connected to network. Please note that at some point next week we will be migrating to the new sever scripts. This transition is going to be as seamless as possible. Please stay tuned.';

$headers 	= apache_request_headers();
		$objectgrid 	= $headers["X-SecondLife-Shard"];
		$objectname 	= $headers["X-SecondLife-Object-Name"];
		$objectkey     	= $headers["X-SecondLife-Object-Key"];
		$objectpos 	= $headers["X-SecondLife-Local-Position"];
		$ownerkey     	= $headers["X-SecondLife-Owner-Key"];
		$ownername 	= $headers["X-SecondLife-Owner-Name"];
		$regiondata     = $headers["X-SecondLife-Region"];
		$regiontmp 	= explode ("(",$regiondata); // cut cords off 
		$regionpos 	= explode (")",$regiontmp[1]); //
		$regionname 	= substr($regiontmp[0],0,-1); // cut last space from simname

$bdoc = $db->teams->find();
foreach ($bdoc as $doc) {
	var_dump($doc);
	if(in_array($ownername, $doc['agents']))
		$bdoc = $doc;
}

$pdoc = $db->payments->findOne(array('account' => $bdoc['_id']));



function generate_people(){
global $conn;

$headers 	= apache_request_headers();
		$objectgrid 	= $headers["X-SecondLife-Shard"];
		$objectname 	= $headers["X-SecondLife-Object-Name"];
		$objectkey     	= $headers["X-SecondLife-Object-Key"];
		$objectpos 	= $headers["X-SecondLife-Local-Position"];
		$ownerkey     	= $headers["X-SecondLife-Owner-Key"];
		$ownername 	= $headers["X-SecondLife-Owner-Name"];
		$regiondata     = $headers["X-SecondLife-Region"];
		$regiontmp 	= explode ("(",$regiondata); // cut cords off 
		$regionpos 	= explode (")",$regiontmp[1]); //
		$regionname 	= substr($regiontmp[0],0,-1); // cut last space from simname

//$conn = new Mongo("mongodb://kraft:awdadw34@localhost/Kraft");

$db = $conn->CRMSimLegacy;
global $pdoc;

echo('people|');
	if($pdoc['disabled']==FALSE){
		
	 
		
		
			$db = $conn->CRMSimLegacy;
			$collection = $db->visitors;
			$bdoc = $db->teams->findOne(array('agents' => $ownername));
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
				echo($doc['name'].'//'.$doc['uuid'].'//'.$doc['uuid'].'//'.$doc['locked_by'].'//'.$doc['location'].'&&'.$doc['estate'].'*');					
				}
				}
			}
	}
	else{
		echo('unauthorized|Can\'t connect. Balance unpaid please contact your team leader..');			
	}
}

$method = $_REQUEST['method'];
if($method == "register"){
	//$ownername = $_REQUEST['owner'];
	$db = $conn->CRMSimLegacy;
	$collection = $db->users;
	
	
	$criteria = array(
		'name' => $ownername,
	);
	$doc = $collection->findOne($criteria);
	//$bdoc = $db->teams->findOne(array('agents' => $ownername));
	//$pdoc = $db->payments->findOne(array('account' => $bdoc['_id']));
	//echo $doc->count() . ' document(s) found. <br/>';

	if($bdoc['lead']!=""){
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
	$db = $conn->CRMSimLegacy;
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
				generate_people();
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
			generate_people();
		}
	}
}
else if($method == "land-board"){
	//$db = $conn->CRMSimLegacy;
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
	  	
		generate_people();
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
			}
	}
	//generate_people();
}
else if($method == "sensor-dropped"){
	$estate = $db->estates->findOne(array('groups'=>$_REQUEST['landgroup']));
	if($estate['estate'] != "") $estate['drops']+=1; $db->estates->save($estate);
}
else if($method == "admin-add"){
	$team = $db->teams->findOne(array("agents" => $ownername));
	array_push($team['agents'], trim($_REQUEST['person']));
	$db->teams->save($team);
	echo($_REQUEST['person']);
}
else if($method == "admin-remove"){
	$team = $db->teams->findOne(array("agents" => $ownername));
	$toremove = 'foo';
	unset($team['agents'][array_search(trim($_REQUEST['person']),$team['agents'])]);
	$db->teams->save($team);
	echo($_REQUEST['person']);
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

		$bdoc = $db->teams->findOne(array('agents' => $agentname['name']));
		
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