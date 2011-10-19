<?php

/**
 * CDom is a simple HTML/BBCode/XML DOM component.
 *
 * Written for Anizoptera CMF.
 * Based on PHP Simple HTML DOM Parser.
 * Licensed under the MIT License.
 *
 * @link http://simplehtmldom.sourceforge.net/
 *
 * @project Anizoptera CMF
 * @package system.CDom
 * @version $Id: CDom.php 2727 2011-10-19 09:11:46Z samally $
 * @author Amal Samally <amal.samally at gmail.com>
 * @license MIT
 */


// Basic lexer functionality
require __DIR__ . '/CLexer.php';



/**
 * Parser for HTML/BBCode/XML-like languages ​​to the DOM-like structure.
 *
 * @project Anizoptera CMF
 * @package system.CDom
 * @version $Id: CDom.php 2727 2011-10-19 09:11:46Z samally $
 */
class CDom extends CLexer
{
	const NODE_ELEMENT	 = 1;	// XML_ELEMENT_NODE
	const NODE_ATTRIBUTE = 2;	// XML_ATTRIBUTE_NODE
	const NODE_TEXT 	 = 3;	// XML_TEXT_NODE
	const NODE_CDATA	 = 4;	// XML_CDATA_SECTION_NODE
	const NODE_COMMENT	 = 8;	// XML_COMMENT_NODE
	const NODE_DOCUMENT	 = 9;	// XML_DOCUMENT_NODE
	const NODE_DOCTYPE	 = 14;	// XML_DTD_NODE
	const NODE_XML_DECL	 = 30;



	/**
	 * Self-closing tags
	 */
	public static $selfClosingTags = array(
		'area'     => true,
		'base'     => true,
		'basefont' => true,
		'br'       => true,
		'embed'    => true,
		'hr'       => true,
		'image'    => true,
		'img'      => true,
		'input'    => true,
		'link'     => true,
		'meta'     => true,
		'param'    => true,
	);

	/**
	 * Inline tags.
	 */
	public static $inlineTags = array(
		'a'        => true,
		'abbr'     => true,
		'acronym'  => true,
		'b'        => true,
		'basefont' => true,
		'bdo'      => true,
		'big'      => true,
		'br'       => true,
		'cite'     => true,
		'code'     => true,
		'dfn'      => true,
		'em'       => true,
		'font'     => true,
		'i'        => true,
		'input'    => true,
		'kbd'      => true,
		'label'    => true,
		'q'        => true,
		's'        => true,
		'samp'     => true,
		'select'   => true,
		'small'    => true,
		'span'     => true,
		'strike'   => true,
		'strong'   => true,
		'sub'      => true,
		'sup'      => true,
		'textarea' => true,
		'tt'       => true,
		'u'        => true,
		'var'      => true,
		'del'      => true,
		'ins'      => true,
	);

	/**
	 * Block tag.
	 */
	public static $blockTags = array(
		'document'   => true,
		'address'    => true,
		'blockquote' => true,
		'center'     => true,
		'div'        => true,
		'fieldset'   => true,
		'form'       => true,
		'h1'         => true,
		'h2'         => true,
		'h3'         => true,
		'h4'         => true,
		'h5'         => true,
		'h6'         => true,
		'menu'       => true,
		'p'          => true,
		'pre'        => true,
		'table'      => true,
		'ol'         => true,
		'ul'         => true,
		'li'         => true,
		'applet'     => true,
		'button'     => true,
		'iframe'     => true,
		'object'     => true,
	);

	/**
	 * Optional closing tags.
	 *
	 * Tag, which closes the other tags => list of tags that it interrupts
	 */
	public static $optionalClosingTags = array(
		'tr'   => array(
			'tr' => true,
			'td' => true,
			'th' => true,
		),
		'th'   => array(
			'th' => true,
		),
		'td'   => array(
			'td' => true,
		),
		'li'   => array(
			'li' => true,
		),
		'dt'   => array(
			'dt' => true,
			'dd' => true,
		),
		'dd'   => array(
			'dd' => true,
			'dt' => true,
		),
		'dl'   => array(
			'dd' => true,
			'dt' => true,
		),
		'p'    => array(
			'p' => true,
		),
		'nobr' => array(
			'nobr' => true,
		),
		'h1'   => array(
			'h2' => true,
			'h3' => true,
			'h4' => true,
			'h5' => true,
			'h6' => true,
		),
		'h2'   => array(
			'h1' => true,
			'h3' => true,
			'h4' => true,
			'h5' => true,
			'h6' => true,
		),
		'h3'   => array(
			'h1' => true,
			'h2' => true,
			'h4' => true,
			'h5' => true,
			'h6' => true,
		),
		'h4'   => array(
			'h1' => true,
			'h2' => true,
			'h3' => true,
			'h5' => true,
			'h6' => true,
		),
		'h5'   => array(
			'h1' => true,
			'h2' => true,
			'h3' => true,
			'h4' => true,
			'h6' => true,
		),
		'h6'   => array(
			'h1' => true,
			'h2' => true,
			'h3' => true,
			'h4' => true,
			'h5' => true,
		),
	);

	/**
	 * Tags to skip contents.
	 * Contents of this tags will be saved in their 'value' property
	 * without creating of text nodes
	 */
	public static $skipContents = array(
		'script'=> true,
		'style' => true,
	);


	/**
	 * Tag opening bracket character
	 */
	public static $bracketOpen  = '<';

	/**
	 * Tag closing bracket character
	 */
	public static $bracketClose = '>';


	/**
	 * Whether to skip unnecessary whitespaces
	 */
	public static $skipWhitespaces = true;

	/**
	 * Whether to skip comments
	 */
	public static $skipComments = false;

	/**
	 * Whether to enable auto charset detection
	 */
	public static $charsetDetection = true;


	/**
	 * Debugging mode flag
	 */
	public static $debug = false;



	/**
	 * Parsing root (Document node)
	 *
	 * @var CDomDocument
	 */
	protected $root;

	/**
	 * Current parsing node parent
	 *
	 * @var CDomNodeTag
	 */
	protected $parent;

	/**
	 * Last parsed node
	 *
	 * @var CDomNode|CDomNodeTag
	 */
	protected $last;



	/**
	 * Creates new DOM object from string
	 *
	 * @param string   $markup
	 * @param CDomNode $parent
	 *
	 * @return CDomDocument
	 */
	public static function fromString($markup = '', $parent = null)
	{
		$lexer = new self($markup, $parent);
		return $lexer->root;
	}


	/**
	 * Constructor. Starts markup parsing.
	 *
	 * @param string   $markup
	 * @param CDomNode $parent
	 */
	protected function __construct($markup = '', $parent = null)
	{
		$this->debug('Loading markup');

		if (self::$charsetDetection) {
			$this->detectCharset($markup);
		}

		$this->cleanString($markup);

		if ($parent === null) {
			$parent = new CDomDocument;
			$parent->ownerDocument = $parent;
		}
		$this->root = $parent;

		if ($markup === '') {
			return;
		}

		$this->string = &$markup;
		if (($this->length = strlen($markup)) > 0) {
			$this->chr = $this->string[0];
		}

		$this->debug('LEXER START => String (' . $this->length . ')');

		$this->parent = $parent;

		$this->parse();

		$this->parent = null;
	}


	/**
	 * Detects the markup encoding and converts it to UTF-8 if needs.
	 *
	 * @param string $markup
	 */
	protected function detectCharset(&$markup)
	{
		$requestedCharset = strtolower(self::CHARSET);

		$regex = '/<(?:meta\s+.*?charset|\?xml\s+.*?encoding)=(?:"|\')?\s*([a-z0-9 _-]+)(?<!\s)/SXsi';
		if (preg_match_all($regex, $markup, $match, PREG_OFFSET_CAPTURE)) {
			/** @var $replaceDocCharset array */
			$replaceDocCharset = $match[1];
			$docCharsets = array();
			foreach ($match[1] as $ch) {
				$ch = strtolower($ch[0]);
				$docCharsets[$ch] = $ch;
			}
			$this->debug('Detecting charset settings in document: ' . join(', ', $docCharsets));
		} else {
			// is it UTF-8 ?
			// @link http://w3.org/International/questions/qa-forms-utf-8.html
			if (preg_match('~^(?:
			   [\x09\x0A\x0D\x20-\x7E]            # ASCII
			 | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
			 | \xE0[\xA0-\xBF][\x80-\xBF]         # excluding overlongs
			 | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
			 | \xED[\x80-\x9F][\x80-\xBF]         # excluding surrogates
			 | \xF0[\x90-\xBF][\x80-\xBF]{2}      # planes 1-3
			 | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
			 | \xF4[\x80-\x8F][\x80-\xBF]{2}      # plane 16
			)*$~DSXx', $markup)) {
				return;
			}
			$docCharsets = array();
			$replaceDocCharset = false;
		}

		if (!isset($docCharsets[$requestedCharset])) {
			$this->debug('Charset detection');
			// Document Encoding Conversion
			$docEncoding = false;
			$possibleCharsets = $docCharsets;
			$documentCharset = reset($docCharsets);
			if ($possibleCharsets && function_exists('mb_detect_encoding')) {
				$docEncoding = mb_detect_encoding($markup, $possibleCharsets);
				if ($docEncoding) {
					$docEncoding = strtolower($docEncoding);
				}
			}
			if (!$docEncoding) {
				array_unshift($possibleCharsets, $requestedCharset);
				$possibleCharsets[] = 'windows-1251';
				$possibleCharsets[] = 'koi8-r';
				$possibleCharsets[] = 'iso-8859-1';
				$possibleCharsets = array_unique($possibleCharsets);
				foreach ($possibleCharsets as $charset) {
					if ($markup === $this->convertCharset($markup, $charset, $charset)) {
						$docEncoding = $charset;
						break;
					}
				}
				if (!$docEncoding) {
					if ($documentCharset) {
						// Ok trust the document
						$docEncoding = $documentCharset;
					} else {
						$this->debug("Can't detect document charset");
						return;
					}
				}
			}
			if ($docEncoding == 'ascii') {
				$docEncoding = $requestedCharset;
			}
			$this->debug("DETECTED '$docEncoding'");
			if ($documentCharset && $docEncoding !== $documentCharset) {
				$this->debug("Detected does not match what document says: $documentCharset");
			}
			if ($docEncoding !== $requestedCharset) {
				$this->debug("CONVERT $docEncoding => $requestedCharset");
				$markup = self::convertCharset($markup, $docEncoding, $requestedCharset);
			}
			if ($replaceDocCharset) {
				$offset = 0;
				$charsetLength = strlen($requestedCharset);
				foreach ($replaceDocCharset as $val) {
					$chLength  = strlen($val[0]);
					$beginning = substr($markup, 0, $val[1]+$offset);
					$ending    = substr($markup, $val[1] + $chLength+$offset);
					$markup = $beginning . $requestedCharset . $ending;
					$offset += $charsetLength-$chLength;
				}
			}
		}
	}

	/**
	 * Converts markup charset
	 *
	 * @param string $markup
	 * @param string $in_charset
	 * @param string $out_charset
	 *
	 * @return string
	 */
	public static function convertCharset($markup, $in_charset, $out_charset)
	{
		if (function_exists('iconv')) {
			$markup = @iconv($in_charset, $out_charset.'//IGNORE', $markup);
		} else {
			// @codeCoverageIgnoreStart
			$markup = @mb_convert_encoding($markup, $out_charset, $in_charset);
			// @codeCoverageIgnoreEnd
		}
		return $markup;
	}


	/**
	 * Parses markup into the DOM tree
	 */
	protected function parse()
	{
		$debug  = self::$debug;
		$curChr = &$this->chr;
		$curPos = &$this->pos;
		$last   = &$this->last;
		$parent = &$this->parent;
		$bo     = self::$bracketOpen;
		$prefix = '';

		do {
			// Text
			if ($curChr !== $bo && $curChr !== null) {
				$str = $prefix.$this->getUntilString($bo, $res);
			} else if ($prefix !== '') {
				$str = $prefix;
			} else {
				$str = '';
			}
			if ($str !== '') {
				if (self::$skipWhitespaces && trim($str) === '') {
					$str = ' ';
				}
				$debug && $this->debug('Parser: Text node ['.strlen($str).']');
				// Do not create consecutive text nodes
				if ($last instanceof CDomNodeText && $last->parent->uniqId === $parent->uniqId) {
					$last->value .= $str;
				} else {
					$this->linkNodes(new CDomNodeText($str));
				}
				$prefix = '';
			}

			// End of input
			if ($curChr === null) {
				$debug && $this->debug('Lexer: end of input');
				break;
			}

			$startPos = $curPos;
			$nextChar = $this->movePos();

			// Closing tag
			if ($nextChar === '/') {
				$debug && $this->debug('Lexer: Closing tag');
				if ($this->parseTagClose()) {
					continue;
				}
			}

			// DOCTYPE | Comment | CDATA
			else if ($nextChar === '!') {
				// Comment
				if (($nextCh = $this->movePos()) === '-') {
					$debug && $this->debug('Lexer: Comment');
					if ($this->parseComment()) {
						continue;
					}
				}
				// CDATA
				else if ($nextCh === '[') {
					$debug && $this->debug('Lexer: CDATA section');
					if ($this->parseCDATA()) {
						continue;
					}
				}
				// DOCTYPE
				else if ($nextCh === 'D' || $nextCh === 'd') {
					$debug && $this->debug('Lexer: DOCTYPE');
					if ($this->parseDoctype()) {
						continue;
					}
				}
			}

			// XML heading
			else if ($nextChar === '?') {
				$debug && $this->debug('Lexer: XML heading');
				if ($this->parseXMLDeclaration()) {
					continue;
				}
			}

			// Opening tag
			else {
				$debug && $this->debug('Lexer: Opening tag');
				if ($this->parseTag()) {
					// Skip contents as text
					if (isset(self::$skipContents[$parent->name])) {
						if (!$last->selfClosed) {
							$last->value = $this->getUntilString("$bo/$parent->name");
						}
					}
					continue;
				}
			}

			// Fail
			$debug && $this->debug('Lexer WARNING: Processing failed, fallback to text');
			$prefix = substr($this->string, $startPos, $curPos-$startPos);
		} while (true);
	}


	/**
	 * Links new node to the current parent.
	 *
	 * Similar to {@link CDomNode::append()}, but without additional
	 * checks and therefore faster.
	 *
	 * @see CDomNode::append()
	 *
	 * @param CDomNode $node
	 * @param bool     $isChild
	 */
	protected function linkNodes($node, $isChild = false)
	{
		$parent = $this->parent;

		$node->parent = $parent;
		$node->ownerDocument = $parent->ownerDocument;

		if ($isChild) {
			$chid = count($parent->children);
			$node->chid = $chid;
			if ($chid === 0) {
				$parent->firstChild = $node;
			} else {
				$prev = $parent->lastChild;
				$prev->next = $node;
				$node->prev = $prev;
			}
			$parent->children[$chid] = $node;
			$parent->lastChild = $node;
		}

		$cnid = count($parent->nodes);
		$node->cnid = $cnid;
		$parent->nodes[$cnid] = $node;

		$this->last = $node;
	}


	/**
	 * XML Heading parsing
	 *
	 * @return string
	 */
	protected function parseXMLDeclaration()
	{
		// Check
		$pos = &$this->pos;
		if (substr_compare($this->string, 'xml', $pos+1, 3, true)) {
			$this->debug('Lexer ERROR: Incorrect XML declaration start');
			return false;
		}

		// Get content
		$this->movePos(4);
		$str = $this->getUntilString('?'.self::$bracketClose, $res, true);
		if (!$res) {
			$this->debug('Lexer ERROR: XML declaration ended incorrectly');
			return false;
		}

		// Node
		$this->debug('Parser: XML declaration node');

		$this->root->isXml = true;

		$this->linkNodes(new CDomNodeXmlDeclaration($str));

		$this->movePos(2);

		return true;
	}

	/**
	 * DOCTYPE parsing
	 *
	 * @return bool
	 */
	protected function parseDoctype()
	{
		// Check
		$pos = &$this->pos;
		if (substr_compare($this->string, 'OCTYPE', $pos+1, 6, true)) {
			$this->debug('Lexer ERROR: Incorrect DOCTYPE start');
			return false;
		}

		// Get content
		$this->movePos(7);
		$str = $this->getUntilString(self::$bracketClose, $res, true);
		if (!$res) {
			$this->debug('Lexer ERROR: DOCTYPE ended incorrectly');
			return false;
		}

		// Node
		$this->debug('Parser: DOCTYPE node');

		if (stripos($str, 'xhtml') !== false) {
			$this->root->isXml = true;
		}

		$this->linkNodes(new CDomNodeDoctype($str));

		$this->movePos();

		return true;
	}

