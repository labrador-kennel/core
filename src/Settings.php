<?php declare(strict_types=1);

namespace Cspray\Labrador;

use IteratorAggregate;

/**
 * Represents a read-only, arbitrary data structure that stores configuration and settings information pertinent to
 * Labrador or your application.
 *
 * Settings storage
 * =====================================================================================================================
 * Though it is expected that settings are stored on the filesystem holding your Application it is not necessary for
 * this to be the case. Your settings can be stored as a PHP object, in your database, or something you access over
 * HTTP. Out-of-the-box Labrador currently only provides for loading settings from the filesystem using PHP or JSON
 * files... though all of the Settings functionality is interface driven and new implementations could easily be created
 * to handle different file types or different storage locations.
 *
 * Ultimately, however you store the actual settings information in your Application code we treat them as a
 * multi-dimensional array of scalar values and will document all settings as if they are PHP arrays. It is your
 * responsibility to ensure that your custom implementations convert your settings information into the appropriate
 * data structure.
 *
 * Expected structure and vendor namespacing
 * =====================================================================================================================
 * The key in the first level of the settings structure should be a vendor name that stores an array of settings data
 * specific to your application or plugin. For example, assume that there's some Labrador specific configuration,
 * configuration for your application, and configuration for a third-party Plugin that provides a database connection
 * using amphp/postgres. The expected, first-level structure for these settings would look like:
 *
 * [
 *   'labrador' => [ ... ],
 *   'myApp' => [ ... ],
 *   'postgres' => [ ... ]
 * ]
 *
 * The 'labrador' key is a reserved key and is expected to only be used to hold configuration settings for internal
 * Labrador components. All other words are valid key names but you should be sensible with what you choose.
 *
 * Additionally, all of the values in this structure are expected to be accessible through dot access. For example,
 * if we assume that the following configuration exists:
 *
 * [
 *   'myApp' => [
 *     'foo' => 'bar',
 *     'bar' => [
 *       'baz' => 'qux'
 *     ]
 *   ]
 * ]
 *
 * The following table shows $keys passed to Settings::get and the value that we would expect to receive.
 *
 * key                  | value
 * ----------------------------------------------------------------------------------
 * myApp                | ['foo' => 'bar', 'bar' => ['baz' => 'qux']]
 * myApp.foo            | 'bar'
 * myApp.bar            | ['baz' => 'qux']
 * myApp.bar.baz        | 'qux'
 * myApp.bar.baz.qux    | throws Exception
 *
 * Calls to Settings::has should adhere to the same rules for dot access.
 *
 * Throw Exceptions on Settings Not Found
 * =====================================================================================================================
 * Sometimes if you attempt to access a value in an array for a key that does not exist it is expected and it is ok to
 * just return a null value. We do not consider Settings access to be one of those cases. We expect access to Settings
 * data to involve critical aspects of your Application and what is available should be explicitly known to all of the
 * developers of the application. If you attempt to access a setting that is not present we fail fast to try to prevent
 * your Application from starting in an invalid state. If you do not want to deal with this exception or your setting
 * truly is optional then make sure to check for the existence of the key using Settings::has
 *
 * @package Cspray\Labrador
 */
interface Settings extends IteratorAggregate {

    /**
     * @param string $key
     * @return string|int|float|bool|array
     */
    public function get(string $key);

    /**
     * @param string $key
     * @return bool
     */
    public function has(string $key) : bool;
}
