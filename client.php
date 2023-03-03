<?php

include("server.php");

echo "<ul>"; // Start an unordered list
foreach ($res_id as $row) {
    echo "<li><span>" . $row['libelle'] . "</span> - " . $row['id'] . "</li>"; // Display the value of the 'id' column for each row
    //echo "<li>" . $row['libelle'] . "</li>";
}

echo "</ul>"; // End the unordered list
?>
