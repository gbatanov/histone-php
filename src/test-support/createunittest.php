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
$WORK_DIR = implode('/', explode('/', str_replace('\\', '/', __DIR__), -2));
$TEST_CASES_XML_FOLDER = '/generated/test-cases-xml';

ini_set("pcre.backtrack_limit", "1000000000000"); // !!! default in PHP 100000
ini_set('log_errors', 'on');
ini_set('error_log', $WORK_DIR . '/generated/php_errors.txt');

/* load xml-describe tests for evaluator */
$modes = array('parser', 'evaluator');

foreach ($modes as $mode) {
	$index = 0;
	$ok = false;
	$filename = $WORK_DIR .  '/src/test-support/' . $mode.'_set_1.json';
	$dir =$WORK_DIR .$TEST_CASES_XML_FOLDER.'/'.$mode;
	$f = fopen($filename, 'rb');

	if ($f) {
		$fileList = json_decode(fread($f, filesize($filename)));
		if ($fileList && is_array($fileList)) {
			try {
				$estr = "";
				foreach ($fileList as $fileTest) {
					$xml = simplexml_load_file($dir . '/' . $fileTest);
					foreach ($xml->suite as $suite) {
						foreach ($suite->case as $case) {
							$expected = $exception = $context = null;
							$input = strval($case->input);
							if (isset($case->expected)) {
								$expected = strval($case->expected);
							}
							if (isset($case->exception)) {
								$exception['line'] = strval($case->exception->line);
								$exception['expected'] = strval($case->exception->expected);
								$exception['found'] = strval($case->exception->found);
							}
							if (isset($case->context))
								$context = strval($case->context);
							$estr.= 'array(';
							$estr.='urldecode(\'' . urlencode($input) . '\'),';
							if ($expected) {
								$estr.='urldecode(\'' . urlencode($expected) . '\'),';
							} else {
								$estr.='\'\',';
							}
							if ($exception) {
								$estr.='array(\'line\'=>urldecode(\'' . urlencode($exception['line']) . '\'),';
								$estr.='\'expected\'=>urldecode(\'' . urlencode($exception['expected']) . '\'),';
								$estr.='\'found\'=>urldecode(\'' . urlencode($exception['found']) . '\')),';
							} else {
								$estr.='\'\',';
							}
							if ($mode == 'evaluator') {
								if ($context) {
									$estr.='urldecode(\'' . urlencode($context) . '\'),';
								} else {
									$estr.='\'\',';
								}
							}
							$estr.='),' . '/*' . $index++ . ' - input: ' . $input .
								($expected ? ' | expected: ' . $expected : '') .
								($exception ? ' | exception: ' . print_r($exception, true) : '') .
								($mode == 'evaluator' ? ($context ? ' | context: ' . $context : '') : '') .
								"*/\r\n";
						}
					} //suites
				}
				echo saveGeneratedTest($mode, $estr);
				$ok = true;
			} catch (Exception $e) {
				throw new Exception('badTestFile');
			}
		}
	}
	if (!$ok)
		throw new Exception('badTestFile');
}

function saveGeneratedTest($mode, $estr) {
	global $WORK_DIR;
	$mode = ucfirst(strtolower($mode));
	$filename = $WORK_DIR . '/generated/generated-tests/' . $mode . 'AcceptanceTest.php';
	$result = '';
	if (file_exists($filename)) {
		$origin = file_get_contents($filename);
		$matches = array();
		$count = preg_match('|module' . $mode . '_start(.*)module' . $mode . '_end|Uis', $origin, $matches);
		if ($count && isset($matches[1])) {
			$result = str_replace($matches[1], '*/' . $estr . '/*', $origin);
		}
		if (strcmp($result, $origin) !== 0) {
			$f = fopen($filename, 'wb');
			if ($f) {
				fwrite($f, $result);
				fclose($f);
				return $mode . "Data Provider array created\r\n";
			}
		}
	}
	else
		return ('badTestFile - ' . $filename);
}

?>
