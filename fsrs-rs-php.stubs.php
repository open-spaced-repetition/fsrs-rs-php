<?php

// Stubs for fsrs-rs-php

namespace fsrs {
    class FSRS {
        public function __construct(array $parameters) {}
        public function next_states(?MemoryState $current_memory_state, float $desired_retention, int $days_elapsed): NextStates {}
        public function compute_parameters(array $train_set): array {}
        public function benchmark(array $train_set): array {}
        public function memory_state_from_sm2(float $ease_factor, float $interval, float $sm2_retention): MemoryState {}
        public function memory_state(FSRSItem $item, ?MemoryState $starting_state = null): MemoryState {}
    }

    class MemoryState {
        public function __construct(float $stability, float $difficulty) {}
        public function get_stability(): float {}
        public function get_difficulty(): float {}
        public function __toString(): string {}
    }

    class NextStates {
        public function get_hard(): ItemState {}
        public function get_good(): ItemState {}
        public function get_easy(): ItemState {}
        public function get_again(): ItemState {}
    }

    class ItemState {
        public function get_memory(): MemoryState {}
        public function get_interval(): float {}
        public function __toString(): string {}
    }

    class FSRSItem {
        public function __construct(array $reviews) {}
        public function get_reviews(): array {}
        public function set_reviews(array $reviews): void {}
        public function long_term_review_cnt(): int {}
        public function __toString(): string {}
    }

    class FSRSReview {
        public function __construct(int $rating, int $delta_t) {}
        public function get_rating(): int {}
        public function get_delta_t(): int {}
        public function __toString(): string {}
    }

    class SimulationResult {
        public function get_memorized_cnt_per_day(): array {}
        public function get_review_cnt_per_day(): array {}
        public function get_learn_cnt_per_day(): array {}
        public function get_cost_per_day(): array {}
        public function get_correct_cnt_per_day(): array {}
    }

    class SimulatorConfig {
        public function __construct() {}
    }
}

/**
 * @param array $w
 * @param float $desired_retention
 * @param SimulatorConfig|null $config
 * @param int|null $seed
 * @return SimulationResult
 */
function simulate(array $w, float $desired_retention, ?SimulatorConfig $config = null, ?int $seed = null): SimulationResult {}

/**
 * @return SimulatorConfig
 */
function default_simulator_config(): SimulatorConfig {}

/**
 * @return array
 */
function get_default_parameters(): array {}
