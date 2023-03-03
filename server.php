<?php

include ('bdd.php');    

$id = $bdd->query('SELECT * FROM categorie');
$res_id = $id->fetchAll(PDO::FETCH_ASSOC); // Fetch all rows as an associative array
?>