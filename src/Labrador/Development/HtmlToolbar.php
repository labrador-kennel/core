<?php

/**
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Development;


use Labrador\Events\ApplicationFinishedEvent;
use Symfony\Component\HttpFoundation\Response;

class HtmlToolbar extends Toolbar {

    function appFinishedEvent(ApplicationFinishedEvent $event) {
        parent::appFinishedEvent($event);
        $response = $event->getResponse();
        $dom = new \DOMDocument();
        // doing this to support potential HTML5 elements
        libxml_use_internal_errors(true);
        $dom->loadHTML($response->getContent());
        libxml_clear_errors();
        $head = $dom->getElementsByTagName('head')->item(0);
        if ($head) {
            $style = $this->getStyleElement($dom);
            $head->appendChild($style);
        }

        $body = $dom->getElementsByTagName('body')->item(0);
        if ($body) {
            $toolbar = $this->getToolbarElement($dom);
            $body->appendChild($toolbar);
        }

        $newHtml = $dom->saveHTML();
        $event->setResponse(new Response($newHtml));
    }

    private function getStyleElement(\DOMDocument $dom) {
        $style = $dom->createElement('style');
        $style->nodeValue = <<<CSS
#labrador-dev-toolbar {
    width: 100%;
    position: fixed;
    bottom: 0;
    left: 0;
    background-color: rgb(209, 209, 209);
    color: black;
    height: 30px;
}

#labrador-dev-toolbar h1 {
    font-size: 100%;
    float: left;
    line-height: 30px;
    margin: 0 1em;
}

#labrador-dev-toolbar ul {
    margin: 0;
    padding: 0;
    list-style-type: none;
}

#labrador-dev-toolbar li {
    float: left;
    line-height: 30px;
    margin-left: .75em;
    letter-spacing: .0625em;
}
CSS;
        return $style;
    }

    private function getToolbarElement(\DOMDocument $dom) {
        $template = $dom->createDocumentFragment();
        $template->appendXML($this->getToolbarTemplate());
        return $template;
    }

    private function getToolbarTemplate() {
        $memory = number_format($this->runtimeProfiler->getPeakMemoryUsage(), 3);
        $totalTime = number_format($this->runtimeProfiler->getTotalTimeElapsed(), 3);
        $handler = var_export($this->requestStack->getMasterRequest()->attributes->get('_labrador')['handler'], true);
        $branch = $this->gitBranch->getBranchName();
        return <<<HTML
<section id="labrador-dev-toolbar">
    <h1>Labrador Dev Bar</h1>
    <ul>
        <li><b>Memory</b>: {$memory}MB</li>
        <li><b>Time</b>: {$totalTime}</li>
        <li><b>Handler</b>: {$handler}</li>
        <li><b>Git</b>: {$branch}</li>
    </ul>
</section>
HTML;

    }

} 
