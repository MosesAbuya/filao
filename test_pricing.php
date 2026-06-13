<?php
$tour = [
    'price_1_pax' => '1200.00',
    'price_child_1_pax' => null
];

$tiers = [
    1 => '1 Person',
    2 => '2 People',
];
$hasPricing = false;
foreach($tiers as $num => $label) {
    $adultCol = "price_{$num}_pax";
    $childCol = "price_child_{$num}_pax";
    
    // Use array_key_exists just in case? isset returns false for null
    $adultP = isset($tour[$adultCol]) ? $tour[$adultCol] : null;
    $childP = isset($tour[$childCol]) ? $tour[$childCol] : null;
    
    if ((!empty($adultP) && $adultP > 0) || (!empty($childP) && $childP > 0)) {
        $hasPricing = true;
        $aDisp = (!empty($adultP) && $adultP > 0) ? "$" . number_format($adultP) : "-";
        $cDisp = (!empty($childP) && $childP > 0) ? "$" . number_format($childP) : "-";
        echo "<tr><td><strong>{$label}</strong></td><td>{$aDisp}</td><td>{$cDisp}</td></tr>\n";
    }
}
if (!$hasPricing) {
    echo "<tr><td colspan='3'>Please contact us for detailed pricing.</td></tr>\n";
}