	/**
	 * Tag parsing
	 *
	 * @return bool
	 */
	protected function parseTag()
	{
		// Name
		$this->skipChars(self::CHARS_SPACE);
		$bc  = self::$bracketClose;
		$tag = $this->getUntilChars($bc." /\n\t");
		if ($tag == '') {
			$this->debug('Lexer ERROR: Tag name not found');
			return false;
		}

		// Attributes
		$attributes = $this->parseParameters();

		// We can get self-closing tag here
		if (($chr = &$this->chr) === '/') {
			$closed = true;
			$this->movePos();
			$this->skipChars(self::CHARS_SPACE);
		} else {
			$closed = false;
		}

		// Here we should get a closing parenthesis
		if ($chr !== $bc) {
			$this->debug('Lexer ERROR: Tag ended incorrectly');
			return false;
		}

		$node = new CDomNodeTag($tag, $closed);
		$node->bracketOpen  = self::$bracketOpen;
		$node->bracketClose = $bc;
		if ($attributes) {
			$attributes->node = $node;
			$node->attributes = $attributes;
		}

		// Handle optional closing tags
		$tag_l = $node->name;
		$parent = &$this->parent;
		if (isset(self::$optionalClosingTags[$tag_l])) {
			while (isset(self::$optionalClosingTags[$tag_l][$parent->name])) {
				$parent = $parent->parent;
			}
		}

		// Block tags closes not closed inline tags.
		if (isset(self::$blockTags[$tag_l])) {
			while (isset(self::$inlineTags[$parent->name])) {
				$parent = $parent->parent;
			}
		}

		$this->debug('Parser: Tag node');

		$this->linkNodes($node, true);

		if (!$node->selfClosed) {
			$parent = $node;
		}

		$this->movePos();

		return true;
	}

	/**
	 * Closing tag parsing
	 *
	 * @return bool
	 */
	protected function parseTagClose()
	{
		$this->movePos();
		$this->skipChars(self::CHARS_SPACE);

		// Name & check
		$tag = $this->getUntilString(self::$bracketClose, $res);
		if (!$res || $tag === '') {
			$this->debug('Lexer ERROR: Closing tag not found');
			return false;
		} else if (($pos = strpos($tag, self::$bracketOpen)) !== false) {
			$this->debug('Lexer ERROR: Malformed closing tag');
			$this->movePos(-(strlen($tag)-$pos));
			return false;
		}

		// Skip trash
		if (($pos = strcspn($tag, self::CHARS_SPACE)) !== strlen($tag)) {
			$tag = substr($tag, 0, $pos);
		}

		$parentName = $this->parent->name;
		$tagName = mb_strtolower($tag, self::CHARSET);

		// Search for closing tag
		$skipping = false;
		if ($parentName !== $tagName) {
			if (isset(self::$optionalClosingTags[$parentName]) && isset(self::$blockTags[$tagName])) {
				$org_parent = $this->parent;
				while ($this->parent->parent && $this->parent->name !== $tagName) {
					$this->parent = $this->parent->parent;
				}
				if ($this->parent->name !== $tagName) {
					// restore original parent
					$this->parent = $org_parent;
					if ($this->parent->parent) {
						$this->parent = $this->parent->parent;
					}
					// Unexpected closing tag. Skipping.
					$skipping = true;
				}
			} else if ($this->parent->parent && isset(self::$blockTags[$tagName])) {
				$org_parent = $this->parent;
				while ($this->parent->parent && $this->parent->name !== $tagName) {
					$this->parent = $this->parent->parent;
				}
				if ($this->parent->name !== $tagName) {
					// restore original parent
					$this->parent = $org_parent;
					// Unexpected closing tag. Skipping.
					$skipping = true;
				}
			} else if ($this->parent->parent && $this->parent->parent->name === $tagName) {
				$this->parent = $this->parent->parent;
			} else {
				// Unexpected closing tag. Skipping.
				$skipping = true;
			}
		}

		if (!$skipping && $this->parent->parent) {
			$this->parent = $this->parent->parent;
		}

		$this->movePos();

		return true;
	}

	/**
	 * Comment parsing
	 *
	 * @return bool
	 */
	protected function parseComment()
	{
		// <!--   -->

		if ($this->movePos() !== '-' || !$this->movePos()) {
			$this->debug('Lexer ERROR: Incorrect comment start');
			return false;
		}

		// Get content
		$str = $this->getUntilString('--'.self::$bracketClose, $res);
		if (!$res) {
			$this->debug('Lexer ERROR: Comment ended incorrectly');
			return false;
		}
		$this->movePos(3);

		// Node
		if (!self::$skipComments) {
			$this->debug('Parser: Comment node');
			$this->linkNodes(new CDomNodeCommment($str));
		}

		return true;
	}

	/**
	 * CDATA parsing
	 *
	 * @return bool
	 */
	protected function parseCDATA()
	{
		// <![CDATA[   ]]>

		// Check
		$pos = &$this->pos;
		if (substr_compare($this->string, 'CDATA[', $pos+1, 6)) {
			$this->debug('Lexer ERROR: Incorrect CDATA start');
			return false;
		}

		// Get content
		$this->movePos(7);
		$str = $this->getUntilString(']]' . self::$bracketClose, $res, true);
		if (!$res) {
			$this->debug('Lexer ERROR: CDATA ended incorrectly');
			return false;
		}

		// Node
		$this->debug('Parser: CDATA node');

		$this->linkNodes(new CDomNodeCdata($str));

		$this->movePos(3);

		return true;
	}


	/**
	 * Parses tag parameters
	 *
	 * @return CDomAttributesList|null
	 */
	protected function parseParameters()
	{
		$attributes = null;
		$curChr = &$this->chr;
		$bc = self::$bracketClose;
		$chars_space = self::CHARS_SPACE;
		$chars_end = '/' . $chars_space . $bc;
		$chars_eq = '=' . $chars_end;

		$this->skipChars($chars_space);
		do {
			// Name
			if (($name = rtrim($this->getUntilChars($chars_eq))) === '') {
				break;
			}
			$this->skipChars($chars_space);
			if ($curChr !== '=') {
				if ($attributes === null) {
					$attributes = new CDomAttributesList;
				}
				$attributes->set($name, true);
				if ($curChr === $bc || $curChr === '/') {
					break;
				}
				continue;
			}

			$this->movePos();
			$this->skipChars($chars_space);

			// Value
			if ($curChr === "'" || $curChr === '"') {
				$quote = $curChr;
				$this->movePos();
			} else {
				$quote = false;
			}
			if ($quote) {
				// Quoted parameter
				$value = $this->getUntilCharEscape($quote);
			} else {
				// Simple parameter
				$value = $this->getUntilChars($chars_end);
			}
			if ($attributes === null) {
				$attributes = new CDomAttributesList;
			}
			$attributes->set($name, $value);
			$this->skipChars($chars_space);
		} while ($curChr !== null);

		return $attributes;
	}



	/**
	 * Debugging output
	 *
	 * @codeCoverageIgnore
	 */
	protected function debug()
	{
		if (!self::$debug) {
			return;
		}
		$args = func_get_args();
		if (count($args) === 1) {
			$args = $args[0];
			if (is_string($args) || is_numeric($args)) {
				echo "$args\n";
				return;
			}
		}
		var_dump($args);
	}
}



/**
 * Base CDom node
 *
 * @see CDom
 *
 * @project Anizoptera CMF
 * @package system.CDom
 * @version $Id: CDom.php 2727 2011-10-19 09:11:46Z samally $
 *
 * @property string           $nodeName               Alias of "name"
 * @property int              $nodeType               Alias of "type"
 * @property string           $nodeValue              Alias of "value"
 * @property int              $childElementCount      Count of child elements
 * @property CDomNode[]       $childNodes             Alias of "nodes"
 * @property CDomNodeTag|null $nextElementSibling     Alias of "next"
 * @property CDomNodeTag|null $previousElementSibling Alias of "prev"
 * @property CDomNode|null    $nextSibling            Next sibling node
 * @property CDomNode|null    $previousSibling        Previous sibling node
 * @property CDomNode|null    $firstNode              First child node
 * @property CDomNode|null    $lastNode               Last child node
 * @property string           $textContent            Alias of "text()"
 * @property string           $text                   Alias of "text()"
 * @property string           $html                   Alias of "html()"
 * @property string           $outerHtml              Alias of "outerHtml()"
 *
 * @method CDomNode parent()    Returns the parent of this node.
 * @method CDomNode clone()     Create a deep copy of this node.
 * @method CDomNode empty()     Remove all child nodes of this element from the DOM. Alias of cleanChildren().
 * @method string   innerHtml() Returns inner html of node (Alias of html()).
 */
abstract class CDomNode
{
	/**
	 * Unique ids counter.
	 */
	protected static $counter = 1;


	/**
	 * Node name (lowercased).
	 *
	 * @var string
	 */
	public $name;

	/**
	 * Original node name.
	 *
	 * @var string
	 */
	public $nameReal;

	/**
	 * The value of this node, depending on its type.
	 *
	 * @var string
	 */
	public $value;

	/**
	 * An attributes map containing the attributes of this node.
	 *
	 * @var CDomAttributesList|null
	 */
	public $attributes;

	/**
	 * Type of this node. One of CDom::NODE_* constants.
	 */
	public $type = 0;

	/**
	 * Child index of this node.
	 */
	public $chid = -1;

	/**
	 * Nodes child index of this node.
	 */
	public $cnid = -1;

	/**
	 * Array that contains all children of this node.
	 *
	 * @var CDomNode[]
	 */
	public $nodes = array();

	/**
	 * Array that contains all tag children of this node.
	 *
	 * @var CDomNodeTag[]
	 */
	public $children = array();

	/**
	 * The parent of this node.
	 *
	 * @var CDomNode|null
	 */
	public $parent;

	/**
	 * The first child of this node. NULL if there is no such node.
	 *
	 * @var CDomNodeTag|null
	 */
	public $firstChild;

	/**
	 * The last child of this node. NULL if there is no such node.
	 *
	 * @var CDomNodeTag|null
	 */
	public $lastChild;

	/**
	 * The node immediately preceding this node. NULL if there is no such node.
	 *
	 * @var CDomNodeTag|null
	 */
	public $prev;

	/**
	 * The node immediately following this node. NULL if there is no such node.
	 *
	 * @var CDomNodeTag|null
	 */
	public $next;

	/**
	 * Document object associated with this node.
	 *
	 * @var CDomDocument|null
	 */
	public $ownerDocument;

	/**
	 * Unique id of this node.
	 */
	public $uniqId = 0;



	/**
	 * Initialization
	 */
	public function __construct()
	{
		$this->uniqId = self::$counter++;
	}


	/**
	 * Cleans memory from this node.
	 *
	 * WARNING: Node will be damaged for further use!
	 */
	public function clean()
	{
		$this->attributes    = null;
		$this->next          = null;
		$this->prev          = null;
		$this->parent        = null;
		$this->ownerDocument = null;
		$this->cleanChildren();
	}

	/**
	 * Cleans childs of this node
	 *
	 * @return CDomNode
	 */
	public function cleanChildren()
	{
		foreach ($this->nodes as $node) {
			$node->clean();
		}
		$this->firstChild = null;
		$this->lastChild  = null;
		$this->children   = array();
		$this->nodes      = array();
		return $this;
	}


	/**
	 * Recursively updates owner document of this node
	 *
	 * @param CDomDocument $document
	 */
	protected function updateOwnerDocument($document)
	{
		if (!$od = &$this->ownerDocument || !$document || $od->uniqId !== $document->uniqId) {
			$od = $document;
			foreach ($this->nodes as $node) {
				$node->updateOwnerDocument($document);
			}
		}
	}



	/**
	 * Handles magic calls to really undefined methods
	 *
	 * @throws BadMethodCallException
	 *
	 * @param string $name
	 * @param array  $arguments
	 *
	 * @return mixed
	 */
	public function __call($name, $arguments)
	{
		$name_l = strtolower($name);
		// clone
		if ($name_l === 'clone') {
			return clone $this;
		}
		// empty
		else if ($name_l === 'empty') {
			return $this->cleanChildren();
		}
		// innerHtml
		else if ($name_l === 'innerhtml') {
			return $this->html();
		}
		// property
		else if (property_exists($this, $name)) {
			return $this->$name;
		}

		throw new BadMethodCallException('Method "'.get_class($this).'.'.$name.'" is not defined.');
	}

	/**
	 * Handles magic usages of really undefined parameters
	 *
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function &__get($name)
	{
		$name_l = strtolower($name);

		// firstNode
		if ($name_l === 'firstnode') {
			$res = reset($this->nodes);
			$res !== false || $res = null;
		}
		// lastNode
		else if ($name_l === 'lastnode') {
			$res = end($this->nodes);
			$res !== false || $res = null;
		}
		// nodeName
		else if ($name_l === 'nodename') {
			$res = $this->name;
		}
		// nodeType
		else if ($name_l === 'nodetype') {
			$res = $this->type;
		}
		// nodeValue
		else if ($name_l === 'nodevalue') {
			$res = $this->value;
		}
		// text, textContent
		else if ($name_l === 'text' || $name_l === 'textcontent') {
			$res = $this->text();
		}
		// html
		else if ($name_l === 'html') {
			$res = $this->html();
		}
		// outerHtml
		else if ($name_l === 'outerhtml') {
			$res = $this->outerHtml();
		}
		// childElementCount
		else if ($name_l === 'childelementcount') {
			$res = count($this->children);
		}
		// childNodes
		else if ($name_l === 'childnodes') {
			$res = $this->nodes;
		}
		// nextElementSibling
		else if ($name_l === 'nextelementsibling') {
			$res = $this->next;
		}
		// previousElementSibling
		else if ($name_l === 'previouselementsibling') {
			$res = $this->prev;
		}
		// nextSibling
		else if ($name_l === 'nextsibling') {
			$res = null;
			if ($parent = $this->parent) {
				$cnid = $this->cnid+1;
				$nodes = &$parent->nodes;
				$res = isset($nodes[$cnid]) ? $nodes[$cnid] : null;
			}
		}
		// previousSibling
		else if ($name_l === 'previoussibling') {
			$res = null;
			if ($parent = $this->parent) {
				$cnid = $this->cnid-1;
				$nodes = &$parent->nodes;
				$res = isset($nodes[$cnid]) ? $nodes[$cnid] : null;
			}
		}
		// Node attribute
		else {
			$res =  ($this->attributes && $a = $this->attributes->get($name)) ? $a->value() : null;
		}

		return $res;
	}

	/**
	 * Handles magic usages of really undefined parameters.
	 *
	 * @see attr()
	 *
	 * @param string $name
	 * @param string $value
	 */
	public function __set($name, $value)
	{
		$this->attr($name, $value);
	}


	/**
	 * Handles node cloning
	 */
	public function __clone()
	{
		self::__construct();
		$this->chid = -1;
		$this->cnid = -1;
		$this->parent = null;
		$this->prev = null;
		$this->next = null;
		$this->firstChild = null;
		$this->lastChild = null;
		if ($attrs = $this->attributes) {
			$attrs = clone $attrs;
			$attrs->node = $this;
			$this->attributes = $attrs;
		}
		if ($nodes = $this->nodes) {
			$this->nodes = array();
			$this->children = array();
			$chid = 0;
			$cnid = 0;
			$prev = null;
			foreach ($nodes as $node) {
				$node = clone $node;
				$node->parent = $this;
				if ($node instanceof CDomNodeTag) {
					$node->prev = $prev;
					if ($prev) {
						$prev->next = $node;
					} else {
						$this->firstChild = $node;
					}
					$prev = $node;
					$node->chid = $chid;
					$this->children[$chid] = $node;
					$chid++;
				}
				$node->cnid = $cnid;
				$this->nodes[$cnid] = $node;
				$cnid++;
			}
			if ($prev) {
				$this->lastChild = $prev;
			}
		}
		if ($this instanceof CDomDocument) {
			/** @noinspection PhpParamsInspection */
			$this->updateOwnerDocument($this);
		}
	}


	/**
	 * Returns the descendants of this element, filtered by a selector.
	 *
	 * @see find
	 *
	 * @link http://api.jquery.com/find/
	 *
	 * @param string|CDomSelector $selector CSS selector
	 * @param int    $n        Index of element in set to return
	 *
	 * @return CDomNodeTag[]|CDomNodesList|CDomNodeTag Nodes list or
	 * one node if index is specified
	 */
	public function __invoke($selector, $n = null)
	{
		return $this->find($selector, $n);
	}


	/**
	 * Converts node to string (outer html of node)
	 *
	 * @see outerHtml
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->outerHtml();
	}

	/**
	 * Returns outer html of node (with self)
	 *
	 * @see html
	 *
	 * @return string
	 */
	public function outerHtml()
	{
		return $this->html();
	}


	/**
	 * Returns or sets the text contents of this element,
	 * including descendants.
	 *
	 * @link http://api.jquery.com/text/
	 *
	 * @param string|CDomNode|CDomNode[]|CDomNodesList $value
	 *
	 * @return string|$this
	 */
	public function text($value = null)
	{
		// Set
		if ($value !== null) {
			// array
			if (is_array($value)) {
				$list = new CDomNodesList();
				$list->list = $value;
				$value = new CDomNodeText($list->textAll());
			}
			// string
			else if (!is_object($value)) {
				$value = new CDomNodeText($value);
			}
			// nodes list
			else if ($value instanceof CDomNodesList) {
				$value = new CDomNodeText($value->textAll());
			}
			// not text node
			else if (!($value instanceof CDomNodeText)) {
				$value = new CDomNodeText($value->text());
			}
			return $this->cleanChildren()->append($value);
		}

		// Get
		return $this->_text();
	}

	/**
	 * Returns text contents of this element,
	 * including descendants.
	 *
	 * @param bool $recursive
	 *
	 * @return string
	 */
	protected function _text($recursive = false)
	{
		$ret = '';
		$blockTags = &CDom::$blockTags;
		/** @var $n CDomNode|CDomNodeTag */
		foreach ($this->nodes as $n) {
			if ($n instanceof CDomNodeTag) {
				$name = $n->name;
				if ($n->selfClosed) {
					if ($name === 'br') {
						$ret .= "\n";
					}
				} else {
					$ret .= $n->_text(true);
				}
				if (isset($blockTags[$name])) {
					$ret .= "\n";
				}
			} else {
				$ret .= $n->text();
			}
		}
		if (!$recursive && CDom::$skipWhitespaces) {
			$ret = trim($ret);
			$ret = preg_replace('/^[ \t]+|[ \t]+$/SXm', '', $ret);
			$ret = preg_replace('/\n{3,}/SX', "\n\n", $ret);
		}
		return $ret;
	}

