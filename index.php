<?php
/**
 * FlashMind — Sistem Flashcard & Spaced Repetition untuk Belajar Mahasiswa
 * Mata Kuliah : Rekayasa Perangkat Lunak (RPL) - D3 Teknik Komputer 24A1
 * Mahasiswa   : Auriel Argi (NIM: 240104002)
 */

// Logika Algoritma Spaced Repetition SuperMemo-2 (SM-2)
function hitung_sm2($repetition, $ease_factor, $interval, $quality) {
    if ($quality >= 3) {
        if ($repetition == 0) {
            $interval = 1;
        } elseif ($repetition == 1) {
            $interval = 6;
        } else {
            $interval = round($interval * $ease_factor);
        }
        $repetition++;
    } else {
        $repetition = 0;
        $interval = 1;
    }

    // Perhitungan Ease Factor baru
    $ease_factor = $ease_factor + (0.1 - (5 - $quality) * (0.08 + (5 - $quality) * 0.02));
    if ($ease_factor < 1.3) {
        $ease_factor = 1.3;
    }

    $tgl_review_berikutnya = date('d-m-Y', strtotime("+$interval days"));

    return [
        'repetition'   => $repetition,
        'interval'     => $interval,
        'ease_factor'  => round($ease_factor, 2),
        'next_review'  => $tgl_review_berikutnya
    ];
}

// Simulasi jika form dikirim
$hasil = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $q   = intval($_POST['quality'] ?? 4);
    $rep = intval($_POST['repetition'] ?? 0);
    $ef  = floatval($_POST['ease_factor'] ?? 2.5);
    $iv  = intval($_POST['interval'] ?? 0);
    $hasil = hitung_sm2($rep, $ef, $iv, $q);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>FlashMind — Demo Logika Sistem PHP (RPL)</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #0f111a; color: #f1f3f9; padding: 40px; margin: 0; }
        .container { max-width: 750px; margin: 0 auto; background: #171926; border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; padding: 32px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        h1 { color: #6366f1; margin-top: 0; font-size: 1.8rem; }
        .meta-box { background: #212438; padding: 16px; border-radius: 8px; margin-bottom: 24px; font-size: 0.95rem; line-height: 1.6; border-left: 4px solid #6366f1; }
        .form-group { margin-bottom: 16px; }
        label { display: block; margin-bottom: 6px; font-size: 0.85rem; color: #9da4c0; text-transform: uppercase; font-weight: 600; }
        input, select { width: 100%; padding: 10px; background: #151724; border: 1px solid rgba(255,255,255,0.15); border-radius: 6px; color: #fff; font-size: 1rem; box-sizing: border-box; }
        button { background: #6366f1; color: #fff; border: none; padding: 12px 24px; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 1rem; width: 100%; }
        button:hover { background: #818cf8; }
        .result-box { margin-top: 24px; padding: 18px; background: rgba(16, 185, 129, 0.15); border: 1px solid #10b981; border-radius: 8px; }
        .badge { display: inline-block; padding: 4px 10px; background: #6366f1; border-radius: 20px; font-size: 0.8rem; margin-right: 6px; }
    </style>
</head>
<body>
<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h1>🧠 FlashMind: Info Proyek & Logika SM-2</h1>
        <span class="badge">UTS RPL 2026</span>
    </div>

    <div class="meta-box">
        <strong>Informasi Pengembang:</strong><br>
        • Nama: <strong>Auriel Argi</strong> | NIM: <strong>240104002</strong> | Kelas: <strong>D3 Teknik Komputer 24A1</strong><br>
        • Judul Proyek: <strong>Flashcard & Spaced Repetition untuk Belajar Mahasiswa</strong><br>
        • Platform: <strong>PHP 8 & Supabase (PostgreSQL RPC)</strong> | Deploy: <strong>Vercel Cloud</strong>
    </div>

    <h3>⚡ Uji Coba Logika Algoritma SM-2 (Server-Side PHP):</h3>
    <form method="POST">
        <div class="form-group">
            <label>Kualitas Ingatan Mahasiswa (Quality 1-5):</label>
            <select name="quality">
                <option value="1">1 — Lagi / Lupa Sama Sekali (Reset)</option>
                <option value="3">3 — Sulit (Ingat dengan susah payah)</option>
                <option value="4" selected>4 — Baik (Ingat dengan wajar)</option>
                <option value="5">5 — Sangat Mudah (Hafal lancar)</option>
            </select>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px;">
            <div class="form-group">
                <label>Repetisi Ke- :</label>
                <input type="number" name="repetition" value="<?= htmlspecialchars($_POST['repetition'] ?? 0) ?>" min="0">
            </div>
            <div class="form-group">
                <label>Ease Factor :</label>
                <input type="number" step="0.1" name="ease_factor" value="<?= htmlspecialchars($_POST['ease_factor'] ?? 2.5) ?>">
            </div>
            <div class="form-group">
                <label>Interval Saat Ini (Hari) :</label>
                <input type="number" name="interval" value="<?= htmlspecialchars($_POST['interval'] ?? 0) ?>" min="0">
            </div>
        </div>
        <button type="submit">Jalankan Perhitungan SM-2</button>
    </form>

    <?php if ($hasil): ?>
    <div class="result-box">
        <h4 style="margin: 0 0 10px 0; color: #10b981;">✅ Hasil Perhitungan Spaced Repetition:</h4>
        <p style="margin: 4px 0;">• Repetisi Berikutnya: <strong>Ke-<?= $hasil['repetition'] ?></strong></p>
        <p style="margin: 4px 0;">• Interval Pengulangan: <strong><?= $hasil['interval'] ?> hari lagi</strong></p>
        <p style="margin: 4px 0;">• Ease Factor Baru: <strong><?= $hasil['ease_factor'] ?></strong></p>
        <p style="margin: 4px 0;">• Tanggal Review Berikutnya: <strong><?= $hasil['next_review'] ?></strong></p>
    </div>
    <?php endif; ?>
</div>
</body>
</html>