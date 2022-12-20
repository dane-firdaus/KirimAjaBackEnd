<?php

$host='localhost';
$db = 'public_db_structure';
$user = 'devkirimaja';
$password = "kirimaja!@#$%"; 
try {
	$dsn = "pgsql:host=$host;port=5432;dbname=$db;";

	$pdo = new PDO(
		$dsn,
		$user,
		$password,
		[PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
	);

	if ($pdo) {
		echo "Connected to the $db database successfully!";
	}
} catch (PDOException $e) {
	die($e->getMessage());
}
?>