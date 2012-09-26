<?php

require 'Slim/Slim.php';

$app = new Slim();

$app->get('/inv', 'getInv');
$app->get('/invstats', 'getStats');
$app->get('/inv/search/:query', 'findByName');
$app->post('/inv', 'addInv');
$app->put('/inv/:id', 'updateInv');
$app->delete('/inv/:id',	'deleteInv');

$app->run();

function getInv() {
	$sql = "select host,package,status,version FROM inv ORDER BY host,package";
	try {
		$db = getConnection();
		$stmt = $db->query($sql);  
		$objects = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
		echo '{"aaData": ' . json_encode($objects) . '}';
		//echo json_encode($objects);
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}

function getStats() {
	$sql = "select count(concat(package,version)) as count, package, version FROM inv GROUP by concat(package,version) ORDER BY count DESC,package";
	try {
		$db = getConnection();
		$stmt = $db->query($sql);  
		$objects = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
		echo '{"aaData": ' . json_encode($objects) . '}';
		//echo json_encode($objects);
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}

function getInv($id) {
	$sql = "SELECT * FROM wine WHERE id=:id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$wine = $stmt->fetchObject();  
		$db = null;
		echo json_encode($wine); 
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}

function addInv() {
	error_log('addInv\n', 3, '/var/tmp/php.log');
	$request = Slim::getInstance()->request();
	$wine = json_decode($request->getBody());
	$sql = "INSERT INTO wine (name, grapes, country, region, year, description, picture) VALUES (:name, :grapes, :country, :region, :year, :description, :picture)";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("name", $wine->name);
		$stmt->bindParam("grapes", $wine->grapes);
		$stmt->bindParam("country", $wine->country);
		$stmt->bindParam("region", $wine->region);
		$stmt->bindParam("year", $wine->year);
		$stmt->bindParam("description", $wine->description);
		$stmt->bindParam("picture", $wine->picture);
		$stmt->execute();
		$wine->id = $db->lastInsertId();
		$db = null;
		echo json_encode($wine); 
	} catch(PDOException $e) {
		error_log($e->getMessage(), 3, '/var/tmp/php.log');
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}

function updateInv($id) {
	$request = Slim::getInstance()->request();
	$body = $request->getBody();
	$wine = json_decode($body);
	$sql = "UPDATE wine SET name=:name, grapes=:grapes, country=:country, region=:region, year=:year, description=:description, picture=:picture WHERE id=:id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("name", $wine->name);
		$stmt->bindParam("grapes", $wine->grapes);
		$stmt->bindParam("country", $wine->country);
		$stmt->bindParam("region", $wine->region);
		$stmt->bindParam("year", $wine->year);
		$stmt->bindParam("description", $wine->description);
		$stmt->bindParam("picture", $wine->picture);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$db = null;
		echo json_encode($wine); 
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}

function deleteInv($id) {
	$sql = "DELETE FROM wine WHERE id=:id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$db = null;
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}

function findByName($query) {
	$sql = "select count(concat(package,version)) as count, package, version FROM inv WHERE package LIKE :query GROUP by concat(package,version) ORDER BY count DESC,package";
	//	$sql = "SELECT * FROM inv WHERE UPPER(package) LIKE :query ORDER BY package";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$query = "%".$query."%";  
		$stmt->bindParam("query", $query);
		$stmt->execute();
		$objects = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
		echo '{"aaData": ' . json_encode($objects) . '}';
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}

function getConnection() {
	$dbhost="127.0.0.1";
	$dbuser="root";
	$dbpass="password";
	$dbname="database";
	$dbh = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);	
	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	return $dbh;
}

?>