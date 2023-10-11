<?php

class CourierClient
{
    private string $apiKey;
    const API_URL = 'https://mtapi.net/?testMode=1';

    const DEFAULT_MAX_CHARACTERS_LIMIT = 30;

    /**
     * @param string $apiKey
     */
    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * @param array $order
     * @param array $params
     * @return void
     * @throws Exception
     */
    public function newPackage(array $order, array $params): string
    {
        $this->validation($order);

        @$data = [
            'Apikey' => $this->apiKey,
            'Command' => 'OrderShipment',
            'Shipment' => [
                'LabelFormat' => $params['label_format'],
                'Service' => $params['service'],
                'Weight' => $order['weight'],
                'Value' => $order['value'],
                'ConsignorAddress' => [
                    'Company' => $order['sender_company'],
                    'Name' => $order['sender_fullname'],
                    'AddressLine1' => $order['sender_address'],
                    'City' => $order['sender_city'],
                    'Zip' => $order['sender_postalcode'],
                    'Email' => $order['sender_email'],
                    'Phone' => $order['sender_phone'],
                ],
                'ConsigneeAddress' => [
                    'Company' => $order['delivery_company'],
                    'Name' => $order['delivery_fullname'],
                    'AddressLine1' => $order['delivery_address'],
                    'City' => $order['delivery_city'],
                    'Zip' => $order['delivery_postalcode'],
                    'Country' => $order['delivery_country'],
                    'Email' => $order['delivery_email'],
                    'Phone' => $order['delivery_phone'],
                ]
            ]
        ];
        $response = $this->apiCall($data);
        if(!isset($response['Shipment']['TrackingNumber'])) {
            throw new Exception($response['Error']);
        }


        return $response['Shipment']['TrackingNumber'];
    }

    /**
     * @param string $trackingNumber
     * @return void
     * @throws Exception
     */
    public function packagePDF(string $trackingNumber): void
    {
        $data = [
            'Apikey' => $this->apiKey,
            'Command' => 'GetShipmentLabel',
            'Shipment' => [
                'TrackingNumber' => $trackingNumber,
            ]
        ];
        $response = $this->apiCall($data);
        if(!isset($response['Shipment']['TrackingNumber'])) {
            throw new Exception($response['Error']);
        }

        $labelFile = base64_decode($response['Shipment']['LabelImage']);
        header('Content-type: application/pdf');
        header('Cache-Control: no-cache');
        header('Pragma: no-cache');
        header('Content-Disposition: inline;filename="' . $response['Shipment']['TrackingNumber'] . '.pdf"');
        header('Content-length: ' . strlen($labelFile));
        echo $labelFile;
        exit();
    }

    /**
     * @param array $data
     * @return array
     * @throws Exception
     */
    private function apiCall(array $data): array
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Content-Type: text/json',
        ]);
        curl_setopt($curl, CURLOPT_URL, static::API_URL);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($curl);
        if (!$result) {
            throw new Exception('Connection Failure.');
        }
        curl_close($curl);
        return json_decode($result, true);
    }

    /**
     * @param array $order
     * @return void
     * @throws Exception
     */
    private function validation(array $order): void
    {
        if (strlen($order['sender_company']) > static::DEFAULT_MAX_CHARACTERS_LIMIT) {
            throw new Exception('sender_company have more than ' . static::DEFAULT_MAX_CHARACTERS_LIMIT . ' characters.');
        }
        if (strlen($order['sender_fullname']) > static::DEFAULT_MAX_CHARACTERS_LIMIT) {
            throw new Exception('sender_fullname have more than ' . static::DEFAULT_MAX_CHARACTERS_LIMIT . ' characters.');
        }
        if (strlen($order['sender_address']) > static::DEFAULT_MAX_CHARACTERS_LIMIT) {
            throw new Exception('sender_address have more than ' . static::DEFAULT_MAX_CHARACTERS_LIMIT . ' characters.');
        }
        if (strlen($order['sender_city']) > static::DEFAULT_MAX_CHARACTERS_LIMIT) {
            throw new Exception('sender_city have more than ' . static::DEFAULT_MAX_CHARACTERS_LIMIT . ' characters.');
        }
        if (strlen($order['sender_postalcode']) > static::DEFAULT_MAX_CHARACTERS_LIMIT) {
            throw new Exception('sender_postalcode have more than ' . static::DEFAULT_MAX_CHARACTERS_LIMIT . ' characters.');
        }
        if (strlen($order['delivery_company']) > static::DEFAULT_MAX_CHARACTERS_LIMIT) {
            throw new Exception('delivery_company have more than ' . static::DEFAULT_MAX_CHARACTERS_LIMIT . ' characters.');
        }
        if (strlen($order['delivery_fullname']) > static::DEFAULT_MAX_CHARACTERS_LIMIT) {
            throw new Exception('delivery_fullname have more than ' . static::DEFAULT_MAX_CHARACTERS_LIMIT . ' characters.');
        }
        if (strlen($order['delivery_address']) > static::DEFAULT_MAX_CHARACTERS_LIMIT) {
            throw new Exception('delivery_address have more than ' . static::DEFAULT_MAX_CHARACTERS_LIMIT . ' characters.');
        }
        if (strlen($order['delivery_postalcode']) > static::DEFAULT_MAX_CHARACTERS_LIMIT) {
            throw new Exception('delivery_address have more than ' . static::DEFAULT_MAX_CHARACTERS_LIMIT . ' characters.');
        }
        if (strlen($order['delivery_phone']) > 15) {
            throw new Exception('delivery_address have more than ' . 15 . ' characters.');
        }
        if (!in_array($order['delivery_country'], [
            'AU', 'AT', 'BE', 'BG', 'BR', 'BY', 'CA', 'CH', 'CN', 'CY', 'CZ', 'DK', 'DE', 'EE', 'ES', 'FI', 'FR', 'GB', 'GF', 'GI', 'GP', 'GR', 'HK', 'HR', 'HU', 'ID', 'IE', 'IL', 'IS', 'IT', 'JP', 'KR', 'LB', 'LT', 'LU', 'LV', 'MQ', 'MT', 'MY',
            'NL', 'NO', 'NZ', 'PL', 'PT', 'RE', 'RO', 'RS', 'RU', 'SA', 'SE', 'SG', 'SI', 'SK', 'TH', 'TR', 'US',
        ])) {
            throw new Exception('delivery_country have wrong value.');
        }
    }
}