<?php
/**
 * @license Apache-2.0
 *
 * Modified by __root__ on 31-May-2022 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

if (class_exists('Deconf_AIWP_Google_Client', false)) {
    // Prevent error with preloading in PHP 7.4
    // @see https://github.com/googleapis/google-api-php-client/issues/1976
    return;
}

$classMap = [
    'Deconf\AIWP\Google\\Client' => 'Deconf_AIWP_Google_Client',
    'Deconf\\AIWP\\Google\\Service' => 'Deconf_AIWP_Google_Service',
    'Deconf\\AIWP\\Google\\AccessToken\\Revoke' => 'Deconf_AIWP_Google_AccessToken_Revoke',
    'Deconf\\AIWP\\Google\\AccessToken\\Verify' => 'Deconf_AIWP_Google_AccessToken_Verify',
    'Deconf\AIWP\Google\\Model' => 'Deconf_AIWP_Google_Model',
    'Deconf\\AIWP\\Google\\Utils\\UriTemplate' => 'Deconf_AIWP_Google_Utils_UriTemplate',
    'Deconf\\AIWP\\Google\\AuthHandler\\Guzzle6AuthHandler' => 'Deconf_AIWP_Google_AuthHandler_Guzzle6AuthHandler',
    'Deconf\\AIWP\\Google\\AuthHandler\\Guzzle7AuthHandler' => 'Deconf_AIWP_Google_AuthHandler_Guzzle7AuthHandler',
    'Deconf\\AIWP\\Google\\AuthHandler\\Guzzle5AuthHandler' => 'Deconf_AIWP_Google_AuthHandler_Guzzle5AuthHandler',
    'Deconf\\AIWP\\Google\\AuthHandler\\AuthHandlerFactory' => 'Deconf_AIWP_Google_AuthHandler_AuthHandlerFactory',
    'Deconf\\AIWP\\Google\\Http\\Batch' => 'Deconf_AIWP_Google_Http_Batch',
    'Deconf\\AIWP\\Google\\Http\\MediaFileUpload' => 'Deconf_AIWP_Google_Http_MediaFileUpload',
    'Deconf\\AIWP\\Google\\Http\\REST' => 'Deconf_AIWP_Google_Http_REST',
    'Deconf\\AIWP\\Google\\Task\\Retryable' => 'Deconf_AIWP_Google_Task_Retryable',
    'Deconf\\AIWP\\Google\\Task\\Exception' => 'Deconf_AIWP_Google_Task_Exception',
    'Deconf\\AIWP\\Google\\Task\\Runner' => 'Deconf_AIWP_Google_Task_Runner',
    'Deconf\AIWP\Google\\Collection' => 'Deconf_AIWP_Google_Collection',
    'Deconf\\AIWP\\Google\\Service\\Exception' => 'Deconf_AIWP_Google_Service_Exception',
    'Deconf\\AIWP\\Google\\Service\\Resource' => 'Deconf_AIWP_Google_Service_Resource',
    'Deconf\AIWP\Google\\Exception' => 'Deconf_AIWP_Google_Exception',
];

foreach ($classMap as $class => $alias) {
    class_alias($class, $alias);
}

/**
 * This class needs to be defined explicitly as scripts must be recognized by
 * the autoloader.
 */
class Deconf_AIWP_Google_Task_Composer extends \Deconf\AIWP\Google\Task\Composer
{
}

/** @phpstan-ignore-next-line */
if (\false) {
    class Deconf_AIWP_Google_AccessToken_Revoke extends \Deconf\AIWP\Google\AccessToken\Revoke
    {
    }
    class Deconf_AIWP_Google_AccessToken_Verify extends \Deconf\AIWP\Google\AccessToken\Verify
    {
    }
    class Deconf_AIWP_Google_AuthHandler_AuthHandlerFactory extends \Deconf\AIWP\Google\AuthHandler\AuthHandlerFactory
    {
    }
    class Deconf_AIWP_Google_AuthHandler_Guzzle5AuthHandler extends \Deconf\AIWP\Google\AuthHandler\Guzzle5AuthHandler
    {
    }
    class Deconf_AIWP_Google_AuthHandler_Guzzle6AuthHandler extends \Deconf\AIWP\Google\AuthHandler\Guzzle6AuthHandler
    {
    }
    class Deconf_AIWP_Google_AuthHandler_Guzzle7AuthHandler extends \Deconf\AIWP\Google\AuthHandler\Guzzle7AuthHandler
    {
    }
    class Deconf_AIWP_Google_Client extends \Deconf\AIWP\Google\Client
    {
    }
    class Deconf_AIWP_Google_Collection extends \Deconf\AIWP\Google\Collection
    {
    }
    class Deconf_AIWP_Google_Exception extends \Deconf\AIWP\Google\Exception
    {
    }
    class Deconf_AIWP_Google_Http_Batch extends \Deconf\AIWP\Google\Http\Batch
    {
    }
    class Deconf_AIWP_Google_Http_MediaFileUpload extends \Deconf\AIWP\Google\Http\MediaFileUpload
    {
    }
    class Deconf_AIWP_Google_Http_REST extends \Deconf\AIWP\Google\Http\REST
    {
    }
    class Deconf_AIWP_Google_Model extends \Deconf\AIWP\Google\Model
    {
    }
    class Deconf_AIWP_Google_Service extends \Deconf\AIWP\Google\Service
    {
    }
    class Deconf_AIWP_Google_Service_Exception extends \Deconf\AIWP\Google\Service\Exception
    {
    }
    class Deconf_AIWP_Google_Service_Resource extends \Deconf\AIWP\Google\Service\Resource
    {
    }
    class Deconf_AIWP_Google_Task_Exception extends \Deconf\AIWP\Google\Task\Exception
    {
    }
    interface Deconf_AIWP_Google_Task_Retryable extends \Deconf\AIWP\Google\Task\Retryable
    {
    }
    class Deconf_AIWP_Google_Task_Runner extends \Deconf\AIWP\Google\Task\Runner
    {
    }
    class Deconf_AIWP_Google_Utils_UriTemplate extends \Deconf\AIWP\Google\Utils\UriTemplate
    {
    }
}
