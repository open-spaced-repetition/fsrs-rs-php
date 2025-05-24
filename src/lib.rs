use ext_php_rs::prelude::*;
use fsrs::ComputeParametersInput;
use std::sync::Mutex;

//  Bring the `fsrs` crate into scope so we can use its types.
//  You'd need `fsrs = { path = "../path/to/fsrs" }` in your `Cargo.toml`.

// FSRS 主结构体
#[php_class(name = "fsrs\\FSRS")]
pub struct FSRS(Mutex<fsrs::FSRS>);

#[php_impl(rename_methods = "none")]
impl FSRS {
    pub fn __construct(parameters: Vec<f32>) -> Self {
        Self(fsrs::FSRS::new(Some(&parameters)).unwrap().into())
    }

    pub fn next_states(
        &self,
        current_memory_state: Option<&MemoryState>,
        desired_retention: f32,
        days_elapsed: u32,
    ) -> NextStates {
        NextStates(
            self.0
                .lock()
                .unwrap()
                .next_states(
                    current_memory_state.map(|x| x.0),
                    desired_retention,
                    days_elapsed,
                )
                .unwrap(),
        )
    }

    pub fn compute_parameters(&self, train_set: Vec<&FSRSItem>) -> Vec<f32> {
        self.0
            .lock()
            .unwrap()
            .compute_parameters(ComputeParametersInput {
                train_set: train_set.iter().map(|x| x.0.clone()).collect(),
                progress: None,
                enable_short_term: true,
                num_relearning_steps: None,
            })
            .unwrap_or_default()
    }

    pub fn benchmark(&self, train_set: Vec<&FSRSItem>) -> Vec<f32> {
        self.0.lock().unwrap().benchmark(ComputeParametersInput {
            train_set: train_set.iter().map(|x| x.0.clone()).collect(),
            progress: None,
            enable_short_term: true,
            num_relearning_steps: None,
        })
    }

    pub fn memory_state_from_sm2(
        &self,
        ease_factor: f32,
        interval: f32,
        sm2_retention: f32,
    ) -> MemoryState {
        MemoryState(
            self.0
                .lock()
                .unwrap()
                .memory_state_from_sm2(ease_factor, interval, sm2_retention)
                .unwrap(),
        )
    }

    pub fn memory_state(&self, item: &FSRSItem, starting_state: Option<&MemoryState>) -> MemoryState {
        MemoryState(
            self.0
                .lock()
                .unwrap()
                .memory_state(item.0.clone(), starting_state.map(|x| x.0))
                .unwrap(),
        )
    }
}

// MemoryState 结构体
#[php_class(name = "fsrs\\MemoryState")]
#[derive(Debug, Clone)]
pub struct MemoryState(fsrs::MemoryState);

#[php_impl(rename_methods = "none")]
impl MemoryState {
    pub fn __construct(stability: f32, difficulty: f32) -> Self {
        Self(fsrs::MemoryState {
            stability,
            difficulty,
        })
    }

    pub fn get_stability(&self) -> f32 {
        self.0.stability
    }

    pub fn get_difficulty(&self) -> f32 {
        self.0.difficulty
    }

    pub fn __toString(&self) -> String {
        format!("{:?}", self.0)
    }
}

// NextStates 结构体
#[php_class(name = "fsrs\\NextStates")]
#[derive(Debug, Clone)]
pub struct NextStates(fsrs::NextStates);

#[php_impl(rename_methods = "none")]
impl NextStates {
    pub fn get_hard(&self) -> ItemState {
        ItemState(self.0.hard.clone())
    }

    pub fn get_good(&self) -> ItemState {
        ItemState(self.0.good.clone())
    }

    pub fn get_easy(&self) -> ItemState {
        ItemState(self.0.easy.clone())
    }

    pub fn get_again(&self) -> ItemState {
        ItemState(self.0.again.clone())
    }
}

// ItemState 结构体
#[php_class(name = "fsrs\\ItemState")]
#[derive(Debug, Clone)]
pub struct ItemState(fsrs::ItemState);

#[php_impl(rename_methods = "none")]
impl ItemState {
    pub fn get_memory(&self) -> MemoryState {
        MemoryState(self.0.memory)
    }

    pub fn get_interval(&self) -> f32 {
        self.0.interval
    }

