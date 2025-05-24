<?php

/**
 * PHP optimization example for fsrs-rs-php
 * This example mirrors the Python optimize.py example from fsrs-rs-python
 */

if (!extension_loaded('fsrs-rs-php')) {
    exit("Error: The required PHP extension 'fsrs-rs-php' is not loaded. Please install or enable it to proceed.\n");
}

use fsrs\FSRS;
use fsrs\FSRSItem;
use fsrs\FSRSReview;

function main() {
    // Create review histories for cards
    $reviewHistoriesOfCards = createReviewHistoriesForCards();
    
    // Convert review histories to FSRSItems
    $fsrsItems = [];
    foreach ($reviewHistoriesOfCards as $history) {
        $items = convertToFsrsItem($history);
        $fsrsItems = array_merge($fsrsItems, $items);
    }
    
    echo "Number of FSRS items: " . count($fsrsItems) . "\n";
    
    // Get default parameters
    $defaultParameters = get_default_parameters();
    echo "Default parameters: [" . implode(', ', array_map(function($p) { 
        return number_format($p, 4); 
    }, $defaultParameters)) . "]\n";
    
    // Create an FSRS instance with default parameters
    $fsrs = new FSRS($defaultParameters);
    
    // Optimize the FSRS model using the created items
    echo "Optimizing parameters...\n";
    $optimizedParameters = $fsrs->compute_parameters($fsrsItems);
    echo "Optimized parameters: [" . implode(', ', array_map(function($p) { 
        return number_format($p, 4); 
    }, $optimizedParameters)) . "]\n";
    
    // Run benchmark to evaluate performance
    echo "Running benchmark...\n";
    $benchmarkResults = $fsrs->benchmark($fsrsItems);
    echo "Benchmark results:\n";
    foreach ($benchmarkResults as $key => $value) {
        if (is_float($value)) {
            echo "  $key: " . number_format($value, 4) . "\n";
        } else {
            echo "  $key: $value\n";
        }
    }
}

function createReviewHistoriesForCards(): array {
    /**
     * This array represents a collection of review histories for multiple cards.
     * Each inner array represents the review history of a single card.
     * The structure is as follows:
     * - Outer array: Contains review histories for multiple cards
     * - Inner array: Represents the review history of a single card
     * - Each element is an array: [date_string, rating]
     * - Date: The date of the review (string in Y-m-d format)
     * - Rating: The rating given during the review (int)
     * 
     * The ratings typically follow this scale:
     * 1: Again, 2: Hard, 3: Good, 4: Easy
     * 
     * This sample data includes various review patterns, such as:
     * - Cards with different numbers of reviews
     * - Various intervals between reviews
     * - Different rating patterns (e.g., consistently high, mixed, or improving over time)
     * 
     * The data is then cycled and repeated to create a larger dataset of 100 cards.
     */
    
    $reviewHistories = [
        [
            ['2023-01-01', 3],
            ['2023-01-02', 4],
            ['2023-01-05', 3],
            ['2023-01-15', 4],
            ['2023-02-01', 3],
            ['2023-02-20', 4],
        ],
        [
            ['2023-01-01', 2],
            ['2023-01-02', 3],
            ['2023-01-04', 4],
            ['2023-01-12', 3],
            ['2023-01-28', 4],
            ['2023-02-15', 3],
            ['2023-03-05', 4],
        ],
        [
            ['2023-01-01', 4],
            ['2023-01-08', 4],
            ['2023-01-24', 3],
            ['2023-02-10', 4],
            ['2023-03-01', 3],
        ],
        [
            ['2023-01-01', 1],
            ['2023-01-02', 1],
            ['2023-01-03', 3],
            ['2023-01-06', 4],
            ['2023-01-16', 4],
            ['2023-02-01', 3],
            ['2023-02-20', 4],
        ],
        [
            ['2023-01-01', 3],
            ['2023-01-03', 3],
            ['2023-01-08', 2],
            ['2023-01-10', 4],
            ['2023-01-22', 3],
            ['2023-02-05', 4],
            ['2023-02-25', 3],
        ],
        [
            ['2023-01-01', 4],
            ['2023-01-09', 3],
            ['2023-01-19', 4],
            ['2023-02-05', 3],
            ['2023-02-25', 4],
        ],
        [
            ['2023-01-01', 2],
            ['2023-01-02', 3],
            ['2023-01-05', 4],
            ['2023-01-15', 3],
            ['2023-01-30', 4],
            ['2023-02-15', 3],
            ['2023-03-05', 4],
        ],
        [
            ['2023-01-01', 3],
            ['2023-01-04', 4],
            ['2023-01-14', 4],
            ['2023-02-01', 3],
            ['2023-02-20', 4],
        ],
        [
            ['2023-01-01', 1],
            ['2023-01-01', 3],
            ['2023-01-02', 1],
            ['2023-01-02', 3],
            ['2023-01-03', 3],
            ['2023-01-07', 3],
            ['2023-01-15', 4],
            ['2023-01-31', 3],
            ['2023-02-15', 4],
            ['2023-03-05', 3],
        ],
        [
            ['2023-01-01', 4],
            ['2023-01-10', 3],
            ['2023-01-20', 4],
            ['2023-02-05', 4],
            ['2023-02-25', 3],
            ['2023-03-15', 4],
        ],
        [
            ['2023-01-01', 1],
            ['2023-01-02', 2],
            ['2023-01-03', 3],
            ['2023-01-04', 4],
            ['2023-01-10', 3],
            ['2023-01-20', 4],
            ['2023-02-05', 3],
            ['2023-02-25', 4],
        ],
        [
            ['2023-01-01', 3],
            ['2023-01-05', 4],
            ['2023-01-15', 3],
            ['2023-01-30', 4],
            ['2023-02-15', 3],
            ['2023-03-05', 4],
        ],
        [
            ['2023-01-01', 2],
            ['2023-01-03', 3],
            ['2023-01-07', 4],
            ['2023-01-17', 3],
            ['2023-02-01', 4],
            ['2023-02-20', 3],
            ['2023-03-10', 4],
        ],
        [
            ['2023-01-01', 4],
            ['2023-01-12', 3],
            ['2023-01-25', 4],
            ['2023-02-10', 3],
            ['2023-03-01', 4],
        ],
    ];
    
    // Cycle and repeat to create 100 cards
    $result = [];
    $historyCount = count($reviewHistories);
    for ($i = 0; $i < 100; $i++) {
        $result[] = $reviewHistories[$i % $historyCount];
    }
    
    return $result;
}

function convertToFsrsItem(array $history): array {
    $reviews = [];
    $lastDate = new DateTime($history[0][0]);
    $items = [];
    
    foreach ($history as $entry) {
        $currentDate = new DateTime($entry[0]);
        $rating = $entry[1];
        
        $deltaT = $currentDate->diff($lastDate)->days;
        $reviews[] = new FSRSReview($rating, $deltaT);
        $items[] = new FSRSItem($reviews);
        $lastDate = $currentDate;
    }
    
    // Filter items to only include those with long-term reviews
    return array_filter($items, function($item) {
        return $item->long_term_review_cnt() > 0;
    });
}

if (php_sapi_name() === 'cli') {
    main();
} else {
    echo "This script should be run from the command line.\n";
} 