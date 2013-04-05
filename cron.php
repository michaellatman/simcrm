<?php
/*
Created, designed, and implemented by Michael Latman 
© Copyright 2012 Dugley Labs. This product was made under contract of Kraft estates and should not be distributed to 3rd parties without the prior consent of the creator.
*/

//SELECT * FROM `visitors` WHERE NOW() > DATE_ADD(`last_seen`, INTERVAL 36 MINUTE) LIMIT 0, 30


$conn = new Mongo("mongodb://localhost");
$db = $conn->CRMSim;


$collection = $db->payments;
$ago = new DateTime();
$ago->modify('-1 month');
$criteria = array('paid' => array('$lte' => $ago));
$cursor = $collection->find($criteria);
foreach ($cursor as $doc) { 
	if($doc['account'] != 'CRMSuite'){
		$hcollection = $db->teams;
		$d = $hcollection->findOne(array("_id" => $doc['account']));
		$estates = count($d['estates']);
		$agents = count($d['agents']);
		if($people > 5){
			$payment = $estates*6000;
		}
		else{
			$payment = $estates*($agents*1000);
		}
		$payment =1;
		
		if($doc['amount'] >= $payment){
			$doc['amount'] -= $payment;
			$doc['disabled'] = FALSE;
			$doc['paid'] = new DateTime();
			$collection->save($doc);
			
		}
		else{
			if($doc['disabled'] == FALSE){
				$mcollection = $db->payments;
				$mque = $mcollection->findOne(array('account' => 'CRMSuite',));
				$mque['messages'] .= '&&'.$d['contact_uuid'].'*Your account has been disabled.';
				$doc['disabled'] = TRUE;
				$collection->save($doc);
				$mcollection->save($mque);
			}

		}
	}
}


$collection = $db->visitors;


$tenMinutesAgo = new DateTime();
$tenMinutesAgo->modify('-15 minutes');
	$search = array('updated' => array('$lte' => $tenMinutesAgo));
$cursor = $collection->remove($search);


$collection = $db->users;
$vcollection = $db->visitors;
$oneMinutesAgo = new DateTime();
$oneMinutesAgo->modify('-1 minutes');
	$search = array('updated' => array('$ne' => null, '$lte' => $oneMinutesAgo));
$cursor = $collection->find($search);
foreach ($cursor as $doc) { 
	$search = array('locked_by' => $doc['key']);
	$find = $vcollection->find($search);
	foreach ($find as $doc2) {
		$doc2['locked_by'] = null;
		$vcollection->save($doc2);
	}
	$doc['last_seen'] = $doc['updated'];
	$doc['updated'] = null;
	$collection->save($doc);
}

?>