	/**
	 * Returns or sets inner html of node (without self)
	 *
	 * @link http://api.jquery.com/html/
	 *
	 * @param string|CDomNode|CDomNode[]|CDomNodesList $value
	 *
	 * @return string|$this
	 */
	public function html($value = null)
	{
		// Set
		if ($value !== null) {
			$this->cleanChildren();
			if (!is_object($value) && !is_array($value)) {
				$value = CDom::fromString($value)->detachChildren();
			}
			$this->append($value);
			return $this;
		}

		// Get
		$ret = '';
		foreach ($this->nodes as $n) {
			$ret .= $n->outerHtml();
		}
		return $ret;
	}


	/**
	 * Returns the specified child element
	 *
	 * @param int $n Child index. Can be negative to get childs
	 * 			from end of the list
	 *
	 * @return CDomNode|null NULL if specified child not exists
	 */
	public function child($n)
	{
		$n >= 0 || $n = count($this->children) + $n;
		return isset($this->children[$n]) ? $this->children[$n] : null;
	}

	/**
	 * Returns the specified child node
	 *
	 * @param int $n Child node index. Can be negative to get
	 * 			childs from end of the list
	 *
	 * @return CDomNode|null NULL if specified child not exists
	 */
	public function node($n)
	{
		$n >= 0 || $n = count($this->nodes) + $n;
		return isset($this->nodes[$n]) ? $this->nodes[$n] : null;
	}


	/**
	 * Returns or sets the value of an attribute for this node.
	 *
	 * @link http://api.jquery.com/attr/
	 *
	 * @param string      $name
	 * @param string|bool $value
	 * @param bool        $toString Whether to convert result to string
	 *
	 * @return CDomAttribute|string|null|$this
	 */
	public function attr($name, $value = null, $toString = true)
	{
		if ($value === null) {
			if ($this->attributes) {
				if ($attr = $this->attributes->get($name)) {
					return $toString ? $attr->value() : $attr;
				}
			}
			return null;
		}
		if (!$this->attributes) {
			$this->attributes = new CDomAttributesList();
		}
		$this->attributes->set($name, $value);
		return $this;
	}

	/**
	 * Removes an attribute from this element.
	 *
	 * @link http://api.jquery.com/removeAttr/
	 *
	 * @param string $name
	 *
	 * @return CDomNode
	 */
	public function removeAttr($name)
	{
		$this->attributes && $this->attributes->delete($name);
		return $this;
	}

	/**
	 * Returns if current node has the specified attribite
	 *
	 * @param string $name
	 *
	 * @return bool
	 */
	public function hasAttribute($name)
	{
		return $this->attributes && $this->attributes->has($name);
	}


	/**
	 * Inserts pecified content to this element at specified position
	 *
	 * @param string|CDomNode|CDomNode[]|CDomDocument|CDomNodesList $nodes
	 * @param int  $targetCnid Child node position to insert to
	 * @param int  $targetChid Child position to insert to
	 * @param bool $replace    Whether to replace target node
	 *
	 * @return CDomNode
	 */
	protected function insertAt(&$nodes, $targetCnid = 0, $targetChid = -1, $replace = false)
	{
		// Work only with tags and docs
		if (!($this instanceof CDomNodeTag) && !($this instanceof CDomDocument)) {
			$nodes = array();
			return $this;
		}

		// Convert content variants to simple array with nodes
		if (!is_array($nodes)) {
			// String
			if (!is_object($nodes)) {
				$nodes = CDom::fromString($nodes);
				$nodes = &$nodes->detachChildren();
			}
			// Document
			else if ($nodes instanceof CDomDocument) {
				$nodes = &$nodes->detachChildren();
			}
			// Nodes list
			else if ($nodes instanceof CDomNodesList) {
				$nodes = $nodes->list;
			}
			// Node
			else {
				$nodes = array($nodes);
			}
		}
		if (!$nodes) {
			$nodes = array();
			return $this;
		}

		// Find $targetChid if it's not set
		$thisNodes = &$this->nodes;
		if ($targetChid === -1) {
			if (!$this->firstChild) {
				$targetChid = 0;
			} else if (isset($thisNodes[$targetCnid])) {
				$lNode = $thisNodes[$targetCnid];
				if ($lNode->chid !== -1) {
					$targetChid = $lNode->chid;
				} else {
					$targetChid = 0;
					$cnid = $targetCnid;
					while (--$cnid > -1) {
						$lNode = $thisNodes[$cnid];
						if ($lNode->chid !== -1) {
							$targetChid = $lNode->chid+1;
							break;
						}
					}
				}
				unset($lNode);
			} else {
				$targetChid = count($this->children);
			}
		}

		// Prepare nodes for inserting
		$childs = array();
		$cnid = $targetCnid;
		$chid = $targetChid;
		if ($chid === 0) {
			if ($replace) {
				$this->firstChild = isset($this->children[$chid+1]) ? $this->children[$chid+1] : null;
			}
			$prev = null;
		} else {
			$prev = $this->children[$chid-1];
			$prev->next = null;
		}
		foreach ($nodes as $n) {
			if ($n->parent) {
				$n->detach();
			}
			$n->parent = $this;
			$n->updateOwnerDocument($this->ownerDocument);
			$n->cnid = $cnid++;
			if ($n instanceof CDomNodeTag) {
				if ($chid === 0) {
					$this->firstChild = $n;
				}
				$n->chid = $chid++;
				$n->prev = $prev;
				$n->next = null;
				if ($prev) {
					$prev->next = $n;
				}
				$childs[] = $n;
				$prev = $n;
			} else {
				$n->chid = -1;
				$n->prev = null;
				$n->next = null;
			}
		}

		// Recalculate child ids and properties
		$nextNotFound = true;
		$replaceChild = false;
		$cnid = $targetCnid;
		if ($replace) {
			if (isset($thisNodes[$cnid])) {
				$n = $thisNodes[$cnid];
				if ($n instanceof CDomNodeTag) {
					$replaceChild = true;
				}
				$n->clean();
			}
			$cnid++;
		}
		if (isset($thisNodes[$cnid])) {
			$incNodes  = count($nodes);
			$incChilds = count($childs);
			if ($replace) {
				$incNodes--;
				if ($replaceChild) {
					$incChilds--;
				}
			}
			do {
				$n = $thisNodes[$cnid];
				$n->cnid += $incNodes;
				if ($n instanceof CDomNodeTag) {
					if ($nextNotFound) {
						$n->prev = $prev;
						if ($prev) {
							$prev->next = $n;
						}
						$nextNotFound = false;
					}
					$n->chid += $incChilds;
				}
			} while (isset($thisNodes[++$cnid]));
		}
		if ($nextNotFound) {
			$this->lastChild = $prev;
		}

		// Insert elements
		array_splice($thisNodes, $targetCnid, $replace ? 1 : 0, $nodes);
		if ($childs || $replaceChild) {
			array_splice($this->children, $targetChid, $replaceChild ? 1 : 0, $childs);
		}

		/** @noinspection PhpUndefinedFieldInspection */
		isset($this->selfClosed) && $this->selfClosed = false;

		return $this;
	}


	/**
	 * Inserts content, after this element.
	 *
	 * @link http://api.jquery.com/after/
	 *
	 * @param string|CDomNode|CDomNode[]|CDomDocument|CDomNodesList $content
	 *
	 * @return CDomNode
	 */
	public function after($content)
	{
		if ($p = $this->parent) {
			$p->insertAt($content, $this->cnid+1);
		}
		return $this;
	}

	/**
	 * Inserts specified content, before this element.
	 *
	 * @link http://api.jquery.com/before/
	 *
	 * @param string|CDomNode|CDomNode[]|CDomDocument|CDomNodesList $content
	 *
	 * @return CDomNode
	 */
	public function before($content)
	{
		if ($p = $this->parent) {
			$p->insertAt($content, $this->cnid, $this->chid);
		}
		return $this;
	}


	/**
	 * Insert specified content, to the end of this element.
	 *
	 * @link http://api.jquery.com/append/
	 *
	 * @param string|CDomNode|CDomNode[]|CDomDocument|CDomNodesList $content
	 *
	 * @return CDomNode
	 */
	public function append($content)
	{
		return $this->insertAt($content, count($this->nodes), count($this->children));
	}

	/**
	 * Insert specified content, to the beginning of this element.
	 *
	 * @link http://api.jquery.com/prepend/
	 *
	 * @param string|CDomNode|CDomNode[]|CDomDocument|CDomNodesList $content
	 *
	 * @return CDomNode
	 */
	public function prepend($content)
	{
		return $this->insertAt($content, 0, 0);
	}


	/**
	 * Replace this element with the specified new content.
	 *
	 * @link http://api.jquery.com/replaceWith/
	 *
	 * @param string|CDomNode|CDomNode[]|CDomDocument|CDomNodesList $content
	 */
	public function replaceWith($content)
	{
		if ($p = $this->parent) {
			$p->insertAt($content, $this->cnid, $this->chid, true);
		}
	}

	/**
	 * Replace each target element with clone of this element.
	 *
	 * @link http://api.jquery.com/replaceAll/
	 *
	 * @param string|CDomNode|CDomNode[]|CDomNodesList $target
	 * 			CSS selector or target object(s)
	 *
	 * @return CDomNode[]|CDomNodesList|CDomNode
	 */
	public function replaceAll($target)
	{
		return $this->targetManipulation($target, false, false, false, false, true);
	}


	/**
	 * Manipulations with targets.
	 *
	 * @param string|CDomNode|CDomNode[]|CDomNodesList $target
	 * 		CSS selector or target object(s)
	 *
	 * @param bool $append
	 * @param bool $prepend
	 * @param bool $after
	 * @param bool $before
	 * @param bool $replace
	 *
	 * @return CDomNode|CDomNodesList|CDomNode[]
	 */
	protected function targetManipulation($target, $append = true, $prepend = false, $after = false, $before = false, $replace = false)
	{
		// Prepare and check targets
		if (is_string($target)) {
			if (!$this->ownerDocument) {
				return $this;
			}
			$target = new CDomSelector($target);
			$target = $target->find($this->ownerDocument);
		}
		if (!is_array($target)) {
			if ($target instanceof CDomNodesList) {
				$target = $target->list;
			} else {
				$target = array($target);
			}
		}
		if ($target) {
			// Prepare object
			if ($this instanceof CDomDocument) {
				$obj = new CDomNodesList;
				$obj->add($this->nodes);
			} else {
				$obj = $this;
			}
			$return = null;
			foreach ($target as $t) {
				if (!($t instanceof CDomNode)) {
					continue;
				}
				if ($append || $prepend) {
					if (!($t instanceof CDomNodeTag) && !($t instanceof CDomDocument)) {
						continue;
					}
					$o = $t;
				} else if (!$o = $t->parent) {
					continue;
				}
				if ($return === null) {
					$return = new CDomNodesList();
					// Remove current element from DOM
					$obj->detach();
				} else {
					$obj = clone $obj;
				}
				$nodes = $obj;
				if ($replace) {
					$o->insertAt($nodes, $t->cnid, $t->chid, true);
				} else if ($append) {
					$o->insertAt($nodes, count($o->nodes), count($o->children));
				} else if ($prepend) {
					$o->insertAt($nodes, 0, 0);
				} else if ($after) {
					$o->insertAt($nodes, $t->cnid+1);
				} else if ($before) {
					$o->insertAt($nodes, $t->cnid, $t->chid);
				}
				if ($nodes) {
					$return->add($nodes);
				}
			}
			return $return === null ? $this : $return;
		}
		return $this;
	}


	/**
	 * Insert this element, to the end of the target.
	 *
	 * @link http://api.jquery.com/appendTo/
	 *
	 * @param string|CDomNode|CDomNode[]|CDomDocument|CDomNodesList $target
	 * 			CSS selector or target object(s)
	 *
	 * @return CDomNode|CDomNodesList|CDomNode[]
	 */
	public function appendTo($target)
	{
		return $this->targetManipulation($target);
	}

	/**
	 * Insert this element to the beginning of the target.
	 *
	 * @param string|CDomNode|CDomNode[]|CDomDocument|CDomNodesList $target
	 * 			CSS selector or target object(s)
	 *
	 * @return CDomNode[]|CDomNodesList|CDomNode
	 *
	 * @return CDomNode
	 */
	public function prependTo($target)
	{
		return $this->targetManipulation($target, false, true);
	}


	/**
	 * Insert this element after the target.
	 *
	 * @link http://api.jquery.com/insertAfter/
	 *
	 * @param string|CDomNode|CDomNode[]|CDomNodesList $target
	 * 			CSS selector or target object(s)
	 *
	 * @return CDomNode[]|CDomNodesList|CDomNode
	 */
	public function insertAfter($target)
	{
		return $this->targetManipulation($target, false, false, true);
	}

	/**
	 * Insert this element before the target.
	 *
	 * @link http://api.jquery.com/insertBefore/
	 *
	 * @param string|CDomNode|CDomNode[]|CDomNodesList $target
	 * 			CSS selector or target object(s)
	 *
	 * @return CDomNode[]|CDomNodesList|CDomNode
	 */
	public function insertBefore($target)
	{
		return $this->targetManipulation($target, false, false, false, true);
	}


	/**
	 * Prepares and checks content to be wrapper
	 *
	 * @param string|CDomNode|CDomNode[]|CDomDocument|CDomNodesList $content
	 * 			CSS Selector, HTML code or object(s)
	 *
	 * @return bool|CDomNodeTag Returns false if error
	 */
	protected function prepareWrapContent($content)
	{
		// Prepare and check content
		if (is_string($content)) {
			$content = trim($content);
			if ($content[0] !== CDom::$bracketOpen && $this->ownerDocument) {
				$content = new CDomSelector($content);
				$content = $content->find($this->ownerDocument);
				$content = $content->list;
			} else {
				$dom = CDom::fromString($content);
				$content = $dom->firstChild;
			}
		}
		if ($content instanceof CDomNodesList) {
			$content = $content->list;
		}
		if (is_array($content)) {
			$content = reset($content);
		}
		if (!$content) {
			return false;
		}
		if ($content instanceof CDomDocument) {
			if (!$content = $content->firstChild) {
				return false;
			}
		} else if (!($content instanceof CDomNodeTag)) {
			return false;
		}
		$content = clone $content;
		$content->cleanChildren();
		if (isset($dom)) {
			$dom->clean();
		}
		return $content;
	}

	/**
	 * Wrap an HTML structure around this element.
	 *
	 * @param string|CDomNode|CDomNode[]|CDomDocument|CDomNodesList $content
	 * 			CSS Selector, HTML code or object(s)
	 *
	 * @return CDomNode
	 */
	public function wrap($content)
	{
		if (!$p = $this->parent) {
			return $this;
		}

		// Prepare and check content
		if (!$content = $this->prepareWrapContent($content)) {
			return $this;
		}

		// Wrap
		$cnid = $this->cnid;
		$chid = $this->chid;
		$this->detach();
		$content->insertAt($this, 0, 0);
		$p->insertAt($content, $cnid, $chid);

		return $this;
	}

	/**
	 * Wrap an HTML structure around the contents of this elements.
	 *
	 * @link http://api.jquery.com/wrapInner/
	 *
	 * @param string|CDomNode|CDomNode[]|CDomDocument|CDomNodesList $content
	 * 			CSS Selector, HTML code or object(s)
	 *
	 * @return CDomNode
	 */
	public function wrapInner($content)
	{
		// Prepare and check content
		if (!$content = $this->prepareWrapContent($content)) {
			return $this;
		}

		// Wrap
		return $this->append($content->append($this->detachChildren()));
	}

	/**
	 * Replace parent of this element with self.
	 *
	 * @return CDomNode
	 */
	public function unwrap()
	{
		if ($p = $this->parent) {
			$p->replaceWith($this);
		}
		return $this;
	}



	/**
	 * Removes this element from the DOM.
	 *
	 * @link http://api.jquery.com/detach/
	 *
	 * @return CDomNode
	 */
	public function detach()
	{
		if ($parent = $this->parent) {
			$next = $this->next;
			$prev = $this->prev;

			if ($isTag = $this instanceof CDomNodeTag) {
				$next ? $next->prev = $prev : $parent->lastChild  = $prev;
				$prev ? $prev->next = $next : $parent->firstChild = $next;
			}

			$nodes = &$parent->nodes;
			$cnid = $this->cnid;
			$chid = $this->chid !== -1;
			unset($parent->children[$this->chid], $nodes[$cnid]);
			while (isset($nodes[ ++$cnid ])) {
				$n = $nodes[$cnid];
				$n->cnid--;
				if ($chid && $n->chid !== -1) {
					$n->chid--;
				}
			}

			$parent->children = array_values($parent->children);
			$nodes = array_values($nodes);

			$this->chid   = -1;
			$this->cnid   = -1;
			$this->next   = null;
			$this->prev   = null;
			$this->parent = null;
		}

		return $this;
	}

	/**
	 * Removes this element children from the DOM.
	 *
	 * @return CDomNode[] Returns detached children
	 */
	public function &detachChildren()
	{
		foreach ($children = $this->nodes as $node) {
			$node->parent = null;
		}
		$this->firstChild = null;
		$this->lastChild  = null;
		$this->children   = array();
		$this->nodes      = array();
		return $children;
	}

