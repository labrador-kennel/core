namespace <?= $appNamespace ?>\Test;

use <?= $appNamespace ?>\Application;
use Cspray\Labrador\Application as LabradorApplication;
use Amp\PHPUnit\AsyncTestCase;

class ApplicationTest extends AsyncTestCase {

    public function testApplicationInstantiable() {
        $application = new Application();

        $this->assertInstanceOf(LabradorApplication::class, $application);
    }

    // You should populate this with more (and better) tests!

}