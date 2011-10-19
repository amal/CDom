<?php

require_once __DIR__ . '/CDom.php';



// --- How to get HTML elements?

// Create DOM from string
$html = file_get_contents('http://www.google.com/');
$dom  = CDom::fromString($html);

// Find all images
foreach($dom->find('img') as $element) {
	echo $element->src . "\n";
}

// Find all links
foreach($dom->find('a') as $element) {
	echo $element->href . "\n";
}



// --- How to modify HTML elements?

// Create DOM from string
$dom = CDom::fromString('<div id="hello">Hello</div><div id="world">World</div>');

// Add class to the second div (first last child)
$dom->find('div:nth-last-child(1)')->class = 'bar';

// Change text of first div
$dom->find('div[id=hello]')->text('foo');

echo $dom . "\n"; // Output: <div id="hello">foo</div><div id="world" class="bar">World</div>



// --- Extract contents from HTML

$html = file_get_contents('http://www.google.com/');
// Dump correctly formatted contents without tags from HTML
echo CDom::fromString($html)->text() . "\n";



// --- Use CDom to work with simple BBCode
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
CDom::$inlineTags = array('b' => true, 'i' => true, 'u' => true);
CDom::$selfClosingTags = array();

// Create DOM from string
$dom = CDom::fromString($bbMarkup);

// Find [b]
$b = $dom->find('b');
$expected = '[b]Bold [u]Underline[/u][/b]';
echo $b->outerHtml() . "\n"; // Output: [b]Bold [u]Underline[/u][/b]

// Change [img] width
$img = $dom->lastChild;
$img->width = 450;
echo $img->outerHtml() . "\n"; // Output: [img width="450" height="16"]url[/img]

// Convert [b] to html
CDom::$bracketOpen  = '<';
CDom::$bracketClose = '>';
echo $b->outerHtml() . "\n"; // Output: <b>Bold <u>Underline</u></b>