	/**
	 * Removes this element from the DOM and memory.
	 *
	 * WARNING: Node will be damaged for further use!
	 *
	 * @link http://api.jquery.com/remove/
	 */
	public function remove()
	{
		$this->detach();
		$this->clean();
	}



	/**
	 * Returns the descendants of this element, filtered by a selector.
	 *
	 * @link http://api.jquery.com/find/
	 *
	 * @param string|CDomSelector $selector CSS selector
	 * @param int $n Index of element in set to return
	 *
	 * @return CDomNodeTag[]|CDomNodesList|CDomNodeTag Nodes list
	 * or one node if index is specified
	 */
	public function find($selector, $n = null)
	{
		if (is_string($selector)) {
			$selector = new CDomSelector($selector);
		}
		$list = $selector->find($this, $n);
		return $n === null ? $list : $list->get($n);
	}

	/**
	 * Returns if this node matches the selector.
	 *
	 * @link http://api.jquery.com/is/
	 *
	 * @param string|CDomSelector $selector CSS selector
	 *
	 * @return bool
	 */
	public function is($selector)
	{
		if (is_string($selector)) {
			$selector = new CDomSelector($selector);
		}
		return $selector->match($this);
	}

	/**
	 * Returns if this element have a descendant that matches the selector.
	 *
	 * @link http://api.jquery.com/has/
	 *
	 * @param string|CDomSelector $selector CSS selector
	 *
	 * @return bool
	 */
	public function has($selector)
	{
		if (!$this->children) {
			return false;
		}
		if (is_string($selector)) {
			$selector = new CDomSelector($selector);
		}
		foreach ($this->children as $child) {
			if ($selector->match($child)) {
				return true;
			}
		}
		return false;
	}


	/**
	 * Returns the children of this element, optionally
	 * filtered by a selector.
	 *
	 * @link http://api.jquery.com/children/
	 *
	 * @param string|CDomSelector $selector CSS selector
	 * @param CDomNodesList       $list
	 *
	 * @return CDomNodeTag[]|CDomNodesList
	 */
	public function children($selector = null, $list = null)
	{
		$list || $list = new CDomNodesList($this);

		if ($this->children) {
			$list->add($this->children);
			if ($selector) {
				$list->filter($selector);
			}
		}

		return $list;
	}

	/**
	 * Returns all relative nodes of this element, optionally
	 * filtered by a selector.
	 *
	 * @param string $type
	 * @param string|CDomSelector $selector CSS selector
	 * @param CDomNodesList $list
	 *
	 * @return CDomNodeTag[]|CDomNodesList
	 */
	protected function getRelativeAll($type, $selector = null, $list = null)
	{
		$list || $list = new CDomNodesList($this);

		if ($node = $this->$type) {
			if ($selector && is_string($selector)) {
				$selector = new CDomSelector($selector);
			}
			do {
				if ((!$selector || $selector->match($node)) && $node instanceof CDomNodeTag) {
					$list->add($node);
				}
			} while ($node = $node->$type);
		}

		return $list;
	}

	/**
	 * Returns all relative nodes of this element, up to but
	 * not including the element matched by the selector.
	 *
	 * @param string $type
	 * @param string|CDomSelector $selector CSS selector
	 * @param CDomNodesList $list
	 *
	 * @return CDomNodeTag[]|CDomNodesList
	 */
	protected function getRelativeUntil($type, $selector, $list = null)
	{
		$list || $list = new CDomNodesList($this);

		if ($node = $this->$type) {
			if (is_string($selector)) {
				$selector = new CDomSelector($selector);
			}
			do {
				if (!($node instanceof CDomNodeTag) || $selector->match($node)) {
					break;
				}
				$list->add($node);
			} while ($node = $node->$type);
		}

		return $list;
	}

	/**
	 * Returns the ancestors of this element, optionally
	 * filtered by a selector.
	 *
	 * @link http://api.jquery.com/parents/
	 *
	 * @param string|CDomSelector $selector CSS selector
	 * @param CDomNodesList $list
	 *
	 * @return CDomNodeTag[]|CDomNodesList
	 */
	public function parents($selector = null, $list = null)
	{
		return $this->getRelativeAll('parent', $selector, $list);
	}

	/**
	 * Returns the ancestors of this element, up to but not including
	 * the element matched by the selector.
	 *
	 * @link http://api.jquery.com/parentsUntil/
	 *
	 * @param string|CDomSelector $selector CSS selector
	 * @param CDomNodesList $list
	 *
	 * @return CDomNodeTag[]|CDomNodesList
	 */
	public function parentsUntil($selector, $list = null)
	{
		return $this->getRelativeUntil('parent', $selector, $list);
	}

	/**
	 * Returns the first ancestor element that matches the selector,
	 * beginning at the current element and progressing up through
	 * the DOM tree.
	 *
	 * @link http://api.jquery.com/closest/
	 *
	 * @param string|CDomSelector $selector CSS selector
	 *
	 * @return CDomNodeTag|null
	 */
	public function closest($selector)
	{
		if (is_string($selector)) {
			$selector = new CDomSelector($selector);
		}
		$node = $this;
		do {
			if ($selector->match($node)) {
				return $node;
			}
		} while ($node = $node->parent);
		return null;
	}

	/**
	 * Returns all following siblings of this element, optionally
	 * filtered by a selector.
	 *
	 * @link http://api.jquery.com/nextAll/
	 *
	 * @param string|CDomSelector $selector CSS selector
	 * @param CDomNodesList $list
	 *
	 * @return CDomNodeTag[]|CDomNodesList
	 */
	public function nextAll($selector = null, $list = null)
	{
		return $this->getRelativeAll('next', $selector, $list);
	}

	/**
	 * Returns all following siblings of this element up to
	 * but not including the element matched by the selector.
	 *
	 * @link http://api.jquery.com/nextUntil/
	 *
	 * @param string|CDomSelector $selector CSS selector
	 * @param CDomNodesList $list
	 *
	 * @return CDomNodeTag[]|CDomNodesList
	 */
	public function nextUntil($selector, $list = null)
	{
		return $this->getRelativeUntil('next', $selector, $list);
	}

	/**
	 * Returns all preceding siblings of this element, optionally
	 * filtered by a selector.
	 *
	 * @link http://api.jquery.com/prevAll/
	 *
	 * @param string|CDomSelector $selector CSS selector
	 * @param CDomNodesList $list
	 *
	 * @return CDomNodeTag[]|CDomNodesList
	 */
	public function prevAll($selector = null, $list = null)
	{
		return $this->getRelativeAll('prev', $selector, $list);
	}

	/**
	 * Returns all preceding siblings of this element up to
	 * but not including the element matched by the selector.
	 *
	 * @link http://api.jquery.com/prevUntil/
	 *
	 * @param string|CDomSelector $selector CSS selector
	 * @param CDomNodesList $list
	 *
	 * @return CDomNodeTag[]|CDomNodesList
	 */
	public function prevUntil($selector, $list = null)
	{
		return $this->getRelativeUntil('prev', $selector, $list);
	}

	/**
	 * Returns the siblings of this element, optionally
	 * filtered by a selector.
	 *
	 * @link http://api.jquery.com/siblings/
	 *
	 * @param string|CDomSelector $selector CSS selector
	 * @param CDomNodesList $list
	 *
	 * @return CDomNodeTag[]|CDomNodesList
	 */
	public function siblings($selector = null, $list = null)
	{
		$list = $this->getRelativeAll('prev', $selector, $list);
		$list = $this->getRelativeAll('next', $selector, $list);
		return $list;
	}


	/**
	 * Returns element by it's id
	 *
	 * @param string $id
	 *
	 * @return CDomNodeTag
	 */
	public function getElementById($id)
	{
		return $this->find("#$id", 0);
	}

	/**
	 * Returns element by it's name
	 *
	 * @param string $name Tag name
	 *
	 * @return CDomNodeTag
	 */
	public function getElementByTagName($name)
	{
		return $this->find($name, 0);
	}

	/**
	 * Returns elements by name
	 *
	 * @param string $name Tag name
	 * @param int    $n    Index of element in set to return
	 *
	 * @return CDomNodeTag[]|CDomNodesList|CDomNodeTag Nodes list
	 * or one node if index is specified
	 */
	public function getElementsByTagName($name, $n = null)
	{
		return $this->find($name, $n);
	}



	/**
	 * Debugging output of DOM tree
	 *
	 * @codeCoverageIgnore
	 *
	 * @param bool $attributes
	 * @param bool $text_nodes
	 * @param int $level
	 *
	 * @return CDomNode
	 */
	public function dump($attributes = true, $text_nodes = true, $level = 0)
	{
		if (!$text_nodes && $this->type === CDom::NODE_TEXT) {
			return null;
		}

		if ($level === 0) {
			echo "\n";
		}

		/** @var $obj CDomNodeTag|CDomNode */
		$obj = $this;

		if ($isTag = ($obj->type === CDom::NODE_ELEMENT)) {
			$current = CDom::$bracketOpen . $obj->name;
		} else {
			$current = $obj->name;
		}
		echo str_repeat('    ', $level) . $current;
		if ($attributes && $obj->attributes) {
			echo $obj->attributes;
		}
		if ($isTag) {
			if ($obj->selfClosed) {
				echo ' /';
			}
			echo CDom::$bracketClose;
		}
		echo "\n";

		if (count($obj->nodes)) {
			foreach ($obj->nodes as $node) {
				$node->dump($attributes, $text_nodes, $level+1);
			}
		}

		if ($level === 0) {
			echo "\n";
			PHP_SAPI === 'cli' && ob_get_level() > 0 && @ob_flush();
			return $this;
		}

		return null;
	}
 }


/**
 * CDom document node
 *
 * @see CDom
 *
 * @project Anizoptera CMF
 * @package system.CDom
 * @version $Id: CDom.php 2727 2011-10-19 09:11:46Z samally $
 */
class CDomDocument extends CDomNode
{
	public $isXml = false;

	/**
	 * Node name
	 */
	public $name = 'document';

	/**
	 * Node type
	 */
	public $type = CDom::NODE_DOCUMENT;


	/**
	 * Checks if node matches selector.
	 *
	 * @param string|CDomSelector $selector CSS selector
	 *
	 * @return bool
	 */
	public function is($selector)
	{
		if (count($this->children) === 1) {
			return $this->firstChild->is($selector);
		}
		return false;
	}
}


/**
 * CDom tag node
 *
 * @see CDom
 *
 * @project Anizoptera CMF
 * @package system.CDom
 * @version $Id: CDom.php 2727 2011-10-19 09:11:46Z samally $
 */
class CDomNodeTag extends CDomNode
{
	/**
	 * Node type
	 */
	public $type = CDom::NODE_ELEMENT;

	/**
	 * Is this node is self closed
	 */
	public $selfClosed = false;

	/**
	 * Is this node is in namespace
	 */
	public $isNamespaced = false;

	/**
	 * The namespace prefix of this node, or NULL if it is unspecified.
	 *
	 * @var string
	 */
	public $namespace;

	/**
	 * Returns the local part of the qualified name of this node.
	 *
	 * @var string
	 */
	public $nameLocal;

	/**
	 * Tag opening bracket character
	 */
	public $bracketOpen  = '<';

	/**
	 * Tag closing bracket character
	 */
	public $bracketClose = '>';



	/**
	 * Initialization
	 *
	 * @param string $name
	 * @param bool $closed
	 */
	public function __construct($name, $closed = false)
	{
		parent::__construct();

		$l_name = mb_strtolower($name, CDom::CHARSET);

		if (!$closed && isset(CDom::$selfClosingTags[$l_name])) {
			$closed = true;
		}

		$this->name 		= (string)$l_name;
		$this->nameReal 	= (string)$name;
		$this->selfClosed 	= (bool)$closed;

		if (($pos = strpos($l_name, ':')) !== false) {
			$this->namespace = substr($l_name, 0, $pos);
			$this->nameLocal = substr($l_name, $pos+1);
		} else {
			$this->nameLocal = (string)$l_name;
		}
	}


	/**
	 * Returns outer html of node (with self)
	 *
	 * @see html
	 *
	 * @return string
	 */
	public function outerHtml()
	{
		$bo = CDom::$bracketOpen;
		$bc = CDom::$bracketClose;
		$html = $bo . $this->name . $this->attributes;

		if ($this->selfClosed) {
			$html .= ' /' . $bc;
		} else {
			// Iterate here, without calling html(), to speed up
			$html .= $bc;
			foreach ($this->nodes as $n) {
				$html .= $n->outerHtml();
			}
			$html .= $bo . '/' . $this->name . $bc;
		}

		return $html;
	}
}

/**
 * CDom XML declaration
 *
 * @see CDom
 *
 * @project Anizoptera CMF
 * @package system.CDom
 * @version $Id: CDom.php 2727 2011-10-19 09:11:46Z samally $
 */
class CDomNodeXmlDeclaration extends CDomNodeText
{
	/**
	 * Node name
	 */
	public $name = 'xml declaration';

	/**
	 * Node type
	 */
	public $type = CDom::NODE_XML_DECL;


	/**
	 * Returns outer html of node (with self)
	 *
	 * @return string
	 */
	public function outerHtml()
	{
		return '<?xml' . $this->value . '?>';
	}


	/**
	 * Returns the text contents of this node.
	 *
	 * @link http://api.jquery.com/text/
	 *
	 * @param null $value
	 *
	 * @return string|$this
	 */
	public function text($value = null)
	{
		return ($value !== null) ? $this : '';
	}

	/**
	 * Returns inner html of this node (without self)
	 *
	 * @link http://api.jquery.com/html/
	 *
	 * @param null $value
	 *
	 * @return string|$this
	 */
	public function html($value = null)
	{
		return ($value !== null) ? $this : '';
	}
}

/**
 * CDom DOCTYPE node
 *
 * @see CDom
 *
 * @project Anizoptera CMF
 * @package system.CDom
 * @version $Id: CDom.php 2727 2011-10-19 09:11:46Z samally $
 */
class CDomNodeDoctype extends CDomNode
{
	/**
	 * Original node name
	 */
	public $nameReal = 'DOCTYPE';

	/**
	 * Node name (lowercased)
	 */
	public $name = 'doctype';

	/**
	 * Node type
	 */
	public $type = CDom::NODE_DOCTYPE;


	/**
	 * Constructs DOCTYPE node
	 *
	 * @param string $doctype
	 */
	public function __construct($doctype)
	{
		parent::__construct();
		$this->value = trim($doctype);
	}


	/**
	 * Returns outer html of node (with self)
	 *
	 * @return string
	 */
	public function outerHtml()
	{
		return '<!DOCTYPE ' . $this->value . '>';
	}


	/**
	 * Returns the text contents of this node.
	 *
	 * @link http://api.jquery.com/text/
	 *
	 * @param null $value
	 *
	 * @return string|$this
	 */
	public function text($value = null)
	{
		return ($value !== null) ? $this : '';
	}

	/**
	 * Returns inner html of this node (without self)
	 *
	 * @link http://api.jquery.com/html/
	 *
	 * @param null $value
	 *
	 * @return string|$this
	 */
	public function html($value = null)
	{
		return ($value !== null) ? $this : '';
	}
}


/**
 * CDom text node
 *
 * @see CDom
 *
 * @project Anizoptera CMF
 * @package system.CDom
 * @version $Id: CDom.php 2727 2011-10-19 09:11:46Z samally $
 */
class CDomNodeText extends CDomNode
{
	/**
	 * Node name
	 */
	public $name = 'text';

	/**
	 * Node type
	 */
	public $type = CDom::NODE_TEXT;


	/**
	 * Constructs text node
	 *
	 * @param string $text
	 */
	public function __construct($text = '')
	{
		parent::__construct();
		$this->value = (string)$text;
	}


	/**
	 * Returns or sets the text contents of this element,
	 * including descendants.
	 *
	 * @link http://api.jquery.com/text/
	 *
	 * @param string|CDomNode|CDomNode[]|CDomNodesList $value
	 *
	 * @return string|$this
	 */
	public function text($value = null)
	{
		// Set
		if ($value !== null) {
			// array
			if (is_array($value)) {
				$list = new CDomNodesList();
				$list->list = $value;
				$value = $list->textAll();
			}
			// object
			else if (is_object($value)) {
				// nodes list
				if ($value instanceof CDomNodesList) {
					$value = $value->textAll();
				}
				// node
				else {
					$value = $value->text();
				}
			}
			return $this->value = (string)$value;
		}

		// Get
		return $this->value;
	}

	/**
	 * Returns or sets inner html of node (without self)
	 *
	 * @link http://api.jquery.com/html/
	 *
	 * @param string|CDomNode|CDomNode[]|CDomNodesList $value
	 *
	 * @return string|$this
	 */
	public function html($value = null)
	{
		return $this->text($value);
	}
}

/**
 * CDom CDATA node
 *
 * @see CDom
 *
 * @project Anizoptera CMF
 * @package system.CDom
 * @version $Id: CDom.php 2727 2011-10-19 09:11:46Z samally $
 */
class CDomNodeCdata extends CDomNodeText
{
	/**
	 * Node name
	 */
	public $name = 'cdata';

	/**
	 * Node type
	 */
	public $type = CDom::NODE_CDATA;


	/**
	 * Returns outer html of node (with self)
	 *
	 * @see html
	 *
	 * @return string
	 */
	public function outerHtml()
	{
		return '<![CDATA[' . $this->value . ']]>';
	}
}

