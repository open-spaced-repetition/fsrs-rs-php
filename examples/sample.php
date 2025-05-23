<?php
if (!extension_loaded('fsrs-rs-php')) {
    exit("Error: The required PHP extension 'fsrs-rs-php' is not loaded. Please install or enable it to proceed.\n");
}
$op = new \fsrs\FSRS([]);
