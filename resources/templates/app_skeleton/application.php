namespace <?= $appNamespace ?>;

use Cspray\Labrador\AbstractApplication;
use Amp\Promise;
use function Amp\call;

/**
 *
 * @package <?= $appNamespace . PHP_EOL ?>
 * @license See LICENSE in source root
 */
class Application extends AbstractApplication {

    public function execute() : Promise {
        return call(function() {
            // Execute your Application logic here
        });
    }

}
