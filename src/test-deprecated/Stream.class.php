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
 * Класс эмуляции схем
 * 
 * @author gbatanov MegaFon
 * @version v.2013
 * @package testClasses
 */
class VariableStream
{

	var $position = 0;
	var $varname = array();
	/**
	 * 
	 * @param string $path
	 * @return string
	 */
	public static function createUniqueName($path)
	{
		$arr = explode(':', $path);
		if (isset($arr[1]))
		{
			$file = trim($arr[1], '/');
			$scheme = $arr[0];
			$path = $scheme . '://' . $file;
		}
		else
		{
			$path = $arr[0];
		}
		$url = parse_url($path);
		if (is_array($url))
		{
			$name = '';
			if (isset($url['scheme']))
				$name .=$url['scheme'] . '_';
			if (isset($url['host']))
				$name .= preg_replace('/[\.\/]/', '_', $url['host']) . '_';
			if (isset($url['path']))
				$name .= preg_replace('/[\.\/]/', '_', $url['path']) . '_';
			$name = trim($name, '_');
		}
		else
		{
			$name = preg_replace('/[\.\/]/', '_', $url);
		}
		return $name;
	}

	public static function registerScheme($scheme)
	{
		if (in_array($scheme, stream_get_wrappers()))
		{
			stream_wrapper_unregister($scheme);
		}
		$res = stream_wrapper_register($scheme, "VariableStream");
		return $res;
	}

	public static function registerSchemeByPath($path)
	{
		list($scheme, $file) = explode(':', $path);
		return self::registerScheme($scheme);
	}

	function stream_open($path, $mode, $options, &$opened_path)
	{
		$name = self::createUniqueName($path);
		$this->varname = $name;
		$this->position = 0;

		return true;
	}

	function url_stat()
	{
		return array();
	}

	function stream_stat()
	{
		return array();
	}

	function stream_read($count)
	{
		$ret = substr($GLOBALS[$this->varname], $this->position, $count);
		$this->position += strlen($ret);

		return $ret;
	}

	function stream_write($data)
	{
		$left = substr($GLOBALS[$this->varname], 0, $this->position);
		$right = substr($GLOBALS[$this->varname], $this->position + strlen($data));
		$GLOBALS[$this->varname] = $left . $data . $right;
		$this->position += strlen($data);
		return strlen($data);
	}

	function stream_tell()
	{
		return $this->position;
	}

	function stream_eof()
	{
		return $this->position >= strlen($GLOBALS[$this->varname]);
	}

	function stream_seek($offset, $whence)
	{
		switch ($whence)
		{
			case SEEK_SET:
				if ($offset < strlen($GLOBALS[$this->varname]) && $offset >= 0)
				{
					$this->position = $offset;
					return true;
				}
				else
				{
					return false;
				}
				break;

			case SEEK_CUR:
				if ($offset >= 0)
				{
					$this->position += $offset;
					return true;
				}
				else
				{
					return false;
				}
				break;

			case SEEK_END:
				if (strlen($GLOBALS[$this->varname]) + $offset >= 0)
				{
					$this->position = strlen($GLOBALS[$this->varname]) + $offset;
					return true;
				}
				else
				{
					return false;
				}
				break;

			default:
				return false;
		}
	}

}

