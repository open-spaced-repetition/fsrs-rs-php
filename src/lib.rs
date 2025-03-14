use std::collections::HashMap;
use std::str::FromStr;

use ext_php_rs::binary::Binary;
use ext_php_rs::convert::FromZval;
use ext_php_rs::exception::PhpException;
use ext_php_rs::flags::DataType;
use ext_php_rs::prelude::*;
use ext_php_rs::types::Zval;

#[php_class(name = "fsrs\\FSRS")]
pub struct FSRS(fsrs::FSRS);

#[php_impl(rename_methods = "none")]
impl FSRS {
    pub fn __construct() -> PhpResult<Self> {
        let instance = fsrs::FSRS::default();
        Ok(Self(instance))
    }
}