    pub fn __toString(&self) -> String {
        format!("{:?}", self.0)
    }
}

// FSRSItem 结构体
#[php_class(name = "fsrs\\FSRSItem")]
#[derive(Debug, Clone)]
pub struct FSRSItem(fsrs::FSRSItem);

#[php_impl(rename_methods = "none")]
impl FSRSItem {
    pub fn __construct(reviews: Vec<FSRSReview>) -> Self {
        Self(fsrs::FSRSItem {
            reviews: reviews.iter().map(|x| x.0).collect(),
        })
    }

    pub fn get_reviews(&self) -> Vec<FSRSReview> {
        self.0.reviews.iter().map(|x| FSRSReview(*x)).collect()
    }

    pub fn set_reviews(&mut self, reviews: Vec<FSRSReview>) {
        self.0.reviews = reviews.iter().map(|x| x.0).collect()
    }

    pub fn long_term_review_cnt(&self) -> usize {
        self.0
            .reviews
            .iter()
            .filter(|review| review.delta_t > 0)
            .count()
    }

    pub fn __toString(&self) -> String {
        format!("{:?}", self.0)
    }
}

// FSRSReview 结构体
#[php_class(name = "fsrs\\FSRSReview")]
#[derive(Debug, Clone, Copy, ZvalConvert)]
pub struct FSRSReview(fsrs::FSRSReview);

#[php_impl(rename_methods = "none")]
impl FSRSReview {
    pub fn __construct(rating: u32, delta_t: u32) -> Self {
        Self(fsrs::FSRSReview { rating, delta_t })
    }

    pub fn get_rating(&self) -> u32 {
        self.0.rating
    }

    pub fn get_delta_t(&self) -> u32 {
        self.0.delta_t
    }

    pub fn __toString(&self) -> String {
        format!("{:?}", self.0)
    }
}

// SimulationResult 结构体
#[php_class(name = "fsrs\\SimulationResult")]
pub struct SimulationResult(fsrs::SimulationResult);

#[php_impl(rename_methods = "none")]
impl SimulationResult {
    pub fn get_memorized_cnt_per_day(&self) -> Vec<f32> {
        self.0.memorized_cnt_per_day.clone()
    }

    pub fn get_review_cnt_per_day(&self) -> Vec<usize> {
        self.0.review_cnt_per_day.clone()
    }

    pub fn get_learn_cnt_per_day(&self) -> Vec<usize> {
        self.0.learn_cnt_per_day.clone()
    }

    pub fn get_cost_per_day(&self) -> Vec<f32> {
        self.0.cost_per_day.clone()
    }

    pub fn get_correct_cnt_per_day(&self) -> Vec<usize> {
        self.0.correct_cnt_per_day.clone()
    }
}

// SimulatorConfig 结构体（需要创建一个基本的配置）
#[php_class(name = "fsrs\\SimulatorConfig")]
pub struct SimulatorConfig(fsrs::SimulatorConfig);

#[php_impl(rename_methods = "none")]
impl SimulatorConfig {
    pub fn __construct() -> Self {
        Self(fsrs::SimulatorConfig::default())
    }
}

// 全局函数
#[php_function]
pub fn simulate(
    w: Vec<f32>,
    desired_retention: f32,
    config: Option<&SimulatorConfig>,
    seed: Option<u64>,
) -> SimulationResult {
    let default_config = fsrs::SimulatorConfig::default();
    let config = if let Some(c) = config { &c.0 } else { &default_config };
    SimulationResult(fsrs::simulate(config, &w, desired_retention, seed, None).unwrap())
}

#[php_function]
pub fn default_simulator_config() -> SimulatorConfig {
    SimulatorConfig(fsrs::SimulatorConfig::default())
}

// 默认参数常量
#[php_function]
pub fn get_default_parameters() -> Vec<f32> {
    vec![
        0.40255, 1.18385, 3.173, 15.69105, 7.1949, 0.5345, 1.4604, 0.0046, 1.54575, 0.1192,
        1.01925, 1.9395, 0.11, 0.29605, 2.2698, 0.2315, 2.9898, 0.51655, 0.6621,
    ]
}

#[php_module]
pub fn get_module(module: ModuleBuilder) -> ModuleBuilder {
    module
}
