<?php

/**
 *
 * @package unittest
 */
class ParserAcceptanceTest extends PHPUnit_Framework_TestCase {


	/**
	 * @var Sponde
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
	 * @covers Sponde::parseString
	 * @dataProvider parserProvider
	 */
	public function testParseString($input, $expected = '', $exception = '') {
		$baseUrl = '.'; 
		$input = htmlspecialchars_decode(/*urldecode*/($input));
		if ($expected)
			$expected = json_decode(/*urldecode*/($expected));
		else
			$expected = null;
		if ($exception) {
			$exceptionS = /*urldecode*/($exception);
			$exception = array();
			$exception['line'] = preg_replace('/(.*line.*>)(.*)(<\/line.*)$/Uis', "$2", $exceptionS);
			$exception['expected'] = preg_replace('/(.*expected.*>)(.*)(<\/expected.*)$/Uis', "$2", $exceptionS);
			$exception['found'] = preg_replace('/(.*found.*>)(.*)(<\/found.*)$/Uis', "$2", $exceptionS);
		} else {
			$exception = null;
		}
		try {
			$cSponde = new Sponde($baseUrl);
			$cSponde->parseString($input);
			$expected = json_encode($expected);
			$result = json_encode($cSponde->getTree());

			if ($expected === $result) {
				return $this->assertEquals($expected, $result);
			} else {
				if ($exception === null) {
					return $this->assertEquals($expected, $result);
				} else {
					return $this->assertEquals(json_encode($exception), 'NO_EXCEPTION'); // TODO str'ing
				}
			}
		} catch (ParseError $thrownException) {
			/* delete debugger messages */
			if (isset($thrownException->xdebug_message))
				unset($thrownException->xdebug_message);
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
		} catch (SpondeError $spondeError) {
			return $this->assertEquals('NO_EXCEPTION', json_encode($spondeError->getMessage()));
		} catch (Exception $e) {
			return $this->assertEquals('NO_EXCEPTION', json_encode($e->getMessage()));
		}
	}

	/**
	 * @covers Sponde::process
	 */
	public function testProcess() {
		/* stub */
		return $this->assertEquals('a', 'a');
	}
/**
 * DataProvider for function testParseString.
 * Containts urlencofed array, maid by script createunittest.php.
 * Arrays write between tags  module_start  and module_end.
 * 
 * @return array
 */
	public function parserProvider() {
		return array(/*module_start*/  /*module_end*/);
	}

}
