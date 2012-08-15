<?php
/**
 *    Copyright 2012 MegaFon
 *
 *   Licensed under the Apache License, Version 2.0 (the "License");
 *   you may not use this file except in compliance with the License.
 *   You may obtain a copy of the License at
 *
 *       http://www.apache.org/licenses/LICENSE-2.0
 *
 *   Unless required by applicable law or agreed to in writing, software
 *   distributed under the License is distributed on an "AS IS" BASIS,
 *   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *   See the License for the specific language governing permissions and
 *    limitations under the License.
 */

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