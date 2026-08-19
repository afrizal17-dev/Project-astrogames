<?php
// Override settings to show errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config/database.php';

try {
    echo "Starting edit game logic test...<br>\n";
    $gameId = 1;
    $title = "Cyberpunk 2077 Edit";
    $developer = "CD Projekt Red";
    $price = 699000;
    $discount = 10;
    $rating = 4.5;
    $minRam = 16;
    $minVram = 8;
    $cover = "https://images.unsplash.com/photo-1542751371-adc38448a05e";
    $banner = "https://images.unsplash.com/photo-1542751371-adc38448a05e";

    dbBegin();
    echo "dbBegin done.<br>\n";

    $sql = "UPDATE games SET title=?, developer=?, publisher=?, price=?, discount_percentage=?, rating=?, cover_image=?, banner_image=? WHERE id=?";
    dbExecute($sql, [$title, $developer, $developer, $price, $discount, $rating, $cover, $banner, $gameId]);
    echo "UPDATE games done.<br>\n";

    dbExecute("DELETE FROM game_genres WHERE game_id=?", [$gameId]);
    echo "DELETE game_genres done.<br>\n";

    $genreList = ["RPG", "Action"];
    foreach ($genreList as $gName) {
        $gRow = dbFetchOne("SELECT id FROM genres WHERE name = ?", [$gName]);
        if ($gRow) {
            dbExecute("INSERT INTO game_genres (game_id, genre_id) VALUES (?, ?)", [$gameId, $gRow['id']]);
        }
    }
    echo "INSERT game_genres done.<br>\n";

    $reqCheck = dbFetchOne("SELECT id FROM game_requirements WHERE game_id=?", [$gameId]);
    if ($reqCheck) {
        dbExecute("UPDATE game_requirements SET min_ram_gb=?, min_vram_gb=? WHERE game_id=?", [$minRam, $minVram, $gameId]);
        echo "UPDATE game_requirements done.<br>\n";
    } else {
        dbExecute("INSERT INTO game_requirements (game_id, min_cpu_id, rec_cpu_id, min_ram_gb, rec_ram_gb, min_gpu_id, rec_gpu_id, min_vram_gb, rec_vram_gb, min_storage_gb) VALUES (?, 3, 4, ?, 16, 4, 6, ?, 8, 50)", [$gameId, $minRam, $minVram]);
        echo "INSERT game_requirements done.<br>\n";
    }

    dbCommit();
    echo "dbCommit done. SUCCESS.<br>\n";

} catch (Exception $e) {
    echo "<br><b>EXCEPTION CAUGHT:</b> " . $e->getMessage() . "<br>\n";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    dbRollback();
} catch (Error $e) {
    echo "<br><b>FATAL ERROR CAUGHT:</b> " . $e->getMessage() . "<br>\n";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    dbRollback();
}
