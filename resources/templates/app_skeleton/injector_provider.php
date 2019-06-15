namespace <?= $appNamespace ?>;

use Cspray\Labrador\Configuration as LabradorConfiguration;
use Cspray\Labrador\DependencyGraph as LabradorDependencyGraph;

return function(LabradorConfiguration configuration) {
    $injector = (new LabradorDependencyGraph($configuration))->wireObjectGraph();
    return (new DependencyGraph())->wireObjectGraph($injector);
};
