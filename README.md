InwxBundle - A bundle to talk to the inwx api (inwx.de/inwx.com)
==============================================================

Features
--------

 * Uses the inwx/domrobot class: https://github.com/inwx/php-client
 * Symfony Service that performs api login upon __construct()
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

The following configuration lines are required:

```yaml
bingemer_inwx:
    username: inwx web user # Required: Username
    password: inwx web pass # Required: Passwort
    url: ~                  # Defaults to https://api.domrobot.com/xmlrpc/   
    locale: "%locale%"      # Defaults to locale parameter
```
