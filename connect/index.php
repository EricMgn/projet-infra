<?php

include('../bdd.php');

if (!empty($_POST['username']) and !empty($_POST['password'])) {


    $verif_user = $bdd->prepare('SELECT * FROM users WHERE username = ?');
    $verif_user->execute(array($_POST['username']));
    $user = $verif_user->fetch();

    if (password_verify($_POST['password'], $user['password'])) {

        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);

        // Create token payload as a JSON string
        $payload = json_encode([
            'username' => $_POST['username'],
            'exp' => time() + 3600
        ]);

        // Encode Header to Base64Url String
        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));

        // Encode Payload to Base64Url String
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

        // Create Signature Hash
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, uniqid(), true);

        // Encode Signature to Base64Url String
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        // Create JWT
        $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;

        print_r($jwt);

        setcookie("username", $_POST['username'], time() + 3600, "/");
        setcookie("token", $jwt, time() + 3600, "/");

        $update_token = $bdd->prepare("UPDATE users SET token = ? WHERE username = ?");
        $update_token->execute(array($jwt, $_POST['username']));
    } else {
        print_r("password pas bon");
    }


    //print_r(json_decode(base64_decode(str_replace('_', '/', str_replace('-', '+', explode('.', $jwt)[1])))));
}
