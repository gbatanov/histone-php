<?php

/**
 * Bootstrap file for unit-tests
 * 
 * @package tests
 */
$WORK_DIR = implode('/', explode('/', str_replace('\\', '/', __DIR__),-2));

ini_set('log_errors', 'on');
ini_set('error_log', $WORK_DIR . '/target/php_errors.txt');


require_once $WORK_DIR . '/src//main/Parser.class.php';
require_once $WORK_DIR . '/src//main/CallStack.class.php';
require_once $WORK_DIR . '/src//main/Runtime.class.php';
require_once $WORK_DIR . '/src//main/Sponde.class.php';
require_once $WORK_DIR . '/src//main/Tokenizer.class.php';

$filename = $WORK_DIR . '/main/unittests/parser_modules.json';
?>