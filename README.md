# php-pubsub-google-cloud

A Google Cloud adapter for the [php-pubsub](https://github.com/Superbalist/php-pubsub) package.

[![Author](http://img.shields.io/badge/author-@superbalist-blue.svg?style=flat-square)](https://twitter.com/superbalist)
[![Build Status](https://img.shields.io/travis/Superbalist/php-pubsub-google-cloud/master.svg?style=flat-square)](https://travis-ci.org/Superbalist/php-pubsub-google-cloud)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Packagist Version](https://img.shields.io/packagist/v/superbalist/php-pubsub-google-cloud.svg?style=flat-square)](https://packagist.org/packages/superbalist/php-pubsub-google-cloud)
[![Total Downloads](https://img.shields.io/packagist/dt/superbalist/php-pubsub-google-cloud.svg?style=flat-square)](https://packagist.org/packages/superbalist/php-pubsub-google-cloud)


## Installation

```bash
composer require superbalist/php-pubsub-google-cloud
```

## Usage

```php
putenv('GOOGLE_APPLICATION_CREDENTIALS=' . __DIR__ . '/../your-gcloud-key.json');

$client = new \Google\Cloud\PubSub\PubSubClient([
    'projectId' => 'your-project-id-here',
]);

$adapter = new \Superbalist\PubSub\GoogleCloud\GoogleCloudPubSubAdapter($client);


// disable auto topic & subscription creation
$adapter->setAutoCreateTopics(false); // this is true by default
$adapter->setAutoCreateSubscriptions(false); // this is true by default

// set a unique client identifier for the subscriber
$adapter->setClientIdentifier('search_service');

// consume messages
// note: this is a blocking call
$adapter->subscribe('my_channel', function ($message) {
    var_dump($message);
});

// publish messages
$adapter->publish('my_channel', 'HELLO WORLD');
$adapter->publish('my_channel', json_encode(['hello' => 'world']));
$adapter->publish('my_channel', 1);
$adapter->publish('my_channel', false);
```

## Examples

The library comes with [examples](examples) for the adapter and a [Dockerfile](Dockerfile) for
running the example scripts.

Run `make up`.

You will start at a `bash` prompt in the `/opt/php-pubsub` directory.

If you need another shell to publish a message to a blocking consumer, you can run `docker-compose run php-pubsub-google-cloud /bin/bash`

To run the examples:
```bash
$ php examples/GoogleCloudConsumerExample.php
$ php examples/GoogleCloudPublishExample.php (in a separate shell)
```
