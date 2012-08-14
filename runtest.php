<?php

/**
 * runtest.php
 * 
 * Console version for building. 
 * $Id: runtest.php 1347 2012-08-12 18:35:27Z gsb $
 * @package tests
 * @version $Revision: 1347 $
 * @deprecated since version 1323
 */
ini_set('log_errors', 'on');
ini_set('error_log', 'php_errors.txt');

require_once('php/Sponde.class.php');
//require_once('Stream.class.php');
require_once('TestRunner.class.php');


/* Test settings */
$type = 'parser';
//$type = 'evaluator';
$filePath = 'tests' . "/$type/";
$filename = $filePath . 'cases.json';
$filename = str_replace('\\', '/', $filename);
$baseTestsUri = $filePath;

/**
 * URIResolver
 * 
 * testUriResolver defines a concrete implementation of getting the template text
 * in a specific project. On the server side it can be a local file, an external resource,
 * Databases, etc.
 * 
 * @param string $resourceURI
 * @param string $baseURI
 * @param array $args optional parameters set
 * @return array
 * <ul>
 *      <li>    "uri": "the resulting resource uri by resolving $resourceURI relative to $baseURI"</li>
 *      <li>    "data": "content of the resource as a string"</li>
 * </ul>
 */
function testUriResolver($resourceURI, $baseURI, $args = null) {
	$file = TestRunner::createFileName($resourceURI, $baseURI);
	if (!$file)
		return null;
	$fileName = $file['fname'];
	try {
		$template = file_get_contents($fileName);
		if (!$template)
			throw new Exception('badResorce');
		/* Check if there is value: exception:, 
		 * which should mean the call of the resource failed.
		 */

		$tmpRet = json_decode($template, true);
		if ($tmpRet && isset($tmpRet['key']) && $tmpRet['key'] == ":exception:")
			throw new Exception('badResource');
		if (strpos($template, ':exception:') !== false)
			throw new Exception('badResource');

		/* imitation of processing of additional parameters */
		if (is_array($args) && count($args)) {
			$check = json_decode($template, true);
			if ($check && isset($check['key']) && $check['key'] == ':args:') {
				$template = array();
				$template['key'] = '[';
				foreach ($args as $val)
					$template['key'] .= 'string(' . $val . ')-';
				$template['key'] = rtrim($template['key'], '-') . ']';
				$template = json_encode($template);
			} elseif (strpos($template, ':args:') !== false) {
				$repl = '[';
				foreach ($args as $val)
					$repl .= 'string(' . $val . ')-';
				$repl = rtrim($repl, '-') . ']';
				$template = str_replace(':args:', $repl, $template);
			}
		}
		/*		 * ************************************************ */
	} catch (Exception $e) {
		return null;
	}

	return array('uri' => $fileName, 'data' => $template);
}

/* * ******** implementation **************************************************** */
$tester = new TestRunner();

$tester->clearTest();

$f = fopen($filename, 'rb');
if ($f) {
	$fileList = json_decode(fread($f, filesize($filename)));
	if ($fileList && is_array($fileList)) {
		foreach ($fileList as $fileTest) {
			try {
				$testFileContent = file_get_contents($filePath . $fileTest);
				$testFileContent = str_replace(':baseURI:', $baseTestsUri, $testFileContent);
				$xmlTest = simplexml_load_string($testFileContent);
				print "\r\n" . $fileTest . "\r\n";
				foreach ($xmlTest->suite as $key => $suite) {
					print '--' . strval($suite['name']) . "---\r\n";
					$tester->executeTests($suite);
				}
			} catch (Exception $e) {
				print "\r\nError from PHP: " . $e->getMessage();
			}
		}
		$tester->result();
	}
	else
		print "\r\nFile with tests is empty\r\n";
}
else
	print "\r\nFile with tests " . $filename . " not found\r\n";

?>
