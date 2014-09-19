<?php

/**
 * 
 * @license See LICENSE in source root
 */

namespace Labrador;

use Symfony\Component\HttpFoundation\Response;

class WelcomeController {

    function index() {
        return new Response($this->getHtml());
    }

    private function getHtml() {
        return <<<'HTML'
<!doctype html>
<html>
    <head>
        <meta charset="utf-8" />
        <title>Welcome to Labrador!</title>
        <link rel="stylesheet" href="http://yui.yahooapis.com/pure/0.5.0/pure-min.css">
        <style>
            body {
                margin: 0 auto;
                width: 900px;
            }

            #main-content > p {
                margin: 0 .5em 1.5em 0;
            }

            #sidebar > h2 {
                margin: 0;
            }
        </style>
    </head>
    <body>
        <header>
            <h1>Labrador</h1>
        </header>
        <article class="pure-g">
            <section id="main-content" class="pure-u-2-3">
                <p>Thanks for trying out Labrador! The first thing you'll want to do is change this ugly front page! You can find the code that Responds with this in <code>/init.php</code>. Change the controller for the <code>GET /</code> route and you're on your way.</p>

                <pre>
// Change this code!
$router->get('/', function(Request $request) {
    return new Response('Your response body here!');
});
                </pre>
                <p>For more information about Labrador you should install the <a href="">Labrador Guide</a> package and navigate to <code>http://your-labrador-project.dev/lg</code> in your browser.</p>
            </section>
            <section id="sidebar" class="pure-u-1-3">
                <h2>Links</h2>
                <ul>
                    <li><a href="http://labrador.cspray.net">Documentation</a></li>
                    <li><a href="https://github.com/cspray/labrador">Source Code</a></li>
                    <li><a href="https://github.com/cspray/labrador/issues">Issues</a></li>
                </ul>

                <h2>Third Party Libs</h2>
                <ul>
                    <li><a href="https://github.com/nikic/FastRoute">Fast Route</a></li>
                    <li><a href="https://github.com/rdlowrey/Artax">Artax</a></li>
                    <li><a href="https://github.com/symfony/HttpKernel">Symfony HttpKernel</a></li>
                    <li><a href="https://github.com/cspray/Configlet">Configlet</a></li>
                </ul>
            </section>
        </article>
        <footer>
        </footer>
    </body>
</html>
HTML;

    }

} 
