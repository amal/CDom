<?php

require_once __DIR__ . '/../CDom.php';


/**
 * Testing CDom
 *
 * @project Anizoptera CMF
 * @package system.CDom
 * @version $Id: Test_Dom.php 2727 2011-10-19 09:11:46Z samally $
 */
class Test_Dom extends PHPUnit_Framework_TestCase
{
	protected $test_html = <<<'HTML'
<div>
	<img class="class0" id="id0" src="src0">
	<img class="class1" id="id1" src="src1">
	<img class="class2" id="id2" src="src2">
</div>
HTML;

	protected $test_html2 = <<<'HTML'
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Example HTML 5 document</title>
		<!-- Comment -->
	</head>
	<body>
		<div id="main" class=container>
			<br>
			<list class="list">
				<li id="li1">li1 text</li>
				<li id="li2"></li>
				<li id="li3">li1 text</li>
				<li id="li4" class="item">li1 text</li>
			</list>
			<hr>
			<p id="p1"></p>
			<p id="p2" />
			<p id="p3">p3 text</p>
			<h1>header1</h1>
			<h6></h6>
		</div>
	</body>
</html>
HTML;

	/**
	 * @var CDomDocument
	 */
	protected $dom;


	/**
	 * This method is called after the last test of this test class is run.
	 */
	protected function tearDown()
	{
		if ($this->dom) {
			$this->dom->clean();
		}
		$this->dom = null;
		gc_collect_cycles();
	}


	/**
	 * Finds elements by selector.
	 *
	 * @param string		$selector
	 * @param int			$n
	 * @param CDomDocument	$dom
	 *
	 * @return CDomNodeTag[]|CDomNodesList|CDomNodeTag
	 */
	protected function find($selector, $n = null, $dom = null)
	{
		$dom || $dom = $this->dom;
		/** @var $res CDomNodeTag[]|CDomNodeTag */
		$res = $dom->find($selector, $n);
		if ($n === null) {
			foreach ($res as $node) {
				$this->assertTrue($node->is($selector));
			}
		} else {
			$this->assertTrue($res->is($selector));
		}
		return $res;
	}

	/**
	 * Checks node structure
	 *
	 * @param CDomNode $node
	 * @param bool     $recursion
	 */
	protected function checkStructure($node, $recursion = false)
	{
		static $ids;

		$ownerDocument = $node->ownerDocument;
		$this->assertTrue(!$ownerDocument || $ownerDocument instanceof CDomDocument);
		$this->assertTrue(!isset($ids[$node->uniqId]));
		$ids[$node->uniqId] = true;

		if (!$recursion) {
			$ids = array();
			if (!$node instanceof CDomNodeTag && !$node instanceof CDomDocument) {
				$this->assertEquals(-1, $node->chid);
				$this->assertEmpty($node->next);
				$this->assertEmpty($node->prev);
				$this->assertEmpty($node->nodes);
				$this->assertEmpty($node->children);
				$this->assertEmpty($node->firstChild);
				$this->assertEmpty($node->lastChild);
				if (!$node->parent) {
					$this->assertEquals(-1, $node->cnid);
				} else {
					$this->assertGreaterThan(-1, $node->cnid);
					$this->assertEquals($node->uniqId, $node->parent->nodes[$node->cnid]->uniqId);
					if ($ownerDocument) {
						$this->assertEquals($ownerDocument->uniqId, $node->parent->ownerDocument->uniqId);
					}
				}
				return;
			}
		}

		$this->assertTrue($node instanceof CDomNodeTag || $node instanceof CDomDocument);
		if ($node instanceof CDomDocument) {
			$this->assertNotEmpty($ownerDocument);
			$this->assertEquals($node->uniqId, $ownerDocument->uniqId);
			$this->assertEmpty($node->parent);
			$this->assertEmpty($node->attributes);
		} elseif ($node->selfClosed) {
			$this->assertEmpty($node->nodes);
		}

		if (!$node->parent) {
			$this->assertEquals(-1, $node->cnid);
			$this->assertEquals(-1, $node->chid);
			$this->assertEmpty($node->next);
			$this->assertEmpty($node->prev);
		} else {
			if ($ownerDocument) {
				$this->assertEquals($ownerDocument->uniqId, $node->parent->ownerDocument->uniqId);
			}
			$this->assertGreaterThan(-1, $node->cnid);
			$this->assertGreaterThan(-1, $node->chid);
			$this->assertEquals($node->uniqId, $node->parent->children[$node->chid]->uniqId);
			$this->assertEquals($node->uniqId, $node->parent->nodes[$node->cnid]->uniqId);
			if ($node->chid !== 0) {
				$this->assertNotEmpty($node->prev);
				$this->assertEquals($node->uniqId, $node->prev->next->uniqId);
			} else {
				$this->assertEquals($node->uniqId, $node->parent->firstChild->uniqId);
			}
			if ($node->chid !== count($node->parent->children)-1) {
				$this->assertNotEmpty($node->next);
				$this->assertEquals($node->uniqId, $node->next->prev->uniqId);
			} else {
				$this->assertEquals($node->uniqId, $node->parent->lastChild->uniqId);
			}
		}

		$prev = null;
		$cnid = 0;
		$chid = 0;
		$firstChild = $node->firstChild;
		$lastChild  = $node->lastChild;

		if ($node->nodes) {
			if ($node->children) {
				$this->assertNotEmpty($firstChild);
				$this->assertNotEmpty($lastChild);
			}
			foreach ($node->nodes as $nkey => $n) {
				$this->assertTrue($n instanceof CDomNode);
				$this->assertEquals($cnid++, $n->cnid);
				$this->assertEquals($nkey, $n->cnid);
				$this->assertNotEmpty($n->parent);
				$this->assertEquals($node->uniqId, $n->parent->uniqId);
				$this->assertEquals($ownerDocument, $n->ownerDocument);
				if ($n instanceof CDomNodeTag) {
					$this->assertTrue(isset($node->children[$chid]));
					if ($n->uniqId !== $node->children[$chid]->uniqId) {
						time();
					}
					$this->assertEquals($n->uniqId, $node->children[$chid]->uniqId);
					$this->assertEquals($chid++, $n->chid);
					$this->assertEquals($prev, $n->prev);
					if ($prev === null) {
						$this->assertEquals($n->uniqId, $firstChild->uniqId);
					} else {
						$this->assertEquals($n->uniqId, $prev->next->uniqId);
					}
					$prev = $n;
					if ($n->nodes) {
						$this->checkStructure($n, true);
					}
				} else {
					$this->assertEquals(-1, $n->chid);
					$this->assertEmpty($n->nodes);
					$this->assertEmpty($n->children);
					$this->assertEmpty($n->firstChild);
					$this->assertEmpty($n->lastChild);
				}
			}
			if ($firstChild) {
				$this->assertEmpty($firstChild->prev);
				$this->assertEquals($node->uniqId, $firstChild->parent->uniqId);
				$this->assertEquals($ownerDocument, $firstChild->ownerDocument);
				$chid = $firstChild->chid;
				$this->assertTrue(0 === $chid);
				$this->assertEquals($firstChild->uniqId, $node->children[0]->uniqId);
			}
			if ($lastChild) {
				$this->assertEmpty($lastChild->next);
				$this->assertEquals($node->uniqId, $lastChild->parent->uniqId);
				$this->assertEquals($ownerDocument, $lastChild->ownerDocument);
				$chid = $lastChild->chid;
				$exChid = count($node->children)-1;
				$this->assertTrue($exChid === $chid);
				$this->assertEquals($lastChild->uniqId, $node->children[$exChid]->uniqId);
			}
		} else {
			$this->assertEmpty($firstChild);
			$this->assertEmpty($lastChild);
			$this->assertEmpty($node->children);
		}

		if ($prev !== null) {
			$this->assertEquals($prev->uniqId, $lastChild->uniqId);
		}

		if (!$recursion) {
			$ids = null;
		}
	}

	/**
	 * Checks nodes list structure
	 *
	 * @param CDomNodesList|CDomNode[] $list
	 */
	protected function checkList($list)
	{
		foreach ($list as $node) {
			$this->checkStructure($node);
		}
	}



