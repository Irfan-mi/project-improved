<?php

namespace App\Libraries;

class BiometricLibrary
{

    var $CloudABIS_API_URL = 'https://bioplugin.cloudabis.com/v12/';
    var $CloudABISAppKey = '58a9fa2fa73c43219fa5fba624fe02c4';
    var $CloudABISSecretKey = '640611549E9D4D34B2E068DA29C4208F';
    var $ENGINE_NAME = 'FingerPrint';
    var $FORMAT = 'ISO';

    public function generateToken()
    {
        try {
            $clientAPIKey = $this->CloudABISAppKey;
            $clientKey = $this->CloudABISSecretKey;

            $data = [
                'clientAPIKey' => $clientAPIKey,
                'clientKey' => $clientKey,
            ];

            $client = \Config\Services::curlrequest();

            $response = $client->request('POST', $this->CloudABIS_API_URL . 'api/Authorizations/Token', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Cache-Control' => 'no-cache',
                    'postman-token' => '6f57f414-8466-926e-03e0-38a76c201598 ',
                ],
                'json' => $data,
                'http_errors' => false, // Handle errors manually
            ]);

            $httpCode = $response->getStatusCode();
            $response = json_decode($response->getBody());

            if ($httpCode !== 200) {
                return "HTTP Error: " . $httpCode;
            } else {
                return isset($response->responseData) ? $response->responseData : null;
            }
        } catch (\Exception $e) {
            throw new \Exception("Experiencing technical difficulties!");
        }
    }


    public function isRegistered($biometricRequest, $token)
    {
        $registrationid = $biometricRequest->username;
        $engineName = $this->ENGINE_NAME;
        $customerKey = $this->CloudABISSecretKey;

        $data = [
            'ClientKey' => $customerKey,
            'RegistrationID' => $registrationid,
        ];

        $client = \Config\Services::curlrequest();

        $response = $client->request('POST', $this->CloudABIS_API_URL . 'api/Biometrics/IsRegistered', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
                'Cache-Control' => 'no-cache',
            ],
            'json' => $data,
            'http_errors' => false, // Handle errors manually
        ]);

        $httpCode = $response->getStatusCode();
        $responseBody = json_decode($response->getBody());

        if ($httpCode !== 200) {
            return [$httpCode, $responseBody];
        } else {
            return [$httpCode, $responseBody];
        }
    }

    public function register($biometricRequest, $token)
    {
        $customerKey = $this->CloudABISSecretKey;
        $registrationid = $biometricRequest->username;

        $data = [
            'ClientKey' => $customerKey,
            'RegistrationID' => $registrationid,
            'Images' => [
                'Fingerprint' => [
                    [
                        'Position' => 1,
                        'Base64Image' => $biometricRequest->templateXML
                    ]
                ]
            ]
        ];

        $client = \Config\Services::curlrequest();

        $response = $client->request('POST', $this->CloudABIS_API_URL . 'api/Biometrics/Register', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
                'Cache-Control' => 'no-cache',
            ],
            'json' => $data,
            'http_errors' => false, // Handle errors manually
        ]);

        $httpCode = $response->getStatusCode();
        $responseBody = json_decode($response->getBody());

        if ($httpCode !== 200) {
            return [$httpCode, $responseBody];
        } else {
            return [$httpCode, $responseBody];
        }
    }

    public function update($biometricRequest, $token)
    {
        $registrationid = $biometricRequest->username;
        $customerKey = $this->CloudABISSecretKey;

        $data = json_encode([
            'ClientKey' => $customerKey,
            'RegistrationID' => $registrationid,
            'Images' => [
                'Fingerprint' => [
                    [
                        'Position' => 1,
                        'Base64Image' => $biometricRequest->templateXML
                    ]
                ]
            ]
        ]);

        $client = \Config\Services::curlrequest();

        $response = $client->request('POST', $this->CloudABIS_API_URL . "api/Biometrics/Update", [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
                'Cache-Control' => 'no-cache',
            ],
            'body' => $data,
            'http_errors' => false, // Handle errors manually
        ]);

        $httpCode = $response->getStatusCode();
        $responseBody = json_decode($response->getBody());

        if ($httpCode !== 200) {
            return [$httpCode, $responseBody];
        } else {
            return [$httpCode, $responseBody];
        }
    }

    public function identify($biometricRequest, $token)
    {
        $customerKey = $this->CloudABISSecretKey;

        $client = \Config\Services::curlrequest();

        $data = json_encode([
            'ClientKey' => $customerKey,
            'Images' => [
                'Fingerprint' => [
                    [
                        'Position' => 1,
                        'Base64Image' => $biometricRequest->templateXML
                    ]
                ]
            ]
        ]);

        $response = $client->request('POST', $this->CloudABIS_API_URL . "api/Biometrics/Identify", [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
                'Cache-Control' => 'no-cache',
            ],
            'body' => $data,
            'http_errors' => false, // Handle errors manually
        ]);

        $httpCode = $response->getStatusCode();
        $responseData = json_decode($response->getBody());

        if ($httpCode !== 200) {
            return [$httpCode, $response->getReasonPhrase()];
        } else {
            return [$httpCode, $responseData];
        }
    }
}
