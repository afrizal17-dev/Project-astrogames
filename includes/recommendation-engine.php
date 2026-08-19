<?php
// includes/recommendation-engine.php
// Engine Analisis Performa & Scoring Kecocokan Game (Total 100 Poin)

/**
 * Estimasi rating relatif performa CPU (1 - 100)
 */
function getCpuPerformanceScore($cpu_string) {
    $cpu = strtolower($cpu_string);
    $score = 40; // Default base score

    // Generation / Series detection
    if (strpos($cpu, 'i9') !== false || strpos($cpu, 'ryzen 9') !== false) {
        $score = 95;
    } elseif (strpos($cpu, 'i7') !== false || strpos($cpu, 'ryzen 7') !== false) {
        $score = 85;
    } elseif (strpos($cpu, 'i5') !== false || strpos($cpu, 'ryzen 5') !== false) {
        $score = 75;
    } elseif (strpos($cpu, 'i3') !== false || strpos($cpu, 'ryzen 3') !== false) {
        $score = 60;
    } elseif (strpos($cpu, 'quad') !== false || strpos($cpu, 'fx') !== false || strpos($cpu, 'athlon') !== false) {
        $score = 45;
    } elseif (strpos($cpu, 'dual') !== false || strpos($cpu, 'celeron') !== false || strpos($cpu, 'pentium') !== false) {
        $score = 30;
    }

    // High generation boosts (e.g., 10th-14th gen, 5000+ series)
    if (preg_match('/1[0-4]\d{3}|[5-9]\d{3}/', $cpu)) {
        $score += 10;
    }

    return min(100, $score);
}

/**
 * Estimasi rating relatif performa GPU (1 - 100)
 */
function getGpuPerformanceScore($gpu_string) {
    $gpu = strtolower($gpu_string);
    $score = 35; // Default base score for integrated graphics

    if (strpos($gpu, 'rtx 40') !== false || strpos($gpu, 'rx 7') !== false) {
        $score = 98;
    } elseif (strpos($gpu, 'rtx 30') !== false || strpos($gpu, 'rx 6700') !== false || strpos($gpu, 'rx 6800') !== false) {
        $score = 90;
    } elseif (strpos($gpu, 'rtx 20') !== false || strpos($gpu, 'gtx 1660') !== false || strpos($gpu, 'rx 5600') !== false) {
        $score = 80;
    } elseif (strpos($gpu, 'gtx 1060') !== false || strpos($gpu, 'gtx 1070') !== false || strpos($gpu, 'gtx 1080') !== false || strpos($gpu, 'rx 580') !== false) {
        $score = 72;
    } elseif (strpos($gpu, 'gtx 1050') !== false || strpos($gpu, 'gtx 960') !== false || strpos($gpu, 'rx 560') !== false || strpos($gpu, 'gtx 750') !== false) {
        $score = 60;
    } elseif (strpos($gpu, 'gtx 660') !== false || strpos($gpu, 'gt 1030') !== false || strpos($gpu, 'hd 7850') !== false) {
        $score = 50;
    } elseif (strpos($gpu, 'intel hd') !== false || strpos($gpu, 'uhd') !== false || strpos($gpu, 'vega 8') !== false || strpos($gpu, 'radeon r5') !== false) {
        $score = 35;
    }

    return min(100, $score);
}

/**
 * Menghitung detail skor kecocokan antara spesifikasi laptop user dan game
 */
