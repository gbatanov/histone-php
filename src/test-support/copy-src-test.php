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
/**
 * Recursive get a list of files in a given directory.
 * The list is constructed with an absolute path.
 * 
 * @global array $filelist
 * @param string $dir
 * @param array $excludeDir Array of excluding directories
 */
function getFileList($dir) {
	global $filelist, $excludeDir;

	$entries = scandir($dir);
	foreach ($entries as $entry) {
		if (!in_array($entry, $excludeDir)) {
			if (is_dir($dir . '/' . $entry))
				getFileList($dir . '/' . $entry);
			else
				$filelist[] = $dir . '/' . $entry;
		}
	}
}

/**
 * Copying the resource file list of a given directory to the new one.
 * 
 * @global array $filelist
 * @param string $resourceDir
 * @param string $targetDir
 */
function copyResourceFileList($resourceDir, $targetDir) {
	global $filelist;

	if (is_array($filelist)) {
		$drive = '';
		foreach ($filelist as $file) {
			$targetFile = str_replace($resourceDir, $targetDir, $file);
			$target = $targetFile = str_replace('\\', '/', $targetFile);
			if (strpos($targetFile, ':') !== false) {
				list($drive, $target) = explode(':', $targetFile);
				$drive.=':';
			}
			// Since the path of the directory may be missing, create them.
			$pathArray = explode('/', $target);
			if (is_array($pathArray)) {
				unset($pathArray[sizeof($pathArray) - 1]);
				$currPath = $drive . $pathArray[0];
				foreach ($pathArray as $path) {
					if (!is_dir($currPath . $path)) {
						mkdir($currPath . $path);
					}
					$currPath .=$path . '/';
				}
			}
			copy($file, $targetFile);
		}
	}
}

/**
 * Copying the test file list of a given directory to the new one.
 * 
 * @global array $filelist
 * @param string $resourceDir
 * @param string $targetDir
 */
function copyTestFileList($resourceDir, $targetDir) {
	global $filelist, $feval, $fparse;

	if (is_array($filelist)) {
		$drive = '';
		$evaluator = array();
		$parser = array();

		foreach ($filelist as $file) {
			$f = file_get_contents($file);
			if (strpos($f, "expectedResult") !== false) {
				$type = "evaluator";
			} elseif (strpos($f, "expectedAST") !== false) {
				$type = "parser";
			}
			else
				$type = "";
			$targetFile = str_replace($resourceDir, $targetDir, $file);
			$target = $targetFile = str_replace('\\', '/', $targetFile);
			if (strpos($targetFile, ':') !== false) {
				list($drive, $target) = explode(':', $targetFile);
				$drive.=':';
			}
			// Since the path of the directory may be missing, create them.
			$pathArray = explode('/', $target);
			if (is_array($pathArray)) {
				if ($type)
//					${$type}[] = preg_replace('/\.json$/', '.xml', $pathArray[sizeof($pathArray) - 1]);
					${$type}[] = $pathArray[sizeof($pathArray) - 1];
				$pathArray[sizeof($pathArray) - 2] = $type;
				$targetFile = $drive . implode('/', $pathArray);
				unset($pathArray[sizeof($pathArray) - 1]);
				$currPath = $drive . $pathArray[0];
				foreach ($pathArray as $path) {
					if (!is_dir($currPath . $path)) {
						mkdir($currPath . $path);
					}
					$currPath .=$path . '/';
				}
			}
			copy($file, $targetFile);
		}
		if (isset($evaluator)) {
			fwrite($feval, json_encode($evaluator));
		}
		if (isset($parser)) {
			fwrite($fparse, json_encode($parser));
		}
	}
}


$WORK_DIR = implode('/', explode('/', str_replace('\\', '/', __DIR__), -2));
/**
 * Directory where will be generated xml-based tests
 */
$TEST_CASES_FOLDER = $WORK_DIR . '/generated/test-cases';
/**
 * Directory  with json-based tests
 */
$TEST_CASES_JSON_FOLDER = str_replace('\\', '/', realpath($WORK_DIR . '/../histone-acceptance-tests/src/main/acceptance'));
if (!is_dir($TEST_CASES_JSON_FOLDER))
	die('Error 1');
/**
 * Files with test list
 */
$EVALUATOR_TEST_LIST = str_replace('\\', '/', realpath($WORK_DIR . '/src/test-support') . '/evaluator_set.json');
$PARSER_TEST_LIST = str_replace('\\', '/', realpath($WORK_DIR . '/src/test-support') . '/parser_set.json');

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
$excludeDir = array('.', '..');
getFileList($resourceDir);
copyResourceFileList($TEST_CASES_JSON_FOLDER, $TEST_CASES_FOLDER);

$filelist = array();
$excludeDir = array_merge($excludeDir, array('testresources', 'synthetic'));
getFileList($TEST_CASES_JSON_FOLDER);
copyTestFileList($TEST_CASES_JSON_FOLDER, $TEST_CASES_FOLDER);

if ($feval)
	fclose($feval);
if ($fparse)
	fclose($fparse);
?>
