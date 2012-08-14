<?php

/**
 * Bootstrap file for unit-tests
 * 
 * @package tests
 */
ini_set('log_errors', 'on');
ini_set('error_log', 'php_errors.txt');

$WORK_DIR = implode('/', array_slice(explode('/', str_replace('\\', '/', __DIR__)), 0, -2));

require_once $WORK_DIR . '/main/php/Parser.class.php';
require_once $WORK_DIR . '/main/php/CallStack.class.php';
require_once $WORK_DIR . '/main/php/Runtime.class.php';
require_once $WORK_DIR . '/main/php/Sponde.class.php';
require_once $WORK_DIR . '/main/php/Tokenizer.class.php';

$filename = $WORK_DIR . '/main/unittests/parser_modules.json';
?>