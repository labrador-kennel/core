<!DOCTYPE html>
<html>
    <head>
        <title>Labrador Demo</title>
        <meta charset="utf-8" />
        <link href="/css/normalize.css" type="text/css" rel="stylesheet" />
        <link href="/css/labrador_guide/prism.css" type="text/css" rel="stylesheet" />
        <link href="/css/labrador_guide/main.css" type="text/css" rel="stylesheet" />
        <script src="/js/labrador_guide/prism.js"></script>
    </head>
    <body>

        <header>
            <h1>Labrador</h1>
        </header>

        <section id="main-content">
            <p>Thanks for trying out Labrador. Now that you've gotten this far you're ready to start developing your own application!</p>
            <p>What you're looking at now is the built-in Labrador user guide. It should be all you need to get started and understand Labrador enough to get the most benefit out of it. What better way to get started than a "Hello World!" Labrador style? While Labrador might be a bit overkill for outputting static content hopefully you can use your imagination to see how this might be easily expanded to be more useful.</p>

            <article id="hello-world">
                <h1>Hello Labrador!</h1>
                <p>The first thing we're going to do is add the appropriate route to make sure that our example is accessible from the browser. If you head over to <code>/config/routes.php</code> you'll see something that looks like the below code. We're gonna add a route to it.</p>
<pre class="language-php"><code class="language-php">&lt;?php

use Labrador\Router\Router;

function(Router $router) {

    // This is the route for the page you're looking at right now
    $router->get('/', 'LabradorGuide\\Controller\\HomeController#index');

    // Let's add a new route for our stuff
    $router->get('/hello-labrador', 'HelloLab\\Controller\\DemoController#index');

};
</code></pre>
            </article>
        </section>

        <footer>

        </footer>

        <aside>
            <h1>Project links</h1>
            <ul>
                <li><a href="http://labrador.cspray.net">Project Homepage</a></li>
                <li><a href="https://github.com/cspray/Labrador">GitHub Repository</a></li>
                <li><a href="https://github.com/cspray/Labrador/issues">Issues and Bugs</a></li>
            </ul>
        </aside>


        <aside>
            <h1>Libraries Utilized</h1>
            <p>These are the libraries currently used by Labrador out of the box.</p>
            <ul>
                <li><a href="https://github.com/rdlowrey/Auryn">Auryn</a> by <a href="https://github.com/rdlowrey">rdlowrey</a></li>
                <li><a href="https://github.com/nikic/FastRoute">FastRoute</a> by <a href="https://github.com/nikic">nikic</a></li>
                <li><a href="https://github.com/symfony/">Symfony HttpKernel</a> by <a href="http://fabien.potencier.org/">Fabien Potencier</a> and <a href="http://sensiolabs.com/">Sensio Labs</a></li>
                <li><a href="https://github.com/cspray/Configlet">Configlet</a> by <a href="https://github.com/cspray">The same guy who wrote Labrador</a></li>
            </ul>
        </aside>

    </body>
</html>