/**
 * CDom comment node
 *
 * @see CDom
 *
 * @project Anizoptera CMF
 * @package system.CDom
 * @version $Id: CDom.php 2727 2011-10-19 09:11:46Z samally $
 */
class CDomNodeCommment extends CDomNodeText
{
	/**
	 * Node name
	 */
	public $name = 'comment';

	/**
	 * Node type
	 */
	public $type = CDom::NODE_COMMENT;


	/**
	 * Returns outer html of node (with self)
	 *
	 * @see html
	 *
	 * @return string
	 */
	public function outerHtml()
	{
		return '<!--' . $this->value . '-->';
	}


	/**
	 * Returns the text contents of this node.
	 *
	 * @link http://api.jquery.com/text/
	 *
	 * @param null $value
	 *
	 * @return string|$this
	 */
	public function text($value = null)
	{
		return ($value !== null) ? $this : '';
	}


	/**
	 * Returns inner html of this node (without self)
	 *
	 * @link http://api.jquery.com/html/
	 *
	 * @param null $value
	 *
	 * @return string|$this
	 */
	public function html($value = null)
	{
		return ($value !== null) ? $this : '';
	}
}


/**
 * CDom attribute node
 *
 * @see CDom
 *
 * @project Anizoptera CMF
 * @package system.CDom
 * @version $Id: CDom.php 2727 2011-10-19 09:11:46Z samally $
 */
class CDomAttribute
{
	/**
	 * Lowercased name
	 *
	 * @var string
	 */
	public $name;

	/**
	 * Real name
	 *
	 * @var string
	 */
	public $nameReal;

	/**
	 * Value
	 *
	 * @var string|bool
	 */
	protected $value;

	/**
	 * Node type
	 */
	public $type = CDom::NODE_ATTRIBUTE;

	/**
	 * Owner node
	 *
	 * @var CDomNode|null
	 */
	public $node;


	/**
	 * Initialization
	 *
	 * @param string      $name		 Attribute name
	 * @param string|bool $value	 Attributa value or TRUE, if it's has no value
	 * @param null        $real_name Real attribute name, if 'name' is lowercased
	 */
	public function __construct($name, $value = true, $real_name = null)
	{
		if ($real_name === null) {
			$real_name = $name;
			$name = mb_strtolower($name, CDom::CHARSET);
		}
		$this->name = $name;
		$this->nameReal = $real_name;
		$this->value($value);
	}

	/**
	 * Sets or gets attribute value
	 *
	 * @param string|bool $value
	 *
	 * @return string|void
	 */
	public function value($value = null)
	{
		if ($value === null) {
			return $this->value === true ? $this->name : $this->value;
		}
		$this->value = $value === true ? $value : html_entity_decode($value, ENT_QUOTES, CDom::CHARSET);
		return null;
	}


	/**
	 * Returns html representation of attribute
	 *
	 * @return string
	 */
	public function html()
	{
		if (($val = $this->value) === true) {
			if (empty($this->node->ownerDocument->isXml)) {
				return $this->name;
			}
			$val = $this->name;
		} else {
			$val = htmlSpecialChars($val, ENT_QUOTES, CDom::CHARSET);
		}
		return $this->name . '="' . $val . '"';
	}

	/**
	 * Returns text representation of attribute
	 *
	 * @see get
	 *
	 * @return string
	 */
	public function text()
	{
		return $this->value();
	}


	/**
	 * Sets or gets attribute value
	 *
	 * @see set
	 *
	 * @param string|bool $value
	 *
	 * @return string|void
	 */
	function __invoke($value = null)
	{
		return $this->value($value);
	}

	/**
	 * Returns string representation of attribute
	 *
	 * @see get
	 *
	 * @return string
	 */
	function __toString()
	{
		return $this->value();
	}
}


/**
 * CDom selector
 *
 * @see CDom
 *
 * @link http://api.jquery.com/category/selectors/
 * @link http://www.w3.org/TR/CSS2/selector.html
 * @link http://www.w3.org/TR/css3-selectors/
 *
 * @throws InvalidArgumentException
 *
 * @project Anizoptera CMF
 * @package system.CDom
 * @version $Id: CDom.php 2727 2011-10-19 09:11:46Z samally $
 */
class CDomSelector extends CLexer
{
	/**
	 * Node type to search for
	 */
	const NODE = CDom::NODE_ELEMENT;


	/**
	 * Pseudo-classes for matched set filtration (jQuery Selector Extensions)
	 *
	 * @link http://api.jquery.com/category/selectors/jquery-selector-extensions/
	 */
	protected static $matchedSetFilters = array(
		'eq'    => true,
		'gt'    => true,
		'lt'    => true,
		'even'  => true,
		'odd'   => true,
		'first' => true,
		'last'  => true,
	);

	/**
	 * Headers tags for :header pseudo-class
	 */
	protected static $headers = array(
		'h1' => true,
		'h2' => true,
		'h3' => true,
		'h4' => true,
		'h5' => true,
		'h6' => true,
	);

	/**
	 * Cache for parsed selector structures
	 */
	protected static $structCache = array();


	/**
	 * Current parsed selector structure
	 *
	 * @var array
	 */
	protected $struct;


	/**
	 * Initialization
	 *
	 * @param string $selector
	 */
	public function __construct($selector)
	{
		$this->struct = $this->parseCssSelector($selector);
	}


	/**
	 * Parses CSS selector to the special structure.
	 *
	 * @param string $selector
	 *
	 * @return array
	 */
	protected function parseCssSelector($selector)
	{
		$selector = trim($selector);
		if (isset(self::$structCache[$selector])) {
			return self::$structCache[$selector];
		}
		if ($selector === '') {
			throw new InvalidArgumentException('Expects valid CSS selector expression.');
		}

		$this->string = &$selector;
		$this->length = strlen($selector);
		$this->chr = $this->string[0];
		$this->pos = 0;

		$curChr = &$this->chr;
		$curPos = &$this->pos;

		$mask_space = self::CHARS_SPACE;
		$mask_hierarchy = '~+>';
		$mask_eq = '=]*~$!^|';
		$mask = '.#[:,' . $mask_space . $mask_hierarchy;

		$struct = array();

		do {
			$cSel = array();
			$h = false;
			do {
				// Selector expression structure
				$sel = array(
					// Element
					'e' => '*',
					// Attributes
					'a' => array(),
					// Modifiers | pseudo-classes
					'm' => array(),
					// Matched set filters
					's' => array(),
					// Hierarchy
					'h' => $h,
					// Set limiter
					'l' => false,
				);

				// Element name
				$str = $this->getUntilChars($mask);
				if ($str !== '') {
					$sel['e'] = mb_strtolower($str, self::CHARSET);
				}

				// Additional
				if ($curChr !== null) {
					// Attributes & Modifiers
					do {
						// Class selector
						// Equivalent of [class~=value]
						if ($curChr === '.') {
							$this->movePos();
							$str = $this->getUntilChars($mask);
							if ($str === '') {
								throw new InvalidArgumentException("Expects valid class name at pos #$curPos.");
							}
							$sel['a']["class~=$str"] = array('class', $str, '~=');
						}

						// Id selector
						// Equivalent of [name="value"]
						else if ($curChr === '#') {
							$this->movePos();
							$str = $this->getUntilChars($mask);
							if ($str === '') {
								throw new InvalidArgumentException("Expects valid id name at pos #$curPos.");
							}
							$sel['a']["id=$str"] = array('id', $str, '=');
						}

						// Attribute selector
						else if ($curChr === '[') {
							$eq = $value = '';

							// Name
							$this->movePos();
							$name = $this->getUntilChars($mask_eq);
							if ($name === '') {
								throw new InvalidArgumentException("Expects valid attribute name at pos #$curPos.");
							}
							$name = mb_strtolower($name, self::CHARSET);

							// Value
							if ($curChr !== ']') {
								if ($curChr !== '=') {
									$eq .= $curChr;
									if ($this->movePos() !== '=') {
										throw new InvalidArgumentException("Expects equals sign at pos #$curPos.");
									}
								}
								$eq .= $curChr;
								$this->movePos();

								if ($curChr === "'" || $curChr === '"') {
									$quote = $curChr;
									$this->movePos();
								} else {
									$quote = false;
								}

								if ($quote) {
									// Quoted parameter
									$value = $this->getUntilCharEscape($quote, $res, true);
									if (!$res) {
										throw new InvalidArgumentException(
											"Expects quote after parameter at pos #$curPos."
										);
									}
									if ($curChr !== ']') {
										$res = false;
									}
								} else {
									// Simple parameter
									$value = $this->getUntilString(']', $res, true);
								}
								if (!$res) {
									throw new InvalidArgumentException("Expects sign ']' at pos #$curPos.");
								}
							}

							$this->movePos();

							$sel['a']["{$name}{$eq}{$value}"] = array($name, $value, $eq);
						}

						// Pseudo-class
						else if ($curChr === ':') {
							// Name
							$this->movePos();
							$name = $this->getUntilChars($mask.'(');
							if ($name === '') {
								throw new InvalidArgumentException(
									"Expects valid pseudo-selector at pos #$curPos."
								);
							}
							$name = mb_strtolower($name, self::CHARSET);

							// Value
							if ($curChr === '(') {
								$this->movePos();
								$value = $this->getUntilCharEscape(')', $res, true);
								if (!$res) {
									throw new InvalidArgumentException(
										"Expects closing bracket at pos #$curPos."
									);
								}
							} else {
								$value = '';
							}

							if (isset(self::$matchedSetFilters[$name])) {
								$value = (int)$value;
								if (!$sel['s']) {
									if ($name === 'first') {
										$sel['l'] = 0;
									} else if (($name === 'eq' || $name === 'lt')) {
										$sel['l'] = $value;
									}
								}
								$sel['s'][] = array($name, $value);
							} else {
								$key = "{$name}={$value}";
								// Preparsing of recursive selectors
								if ($name === 'has' || $name === 'not') {
									$value = $this->parseCssSelector($value === '' ? '*' : $value);
								}
								// Preparsing of nth- rules
								else if (!strncmp($name, 'nth-', 4)) {
									$value = $this->parseNthRule($value);
								}
								$sel['m'][$key] = array($name, $value);
							}
						}

						// Break
						else {
							break;
						}
					} while (true);

					if ($curChr !== null) {
						// Hierarchy
						$this->skipChars($mask_space);
						$continue = true;
						if ($curChr === '~' || $curChr === '+' || $curChr === '>') {
							$h = $curChr;
							$this->movePos();
							$this->skipChars($mask_space);
						} else {
							$h = false;
							// Next group
							if ($curChr === ',') {
								$continue = false;
								$this->movePos();
								$this->skipChars($mask_space);
							}
						}
					} else {
						// End of
						$continue = false;
					}
				} else {
					// End of
					$continue = false;
				}

				// Cleanup keys used for dublicates filtration
				foreach (array('a', 'm', 's') as $k) {
					if ($sel[$k]) {
						$sel[$k] = array_values($sel[$k]);
					}
				}
				$cSel[] = $sel;
			} while ($continue);

			$struct[] = $cSel;
		} while ($curChr !== null);

		return self::$structCache[$selector] = &$struct;
	}

	/**
	 * Parses value of :nth- pseudo-class to the rule structure (an+b)
	 *
	 * @link http://www.w3.org/TR/css3-selectors/#nth-child-pseudo
	 *
	 * @param string $value
	 *
	 * @return false|array($a, $b)
	 */
	protected function parseNthRule($value)
	{
		if (is_numeric($value)) {
			return array(0, (int)$value);
		} else {
			$value = trim(strtolower($value));
			if ($value === 'odd') {
				return array(2, 1);
			} else if ($value === 'even') {
				return array(2, 0);
			}
			$regex = '/^(?:(?:([+-])?(\d+)|([+-]))?(n)(?:\s*([+-])\s*(\d+))?|([+-])?\s*(\d+))$/DSX';
			if (!preg_match($regex, $value, $m)) {
				return array(0, 0);
			}
			if ($m[4] === 'n') {
				if ($m[2] !== '') {
					$a = (int)($m[1].$m[2]);
				} else {
					$a = (int)($m[3].'1');
				}
				if (isset($m[6])) {
					$b = (int)($m[5].$m[6]);
				} else {
					$b = 0;
				}
			} else {
				$a = 1;
				$b = (int)($m[7].$m[8]);
			}
			return array($a, $b);
		}
	}


	/**
	 * Finds nodes matching selector
	 *
	 * @param CDomNode		$context
	 * @param int			$n		 Number of element in set to return only
	 * @param CDomNodesList	$result
	 *
	 * @return CDomNodeTag[]|CDomNodesList
	 */
	public function find($context, $n = null, $result = null)
	{
		// Prepare result
		$result || $result = new CDomNodesList($context);

		// Speed up for needless searches
		if (!$context->children) {
			return $result;
		}

		// Element number, to return only, from full matched set
		$n === null || $n = (int)$n;

		// Iteration over independent selectors
		$rListByIds	= &$result->listByIds;
		foreach ($this->struct as $selector) {
			$e		   = $selector[0];
			$only	   = !isset($selector[1]);
			$setFilter = (bool)$e['s'];
			$en		   = ($e['l'] !== false) ? $e['l'] : null;
			$list	   = array();

			$res = $this->findNodes($context, $selector, $list, $n, $e, $only, $setFilter, $en);

			if ($list) {
				if ($rListByIds) {
					$rListByIds += $list;
				} else {
					$rListByIds = $list;
				}
			}

			if (!$res) {
				break;
			}
		}

		if ($rListByIds) {
			$result->list = array_values($rListByIds);
			$result->length = count($rListByIds);
		}

		return $result;
	}

	/**
	 * Checks if node matches selector
	 *
	 * @param CDomNode $node
	 *
	 * @return bool
	 */
	public function match($node)
	{
		return $this->nodeMatchSelector($node, $this->struct);
	}


	/**
	 * Finds nodes
	 *
	 * @param CDomNode		$context
	 * @param array			$selector
	 * @param CDomNode[]	$listByIds
	 * @param int			$n
	 * @param array			$e
	 * @param bool			$only
	 * @param bool			$setFilter
	 * @param null|int		$en
	 * @param bool			$recursive
	 *
	 * @return bool
	 */
	protected function findNodes($context, $selector, &$listByIds, $n, $e, $only, $setFilter, $en, $recursive = false)
	{
		// Iteration over nodes
		foreach ($context->children as $node) {
			if ($this->nodeMatchExpression($node, $e, $tree)) {
				// Simple match
				if ($only || $setFilter) {
					$listByIds[$node->uniqId] = $node;
					// speed up
					if ($en !== null && count($listByIds) > $en) {
						break;
					} else if (!$setFilter && $n !== null && count($listByIds) > $n) {
						return false;
					}
				}
				// Hierarchical match
				else {
					$nodes = array($node->uniqId => $node);
					$this->findNodesHierarchical($nodes, $selector);
					// Matches found
					if ($nodes) {
						$listByIds += $nodes;
					}
					// speed up
					if ($n !== null && count($listByIds) > $n) {
						return false;
					}
				}
			}
			if ($tree && $node->children) {
				$res = $this->findNodes($node, $selector, $listByIds, $n, $e, $only, $setFilter, $en, true);
				if (!$res) {
					return false;
				}
			}

			// speed up
			if ($en !== null && count($listByIds) > $en) {
				break;
			}
		}

		// Matched set filtraton
		if ($setFilter && !$recursive && $listByIds) {
			$this->matchedSetFilter($listByIds, $e['s']);
			if (!$only) {
				$this->findNodesHierarchical($listByIds, $selector);
			}
		}

		return true;
	}


	/**
	 * Finds nodes hierarchically
	 *
	 * @param CDomNodeTag[] $listByIds
	 * @param array         $selector
	 */
	protected function findNodesHierarchical(&$listByIds, $selector)
	{
		// Shift first part
		unset($selector[0]);

		// Iteration over hierarchical parts of selector
		foreach ($selector as $e) {
			$list = array();

			// Ancestor descendant
			if (!$h = $e['h']) {
				foreach ($listByIds as $node) {
					$this->findDescedants($node, $e, $list);
				}
			}

			// parent > child
			else if ($h === '>') {
				foreach ($listByIds as $node) {
					foreach ($node->children as $child) {
						if ($this->nodeMatchExpression($child, $e)) {
							$list[$child->uniqId] = $child;
						}
					}
				}
			}

			// prev + next
			else if ($h === '+') {
				foreach ($listByIds as $node) {
					if (($next = $node->next) && $this->nodeMatchExpression($next, $e)) {
						$list[$next->uniqId] = $next;
					}
				}
			}

			// prev ~ siblings
			else if ($h === '~') {
				foreach ($listByIds as $next) {
					while (($next = $next->next) !== null) {
						if ($this->nodeMatchExpression($next, $e)) {
							$list[$next->uniqId] = $next;
						}
					}
				}
			}

			// nothing found
			if (!$listByIds = $list) {
				break;
			}

			// Matched set filtration
			if ($e['s']) {
				$this->matchedSetFilter($listByIds, $e['s']);
			}
		}
	}

