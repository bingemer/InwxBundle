InwxBundle - A bundle to talk to the inwx api
=============================================

Build Status: [![Build Status](https://scrutinizer-ci.com/g/bingemer/InwxBundle/badges/build.png?b=master)](https://scrutinizer-ci.com/g/bingemer/InwxBundle/build-status/master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/bingemer/InwxBundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/bingemer/InwxBundle/?branch=master)

Features
--------

 * Uses the inwx class: https://github.com/inwx/php-client
 * Service that performs api login upon __construct(), logout on __destruct()
 * predefined funtions for common steps like new DNS entry or update an existing entry.

Installation
-----------------------------------

Add the package to your composer.json file
```
"bingemer/inwxbundle": "dev-master",
```

Add this to app/AppKernel.php
```php
<?php
    public function registerBundles()
    {
        $bundles = array(
            ...
            new Bingemer\InwxBundle\BingemerInwxBundle(),
        );

        ...

        return $bundles;
    }
```


Configuration
-------------

### 1) Edit app/config.yml

Add the following parameters to parameters.yml
```yaml
parameters:
    ...
    inwx_user: username
    inwx_pass: password
    inwx_url: 'https://api.domrobot.com/xmlrpc/'
    inwx_locale: en
```

Usage
-----
In your Controller get the service:
```php
$domrobot = $this->get('bingemer_inwx_bundle');
```

Use the functions provided to create an A entry (4th parameter is entry type which defaults to 'A'):
```php
$result = $domrobot->createRecord('hostname w/o domain', 'ip-address', 'domain');
```
update record:
```php
$result = $domrobot->updateRecord('inwx_id, 'ip-address');
```
delete record:
```php
$result = $domrobot->deleteRecord('inwx_id);
```

The result array is 1:1 from the original Domrobot class documented here:
[API DOC](https://www.inwx.de/en/help/apidoc)

In case of my createRecord() function, the "inwx_id" is contained in the $result.

The original Domrobot class can be found here:
[Inwx Domrobot Class](https://github.com/inwx/php-client)

More information and classes for other languages can be found here:
[https://www.inwx.de/en/offer/api](https://www.inwx.de/en/offer/api)

Errors
------
This is my first symfony bundle, if you have any problems or found mistakes I made please tell me ;)
