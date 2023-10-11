<?php
declare(strict_types=1);

$order = [
    'sender_company' => 'BaseLinker',
    'sender_fullname' => 'Jan Kowalski',
    'sender_address' => 'Kopernika 10',
    'sender_city' => 'Gdansk',
    'sender_postalcode' => '80208',
    'sender_email' => '',
    'sender_phone' => '666666666',

    'delivery_company' => 'Spring GDS',
    'delivery_fullname' => 'Maud Driant',
    'delivery_address' => 'Strada Foisorului, Nr. 16',
    'delivery_city' => 'Bucuresti, Sector 3',
    'delivery_postalcode' => '031179',
    'delivery_country' => 'RU',
    'delivery_email' => 'john@doe.com',
    'delivery_phone' => '555555555',

    //bez tego parametru api generowalo przesylke ale zwracalo blad.
    'weight' => '0.85',
    'value' => '0.85'
];

$params = [
    'api_key' => 'f16753b55cac6c6e',
    'label_format' => 'PDF',
    'service' => 'PPTT',
];

include 'CourierClient.php';

try {
    $courierClient = new CourierClient($params['api_key']);
    $trackingNumber = $courierClient->newPackage($order, $params);
    $courierClient->packagePDF($trackingNumber);
} catch (Exception $e) {
    echo $e->getMessage();
    die();
}