	/**
	 * Filters nodes list (jQuery Selector Extensions)
	 *
	 * @link http://api.jquery.com/category/selectors/jquery-selector-extensions/
	 *
	 * @param CDomNode[] $listByIds
	 * @param array      $filters
	 *
	 * @return CDomNodesList
	 */
	protected function matchedSetFilter(&$listByIds, $filters)
	{
		foreach ($filters as $f) {
			list($name, $value) = $f;

			// :first
			if ($name === 'first') {
				// We always have only one item here
//				if (count($listByIds) > 1) {
//					if ($node = reset($listByIds)) {
//						$listByIds = array($node->uniqId => $node);
//					} else {
//						$listByIds = array();
//					}
//				}
			}
			// :last
			else if ($name === 'last') {
				$node = end($listByIds);
				$listByIds = array(
					$node->uniqId => $node,
				);
			}
			// :eq()
			else if ($name === 'eq') {
				$listByIds = array_slice($listByIds, $value, 1, true);
			}
			// :lt()
			else if ($name === 'lt') {
				$listByIds = array_slice($listByIds, 0, $value, true);
			}
			// :gt()
			else if ($name === 'gt') {
				$listByIds = array_slice($listByIds, $value+1, null, true);
			}
			// :odd
			else if ($name === 'odd') {
				$i = 0;
				foreach($listByIds as $key => $node) {
					if (!($i & 1)) {
						unset($listByIds[$key]);
					}
					$i++;
				}
			}
			// :even
			else if ($name === 'even') {
				$i = 0;
				foreach($listByIds as $key => $node) {
					if ($i & 1) {
						unset($listByIds[$key]);
					}
					$i++;
				}
			}

			// speed up
			if (!$listByIds) {
				return;
			}
		}
	}


	/**
	 * Checks if node matches selector
	 *
	 * @param CDomNode $node
	 * @param array    $struct
	 *
	 * @return bool
	 */
	protected function nodeMatchSelector($node, $struct)
	{
		// Iteration over independent selectors
		foreach ($struct as $selector) {
			if (!$only = !isset($selector[1])) {
				foreach ($selector as $k => &$e) {
					if (isset($selector[$k+1])) {
						$e['h'] = $selector[$k+1]['h'];
					}
				}
				unset($k, $e);
				$selector = array_reverse($selector);
			}
			$e = $selector[0];
			if ($this->nodeMatchExpression($node, $e)) {
				// Simple match
				if ($only) {
					return true;
				}
				// Hierarchy
				else {
					unset($selector[0]);
					/** @var $nodes CDomNodeTag[] */
					$nodes = array($node);
					foreach ($selector as $k => $e) {
						$list = array();
						// Ancestor descendant
						if (!$h = $e['h']) {
							foreach ($nodes as $_n) {
								$this->findAncestors($_n, $e, $list);
							}
						}
						// parent > child
						else if ($h === '>') {
							foreach ($nodes as $_n) {
								if ((($parent = $_n->parent) && $parent->type === self::NODE)
									&& $this->nodeMatchExpression($parent, $e)
								) {
									$list[] = $parent;
								}
							}
						}
						// prev + next
						else if ($h === '+') {
							foreach ($nodes as $_n) {
								if (($prev = $_n->prev) && $this->nodeMatchExpression($prev, $e)) {
									$list[] = $prev;
								}
							}
						}
						// prev ~ siblings
						else if ($h === '~') {
							foreach ($nodes as $prev) {
								while (($prev = $prev->prev) !== null) {
									if ($this->nodeMatchExpression($prev, $e)) {
										$list[] = $prev;
									}
								}
							}
						}

						if (!$nodes = $list) {
							// nothing found
							break;
						}
					}
					if ($nodes) {
						return true;
					}
				}
			}
		}

		return false;
	}

	/**
	 * Checks if node matches the expression
	 *
	 * @param CDomNode $node Node to check
	 * @param array    $e    Expression structure
	 * @param bool     $tree Whether to check childs nodes
	 *
	 * @return bool
	 */
	protected function nodeMatchExpression($node, $e, &$tree = null)
	{
		$tree = true;

		// Element name
		if ($e['e'] !== '*' && $node->name !== $e['e']) {
			return false;
		}

		// Attributes
		if ($e['a']) {
			foreach ($e['a'] as $a) {
				$search = (string)$a[1];
				$has = ($attrs = $node->attributes) && isset($attrs->list[$a[0]]);

				// Attribute with any value
				if (!$eq = $a[2]) {
					if (!$has) {
						return false;
					}
					continue;
				}

				/** @noinspection PhpUndefinedMethodInspection */
				$val = $has ? $attrs->list[$a[0]]->value() : '';

				// Exactly not equal
				if ($eq === '!=') {
					if ($val === $search) {
						return false;
					}
				}
				// Speed up - empty val, but not empty search
				else if ($val === '' && $search !== '') {
					return false;
				}
				// Exactly equal
				else if ($eq === '=') {
					if ($val !== $search) {
						return false;
					}
				}
				// Containing a given substring
				else if ($eq === '*=') {
					if (strpos($val, $search) === false) {
						return false;
					}
				}
				// Space-separated values, one of which is exactly equal
				else if ($eq === '~=') {
					$regex = '/(?:^|\s)' . preg_quote($search, '/') . '(?:\s|$)/DSXu';
					if (!preg_match($regex, $val)) {
						return false;
					}
				}
				// Exactly equal at start
				else if ($eq === '^=') {
					if (strncmp($val, $search, strlen($search))) {
						return false;
					}
				}
				// Exactly equal at end
				else if ($eq === '$=') {
					if (substr_compare($val, $search, -strlen($search))) {
						return false;
					}
				}
				// Hyphen-separated list of values beginning (from the left) with a given string
				else if ($eq === '|=') {
					$regex = '/^' . preg_quote($search, '/') . '(?:-|$)/DSXu';
					if (!preg_match($regex, $val)) {
						return false;
					}
				}
			}
		}

		// Modifiers / pseudo-classes
		if ($e['m']) {
			foreach ($e['m'] as $m) {
				list($name, $value) = $m;
				// elements that have no children (including text nodes).
				if ($name === 'empty') {
					if ($node->nodes) {
						return false;
					}
				}
				// elements that are the parent of another element, including text nodes.
				else if ($name === 'parent') {
					if (!$node->nodes) {
						return false;
					}
				}
				// elements that are headers, like h1, h2, h3 and so on. (jQuery Selector Extensions)
				else if ($name === 'header') {
					if (!isset(self::$headers[$node->name])) {
						return false;
					}
				}
				// elements that contain the specified text (case-sensitive)
				else if ($name === 'contains') {
					if ($value !== '' && mb_strpos($node->text(), $value, null, CDom::CHARSET) === false) {
						$tree = false;
						return false;
					}
				}
				// elements that do not match the given selector.
				else if ($name === 'not') {
					if ($this->nodeMatchSelector($node, $value)) {
						return false;
					}
				}
				// elements which contain at least one element that matches the specified selector.
				else if ($name === 'has') {
					if (!$node->children) {
						return false;
					}
					// speed up
					if ($value[0][0]['e'] === '*' && count($value) === 1 && count($value[0]) === 1) {
						return true;
					}
					$res = false;
					foreach ($node->children as $child) {
						if ($this->nodeMatchSelector($child, $value)) {
							$res = true;
							break;
						}
					}
					if (!$res) {
						return false;
					}
				}

				/*
				 * Speed up for -childs checks (Structural pseudo-classes)
				 * search only if the parent is element or has multiple children
				 *
				 * @link http://www.w3.org/TR/css3-selectors/#structural-pseudos
				 */
				else if (!($parent = $node->parent) || ($parent->type !== self::NODE
					&& count($parent->children) < 2)
				) {
					return false;
				}

				// elements that are the first child of their parent.
				else if ($name === 'first-child') {
					if ($node->prev) {
						return false;
					}
				}
				// elements that are the last child of their parent.
				else if ($name === 'last-child') {
					if ($node->next) {
						return false;
					}
				}
				// elements that are the only child of their parent.
				else if ($name === 'only-child') {
					if ($node->prev || $node->next) {
						return false;
					}
				}
				// elements that are the nth-child of their parent.
				else if ($name === 'nth-child') {
					if (!$this->numberMatchNthRule($node->chid+1, $value)) {
						return false;
					}
				}
				// elements that are the nth-child of their parent from end.
				else if ($name === 'nth-last-child') {
					if (!$this->numberMatchNthRule(count($parent->children)-$node->chid, $value)) {
						return false;
					}
				}
				// elements that are the only child of their parent of specified type.
				else if ($name === 'only-of-type') {
					$chid = $node->chid;
					$type = $node->name;
					$res = false;
					$_node = $node->parent->firstChild;
					do {
						if ($_node->name === $type) {
							if ($res) {
								return false;
							} else if ($_node->chid === $chid) {
								$res = true;
							} else {
								return false;
							}
						}
					} while ($_node = $_node->next);
				}
				// group of type checks
				else if (!substr_compare($name, '-of-type', -8)) {
					if (strncmp($name, 'nth-', 4)) {
						$value = array(0, 1);
					}
					$break = !$value[0];
					$chid = $node->chid;
					$type = $node->name;
					$i = 1;
					/**
					 * nth-of-type, first-of-type
					 *
					 * :nth-of-type(an+b|even|odd)
					 * elements that are the nth-child of their parent of specified type.
					 *
					 * :first-of-type
					 * elements that are the first child of their parent of specified type.
					 */
					if (strpos($name, 'last-') === false) {
						$first = 'firstChild';
						$next = 'next';
					}
					/**
					 * nth-last-of-type, last-of-type
					 *
					 * :nth-last-of-type(an+b|even|odd)
					 * elements that are the nth-child of their parent of specified type from end.
					 *
					 * :last-of-type
					 * elements that are the last child of their parent of specified type.
					 */
					else {
						$first = 'lastChild';
						$next = 'prev';
					}

					/** @noinspection PhpUndefinedVariableInspection */
					$_node = $node->parent->$first;
					do {
						if ($_node->name === $type) {
							if ($_node->chid === $chid) {
								if (!$this->numberMatchNthRule($i, $value)) {
									return false;
								}
								break;
							}
							// speed up
							if ($break && $i >= $value[1]) {
								return false;
							}
							$i++;
						}
					} while ($_node = $_node->$next);
				}
			}
		}

		return true;
	}

	/**
	 * Checks if number matches the nth- rule.
	 * A number that has "ax+b-1" siblings before it.
	 *
	 * @link http://www.w3.org/TR/css3-selectors/#nth-child-pseudo
	 *
	 * @param int $n
	 * @param array $rule
	 *
	 * @return bool
	 */
	protected function numberMatchNthRule($n, $rule)
	{
		list($a, $b) = $rule;

		// an element that is at "ax+b" position
		// If both 'a' and 'b' are equal to zero, result is false
		// The value 'a' can be negative, but only the positive values of ax+b,
		// for x≥0, may represent an element in the document tree.
		return !$a ? $b == $n : !(($d = $n - $b) % $a) && $d / $a >= 0;
	}


	/**
	 * Finds descedant elements matches the expression
	 *
	 * @param CDomNode   $node  Node to search in
	 * @param array      $e     Expression structure
	 * @param CDomNode[] &$list Array with found nodes
	 */
	protected function findDescedants($node, $e, &$list = null)
	{
		$list !== null || $list = array();

		foreach ($node->children as $child) {
			if ($this->nodeMatchExpression($child, $e)) {
				$list[$child->uniqId] = $child;
			}
			$this->findDescedants($child, $e, $list);
		}
	}

	/**
	 * Finds ancestor elements matches the expression
	 *
	 * @param CDomNode   $node  low level node
	 * @param array      $e     Expression structure
	 * @param CDomNode[] &$list Array with found nodes
	 */
	protected function findAncestors($node, $e, &$list = null)
	{
		$list !== null || $list = array();

		if (($parent = $node->parent) && $parent->type === self::NODE) {
			if ($this->nodeMatchExpression($parent, $e)) {
				$list[] = $parent;
			}
			$this->findAncestors($parent, $e, $list);
		}
	}
}


/**
 * CDom list
 *
 * @see CDom
 *
 * @project Anizoptera CMF
 * @package system.CDom
 * @version $Id: CDom.php 2727 2011-10-19 09:11:46Z samally $
 */
abstract class CDomList implements Iterator, ArrayAccess, Countable
{
	/**
	 * Length of the list
	 */
	public $length = 0;

	/**
	 * Internal list
	 */
	public $list = array();


	/**
	 * Returns value
	 *
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function get($name)
	{
		return isset($this->list[$name]) ? $this->list[$name] : null;
	}

	/**
	 * Sets value
	 *
	 * @param string $name
	 * @param mixed $value
	 */
	abstract function set($name, $value);

	/**
	 * Returns if value exists
	 *
	 * @param string $name
	 *
	 * @return bool
	 */
	public function has($name)
	{
		return isset($this->list[$name]);
	}

	/**
	 * Removes value
	 *
	 * @param string $name
	 */
	abstract function delete($name);


	/**
	 * Converts list to string
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->html();
	}


	/**
	 * Returns text representation of list
	 *
	 * @return string
	 */
	abstract function text();

	/**
	 * Returns html representation of list
	 *
	 * @return string
	 */
	abstract function html();


	/**
	 * Returns value
	 *
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function __get($name)
	{
		return $this->get($name);
	}

	/**
	 * Sets value
	 *
	 * @param string $name
	 * @param mixed $value
	 */
	public function __set($name, $value)
	{
		return $this->set($name, $value);
	}

	/**
	 * Returns if value exists
	 *
	 * @param string $name
	 *
	 * @return bool
	 */
	public function __isset($name)
	{
		return $this->has($name);
	}

	/**
	 * Removes value
	 *
	 * @param string $name
	 */
	public function __unset($name)
	{
		return $this->delete($name);
	}


	/**
	 * Counts elements of an object
	 *
	 * @link http://php.net/manual/en/countable.count.php
	 *
	 * @return int
	 */
	public function count()
	{
		return count($this->list);
	}

	/**
	 * Return the current element
	 *
	 * @link http://php.net/manual/en/iterator.current.php
	 *
	 * @return mixed
	 */
	public function current()
	{
		return current($this->list);
	}

	/**
	 * Return the key of the current element
	 *
	 * @link http://php.net/manual/en/iterator.key.php
	 *
	 * @return string
	 */
	public function key()
	{
		return key($this->list);
	}

	/**
	 * Move forward to next element
	 *
	 * @link http://php.net/manual/en/iterator.next.php
	 *
	 * @return mixed
	 */
	public function next()
	{
		return next($this->list);
	}

	/**
	 * Whether a offset exists
	 *
	 * @link http://php.net/manual/en/arrayaccess.offsetexists.php
	 *
	 * @param mixed $offset
	 *
	 * @return bool
	 */
	public function offsetExists($offset)
	{
		return $this->has($offset);
	}

	/**
	 * Offset to retrieve
	 *
	 * @link http://php.net/manual/en/arrayaccess.offsetget.php
	 *
	 * @param mixed $offset
	 *
	 * @return mixed
	 */
	public function offsetGet($offset)
	{
		return $this->get($offset);
	}

	/**
	 * Offset to set
	 *
	 * @link http://php.net/manual/en/arrayaccess.offsetset.php
	 *
	 * @param mixed $offset
	 *
	 * @param mixed $value
	 */
	public function offsetSet($offset, $value)
	{
		return $this->set($offset, $value);
	}

	/**
	 * Offset to unset
	 *
	 * @link http://php.net/manual/en/arrayaccess.offsetunset.php
	 *
	 * @param mixed $offset
	 */
	public function offsetUnset($offset)
	{
		return $this->delete($offset);
	}

	/**
	 * Rewind the Iterator to the first element
	 *
	 * @link http://php.net/manual/en/iterator.rewind.php
	 *
	 * @return mixed
	 */
	public function rewind()
	{
		return reset($this->list);
	}

	/**
	 * Checks if current position is valid
	 *
	 * @link http://php.net/manual/en/iterator.valid.php
	 *
	 * @return bool
	 */
	public function valid()
	{
		return key($this->list) !== null;
	}
}


/**
 * CDom attributes list
 *
 * @see CDom
 * @see CDomAttribute
 *
 * @project Anizoptera CMF
 * @package system.CDom
 * @version $Id: CDom.php 2727 2011-10-19 09:11:46Z samally $
 *
 * @property CDomAttribute[] $list Internal attributes list
 */
class CDomAttributesList extends CDomList
{
	/**
	 * Owner node
	 *
	 * @var CDomNode|null
	 */
	public $node;


	/**
	 * Handles cloning
	 */
	public function __clone()
	{
		$node = null;
		$this->node = &$node;
		foreach ($this->list as &$attr) {
			$attr = clone $attr;
			$attr->node = &$node;
		}
	}


	/**
	 * Returns attribute value
	 *
	 * @param string $name
	 *
	 * @return CDomAttribute|null
	 */
	public function get($name)
	{
		$name_l = mb_strtolower($name, CDom::CHARSET);
		return isset($this->list[$name_l]) ? $this->list[$name_l] : null;
	}

	/**
	 * Sets attribute value
	 *
	 * @param string $name
	 * @param string $value
	 */
	public function set($name, $value)
	{
		$name_l = mb_strtolower($name, CDom::CHARSET);

		if (isset($this->list[$name_l])) {
			$attr = $this->list[$name_l];
			$attr->value($value);
			$attr->nameReal = $name;
		} else {
			$attr = new CDomAttribute($name_l, $value, $name);
			$attr->node = &$this->node;
			$this->list[$name_l] = $attr;
		}
	}

	/**
	 * Returns if attribute exists
	 *
	 * @param string $name
	 *
	 * @return bool
	 */
	public function has($name)
	{
		$name_l = mb_strtolower($name, CDom::CHARSET);
		return isset($this->list[$name_l]);
	}

