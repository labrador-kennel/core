<?php

/**
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Test\Unit;

use Labrador\Renderer;
use PHPUnit_Framework_TestCase as UnitTestCase;
use Zend\Escaper\Escaper;

class RendererTest extends UnitTestCase {

    private $templates;

    function setUp() {
        $this->templates = dirname(__DIR__) . '/_templates';
    }

    function getRenderer() {
        return new Renderer(new Escaper(), $this->templates);
    }

    function testRenderPartialNoData() {
        $renderer = $this->getRenderer();
        $expected = <<<TEXT
partial
TEXT;
        $actual = trim($renderer->renderPartial('partial'), PHP_EOL);
        $this->assertSame($expected, $actual);
    }

    function testRenderPartialWithData() {
        $renderer = $this->getRenderer();
        $expected = <<<TEXT
layout
foobar
TEXT;

        $actual = trim($renderer->renderPartial('layout', ['_content' => 'foobar']), PHP_EOL);
        $this->assertSame($expected, $actual);
    }

    function testRenderNoData() {
        $renderer = $this->getRenderer();
        $renderer->setLayout('layout');
        $expected = <<<TEXT
layout
partial
TEXT;

        $actual = trim($renderer->render('partial'), PHP_EOL);
        $this->assertSame($expected, $actual);
    }



} 
