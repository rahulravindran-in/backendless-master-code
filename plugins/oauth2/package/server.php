<?php

require_once('oauth2-library/src/OAuth2/Autoloader.php');
OAuth2\Autoloader::register();

$hostname = '<hostname>';
$dbname = '<dbname>';
$username = '<username>';
$password = '<password>';
$dsn      = "mysql:dbname=$dbname;host=$hostname";

$storage = new OAuth2\Storage\Pdo(array('dsn' => $dsn, 'username' => $username, 'password' => $password));
$server = new OAuth2\Server($storage);
$server->addGrantType(new OAuth2\GrantType\ClientCredentials($storage));
$server->addGrantType(new OAuth2\GrantType\AuthorizationCode($storage));

?>
