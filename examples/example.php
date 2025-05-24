<?php

/**
 * Basic PHP example for fsrs-rs-php
 * This example mirrors the Python example.py from fsrs-rs-python
 * Demonstrates basic card scheduling with FSRS algorithm
 */

if (!extension_loaded('fsrs-rs-php')) {
    exit("Error: The required PHP extension 'fsrs-rs-php' is not loaded. Please install or enable it to proceed.\n");
}

use fsrs\FSRS;

function main() {
    // Set optimal retention rate
    $optimalRetention = 0.75;
    
    // Get default parameters and create FSRS instance
    $defaultParameters = get_default_parameters();
    $fsrs = new FSRS($defaultParameters);
    
    echo "FSRS Basic Example\n";
    echo "==================\n\n";
    
    echo "Using optimal retention: " . ($optimalRetention * 100) . "%\n";
    echo "Default parameters: [" . implode(', ', array_map(function($p) { 
        return number_format($p, 4); 
    }, $defaultParameters)) . "]\n\n";
    
    // Create a completely new card (no memory state)
    echo "Day 1: Creating a new card\n";
    $currentMemoryState = null;
    $day1States = $fsrs->next_states($currentMemoryState, $optimalRetention, 0);
    
    // Rate as 'hard' on first day
    $day1 = $day1States->get_hard();
    echo "Rating: Hard\n";
    echo "Result: " . $day1 . "\n";
    echo "Scheduled for review in " . round($day1->get_interval()) . " days\n\n";
    
    // Now we review the card 2 days later
    echo "Day 3: Reviewing the card (2 days later)\n";
    $day3States = $fsrs->next_states($day1->get_memory(), $optimalRetention, 2);
    
    // Rate as 'good' this time
    $day3 = $day3States->get_good();
    echo "Rating: Good\n";
    echo "Result: " . $day3 . "\n";
    echo "Scheduled for review in " . round($day3->get_interval()) . " days\n\n";
    
    // Let's show all possible next states for day 3
    echo "All possible states for Day 3 review:\n";
    echo "- Again: " . round($day3States->get_again()->get_interval()) . " days\n";
    echo "- Hard:  " . round($day3States->get_hard()->get_interval()) . " days\n";
    echo "- Good:  " . round($day3States->get_good()->get_interval()) . " days\n";
    echo "- Easy:  " . round($day3States->get_easy()->get_interval()) . " days\n";
}

if (php_sapi_name() === 'cli') {
    main();
} else {
    echo "This script should be run from the command line.\n";
} 