	/**
	 * Removes attribute
	 *
	 * @param string $name
	 */
	public function delete($name)
	{
		$name_l = mb_strtolower($name, CDom::CHARSET);
		unset($this->list[$name_l]);
	}


	/**
	 * Returns text representation of list
	 *
	 * @return string
	 */
	public function text()
	{
		return $this->html();
	}

	/**
	 * Returns html representation of list
	 *
	 * @return string
	 */
	function html()
	{
		$html = '';

		foreach ($this->list as $attr) {
			$html .= ' ' . $attr->html();
		}

		return $html;
	}
}


/**
 * CDom attributes list
 *
 * @see CDom
 * @see CDomNode
 *
 * @project Anizoptera CMF
 * @package system.CDom
 * @version $Id: CDom.php 2727 2011-10-19 09:11:46Z samally $
 *
 * @method CDomNodeTag   get(int $n) Returns node by it's position in list.
 * @method CDomNodesList clone()     Create a deep copy of the set of elements.
 * @method CDomNodesList empty()     Remove all child nodes of the set of elements from the DOM.
 * @method int           size()      Returns size of elements in the list. Use 'length' property instead of this method.
 *
 * @property CDomNodeTag[]     $list       Internal nodes list.
 * @property CDomNodeTag|null  $first      First node in list.
 * @property CDomNodeTag |null $last       Last node in list.
 * @property CDomNodeTag|null  $prev       The node immediately preceding first node in the list. NULL if there is no such node.
 * @property CDomNodeTag|null  $next       The node immediately following first node in the list. NULL if there is no such node.
 * @property CDomNodeTag|null  $firstChild The first child of first node in the list. NULL if there is no such node.
 * @property CDomNodeTag|null  $lastChild  The last child of first node in the list. NULL if there is no such node.
 * @property CDomNode|null     $firstNode  First child node of first node in the list. NULL if there is no such node.
 * @property CDomNode|null     $lastNode   Last child node of first node in the list. NULL if there is no such node.
 */
class CDomNodesList extends CDomList
{
	/**
	 * Internal list by node unique IDs
	 *
	 * @var CDomNodeTag[]
	 */
	public $listByIds = array();

	/**
	 * CDomDocument object associated with this list.
	 *
	 * @var CDomDocument
	 */
	public $ownerDocument;

	/**
	 * CDomNode context of this list.
	 *
	 * @var CDomNode
	 */
	public $context;

	/**
	 * Previous list state
	 *
	 * @var array|null
	 */
	protected $state;


	/**
	 * Constructs list
	 *
	 * @param CDomNode $context
	 */
	public function __construct($context = null)
	{
		if ($context) {
			$this->context = $context;
			$this->ownerDocument = $context->ownerDocument;
		}
	}


	/**
	 * Handles magic calls to really undefined methods
	 *
	 * @throws BadMethodCallException
	 *
	 * @param string $name
	 * @param array  $arguments
	 *
	 * @return mixed
	 */
	public function __call($name, $arguments)
	{
		$name_l = strtolower($name);

		// clone
		if ($name_l === 'clone') {
			return clone $this;
		}

		// empty
		else if ($name_l === 'empty') {
			foreach ($this->list as $node) {
				$node->cleanChildren();
			}
			return $this;
		}

		// size
		else if ($name_l === 'size') {
			return $this->length;
		}

		throw new BadMethodCallException('Method "'.get_class($this).'.'.$name.'" is not defined.');
	}

	/**
	 * Handles magic usages of really undefined parameters
	 *
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function &__get($name)
	{
		$name_l = strtolower($name);

		// Last
		if ($name_l === 'last') {
			$res = ($k = $this->length) ? $this->list[$k-1] : null;
			return $res;
		}
		// First, attributes and properties
		else if (isset($this->list[0])) {
			$first = $this->list[0];
			// First
			if ($name_l === 'first') {
				return $first;
			}
			// Properties
			else if (isset($first->$name)) {
				return $first->$name;
			}
			// Attributes
			$val = $this->list[0]->attr($name_l, null, true);
			return $val;
		}

		$res = null;
		return $res;
	}

	/**
	 * Handles magic usages of really undefined parameters.
	 *
	 * @see attr()
	 *
	 * @param string $name
	 * @param string $value
	 */
	public function __set($name, $value)
	{
		if (isset($this->list[0])) {
			$first = $this->list[0];
			// Properties
			if (isset($first->$name)) {
				$first->$name = $value;
			}
			// Attributes
			else {
				$this->list[0]->attr($name, $value);
			}
		}
	}


	/**
	 * Handles cloning
	 */
	public function __clone()
	{
		$this->listByIds = array();
		foreach ($this->list as &$node) {
			$node = clone $node;
			$this->listByIds[$node->uniqId] = $node;
		}
	}


	/**
	 * Returns the descendants of each element in the current set of
	 * matched elements, filtered by a selector.
	 *
	 * @see find
	 *
	 * @link http://api.jquery.com/find/
	 *
	 * @param string|CDomSelector $selector CSS selector
	 * @param int    $n        Index of element in set to return
	 *
	 * @return CDomNodeTag[]|CDomNodesList|CDomNodeTag Nodes list or
	 * one node if index is specified
	 */
	public function __invoke($selector, $n = null)
	{
		return $this->find($selector, $n);
	}


	/**
	 * Converts list to string
	 *
	 * @see htmlAll
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->htmlAll();
	}



	/**
	 * Add element(s) to the set of matched elements.
	 *
	 * @link http://api.jquery.com/add/
	 *
	 * @param CDomNode|CDomNodesList|CDomNode[] $value
	 *
	 * @return CDomNodeTag[]|CDomNodesList
	 */
	public function add($value)
	{
		if ($value instanceof self) {
			$value = $value->list;
		}
		if (is_array($value)) {
			foreach ($value as $node) {
				if (!isset($this->listByIds[$uid = $node->uniqId])) {
					$this->listByIds[$uid] = $node;
					$this->list[] = $node;
				}
			}
		} else if (!isset($this->listByIds[$uid = $value->uniqId])) {
			$this->listByIds[$uid] = $value;
			$this->list[] = $value;
		}
		$this->length = count($this->list);
		return $this;
	}

	/**
	 * Removes node from set by it's position
	 *
	 * @param int|CDomNode $n
	 *
	 * @return CDomNodeTag[]|CDomNodesList
	 */
	public function delete($n)
	{
		if (!($n instanceof CDomNode)) {
			$n = isset($this->list[$n]) ? $this->list[$n] : null;
		}
		if ($n && isset($this->listByIds[$n->uniqId])) {
			unset($this->listByIds[$n->uniqId]);
			$this->list = array_values($this->listByIds);
			$this->length = count($this->list);
		}
		return $this;
	}

	/**
	 * Don't use this method :)
	 *
	 * @param string $name
	 * @param CDomNode $value
	 *
	 * @return CDomNodeTag[]|CDomNodesList
	 */
	public function set($name, $value)
	{
		return $this;
	}



	/**
	 * Returns or sets the text contents of the first element in
	 * the set of matched elements.
	 *
	 * @link http://api.jquery.com/text/
	 *
	 * @see CDomNode::text
	 *
	 * @param string|CDomNode|CDomNode[]|CDomNodesList $value
	 *
	 * @return string|CDomNodeTag[]|CDomNodesList
	 */
	public function text($value = null)
	{
		if ($this->length > 0) {
			$res = $this->list[0]->text($value);
			return ($value === null) ? $res : $this;
		}
		return ($value === null) ? '' : $this;
	}

	/**
	 * Returns or sets the HTML contents of the first element in the set.
	 *
	 * @link http://api.jquery.com/html/
	 *
	 * @see CDomNode::html
	 *
	 * @param string|CDomNode|CDomNode[]|CDomNodesList $value
	 *
	 * @return string|CDomNodeTag[]|CDomNodesList
	 */
	public function html($value = null)
	{
		if ($this->length > 0) {
			$res = $this->list[0]->html($value);
			return ($value === null) ? $res : $this;
		}
		return ($value === null) ? '' : $this;
	}

	/**
	 * Returns the outer HTML of the first element in the set.
	 *
	 * @see CDomNode::outerHtml
	 *
	 * @return string
	 */
	public function outerHtml()
	{
		if ($this->length > 0) {
			return $this->list[0]->outerHtml();
		}
		return '';
	}


	/**
	 * Returns the combined text contents of all elements in the matched set.
	 *
	 * @see CDomNode::text
	 *
	 * @return string
	 */
	public function textAll()
	{
		$string = '';
		foreach ($this->list as $node) {
			$string .= $node->text();
		}
		return $string;
	}

	/**
	 * Returns the combined HTML contents of all elements in the matched set.
	 *
	 * @see CDomNode::html
	 *
	 * @return string
	 */
	public function htmlAll()
	{
		$string = '';
		foreach ($this->list as $node) {
			$string .= $node->html();
		}
		return $string;
	}

	/**
	 * Returns the combined outer HTML of all elements in the matched set.
	 *
	 * @see CDomNode::outerHtml
	 *
	 * @return string
	 */
	public function outerHtmlAll()
	{
		$string = '';
		foreach ($this->list as $node) {
			$string .= $node->outerHtml();
		}
		return $string;
	}



	/**
	 * Returns or sets the value of an attribute for
	 * the first element in the set of matched elements.
	 *
	 * @link http://api.jquery.com/attr/
	 *
	 * @param string      $name
	 * @param string|bool $value
	 * @param bool        $toString Whether to convert result to string
	 *
	 * @return CDomAttribute|string|null|CDomNodeTag[]|$this
	 */
	public function attr($name, $value = null, $toString = true)
	{
		if (isset($this->list[0])) {
			return $this->list[0]->attr($name, $value, $toString);
		}
		return $value === null ? null : $this;
	}

	/**
	 * Removes an attribute from each element in the set of matched elements.
	 *
	 * @link http://api.jquery.com/removeAttr/
	 *
	 * @param string $name
	 *
	 * @return CDomNodeTag[]|CDomNodesList
	 */
	public function removeAttr($name)
	{
		foreach ($this->list as $node) {
			$node->attributes && $node->attributes->delete($name);
		}
		return $this;
	}

	/**
	 * Returns if first element in the set has the specified attribite
	 *
	 * @param string $name
	 *
	 * @return bool
	 */
	public function hasAttribute($name)
	{
		/** @noinspection PhpUndefinedMethodInspection */
		return isset($this->list[0])
			   && $this->list[0]->attributes
			   && $this->list[0]->attributes->has($name);
	}



	/**
	 * Prepares content, for manipulation.
	 *
	 * @param string|CDomNode|CDomNode[]|CDomDocument|CDomNodesList $content
	 *
	 * @return CDomNodesList|false returns FALSE if content is empty
	 */
	protected function prepareContent($content)
	{
		if (!($content instanceof CDomNodesList)) {
			// Document
			if ($content instanceof CDomDocument) {
				$content = &$content->detachChildren();
			}
			// String
			else if (!is_object($content) && !is_array($content)) {
				$content = CDom::fromString($content);
				$content = &$content->detachChildren();
			}
			$list = new self;
			$list->add($content);
			$content = $list;
		}
		return $content->length ? $content : false;
	}

	/**
	 * Prepares and checks targets
	 *
	 * @param string|CDomNode|CDomNode[]|CDomNodesList $target
	 * 		CSS selector or target object(s)
	 *
	 * @return CDomNode[]|bool returns FALSE if error
	 */
	protected function prepareTarget($target)
	{
		if (is_string($target)) {
			if (!isset($this->list[0])
				|| !$od = $this->list[0]->ownerDocument
			) {
				return false;
			}
			$target = new CDomSelector($target);
			$target = $target->find($od);
		}
		if (!is_array($target)) {
			$target = ($target instanceof CDomNodesList)
					? $target->list
					: array($target);
		}
		return $target;
	}


	/**
	 * Insert content, specified by the parameter, after
	 * each element in the set of matched elements.
	 *
	 * @link http://api.jquery.com/after/
	 *
	 * @param string|CDomNode|CDomNode[]|CDomDocument|CDomNodesList $content
	 *
	 * @return CDomNodeTag[]|CDomNodesList
	 */
	public function after($content)
	{
		if ($content = $this->prepareContent($content)) {
			foreach ($this->list as $i => $node) {
				if ($i !== 0) {
					$content = clone $content;
				}
				$node->after($content->list);
			}
		}
		return $this;
	}

	/**
	 * Insert content, specified by the parameter,
	 * before each element in the set of matched elements.
	 *
	 * @link http://api.jquery.com/before/
	 *
	 * @param string|CDomNode|CDomNode[]|CDomDocument|CDomNodesList $content
	 *
	 * @return CDomNodeTag[]|CDomNodesList
	 */
	public function before($content)
	{
		if ($content = $this->prepareContent($content)) {
			foreach ($this->list as $i => $node) {
				if ($i !== 0) {
					$content = clone $content;
				}
				$node->before($content->list);
			}
		}
		return $this;
	}


	/**
	 * Insert every element in the set of matched elements after the target.
	 *
	 * @link http://api.jquery.com/insertAfter/
	 *
	 * @param string|CDomNode|CDomNode[]|CDomNodesList $target
	 * 			CSS selector or target object(s)
	 *
	 * @return CDomNodeTag[]|CDomNodesList
	 */
	public function insertAfter($target)
	{
		if ($target = $this->prepareTarget($target)) {
			/** @var $list CDomNode[] */
			$list = array_reverse($this->resetState());
			foreach ($list as $node) {
				$res = $node->insertAfter($target);
				$this->add($res);
			}
			$this->list = array_reverse($this->list);
			$this->listByIds = array_reverse($this->listByIds, true);
		}
		return $this;
	}

	/**
	 * Insert every element in the set of matched elements before the target.
	 *
	 * @link http://api.jquery.com/insertBefore/
	 *
	 * @param string|CDomNode|CDomNode[]|CDomNodesList $target
	 * 			CSS selector or target object(s)
	 *
	 * @return CDomNodeTag[]|CDomNodesList
	 */
	public function insertBefore($target)
	{
		if ($target = $this->prepareTarget($target)) {
			$list = $this->resetState();
			foreach ($list as $node) {
				$res = $node->insertBefore($target);
				$this->add($res);
			}
		}
		return $this;
	}


	/**
	 * Insert content, specified by the parameter,
	 * to the end of each element in the set.
	 *
	 * @link http://api.jquery.com/append/
	 *
	 * @param string|CDomNode|CDomNode[]|CDomDocument|CDomNodesList $content
	 *
	 * @return CDomNodeTag[]|CDomNodesList
	 */
	public function append($content)
	{
		if ($content = $this->prepareContent($content)) {
			foreach ($this->list as $i => $node) {
				if ($i !== 0) {
					$content = clone $content;
				}
				$node->append($content->list);
			}
		}
		return $this;
	}

	/**
	 * Insert content, specified by the parameter, to the beginning
	 * of each element in the set of matched elements.
	 *
	 * @link http://api.jquery.com/prepend/
	 *
	 * @param string|CDomNode|CDomNode[]|CDomDocument|CDomNodesList $content
	 *
	 * @return CDomNodeTag[]|CDomNodesList
	 */
	public function prepend($content)
	{
		if ($content = $this->prepareContent($content)) {
			foreach ($this->list as $i => $node) {
				if ($i !== 0) {
					$content = clone $content;
				}
				$node->prepend($content->list);
			}
		}
		return $this;
	}


	/**
	 * Insert every element in the set of matched elements to the end
	 * of the target.
	 *
	 * @link http://api.jquery.com/appendTo/
	 *
	 * @param string|CDomNode|CDomNode[]|CDomNodesList $target
	 * 			CSS selector or target object(s)
	 *
	 * @return CDomNodeTag[]|CDomNodesList
	 */
	public function appendTo($target)
	{
		if ($target = $this->prepareTarget($target)) {
			$list = $this->resetState();
			foreach ($list as $node) {
				$res = $node->appendTo($target);
				$this->add($res);
			}
		}
		return $this;
	}

	/**
	 * Insert every element in the set of matched elements to the
	 * beginning of the target.
	 *
	 * @link http://api.jquery.com/prependTo/
	 *
	 * @param string|CDomNode|CDomNode[]|CDomNodesList $target
	 * 			CSS selector or target object(s)
	 *
	 * @return CDomNodeTag[]|CDomNodesList
	 */
	public function prependTo($target)
	{
		if ($target = $this->prepareTarget($target)) {
			/** @var $list CDomNode[] */
			$list = array_reverse($this->resetState());
			foreach ($list as $node) {
				$res = $node->prependTo($target);
				$this->add($res);
			}
			$this->list = array_reverse($this->list);
			$this->listByIds = array_reverse($this->listByIds, true);
		}
		return $this;
	}


	/**
	 * Replace each target element with the set of matched elements.
	 *
	 * @link http://api.jquery.com/replaceAll/
	 *
	 * @param string|CDomNode|CDomNode[]|CDomNodesList $target
	 * 			CSS selector or target object(s)
	 *
	 * @return CDomNodeTag[]|CDomNodesList
	 */
	public function replaceAll($target)
	{
		if ($target = $this->prepareTarget($target)) {
			$list = $this->resetState();
			$last = null;
			foreach ($list as $i => $node) {
				if ($i === 0) {
					$res = $node->replaceAll($target);
				} else {
					$res = $node->insertAfter($last);
				}
				$last = $res;
				$this->add($res);
			}
		}
		return $this;
	}

