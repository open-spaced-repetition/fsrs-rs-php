# FSRS-RS-PHP

A PHP binding for the FSRS (Free Spaced Repetition Scheduler) Rust implementation.

## Description

This project provides PHP bindings for the Rust implementation of FSRS, allowing PHP applications to utilize the FSRS algorithm for spaced repetition learning.

## Requirements

- PHP 8.1 or higher
- Rust toolchain
- Composer

## Installation

on linux

```bash
extension_dir=$(php -r "echo ini_get('extension_dir');")
sudo mkdir -p $extension_dir
sudo cp target/debug/libfsrs_rs_php.so $extension_dir/libfsrs_rs_php.so
echo "extension=libfsrs_rs_php.so" >> $(php -r "echo php_ini_loaded_file();")
```

## Usage:

check `./examples`
