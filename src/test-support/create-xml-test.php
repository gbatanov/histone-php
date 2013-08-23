<?php

/**
 *    Copyright 2012, 2013 MegaFon
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
$WORK_DIR = implode('/', explode('/', str_replace('\\', '/', __DIR__), -2));
/**
 * Directory where will be generated xml-based tests
 */
$TEST_CASES_XML_FOLDER = $WORK_DIR . '/generated/test-cases-xml';
/**
 * Directory  with json-based tests
 */
$TEST_CASES_JSON_FOLDER = realpath($WORK_DIR . '/../histone-acceptance-tests/src/main/acceptance');
if (!is_dir($TEST_CASES_JSON_FOLDER))
	die('Error 1');
/**
 * Files with test list
 */
$EVALUATOR_TEST_LIST = realpath($WORK_DIR . '/src/test-support') . '/evaluator_set.json';
$PARSER_TEST_LIST = realpath($WORK_DIR . '/src/test-support') . '/parser_set.json';

ini_set("pcre.backtrack_limit", "1000000000000"); // !!! default in PHP 100000
ini_set('log_errors', 'on');
ini_set('error_log', $WORK_DIR . '/generated/php_errors.txt');

$feval = fopen($EVALUATOR_TEST_LIST, 'wb');
if (!$feval)
	die('Error 2');
$fparse = fopen($PARSER_TEST_LIST, 'wb');
if (!$fparse)
	die('Error 2');

// Copy resource folder
$resourceDir = $TEST_CASES_JSON_FOLDER . '/' . 'testresources';
$filelist = array();
function getFileList($dir) {
	global $resourceDir, $filelist;

	$entries = scandir($dir);
	foreach ($entries as $entry) {
		if (!in_array($entry, array('.', '..'))) {
			if (is_dir($dir . '/' . $entry))
				getFileList($dir . '/' . $entry);
			else
				$filelist[] =  $dir . '/' . $entry;
		}
	}
}

getFileList($resourceDir);
print_r($filelist);


if ($feval)
	fclose($feval);
if ($fparse)
	fclose($fparse);
?>
