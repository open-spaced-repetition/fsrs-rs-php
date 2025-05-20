use ext_php_rs::prelude::*;

//  Bring the `fsrs` crate into scope so we can use its types.
//  You'd need `fsrs = { path = "../path/to/fsrs" }` in your `Cargo.toml`.

#[php_class(name = "fsrs\\FSRS")]
pub struct FSRS(fsrs::FSRS);

#[php_impl(rename_methods = "none")]
impl FSRS {
    pub fn __construct(parameters: Vec<f32>) -> Self {
        Self(fsrs::FSRS::new(Some(&parameters)).unwrap())
    }
}

#[php_module]
pub fn get_module(module: ModuleBuilder) -> ModuleBuilder {
    module
}