	/**
	 * Tests base functional
	 *
	 * @covers CDomAttribute
	 * @covers CDomAttributesList
	 * @covers CDomDocument
	 * @covers CDomList
	 * @covers CDomNode
	 * @covers CDomNodeCdata
	 * @covers CDomNodeCommment
	 * @covers CDomNodeDoctype
	 * @covers CDomNodesList
	 * @covers CDomNodeTag
	 * @covers CDomNodeText
	 * @covers CDomNodeXmlDeclaration
	 * @covers CDomSelector
	 * @covers CDom
	 * @covers CLexer
	 */
	function testBase()
	{
		// ----
		$dom = CDom::fromString();
		$this->checkStructure($dom);
		$this->assertEmpty($dom->nodes);
		$this->assertEquals(CDom::NODE_DOCUMENT, $dom->type);
		$dom->clean();


		// ----
		$dom = CDom::fromString($this->test_html2);
		$this->checkStructure($dom);
		$dom->clean();

		$dom = CDom::fromString($this->test_html);
		$this->checkStructure($dom);
		$dom->clean();

		$dom = CDom::fromString('<div>text</div>');
		$this->checkStructure($dom);
		$dom->clean();


		// ----
		$dom = CDom::fromString('l<a /><bR>l ');
		$this->checkStructure($dom);
		$this->assertEquals(4, count($dom->nodes));
		$this->assertEquals(2, count($dom->children));
		$this->assertEquals(2, $dom->childElementCount);
		$this->assertEquals($dom->nodes, $dom->childNodes);
		$this->assertEquals($dom->nodes, $dom->nodes());
		$a  = $dom->firstChild;
		$br = $dom->lastChild;
		$this->assertNotEmpty($a);
		$this->assertNotEmpty($br);
		$this->assertNotEquals($a,  $dom->firstNode);
		$this->assertNotEquals($br, $dom->lastNode);
		$this->assertTrue($a->selfClosed);
		$this->assertEquals('a', $a->name);
		$this->assertEquals('a', $a->nameReal);
		$this->assertEquals('br', $a->next->name);
		$this->assertEquals($a->next, $a->nextElementSibling);
		$this->assertEquals($a->next, $a->nextSibling);
		$this->assertTrue($br->selfClosed);
		$this->assertEquals('br', $br->name);
		$this->assertEquals('bR', $br->nameReal);
		$this->assertEquals('a', $br->prev->name);
		$this->assertEquals($br->prev, $br->previousElementSibling);
		$this->assertEquals($br->prev, $br->previousSibling);
		$this->assertEquals('', $a->html());
		$this->assertEquals('', $a->innerHtml());
		$this->assertEquals('', $a->html);
		$this->assertEquals('<a />', $a->outerHtml());
		$this->assertEquals('<a />', $a->outerHtml);
		$this->assertEquals('', $a->text());
		$this->assertEquals('', $a->text);
		$this->assertEquals('', $br->html());
		$this->assertEquals('', $br->innerHtml());
		$this->assertEquals('', $br->html);
		$this->assertEquals('<br />', $br->outerHtml());
		$this->assertEquals('<br />', $br->outerHtml);
		$this->assertEquals('', $br->text());
		$this->assertEquals('', $br->text);
		$this->assertEquals('', $br->textContent);
		$expected = 'l<a /><br />l';
		$this->assertEquals($expected, $dom->html());
		$this->assertEquals($expected, (string)$dom);
		$this->assertEquals($expected, $dom->outerHtml());
		$this->assertEquals("l\nl", $dom->text());
		$catched = false;
		try {
			$dom->unknown();
		} catch (Exception $e) {
			$this->assertTrue($e instanceof BadMethodCallException);
			$catched = true;
		}
		$this->assertTrue($catched);

		$dom->clean();


		// ----
		$dom = CDom::fromString('<?xml version="1.0" encoding="UTF-8"?><XmL:Name />');
		$this->checkStructure($dom);
		$this->assertEquals(2, count($dom->nodes));
		$this->assertEquals(1, count($dom->children));
		$this->assertEquals(1, $dom->childElementCount);
		$this->assertEquals($dom->nodes, $dom->childNodes);
		$xml   = $dom->firstNode;
		$child = $dom->firstChild;
		$this->assertNotEquals($xml, $child);
		$this->assertEquals($dom->node(0), $dom->nodes[0]);
		$this->assertEquals($dom->node(-1), $dom->nodes[1]);
		$this->assertEquals(CDom::NODE_XML_DECL, $dom->nodes[0]->type);
		$this->assertEquals(CDom::NODE_XML_DECL, $xml->type);
		$this->assertEquals('<?xml version="1.0" encoding="UTF-8"?>', $xml->outerHtml());
		$this->assertEquals('', $xml->text());
		$this->assertEquals('', $xml->html());
		$this->assertEquals(CDom::NODE_ELEMENT, $child->type);
		$this->assertEquals('xml:name', $child->name);
		$this->assertEquals('XmL:Name', $child->nameReal);
		$this->assertEquals('xml', $child->namespace);
		$this->assertEquals('name', $child->nameLocal);
		$this->assertNotEmpty($dom->child(0));
		$this->assertEmpty($dom->child(1));
		$this->assertEquals($dom->child(0), $dom->children[0]);
		$this->assertEquals($dom->child(-1), $dom->children[0]);
		$this->assertEmpty($dom->child(-2));
		$dom->clean();


		// ----
		$html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
		$dom = CDom::fromString($html);
		$this->checkStructure($dom);
		$this->assertEquals(1, count($dom->nodes));
		$this->assertEquals(0, count($dom->children));
		$doctype = $dom->firstNode;
		$this->assertEquals(CDom::NODE_DOCTYPE, $doctype->type);
		$this->assertEquals(CDom::NODE_DOCTYPE, $dom->nodes[0]->type);
		$this->assertEquals($html, $doctype->outerHtml());
		$this->assertEquals('', $doctype->html());
		$this->assertEquals('', $doctype->text());
		$dom->clean();


		// ----
		$html = '<b>text текст %$#@!~</b>';
		$dom  = CDom::fromString($html);
		$this->checkStructure($dom);
		$this->assertEquals(1, count($dom->nodes));
		$this->assertEquals(1, count($dom->children));
		$b = $dom->firstChild;
		$this->assertNotEmpty($b);
		$this->assertEquals($b, $dom->firstNode);
		$text = $b->firstNode;
		$this->assertEmpty($b->firstChild);
		$this->assertNotEmpty($text);
		$this->assertEquals(CDom::NODE_TEXT, $text->type);
		$this->assertEquals($html, $dom->html());
		$this->assertEquals($html, $b->outerHtml());
		$expected = 'text текст %$#@!~';
		$this->assertEquals($expected, $dom->text());
		$this->assertEquals($expected, $b->text());
		$this->assertEquals($expected, $b->html());
		$this->assertEquals($expected, $b->innerHtml());
		$dom->clean();


		// ----
		$dom  = CDom::fromString('  text текст  ');
		$this->checkStructure($dom);
		$this->assertEquals(1, count($dom->nodes));
		$this->assertEquals(0, count($dom->children));
		$text = $dom->firstNode;
		$this->assertEmpty($dom->firstChild);
		$this->assertNotEmpty($text);
		$this->assertEquals($text, $dom->nodes[0]);
		$this->assertEquals(CDom::NODE_TEXT, $dom->nodes[0]->type);
		$this->assertEquals(CDom::NODE_TEXT, $text->type);
		$expected = 'text текст';
		$this->assertEquals($expected, $dom->html());
		$this->assertEquals($expected, $dom->text());
		$this->assertEquals($expected, $dom->outerHtml());
		$this->assertEquals($expected, $text->html());
		$this->assertEquals($expected, $text->text());
		$this->assertEquals($expected, $text->outerHtml());
		$dom->clean();


		// ----
		$html = '<!-- comment -->';
		$dom  = CDom::fromString($html);
		$this->checkStructure($dom);
		$this->assertEquals(1, count($dom->nodes));
		$this->assertEquals(0, count($dom->children));
		$comment = $dom->firstNode;
		$this->assertEmpty($dom->firstChild);
		$this->assertNotEmpty($comment);
		$this->assertEquals($comment, $dom->nodes[0]);
		$this->assertEquals(CDom::NODE_COMMENT, $dom->nodes[0]->type);
		$this->assertEquals(CDom::NODE_COMMENT, $comment->type);
		$this->assertEquals('', $dom->text());
		$this->assertEquals($html, $dom->html());
		$this->assertEquals($html, $dom->outerHtml());
		$this->assertEquals('', $comment->text());
		$this->assertEquals('', $comment->html());
		$this->assertEquals($html, $comment->outerHtml());
		$dom->clean();


		// ----
		$html = '<![CDATA[Yeah cdata section <nav>...</nav> here]]>';
		$dom  = CDom::fromString($html);
		$this->checkStructure($dom);
		$this->assertEquals(1, count($dom->nodes));
		$this->assertEquals(0, count($dom->children));
		$this->assertEquals(0, $dom->childElementCount);
		$cdata = $dom->firstNode;
		$this->assertEquals($cdata->name, $cdata->nodeName);
		$this->assertEquals($cdata->value, $cdata->nodeValue);
		$this->assertEquals($cdata->type, $cdata->nodeType);
		$this->assertEmpty($dom->firstChild);
		$this->assertNotEmpty($cdata);
		$this->assertEquals($cdata, $dom->nodes[0]);
		$this->assertEquals(CDom::NODE_CDATA, $dom->nodes[0]->type);
		$this->assertEquals(CDom::NODE_CDATA, $cdata->type);
		$text = 'Yeah cdata section <nav>...</nav> here';
		$this->assertEquals($html, $dom->html());
		$this->assertEquals($text, $dom->text());
		$this->assertEquals($html, $dom->outerHtml());
		$this->assertEquals($text, $cdata->html());
		$this->assertEquals($text, $cdata->text());
		$this->assertEquals($html, $cdata->outerHtml());
		$dom->clean();


		// ----
		$html = '<br><br>';
		$dom = CDom::fromString($html);
		$this->checkStructure($dom);
		$res = $dom->find('*');
		$size = 2;
		$this->assertEquals($size, $res->length);
		$this->assertEquals($size, count($res));
		$this->assertEquals($size, sizeOf($res));
		$this->assertTrue(isset($res[0]));
		$this->assertFalse(isset($res[2]));

		$node = $res[1];
		$this->assertTrue($node->selfClosed);
		$this->assertEquals('br', $node->name);

		unset($res[1]);
		$this->assertFalse(isset($res[1]));

		$res[1] = $node;
		$this->assertEquals(1, $res->length);

		$res->add($node);
		$size = 2;
		$this->assertEquals($size, $res->length);
		$this->assertEquals($size, count($res));

		$k = 0;
		foreach ($res as $key => $node) {
			$this->assertEquals($k++, $key);
			$this->assertEquals(CDom::NODE_ELEMENT, $node->type);
			$this->assertEquals('br', $node->name);
		}

		$this->assertEquals('', $res->html());
		$this->assertEquals('', $res->text());
		$this->assertEquals('<br />', $res->outerHtml());
		$this->assertEquals('', $res->htmlAll());
		$this->assertEquals('', $res->textAll());
		$this->assertEquals('<br /><br />', $res->outerHtmlAll());
		$this->assertEquals($res->htmlAll(), (string)$res);
		$res->html('<i>');
		$this->assertEquals('<i></i>', $res->html());
		$this->assertEquals('', $res->text());
		$this->assertFalse($res->get(0)->selfClosed);
		$res->text('<i>');
		$this->assertEquals('<i>', $res->html());
		$this->assertEquals('<i>', $res->text());
		$dom->clean();


		// ----
		$html = 'text <br> text';
		$dom = CDom::fromString($html);
		$this->checkStructure($dom);

		$this->assertEquals("text\ntext", $dom->text());
		$br = $dom->firstChild;
		$this->assertEquals('br', $br->name);
		$this->assertEmpty($br->prev);
		$this->assertEmpty($br->previousElementSibling);
		$this->assertNotEmpty($br->previousSibling);
		$this->assertEmpty($br->next);
		$this->assertEmpty($br->nextElementSibling);
		$this->assertNotEmpty($br->nextSibling);
		$this->assertTrue($br->selfClosed);
		$br->text('test');
		$this->assertFalse($br->selfClosed);
		$this->assertEquals('test', $br->text());
		$this->assertEquals('test', $br->text);
		$this->assertEquals('<br>test</br>', $br->outerHtml());
		$dom->clean();


		// ----
		$dom = CDom::fromString('<tag disabled />');
		$this->checkStructure($dom);
		$this->assertEquals('disabled', (string)$dom->firstChild->disabled);
		$dom->clean();

		$dom = CDom::fromString('<tag on off />');
		$this->checkStructure($dom);
		$this->assertEquals('on', (string)$dom->firstChild->on);
		$this->assertEquals('off', (string)$dom->firstChild->off);
		$dom->clean();


		// ----
		$dom = CDom::fromString('<tag class=value test />');
		$this->checkStructure($dom);
		$this->assertEquals('value', (string)$dom->firstChild->class);
		$this->assertEquals('test', (string)$dom->firstChild->test);
		$dom->clean();

		$dom = CDom::fromString('<tag class="value test" />');
		$this->checkStructure($dom);
		$this->assertEquals('value test', (string)$dom->firstChild->class);
		$this->assertEquals('', (string)$dom->firstChild->test);
		$dom->clean();


		// ----
		$dom = CDom::fromString('<tr><td><tr><td>');
		$this->checkStructure($dom);
		$this->assertEquals(2, count($dom->nodes));
		$this->assertEquals(2, count($dom->children));
		$this->assertEquals('tr', $dom->nodes[0]->name);
		$this->assertEquals(1, count($dom->nodes[0]->nodes));
		$this->assertEquals(1, count($dom->nodes[0]->children));
		$this->assertEquals('td', $dom->nodes[0]->nodes[0]->name);
		$this->assertEquals('tr', $dom->nodes[1]->name);
		$this->assertEquals(1, count($dom->nodes[1]->nodes));
		$this->assertEquals(1, count($dom->nodes[1]->children));
		$this->assertEquals('td', $dom->nodes[1]->nodes[0]->name);
		$dom->clean();


		// ----
		$list = new CDomNodesList();
		$this->assertEquals(0, $list->length);
		$this->assertEquals($list->size(), $list->length);
		$this->assertEmpty($list->first);
		$this->assertEmpty($list->last);
		$this->assertEmpty($list->notDefined);
		$this->assertEmpty($list->text());
		$this->assertEmpty($list->html());
		$this->assertEmpty($list->outerHtml());
		$list->add(new CDomNodeTag('p'));
		$this->assertEquals(1, $list->length);
		$this->assertEquals($list->size(), $list->length);
		$this->assertNotEmpty($list->first);
		$this->assertNotEmpty($list->last);
		$this->assertEquals($list->first->uniqId, $list->last->uniqId);

		$catched = false;
		try {
			$list->notDefined();
		} catch (Exception $e) {
			$catched = true;
			$this->assertContains('is not defined', $e->getMessage());
		}
		$this->assertTrue($catched);

		$this->assertFalse($list->first->selfClosed);
		$this->assertFalse($list->selfClosed);
		$list->selfClosed = true;
		$this->assertTrue($list->first->selfClosed);
		$this->assertTrue($list->selfClosed);

		$this->assertEmpty($list->class);
		$this->assertEmpty($list->first->class);
		$list->class = 'test';
		$this->assertEquals('test', $list->class);
		$this->assertEquals('test', $list->first->class);


		// ----
		$markup = <<<'TXT'
<div class="news">



        <div class="alert push"><div class="body"><div class="title">
  <a href="/mishoo">mishoo</a>
  <span>pushed</span> to master at <a href="/mishoo/UglifyJS">mishoo/UglifyJS</a>
  <time class="js-relative-date" datetime="2011-10-17T06:37:27Z" title="2011-10-17 06:37:27">about 5 hours ago</time>
</div>
<div class="details">
  <div class="gravatar"><img src="https://secure.gravatar.com/avatar/8a966def06bd0f3e02f1af3570ec42a9?s=140&amp;d=https://a248.e.akamai.net/assets.github.com%2Fimages%2Fgravatars%2Fgravatar-140.png" alt="" width="30" height="30"></div>

  <div class="commits">
    <ul>
      <li>
        <span title="Someone"></span>
        <code><a href="/mishoo/UglifyJS/commit/845992977b5fe46335a1817947a24159a6ac3dd6">8459929</a></code>

        <div class="message">
          <blockquote title="use == instead of === in `member` (fixes #244)">
            use == instead of === in `member` (fixes <a href="https://github.com/mishoo/UglifyJS/issues/244" title="Handle ASTs with embedded tokens in member()" class="issue-link">#244</a>)
          </blockquote>
        </div>
      </li>
    </ul>
  </div>
</div>
</div></div>
        <div class="alert issues_closed"><div class="body"><div class="title">
  <a href="/mishoo">mishoo</a>
  <span>closed</span>
  <a href="/mishoo/UglifyJS/pull/244">pull request 244</a> on <a href="/mishoo/UglifyJS">mishoo/UglifyJS</a>
  <time class="js-relative-date" datetime="2011-10-17T06:37:26Z" title="2011-10-17 06:37:26">about 5 hours ago</time>
</div>
<div class="details">
  <div class="gravatar"><img src="https://secure.gravatar.com/avatar/8a966def06bd0f3e02f1af3570ec42a9?s=140&amp;d=https://a248.e.akamai.net/assets.github.com%2Fimages%2Fgravatars%2Fgravatar-140.png" alt="" width="30" height="30"></div>
  <div class="message">
    <blockquote>Handle ASTs with embedded tokens in member()</blockquote>
  </div>
</div>
</div></div>
        <div class="alert issues_opened"><div class="body"><div class="title">
  <a href="/jesserosenfield">jesserosenfield</a>
  <span>opened</span>
  <a href="/leafo/lessphp/issues/143">issue 143</a> on <a href="/leafo/lessphp">leafo/lessphp</a>
  <time class="js-relative-date" datetime="2011-10-17T03:03:47Z" title="2011-10-17 03:03:47">about 9 hours ago</time>
</div>

<div class="details">
  <div class="gravatar"><img src="https://secure.gravatar.com/avatar/ea89afd6242491a6f37a3f769a67573d?s=140&amp;d=https://a248.e.akamai.net/assets.github.com%2Fimages%2Fgravatars%2Fgravatar-140.png" alt="" width="30" height="30"></div>
  <div class="message">
    <blockquote>
      Calculated Percentages Parsed without % sign
    </blockquote>
  </div>
</div>
</div></div>
        <div class="alert issues_comment"><div class="body"><div class="title">
  <a href="/jesserosenfield">jesserosenfield</a>
  <span>commented</span> on
  <a href="/leafo/lessphp/issues/134#issuecomment-2424526">issue 134</a> on <a href="/leafo/lessphp">leafo/lessphp</a>
  <time class="js-relative-date" datetime="2011-10-17T02:56:36Z" title="2011-10-17 02:56:36">about 9 hours ago</time>
</div>

<div class="details">
  <div class="gravatar"><img src="https://secure.gravatar.com/avatar/ea89afd6242491a6f37a3f769a67573d?s=140&amp;d=https://a248.e.akamai.net/assets.github.com%2Fimages%2Fgravatars%2Fgravatar-140.png" alt="" width="30" height="30"></div>
  <div class="message">
    <blockquote title="I'm running into what I believe is a related @media issue. There is a visual inconsistency between what happens with media-queries parsed by less.js and by lessphp. As far as I can tell this should work fine:

@media all and (max-width: 40em) { ... }

But is producing a ton of visual errors and I can't figure out why. I also get the following css error from the w3c CSS validator (which I haven't been able to find any additional info on): 'max-width Property max-width doesn't exist in all but exists in all'

Currently this error is totally preventing me from using lessphp which is a shame because in theory it will cut both my site's loadtime and my workflow in half!">
      <p>I'm running into what I believe is a related <a href="https://github.com/media" class="user-mention">@media</a> issue. There is a visual inconsistency between what happens with media-queries parsed by less.js a...</p>
    </blockquote>
  </div>
</div>
</div></div>
        <div class="alert issues_comment"><div class="body"><div class="title">
  <a href="/Micoss">Micoss</a>
  <span>commented</span> on
  <a href="/facebook/php-sdk/issues/221#issuecomment-2418553">issue 221</a> on <a href="/facebook/php-sdk">facebook/php-sdk</a>
  <time class="js-relative-date" datetime="2011-10-16T01:31:15Z" title="2011-10-16 01:31:15">1 day ago</time>
</div>

<div class="details">
  <div class="gravatar"><img src="https://secure.gravatar.com/avatar/49061e8503ca321a8045c9065963a8c6?s=140&amp;d=https://a248.e.akamai.net/assets.github.com%2Fimages%2Fgravatars%2Fgravatar-140.png" alt="" width="30" height="30"></div>
  <div class="message">
    <blockquote>
      Globals in unit test
    </blockquote>
  </div>
</div>
</div></div>
        <div class="alert issues_comment"><div class="body"><div class="title">
  <a href="/ranbena">ranbena</a>
  <span>commented</span> on
  <a href="/necolas/normalize.css/pull/37#issuecomment-2379646">pull request 37</a> on <a href="/necolas/normalize.css">necolas/normalize.css</a>
  <time class="js-relative-date" datetime="2011-10-12T14:16:38Z" title="2011-10-12 14:16:38">4 days ago</time>
</div>

<div class="details">
  <div class="gravatar"><img src="https://secure.gravatar.com/avatar/ff4fc00e6f14d683b3bc5a1714619e92?s=140&amp;d=https://a248.e.akamai.net/assets.github.com%2Fimages%2Fgravatars%2Fgravatar-140.png" alt="" width="30" height="30"></div>
  <div class="message">
    <blockquote title="Hey Nicolas, I agree. In the next release, we'll remove the differentiation and use your original file.

Thanks for putting in your thoughts and taking the time to reply.

Cheers, Ran">
      <p>Hey Nicolas, I agree. In the next release, we'll remove the differentiation and use your original file. Thanks for putting in your thoughts and taking...</p>
    </blockquote>
  </div>
</div>
</div></div>


        <div class="pagination ajax_paginate"><a href="/dashboard/index/2">More</a></div>
  </div>
TXT;
		$dom = CDom::fromString($markup);
		$this->checkStructure($dom);
		$txt = $dom->text();
		$expected = <<<'TXT'
mishoo pushed to master at mishoo/UglifyJS about 5 hours ago

8459929
use == instead of === in `member` (fixes #244)

mishoo closed pull request 244 on mishoo/UglifyJS about 5 hours ago

Handle ASTs with embedded tokens in member()

jesserosenfield opened issue 143 on leafo/lessphp about 9 hours ago

Calculated Percentages Parsed without % sign

jesserosenfield commented on
issue 134 on leafo/lessphp about 9 hours ago

I'm running into what I believe is a related @media issue. There is a visual inconsistency between what happens with media-queries parsed by less.js a...

Micoss commented on
issue 221 on facebook/php-sdk 1 day ago

Globals in unit test

ranbena commented on
pull request 37 on necolas/normalize.css 4 days ago

Hey Nicolas, I agree. In the next release, we'll remove the differentiation and use your original file. Thanks for putting in your thoughts and taking...

More
TXT;
		$this->assertEquals($expected, $txt);
		$dom->clean();


		// ----
		$markup = <<<'TXT'
<style type="text/css">
	#medialand_adland_inline_div_750,#nadaviSpan,.rbcobmen { display:none !important; }
<crazy><markup>
</style>
TXT;
		$dom = CDom::fromString($markup);
		$this->checkStructure($dom);
		$this->assertEquals(0, count($dom->firstChild->nodes));
		$this->assertContains('medialand_adland_inline_div_750', $dom->firstChild->value);
		$dom->clean();
	}

	/**
	 * Tests charset recognition
	 *
	 * @covers CDom
	 */
	function testCharset()
	{
		$data_dir = __DIR__ . '/data_Dom/';

		$expected_quotes = array(
			"Веталь: да, было время когда меня бабушка бабаем пугала, чтобы я кашу съел )\nTrapekt: счастливый ты ребенок, я рос в советское время и меня бабушка Рейганом пугала, говорит не съешь кашу, на тебя Рейган атомную бомбу скинет, жрал как миленький.",
			"Новость: У берегов Чили обнаружен гигантский вирус.\nxxx: ну наконец-то, а то я уж думала, рыбьего гриппа не будет",
			"Novosti: В план эвакуации теперь обязательно входит пиктограмма туалетной кабинки.\nconst: в панике всякое бывает.",
		);

		$original = $data_dir . 'bash.org.ru_1251_original.html';
		$markup = file_get_contents($original);
		$dom = CDom::fromString($markup);
		$quotes = $dom->find('.q > div:nth-child(2)');
		$this->assertEquals(3, $quotes->length);
		$i = 0;
		foreach ($quotes as $i => $q) {
			$this->assertEquals($expected_quotes[$i], $q->text());
			$expected_quotes[] = $q->text();
		}
		$this->assertEquals(2, $i);

		$modified = $data_dir . 'bash.org.ru_utf-8_modified.html';
		$markup = file_get_contents($modified);
		$dom = CDom::fromString($markup);
		$quotes = $dom->find('.q > div:nth-child(2)');
		$this->assertEquals(3, $quotes->length);
		$i = 0;
		foreach ($quotes as $i => $q) {
			$this->assertEquals($expected_quotes[$i], $q->text());
			$expected_quotes[] = $q->text();
		}
		$this->assertEquals(2, $i);

		$modified = $data_dir . 'bash.org.ru_1251_modified.html';
		$markup = file_get_contents($modified);
		$dom = CDom::fromString($markup);
		$quotes = $dom->find('.q > div:nth-child(2)');
		$this->assertEquals(3, $quotes->length);
		$i = 0;
		foreach ($quotes as $i => $q) {
			$this->assertEquals($expected_quotes[$i], $q->text());
			$expected_quotes[] = $q->text();
		}
		$this->assertEquals(2, $i);
	}

	/**
	 * Tests CDom for simple BBCode parsing
	 *
	 * @covers CDomAttribute
	 * @covers CDomAttributesList
	 * @covers CDomDocument
	 * @covers CDomNode
	 * @covers CDomNodeTag
	 * @covers CDomNodeText
	 * @covers CDomSelector
	 * @covers CDom
	 * @covers CLexer
	 */
	function testBBCode()
	{
		// Backup default values
		$bo = CDom::$bracketOpen;
		$bc = CDom::$bracketClose;
		$bt = CDom::$blockTags;
		$it = CDom::$inlineTags;
		$st = CDom::$selfClosingTags;

		$bbMarkup = <<<'TXT'
[quote]
	[b]Bold [u]Underline[/u][/b]
	[i]Italic
[/quote]
[img width=12 height=16]url[/img]
TXT;
		CDom::$bracketOpen  = '[';
		CDom::$bracketClose = ']';

		CDom::$blockTags  = array('quote' => true);
		CDom::$inlineTags = array('b' => true, 'i' => true);
		CDom::$selfClosingTags = array();

		$dom = CDom::fromString($bbMarkup);

		$this->assertEquals(3, count($dom->nodes));
		$this->assertEquals(2, count($dom->children));

		$this->assertEquals(4, count($dom->firstChild->nodes));
		$this->assertEquals(2, count($dom->firstChild->children));

		$b = $dom->find('b');
		$expected = '[b]Bold [u]Underline[/u][/b]';
		$this->assertEquals($expected, $b->outerHtml());

		$img = $dom->lastChild;
		$img->width = 450;
		$this->assertEquals('[img width="450" height="16"]url[/img]', $img->outerHtml());

		CDom::$bracketOpen  = $bo;
		CDom::$bracketClose = $bc;
		$expected = '<b>Bold <u>Underline</u></b>';
		$this->assertEquals($expected, $b->outerHtml());

		$dom->clean();

		// Restore
		CDom::$bracketOpen     = $bo;
		CDom::$bracketClose    = $bc;
		CDom::$blockTags       = $bt;
		CDom::$inlineTags      = $it;
		CDom::$selfClosingTags = $st;
	}

	/**
	 * Tests malformed parsing
	 *
	 * @covers CDom
	 * @covers CLexer
	 */
	function testMalformed()
	{
		// Attributes
		$string = '<a class="b-mpUsersList-ava" hRef="http://3run.ru/id296" title="4b~<7sm`mk \",}hHC:<63* m>u:%.PVV>">';
		$dom = CDom::fromString($string);
		$expected = '<a class="b-mpUsersList-ava" href="http://3run.ru/id296" title="4b~&lt;7sm`mk &quot;,}hHC:&lt;63* m&gt;u:%.PVV&gt;"></a>';
		$this->assertEquals($expected, $dom->html());
		$this->assertEquals(1, count($dom->children));
		$this->assertEquals(1, count($dom->nodes));
		$dom->clean();


		// Closing tags
		$dom = CDom::fromString('<div> </ div </div>');
		$this->assertEquals(1, count($dom->children));
		$this->assertEquals(1, count($dom->nodes));
		$div = $dom->firstChild;
		$this->assertEquals(0, count($div->children));
		$this->assertEquals(1, count($div->nodes));
		$this->assertEquals('</ div', $div->text());
		$this->assertEquals(' </ div ', $div->firstNode->value);
		$dom->clean();

		$dom = CDom::fromString('<div></ div test>');
		$this->assertEquals(1, count($dom->children));
		$this->assertEquals(1, count($dom->nodes));
		$div = $dom->firstChild;
		$this->assertEquals(0, count($div->children));
		$this->assertEquals(0, count($div->nodes));
		$this->assertEquals('<div></div>', $dom->outerHtml());
		$dom->clean();

		$dom = CDom::fromString('<div></');
		$this->assertEquals(1, count($dom->children));
		$this->assertEquals(1, count($dom->nodes));
		$div = $dom->firstChild;
		$this->assertEquals(0, count($div->children));
		$this->assertEquals(1, count($div->nodes));
		$this->assertEquals('</', $div->text());
		$dom->clean();


		// XML declaration
		$dom = CDom::fromString('<?xml version="1.0"><tag />');
		$this->assertEquals(1, count($dom->children));
		$this->assertEquals(2, count($dom->nodes));
		$text = $dom->firstNode;
		$this->assertEquals(CDom::NODE_TEXT, $text->type);
		$this->assertEquals('<?xml version="1.0">', $text->value);
		$tag = $dom->firstChild;
		$this->assertEquals('<tag />', $tag->outerHtml());
		$dom->clean();

		$dom = CDom::fromString('<?x ?><tag />');
		$this->assertEquals(1, count($dom->children));
		$this->assertEquals(2, count($dom->nodes));
		$text = $dom->firstNode;
		$this->assertEquals(CDom::NODE_TEXT, $text->type);
		$this->assertEquals('<?x ?>', $text->value);
		$tag = $dom->firstChild;
		$this->assertEquals('<tag />', $tag->outerHtml());
		$dom->clean();


		// DOCTYPE
		$dom = CDom::fromString('<!DOC html ><tag />');
		$this->assertEquals(1, count($dom->children));
		$this->assertEquals(2, count($dom->nodes));
		$text = $dom->firstNode;
		$this->assertEquals(CDom::NODE_TEXT, $text->type);
		$this->assertEquals('<!DOC html >', $text->value);
		$tag = $dom->firstChild;
		$this->assertEquals('<tag />', $tag->outerHtml());
		$dom->clean();

		$dom = CDom::fromString('<!DOCTYPE html');
		$this->assertEquals(0, count($dom->children));
		$this->assertEquals(1, count($dom->nodes));
		$text = $dom->firstNode;
		$this->assertEquals(CDom::NODE_TEXT, $text->type);
		$this->assertEquals('<!DOCTYPE html', $text->value);
		$dom->clean();

		$dom = CDom::fromString('<!');
		$this->assertEquals(0, count($dom->children));
		$this->assertEquals(1, count($dom->nodes));
		$text = $dom->firstNode;
		$this->assertEquals(CDom::NODE_TEXT, $text->type);
		$this->assertEquals('<!', $text->value);
		$dom->clean();


		// Tags
		$dom = CDom::fromString('<>');
		$this->assertEquals(0, count($dom->children));
		$this->assertEquals(1, count($dom->nodes));
		$text = $dom->firstNode;
		$this->assertEquals(CDom::NODE_TEXT, $text->type);
		$this->assertEquals('<>', $text->value);
		$dom->clean();

		$dom = CDom::fromString('<foo class="bar"');
		$this->assertEquals(0, count($dom->children));
		$this->assertEquals(1, count($dom->nodes));
		$text = $dom->firstNode;
		$this->assertEquals(CDom::NODE_TEXT, $text->type);
		$this->assertEquals('<foo class="bar"', $text->value);
		$dom->clean();


		// Comments
		$dom = CDom::fromString('<!- -->');
		$this->assertEquals(0, count($dom->children));
		$this->assertEquals(1, count($dom->nodes));
		$text = $dom->firstNode;
		$this->assertEquals(CDom::NODE_TEXT, $text->type);
		$this->assertEquals('<!- -->', $text->value);
		$dom->clean();

		$dom = CDom::fromString('<!--->');
		$this->assertEquals(0, count($dom->children));
		$this->assertEquals(1, count($dom->nodes));
		$text = $dom->firstNode;
		$this->assertEquals(CDom::NODE_TEXT, $text->type);
		$this->assertEquals('<!--->', $text->value);
		$dom->clean();

		// CDATA
		$dom = CDom::fromString('<![CDATA   ]]>');
		$this->assertEquals(0, count($dom->children));
		$this->assertEquals(1, count($dom->nodes));
		$text = $dom->firstNode;
		$this->assertEquals(CDom::NODE_TEXT, $text->type);
		$this->assertEquals('<![CDATA   ]]>', $text->value);
		$dom->clean();

		$dom = CDom::fromString('<![CDATA[ ]>');
		$this->assertEquals(0, count($dom->children));
		$this->assertEquals(1, count($dom->nodes));
		$text = $dom->firstNode;
		$this->assertEquals(CDom::NODE_TEXT, $text->type);
		$this->assertEquals('<![CDATA[ ]>', $text->value);
		$dom->clean();


		// ----
		$dom = CDom::fromString('<p><i><div/></p><p><i></p>');
		$expected = '<p><i></i><div /></p><p><i></i></p>';
		$this->assertEquals($expected, $dom->html());

		$dom = $dom->html('<p><p><p></p></p></p>');
		$expected = '<p></p><p></p><p></p>';
		$this->assertEquals($expected, $dom->html());

		$dom = $dom->html('<div><p></div>');
		$this->assertEquals(1, count($dom->nodes));
		$this->assertEquals(1, count($dom->firstChild->nodes));
		$expected = '<div><p></p></div>';
		$this->assertEquals($expected, $dom->html());
		$this->checkStructure($dom);

		$dom = $dom->html('<h1><p></div>');
		$this->assertEquals(1, count($dom->nodes));
		$this->assertEquals(1, count($dom->firstChild->nodes));
		$expected = '<h1><p></p></h1>';
		$this->assertEquals($expected, $dom->html());
		$this->checkStructure($dom);

		$dom = $dom->html('<i></p>');
		$expected = '<i></i>';
		$this->assertEquals($expected, $dom->html());
		$this->checkStructure($dom);

		$dom = $dom->html('<b><i></b>');
		$expected = '<b><i></i></b>';
		$this->assertEquals($expected, $dom->html());
		$this->checkStructure($dom);
		$dom->clean();
	}

	/**
	 * Tests malformed selectors
	 *
	 * @covers CDomSelector
	 * @covers CLexer
	 */
	function testMalformedSelector()
	{
		$n = 0;

		try {
			new CDomSelector('');
		} catch (Exception $e) {
			$this->assertTrue($e instanceof InvalidArgumentException);
			$n++;
		}
		try {
			new CDomSelector('.');
		} catch (Exception $e) {
			$this->assertTrue($e instanceof InvalidArgumentException);
			$n++;
		}
		try {
			new CDomSelector('#');
		} catch (Exception $e) {
			$this->assertTrue($e instanceof InvalidArgumentException);
			$n++;
		}
		try {
			new CDomSelector('[]');
		} catch (Exception $e) {
			$this->assertTrue($e instanceof InvalidArgumentException);
			$n++;
		}
		try {
			new CDomSelector('[name~]');
		} catch (Exception $e) {
			$this->assertTrue($e instanceof InvalidArgumentException);
			$n++;
		}
		try {
			new CDomSelector('[name="]');
		} catch (Exception $e) {
			$this->assertTrue($e instanceof InvalidArgumentException);
			$n++;
		}
		try {
			new CDomSelector('[name="" ]');
		} catch (Exception $e) {
			$this->assertTrue($e instanceof InvalidArgumentException);
			$n++;
		}
		try {
			new CDomSelector(':');
		} catch (Exception $e) {
			$this->assertTrue($e instanceof InvalidArgumentException);
			$n++;
		}
		try {
			new CDomSelector(':n(');
		} catch (Exception $e) {
			$this->assertTrue($e instanceof InvalidArgumentException);
			$n++;
		}

		$this->assertEquals(9, $n);
	}

	/**
	 * Tests selectors
	 *
	 * @covers CDomNode
	 * @covers CDomDocument
	 * @covers CDomList
	 * @covers CDomNodesList
	 * @covers CDomSelector
	 * @covers CLexer
	 */
	function testSelectors()
	{
		// Basic
		if (true) {
			$this->dom = $dom = CDom::fromString($this->test_html);

			$this->assertTrue($dom->is('div'));
			$this->assertFalse($dom->is('img'));

			$res = $this->find('*');
			$this->assertEquals(4, $res->length);
			$this->assertEquals('div', $res[0]->name);
			$this->assertEquals('img', $res[1]->name);
			$this->assertEquals('class0', (string)$res[1]->attr('class'));
			$this->assertEquals('img', $res[2]->name);
			$this->assertEquals('class1', (string)$res[2]->attr('class'));
			$this->assertEquals('img', $res[3]->name);
			$this->assertEquals('class2', (string)$res[3]->attr('class'));

			$res('*');
			$this->assertEquals('img', $res[0]->name);
			$this->assertEquals('class0', (string)$res[0]->attr('class'));
			$this->assertEquals('img', $res[1]->name);
			$this->assertEquals('class1', (string)$res[1]->attr('class'));
			$this->assertEquals('img', $res[2]->name);
			$this->assertEquals('class2', (string)$res[2]->attr('class'));

			/** @noinspection PhpUndefinedMethodInspection */
			$res = $res->end()->find('*', 1);
			$this->assertEquals('img', $res->name);
			$this->assertEquals('class1', $res->class);


			$res = $this->find(' div > * ');
			$this->assertEquals(3, $res->length);
			$this->assertEquals('img', $res[0]->name);
			$this->assertEquals('class0', (string)$res[0]->attr('class'));
			$this->assertEquals('img', $res[1]->name);
			$this->assertEquals('class1', (string)$res[1]->attr('class'));
			$this->assertEquals('img', $res[2]->name);
			$this->assertEquals('class2', (string)$res[2]->attr('class'));

			$res = $this->find('div > img');
			$this->assertEquals(3, $res->length);
			$this->assertEquals('img', $res[0]->name);
			$this->assertEquals('class0', (string)$res[0]->attr('class'));
			$this->assertEquals('img', $res[1]->name);
			$this->assertEquals('class1', (string)$res[1]->attr('class'));
			$this->assertEquals('img', $res[2]->name);
			$this->assertEquals('class2', (string)$res[2]->attr('class'));

			$res = $this->find('div img ');
			$this->assertEquals(3, $res->length);
			$this->assertEquals('img', $res[0]->name);
			$this->assertEquals('class0', (string)$res[0]->attr('class'));
			$this->assertEquals('img', $res[1]->name);
			$this->assertEquals('class1', (string)$res[1]->attr('class'));
			$this->assertEquals('img', $res[2]->name);
			$this->assertEquals('class2', (string)$res[2]->attr('class'));

			$res = $dom('div + img');
			$this->assertEquals(0, $res->length);

			$res = $dom->getElementById('id0');
			$this->assertEquals('img', $res->name);
			$this->assertEquals('class0', (string)$res->attr('class'));

			$res = $dom->getElementByTagName('img');
			$this->assertEquals('img', $res->name);
			$this->assertEquals('class0', (string)$res->attr('class'));

			$res = $dom->getElementsByTagName('img');
			$this->assertEquals(3, $res->length);
			$this->assertEquals('img', $res[0]->name);
			$this->assertEquals('class0', (string)$res[0]->attr('class'));
			$this->assertEquals('img', $res[1]->name);
			$this->assertEquals('class1', (string)$res[1]->attr('class'));
			$this->assertEquals('img', $res[2]->name);
			$this->assertEquals('class2', (string)$res[2]->attr('class'));


			$html = <<<'HTML'
<div>
	<img id="id0">
</div>
<img id="id1">
HTML;
			$this->dom = $dom = CDom::fromString($html);

			$this->assertFalse($dom->is('*'));

			$res = $this->find('div + img');
			$this->assertEquals(1, $res->length);
			$this->assertEquals('img', $res[0]->name);
			$this->assertEquals('id1', (string)$res[0]->attr('id'));

			$html = <<<'HTML'
<div>
	<img id="id0">
</div>
<br/>
<img id="id1">
HTML;
			$this->dom = $dom  = CDom::fromString($html);
			$res  = $this->find('div + img');
			$this->assertEquals(0, $res->length);

			$res = $this->find('div ~ img');
			$this->assertEquals(1, $res->length);
			$this->assertEquals('img', $res[0]->name);
			$this->assertEquals('id1', (string)$res[0]->attr('id'));

			$dom->clean();
		}

		// Complex
		$this->dom = $dom = CDom::fromString('');
		$res = $this->find('p.class#id[attr][name*="value"]:empty:contains(test) + next ~ #simp :first , .class[attr=value]');
		$this->assertEquals(0, $res->length);

		// Attributes
		if (true) {
			$html = <<<'HTML'
<span t=1 disabled />
<span t=2 class="highlighted" />
<span t=3 class="high low right" id="main val" />
<span t=4 id="main" />
<span t=5 lang="ru-RU" />
HTML;
			$this->dom = $dom = CDom::fromString($html);

			$res = $this->find('span.low');
			$this->assertEquals(1, $res->length);

			$res = $this->find('.highlighted');
			$this->assertEquals(1, $res->length);

			$res = $this->find('.high');
			$this->assertEquals(1, $res->length);

			$res = $this->find('.main');
			$this->assertEquals(0, $res->length);

			$res = $this->find('span#main');
			$this->assertEquals(1, $res->length);

			$res = $this->find('#main');
			$this->assertEquals(1, $res->length);

			$res = $this->find('#high');
			$this->assertEquals(0, $res->length);

			$res = $this->find('.low');
			$this->assertEquals(1, $res->length);

			$res = $this->find('[disabled]');
			$this->assertEquals(1, $res->length);
			$this->assertEquals(1, (string)$res[0]->attr('t'));

			$res = $this->find('[class]');
			$this->assertEquals(2, $res->length);
			$this->assertEquals(2, (string)$res[0]->attr('t'));
			$this->assertEquals(3, (string)$res[1]->attr('t'));

			$res = $this->find('[disabled]');
			$this->assertEquals(1, $res->length);
			$this->assertEquals(1, (string)$res[0]->attr('t'));

			$res = $this->find('[class=highlighted]');
			$this->assertEquals(1, $res->length);

			$res = $this->find('[id="main"]');
			$this->assertEquals(1, $res->length);

			$res = $this->find('[lang="ru"]');
			$this->assertEquals(0, $res->length);

			$res = $this->find('[lang="ru-RU"]');
			$this->assertEquals(1, $res->length);

			$res = $this->find('[lang="ru-ru"]');
			$this->assertEquals(0, $res->length);

			$res = $this->find('[class!=highlighted]');
			$this->assertEquals(4, $res->length);

			$res = $this->find('[class*=high]');
			$this->assertEquals(2, $res->length);

			$res = $this->find('[class*=ight]');
			$this->assertEquals(2, $res->length);

			$res = $this->find('[lang*=ru]');
			$this->assertEquals(1, $res->length);

			$res = $this->find('[class*=ru]');
			$this->assertEquals(0, $res->length);

			$res = $this->find('[class^=hi]');
			$this->assertEquals(2, $res->length);

			$res = $this->find('[class^=low]');
			$this->assertEquals(0, $res->length);

			$res = $this->find('[lang^=ru]');
			$this->assertEquals(1, $res->length);

			$res = $this->find('[class$=ed]');
			$this->assertEquals(1, $res->length);

			$res = $this->find('[lang$=ru]');
			$this->assertEquals(0, $res->length);

			$res = $this->find('[class$=low]');
			$this->assertEquals(0, $res->length);

			$res = $this->find('[class~=high]');
			$this->assertEquals(1, $res->length);

			$res = $this->find('[class~=low]');
			$this->assertEquals(1, $res->length);

			$res = $this->find('[class~=lo]');
			$this->assertEquals(0, $res->length);

			$res = $this->find('[lang~=ru]');
			$this->assertEquals(0, $res->length);

			$res = $this->find('[id|=main]');
			$this->assertEquals(1, $res->length);

			$res = $this->find('[lang|=ru]');
			$this->assertEquals(1, $res->length);

			$res = $this->find('[disabled], #main');
			$this->assertEquals(2, $res->length);
			$this->assertEquals('<span t="1" disabled />', $res->outerHtml());
			$this->assertEquals('<span t="1" disabled />', $res[0]->outerHtml());
			$this->assertEquals('<span t="4" id="main" />', $res[1]->outerHtml());

			$res = $this->find('[t=1][disabled]');
			$this->assertEquals(1, $res->length);
			$this->assertEquals('<span t="1" disabled />', $res->outerHtml());

			$res = $this->find('*[t=1][disabled]');
			$this->assertEquals(1, $res->length);

			$res = $this->find('span[t=1][disabled]');
			$this->assertEquals(1, $res->length);

			$dom->clean();
		}


		// Pseudo classes part 1
		$this->dom = $dom = CDom::fromString($this->test_html);
		$res = $this->find('div > img', 1);
		$this->assertEquals('img', $res->name);
		if (true) {
			// empty
			$res = $this->find(':empty');
			$this->assertEquals(3, $res->length);
			$this->assertEquals('img', $res[0]->name);
			$this->assertEquals('id0', (string)$res[0]->attr('id'));
			$this->assertEquals('img', $res[1]->name);
			$this->assertEquals('id1', (string)$res[1]->attr('id'));
			$this->assertEquals('img', $res[2]->name);
			$this->assertEquals('id2', (string)$res[2]->attr('id'));

			// parent
			$res = $this->find(':parent');
			$this->assertEquals(1, $res->length);
			$this->assertEquals('div', $res[0]->name);

			// first-child
			$res = $this->find(':first-child');
			$this->assertEquals(1, $res->length);
			$this->assertEquals('img', $res[0]->name);
			$this->assertEquals('id0', (string)$res[0]->attr('id'));

			// last-child
			$res = $this->find(':last-child');
			$this->assertEquals(1, $res->length);
			$this->assertEquals('img', $res[0]->name);
			$this->assertEquals('id2', (string)$res[0]->attr('id'));

			// only-child
			$res = $this->find(':only-child');
			$this->assertEquals(0, $res->length);

			// header
			$res = $this->find(':header');
			$this->assertEquals(0, $res->length);

			// nth-child
			$res = $this->find(':nth-child(2)');
			$this->assertEquals(1, $res->length);
			$this->assertEquals('img', $res[0]->name);
			$this->assertEquals('id1', (string)$res[0]->attr('id'));

			$res = $this->find(':nth-child(3)');
			$this->assertEquals(1, $res->length);
			$this->assertEquals('img', $res[0]->name);
			$this->assertEquals('id2', (string)$res[0]->attr('id'));

			$res = $this->find(':nth-child(4)');
			$this->assertEquals(0, $res->length);

			$res = $this->find(':nth-child(0)');
			$this->assertEquals(0, $res->length);

			$res = $this->find(':nth-child(-1)');
			$this->assertEquals(0, $res->length);

			// nth-last-child
			$res = $this->find(':nth-last-child(0)');
			$this->assertEquals(0, $res->length);

			$res = $this->find(':nth-last-child(1)');
			$this->assertEquals(1, $res->length);
			$this->assertEquals('img', $res[0]->name);
			$this->assertEquals('id2', (string)$res[0]->attr('id'));

			$res = $this->find(':nth-last-child(3)');
			$this->assertEquals(1, $res->length);
			$this->assertEquals('img', $res[0]->name);
			$this->assertEquals('id0', (string)$res[0]->attr('id'));

			$res = $this->find(':nth-last-child(4)');
			$this->assertEquals(0, $res->length);

			$res = $this->find(':nth-last-child(-1)');
			$this->assertEquals(0, $res->length);

			// contains
			$res = $this->find(':contains(text)');
			$this->assertEquals(0, $res->length);

			// nth-of-type
			$res = $this->find(':nth-of-type(-1)');
			$this->assertEquals(0, $res->length);

			$res = $this->find(':nth-of-type(0)');
			$this->assertEquals(0, $res->length);

			$res = $this->find(':nth-of-type(1)');
			$this->assertEquals(1, $res->length);
			$this->assertEquals('img', $res[0]->name);
			$this->assertEquals('id0', (string)$res[0]->attr('id'));

			$res = $this->find(':nth-of-type(3)');
			$this->assertEquals(1, $res->length);
			$this->assertEquals('img', $res[0]->name);
			$this->assertEquals('id2', (string)$res[0]->attr('id'));

			$res = $this->find(':nth-of-type(4)');
			$this->assertEquals(0, $res->length);

			$res = $this->find(':only-of-type');
			$this->assertEquals(0, $res->length);

			// first-of-type
			$res = $this->find(':first-of-type');
			$this->assertEquals(1, $res->length);
			$this->assertEquals('img', $res[0]->name);
			$this->assertEquals('id0', (string)$res[0]->attr('id'));

			// last-of-type
			$res = $this->find(':last-of-type');
			$this->assertEquals(1, $res->length);
			$this->assertEquals('img', $res[0]->name);
			$this->assertEquals('id2', (string)$res[0]->attr('id'));

			// nth-last-of-type
			$res = $this->find(':nth-last-of-type(0)');
			$this->assertEquals(0, $res->length);

			$res = $this->find(':nth-last-of-type(-1)');
			$this->assertEquals(0, $res->length);

			$res = $this->find(':nth-last-of-type(1)');
			$this->assertEquals(1, $res->length);
			$this->assertEquals('img', $res[0]->name);
			$this->assertEquals('id2', (string)$res[0]->attr('id'));

			$res = $this->find(':nth-last-of-type(3)');
			$this->assertEquals(1, $res->length);
			$this->assertEquals('img', $res[0]->name);
			$this->assertEquals('id0', (string)$res[0]->attr('id'));

			$res = $this->find(':nth-last-of-type(4)');
			$this->assertEquals(0, $res->length);

			// not
			$res = $this->find(':not(img)');
			$this->assertEquals(1, $res->length);
			$this->assertEquals('div', $res[0]->name);

			$res = $this->find(':not(div)');
			$this->assertEquals(3, $res->length);
			$this->assertEquals('img', $res[0]->name);

			$res = $this->find(':not(div > img)');
			$this->assertEquals(1, $res->length);
			$this->assertEquals('div', $res[0]->name);

			$res = $this->find(':not(div > img ~ img)');
			$this->assertEquals(2, $res->length);
			$this->assertEquals('div', $res[0]->name);
			$this->assertEquals('img', $res[1]->name);
			$this->assertEquals('id0', (string)$res[1]->attr('id'));

			$res = $this->find(':not(div img)');
			$this->assertEquals(1, $res->length);
			$this->assertEquals('div', $res[0]->name);

			$res = $this->find(':not(img + img)');
			$this->assertEquals(2, $res->length);
			$this->assertEquals('div', $res[0]->name);
			$this->assertEquals('img', $res[1]->name);
			$this->assertEquals('id0', (string)$res[1]->attr('id'));

			$res = $this->find(':not(img ~ img)');
			$this->assertEquals(2, $res->length);
			$this->assertEquals('div', $res[0]->name);
			$this->assertEquals('img', $res[1]->name);
			$this->assertEquals('id0', (string)$res[1]->attr('id'));

			$res = $this->find(':not(div, img.class0)');
			$this->assertEquals(2, $res->length);
			$this->assertEquals('img', $res[0]->name);
			$this->assertEquals('id1', (string)$res[0]->attr('id'));
			$this->assertEquals('img', $res[1]->name);
			$this->assertEquals('id2', (string)$res[1]->attr('id'));

			// has
			$res = $this->find(':has(div)');
			$this->assertEquals(0, $res->length);

			$res = $this->find(':has(img)');
			$this->assertEquals(1, $res->length);
			$this->assertEquals('div', $res[0]->name);
		}
		$dom->clean();

		// Pseudo classes part 2
		$this->dom = $dom = CDom::fromString($this->test_html2);
		if (true) {
			// empty
			$res = $this->find(':empty');
			$this->assertEquals(7, $res->length);
			$this->assertEquals('meta', $res[0]->name);
			$this->assertEquals('br', $res[1]->name);
			$this->assertEquals('li', $res[2]->name);
			$this->assertEquals('hr', $res[3]->name);
			$this->assertEquals('p', $res[4]->name);
			$this->assertEquals('p', $res[5]->name);

			$res = $this->find('*:empty');
			$this->assertEquals(7, $res->length);

			$res = $this->find(':empty:empty');
			$this->assertEquals(7, $res->length);

			$res = $this->find(':empty :empty');
			$this->assertEquals(0, $res->length);

			$res = $this->find('hr:empty');
			$this->assertEquals(1, $res->length);
			$this->assertEquals('hr', $res[0]->name);

			$res = $this->find('p:empty');
			$this->assertEquals(2, $res->length);

			$res = $this->find('[id]:empty');
			$this->assertEquals(3, $res->length);

			$res = $this->find('[id][id]:empty[id]');
			$this->assertEquals(3, $res->length);

			$res = $this->find('#p1:empty');
			$this->assertEquals(1, $res->length);
			$this->assertEquals('p', $res[0]->name);


			// parent
			$res = $this->find(':parent');
			$this->assertEquals(11, $res->length);

			$res = $this->find(':parent:parent');
			$this->assertEquals(11, $res->length);

			$res = $this->find(':parent :parent');
			$this->assertEquals(10, $res->length);

			$res = $this->find(':parent ~ :parent');
			$this->assertEquals(5, $res->length);

			$res = $this->find(':parent :parent :parent');
			$this->assertEquals(8, $res->length);

			$res = $this->find(':parent + :parent');
			$this->assertEquals(3, $res->length);

			$res = $this->find(':parent :parent + :parent');
			$this->assertEquals(3, $res->length);

			$res = $this->find(':parent ~ :parent + :parent');
			$this->assertEquals(2, $res->length);

			$res = $this->find('p:parent');
			$this->assertEquals(1, $res->length);


			// first-child
			$res = $this->find(':first-child');
			$this->assertEquals(5, $res->length);

			$res = $this->find(':first-child:first-child');
			$this->assertEquals(5, $res->length);

			$res = $this->find(':first-child :first-child');
			$this->assertEquals(3, $res->length);


			// last-child
			$res = $this->find(':last-child');
			$this->assertEquals(5, $res->length);

			$res = $this->find(':last-child:last-child');
			$this->assertEquals(5, $res->length);

			$res = $this->find(':last-child :last-child');
			$this->assertEquals(3, $res->length);


			// only-child
			$res = $this->find(':only-child');
			$this->assertEquals(1, $res->length);

			$res = $this->find(':only-child:only-child');
			$this->assertEquals(1, $res->length);


			// header
			$res = $this->find(':header');
			$this->assertEquals(2, $res->length);
			$this->assertEquals('h1', $res[0]->name);
			$this->assertEquals('h6', $res[1]->name);


			// nth-child(n)
			$res = $this->find(':nth-child(2)');
			$this->assertEquals(4, $res->length);
			$this->assertEquals('title', $res[0]->name);
			$this->assertEquals('body', $res[1]->name);
			$this->assertEquals('list', $res[2]->name);
			$this->assertEquals('li', $res[3]->name);
			$this->assertEquals('li2', (string)$res[3]->attr('id'));

			$res = $this->find(':nth-child(3)');
			$this->assertEquals(2, $res->length);
			$this->assertEquals('li', $res[0]->name);
			$this->assertEquals('li3', (string)$res[0]->attr('id'));
			$this->assertEquals('hr', $res[1]->name);

			$res = $this->find(':nth-child(4)');
			$this->assertEquals(2, $res->length);

			$res = $this->find(':nth-child(5)');
			$this->assertEquals(1, $res->length);

			$res = $this->find(':nth-child(6)');
			$this->assertEquals(1, $res->length);

			$res = $this->find(':nth-child(7)');
			$this->assertEquals(1, $res->length);

			$res = $this->find(':nth-child(8)');
			$this->assertEquals(1, $res->length);

			$res = $this->find(':nth-child(9)');
			$this->assertEquals(0, $res->length);

			$res = $this->find(':nth-child(0)');
			$this->assertEquals(0, $res->length);

			$res = $this->find(':nth-child()');
			$this->assertEquals(0, $res->length);

			$res = $this->find(':nth-child(-1)');
			$this->assertEquals(0, $res->length);

			$res = $this->find(':nth-child');
			$this->assertEquals(0, $res->length);


			// nth-last-child(n)
			$res = $this->find(':nth-last-child(1)');
			$this->assertEquals(5, $res->length);

			$res = $this->find(':nth-last-child(2)');
			$this->assertEquals(4, $res->length);

			$res = $this->find(':nth-last-child(3)');
			$this->assertEquals(2, $res->length);

			$res = $this->find(':nth-last-child(4)');
			$this->assertEquals(2, $res->length);

			$res = $this->find(':nth-last-child(5)');
			$this->assertEquals(1, $res->length);

			$res = $this->find(':nth-last-child(6)');
			$this->assertEquals(1, $res->length);

			$res = $this->find(':nth-last-child(7)');
			$this->assertEquals(1, $res->length);

			$res = $this->find(':nth-last-child(8)');
			$this->assertEquals(1, $res->length);

			$res = $this->find(':nth-last-child(9)');
			$this->assertEquals(0, $res->length);


			// contains(t)
			$res = $this->find(':contains(text)');
			$this->assertEquals(8, $res->length);

			$res = $this->find(':contains(example)');
			$this->assertEquals(0, $res->length);

			$res = $this->find(':contains(Example)');
			$this->assertEquals(3, $res->length);

			$res = $this->find(':contains(header)');
			$this->assertEquals(4, $res->length);

			$res = $this->find(':contains(some)');
			$this->assertEquals(0, $res->length);


			// nth-of-type(n)
			$res = $this->find(':nth-of-type(z)');
			$this->assertEquals(0, $res->length);

			$res = $this->find(':nth-of-type(1)');
			$this->assertEquals(12, $res->length);

			$res = $this->find(':nth-of-type(2)');
			$this->assertEquals(2, $res->length);

			$res = $this->find(':nth-of-type(3)');
			$this->assertEquals(2, $res->length);

			$res = $this->find(':nth-of-type(4)');
			$this->assertEquals(1, $res->length);

			$res = $this->find(':nth-of-type(5)');
			$this->assertEquals(0, $res->length);


			// nth-of-type(n)
			$res = $this->find(':only-of-type');
			$this->assertEquals(10, $res->length);

			$res = $this->find(':only-of-type:only-of-type');
			$this->assertEquals(10, $res->length);

			$res = $this->find(':only-of-type(ertertrt)');
			$this->assertEquals(10, $res->length);

			$res = $this->find(':only-of-type :only-of-type');
			$this->assertEquals(8, $res->length);

			$res = $this->find(':only-of-type :only-of-type :only-of-type');
			$this->assertEquals(5, $res->length);


			// first-of-type
			$res = $this->find(':first-of-type');
			$this->assertEquals(12, $res->length);

			$res = $this->find(':first-of-type(1232431)');
			$this->assertEquals(12, $res->length);

			$res = $this->find(':first-of-type :first-of-type');
			$this->assertEquals(10, $res->length);

			$res = $this->find(':first-of-type :first-of-type :first-of-type');
			$this->assertEquals(7, $res->length);


			// nth-last-of-type(n)
			$res = $this->find(':nth-last-of-type(1)');
			$this->assertEquals(12, $res->length);

			$res = $this->find(':nth-last-of-type(2)');
			$this->assertEquals(2, $res->length);

			$res = $this->find(':nth-last-of-type(3)');
			$this->assertEquals(2, $res->length);

			$res = $this->find(':nth-last-of-type(4)');
			$this->assertEquals(1, $res->length);

			$res = $this->find(':nth-last-of-type(5)');
			$this->assertEquals(0, $res->length);


			// last-of-type
			$res = $this->find(':last-of-type');
			$this->assertEquals(12, $res->length);

			$res = $this->find(':last-of-type(12341325dfgsd)');
			$this->assertEquals(12, $res->length);

			$res = $this->find(':last-of-type :last-of-type');
			$this->assertEquals(10, $res->length);

			$res = $this->find(':last-of-type :last-of-type :last-of-type');
			$this->assertEquals(7, $res->length);


			// not(s)
			$res = $this->find(':not(*)');
			$this->assertEquals(0, $res->length);

			$res = $this->find(':not(li)');
			$this->assertEquals(14, $res->length);

			$res = $this->find(':not(li,p)');
			$this->assertEquals(11, $res->length);

			$res = $this->find('li:not(:empty)');
			$this->assertEquals(3, $res->length);


			// has(s)
			$res = $this->find(':has');
			$this->assertEquals(5, $res->length);

			$res = $this->find(':has(li)');
			$this->assertEquals(1, $res->length);

			$res = $this->find(':has(p)');
			$this->assertEquals(1, $res->length);
		}
		$dom->clean();
	}

	/**
	 * Tests matched set filtering selectors
	 *
	 * @covers CDomSelector
	 */
	function testMatchedSetFilters()
	{
		$this->dom = $dom = CDom::fromString($this->test_html2);

		$res = $this->find('*');
		$this->assertEquals(18, $res->length);
		$this->assertEquals('html', $res[0]->name);
		$this->assertEquals('head', $res[1]->name);
		$this->assertEquals('meta', $res[2]->name);
		$this->assertEquals('title', $res[3]->name);
		$this->assertEquals('body', $res[4]->name);
		$this->assertEquals('div', $res[5]->name);
		$this->assertEquals('br', $res[6]->name);
		$this->assertEquals('list', $res[7]->name);


		// :first
		$res = $this->find(':first');
		$this->assertEquals(1, $res->length);
		$this->assertEquals('html', $res[0]->name);

		$res = $this->find(':first *');
		$this->assertEquals(17, $res->length);
		$this->assertEquals('head', $res[0]->name);
		$this->assertEquals('meta', $res[1]->name);
		$this->assertEquals('title', $res[2]->name);
		$this->assertEquals('body', $res[3]->name);
		$this->assertEquals('div', $res[4]->name);

		$res = $this->find('li:first');
		$this->assertEquals(1, $res->length);
		$this->assertEquals('li', $res[0]->name);
		$this->assertEquals('li1', (string)$res[0]->attr('id'));


		// :last
		$res = $this->find(':last');
		$this->assertEquals(1, $res->length);
		$this->assertEquals('h6', $res[0]->name);

		$res = $this->find(':last *');
		$this->assertEquals(0, $res->length);

		$res = $this->find('li:last');
		$this->assertEquals(1, $res->length);
		$this->assertEquals('li', $res[0]->name);
		$this->assertEquals('li4', (string)$res[0]->attr('id'));

		$res = $this->find('nothing:last');
		$this->assertEquals(0, $res->length);


		// :eq()
		$res = $this->find(':eq(3)');
		$this->assertEquals(1, $res->length);
		$this->assertEquals('title', $res[0]->name);

		$res = $this->find('body :eq(3)');
		$this->assertEquals(1, $res->length);
		$this->assertEquals('li', $res[0]->name);
		$this->assertEquals('li1', (string)$res[0]->attr('id'));

		$res = $this->find(':header:eq(1)');
		$this->assertEquals(1, $res->length);
		$this->assertEquals('h6', $res[0]->name);


		// :lt()
		$res = $this->find(':lt(4)');
		$this->assertEquals(4, $res->length);
		$this->assertEquals('html', $res[0]->name);
		$this->assertEquals('head', $res[1]->name);
		$this->assertEquals('meta', $res[2]->name);
		$this->assertEquals('title', $res[3]->name);


		// :gt()
		$res = $this->find(':gt(4)');
		$this->assertEquals(13, $res->length);
		$this->assertEquals('div', $res[0]->name);
		$this->assertEquals('br', $res[1]->name);
		$this->assertEquals('list', $res[2]->name);
		$this->assertEquals('li', $res[3]->name);

		$res = $this->find('li:gt(9)');
		$this->assertEquals(0, $res->length);


		// :even
		$res = $this->find(':even');
		$this->assertEquals(9, $res->length);
		$this->assertEquals('html', $res[0]->name);
		$this->assertEquals('meta', $res[1]->name);
		$this->assertEquals('body', $res[2]->name);
		$this->assertEquals('br', $res[3]->name);

		$res = $this->find(':even *');
		$this->assertEquals(17, $res->length);
		$this->assertEquals('head', $res[0]->name);
		$this->assertEquals('meta', $res[1]->name);
		$this->assertEquals('title', $res[2]->name);
		$this->assertEquals('body', $res[3]->name);

		$res = $this->find(':even:even');
		$this->assertEquals(5, $res->length);
		$this->assertEquals('html', $res[0]->name);
		$this->assertEquals('body', $res[1]->name);
		$this->assertEquals('li', $res[2]->name);
		$this->assertEquals('hr', $res[3]->name);

		$res = $this->find(':even:odd');
		$this->assertEquals(4, $res->length);
		$this->assertEquals('meta', $res[0]->name);
		$this->assertEquals('br', $res[1]->name);
		$this->assertEquals('li', $res[2]->name);
		$this->assertEquals('p', $res[3]->name);


		// :odd
		$res = $this->find(':odd');
		$this->assertEquals(9, $res->length);
		$this->assertEquals('head', $res[0]->name);
		$this->assertEquals('title', $res[1]->name);
		$this->assertEquals('div', $res[2]->name);
		$this->assertEquals('list', $res[3]->name);

		$res = $this->find(':odd *');
		$this->assertEquals(14, $res->length);
		$this->assertEquals('meta', $res[0]->name);
		$this->assertEquals('title', $res[1]->name);
		$this->assertEquals('br', $res[2]->name);
		$this->assertEquals('list', $res[3]->name);

		$res = $this->find(':odd:odd');
		$this->assertEquals(4, $res->length);
		$this->assertEquals('title', $res[0]->name);
		$this->assertEquals('list', $res[1]->name);
		$this->assertEquals('li', $res[2]->name);
		$this->assertEquals('p', $res[3]->name);

		$res = $this->find(':odd:even');
		$this->assertEquals(5, $res->length);
		$this->assertEquals('head', $res[0]->name);
		$this->assertEquals('div', $res[1]->name);
		$this->assertEquals('li', $res[2]->name);
		$this->assertEquals('p', $res[3]->name);


		$dom->clean();
	}

	/**
	 * Tests nth-* pseudo-classes
	 *
	 * @covers CDomSelector
	 */
	function testNthRule()
	{
		$sel = new CDomSelector('*');
		$parseNthRule = new ReflectionMethod($sel, 'parseNthRule');
		$parseNthRule->setAccessible(true);

		// Rule parsing
		$data = array(
			' 3n + 1 '	=> array(3, 1),
			' +3n - 2 '	=> array(3, -2),
			' -n+ 6'	=> array(-1, 6),
			' +6 '		=> array(1, 6),
			'1n+0'		=> array(1, 0),
			'n+0'		=> array(1, 0),
			'n'			=> array(1, 0),
			'0n+5'		=> array(0, 5),
			'5'			=> array(0, 5),
			'10n-1'		=> array(10, -1),
			'10n+9'		=> array(10, 9),
			'2n+0'		=> array(2, 0),
			'2n'		=> array(2, 0),
			'0n+0'		=> array(0, 0),
			'odd'		=> array(2, 1),
			'even'		=> array(2, 0),
			'0n+0'		=> array(0, 0),
			'0n'		=> array(0, 0),
			'0'			=> array(0, 0),
			'3 n'		=> array(0, 0),
			'+ 2n'		=> array(0, 0),
			'2n+-0'		=> array(0, 0),
			'eeer'		=> array(0, 0),
		);
		foreach ($data as $value => $expected) {
			$res = $parseNthRule->invoke($sel, $value);
			$this->assertEquals($expected, $res);
		}

		// Matching
		$numberMatchNthRule = new ReflectionMethod($sel, 'numberMatchNthRule');
		$numberMatchNthRule->setAccessible(true);
		$data = array(
			'0n+0'	=> array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0),
			'0n'	=> array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0),
			'0'		=> array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0),
			'-n'	=> array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0),
			'3 n'	=> array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0),
			'+ 2n'	=> array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0),
			'10n+-1'=> array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0),
			'odd'	=> array(1,0,1,0,1,0,1,0,1,0,1,0,1,0,1,0,1,0,1,0),
			'2n+1'	=> array(1,0,1,0,1,0,1,0,1,0,1,0,1,0,1,0,1,0,1,0),
			'even'	=> array(0,1,0,1,0,1,0,1,0,1,0,1,0,1,0,1,0,1,0,1),
			'2n+0'	=> array(0,1,0,1,0,1,0,1,0,1,0,1,0,1,0,1,0,1,0,1),
			'2n'	=> array(0,1,0,1,0,1,0,1,0,1,0,1,0,1,0,1,0,1,0,1),
			'5'		=> array(0,0,0,0,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0),
			'+5'	=> array(0,0,0,0,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0),
			'0n+5'	=> array(0,0,0,0,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0),
			'2n+3'	=> array(0,0,1,0,1,0,1,0,1,0,1,0,1,0,1,0,1,0,1,0),
			'3n+1'	=> array(1,0,0,1,0,0,1,0,0,1,0,0,1,0,0,1,0,0,1,0),
			'3n+2'	=> array(0,1,0,0,1,0,0,1,0,0,1,0,0,1,0,0,1,0,0,1),
			'+3n+2'	=> array(0,1,0,0,1,0,0,1,0,0,1,0,0,1,0,0,1,0,0,1),
			'3n+3'	=> array(0,0,1,0,0,1,0,0,1,0,0,1,0,0,1,0,0,1,0,0),
			'3n+4'	=> array(0,0,0,1,0,0,1,0,0,1,0,0,1,0,0,1,0,0,1,0),
			'3n+5'	=> array(0,0,0,0,1,0,0,1,0,0,1,0,0,1,0,0,1,0,0,1),
			'3n+6'	=> array(0,0,0,0,0,1,0,0,1,0,0,1,0,0,1,0,0,1,0,0),
			'3n-1'	=> array(0,1,0,0,1,0,0,1,0,0,1,0,0,1,0,0,1,0,0,1),
			'3n-2'	=> array(1,0,0,1,0,0,1,0,0,1,0,0,1,0,0,1,0,0,1,0),
			'1n+0'	=> array(1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1),
			'n+0'	=> array(1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1),
			'1n'	=> array(1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1),
			'n'		=> array(1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1),
			'n'		=> array(1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1),
			'-n+6'	=> array(1,1,1,1,1,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0),
			'-2n-6'	=> array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0),
		);
		foreach ($data as $s => $variants) {
			$rule = $parseNthRule->invoke($sel, $s);
			foreach ($variants as $i => $expected) {
				$i++;
				$res = $numberMatchNthRule->invoke($sel, $i, $rule);
				$message = "Assertion $i for rule '$s'";
				if ($expected) {
					$this->assertTrue($res, $message);
				} else {
					$this->assertFalse($res, $message);
				}
			}
		}


		// Searching
		$html = '';
		for ($i = 1; $i <= 20; $i++) {
			// $i & 1 - is_odd
			$t = $i & 1 ? 'i' : 'b';
			$html .= " <$t n=$i>$i</$t>";
		}
		$this->dom = $dom = CDom::fromString($html);

		$res = $this->find(':nth-child(0n+0)');
		$this->assertEquals(0, $res->length);

		$res = $this->find(':nth-child(0n)');
		$this->assertEquals(0, $res->length);

		$res = $this->find(':nth-child(0)');
		$this->assertEquals(0, $res->length);

		$res = $this->find(':nth-child(3 n)');
		$this->assertEquals(0, $res->length);

		$res = $this->find(':nth-child(+ 2n)');
		$this->assertEquals(0, $res->length);

		$res = $this->find(':nth-child(odd)');
		$this->assertEquals(10, $res->length);
		$this->assertEquals('i', $res[0]->name);
		$this->assertEquals(1, (string)$res[0]->attr('n'));
		$this->assertEquals('i', $res[1]->name);
		$this->assertEquals(3, (string)$res[1]->attr('n'));
		$this->assertEquals('i', $res[2]->name);
		$this->assertEquals(5, (string)$res[2]->attr('n'));
		$this->assertEquals('i', $res[3]->name);
		$this->assertEquals(7, (string)$res[3]->attr('n'));
		$this->assertEquals('i', $res[4]->name);
		$this->assertEquals(9, (string)$res[4]->attr('n'));
		$this->assertEquals('i', $res[5]->name);
		$this->assertEquals(11, (string)$res[5]->attr('n'));

		$res = $this->find(':nth-child(even)');
		$this->assertEquals(10, $res->length);
		$this->assertEquals('b', $res[0]->name);
		$this->assertEquals(2, (string)$res[0]->attr('n'));
		$this->assertEquals('b', $res[1]->name);
		$this->assertEquals(4, (string)$res[1]->attr('n'));
		$this->assertEquals('b', $res[2]->name);
		$this->assertEquals(6, (string)$res[2]->attr('n'));
		$this->assertEquals('b', $res[3]->name);
		$this->assertEquals(8, (string)$res[3]->attr('n'));
		$this->assertEquals('b', $res[4]->name);
		$this->assertEquals(10, (string)$res[4]->attr('n'));
		$this->assertEquals('b', $res[5]->name);
		$this->assertEquals(12, (string)$res[5]->attr('n'));

		$res = $this->find(':nth-child(0n+5)');
		$this->assertEquals(1, $res->length);
		$this->assertEquals('i', $res[0]->name);
		$this->assertEquals(5, (string)$res[0]->attr('n'));

		$res = $this->find(':nth-child(5)');
		$this->assertEquals(1, $res->length);
		$this->assertEquals('i', $res[0]->name);
		$this->assertEquals(5, (string)$res[0]->attr('n'));

		$res = $this->find(':nth-child(2n+3)');
		$this->assertEquals('i', $res[0]->name);
		$this->assertEquals(3, (string)$res[0]->attr('n'));
		$this->assertEquals('i', $res[1]->name);
		$this->assertEquals(5, (string)$res[1]->attr('n'));
		$this->assertEquals('i', $res[2]->name);
		$this->assertEquals(7, (string)$res[2]->attr('n'));
		$this->assertEquals('i', $res[3]->name);
		$this->assertEquals(9, (string)$res[3]->attr('n'));
		$this->assertEquals('i', $res[4]->name);
		$this->assertEquals(11, (string)$res[4]->attr('n'));

		$res = $this->find(':nth-child(3n+1)');
		$this->assertEquals(7, $res->length);
		$this->assertEquals('i', $res[0]->name);
		$this->assertEquals(1, (string)$res[0]->attr('n'));
		$this->assertEquals('b', $res[1]->name);
		$this->assertEquals(4, (string)$res[1]->attr('n'));
		$this->assertEquals('i', $res[2]->name);
		$this->assertEquals(7, (string)$res[2]->attr('n'));
		$this->assertEquals('b', $res[3]->name);
		$this->assertEquals(10, (string)$res[3]->attr('n'));

		$res = $this->find(':nth-child(3n+2)');
		$this->assertEquals(7, $res->length);
		$this->assertEquals('b', $res[0]->name);
		$this->assertEquals(2, (string)$res[0]->attr('n'));
		$this->assertEquals('i', $res[1]->name);
		$this->assertEquals(5, (string)$res[1]->attr('n'));
		$this->assertEquals('b', $res[2]->name);
		$this->assertEquals(8, (string)$res[2]->attr('n'));
		$this->assertEquals('i', $res[3]->name);
		$this->assertEquals(11, (string)$res[3]->attr('n'));

		$res = $this->find(':nth-child(3n-1)');
		$this->assertEquals(7, $res->length);
		$this->assertEquals('b', $res[0]->name);
		$this->assertEquals(2, (string)$res[0]->attr('n'));
		$this->assertEquals('i', $res[1]->name);
		$this->assertEquals(5, (string)$res[1]->attr('n'));
		$this->assertEquals('b', $res[2]->name);
		$this->assertEquals(8, (string)$res[2]->attr('n'));
		$this->assertEquals('i', $res[3]->name);
		$this->assertEquals(11, (string)$res[3]->attr('n'));

		$res = $this->find(':nth-child(-n+2)');
		$this->assertEquals(2, $res->length);
		$this->assertEquals('i', $res[0]->name);
		$this->assertEquals(1, (string)$res[0]->attr('n'));
		$this->assertEquals('b', $res[1]->name);
		$this->assertEquals(2, (string)$res[1]->attr('n'));


		$dom->clean();
	}

	/**
	 * Tests attributes manipulation
	 *
	 * @covers CDomNode
	 * @covers CDomList
	 * @covers CDomNodesList
	 * @covers CDomAttribute
	 * @covers CDomAttributesList
	 */
	function testAttributes()
	{
		$dom = CDom::fromString('<div>');
		$div = $dom->firstChild;
		$val = $div->attr('class');
		$this->assertEmpty($val);

		$div->attr('class', 'foo');
		$val = $div->attr('Class');
		$this->assertEquals('foo', (string)$val);

		$this->assertEquals('<div class="foo"></div>', $dom->html());

		$div->selfClosed = true;
		$this->assertEquals('<div class="foo" />', $dom->html());

		$div->attr('class', 'bar');
		$val = $div->attr('claSS');
		$this->assertEquals('bar', (string)$val);

		$this->assertEquals('<div class="bar" />', $dom->html());

		$this->assertEquals(' class="bar"', $dom->firstChild->attributes->text());

		$res = $div->hasAttribute('id');
		$this->assertFalse($res);

		$res = isset($div->attributes->id);
		$this->assertFalse($res);

		$res = $div->hasAttribute('class');
		$this->assertTrue($res);

		$res = isset($div->attributes->class);
		$this->assertTrue($res);

		$div->removeAttr('class');

		$res = $div->hasAttribute('class');
		$this->assertFalse($res);

		$res = isset($div->attributes->class);
		$this->assertFalse($res);

		$this->assertEquals('<div />', $div->outerHtml());

		$div->attributes->class = 'bar';
		$this->assertTrue(isset($div->attributes->class));

		$this->assertEquals('bar', (string)$div->attributes->class);
		$this->assertEquals('bar', (string)$div->class);

		unset($div->attributes->class);
		$this->assertFalse(isset($div->attributes->class));

		$div->class = 'zoo';
		$this->assertEquals('zoo', (string)$div->attr('class'));

		unset($div->attributes['class']);
		$this->assertFalse($div->attributes->has('class'));

		$div->attributes['class'] = 'test';
		$this->assertEquals('test', (string)$div->class);

		$val = $div->attr('foobar');
		$this->assertEmpty($val);


		$orig = 'DisaBled';
		$low = 'disabled';
		$val = 'Yeah';
		$attr = new CDomAttribute($orig, $val);
		$this->assertEquals($low, $attr->name);
		$this->assertEquals($orig, $attr->nameReal);
		$this->assertEquals($val, (string)$attr);
		$this->assertEquals($val, $attr());
		$attr(true);
		$this->assertEquals($low, $attr->text());
		$this->assertEquals($low, $attr->html());

		$dom->clean();


		$dom = CDom::fromString('<div disabled>');
		$dom->isXml = true;
		$this->assertEquals('<div disabled="disabled"></div>', $dom->html());

		$dom->clean();

		$list = new CDomNodesList();
		$this->assertFalse($list->hasAttribute('class'));
		$list->attr('class', 'zzz');
		$this->assertFalse($list->hasAttribute('class'));
		$list->add(new CDomNodeTag('p'));
		$this->assertFalse($list->hasAttribute('class'));
		$list->attr('class', 'zzz');
		$this->assertTrue($list->hasAttribute('class'));
		$list->removeAttr('class');
		$this->assertFalse($list->hasAttribute('class'));
	}

	/**
	 * Tests manipulation
	 *
	 * @covers CDomDocument
	 * @covers CDomAttributesList
	 * @covers CDomAttribute
	 * @covers CDomList
	 * @covers CDomNode
	 * @covers CDomNodeCdata
	 * @covers CDomNodeCommment
	 * @covers CDomNodeDoctype
	 * @covers CDomNodesList
	 * @covers CDomNodeTag
	 * @covers CDomNodeText
	 * @covers CDomNodeXmlDeclaration
	 * @covers CDom
	 */
	function testManipulation()
	{
		$dom = CDom::fromString($this->test_html);
		$this->checkStructure($dom);
		$div1 = $dom->getElementByTagName('div');


		// Clone
		$div2 = $div1->clone();
		$this->checkStructure($div2);

		$this->assertNotEquals($div1->uniqId, $div2->uniqId);
		$this->assertEmpty($div2->parent);
		$this->assertNotEmpty($div2->ownerDocument);

		$test = 1234;
		$div2->attr('n', $test);
		$this->assertNotEquals($test, (string)$div1->attr('n'));
		$this->assertEquals($test, (string)$div2->attr('n'));

		$list = $dom->find('div');
		$list2 = $list->clone();
		$this->assertEquals($list->length, $list2->length);
		$this->assertNotEquals($list[0]->uniqId, $list2[0]->uniqId);
		$this->assertEmpty($list2[0]->parent);

		$dom2 = CDom::fromString('<div/>text<br>');
		$dom2 = clone $dom2;
		$this->checkStructure($dom2);
		$dom2->clean();

		$dom2 = CDom::fromString('<div/>text<br>');
		$dom2 = clone $dom2;
		$this->checkStructure($dom2);
		$dom2->clean();


		// Empty
		$this->assertEquals(3, count($list->children));
		$this->assertEquals(7, count($list->nodes));
		$this->assertNotEmpty($list->firstChild);
		$this->assertNotEmpty($list->lastChild);
		$list->empty();
		$this->assertEquals(0, count($list->children));
		$this->assertEquals(0, count($list->nodes));
		$this->assertEmpty($list->firstChild);
		$this->assertEmpty($list->lastChild);

		$this->assertEquals(1, count($dom->children));
		$this->assertEquals(1, count($dom->nodes));
		$this->assertNotEmpty($dom->firstChild);
		$this->assertNotEmpty($dom->lastChild);

		$dom->empty();
		$this->assertEquals(0, count($dom->nodes));
		$this->checkStructure($dom);


		// Html
		$html = '<b>test</b>';
		$dom->html($html);
		$this->checkStructure($dom);
		$this->assertEquals(1, count($dom->children));
		$this->assertEquals(1, count($dom->nodes));
		$this->assertNotEmpty($dom->firstChild);
		$this->assertNotEmpty($dom->lastChild);
		$this->assertEquals($html, $dom->html());
		$b = $dom->firstChild;
		$this->assertEquals('b', $b->name);
		$this->assertEquals(0, count($b->children));
		$this->assertEquals(1, count($b->nodes));
		$text = $b->firstNode;
		$this->assertEquals(CDom::NODE_TEXT, $text->type);

		$text->html('<br>');
		$this->assertEquals(0, count($text->children));
		$this->assertEquals(0, count($text->nodes));
		$this->assertEquals('<br>', $text->value);

		$list = array(
			new CDomNodeTag('br', true),
			new CDomNodeText('text'),
			new CDomNodeTag('b'),
		);
		$dom->html($list);
		$this->checkStructure($dom);
		$this->assertEquals(2, count($dom->children));
		$this->assertEquals(3, count($dom->nodes));
		$this->assertEquals('<br />text<b></b>', $dom->html());

		$dom->html('');
		$this->assertEquals(0, count($dom->children));
		$this->assertEquals(0, count($dom->nodes));

		$nlist = new CDomNodesList();
		$nlist->add($list);
		$dom->html($nlist);
		$this->checkStructure($dom);
		$this->assertEquals(2, count($dom->children));
		$this->assertEquals(3, count($dom->nodes));
		$this->assertEquals('<br />text<b></b>', $dom->html());
		$this->assertEquals('text', $dom->text());

		$br = new CDomNodeTag('br');
		$this->assertTrue($br->selfClosed);
		$br->html('<p>example</p>');
		$this->assertFalse($br->selfClosed);
		$this->assertEquals(1, count($br->children));
		$this->assertEquals(1, count($br->nodes));
		$this->assertEquals('<p>example</p>', $br->html());
		$this->assertEquals('example', $br->text());
		$this->assertEquals('<br><p>example</p></br>', $br->outerHtml());


		// Text
		$html = '<b>test</b>';
		$dom->empty()->text($html);
		$this->checkStructure($dom);
		$this->assertEquals(0, count($dom->children));
		$this->assertEquals(1, count($dom->nodes));
		$this->assertEquals($html, $dom->text());

		$node = new CDomNodeTag('p');
		$node->text($list);
		$this->assertEquals(0, count($node->children));
		$this->assertEquals(1, count($node->nodes));
		$this->assertEquals('text', $node->text());
		$this->assertEquals('text', $node->html());

		$node->empty()->text($nlist);
		$this->assertEquals(0, count($node->children));
		$this->assertEquals(1, count($node->nodes));
		$this->assertEquals('text', $node->text());
		$this->assertEquals('text', $node->html());

		$node->empty()->text(new CDomNodeTag('br'));
		$this->assertEquals(0, count($node->children));
		$this->assertEquals(1, count($node->nodes));
		$this->assertEquals('', $node->text());
		$this->assertEquals('', $node->html());

		$text = 'example';
		$tnode = new CDomNodeText($text);
		$node->text($tnode);
		$this->assertEquals(0, count($node->children));
		$this->assertEquals(1, count($node->nodes));
		$this->assertEquals($text, $node->text());
		$this->assertEquals($text, $node->html());
		$this->assertEquals($tnode->parent, $node);
		$this->assertEquals($tnode->ownerDocument, $node->ownerDocument);

		$tnode->text($node);
		$this->assertEquals(0, count($tnode->children));
		$this->assertEquals(0, count($tnode->nodes));
		$this->assertEquals($text, $tnode->text());
		$this->assertEquals($text, $tnode->html());

		$tnode->text($list);
		$this->assertEquals(0, count($tnode->children));
		$this->assertEquals(0, count($tnode->nodes));
		$this->assertEquals('text', $tnode->text());
		$this->assertEquals('text', $tnode->html());

		$tnode->text($nlist);
		$this->assertEquals(0, count($tnode->children));
		$this->assertEquals(0, count($tnode->nodes));
		$this->assertEquals('text', $tnode->text());
		$this->assertEquals('text', $tnode->html());

		$br = new CDomNodeTag('br');
		$this->assertTrue($br->selfClosed);
		$br->text('example');
		$this->assertFalse($br->selfClosed);
		$this->assertEquals(0, count($br->children));
		$this->assertEquals(1, count($br->nodes));
		$this->assertEquals('example', $br->html());
		$this->assertEquals('example', $br->text());


		// Detach
		$dom->html('<br>text<br>text<br>text');
		$this->assertEquals(3, count($dom->children));
		$this->assertEquals(6, count($dom->nodes));
		$this->checkStructure($dom);
		$cnid = -1;
		$e = $dom->nodes[++$cnid];
		$this->assertEquals(CDom::NODE_ELEMENT, $e->type);
		$e = $dom->nodes[++$cnid];
		$this->assertEquals(CDom::NODE_TEXT, $e->type);
		$e = $dom->nodes[++$cnid];
		$this->assertEquals(CDom::NODE_ELEMENT, $e->type);
		$e = $dom->nodes[++$cnid];
		$this->assertEquals(CDom::NODE_TEXT, $e->type);
		$e = $dom->nodes[++$cnid];
		$this->assertEquals(CDom::NODE_ELEMENT, $e->type);
		$e = $dom->nodes[++$cnid];
		$this->assertEquals(CDom::NODE_TEXT, $e->type);

		$d = $dom->nodes[2];
		$this->assertNotEmpty($d->next);
		$this->assertNotEmpty($d->prev);
		$this->assertNotEmpty($d->parent);
		$d->detach();
		$this->assertEmpty($d->next);
		$this->assertEmpty($d->prev);
		$this->assertEmpty($d->parent);
		$this->assertEquals(-1, $d->cnid);
		$this->assertEquals(-1, $d->chid);

		$this->checkStructure($dom);
		$this->assertEquals(2, count($dom->children));
		$this->assertEquals(5, count($dom->nodes));
		$cnid = -1;
		$e = $dom->nodes[++$cnid];
		$this->assertEquals(CDom::NODE_ELEMENT, $e->type);
		$e = $dom->nodes[++$cnid];
		$this->assertEquals(CDom::NODE_TEXT, $e->type);
		$e = $dom->nodes[++$cnid];
		$this->assertEquals(CDom::NODE_TEXT, $e->type);
		$e = $dom->nodes[++$cnid];
		$this->assertEquals(CDom::NODE_ELEMENT, $e->type);
		$e = $dom->nodes[++$cnid];
		$this->assertEquals(CDom::NODE_TEXT, $e->type);

		$dom->html('<br>');
		$dom->firstChild->detach();
		$this->assertEquals(0, count($dom->children));
		$this->assertEquals(0, count($dom->nodes));
		$this->checkStructure($dom);

		$dom->html('<br>text<br>text<br>text');
		$dom->nodes[1]->detach();
		$this->assertEquals(3, count($dom->children));
		$this->assertEquals(5, count($dom->nodes));
		$this->checkStructure($dom);

		$list = $dom->find('br')->detach();
		$this->assertEquals(0, count($dom->children));
		$this->assertEquals(2, count($dom->nodes));
		$this->assertEquals(3, $list->length);
		$dom->append($list);
		$this->assertEquals('texttext<br /><br /><br />', $dom->html());


		// Remove
		$dom->html('<p><br>text</p>');
		$dom->firstChild->firstNode->remove();
		$this->checkStructure($dom);
		$dom->firstChild->firstNode->remove();
		$this->checkStructure($dom);
		$this->assertEquals(0, count($dom->firstChild->children));
		$this->assertEquals(0, count($dom->firstChild->nodes));

		$list = $dom->html('<br>text<br>text<br>text')->find('br')->remove();
		$this->assertEquals(0, count($dom->children));
		$this->assertEquals(3, count($dom->nodes));
		$this->assertEquals(0, $list->length);


		// Append
		$dom->empty()->append($dom);
		$this->assertEquals(0, count($dom->children));
		$this->assertEquals(0, count($dom->nodes));

		$dom2 = CDom::fromString('<div/>text<br>');
		$this->checkStructure($dom);
		$this->assertEquals(2, count($dom2->children));
		$this->assertEquals(3, count($dom2->nodes));
		$div = $dom2->firstChild;
		$this->assertEquals('div', $div->name);
		$this->assertEquals($dom2, $div->ownerDocument);
		$this->assertEquals($dom2, $div->parent);
		$uid = $div->uniqId;

		$dom->empty()->append($dom2);
		$this->checkStructure($dom);
		$this->assertEquals(0, count($dom2->children));
		$this->assertEquals(0, count($dom2->nodes));
		$this->assertEmpty($dom2->firstChild);
		$this->assertEmpty($dom2->lastChild);
		$this->assertNotEquals($dom2, $div->ownerDocument);
		$dom2->clean();

		$this->assertEquals(2, count($dom->children));
		$this->assertEquals(3, count($dom->nodes));
		$this->assertEquals('div', $dom->firstChild->name);
		$this->assertEquals($dom, $dom->firstChild->ownerDocument);
		$this->assertEquals($dom, $div->ownerDocument);
		$this->assertEquals($dom, $div->parent);
		$this->assertEquals($uid, $div->uniqId);

		$dom->empty()->append('text');
		$this->assertEquals(0, count($dom->children));
		$this->assertEquals(1, count($dom->nodes));

		$dom2 = CDom::fromString('<div>text</div>');
		$res = $dom->empty()->append($dom2);
		$this->assertTrue($res instanceof CDomDocument);
		$this->assertEquals($dom->uniqId, $res->uniqId);
		$this->checkStructure($dom);
		$this->assertEquals(1, count($dom->children));
		$this->assertEquals(1, count($dom->nodes));
		$this->assertEquals($dom, $dom->firstChild->firstNode->ownerDocument);
		$dom2->clean();

		$dom2 = CDom::fromString('<div>text</div>');
		$list = $dom->html('<p/><p/><p/>')->find('p');
		$this->assertEquals(3, $list->length);
		$list->append($dom2);
		$this->assertEquals(3, $list->length);
		$this->assertEmpty($dom2->nodes);
		$this->checkStructure($dom2);
		$this->checkStructure($dom);
		$this->assertEquals(3, count($dom->nodes));
		$expected = '<p><div>text</div></p><p><div>text</div></p><p><div>text</div></p>';
		$this->assertEquals($expected, $dom->html());
		$dom2->clean();


		// Prepend
		$dom->empty()->prepend('text');
		$this->assertEquals(0, count($dom->children));
		$this->assertEquals(1, count($dom->nodes));
		$this->assertEquals('text', $dom->nodes[0]->value);

		$dom->prepend('string');
		$this->assertEquals(0, count($dom->children));
		$this->assertEquals(2, count($dom->nodes));
		$this->assertEquals('string', $dom->nodes[0]->value);
		$this->assertEquals('text', $dom->nodes[1]->value);

		$list = $dom->html('<p>l</p><p>l</p>')->find('p');
		$this->assertEquals(2, $list->length);
		$list->prepend('<div>text</div>');
		$this->assertEquals(2, $list->length);
		$this->checkStructure($dom);
		$this->assertEquals(2, count($dom->nodes));
		$expected = '<p><div>text</div>l</p><p><div>text</div>l</p>';
		$this->assertEquals($expected, $dom->html());


		// After
		$string = str_repeat('<b/>text', 5);
		$dom->html($string);
		$this->checkStructure($dom);
		$this->assertEquals(5, count($dom->children));
		$this->assertEquals(10, count($dom->nodes));
		$cnid = 2;
		$e = $dom->nodes[++$cnid];
		$this->assertEquals(CDom::NODE_TEXT, $e->type);
		$e = $dom->nodes[++$cnid];
		$this->assertEquals(CDom::NODE_ELEMENT, $e->type);
		$this->assertEquals('b', $e->name);
		$e = $dom->nodes[++$cnid];
		$this->assertEquals(CDom::NODE_TEXT, $e->type);
		$e = $dom->nodes[++$cnid];
		$this->assertEquals(CDom::NODE_ELEMENT, $e->type);
		$this->assertEquals('b', $e->name);

		$dom->nodes[3]->after('string<i/>');
		$this->checkStructure($dom);
		$this->assertEquals(6, count($dom->children));
		$this->assertEquals(12, count($dom->nodes));
		$cnid = 2;
		$e = $dom->nodes[++$cnid];
		$this->assertEquals(CDom::NODE_TEXT, $e->type);
		$e = $dom->nodes[++$cnid];
		$this->assertEquals(CDom::NODE_TEXT, $e->type);
		$e = $dom->nodes[++$cnid];
		$this->assertEquals(CDom::NODE_ELEMENT, $e->type);
		$this->assertEquals('i', $e->name);
		$e = $dom->nodes[++$cnid];
		$this->assertEquals(CDom::NODE_ELEMENT, $e->type);
		$this->assertEquals('b', $e->name);

		$dom->html($string);
		$dom->nodes[4]->after('<i/>string');
		$this->checkStructure($dom);
		$this->assertEquals(6, count($dom->children));
		$this->assertEquals(12, count($dom->nodes));
		$cnid = 3;
		$e = $dom->nodes[++$cnid];
		$this->assertEquals(CDom::NODE_ELEMENT, $e->type);
		$this->assertEquals('b', $e->name);
		$e = $dom->nodes[++$cnid];
		$this->assertEquals(CDom::NODE_ELEMENT, $e->type);
		$this->assertEquals('i', $e->name);
		$e = $dom->nodes[++$cnid];
		$this->assertEquals(CDom::NODE_TEXT, $e->type);
		$e = $dom->nodes[++$cnid];
		$this->assertEquals(CDom::NODE_TEXT, $e->type);

		$dom->html('string');
		$dom->lastNode->after('<i/>');
		$this->checkStructure($dom);

		$dom->html('string');
		$dom->lastNode->after('text');
		$this->checkStructure($dom);

		$dom->html('string<br/>');
		$dom2 = CDom::fromString('string');
		$dom->lastNode->after($dom2->nodes);
		$this->assertEquals(1, count($dom->children));
		$this->assertEquals(3, count($dom->nodes));
		$this->checkStructure($dom);
		$this->assertEquals(0, count($dom2->children));
		$this->assertEquals(0, count($dom2->nodes));
		$this->assertEmpty($dom2->firstChild);
		$this->assertEmpty($dom2->lastChild);
		$this->checkStructure($dom2);

		$dom->html('<br/>');
		$dom->lastNode->after('<br/>');
		$this->assertEquals(2, count($dom->children));
		$this->assertEquals(2, count($dom->nodes));
		$this->checkStructure($dom);

		$dom->html('string<!-- comment --><br/>');
		$dom2 = CDom::fromString('string');
		$dom->nodes[0]->after($dom2->nodes);
		$this->assertEquals(1, count($dom->children));
		$this->assertEquals(4, count($dom->nodes));
		$this->checkStructure($dom);

		$list = $dom->html('<p/><p/><p/>')->find('p');
		$list->after('<br>');
		$this->checkStructure($dom);
		$this->assertEquals(6, count($dom->nodes));
		$expected = '<p /><br /><p /><br /><p /><br />';
		$this->assertEquals($expected, $dom->html());


		// Before
		$string = str_repeat('<b/>text', 4);
		$dom->html($string);
		$this->assertEquals(4, count($dom->children));
		$this->assertEquals(8, count($dom->nodes));
		$cnid = 1;
		$e = $dom->nodes[++$cnid];
		$this->assertEquals(CDom::NODE_ELEMENT, $e->type);
		$this->assertEquals('b', $e->name);
		$e = $dom->nodes[++$cnid];
		$this->assertEquals(CDom::NODE_TEXT, $e->type);
		$e = $dom->nodes[++$cnid];
		$this->assertEquals(CDom::NODE_ELEMENT, $e->type);
		$this->assertEquals('b', $e->name);
		$e = $dom->nodes[++$cnid];
		$this->assertEquals(CDom::NODE_TEXT, $e->type);
		$e = $dom->nodes[++$cnid];
		$this->assertEquals(CDom::NODE_ELEMENT, $e->type);
		$this->assertEquals('b', $e->name);

		$dom->nodes[3]->before('<i/>string');
		$this->checkStructure($dom);
		$this->assertEquals(5, count($dom->children));
		$this->assertEquals(10, count($dom->nodes));
		$cnid = 1;
		$e = $dom->nodes[++$cnid];
		$this->assertEquals(CDom::NODE_ELEMENT, $e->type);
		$this->assertEquals('b', $e->name);
		$e = $dom->nodes[++$cnid];
		$this->assertEquals(CDom::NODE_ELEMENT, $e->type);
		$this->assertEquals('i', $e->name);
		$e = $dom->nodes[++$cnid];
		$this->assertEquals(CDom::NODE_TEXT, $e->type);
		$e = $dom->nodes[++$cnid];
		$this->assertEquals(CDom::NODE_TEXT, $e->type);
		$e = $dom->nodes[++$cnid];
		$this->assertEquals(CDom::NODE_ELEMENT, $e->type);
		$this->assertEquals('b', $e->name);

		$dom->html('<br>');
		$dom->nodes[0]->before('<br>');
		$this->assertEquals(2, count($dom->children));
		$this->assertEquals(2, count($dom->nodes));
		$this->checkStructure($dom);

		$list = $dom->html('<p/><p/><p/>')->find('p');
		$list->before('<br>');
		$this->checkStructure($dom);
		$this->assertEquals(6, count($dom->nodes));
		$expected = '<br /><p /><br /><p /><br /><p />';
		$this->assertEquals($expected, $dom->html());


		// ReplaceWith
		$string = str_repeat('text<b/>', 3);
		$dom->html($string);
		$this->assertEquals(3, count($dom->children));
		$this->assertEquals(6, count($dom->nodes));

		$dom->nodes[3]->replaceWith('string');
		$this->assertEquals(2, count($dom->children));
		$this->assertEquals(6, count($dom->nodes));
		$this->checkStructure($dom);
		$cnid = 1;
		$e = $dom->nodes[++$cnid];
		$this->assertEquals(CDom::NODE_TEXT, $e->type);
		$e = $dom->nodes[++$cnid];
		$this->assertEquals(CDom::NODE_TEXT, $e->type);
		$e = $dom->nodes[++$cnid];
		$this->assertEquals(CDom::NODE_TEXT, $e->type);
		$e = $dom->nodes[++$cnid];
		$this->assertEquals(CDom::NODE_ELEMENT, $e->type);

		$dom->html($string);
		$dom->nodes[2]->replaceWith('<i/>string<i/>');
		$this->assertEquals(5, count($dom->children));
		$this->assertEquals(8, count($dom->nodes));
		$this->checkStructure($dom);
		$cnid = 0;
		$e = $dom->nodes[++$cnid];
		$this->assertEquals(CDom::NODE_ELEMENT, $e->type);
		$this->assertEquals('b', $e->name);
		$e = $dom->nodes[++$cnid];
		$this->assertEquals(CDom::NODE_ELEMENT, $e->type);
		$this->assertEquals('i', $e->name);
		$e = $dom->nodes[++$cnid];
		$this->assertEquals(CDom::NODE_TEXT, $e->type);
		$e = $dom->nodes[++$cnid];
		$this->assertEquals(CDom::NODE_ELEMENT, $e->type);
		$this->assertEquals('i', $e->name);
		$e = $dom->nodes[++$cnid];
		$this->assertEquals(CDom::NODE_ELEMENT, $e->type);
		$this->assertEquals('b', $e->name);

		$dom->html($string);
		$dom->nodes[0]->replaceWith('<i/>string<i/>');
		$this->assertEquals(5, count($dom->children));
		$this->assertEquals(8, count($dom->nodes));
		$this->checkStructure($dom);

		$dom->html($string);
		$dom->nodes[0]->replaceWith('<i/>');
		$this->assertEquals(4, count($dom->children));
		$this->assertEquals(6, count($dom->nodes));
		$this->checkStructure($dom);

		$dom->html($string);
		$dom->nodes[1]->replaceWith('text');
		$this->assertEquals(2, count($dom->children));
		$this->assertEquals(6, count($dom->nodes));
		$this->checkStructure($dom);

		$dom->html($string);
		$dom->nodes[5]->replaceWith('string');
		$this->assertEquals(2, count($dom->children));
		$this->assertEquals(6, count($dom->nodes));
		$this->checkStructure($dom);

		$dom->html('text');
		$dom->nodes[0]->replaceWith('<br>');
		$this->assertEquals(1, count($dom->children));
		$this->assertEquals(1, count($dom->nodes));
		$this->checkStructure($dom);

		$dom->html('<br>');
		$dom->nodes[0]->replaceWith('text');
		$this->assertEquals(0, count($dom->children));
		$this->assertEquals(1, count($dom->nodes));
		$this->checkStructure($dom);

		$list = $dom->html('<p/><p/><p/>')->find('p');
		$list->replaceWith('<br>');
		$this->checkStructure($dom);
		$this->assertEquals(3, count($dom->nodes));
		$expected = '<br /><br /><br />';
		$this->assertEquals($expected, $dom->html());


		// ReplaceAll
		$string = str_repeat('text<b/>', 3);
		$dom->html($string);
		$res = $dom->nodes[2]->replaceAll('b:eq(1)');
		$this->assertEquals($dom->nodes[2]->uniqId, $res[0]->uniqId);
		$this->assertEquals(2, count($dom->children));
		$this->assertEquals(5, count($dom->nodes));
		$this->checkStructure($dom);
		$cnid = 0;
		$e = $dom->nodes[++$cnid];
		$this->assertEquals(CDom::NODE_ELEMENT, $e->type);
		$this->assertEquals('b', $e->name);
		$e = $dom->nodes[++$cnid];
		$this->assertEquals(CDom::NODE_TEXT, $e->type);
		$e = $dom->nodes[++$cnid];
		$this->assertEquals(CDom::NODE_TEXT, $e->type);
		$e = $dom->nodes[++$cnid];
		$this->assertEquals(CDom::NODE_ELEMENT, $e->type);
		$this->assertEquals('b', $e->name);

		$div = new CDomNodeTag('div');
		$res = $div->replaceAll('b:eq(1)');
		$this->assertEquals($div, $res);

		$dom->html($string);
		$dom->nodes[2]->replaceAll($dom->nodes[3]);
		$this->assertEquals(2, count($dom->children));
		$this->assertEquals(5, count($dom->nodes));
		$this->checkStructure($dom);
		$cnid = 0;
		$e = $dom->nodes[++$cnid];
		$this->assertEquals(CDom::NODE_ELEMENT, $e->type);
		$this->assertEquals('b', $e->name);
		$e = $dom->nodes[++$cnid];
		$this->assertEquals(CDom::NODE_TEXT, $e->type);
		$e = $dom->nodes[++$cnid];
		$this->assertEquals(CDom::NODE_TEXT, $e->type);
		$e = $dom->nodes[++$cnid];
		$this->assertEquals(CDom::NODE_ELEMENT, $e->type);
		$this->assertEquals('b', $e->name);

		$dom->html($string);
		$dom->nodes[2]->replaceAll(array($dom->nodes[3], $dom->nodes[3]));
		$this->assertEquals(2, count($dom->children));
		$this->assertEquals(5, count($dom->nodes));
		$this->checkStructure($dom);
		$cnid = 0;
		$e = $dom->nodes[++$cnid];
		$this->assertEquals(CDom::NODE_ELEMENT, $e->type);
		$this->assertEquals('b', $e->name);
		$e = $dom->nodes[++$cnid];
		$this->assertEquals(CDom::NODE_TEXT, $e->type);
		$e = $dom->nodes[++$cnid];
		$this->assertEquals(CDom::NODE_TEXT, $e->type);
		$e = $dom->nodes[++$cnid];
		$this->assertEquals(CDom::NODE_ELEMENT, $e->type);
		$this->assertEquals('b', $e->name);

		$dom->html($string);
		$dom->nodes[2]->value = 'ex';
		$res = $dom->nodes[2]->replaceAll(array($dom->nodes[3], $dom->nodes[4]));
		$this->assertEquals(2, $res->length);
		$this->assertEquals(2, count($dom->children));
		$this->assertEquals(5, count($dom->nodes));
		$this->checkStructure($dom);
		$cnid = 0;
		$e = $dom->nodes[++$cnid];
		$this->assertEquals(CDom::NODE_ELEMENT, $e->type);
		$this->assertEquals('b', $e->name);
		$e = $dom->nodes[++$cnid];
		$this->assertEquals(CDom::NODE_TEXT, $e->type);
		$this->assertEquals('ex', $e->value);
		$this->assertNotEquals($e->uniqId, $dom->nodes[$cnid+1]->uniqId);
		$e = $dom->nodes[++$cnid];
		$this->assertEquals(CDom::NODE_TEXT, $e->type);
		$this->assertEquals('ex', $e->value);
		$e = $dom->nodes[++$cnid];
		$this->assertEquals(CDom::NODE_ELEMENT, $e->type);
		$this->assertEquals('b', $e->name);

		$list = $dom->html('<i/><b/><u/>')->find('*');
		$dom2 = CDom::fromString('<br/><t/>');
		$list2 = $dom2->find('*')->replaceAll($list);
		$this->assertEmpty($dom2->nodes);
		$this->assertEquals(6, $list2->length);
		$this->checkStructure($dom);
		$this->checkStructure($dom2);
		$expected = '<br /><t /><br /><t /><br /><t />';
		$this->assertEquals($expected, $dom->html());
		$list2->remove();
		$expected = '';
		$this->assertEquals($expected, $dom->html());
		$this->checkStructure($dom);


		// Unwrap
		$dom->html('<p>string<br/></p>text');
		$str = $dom->firstChild->firstNode;
		$uid = $str->uniqId;
		$str->unwrap();
		$this->checkStructure($dom);
		$this->assertEquals(0, count($dom->children));
		$this->assertEquals(2, count($dom->nodes));
		$this->assertEquals('string', $dom->firstNode->value);
		$this->assertEquals(CDom::NODE_TEXT, $dom->firstNode->type);
		$this->assertEquals($uid, $dom->firstNode->uniqId);

		$list = $dom->html('<p><i/></p><p><i/></p>')->find('i')->unwrap();
		$this->assertEquals(2, $list->length);
		$this->assertEquals(2, count($dom->children));
		$this->assertEquals(2, count($dom->nodes));
		$this->checkStructure($dom);
		$expected = '<i /><i />';
		$this->assertEquals($expected, $dom->html());


		// Wrap
		$dom->html('<br/><br/><br/>');
		$i = 0;
		$uid = $dom->children[$i]->uniqId;
		$dom->children[$i]->wrap('<p/>');
		$this->assertEquals(3, count($dom->children));
		$this->assertEquals(3, count($dom->nodes));
		$this->checkStructure($dom);
		$this->assertEquals('p', $dom->children[$i]->name);
		$this->assertEquals('br', $dom->children[$i]->firstChild->name);
		$this->assertEquals($uid, $dom->children[$i]->firstChild->uniqId);
		$this->assertFalse($dom->children[$i]->selfClosed);

		$div = new CDomNodeTag('div');
		$div->wrap('<p/>');
		$this->assertEmpty($div->parent);

		$dom->wrap('<p/>');
		$this->assertEmpty($dom->parent);

		$dom2 = CDom::fromString();
		$dom->html('<br/><br/><br/>');
		$i = 1;
		$uid = $dom->children[$i]->uniqId;
		$dom->children[$i]->wrap($dom2);
		$this->assertEquals($uid, $dom->children[$i]->uniqId);
		$this->assertEquals('br', $dom->children[$i]->name);

		$text = new CDomNodeText();
		$dom->html('<br/>');
		$dom->firstChild->wrap($text);
		$this->assertEquals(1, count($dom->children));
		$this->assertEquals(1, count($dom->nodes));
		$this->assertEquals('br', $dom->firstChild->name);
		$this->assertEmpty($text->parent);

		$list = new CDomNodesList();
		$list->add(new CDomNodeTag('p'));
		$list->add(new CDomNodeTag('div'));
		$dom->html('<br/>');
		$dom->firstChild->wrap($list);
		$this->assertEquals(1, count($dom->children));
		$this->assertEquals(1, count($dom->nodes));
		$this->assertEquals('p', $dom->firstChild->name);
		$this->assertEmpty($list->get(1)->parent);

		$list = new CDomNodesList();
		$dom->html('<br/>');
		$dom->firstChild->wrap($list);
		$this->assertEquals(1, count($dom->children));
		$this->assertEquals(1, count($dom->nodes));
		$this->assertEquals('br', $dom->firstChild->name);

		$dom->html('<br/>');
		$dom->firstChild->wrap(array());
		$this->assertEquals(1, count($dom->children));
		$this->assertEquals(1, count($dom->nodes));
		$this->assertEquals('br', $dom->firstChild->name);

		$list = array(
			new CDomNodeTag('div'),
			new CDomNodeTag('p'),
		);
		$dom->html('<br/>');
		$dom->firstChild->wrap($list);
		$this->assertEquals(1, count($dom->children));
		$this->assertEquals(1, count($dom->nodes));
		$this->assertEquals('div', $dom->firstChild->name);
		$this->assertEquals('br', $dom->firstChild->firstChild->name);
		$this->checkStructure($dom);

		$dom->html('<br/><p/>');
		$dom->firstChild->wrap('p');
		$this->assertEquals(2, count($dom->children));
		$this->assertEquals(2, count($dom->nodes));
		$this->assertEquals('p', $dom->firstChild->name);
		$this->assertEquals('br', $dom->firstChild->firstChild->name);
		$this->assertNotEquals($dom->lastChild->uniqId, $dom->firstChild->uniqId);
		$this->checkStructure($dom);

		$list = $dom->html('<p/><p/><p/>')->find('p');
		$list->wrap('<div>');
		$this->checkStructure($dom);
		$this->checkList($list);
		$this->assertEquals(3, count($dom->nodes));
		$this->assertEquals(3, $list->length);
		$this->assertEquals('p', $list[0]->name);
		$expected = '<div><p /></div><div><p /></div><div><p /></div>';
		$this->assertEquals($expected, $dom->html());
		$list[0]->name = 'b';
		$expected = '<div><b /></div><div><p /></div><div><p /></div>';
		$this->assertEquals($expected, $dom->html());


		// WrapInner
		$text = new CDomNodeText();
		$text->wrapInner('<p>');
		$this->assertEmpty($text->children);
		$this->assertEmpty($text->nodes);

		$dom->html('<div>text<br/></div>');
		$div = $dom->firstChild;
		$this->assertEquals(1, count($div->children));
		$this->assertEquals(2, count($div->nodes));
		$div->wrapInner('<p>');
		$this->assertEquals(1, count($div->children));
		$this->assertEquals(1, count($div->nodes));
		$this->checkStructure($div);
		$p = $div->firstChild;
		$this->assertEquals('p', $p->name);
		$this->assertEquals(1, count($p->children));
		$this->assertEquals(2, count($p->nodes));

		$dom->html('<div>text<br/></div>');
		$div = $dom->firstChild;
		$this->assertEquals(1, count($div->children));
		$this->assertEquals(2, count($div->nodes));
		$div->wrapInner('<>');
		$this->assertEquals(1, count($div->children));
		$this->assertEquals(2, count($div->nodes));
		$p = $div->firstChild;
		$this->assertEquals('br', $p->name);

		$list = $dom->html('<p>l</p><p><br/></p>')->find('p');
		$list->wrapInner('<b>');
		$this->checkStructure($dom);
		$this->checkList($list);
		$this->assertEquals(2, $list->length);
		$expected = '<p><b>l</b></p><p><b><br /></b></p>';
		$this->assertEquals($expected, $dom->html());


		// WrapAll
		$list = $dom->html('<p/><l>t</l><l/><b/><i>')->find('l,i');
		$this->assertEquals(3, $list->length);
		$list->wrapAll('<div>');
		$this->checkStructure($dom);
		$this->checkList($list);
		$expected = '<p /><div><l>t</l><l /><i></i></div><b />';
		$this->assertEquals($expected, $dom->html());


		// AppendTo
		$dom->html('<div>text<br/></div>');
		$div = $dom->firstChild;
		CDom::fromString('<p>')->appendTo($div);
		$this->assertEquals(2, count($div->children));
		$this->assertEquals(3, count($div->nodes));
		$this->assertEquals('p', $div->lastChild->name);
		$this->checkStructure($div);

		$p = CDom::fromString('<p>')->appendTo(array());
		$this->assertTrue($p instanceof CDomDocument);
		$this->assertEmpty($p->parent);

		$p = CDom::fromString('<p>')->appendTo(array('<div>'));
		$this->assertTrue($p instanceof CDomDocument);
		$this->assertEmpty($p->parent);

		$dom2 = CDom::fromString('text');
		$p = CDom::fromString('<p>')->appendTo($dom2->firstNode);
		$this->assertTrue($p instanceof CDomDocument);
		$this->assertEmpty($p->parent);
		$this->assertEmpty($dom2->firstNode->nodes);

		$list = $dom->html('<i><b></b></i>')->find('*');
		$dom2 = CDom::fromString('<br/><t/>');
		$list2 = $dom2->find('*')->appendTo($list);
		$this->assertEmpty($dom2->nodes);
		$this->assertEquals(4, $list2->length);
		$this->checkStructure($dom);
		$this->checkStructure($dom2);
		$expected = '<i><b><br /><t /></b><br /><t /></i>';
		$this->assertEquals($expected, $dom->html());
		$list2->remove();
		$expected = '<i><b></b></i>';
		$this->assertEquals($expected, $dom->html());
		$this->checkStructure($dom);

		$list2 = $dom2->find('*')->appendTo($list);
		$this->assertEquals(0, $list2->length);
		$expected = '<i><b></b></i>';
		$this->assertEquals($expected, $dom->html());

		$list2 = $dom->find('.unknown')->appendTo('b');
		$this->assertEquals(0, $list2->length);
		$expected = '<i><b></b></i>';
		$this->assertEquals($expected, $dom->html());

		$list2 = $dom->append('<br>')->find('br')->appendTo('b');
		$this->assertEquals(1, $list2->length);
		$expected = '<i><b><br /></b></i>';
		$this->assertEquals($expected, $dom->html());


		// PrependTo
		$dom->html('<div>text<br/></div>');
		$div = $dom->firstChild;
		CDom::fromString('<p>')->prependTo($div);
		$this->assertEquals(2, count($div->children));
		$this->assertEquals(3, count($div->nodes));
		$this->assertEquals('p', $div->firstChild->name);
		$this->checkStructure($div);

		$list = $dom->html('<i><b></b></i>')->find('*');
		$dom2 = CDom::fromString('<br/><t/>');
		$list2 = $dom2->find('*')->prependTo($list);
		$this->assertEmpty($dom2->nodes);
		$this->assertEquals(4, $list2->length);
		$this->checkStructure($dom);
		$this->checkStructure($dom2);
		$expected = '<i><br /><t /><b><br /><t /></b></i>';
		$this->assertEquals($expected, $dom->html());
		$list2->remove();
		$expected = '<i><b></b></i>';
		$this->assertEquals($expected, $dom->html());
		$this->checkStructure($dom);


		// InsertBefore
		$dom->html('<div>text<br/></div>');
		$div = $dom->firstChild;
		CDom::fromString('<p>')->insertBefore($div);
		$this->assertEquals(2, count($dom->children));
		$this->assertEquals(2, count($dom->nodes));
		$this->assertEquals('p', $dom->firstChild->name);
		$this->assertEquals('div', $dom->lastChild->name);
		$this->checkStructure($dom);

		$list = $dom->html('<i><b></b></i>')->find('*');
		$dom2 = CDom::fromString('<br/><t/>');
		$list2 = $dom2->find('*');
		$this->assertEquals(2, $list2->length);
		$list2->insertBefore($list);
		$this->assertEmpty($dom2->nodes);
		$this->assertEquals(4, $list2->length);
		$this->checkStructure($dom);
		$this->checkStructure($dom2);
		$expected = '<br /><t /><i><br /><t /><b></b></i>';
		$this->assertEquals($expected, $dom->html());
		$list2->remove();
		$expected = '<i><b></b></i>';
		$this->assertEquals($expected, $dom->html());
		$this->checkStructure($dom);


		// InsertAfter
		$dom->html('<div>text<br/></div>');
		$div = $dom->firstChild;
		CDom::fromString('<p>')->insertAfter($div);
		$this->assertEquals(2, count($dom->children));
		$this->assertEquals(2, count($dom->nodes));
		$this->assertEquals('div', $dom->firstChild->name);
		$this->assertEquals('p', $dom->lastChild->name);
		$this->checkStructure($dom);

		$list = $dom->html('<i/><i/>')->find('*');
		$this->assertEquals(2, $list->length);
		$list2 = CDom::fromString('<p/>')->insertAfter($list);
		$this->assertEquals(2, $list2->length);
		$this->checkStructure($dom);
		$this->checkList($list2);
		$expected = '<i /><p /><i /><p />';
		$this->assertEquals($expected, $dom->html());

		$list = $dom->html('<i><b></b></i>')->find('*');
		$dom2 = CDom::fromString('<br/><t/>');
		$list2 = $dom2->find('*');
		$this->assertEquals(2, $list2->length);
		$list2->insertAfter($list);
		$this->assertEmpty($dom2->nodes);
		$this->assertEquals(4, $list2->length);
		$this->checkStructure($dom);
		$this->checkStructure($dom2);
		$expected = '<i><b></b><br /><t /></i><br /><t />';
		$this->assertEquals($expected, $dom->html());
		$list2->remove();
		$expected = '<i><b></b></i>';
		$this->assertEquals($expected, $dom->html());
		$this->checkStructure($dom);


		$dom->clean();
		$dom2->clean();
	}

	/**
	 * Tests traversing
	 *
	 * @covers CDomList
	 * @covers CDomNode
	 * @covers CDomNodesList
	 */
	function testTraversing()
	{
		$dom = CDom::fromString($this->test_html2);


		// ---
		$l = $dom->find('body')->find('li:eq(1)')->andSelf();
		$this->assertEquals(2, $l->length);
		$i = 0;
		$el = $l->get($i++);
		$this->assertEquals('li2', $el->id);
		$el = $l->get($i);
		$this->assertEquals('body', $el->name);


		// ---
		$l = $dom->find('*')->first();
		$this->assertEquals(1, $l->length);
		$this->assertEquals('html', $l->name);

		/** @noinspection PhpUndefinedMethodInspection */
		$l->end()->last();
		$this->assertEquals(1, $l->length);
		$this->assertEquals('h6', $l->name);

		/** @noinspection PhpUndefinedMethodInspection */
		$l->end()->slice(-2);
		$this->assertEquals(2, $l->length);
		$i = 0;
		$el = $l->get($i++);
		$this->assertEquals('h1', $el->name);
		$el = $l->get($i);
		$this->assertEquals('h6', $el->name);

		/** @noinspection PhpUndefinedMethodInspection */
		$l->end()->slice(3, 3);
		$this->assertEquals(3, $l->length);
		$i = 0;
		$el = $l->get($i++);
		$this->assertEquals('title', $el->name);
		$el = $l->get($i++);
		$this->assertEquals('body', $el->name);
		$el = $l->get($i);
		$this->assertEquals('div', $el->name);

		$list = $dom->find('list');
		/** @noinspection PhpUndefinedMethodInspection */
		$index = $l->end()->index($list);
		$this->assertEquals(7, $index);
		/** @var $res CDomNodesList */
		$res = $l->eq(7);
		$this->assertEquals($list->uniqId, $res->uniqId);
		/** @noinspection PhpUndefinedMethodInspection */
		$res = $l->end()->eq(-11);
		$this->assertEquals($list->uniqId, $res->uniqId);
		/** @noinspection PhpUndefinedMethodInspection */
		$index = $l->end()->index(new CDomNodeText());
		$this->assertEquals(-1, $index);


		// ---
		$el = $list->get(0);
		$res = $el->has('li');
		$this->assertTrue($res);
		$res = $el->has('p');
		$this->assertFalse($res);
		/** @var $li CDomNodeTag */
		$li = $el->firstChild;
		$res = $li->has('*');
		$this->assertFalse($res);


		// Not
		$list2 = $dom('li')->not('#li2');
		$this->assertEquals(3, $list2->length);
		$i = 0;
		$el = $list2->get($i++);
		$this->assertEquals('li1', $el->attr('id'));
		$el = $list2->get($i++);
		$this->assertEquals('li3', $el->attr('id'));
		$el = $list2->get($i);
		$this->assertEquals('li4', $el->attr('id'));


		// Is
		$list2 = $dom('li');
		$this->assertTrue($list2->is('li'));
		$this->assertTrue($list2->is('#li4'));
		$this->assertFalse($list2->is('div'));


		// Contents
		$list2 = $dom->find('list')->contents();
		$this->assertEquals(9, $list2->length);
		foreach ($list2 as $i => $n) {
			if ($i & 1) {
				$this->assertTrue($n instanceof CDomNodeTag);
			} else {
				$this->assertTrue($n instanceof CDomNodeText);
			}
		}


		// Children
		$list2 = $list->get(0)->children();
		$this->assertEquals(4, $list2->length);
		$i = 0;
		$el = $list2->get($i++);
		$this->assertEquals('li1', $el->attr('id'));
		$el = $list2->get($i++);
		$this->assertEquals('li2', $el->attr('id'));
		$el = $list2->get($i++);
		$this->assertEquals('li3', $el->attr('id'));
		$el = $list2->get($i);
		$this->assertEquals('li4', $el->attr('id'));

		$list2 = $list->first->children('#li2,#li4');
		$this->assertEquals(2, $list2->length);
		$i = 0;
		$el = $list2->get($i++);
		$this->assertEquals('li2', $el->attr('id'));
		$el = $list2->get($i);
		$this->assertEquals('li4', $el->attr('id'));

		$list2 = $list->children(':not(#li3)');
		$this->assertEquals(3, $list2->length);
		$i = 0;
		$el = $list2->get($i++);
		$this->assertEquals('li1', $el->attr('id'));
		$el = $list2->get($i++);
		$this->assertEquals('li2', $el->attr('id'));
		$el = $list2->get($i);
		$this->assertEquals('li4', $el->attr('id'));
		$list->end();


		// Parent
		$list2 = $dom->find('li:eq(0),title');
		$list2->parent();
		$this->assertEquals(2, $list2->length);
		$list2->end();
		$list2->parent('head');
		$this->assertEquals(1, $list2->length);


		// Parents
		$list2 = $li->parents();
		$this->assertEquals(4, $list2->length);
		$this->assertEquals($list2->length, count($list2->list));
		$this->assertEquals($list2->length, count($list2->listByIds));
		$i = 0;
		$el = $list2->get($i++);
		$this->assertEquals('list', $el->name);
		$el = $list2->get($i++);
		$this->assertEquals('div', $el->name);
		$el = $list2->get($i++);
		$this->assertEquals('body', $el->name);
		$el = $list2->get($i);
		$this->assertEquals('html', $el->name);

		$list2 = $li->parents('html,div');
		$this->assertEquals(2, $list2->length);
		$i = 0;
		$el = $list2->get($i++);
		$this->assertEquals('div', $el->name);
		$el = $list2->get($i);
		$this->assertEquals('html', $el->name);

		$list2 = $li->parents(':not(html,div)');
		$this->assertEquals(2, $list2->length);
		$i = 0;
		$el = $list2->get($i++);
		$this->assertEquals('list', $el->name);
		$el = $list2->get($i);
		$this->assertEquals('body', $el->name);

		$list2 = $li->parentsUntil('div');
		$this->assertEquals(1, $list2->length);
		$i = 0;
		$el = $list2->get($i);
		$this->assertEquals('list', $el->name);

		$list2 = $dom->find('li:eq(0),p:eq(1)');
		$list2->parents();
		$this->assertEquals(4, $list2->length);
		/** @noinspection PhpUndefinedMethodInspection */
		$list2->end()->parents('body');
		$this->assertEquals(1, $list2->length);


		// parentsUntil
		$list2 = $li->parentsUntil('html');
		$this->assertEquals(3, $list2->length);
		$i = 0;
		$el = $list2->get($i++);
		$this->assertEquals('list', $el->name);
		$el = $list2->get($i++);
		$this->assertEquals('div', $el->name);
		$el = $list2->get($i);
		$this->assertEquals('body', $el->name);

		$list2 = $li->parentsUntil('list');
		$this->assertEquals(0, $list2->length);

		$list2 = $dom->find('li:eq(0),p:eq(1)');
		$list2->parentsUntil('body');
		$this->assertEquals(2, $list2->length);


		// closest
		$el = $li->closest('div');
		$this->assertEquals('div', $el->name);

		$el = $li->closest('html');
		$this->assertEquals('html', $el->name);

		$el = $li->closest('list,body');
		$this->assertEquals('list', $el->name);

		$el = $li->closest('p');
		$this->assertEmpty($el);

		$el = $dom->find('p:eq(1)')->closest('body');
		$this->assertEquals('body', $el->name);

		$list2 = new CDomNodesList();
		$this->assertEmpty($list2->closest('*'));


		// next
		$list2 = $dom->find('li:eq(0),p:eq(1)');
		$list2->getNext();
		$this->assertEquals(2, $list2->length);
		$i = 0;
		$el = $list2->get($i++);
		$this->assertEquals('li2', $el->attr('id'));
		$el = $list2->get($i);
		$this->assertEquals('p3', $el->attr('id'));

		/** @noinspection PhpUndefinedMethodInspection */
		$list2->end()->getNext('p');
		$this->assertEquals(1, $list2->length);
		$el = $list2->get(0);
		$this->assertEquals('p3', $el->attr('id'));


		// nextAll
		$list2 = $li->nextAll();
		$this->assertEquals(3, $list2->length);
		foreach ($list2 as $el) {
			$this->assertEquals('li', $el->name);
		}
		$i = 0;
		$el = $list2->get($i++);
		$this->assertEquals('li2', $el->attr('id'));
		$el = $list2->get($i++);
		$this->assertEquals('li3', $el->attr('id'));
		$el = $list2->get($i);
		$this->assertEquals('li4', $el->attr('id'));

		$list2 = $li->nextAll('#li3');
		$this->assertEquals(1, $list2->length);
		$el = $list2->get(0);
		$this->assertEquals('li3', $el->attr('id'));

		$list2 = $dom->find('li:eq(1),p:eq(2)');
		$list2->nextAll();
		$this->assertEquals(4, $list2->length);
		$i = 1;
		$el = $list2->get($i++);
		$this->assertEquals('li4', $el->id);
		$el = $list2->get($i++);
		$this->assertEquals('h1', $el->name);
		$el = $list2->get($i);
		$this->assertEquals('h6', $el->name);

		/** @noinspection PhpUndefinedMethodInspection */
		$list2->end()->nextAll(':header');
		$this->assertEquals(2, $list2->length);
		$i = 0;
		$el = $list2->get($i++);
		$this->assertEquals('h1', $el->name);
		$el = $list2->get($i);
		$this->assertEquals('h6', $el->name);


		// nextUntil
		$list2 = $li->nextUntil('#li4');
		$this->assertEquals(2, $list2->length);
		$i = 0;
		$el = $list2->get($i++);
		$this->assertEquals('li2', $el->attr('id'));
		$el = $list2->get($i);
		$this->assertEquals('li3', $el->attr('id'));

		$list2 = $li->nextUntil('#li2');
		$this->assertEquals(0, $list2->length);

		$list2 = $dom->find('li:eq(1),p:eq(2)')->nextUntil(':header');
		$this->assertEquals(2, $list2->length);


		// prev
		$list2 = $dom->find('li:eq(1),p:eq(2)');
		$list2->getPrev();
		$this->assertEquals(2, $list2->length);
		$i = 0;
		$el = $list2->get($i++);
		$this->assertEquals('li1', $el->attr('id'));
		$el = $list2->get($i);
		$this->assertEquals('p2', $el->attr('id'));

		/** @noinspection PhpUndefinedMethodInspection */
		$list2->end()->getPrev('p');
		$this->assertEquals(1, $list2->length);
		$el = $list2->get(0);
		$this->assertEquals('p2', $el->attr('id'));


		// prevAll
		$li3 = $li->next->next;
		$list2 = $li3->prevAll();
		$this->assertEquals(2, $list2->length);
		$i = 0;
		$el = $list2->get($i++);
		$this->assertEquals('li2', $el->attr('id'));
		$el = $list2->get($i);
		$this->assertEquals('li1', $el->attr('id'));

		$list2 = $li3->prevAll('#li1');
		$this->assertEquals(1, $list2->length);
		$el = $list2->get(0);
		$this->assertEquals('li1', $el->attr('id'));

		$list2 = $dom->find('li:eq(1),p:eq(2)');
		$list2->prevAll();
		$this->assertEquals(6, $list2->length);
		$i = 0;
		$el = $list2->get($i++);
		$this->assertEquals('li', $el->name);
		$el = $list2->get($i++);
		$this->assertEquals('p', $el->name);
		$el = $list2->get($i++);
		$this->assertEquals('p', $el->name);
		$el = $list2->get($i++);
		$this->assertEquals('hr', $el->name);
		$el = $list2->get($i++);
		$this->assertEquals('list', $el->name);
		$el = $list2->get($i);
		$this->assertEquals('br', $el->name);

		/** @noinspection PhpUndefinedMethodInspection */
		$list2->end()->prevAll('list');
		$this->assertEquals(1, $list2->length);
		$el = $list2->get(0);
		$this->assertEquals('list', $el->name);


		// prevUntil
		$list2 = $li3->prevUntil('#li1');
		$this->assertEquals(1, $list2->length);
		$el = $list2->get(0);
		$this->assertEquals('li2', $el->attr('id'));

		$list2 = $li3->prevUntil('#li2');
		$this->assertEquals(0, $list2->length);

		$list2 = $dom->find('li:eq(1),p:eq(2)')->prevUntil('hr');
		$this->assertEquals(3, $list2->length);


		// siblings
		$list2 = $li3->siblings();
		$this->assertEquals(3, $list2->length);
		$i = 0;
		$el = $list2->get($i++);
		$this->assertEquals('li2', $el->attr('id'));
		$el = $list2->get($i++);
		$this->assertEquals('li1', $el->attr('id'));
		$el = $list2->get($i);
		$this->assertEquals('li4', $el->attr('id'));

		$list2 = $dom->find('li:eq(1),p:eq(2)')->siblings();
		$this->assertEquals(10, $list2->length);

		/** @noinspection PhpUndefinedMethodInspection */
		$list2->end()->siblings(':not(p)');
		$this->assertEquals(8, $list2->length);


		$dom->clean();
	}
}
