<?php
// Copy OUTSIDE public_html as yorunge-config.php. Never commit real credentials.
return [
 'dsn'=>'mysql:host=localhost;dbname=YOUR_DATABASE;charset=utf8mb4',
 'user'=>'YOUR_DATABASE_USER',
 'password'=>'YOUR_DATABASE_PASSWORD',
 'origin'=>'https://YOUR_DOMAIN',
 // Generate: php -r 'echo bin2hex(random_bytes(32)), PHP_EOL;'
 'app_key'=>'REPLACE_WITH_64_RANDOM_HEX_CHARACTERS',
];
