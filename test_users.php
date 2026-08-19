<?php
require 'config/database.php';
$users = dbFetchAll("SELECT * FROM users");
print_r($users);
