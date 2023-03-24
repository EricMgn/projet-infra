<?php

include('../bdd.php');

if (!empty($_POST['username']) and !empty($_POST['password'])) {

    $username = $_POST['username'];
    $password = trim(htmlspecialchars($_POST['password']));


    $verif_username = $bdd->prepare('SELECT * FROM users WHERE username = ?');
    $verif_username->execute(array($_POST['username']));
    $username_verif = $verif_username->rowCount();


    if ($username_verif['username'] == 0) {


        $password_crypted = password_hash($password, PASSWORD_BCRYPT);

        $insert_user = $bdd->prepare("INSERT INTO users (username, password, token) VALUES (?,?,?)");
        $insert_user->execute(array($username, $password_crypted, '0'));


        print_r("c'est bon t'es inscris");
    } else {
        print_r("username déjà use");
    }
} else {
    print_r("non");
}
