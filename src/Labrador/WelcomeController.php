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

            header {
                overflow: auto;
                height: 60px;
            }

            ul {
                list-style: none;
            }

            header h1, header ul {
                margin: 0;
                line-height: 60px;
            }

            header h1, header ul, header ul li {
                float: left;
            }

            header ul li {
                margin-left: 1.5em;
            }

            #main-content > p {
                margin: 0 .5em 1.5em 0;
            }

            #sidebar h2, #sidebar ul {
                margin: 0;
                padding: 0;
            }

            #sidebar ul li {
                margin-top: .75em;
            }

            #sidebar ul li p {
                margin: 0;
            }
        </style>
    </head>
    <body>
        <header>
            <h1>Labrador</h1>
            <ul>
                <li><a href="http://labrador.cspray.net">Docs</a></li>
                <li><a href="https://github.com/cspray/labrador">Source</a></li>
                <li><a href="https://github.com/cspray/labrador/issues">Issues</a></li>
            </ul>
        </header>
        <article class="pure-g">
            <section id="main-content" class="pure-u-2-3">
                <p>Thanks for trying out Labrador! The first thing you'll want to do is change this ugly front page! You can find the code that responds with this in <code>/init.php</code>. Change the controller for the <code>GET /</code> route and you're on your way.</p>
                <pre>
// Change this code in init.php!
$router->get('/', WelcomeController::class . '#index);</pre>
                <p>For more information about Labrador you should check out the <a href="http://labrador.cspray.net">online Labrador Guide</a>. You can also install the <a href="http://github.com/cspray/labrador-guide">Labrador Guide</a> package as a stand-alone addition to your Labrador installations.
            </section>
            <section id="sidebar" class="pure-u-1-3">
                <h2>Third Party Libs</h2>
                <ul>
                    <li>
                        <a href="https://github.com/nikic/FastRoute">Fast Route</a>
                        <p>An extremely performant, regex based HTTP routing library.</p>
                    </li>
                    <li>
                        <a href="https://github.com/rdlowrey/Auryn">Auryn</a>
                        <p>A dependency injection container that encourages inversion of control.</p>
                    </li>
                    <li>
                        <a href="https://github.com/symfony/HttpKernel">Symfony HttpKernel</a>
                        <p>An HTTP abstraction and event dispatcher.</p>
                    </li>
                    <li>
                        <a href="https://github.com/cspray/Configlet">Configlet</a>
                        <p>Configuration library for creating module specific configurations.</p>
                    </li>
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
