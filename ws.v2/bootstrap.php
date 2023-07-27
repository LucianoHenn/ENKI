<?php

$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

set_exception_handler(function($exception) {
    error_log($exception);
});