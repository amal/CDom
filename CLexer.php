<?php

/**
 * Basic lexer functionality
 *
 * @project Anizoptera CMF
 * @package system.lexer
 * @version $Id: CLexer.php 2725 2011-10-18 17:03:13Z samally $
 */
abstract class CLexer
{
	/**
	 * Charset of processing string.
	 */
	const CHARSET = 'UTF-8';

	/**
	 * Space characters mask.
	 */
	const CHARS_SPACE = " \n\t";



	/**
	 * Current lexer position.
	 */
	protected $pos = 0;

	/**
	 * Current lexer character.
	 * NULL at the end of input or at the beginnning.
	 *
	 * @var string|null
	 */
	protected $chr;

	/**
	 * Length of string for parsing.
	 */
	protected $length = 0;

	/**
	 * String for parsing.
	 *
	 * @var string
	 */
	protected $string;



	/**
	 * String cleaning
	 *
	 * @param string $string
	 */
	protected function cleanString(&$string)
	{
		$string = trim($string);

		// Standardize new lines
		if (strpos($string, "\r") !== false) {
			$string = str_replace(array("\r\n", "\r"), "\n", $string);
		}

		// Cut ASCII control characters
		// every control character except newline (dec 10), carriage return (dec 13), and horizontal tab (dec 09)
		// url encoded 00-08, 11, 12, 14, 15
		// url encoded 16-31
		$string = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+|%0[0-8bcef]|%1[0-9a-f]/SXu', '', $string);
	}


	/**
	 * Moves current position to the N character forward and returns current char.
	 *
	 * @param int $n
	 *
	 * @return string
	 */
	protected function movePos($n = 1)
	{
		return $this->chr = (($this->pos += $n) < $this->length) ? $this->string[$this->pos] : null;
	}


	/**
	 * Skips first segment consisting entirely of characters contained within a given mask.
	 *
	 * @param string $mask The list of characters to skip
	 */
	protected function skipChars($mask)
	{
		$this->pos += strspn($this->string, $mask, $this->pos);
		$this->chr = ($this->pos < $this->length) ? $this->string[$this->pos] : null;
	}

	/**
	 * Returns first segment consisting entirely of characters contained within a given mask.
	 *
	 * @param string $mask The list of allowable characters to include
	 *
	 * @return string
	 */
	protected function getChars($mask)
	{
		$pos = $this->pos;
		$len = strspn($this->string, $mask, $pos);
		$this->pos += $len;
		$this->chr = ($this->pos < $this->length) ? $this->string[$this->pos] : null;
		if ($len === 0) {
			return '';
		}
		return substr($this->string, $pos, $len);
	}

	/**
	 * Returns first segment consisting entirely of characters not matching mask.
	 *
	 * @param string $mask The list of forbidden characters
	 *
	 * @return string
	 */
	protected function getUntilChars($mask)
	{
		$pos = $this->pos;
		$len = strcspn($this->string, $mask, $pos);
		$this->pos += $len;
		$this->chr = ($this->pos < $this->length) ? $this->string[$this->pos] : null;
		if ($len === 0) {
			return '';
		}
		return substr($this->string, $pos, $len);
	}

	/**
	 * Returns first segment before the specified substring.
	 *
	 * @param string $string		String to search
	 * @param bool $res				TRUE if substring found, FALSE otherwise
	 * @param bool $skipIfNotFound	Whether to return empty string instead of segment if substring not found
	 *
	 * @return string
	 */
	protected function getUntilString($string, &$res = false, $skipIfNotFound = false)
	{
		// End of input
		if ($this->chr === null) {
			$res = false;
			return '';
		}

		// Not found, end of input
		if (($pos = strpos($this->string, $string, $this->pos)) === false) {
			$res = false;
			if ($skipIfNotFound) {
				return '';
			}
			$ret = substr($this->string, $this->pos);
			$this->chr = null;
			$this->pos = $this->length;
			return $ret;
		}

		$res = true;

		// Found in current position
		if ($pos === $this->pos) {
			return '';
		}

		// Found
		$pos_old = $this->pos;
		$this->chr = $this->string[$pos];
		$this->pos = $pos;

		return substr($this->string, $pos_old, $pos - $pos_old);
	}

	/**
	 * Returns first segment before the specified charcter.
	 * Allows character to be escaped.
	 * Moves lexer position to one character after the quoted char.
	 *
	 * @param string $char			Character to search
	 * @param bool $res				TRUE if substring found, FALSE otherwise
	 * @param bool $skipIfNotFound	Whether to return empty string instead of segment if substring not found
	 *
	 * @return string
	 */
	protected function getUntilCharEscape($char, &$res = false, $skipIfNotFound = false)
	{
		$res = false;

		// End of input
		if ($this->chr === null) {
			return '';
		}

		$start = $this->pos;
		$unescape = false;
		do {
			if (($pos = strpos($this->string, $char, $start)) === false) {
				if ($skipIfNotFound) {
					return '';
				}
				$ret = substr($this->string, $this->pos, $this->length - $this->pos);
				$this->chr = null;
				$this->pos = $this->length;
				return $ret;
			}

			// Found in current position
			if ($pos === $this->pos) {
				$res = true;
				$this->chr = (($this->pos = $pos+1) < $this->length) ? $this->string[$this->pos] : null;
				return '';
			}

			// Escaping
			if ($this->string[$pos - 1] === '\\') {
				$start = $pos + 1;
				$unescape = true;
				continue;
			}

			$res = true;
			$pos_old = $this->pos;
			$str = substr($this->string, $pos_old, $pos - $pos_old);

			$this->chr = (($this->pos = $pos+1) < $this->length) ? $this->string[$this->pos] : null;

			// Unescape and return
			return $unescape ? str_replace("\\$char", $char, $str) : $str;
		} while (true);
	}
}
