<?php
require_once __DIR__ . '/config/database.php';
$g = dbFetchOne("SELECT title FROM games WHERE id=1");
echo "Game 1 title: " . $g['title'];
