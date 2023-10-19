<?php
require "Dotenv.php";

class Token {
  const REFRESH_EXP = 60 * 60 * 24;

  public static function auth($payload) {
    $ACCESS_KEY = Dotenv::load("ACCESS_KEY");
    $access_token = self::sign($payload, $ACCESS_KEY);
    setcookie("jwt", $access_token, time() + self::REFRESH_EXP, "/", "", true, true);
  }

  private static function sign($payload, $key) {
    // header
    $header = ["alg" => "HS256", "type" => "JWT"];
    $header_encoded = base64_encode((json_encode($header)));

    // payload
    $payload_meta = [
        "perm" => "adm"
      ];
    $cat_payload = $payload + $payload_meta;
    $payload_encoded = base64_encode(json_encode($cat_payload));

    //signature
    $signature = hash_hmac("SHA256", $header_encoded . $payload_encoded, $key);
    $signature_encoded = base64_encode($signature);

    // return the token
    return $header_encoded . "." . $payload_encoded . "." . $signature_encoded;
  }

  public static function verify($token) {
    $ACCESS_KEY = Dotenv::load("ACCESS_KEY");

    // seperate string
    $token_parts = explode(".", $token);

    // hmac stuff
    $signature = base64_encode(hash_hmac("SHA256", $token_parts[0] . $token_parts[1], $ACCESS_KEY));

    // verify signature
    if ($signature != $token_parts[2]) {
      return false;
    }

    // decode payload
    $payload = json_decode(base64_decode($token_parts[1]), true);

    // refresh cookie
    setcookie("jwt", $token, time() + self::REFRESH_EXP);

    return $payload;
  }
}


