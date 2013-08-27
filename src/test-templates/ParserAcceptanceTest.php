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
class ParserAcceptanceTest extends PHPUnit_Framework_TestCase {

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
	 * @covers Histone::parseString
	 * @dataProvider parserProvider
	 */
	public function testParseString($input, $expected = '', $exception = '') {
		$baseUrl = '.';
		$result = null;

		if ($expected)
			$expected = json_decode($expected);
		else
			$expected = null;

		try {
			$cHistone = new Histone($baseUrl);
			$cHistone->parseString($input);
			$expected = json_encode($expected);
			$result = json_encode($cHistone->getTree());

			if ($expected === $result) {
				return $this->assertEquals($expected, $result);
			} else {
				if ($exception === null) {
					return $this->assertEquals($expected, $result);
				} else {
					return $this->assertEquals(json_encode($exception), 'NO_EXCEPTION');
				}
			}
		} catch (ParseError $thrownException) {
			if ($exception !== null) {
				$resException = json_encode(array(
					'line' => (string) $thrownException->line,
					'expected' => (string) $thrownException->expected,
					'found' => (string) $thrownException->found,));
				$exception = json_encode($exception);
				return $this->assertEquals($exception, $resException);
			} else {
				return $this->assertEquals('NO_EXCEPTION', json_encode(array((string) $thrownException->line, (string) $thrownException->expected, (string) $thrownException->found,)));
			}
		} catch (HistoneError $spondeError) {
			return $this->assertEquals('NO_EXCEPTION', json_encode(array($spondeError->getMessage(), $result)));
		} catch (Exception $e) {
			return $this->assertEquals('NO_EXCEPTION', json_encode(array($e->getMessage(), $result)));
		}
	}

	/**
	 * DataProvider for function testParseString.
	 * Containts urlencoded array, maid by script createunittest.php.
	 * 
	 * @return array
	 */
	public function parserProvider() {
		return array(/* moduleParser_start */array('CHANGE'), /* moduleParser_end */);
	}

}