	/**
	 * Replace each element in the set of matched elements with the
	 * provided new content.
	 *
	 * @link http://api.jquery.com/replaceWith/
	 *
	 * @param string|CDomNode|CDomNode[]|CDomDocument|CDomNodesList $content
	 *
	 * @return CDomNodeTag[]|CDomNodesList
	 */
	public function replaceWith($content)
	{
		if ($content = $this->prepareContent($content)) {
			foreach ($this->list as $i => $node) {
				if ($i !== 0) {
					$content = clone $content;
				}
				$node->replaceWith($content->list);
			}
		}
		return $this;
	}


	/**
	 * Wrap an HTML structure around each element in the set
	 * of matched elements.
	 *
	 * @link http://api.jquery.com/wrap/
	 *
	 * @param string|CDomNode|CDomNode[]|CDomDocument|CDomNodesList $content
	 * 			CSS Selector, HTML code or object(s)
	 *
	 * @return CDomNodeTag[]|CDomNodesList
	 */
	public function wrap($content)
	{
		if ($content = $this->prepareContent($content)) {
			$content = $content[0];
			foreach ($this->list as $i => $node) {
				if ($i !== 0) {
					$content = clone $content;
				}
				$node->wrap($content);
			}
		}
		return $this;
	}

	/**
	 * Wrap an HTML structure around all elements in the set
	 * of matched elements.
	 *
	 * @link http://api.jquery.com/wrapAll/
	 *
	 * @param string|CDomNode|CDomNode[]|CDomDocument|CDomNodesList $content
	 * 			CSS Selector, HTML code or object(s)
	 *
	 * @return CDomNodeTag[]|CDomNodesList
	 */
	public function wrapAll($content)
	{
		if (isset($this->list[0]) && $content = $this->prepareContent($content)) {
			$first = $this->list[0];
			if ($p = $first->parent) {
				$content = $content[0];
				$content->insertBefore($first)->append($this->list);
			}
		}
		return $this;
	}

	/**
	 * Wrap an HTML structure around the content of each element
	 * in the set of matched elements.
	 *
	 * @link http://api.jquery.com/wrapInner/
	 *
	 * @param string|CDomNode|CDomNode[]|CDomDocument|CDomNodesList $content
	 * 			CSS Selector, HTML code or object(s)
	 *
	 * @return CDomNodeTag[]|CDomNodesList
	 */
	public function wrapInner($content)
	{
		if ($content = $this->prepareContent($content)) {
			$content = $content[0];
			foreach ($this->list as $i => $node) {
				if ($i !== 0) {
					$content = clone $content;
				}
				$node->wrapInner($content);
			}
		}
		return $this;
	}


	/**
	 * Remove the parents of the set of matched elements from
	 * the DOM, leaving the matched elements in their place.
	 *
	 * @link http://api.jquery.com/unwrap/
	 *
	 * @return CDomNodeTag[]|CDomNodesList
	 */
	public function unwrap()
	{
		foreach ($this->list as $node) {
			$node->unwrap();
		}
		return $this;
	}



	/**
	 * Remove the set of matched elements from the DOM.
	 *
	 * @link http://api.jquery.com/detach/
	 *
	 * @return CDomNodeTag[]|CDomNodesList
	 */
	public function detach()
	{
		foreach ($this->list as $node) {
			$node->detach();
		}
		return $this;
	}

	/**
	 * Removes the set of matched elements from the DOM and memory.
	 *
	 * WARNING: Nodes will be damaged for further use!
	 *
	 * @link http://api.jquery.com/remove/
	 *
	 * @return CDomNodeTag[]|CDomNodesList
	 */
	public function remove()
	{
		foreach ($this->list as $node) {
			$node->remove();
		}
		$this->resetState();
		$this->state = null;
		return $this;
	}



	/**
	 * Prepares list for traversing. Saves current state.
	 *
	 * @return CDomNodeTag[]
	 */
	protected function saveState()
	{
		$list = $this->list;
		$this->state = array(
			$this->listByIds,
			$list,
		);
		$this->listByIds = array();
		$this->list      = array();
		$this->length    = 0;
		return $list;
	}

	/**
	 * Resets list of matched nodes.
	 *
	 * @return CDomNodeTag[]
	 */
	protected function resetState()
	{
		$list = $this->list;
		$this->listByIds = array();
		$this->list      = array();
		$this->length    = 0;
		return $list;
	}

	/**
	 * End the most recent filtering operation in the current chain
	 * and return the set of matched elements to its previous state.
	 *
	 * @link http://api.jquery.com/end/
	 *
	 * @return CDomNodeTag[]|CDomNodesList
	 */
	public function end()
	{
		if ($this->state) {
			list(
				$this->listByIds,
				$this->list
			) = $this->state;
			$this->length = count($this->list);
		}
		return $this;
	}


	/**
	 * Adds the previous set of elements on the stack
	 * to the current set.
	 *
	 * @link http://api.jquery.com/andSelf/
	 *
	 * @return CDomNodeTag[]|CDomNodesList
	 */
	public function andSelf()
	{
		if ($this->state) {
			$this->add($this->state[0]);
		}
		return $this;
	}


	/**
	 * Reduces the set of matched elements to the one at the specified index.
	 *
	 * @link http://api.jquery.com/eq/
	 *
	 * @param int $n Index of element. Can be negative to get
	 * 			element from end of the list
	 *
	 * @return CDomNodeTag[]|CDomNodesList
	 */
	public function eq($n)
	{
		$list = $this->saveState();
		$n >= 0 || $n = count($list) + $n;
		if ($el = isset($list[$n]) ? $list[$n] : null) {
			$this->add($el);
		}
		return $this;
	}

	/**
	 * Reduces the set of matched elements to the first in the set.
	 *
	 * @link http://api.jquery.com/first/
	 *
	 * @return CDomNodeTag[]|CDomNodesList
	 */
	public function first()
	{
		$list = $this->saveState();
		if ($el = reset($list)) {
			$this->add($el);
		}
		return $this;
	}

	/**
	 * Reduces the set of matched elements to the final one in the set.
	 *
	 * @link http://api.jquery.com/last/
	 *
	 * @return CDomNodeTag[]|CDomNodesList
	 */
	public function last()
	{
		$list = $this->saveState();
		if ($el = end($list)) {
			$this->add($el);
		}
		return $this;
	}


	/**
	 * Reduces the set of matched elements to a subset
	 * specified by a range of indices.
	 *
	 * @see array_slice
	 *
	 * @link http://api.jquery.com/slice/
	 *
	 * @param int $offset If offset is non-negative, the sequence will
	 * start at that offset in the array. If offset is negative,
	 * the sequence will start that far from the end of the array.
	 *
	 * @param int $length [optional] If length is given and is positive,
	 * then the sequence will have that many elements in it. If
	 * length is given and is negative then the
	 * sequence will stop that many elements from the end of the
	 * array. If it is omitted, then the sequence will have everything
	 * from offset up until the end of the array.
	 *
	 * @return CDomNodeTag[]|CDomNodesList
	 */
	public function slice($offset, $length = null)
	{
		$list = $this->saveState();
		if ($list = array_slice($list, $offset, $length)) {
			$this->add($list);
		}
		return $this;
	}


	/**
	 * Search for a given element from among the matched elements.
	 *
	 * @link http://api.jquery.com/index/
	 *
	 * @param CDomNode|CDomNodesList $node
	 *
	 * @return int Numerical index of element in the list or -1
	 * if it's not found.
	 */
	public function index($node)
	{
		if ($node instanceof CDomNodesList) {
			$node = reset($node->list);
		}
		if ($node && isset($this->listByIds[$uid = $node->uniqId])) {
			$i = 0;
			foreach ($this->listByIds as $uniqId => $n) {
				if ($uniqId === $uid) {
					return $i;
				}
				$i++;
			}
		// @codeCoverageIgnoreStart
		}
		// @codeCoverageIgnoreEnd
		return -1;
	}


	/**
	 * Returns the descendants of each element in the current set of
	 * matched elements, filtered by a selector.
	 *
	 * @link http://api.jquery.com/find/
	 *
	 * @param string|CDomSelector $selector CSS selector
	 * @param int    $n        Index of element in set to return
	 *
	 * @return CDomNodeTag[]|CDomNodesList|CDomNodeTag Nodes list or
	 * one node if index is specified
	 */
	public function find($selector, $n = null)
	{
		if (is_string($selector)) {
			$selector = new CDomSelector($selector);
		}
		foreach ($this->saveState() as $node) {
			$selector->find($node, $n, $this);
			if ($n && $this->length > $n) {
				break;
			}
		}
		return $n === null ? $this : $this->list[$n];
	}

	/**
	 * Reduce the set of matched elements to those that
	 * match the selector.
	 *
	 * @link http://api.jquery.com/filter/
	 *
	 * @param string|CDomSelector $selector  CSS selector
	 * @param bool                $saveState Whether to save previous
	 *
	 * @return CDomNodeTag[]|CDomNodesList
	 */
	public function filter($selector, $saveState = true)
	{
		if (is_string($selector)) {
			$selector = new CDomSelector($selector);
		}
		$list = $saveState ? $this->saveState() : $this->resetState();
		foreach ($list as $node) {
			if ($selector->match($node)) {
				$this->add($node);
			}
		}
		return $this;
	}

	/**
	 * Remove elements from the set of matched elements.
	 *
	 * @link http://api.jquery.com/not/
	 *
	 * @param string|CDomSelector $selector CSS selector
	 *
	 * @return CDomNodeTag[]|CDomNodesList
	 */
	public function not($selector)
	{
		if (is_string($selector)) {
			$selector = new CDomSelector($selector);
		}
		foreach ($this->saveState() as $node) {
			if (!$selector->match($node)) {
				$this->add($node);
			}
		}
		return $this;
	}

	/**
	 * Check the current matched set of elements against a selector
	 * and return true if at least one of these elements matches the
	 * given arguments.
	 *
	 * @link http://api.jquery.com/is/
	 *
	 * @param string|CDomSelector $selector CSS selector
	 *
	 * @return bool
	 */
	public function is($selector)
	{
		if (is_string($selector)) {
			$selector = new CDomSelector($selector);
		}
		foreach ($this->list as $node) {
			if ($selector->match($node)) {
				return true;
			}
		}
		return false;
	}


	/**
	 * Get the children of each element in the set of matched elements,
	 * optionally filtered by a selector.
	 *
	 * @link http://api.jquery.com/children/
	 *
	 * @param string|CDomSelector $selector CSS selector
	 *
	 * @return CDomNodeTag[]|CDomNodesList
	 */
	public function children($selector = null)
	{
		foreach ($this->saveState() as $node) {
			$node->children(null, $this);
		}
		if ($selector) {
			$this->filter($selector, false);
		}
		return $this;
	}

	/**
	 * Get the children nodes of each element in the set of matched elements,
	 * including text and comment nodes.
	 *
	 * @link http://api.jquery.com/contents/
	 *
	 * @return CDomNodeTag[]|CDomNodesList
	 */
	public function contents()
	{
		foreach ($this->saveState() as $node) {
			if ($node->nodes) {
				$this->add($node->nodes);
			}
		}
		return $this;
	}


	/**
	 * Get the immediately following sibling of each element in the
	 * set of matched elements, optionally filtered by a selector.
	 *
	 * @link http://api.jquery.com/next/
	 *
	 * @param string|CDomSelector $selector CSS selector
	 *
	 * @return CDomNodeTag[]|CDomNodesList
	 */
	public function getNext($selector = null)
	{
		if (is_string($selector)) {
			$selector = new CDomSelector($selector);
		}
		foreach ($this->saveState() as $node) {
			if (($next = $node->next) && (!$selector || $selector->match($next))) {
				$this->add($next);
			}
		}
		return $this;
	}

	/**
	 * Get all following siblings of each element in the set of
	 * matched elements, optionally filtered by a selector.
	 *
	 * @link http://api.jquery.com/nextAll/
	 *
	 * @param string|CDomSelector $selector CSS selector
	 *
	 * @return CDomNodeTag[]|CDomNodesList
	 */
	public function nextAll($selector = null)
	{
		if (is_string($selector)) {
			$selector = new CDomSelector($selector);
		}
		foreach ($this->saveState() as $node) {
			$node->nextAll($selector, $this);
		}
		return $this;
	}

	/**
	 * Get all following siblings of each element up to but not including
	 * the element matched by the selector.
	 *
	 * @link http://api.jquery.com/nextUntil/
	 *
	 * @param string|CDomSelector $selector CSS selector
	 *
	 * @return CDomNodeTag[]|CDomNodesList
	 */
	public function nextUntil($selector)
	{
		if (is_string($selector)) {
			$selector = new CDomSelector($selector);
		}
		foreach ($this->saveState() as $node) {
			$node->nextUntil($selector, $this);
		}
		return $this;
	}


	/**
	 * Get the immediately preceding sibling of each element in the set
	 * of matched elements, optionally filtered by a selector.
	 *
	 * @link http://api.jquery.com/prev/
	 *
	 * @param string|CDomSelector $selector CSS selector
	 *
	 * @return CDomNodeTag[]|CDomNodesList
	 */
	public function getPrev($selector = null)
	{
		if (is_string($selector)) {
			$selector = new CDomSelector($selector);
		}
		foreach ($this->saveState() as $node) {
			if (($prev = $node->prev) && (!$selector || $selector->match($prev))) {
				$this->add($prev);
			}
		}
		return $this;
	}

	/**
	 * Get all preceding siblings of each element in the set of matched
	 * elements, optionally filtered by a selector.
	 *
	 * @link http://api.jquery.com/prevAll/
	 *
	 * @param string|CDomSelector $selector CSS selector
	 *
	 * @return CDomNodeTag[]|CDomNodesList
	 */
	public function prevAll($selector = null)
	{
		if (is_string($selector)) {
			$selector = new CDomSelector($selector);
		}
		foreach ($this->saveState() as $node) {
			$node->prevAll($selector, $this);
		}
		return $this;
	}

	/**
	 * Get all preceding siblings of each element up to but not including
	 * the element matched by the selector.
	 *
	 * @link http://api.jquery.com/prevUntil/
	 *
	 * @param string|CDomSelector $selector CSS selector
	 *
	 * @return CDomNodeTag[]|CDomNodesList
	 */
	public function prevUntil($selector)
	{
		if (is_string($selector)) {
			$selector = new CDomSelector($selector);
		}
		foreach ($this->saveState() as $node) {
			$node->prevUntil($selector, $this);
		}
		return $this;
	}


	/**
	 * Get the parent of each element in the current set of
	 * matched elements, optionally filtered by a selector.
	 *
	 * @link http://api.jquery.com/parent/
	 *
	 * @param string|CDomSelector $selector CSS selector
	 *
	 * @return CDomNodeTag[]|CDomNodesList
	 */
	public function parent($selector = null)
	{
		if (is_string($selector)) {
			$selector = new CDomSelector($selector);
		}
		foreach ($this->saveState() as $node) {
			if (($parent = $node->parent) && (!$selector || $selector->match($parent))) {
				$this->add($parent);
			}
		}
		return $this;
	}

	/**
	 * Get the ancestors of each element in the current set of
	 * matched elements, optionally filtered by a selector.
	 *
	 * @link http://api.jquery.com/parents/
	 *
	 * @param string|CDomSelector $selector CSS selector
	 *
	 * @return CDomNodeTag[]|CDomNodesList
	 */
	public function parents($selector = null)
	{
		if (is_string($selector)) {
			$selector = new CDomSelector($selector);
		}
		foreach ($this->saveState() as $node) {
			$node->parents($selector, $this);
		}
		return $this;
	}

	/**
	 * Get the ancestors of each element in the current set of matched
	 * elements, up to but not including the element matched by the selector.
	 *
	 * @link http://api.jquery.com/parentsUntil/
	 *
	 * @param string|CDomSelector $selector CSS selector
	 *
	 * @return CDomNodeTag[]|CDomNodesList
	 */
	public function parentsUntil($selector)
	{
		if (is_string($selector)) {
			$selector = new CDomSelector($selector);
		}
		foreach ($this->saveState() as $node) {
			$node->parentsUntil($selector, $this);
		}
		return $this;
	}


	/**
	 * Returns the first ancestor element that matches the selector,
	 * beginning at the first element in the current matched set
	 * and progressing up through the DOM tree.
	 *
	 * @link http://api.jquery.com/closest/
	 *
	 * @param string|CDomSelector $selector CSS selector
	 *
	 * @return CDomNodeTag|null
	 */
	public function closest($selector)
	{
		if (isset($this->list[0])) {
			return $this->list[0]->closest($selector);
		}
		return null;
	}


	/**
	 * Get the siblings of each element in the set of matched elements,
	 * optionally filtered by a selector.
	 *
	 * @link http://api.jquery.com/siblings/
	 *
	 * @param string|CDomSelector $selector CSS selector
	 *
	 * @return CDomNodeTag[]|CDomNodesList
	 */
	public function siblings($selector = null)
	{
		if (is_string($selector)) {
			$selector = new CDomSelector($selector);
		}
		foreach ($this->saveState() as $node) {
			$node->siblings($selector, $this);
		}
		return $this;
	}



	/**
	 * Debugging output of DOM list
	 *
	 * @codeCoverageIgnore
	 *
	 * @param bool $attributes
	 * @param bool $text_nodes
	 */
	public function dump($attributes = true, $text_nodes = true)
	{
		if ($this->length) {
			foreach ($this->list as $node) {
				$node->dump($attributes, $text_nodes);
			}
		} else {
			echo "\n";
		}
		echo 'NodesList dump: ' . $this->length . "\n";
		PHP_SAPI === 'cli' && ob_get_level() > 0 && @ob_flush();
	}
}
