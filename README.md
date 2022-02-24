# NovalnetAG by Goldbach

[<img src="https://badgen.net/badge/Powered%20by/Goldbach/red" />](https://github.com/Goldbach07/)
[<img src="https://badgen.net/badge/Developed%20for/Symfony/black" />](https://symfony.com/)
[<img src="https://badgen.net/badge/Developed%20for/Drupal/blue" />](https://www.drupal.org/)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

NovalnetAG by Goldbach is a PHP library developed to integrate the Novalnet payment system with Symfony and Drupal.

## Installation

Use the composer to install

```bash
composer require goldbach-algorithms/novalnet-ag
```

## Usage

### Transaction
To execute a transaction it is necessary to assign the configuration keys of your Novalnet account. Then configure customer, billing address and payment data.  At the end, a link will be generated to the payment page to which the customer must be redirected to complete their purchase.
```php
# add use Novalnet
use GoldbachAlgorithms\Novalnet\Novalnet;

# create a instance of Novalnet
$novalnet = new Novalnet();

# set account config keys
$novalnet->setPaymentKey('YOUR_PAYMENT_KEY');
$novalnet->setSignature('YOUR_SIGNATURE');
$novalnet->setTariff('YOUR_TARIFF_CODE');

# set transaction data
$novalnet->setTransaction(
            'CREDITCARD', // payment type
            '7', // amount
            'EUR', // currency
            1, // test mode (true or false)
            '', // return url to redirect (success)
            '' // return url to redirect (error)
        );

# set customer data
$novalnet->setCustomer(
            'Max', // first name
            'Mustermann', // last name
            'test@novalnet.de', //e-mail
            '+49 174 7781423' // mobile number
            '1911-11-11', // birth date
            'm', // gender (m or f)
            '+49 (0)89 123456', // telephone number (optional)
            '' // fax (optional)
        );

# set billing data
$novalnet->setBilling(
            '2', // house number
            'Musterstr', // street
            'Musterhausen', // city
            '12345', // zipcode
            'DE', // country code
            'ABC GmbH', // company name (optional)
        );

# set hide blocks (will change the payment screen)
$novalnet->setHideBlocks(
            [
                'ADDRESS_FORM',
                'SHOP_INFO', 
                'LANGUAGE_MENU', 
                'TARIFF'
            ]
        );

# set skip pages (will change the payment screen)
$novalnet->setSkipPages(
            [
                'CONFIRMATION_PAGE',
                'SUCCESS_PAGE',
                'PAYMENT_PAGE'
            ]
        );

# generating payment link
$payment_link = $novalnet->getLink();
```

### Transaction return
After payment, a unique code will be generated that will be returned along with the secret that is the identification of the payment page generated previously
```php
# success returns
stdClass Object (
     [status] => "success"
     [link] => "https://paygate.novalnet.de/nn/d8884c8c299cfdd7232964e5fe788849"
     [secret] => "d8884c8c299cfdd7232964e5fe788849"
)

# error return
stdClass Object (
     [status] => "error"
     [link] => "Invalid payment type or payment type inactive"
)
```

### Refund
In the refund process, the full amount or partial amount can be refunded. When the value of 'amount' is not filled, the full amount will be applied.

```php
# add use Novalnet
use GoldbachAlgorithms\Novalnet\Novalnet;

# create a instance of Novalnet
$novalnet = new Novalnet();

# set account payment key
$novalnet->setPaymentKey('2cbd9c540641923027adb8ab89decc05');

# config refund data
$refund = $novalnet->refund(
            '14533600047325226', // tid
            'fail', // reason
            'EN', // language
            '200' // amount to refund (optional)
        );
```

### Refund return
```php
# success returns
stdClass Object (
     [status] => "success"
     [tid] => "14533600047325226"
     [refunded_amount] => "200"
)

# error return
stdClass Object (
     [status] => "error"
     [message] => "Amount larger than zero required"
)
```

## License
[MIT](https://choosealicense.com/licenses/mit/)

Copyright © 2021 [Goldbach Algorithms](https://github.com/GoldbachAlgorithms/NovalnetAG/blob/main/LICENSE)