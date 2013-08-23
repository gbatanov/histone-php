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
 * Class-template to generate a class test.
 * 
 * @package unittest
 */
class EvaluatorAcceptanceTest extends PHPUnit_Framework_TestCase {

	/**
	 * @var Histone
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() {
		
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown() {
		
	}

	/**
	 * @covers Histone::process
	 * @dataProvider evalProvider
	 */
	public function testProcess($input, $expected = null, $exception = null, $context = null, $global = null, $function = null) {
		$baseUrl = '.';
		if (is_array($global)) {
			if (isset($global['baseURI']))
				$baseUrl = $global['baseURI'];
		}
		if ($function) {
			require_once('generated/generated-tests/external/external_func.php');
		}

		if ($context)
			$context = json_decode($context, true);

		try {
			$cHistone = new Histone($baseUrl);
			$cHistone->parseString($input);
			$result = $cHistone->process($context);
			return $this->assertEquals($expected, $result);
		}
		catch (ParseError $thrownException) {
			if ($exception !== null) {
				$resException = json_encode(array(
					'line' => (string) $thrownException->line,
					'expected' => (string) $thrownException->expected,
					'found' => (string) $thrownException->found,));
				$exception = json_encode($exception);
				return $this->assertEquals($exception, $resException);
			}
			else {
				return $this->assertEquals('NO_EXCEPTION', json_encode(array((string) $thrownException->line, (string) $thrownException->expected, (string) $thrownException->found,)));
			}
		}
		catch (HistoneError $histoneError) {
			return $this->assertEquals('NO_EXCEPTION', $histoneError->getMessage());
		}
		catch (Exception $e) {
			// формируется при невыполнении assert
			return $this->assertEquals($expected, $result);
		}
	}

	/**
	 * DataProvider for function testProcess.
	 * Containts urlencoded array, maid by script createunittest.php.
	 * 
	 * @return array
	 */
	public function evalProvider() {
		return array(/* moduleEvaluator_start */array('CHANGE'), /* moduleEvaluator_end */);
	}

}