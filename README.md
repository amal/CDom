CDom
====

https://github.com/amal/CDom

CDom is a simple HTML/XML/BBCode DOM component.
It provides a parser for HTML-like markup language in the DOM-like structure and support searching through the DOM with full strength of CSS3 selectors and any manipulations.
CDom is based on [PHP Simple HTML DOM Parser](http://simplehtmldom.sourceforge.net/) and licensed under the MIT License.

Main features and possibilites:

* Traversing and manipulations in jQuery-like manner for PHP.
* Automatic detection of encoding.
* Supports damaged and malformed html.
* Full support of [CSS3 selectors](http://www.w3.org/TR/css3-selectors/).
* Support of [jQuery selector extensions](http://api.jquery.com/category/selectors/jquery-selector-extensions/).
* Extract contents from DOM as text or html in a single line.
* Full code coverage.
* Can work with simple BBCode or other HTML-like markup languages.

CDom is written for Anizoptera CMF by Amal Samally (amal.samally at gmail.com)


Documentation
-------------

CDom use is very simple. Most of the methods match the jQuery's ones. All methods are detail commented. Your IDE can easy show autocompletion, if support PHPDoc. And you can see examples of using below.
Full documentation will be soon.


Requirements
------------

* CDom requires PHP 5.3.0 (or later).


Examples
--------

How to get HTML elements?

```php
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
```


How to modify HTML elements?

```php
// Create DOM from string
$dom = CDom::fromString('<div id="hello">Hello</div><div id="world">World</div>');

// Add class to the second div (first last child)
$dom->find('div:nth-last-child(1)')->class = 'bar';

// Change text of first div
$dom->find('div[id=hello]')->text('foo');

echo $dom . "\n"; // Output: <div id="hello">foo</div><div id="world" class="bar">World</div>
```


Extract contents from HTML

```php
$html = file_get_contents('http://www.google.com/');
// Dump correctly formatted contents without tags from HTML
echo CDom::fromString($html)->text() . "\n";
```


Use CDom to work with simple BBCode

```php
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
```
