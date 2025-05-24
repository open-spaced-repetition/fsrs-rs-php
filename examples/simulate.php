<?php

/**
 * PHP simulation example for fsrs-rs-php
 * This example mirrors the Python simulate.py from fsrs-rs-python
 * Demonstrates how to run FSRS simulations to analyze learning patterns
 */

if (!extension_loaded('fsrs-rs-php')) {
    exit("Error: The required PHP extension 'fsrs-rs-php' is not loaded. Please install or enable it to proceed.\n");
}

function main(): void {
    echo "FSRS Simulation Example\n";
    echo "=======================\n\n";
    
    // Get default simulator configuration
    $config = default_simulator_config();
    
    // Configure simulation parameters (similar to Python example)
    // Note: The PHP extension may not expose all configuration properties
    // We'll work with what's available
    
    // Get default parameters
    $defaultParameters = get_default_parameters();
    
    // Set desired retention
    $desiredRetention = 0.9;
    
    echo "Running FSRS simulation...\n";
    echo "Desired retention: " . ($desiredRetention * 100) . "%\n";
    echo "Using default parameters: [" . implode(', ', array_map(function($p) { 
        return number_format($p, 4); 
    }, $defaultParameters)) . "]\n\n";
    
    // Run the simulation
    $simulationResult = simulate($defaultParameters, $desiredRetention, $config);
    
    // Get simulation results
    $memorizedCntPerDay = $simulationResult->get_memorized_cnt_per_day();
    $reviewCntPerDay = $simulationResult->get_review_cnt_per_day();
    $learnCntPerDay = $simulationResult->get_learn_cnt_per_day();
    $costPerDay = $simulationResult->get_cost_per_day();
    $correctCntPerDay = $simulationResult->get_correct_cnt_per_day();
    
    // Print results in a table format
    echo "Simulation Results:\n";
    echo "==================\n\n";
    echo "Day\tMemorized\tReview Count\tLearn Count\tCost Per Day\tCorrect Per Day\n";
    echo str_repeat("-", 80) . "\n";
    
    $maxDays = min(count($memorizedCntPerDay), 20); // Show first 20 days
    
    for ($day = 0; $day < $maxDays; $day++) {
        printf(
            "%d\t%.2f\t\t%.2f\t\t%.2f\t\t%.2f\t\t%.2f\n",
            $day,
            $memorizedCntPerDay[$day] ?? 0.0,
            $reviewCntPerDay[$day] ?? 0.0,
            $learnCntPerDay[$day] ?? 0.0,
            $costPerDay[$day] ?? 0.0,
            $correctCntPerDay[$day] ?? 0.0
        );
    }
    
    if (count($memorizedCntPerDay) > 20) {
        echo "... (showing first 20 days out of " . count($memorizedCntPerDay) . " total days)\n";
    }
    
    // Calculate and display summary statistics
    echo "\nSummary Statistics:\n";
    echo "==================\n";
    
    if (!empty($memorizedCntPerDay)) {
        $totalMemorized = end($memorizedCntPerDay);
        $totalReviews = array_sum($reviewCntPerDay);
        $totalLearned = array_sum($learnCntPerDay);
        $totalCost = array_sum($costPerDay);
        $totalCorrect = array_sum($correctCntPerDay);
        $avgDailyCost = $totalCost / count($costPerDay);
        
        echo sprintf("Total cards memorized: %.2f\n", $totalMemorized);
        echo sprintf("Total reviews: %.2f\n", $totalReviews);
        echo sprintf("Total learned: %.2f\n", $totalLearned);
        echo sprintf("Total cost: %.2f\n", $totalCost);
        echo sprintf("Total correct: %.2f\n", $totalCorrect);
        echo sprintf("Average daily cost: %.2f\n", $avgDailyCost);
        
        if ($totalReviews > 0) {
            $efficiency = $totalCorrect / $totalReviews;
            echo sprintf("Review efficiency: %.2f%%\n", $efficiency * 100);
        }
    }
    
    echo "\nSimulation completed successfully!\n";
}

if (php_sapi_name() === 'cli') {
    main();
} else {
    echo "This script should be run from the command line.\n";
} 