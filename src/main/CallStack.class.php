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
 * Project Histone
 * 
 * @package HistoneClasses
 */

/**
 * class CallStack
 * 
 * @package HistoneClasses
 */
class CallStack {

	private $context = '';
	private $baseURI = '';
	private $stackPointer = 0;
	private $macros = array();
	private $variables = array();

	/**
	 * 
	 * @param string $context
	 */
	public function __construct($context) {
		$this->baseURI = '';
		$this->context = $context;
		$this->macros = array(array());
		$this->variables = array(array());
	}

	/**
	 * 
	 * @param string $baseURI
	 */
	public function setBaseURI($baseURI) {
		$this->baseURI = $baseURI;
	}

	/**
	 * 
	 * @return string
	 */
	public function getBaseURI() {
		return $this->baseURI;
	}

	/**
	 * 
	 * @param string $name
	 * @param mixed $value
	 */
	public function putVar($name, $value) {
		$this->variables[$this->stackPointer][$name] = $value;
		if (isset($this->macros[$this->stackPointer][$name]))
			unset($this->macros[$this->stackPointer][$name]);
	}

	/**
	 * 
	 * @param string $name
	 * @param array $args
	 * @param string $body
	 * @param string $baseURI
	 */
	public function putMacro($name, $args, $body, $baseURI) {
		$this->macros[$this->stackPointer][$name] = array($args, $body, $baseURI);
		if (isset($this->variables[$this->stackPointer][$name]))
			unset($this->variables[$this->stackPointer][$name]);
	}

	/**
	 * 
	 * @param string $name
	 * @return mixed
	 */
	public function getMacro($name) {
		$macros = $this->macros;
		$index = $this->stackPointer;
		do {
			$stackFrame = $macros[$index];
			if (isset($stackFrame[$name])) {
				return $stackFrame[$name];
			}
		} while ($index--);
	}

	/**
	 * 
	 * @param string $name
	 * @return mixed
	 */
	public function getVar($name) {
		$variables = $this->variables;
		$index = $this->stackPointer;
		do {
			$stackFrame = $variables[$index];
			if (key_exists($name, (array) $stackFrame)) {
				return $stackFrame[$name];
			}
		} while ($index--);
		return new HistoneUndefined();
	}

	/**
	 * @return void 
	 */
	public function save() {
		$this->stackPointer++;
		array_push($this->macros, array());
		array_push($this->variables, array());
	}

	/**
	 * @return void 
	 */
	public function restore() {
		$this->stackPointer--;
		array_pop($this->macros);
		array_pop($this->variables);
	}

	/**
	 * 
	 * @return array
	 */
	public function getContext() {
		return $this->context;
	}

}

