<?php

// Stubs for fsrs-rs-php

namespace fsrs {
    class SimulationResult {
        public function get_memorized_cnt_per_day(): array {}

        public function get_review_cnt_per_day(): array {}

        public function get_learn_cnt_per_day(): array {}

        public function get_cost_per_day(): array {}

        public function get_correct_cnt_per_day(): array {}
    }

    class FSRSReview {
        public function __construct(int $rating, int $delta_t) {}

        public function get_rating(): int {}

        public function get_delta_t(): int {}

        public function __toString(): string {}
    }

    class ItemState {
        public function get_memory(): \fsrs\MemoryState {}

        public function get_interval(): float {}

        public function __toString(): string {}
    }

    class NextStates {
        public function get_hard(): \fsrs\ItemState {}

        public function get_good(): \fsrs\ItemState {}

        public function get_easy(): \fsrs\ItemState {}

        public function get_again(): \fsrs\ItemState {}
    }

    class MemoryState {
        public function __construct(float $stability, float $difficulty) {}

        public function get_stability(): float {}

        public function get_difficulty(): float {}

        public function __toString(): string {}
    }

    class FSRS {
        public function __construct(array $parameters) {}

        public function next_states(?\fsrs\MemoryState $current_memory_state, float $desired_retention, int $days_elapsed): \fsrs\NextStates {}

        public function compute_parameters(array $train_set): array {}

        public function benchmark(array $train_set): array {}

        public function memory_state_from_sm2(float $ease_factor, float $interval, float $sm2_retention): \fsrs\MemoryState {}

        public function memory_state(\fsrs\FSRSItem $item, ?\fsrs\MemoryState $starting_state): \fsrs\MemoryState {}
    }

    class FSRSItem {
        public function __construct(array $reviews) {}

        public function get_reviews(): array {}

        public function set_reviews(array $reviews) {}

        public function long_term_review_cnt(): int {}

        public function __toString(): string {}
    }

    class SimulatorConfig {
        public function __construct() {}
    }
}

namespace {
    function simulate(array $w, float $desired_retention, ?\fsrs\SimulatorConfig $config, ?int $seed): \fsrs\SimulationResult {}

    function default_simulator_config(): \fsrs\SimulatorConfig {}

    function get_default_parameters(): array {}
}