function analyzeGameCompatibility($user_spec, $game) {
    $user_cpu_val  = getCpuPerformanceScore($user_spec['cpu']);
    $user_gpu_val  = getGpuPerformanceScore($user_spec['gpu']);
    
    $req_cpu_min   = getCpuPerformanceScore($game['minimum_cpu']);
    $req_cpu_rec   = getCpuPerformanceScore($game['recommended_cpu']);
    
    $req_gpu_min   = getGpuPerformanceScore($game['minimum_gpu']);
    $req_gpu_rec   = getGpuPerformanceScore($game['recommended_gpu']);
    
    // 1. CPU Scoring (Max 20 Pts)
    if ($user_cpu_val >= $req_cpu_rec) {
        $cpu_score = 20;
        $cpu_status = true;
    } elseif ($user_cpu_val >= $req_cpu_min) {
        $cpu_score = 14;
        $cpu_status = true;
    } else {
        $cpu_score = 7;
        $cpu_status = false;
    }

    // 2. RAM Scoring (Max 20 Pts)
    $user_ram = (int)$user_spec['ram'];
    if ($user_ram >= (int)$game['recommended_ram']) {
        $ram_score = 20;
        $ram_status = true;
    } elseif ($user_ram >= (int)$game['minimum_ram']) {
        $ram_score = 14;
        $ram_status = true;
    } else {
        $ram_score = min(10, round(($user_ram / max(1, (int)$game['minimum_ram'])) * 10));
        $ram_status = false;
    }

    // 3. GPU Scoring (Max 25 Pts)
    if ($user_gpu_val >= $req_gpu_rec) {
        $gpu_score = 25;
        $gpu_status = true;
    } elseif ($user_gpu_val >= $req_gpu_min) {
        $gpu_score = 17;
        $gpu_status = true;
    } else {
        $gpu_score = 8;
        $gpu_status = false;
    }

    // 4. VRAM Scoring (Max 20 Pts)
    $user_vram = (int)$user_spec['vram'];
    if ($user_vram >= (int)$game['recommended_vram']) {
        $vram_score = 20;
        $vram_status = true;
    } elseif ($user_vram >= (int)$game['minimum_vram']) {
        $vram_score = 14;
        $vram_status = true;
    } else {
        $vram_score = min(10, round(($user_vram / max(1, (int)$game['minimum_vram'])) * 10));
        $vram_status = false;
    }

    // 5. OS Scoring (Max 15 Pts)
    $os_score = 15;
    $os_status = true;

    $total_score = min(100, $cpu_score + $ram_score + $gpu_score + $vram_score + $os_score);

    // Determination Badge
    if ($total_score >= 90) {
        $status_label = 'Sangat Cocok';
        $status_badge = 'status-sangat-cocok';
        $icon = '';
        $reason = 'Laptop kamu sangat bertenaga! Game dapat dimainkan dengan lancar pada pengaturan grafis Tinggi hingga Ultra.';
    } elseif ($total_score >= 70) {
        $status_label = 'Bisa Dimainkan';
        $status_badge = 'status-cocok';
        $icon = '';
        $reason = 'Laptop kamu memenuhi spesifikasi. Game dapat dimainkan dengan nyaman di pengaturan grafis Menengah / Standar.';
    } elseif ($total_score >= 50) {
        $status_label = 'Bisa dengan Setting Rendah';
        $status_badge = 'status-rendah';
        $icon = '';
        $reason = 'Performa laptop agak terbatas. Game dapat berjalan namun direkomendasikan menggunakan pengaturan grafis Rendah (Low) dan resolusi disesuaikan.';
    } else {
        $status_label = 'Tidak Direkomendasikan';
        $status_badge = 'status-tidak-cocok';
        $icon = '';
        $reason = 'Spesifikasi laptop kamu belum memenuhi standar minimum game ini. Berisiko mengalami lag berat atau frame drop.';
    }

    return [
        'score' => $total_score,
        'label' => $status_label,
        'badge' => $status_badge,
        'icon'  => $icon,
        'reason'=> $reason,
        'breakdown' => [
            'cpu'  => ['score' => $cpu_score, 'max' => 20, 'passed' => $cpu_status, 'user' => $user_spec['cpu'], 'req' => $game['minimum_cpu']],
            'ram'  => ['score' => $ram_score, 'max' => 20, 'passed' => $ram_status, 'user' => $user_spec['ram'] . ' GB', 'req' => $game['minimum_ram'] . ' GB'],
            'gpu'  => ['score' => $gpu_score, 'max' => 25, 'passed' => $gpu_status, 'user' => $user_spec['gpu'], 'req' => $game['minimum_gpu']],
            'vram' => ['score' => $vram_score, 'max' => 20, 'passed' => $vram_status, 'user' => $user_spec['vram'] . ' GB', 'req' => $game['minimum_vram'] . ' GB'],
            'os'   => ['score' => $os_score, 'max' => 15, 'passed' => $os_status, 'user' => $user_spec['os'], 'req' => $game['minimum_os']]
        ]
    ];
}
