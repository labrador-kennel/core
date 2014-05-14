<?php

/**
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace LabradorDemo\Model;

use SplFileObject;

class UserGuide {

    private $userGuide;
    private $cacheFile;

    function __construct(SplFileObject $userGuideFile, SplFileObject $cacheFile = null) {
        $this->userGuide = $userGuideFile;
        $this->cacheFile = $cacheFile;
    }

    function getMarkdownContent() {
        return $this->userGuide->fgets();
    }

    function getHtmlContent() {
        if (function_exists('curl_init')) { // Don't use cURL in production environments, check out rdlowrey/Artax
            try {
                $file = $this->docDir . '/002-Labrador-User-Guide.md';
                $userGuide = file_get_contents($file);
            } catch (\ErrorException $error) {
                return false;
            }

            $requestBody = json_encode(['text' => $userGuide]);
            $handle = curl_init('https://api.github.com/markdown');
            curl_setopt_array($handle, [
                CURLOPT_POST => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POSTFIELDS => $requestBody,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($requestBody),
                    'User-Agent: PHP ' . PHP_VERSION . ' cURL'
                ]
            ]);

            return curl_exec($handle);
        }

        return false;
    }

} 
