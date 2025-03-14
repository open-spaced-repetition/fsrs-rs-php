use ext_php_rs::prelude::*;
use ext_php_rs::types::{ZStr, Zval};
use ext_php_rs::zend::ExecuteData;

//  Bring the `fsrs` crate into scope so we can use its types.
//  You'd need `fsrs = { path = "../path/to/fsrs" }` in your `Cargo.toml`.
use fsrs;

#[php_class(name = "fsrs\\FSRS")]
#[derive(Debug)]
pub struct FSRS {
    inner: Mutex<fsrs::FSRS>,
}

#[php_impl(rename_methods = "none")]
impl FSRS {
    pub fn __construct(parameters: Vec<f32>) -> Self {
        // Convert f64 to f32 for the Rust library.
        Self {
            inner: Mutex::new(fsrs::FSRS::new(Some(&parameters)).unwrap()),
        }
    }

    pub fn next_states(
        &self,
        current_memory_state: Option<&MemoryState>,
        desired_retention: f64,
        days_elapsed: i64, // Use i64 for PHP integers, which can be larger than u32.
    ) -> PhpResult<NextStates> {
        // Use PhpResult for proper error handling
        let mem_state = current_memory_state.map(|x| x.0.clone());

        let next_states = self
            .inner
            .lock()
            .unwrap()
            .next_states(mem_state, desired_retention as f32, days_elapsed as u32) //Needs explicit type conversion
            .map_err(|e| PhpException::from(format!("FSRS Error: {:?}", e)))?; //  Convert Rust errors to PHP exceptions

        Ok(NextStates(next_states)) // Wrap the result
    }

    pub fn compute_parameters(&self, train_set: Vec<FSRSItem>) -> Vec<f64> {
        let parameters = self
            .inner
            .lock()
            .unwrap()
            .compute_parameters(train_set.iter().map(|x| x.0.clone()).collect(), None, true)
            .unwrap_or_default();

        // Convert the parameters to f64 for the PHP user
        parameters.iter().map(|&x| x as f64).collect()
    }

    pub fn benchmark(&self, train_set: Vec<FSRSItem>) -> Vec<f64> {
        let benchmark_result = self
            .inner
            .lock()
            .unwrap()
            .benchmark(train_set.iter().map(|x| x.0.clone()).collect(), true);

        benchmark_result.iter().map(|&x| x as f64).collect()
    }

    pub fn memory_state_from_sm2(
        &self,
        ease_factor: f64,
        interval: f64,
        sm2_retention: f64,
    ) -> MemoryState {
        MemoryState(
            self.inner
                .lock()
                .unwrap()
                .memory_state_from_sm2(ease_factor as f32, interval as f32, sm2_retention as f32)
                .unwrap(),
        )
    }

    pub fn memory_state(
        &self,
        item: &FSRSItem,
        starting_state: Option<&MemoryState>,
    ) -> MemoryState {
        MemoryState(
            self.inner
                .lock()
                .unwrap()
                .memory_state(item.0.clone(), starting_state.map(|x| x.0.clone()))
                .unwrap(),
        )
    }
}

#[php_class(name = "fsrs\\MemoryState")]
#[derive(Debug, Clone)]
pub struct MemoryState(pub fsrs::MemoryState); // Make the inner field public

#[php_impl(rename_methods = "none")]
impl MemoryState {
    pub fn __construct(stability: f64, difficulty: f64) -> Self {
        Self(fsrs::MemoryState {
            stability: stability as f32,
            difficulty: difficulty as f32,
        })
    }

    pub fn __toString(&self) -> String {
        format!("{:?}", self.0)
    }
}

#[php_class(name = "fsrs\\NextStates")]
#[derive(Debug, Clone)]
pub struct NextStates(fsrs::NextStates);

#[php_impl(rename_methods = "none")]
impl NextStates {
    #[getter]
    pub fn hard(&self) -> ItemState {
        ItemState(self.0.hard.clone())
    }
    #[getter]
    pub fn good(&self) -> ItemState {
        ItemState(self.0.good.clone())
    }
    #[getter]
    pub fn easy(&self) -> ItemState {
        ItemState(self.0.easy.clone())
    }
    #[getter]
    pub fn again(&self) -> ItemState {
        ItemState(self.0.again.clone())
    }
}

#[php_class(name = "fsrs\\ItemState")]
#[derive(Debug, Clone)]
pub struct ItemState(fsrs::ItemState);

#[php_impl]
impl ItemState {
    #[getter]
    pub fn memory(&self) -> MemoryState {
        MemoryState(self.0.memory.clone())
    }
    #[getter]
    pub fn interval(&self) -> f64 {
        self.0.interval as f64
    }

    pub fn __toString(&self) -> String {
        format!("{:?}", self.0)
    }
}

#[php_class(name = "fsrs\\FSRSItem")]
#[derive(Debug, Clone)]
pub struct FSRSItem(pub fsrs::FSRSItem); // Make inner field public

