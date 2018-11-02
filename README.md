[![Build Status](https://travis-ci.org/michaelesmith/dyndns-processor-cache.svg?branch=master)](https://travis-ci.org/michaelesmith/dyndns-processor-cache)

# What is this?
This is a processor for the dyndns-kit framework to allow other processors to be cached via [PSR-6](https://www.php-fig.org/psr/psr-6/) so that they are only called with new or updated query information. If you don't know what DynDNS-Kit is take a [look](https://github.com/michaelesmith/dyndns-kit).

# Install
`composer require "michaelesmith/dyndns-processor-cache"`

You will also need a PSR-6 compatible cache library such as `"cache/cache"` or `"symfony/cache"`.

# How do I use it
To see a full example usage please refer to the [example project](https://github.com/michaelesmith/dyndns-example). 

## Basic usage
```php
$cacheProcessor = new CacheProcessor(
    new JsonProcessor(__DIR__ . '/var/dns.json'),
    new FilesystemCachePool(
        new \League\Flysystem\Filesystem(
            new \League\Flysystem\Adapter\Local(__DIR__ . '/../var/')
        )
    )
);
```

This example uses the [League Flysystem](https://github.com/thephpleague/flysystem) cache library but any one could be used. In this example only the initial request or one with a new hostname or new ip would fall through to the embedded processor. This is useful for processors that tend to be expensive, especially if the data rarely changes which is usually the case with most DynDNS implementations.

# Contributing
Have an idea to make something better? Submit a pull request. PR's make the open source world turn. :earth_americas: :earth_asia: :earth_africa: :octocat: Happy Coding!
