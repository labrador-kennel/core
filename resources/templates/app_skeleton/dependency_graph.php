namespace <?= $appNamespace ?>;

use Auryn\Injector;
use Cspray\Labrador\Application as LabradorApplication;

/**
 *
 * @package <?= $appNamespace . PHP_EOL ?>
 * @license See LICENSE in source root
 */
class DependencyGraph {

    public function wireObjectGraph(Injector $injector = null) : Injector {
        $injector = $injector ?? new Injector();

        $injector->share(Application::class);
        $injector->alias(LabradorApplication::class, Application::class);

        return $injector;
    }

}