#[php_impl]
impl FSRSItem {
    pub fn __construct(reviews: Vec<FSRSReview>) -> Self {
        Self(fsrs::FSRSItem {
            reviews: reviews.iter().map(|x| x.0.clone()).collect(),
        })
    }

    #[getter]
    pub fn reviews(&self) -> Vec<FSRSReview> {
        self.0
            .reviews
            .iter()
            .map(|x| FSRSReview(x.clone()))
            .collect()
    }

    #[setter]
    pub fn set_reviews(&mut self, other: Vec<FSRSReview>) {
        self.0.reviews = other.iter().map(|x| x.0.clone()).collect()
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

#[php_class(name = "fsrs\\FSRSReview")]
#[derive(Debug, Clone)]
pub struct FSRSReview(pub fsrs::FSRSReview); // Make inner field public

#[php_impl]
impl FSRSReview {
    #[php_constructor]
    pub fn __construct(rating: i64, delta_t: i64) -> Self {
        Self(fsrs::FSRSReview {
            rating: rating as u32, // Needs explicit type conversion
            delta_t: delta_t as u32,
        })
    }

    pub fn __toString(&self) -> String {
        format!("{:?}", self.0)
    }
}

#[php_class(name = "fsrs\\SimulationResult")]
pub struct SimulationResult(fsrs::SimulationResult);

#[php_impl]
impl SimulationResult {
    #[getter]
    pub fn memorized_cnt_per_day(&self) -> Vec<f32> {
        self.0.memorized_cnt_per_day.clone()
    }
    #[getter]
    pub fn review_cnt_per_day(&self) -> Vec<usize> {
        self.0.review_cnt_per_day.clone()
    }
    #[getter]
    pub fn learn_cnt_per_day(&self) -> Vec<usize> {
        self.0.learn_cnt_per_day.clone()
    }
    #[getter]
    pub fn cost_per_day(&self) -> Vec<f32> {
        self.0.cost_per_day.clone()
    }
}

#[php_function]
#[php_signature(w, desired_retention, config, seed)]
pub fn simulate(
    w: Vec<f64>,
    desired_retention: f64,
    config: Option<&SimulatorConfig>,
    seed: Option<i64>,
) -> SimulationResult {
    let default_config = SimulatorConfig::default();
    let config = config.unwrap_or(&default_config);
    let w_f32: Vec<f32> = w.iter().map(|&x| x as f32).collect(); // Convert f64 to f32
    SimulationResult(
        fsrs::simulate(
            &config.0,
            &w_f32,
            desired_retention as f32,
            seed.map(|x| x as u64),
            None,
        )
        .unwrap(),
    )
}

#[php_function]
pub fn default_simulator_config() -> SimulatorConfig {
    SimulatorConfig::default()
}

#[php_class(name = "fsrs\\SimulatorConfig")]
#[derive(Debug, Clone)]
pub struct SimulatorConfig(pub fsrs::SimulatorConfig);

#[php_impl]
impl SimulatorConfig {
    #[php_constructor]
    pub fn __construct() -> Self {
        Self(fsrs::SimulatorConfig::default())
    }

    #[setter]
    pub fn set_max_cost_perday(&mut self, max_cost_perday: f64) {
        self.0.max_cost_perday = max_cost_perday as f32;
    }
    #[setter]
    pub fn set_max_reviews_perday(&mut self, max_reviews_perday: i64) {
        self.0.max_reviews_perday = max_reviews_perday as usize;
    }

    #[setter]
    pub fn set_recall_costs(&mut self, recall_costs: Vec<f64>) {
        self.0.recall_costs = recall_costs.iter().map(|&x| x as f32).collect();
    }

    #[setter]
    pub fn set_forget_costs(&mut self, forget_costs: Vec<f64>) {
        self.0.forget_costs = forget_costs.iter().map(|&x| x as f32).collect();
    }

    #[setter]
    pub fn set_learn_cost(&mut self, learn_cost: f64) {
        self.0.learn_cost = learn_cost as f32;
    }

    #[getter]
    pub fn max_cost_perday(&mut self) -> f64 {
        self.0.max_cost_perday as f64
    }
    #[getter]
    pub fn max_reviews_perday(&mut self) -> i64 {
        self.0.max_reviews_perday as i64
    }

    #[getter]
    pub fn recall_costs(&mut self) -> Vec<f64> {
        self.0.recall_costs.iter().map(|&x| x as f64).collect()
    }

    #[getter]
    pub fn forget_costs(&mut self) -> Vec<f64> {
        self.0.forget_costs.iter().map(|&x| x as f64).collect()
    }

    #[getter]
    pub fn learn_cost(&mut self) -> f64 {
        self.0.learn_cost as f64
    }
}

impl Default for SimulatorConfig {
    fn default() -> Self {
        Self(fsrs::SimulatorConfig::default())
    }
}

// This is the main entry point that PHP will call.
#[php_module]
pub fn get_module(module: ModuleBuilder) -> ModuleBuilder {
    module
        .name("fsrs_rs_php") // Set the module name
        .build()
}
