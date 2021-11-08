<p align="center">
  <a href="https://webmarketer.io" target="_blank" align="center">
    <img src="https://avatars.githubusercontent.com/u/89253090?s=200&v=4" width="80">
  </a>
  <br />
</p>

# PHP Core plugin for Webmarketer tiers integrations

[![Latest Stable Version](http://poser.pugx.org/webmarketer/webmarketer-php-core-plugin/v)](https://packagist.org/packages/webmarketer/webmarketer-php-core-plugin)
[![Total Downloads](http://poser.pugx.org/webmarketer/webmarketer-php-core-plugin/downloads)](https://packagist.org/packages/webmarketer/webmarketer-php-core-plugin)
[![Latest Unstable Version](http://poser.pugx.org/webmarketer/webmarketer-php-core-plugin/v/unstable)](https://packagist.org/packages/webmarketer/webmarketer-php-core-plugin)
[![PHP Version Require](http://poser.pugx.org/webmarketer/webmarketer-php-core-plugin/require/php)](https://packagist.org/packages/webmarketer/webmarketer-php-core-plugin)
[![License](http://poser.pugx.org/webmarketer/webmarketer-php-core-plugin/license)](https://packagist.org/packages/webmarketer/webmarketer-php-core-plugin)

The official PHP Core bundle for Webmarketer plugins.

## Install
To add this package, your project must meet several requirements :
* PHP >= 5.6
* Composer ([install composer](https://getcomposer.org))

This package is the core used by all Webmarketer tiers integrations and plugins (WordPress, Prestashop). It provides interfaces and utilities for any plugin.  
It is not designed to work as a standalone and must be used along a plugin implementation.

This package wrap the [PHP SDK for Webmarketer](https://github.com/webmarketer-saas/php-webmarketer-sdk). Thereby, check that your project meet the SDK requirements too.

```bash
composer require webmarketer/webmarketer-php-core-plugin
```

## Usage
```php
try {
    // create an instance of the SDK with the desired configuration
    $client = new \Webmarketer\WebmarketerSdk([
        'credential' => '{ ...jsonSa }',
        'scopes' => 'test',
        'default_project_id' => 'webmarketer-awesome-project'
    ]);
} catch (\Webmarketer\Exception\DependencyException $dep_ex) {
    // SDK init throw a dependency exception if requirements are not meet (see Install)
} catch (\Webmarketer\Exception\CredentialException $cred_ex) {
    // SDK automatically try to authenticate you agains API
    // A credential exception could be throw if credentials are invalid
}

// SDK exposes resources services, use them to manipulate your resources
$event_type_service = $client->getEventTypeService();
$field_service = $client->getFieldService();
```

### Integrations based on this package
* [WordPress Plugin](https://github.com/webmarketer-saas/wp-webmarketer)

## Contributing
All SDK dependencies are managed via Composer :
```bash
composer install
```
Run all tests with PHPUnit and the configuration provided :
```bash
composer tests
```
Run all tests and check codecoverage (must be >= 80%) with PHPUnit :
```bash
composer tests-coverage
```
Lint code :
```bash
composer phpcs
```
---
Feel free to report issues and bugs directly on this repository.

## Resources
* [PHP SDK for Webmarketer](https://github.com/webmarketer-saas/php-webmarketer-sdk)
* [App](https://app.webmarketer.io)
* [Official documentation](https://doc.webmarketer.io)
* [Official site](https://webmarketer.io)