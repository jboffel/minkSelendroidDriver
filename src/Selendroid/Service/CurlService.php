<?php
namespace Selendroid\Service;

use WebDriver\Exception as WebDriverException;
use WebDriver\Service\CurlServiceInterface;

/**
 * WebDriver\Service\CurlService class
 *
 * @package WebDriver
 */
class CurlService implements CurlServiceInterface
{
    /**
     * {@inheritdoc}
     */
    public function execute($requestMethod, $url, $parameters = null, $extraOptions = array())
    {
        $customHeaders = array(
            'Content-Type: application/json;charset=UTF-8',
            'Accept: application/json;charset=UTF-8',
        );

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 300);

        switch ($requestMethod) {
            case 'GET':
                break;

            case 'POST':
                if ($parameters && is_array($parameters)) {
                    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($parameters));
                } else {
                    $customHeaders[] = 'Content-Length: 0';
                }

                curl_setopt($curl, CURLOPT_POST, true);
                break;

            case 'DELETE':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;

            case 'PUT':
                if ($parameters && is_array($parameters)) {
                    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($parameters));
                } else {
                    $customHeaders[] = 'Content-Length: 0';
                }

                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
                break;
        }

        foreach ($extraOptions as $option => $value) {
            curl_setopt($curl, $option, $value);
        }

        curl_setopt($curl, CURLOPT_HTTPHEADER, $customHeaders);

        $rawResults = trim(curl_exec($curl));
        $info = curl_getinfo($curl);

        if (CURLE_GOT_NOTHING !== curl_errno($curl) && $error = curl_error($curl)) {
            $message = sprintf(
                'Curl error thrown for http %s to %s%s',
                $requestMethod,
                $url,
                $parameters && is_array($parameters)
                    ? ' with params: ' . json_encode($parameters) : ''
            );

            throw WebDriverException::factory(WebDriverException::CURL_EXEC, $message . "\n\n" . $error);
        }

        curl_close($curl);

        return array($rawResults, $info);
    }
}
