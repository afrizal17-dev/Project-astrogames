<?php
require_once __DIR__ . '/config/database.php';

try {
    dbBegin();
    echo "dbBegin OK. \n";

    $gameId = 1;
    $title = "Test Title";
    $sql = "UPDATE games SET title=? WHERE id=?";
    dbExecute($sql, [$title, $gameId]);
    echo "dbExecute UPDATE games OK. \n";

    dbExecute("DELETE FROM game_genres WHERE game_id=?", [$gameId]);
    echo "dbExecute DELETE game_genres OK. \n";
    
    dbExecute("UPDATE game_requirements SET min_ram_gb=12, min_vram_gb=6 WHERE game_id=?", [$gameId]);
    echo "dbExecute UPDATE game_requirements OK. \n";

    dbCommit();
    echo "dbCommit OK. \n";

} catch (Exception $e) {
    echo "EXCEPTION CAUGHT: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
    dbRollback();
}
