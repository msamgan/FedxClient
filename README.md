## About FedxClient

some text

##Get Rates

```
$fedxRateRequestData = [
    'shipper_address' => [
        'StreetLines' => ['10 Fed Ex Pkwy'],
        'City' => 'Memphis',
        'StateOrProvinceCode' => 'TN',
        'PostalCode' => '38115',
        'CountryCode' => 'US'
    ],
    'recipient_address' => [
        'StreetLines' => ['13450 Farmcrest Ct'],
        'City' => 'Dubai',
        'StateOrProvinceCode' => 'DB',
        'PostalCode' => '20171',
        'CountryCode' => 'AE'
    ],
    'parcels' => [
        0 => [
            'weight' => [
                'Value' => 2.0,
                'Units' => 'LB'
            ],
            'dimensions' => [
                'Length' => 2,
                'Width' => 2,
                'Height' => 2,
                'Units' => 'IN'
            ]
        ]
    ]
];

$rates = (new FedxRatesAdapter())->getRates($fedxRateRequestData);
```

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
