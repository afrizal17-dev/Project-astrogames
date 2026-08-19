<?php
require_once __DIR__ . '/config/database.php';

try {
    dbExecute("ALTER TABLE games MODIFY cover_image TEXT NOT NULL");
    dbExecute("ALTER TABLE games MODIFY banner_image TEXT NOT NULL");
    echo "Migration Success! Columns are now TEXT.";
} catch (Exception $e) {
    echo "Migration Failed: " . $e->getMessage();
}
