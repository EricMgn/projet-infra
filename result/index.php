<?php

include('../bdd.php');

if (!empty($_COOKIE['token'])) {
    $verif_token = $bdd->prepare('SELECT token FROM users WHERE token = ?');
    $verif_token->execute(array($_COOKIE['token']));
    $token_verif = $verif_token->rowCount();

    $token_check = $bdd->prepare('SELECT * FROM users WHERE username = ?');
    $token_check->execute(array($_COOKIE['username']));
    $token_check = $token_check->fetch();

    if ($token_verif == 1) {

        if ($token_check['token'] == $_COOKIE['token']) {
            print_r("Léo le plus beau <3");
        } else {
            print_r("pas good");
        }
    } else {
        print_r("Le token est pas bon");
    }
} else {
    print_r("tu dois d'abord te connecter mec");
}
