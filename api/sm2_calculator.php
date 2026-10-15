<?php
header('Content-Type: application/json');

$quality    = intval($_GET['quality'] ?? 4);
$repetition = intval($_GET['repetition'] ?? 0);
$ease       = floatval($_GET['ease_factor'] ?? 2.5);
$interval   = intval($_GET['interval'] ?? 0);

if ($quality >= 3) {
    if ($repetition == 0) $interval = 1;
    elseif ($repetition == 1) $interval = 6;
    else $interval = round($interval * $ease);
    $repetition++;
} else {
    $repetition = 0;
    $interval = 1;
}

$ease = $ease + (0.1 - (5 - $quality) * (0.08 + (5 - $quality) * 0.02));
if ($ease < 1.3) $ease = 1.3;

echo json_encode([
    'status'       => 'success',
    'repetition'   => $repetition,
    'interval'     => $interval,
    'ease_factor'  => round($ease, 2),
    'next_review'  => date('Y-m-d', strtotime("+$interval days"))
]);
