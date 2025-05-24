<?php

/**
 * PHP scheduling example for fsrs-rs-php
 * This example mirrors the Python schedule.py from fsrs-rs-python
 * Demonstrates how to schedule new and existing cards with proper date handling
 */

if (!extension_loaded('fsrs-rs-php')) {
    exit("Error: The required PHP extension 'fsrs-rs-php' is not loaded. Please install or enable it to proceed.\n");
}

use fsrs\FSRS;
use fsrs\MemoryState;

class Card {
    public DateTime $due;
    public ?MemoryState $memoryState;
    public int $scheduledDays;
    public ?DateTime $lastReview;
    
    public function __construct() {
        $this->due = new DateTime('now', new DateTimeZone('UTC'));
        $this->memoryState = null;
        $this->scheduledDays = 0;
        $this->lastReview = null;
    }
}

function scheduleNewCard(): void {
    echo "Scheduling a new card:\n";
    echo "=====================\n";
    
    // Create a new card
    $card = new Card();
    
    // Set desired retention
    $desiredRetention = 0.9;
    
    // Create a new FSRS model
    $defaultParameters = get_default_parameters();
    $fsrs = new FSRS($defaultParameters);
    
    // Get next states for a new card
    $nextStates = $fsrs->next_states($card->memoryState, $desiredRetention, 0);
    
    // Display the intervals for each rating
    echo sprintf("Again interval: %.1f days\n", $nextStates->get_again()->get_interval());
    echo sprintf("Hard interval: %.1f days\n", $nextStates->get_hard()->get_interval());
    echo sprintf("Good interval: %.1f days\n", $nextStates->get_good()->get_interval());
    echo sprintf("Easy interval: %.1f days\n", $nextStates->get_easy()->get_interval());
    
    // Assume the card was reviewed and the rating was 'good'
    $nextState = $nextStates->get_good();
    $interval = max(1, round($nextState->get_interval()));
    
    // Update the card with the new memory state and interval
    $card->memoryState = $nextState->get_memory();
    $card->scheduledDays = $interval;
    $card->lastReview = new DateTime('now', new DateTimeZone('UTC'));
    $card->due = clone $card->lastReview;
    $card->due->add(new DateInterval("P{$interval}D"));
    
    echo sprintf("Next review due: %s\n", $card->due->format('Y-m-d H:i:s T'));
    echo sprintf("Memory state: %s\n", $card->memoryState);
    echo sprintf("Scheduled days: %d\n", $card->scheduledDays);
}

function scheduleExistingCard(): void {
    echo "\nScheduling an existing card:\n";
    echo "============================\n";
    
    // Create an existing card with memory state and last review date
    $card = new Card();
    $card->due = new DateTime('now', new DateTimeZone('UTC'));
    $card->lastReview = new DateTime('now', new DateTimeZone('UTC'));
    $card->lastReview->sub(new DateInterval('P7D')); // 7 days ago
    $card->memoryState = new MemoryState(7.0, 5.0); // stability=7.0, difficulty=5.0
    $card->scheduledDays = 7;
    
    // Set desired retention
    $desiredRetention = 0.9;
    
    // Create a new FSRS model
    $defaultParameters = get_default_parameters();
    $fsrs = new FSRS($defaultParameters);
    
    // Calculate the elapsed time since the last review
    $now = new DateTime('now', new DateTimeZone('UTC'));
    $elapsedDays = $now->diff($card->lastReview)->days;
    
    echo sprintf("Last review: %s\n", $card->lastReview->format('Y-m-d H:i:s T'));
    echo sprintf("Elapsed days: %d\n", $elapsedDays);
    echo sprintf("Current memory state: %s\n", $card->memoryState);
    
    // Get next states for an existing card
    $nextStates = $fsrs->next_states($card->memoryState, $desiredRetention, $elapsedDays);
    
    // Display the intervals for each rating
    echo sprintf("Again interval: %.1f days\n", $nextStates->get_again()->get_interval());
    echo sprintf("Hard interval: %.1f days\n", $nextStates->get_hard()->get_interval());
    echo sprintf("Good interval: %.1f days\n", $nextStates->get_good()->get_interval());
    echo sprintf("Easy interval: %.1f days\n", $nextStates->get_easy()->get_interval());
    
    // Assume the card was reviewed and the rating was 'again'
    $nextState = $nextStates->get_again();
    $interval = max(1, round($nextState->get_interval()));
    
    // Update the card with the new memory state and interval
    $card->memoryState = $nextState->get_memory();
    $card->scheduledDays = $interval;
    $card->lastReview = new DateTime('now', new DateTimeZone('UTC'));
    $card->due = clone $card->lastReview;
    $card->due->add(new DateInterval("P{$interval}D"));
    
    echo sprintf("Next review due: %s\n", $card->due->format('Y-m-d H:i:s T'));
    echo sprintf("Memory state: %s\n", $card->memoryState);
    echo sprintf("Scheduled days: %d\n", $card->scheduledDays);
}

function main(): void {
    echo "FSRS Scheduling Example\n";
    echo "=======================\n\n";
    
    scheduleNewCard();
    scheduleExistingCard();
}

if (php_sapi_name() === 'cli') {
    main();
} else {
    echo "This script should be run from the command line.\n";
} 