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
 * Class for runnig tests
 * 
 * @version $Id: TestRunner.class.php 1310 2012-08-07 19:30:38Z gsb $
 */

/**
 * class TestRunner
 * 
 * @package testClasses
 */
class TestRunner {

	private $with_xdebug = false;
	private $varArrays = null;
	private $type = 'console';
	public $_errCount = 0;
	public $_goodCount = 0;

	/**
	 * 
	 * @param string $type
	 */
	public function __construct($type = 'console') {
		$this->type = $type;
	}

	public function executeTests($testCases) {
		$index = 0;
		foreach ($testCases->xpath('descendant::case') as $case) {
			$this->clearTest();

			if ($case->resourceResolver['type'] == "default")
				Sponde::setUriResolver(null);
			else
				Sponde::setUriResolver('testUriResolver');
			$input = (string) $case->input;
			$baseUrl = (isset($case->global['name']) && (strval($case->global['name']) == 'baseURI')) ? strval($case->global['value']) : '.';

			$this->varArrays = array();

			if (isset($case->data)) {
				foreach ($case->xpath('data') as $val) {
					$path = (strval($val['url']));
					$file = self::createFileName($path, $baseUrl);
					if ($file['scheme'] == 'file') {
						if (!is_dir('./templates'))
							mkdir('./templates');
						$f = fopen('./templates/' . $file['fname'], 'wb');
						if ($f) {
							fwrite($f, strval($val));
							fclose($f);
						}
					} else {
						$varname = VariableStream::createUniqueName($file['fname']);
						$this->varArrays[] = $varname;
						$GLOBALS[$varname] = strval($val);
					}
				}
			}

			if (isset($case->function)) {
				if (!is_dir('./external'))
					mkdir('./external');
				$f = fopen('./external/external_func.php', 'wb');
				if ($f) {
					/* Open namespace */
					$text = '<?php 	
					 
namespace Outer
{
';
					fwrite($f, $text);
					$res = array();
					if (is_object($case->function)) {
						foreach ($case->function as $func) {
							$res[] = $this->createFunction($f, $func);
						}
					} else {
						$res[] = $this->createFunction($f, $case->function);
					}

					$currentClass = '';
					$text = '';
					$textEnd = '';
					foreach ($res as $value) {
						if ($value[0] != $currentClass) {
							$currentClass = $value[0];
							$text = 'class ' . $currentClass . '{';
							$text .= 'function ' . $value[1] . $value[2];
							$textEnd = '}';
						} else {
							$text .= 'function ' . $value[1] . $value[2];
						}
					}
					$text .='}';

					/* Close namespace */
					$text .= '}' . chr(13) . chr(10) . ' ?>';
					fwrite($f, $text);

					fclose($f);
					if (is_array($res[0]))
						Sponde::registerExternalFunction('./external/external_func.php');
				}
			}

			if (isset($case->exception)) {
				$exception = $case->exception;
			}
			else
				$exception = null;

			if ($expected = $case->expected) {
				if ($expected->attributes()->type == 'json') {
					$expected = json_decode($expected, true);
				}
				else
					$expected = (string) $expected;
			}

			if (isset($case->context)) {
				$context = $case->context;
//if ($expected->attributes()->type == 'json') {
				$context = json_decode($context, true);
//}
			}
			else
				$context = new SpondeUndefined();

			$index++;
			try {
				$result = new Sponde($baseUrl);
				$result->parseString($input);
				if (is_array($expected)) {
					$expected = json_encode($expected);
					$result = json_encode($result->getTree());
				} else {
					$result = $result->process($context);
				}


				if ($expected === $result) {
					$this->_success($index, $input, $result);
				} else {
					if ($exception === null) {
						$this->_error($index, $input, $expected, $result);
					} else {
						$this->_error($index, $input, json_encode($exception), 'NO_EXCEPTION');
					}
				}
			} catch (ParseError $thrownException) {
				if ($this->with_xdebug) {
					if (isset($thrownException->xdebug_message))
						echo '<table>' . $thrownException->xdebug_message, '</table>';
				}
				/* delete debugger messages */
				if (isset($thrownException->xdebug_message))
					unset($thrownException->xdebug_message);
				if ($exception !== null) {
					foreach ($exception->children() as $key => $value) {
						if ((string) $value !== (string) $thrownException->$key) {
							$this->_error($index, $input, json_encode($exception), json_encode($thrownException));
						}
					}
					$exception = json_encode($exception);
					$this->_success($index, $input, $exception);
				} else {
					$this->_error($index, $input, 'NO_EXCEPTION', json_encode($thrownException));
				}
			} catch (SpondeError $spondeError) {
				$this->_error($index, $input, 'NO_EXCEPTION', json_encode($spondeError->getMessage()));
			} catch (Exception $e) {
				$this->_error($index, $input, 'NO_EXCEPTION', json_encode($e->getMessage()));
			}
			$this->clearTest();
		}
	}

	public function createFunction($f, $func) {
		$funcBody = strval($func);
		if (strpos($funcBody, ':exception:') !== false) {
			return false;
		}

		$func_name = trim(strval($func['name']));
		$func_name = preg_replace('/[^_a-zA-Z0-9]/', '_', $func_name);

		$funcReturnType = isset($func['return']) ? strval($func['return']) : '';

		$nodeType = 'Sponde' . (isset($func['node']) ? ucfirst(strtolower(strval($func['node']))) : 'Global');

		if (in_array($func_name, array('include'))) {
			$func_name .= '_external';
		}

		$text = '';

		$text .= '($args=array()){';
		$delim = array(
			'common' => array('', ''),
			'args' => array('[', ']'),
			'target' => array('(', ')'));
		$type = 'common';
		if (strpos($funcBody, ':args:')) {
			list($first, $last) = explode(':args:', $funcBody);
			$type = 'args';
		} elseif (strpos($funcBody, ':target:')) {
			list($first, $last) = explode(':target:', $funcBody);
			$type = 'target';
		}


		if (!isset($last)) {
			if (!isset($first))
				$first = $funcBody;
			if ($funcReturnType == 'string') {
				$text .= '$result = "' . $first . '";' . chr(13) . chr(10);
			} elseif ($funcReturnType == 'number') {
				$text .= '$result = ' . $first . ';' . chr(13) . chr(10);
			} else {
				if (is_numeric($first))
					$text .= '$result = ' . $first . ';' . chr(13) . chr(10);
				else
					$text .= '$result = "' . $first . '";' . chr(13) . chr(10);
			}
			$text .= 'return $result;}' . chr(13) . chr(10);
		}
		else {
			$text .='$sum = "' . $first . $delim[$type][0] . '";' . chr(13) . chr(10);
			if ($type == 'args') {
				$text .= <<<'nowdoc'
$args = func_get_args();
//	print_r($args);
	$target = $args[0];
	$args = $args[1];
		$size = count($args);

			for ($i = 0; $i < $size; $i++)
			{
					if (is_array($args[$i]))
					{
						foreach($args[$i] as $arg)
						{
nowdoc;

				$text .= <<<'nowdoc'
							if (is_string($arg))
								$sum .= 'string('.$arg.')';
							else
								$sum .= 'number('.$arg.')';
nowdoc;
				$text .= <<<'nowdoc'
							$sum .='-';
						}
					}
					else
					{
							if (is_string($args[$i]))
								$sum .= 'string('.$args[$i].')';
							else
								$sum .= 'number('.$args[$i].')';
							$sum .='-';
					}
			}
				$sum = rtrim($sum, '-');

nowdoc;
			} else {
				$text .= <<<'nowdoc'
   
$args = func_get_args();
//	print_r($args);
	$target = $args[0];
	$args = $args[1];
				if (is_string($target))
				{
					$sum .= 'string('.$target.')';
				}
			

nowdoc;
			}
			$text .='$sum .= "' . $delim[$type][1] . $last . '";' . chr(13) . chr(10);
			$text .='return  $sum ;}' . chr(13) . chr(10);
		}


		return array($nodeType, $func_name, $text);
	}

	public function clearTest() {
		/* cleaning of the test variables */
		if (in_array("dummy", stream_get_wrappers())) {
			stream_wrapper_unregister("dummy");
		}
		if (in_array("other", stream_get_wrappers())) {
			stream_wrapper_unregister("other");
		}
		if (is_array($this->varArrays)) {
			foreach ($this->varArrays as $key => $val) {
				if (isset($GLOBALS[$val]))
					unset($GLOBALS[$val]);
			}
		}
		if (file_exists('./external/external_func.php')) {
			unlink('./external/external_func.php');
		}
		clearstatcache();
	}

	public static function createFileName($fname = '', $baseURI = '') {
		$fname = trim($fname);
		$baseURI = trim($baseURI);
		$scheme = self::getCurrentScheme($baseURI);
//		$baseURI = str_replace($scheme . ':', $scheme . '://', $baseURI);
		if (!$fname && !$baseURI)
			return false;
		if (!$fname)
			return $baseURI;
		if ($fname[0] == ':') {
			return false;
		} elseif ($fname[0] == '/') { // absolute path
			$fname = realpath($fname);
		} elseif (strpos($fname, ':') !== false) {
			$last = null;
			list($first, $last) = explode('://', $fname);
			if (isset($last)) { // scheme:path,  if scheme == 'file',  
				$scheme = $first;
				if ($scheme == 'file') {
					$fname = realpath('./templates/') . trim($last, '/');
				} else {
					$res = VariableStream::registerScheme($scheme);
					$fname = $scheme . '://' . $last;
				}
			} else {
				list($first, $last) = explode(':', $fname);
				if (isset($last)) {
// windows system - drive letter
					$fname = realpath($fname);
					$scheme = 'file';
				}
			}
		} else { // relative path
			$fname = /* realpath */($baseURI . $fname);
		}
		return array('fname' => $fname, 'scheme' => $scheme);
	}

	/**
	 * function _error
	 * 
	 * @param number $index
	 * @param string $input
	 * @param string $expected
	 * @param string $result
	 */
	private function _error($index, $input, $expected, $result) {
		++$this->_errCount;
		if ($this->type == 'console') {
			echo '[' . str_pad($index, 4, '0', STR_PAD_LEFT) . '] ';
			echo 'input: ' . $input . "\r\n";
			echo '.......expect: ' . $expected . "\r\n";
			echo '.......result: ' . $result . "\r\n";
		}
		else
			$this->error($index, $input, $expected, $result);
	}

	/**
	 * function _success
	 * 
	 * @param number $index
	 * @param string $input
	 * @param string $output
	 */
	private function _success($index, $input, $output) {
		++$this->_goodCount;
		if ($this->type == 'console') {
			print '[' . str_pad($index, 4, '0', STR_PAD_LEFT) . '] ';
			print $input . ' ------ ' . $output;
			print "\r\n";
		}
		else
			$this->success($index, $input, $output);
	}

	/**
	 *
	 *  
	 * @param string $baseURI
	 * @return string
	 */
	public static function getCurrentScheme($baseURI = '.') {
		$scheme = 'file'; // default
		if (strpos($baseURI, ':') !== false) {
			list($first, $last) = explode(':', $baseURI);
			if ($first) {
				if (strlen($first) > 2)
					return $first;
				else
					return 'file';
			}
		}
		return $scheme;
	}

	/**
	 * function error
	 * 
	 * @param integer $index
	 * @param string $input
	 * @param string $expected
	 * @param string $result
	 */
	private function error($index, $input, $expected, $result) {
		echo '<div style="color: red; font-family: Monospace;">';
		echo '[' . str_pad($index, 4, '0', STR_PAD_LEFT) . '] ';
		echo 'input: ' . $input;
		echo '<br /><span style="visibility: hidden;">.......</span>expect: ' . $expected;
		echo '<br /><span style="visibility: hidden;">.......</span>result: ' . $result;
		echo '</div>';
	}

	/**
	 * function success
	 * 
	 * @param integer $index
	 * @param string $input
	 * @param string $output
	 */
	private function success($index, $input, $output) {
		echo '<div style="color: green; font-family: Monospace;">';
		echo '[' . str_pad($index, 4, '0', STR_PAD_LEFT) . '] ';
		echo $input . ' ------ ' . $output;
		echo '</div>';
	}

	public function result()
	{
		if ($this->type=='console')
		{
			print "Cases:\r\n";
			print 'All: '.($this->_goodCount + $this->_errCount)." cases\r\n"; 
			print 'Good: '.($this->_goodCount)." cases\r\n"; 
			print 'Bad: '.($this->_errCount)." cases\r\n";  
		}
		else
		{
			echo "Cases:<br />";
			echo 'All: '.($this->_goodCount + $this->_errCount).'<br />'; 
			echo 'Good: '.($this->_goodCount).'<br />'; 
			echo 'Bad: '.($this->_errCount).'<br />'; 
		}
	}
}

