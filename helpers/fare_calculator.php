<?php
/**
 * Desire Travel - Distance Fare Calculator & Pricing Rules
 */

require_once __DIR__ . '/../config/database.php';

function calculateDistanceFare(float $distanceKm, string $busType = 'AC Seater (2x2)'): array {
    global $pdo;

    $tier1Max = 5.0;
    $tier1Fare = 5.0;
    $tier2Max = 15.0;
    $tier2Rate = 2.0;
    $tier3Rate = 1.0;
    $luxuryMultiplier = 1.25;

    // Fetch dynamic rules from database if present
    try {
        $stmt = $pdo->query("SELECT rule_key, value FROM pricing_rules");
        $rules = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        if (!empty($rules)) {
            $tier1Max = (float)($rules['tier_1_max_km'] ?? $tier1Max);
            $tier1Fare = (float)($rules['tier_1_fixed_fare'] ?? $tier1Fare);
            $tier2Max = (float)($rules['tier_2_max_km'] ?? $tier2Max);
            $tier2Rate = (float)($rules['tier_2_rate_per_km'] ?? $tier2Rate);
            $tier3Rate = (float)($rules['tier_3_rate_per_km'] ?? $tier3Rate);
            $luxuryMultiplier = (float)($rules['luxury_multiplier'] ?? $luxuryMultiplier);
        }
    } catch (Exception $e) {}

    $breakdown = [];
    $totalFare = 0.0;

    if ($distanceKm <= 0) {
        return [
            'distance_km' => 0,
            'bus_type' => $busType,
            'breakdown' => [],
            'base_fare' => 0.0,
            'multiplier' => 1.0,
            'total_fare' => 0.0
        ];
    }

    // Tier 1 calculation
    $tier1Km = min($distanceKm, $tier1Max);
    $tier1Amt = $tier1Fare;
    $totalFare += $tier1Amt;
    $breakdown[] = [
        'tier' => 'Tier 1 (First ' . $tier1Max . ' km)',
        'distance' => $tier1Km . ' km',
        'rate' => 'Flat ₹' . number_format($tier1Fare, 2),
        'amount' => $tier1Amt
    ];

    // Tier 2 calculation
    if ($distanceKm > $tier1Max) {
        $tier2Km = min($distanceKm - $tier1Max, $tier2Max - $tier1Max);
        $tier2Amt = $tier2Km * $tier2Rate;
        $totalFare += $tier2Amt;
        $breakdown[] = [
            'tier' => 'Tier 2 (5 to ' . $tier2Max . ' km)',
            'distance' => number_format($tier2Km, 1) . ' km',
            'rate' => '₹' . number_format($tier2Rate, 2) . '/km',
            'amount' => $tier2Amt
        ];
    }

    // Tier 3 calculation
    if ($distanceKm > $tier2Max) {
        $tier3Km = $distanceKm - $tier2Max;
        $tier3Amt = $tier3Km * $tier3Rate;
        $totalFare += $tier3Amt;
        $breakdown[] = [
            'tier' => 'Tier 3 (Beyond ' . $tier2Max . ' km)',
            'distance' => number_format($tier3Km, 1) . ' km',
            'rate' => '₹' . number_format($tier3Rate, 2) . '/km',
            'amount' => $tier3Amt
        ];
    }

    // Bus Type Multiplier
    $mult = 1.0;
    if (stripos($busType, 'Volvo') !== false || stripos($busType, 'Luxury') !== false || stripos($busType, 'AC Sleeper') !== false) {
        $mult = $luxuryMultiplier;
    }

    $finalFare = round($totalFare * $mult, 2);

    return [
        'distance_km' => $distanceKm,
        'bus_type' => $busType,
        'breakdown' => $breakdown,
        'base_fare' => round($totalFare, 2),
        'multiplier' => $mult,
        'total_fare' => $finalFare
    ];
}
