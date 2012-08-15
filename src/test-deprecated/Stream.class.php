<?php

/**
 * Эмулятор схем
 * 
 * 
 * @version $Id: Stream.class.php 1298 2012-08-07 17:42:00Z gsb $
 */

/**
 * Класс эмуляции схем
 * 
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

