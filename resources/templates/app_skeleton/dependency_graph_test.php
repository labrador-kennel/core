namespace <?= $appNamespace ?>\Test;

use <?= $appNamespace ?>\DependencyGraph;
use Cspray\Labrador\Application as LabradorApplication;
use Amp\PHPUnit\AsyncTestCase;

class ApplicationTest extends AsyncTestCase {

    public function testApplicationCreatedForLabradorApplication() {
        $injector = (new DependencyGraph())->wireObject();
        $application = $injector->make(LabradorApplication::class);

        $this->assertInstanceOf(Application::class, $application);
    }

    // You should populate this with more (and better) tests!

}
