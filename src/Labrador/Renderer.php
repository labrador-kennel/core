<?php

/**
 *
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador;

use Zend\Escaper\Escaper;

class Renderer {

    private $escaper;
    private $templates;
    private $layout;

    function __construct(Escaper $escaper, $templatesDir, $layout = null) {
        $this->escaper = $escaper;
        $this->templates = rtrim($templatesDir, '/ ');
        $this->layout = (string) $layout;
    }

    function getTemplatesDir() {
        return $this->templates;
    }

    function setLayout($layout) {
        $this->layout = (string) $layout;
    }

    function getLayout() {
        return $this->layout;
    }

    function render($template, array $data = []) {
        return $this->renderPartial($this->getLayout(), ['_content' => $this->renderPartial($template, $data)]);
    }

    function renderPartial($_template, array $_data = []) {
        $_template = $this->getTemplatePath($_template);
        extract($_data, EXTR_SKIP);
        ob_start();
        include $_template;
        return ob_get_clean();
    }

    private function getTemplatePath($_template) {
        return $this->templates . '/' . $_template . '.php';
    }

    function _html($val) {
        return $this->escaper->escapeHtml($val);
    }

    function _htmlAttr($val) {
        return $this->escaper->escapeHtmlAttr($val);
    }

    function _css($val) {
        return $this->escaper->escapeCss($val);
    }

    function _js($val) {
        return $this->escaper->escapeJs($val);
    }

    function _url($val) {
        return $this->escaper->escapeUrl($val);
    }

} 
