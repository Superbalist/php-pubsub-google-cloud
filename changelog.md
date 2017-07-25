# Changelog

## 5.0.0 - 2017-07-25

* Add support for using Google Cloud batch requests (aka background daemon)

## 4.0.1 - 2017-07-21

* Allow for google/cloud ^0.29.0|^0.30.0|^0.31.0|^0.32.0|^0.33.0|^0.34.0|^0.35.0

## 4.0.0 - 2017-05-16

* Allow for google/cloud ^0.26.0|^0.27.0|^0.28.0
* Bump up to superbalist/php-pubsub ^2.0
* Add new publishBatch method to GoogleCloudPubSubAdapter

## 3.0.1 - 2017-04-03

* Fix to gRPC timeouts
* Allow for google/cloud ^0.21.0|^0.22.0|^0.23.0|^0.24.0|^0.25.0

## 3.0.0 - 2017-01-03

* Bump up google/cloud requirement to ^0.11.0|^0.12.0|^0.13.0|^0.20.0

## 2.0.2 - 2017-01-03

* Allow for google/cloud ^0.10.0

## 2.0.1 - 2016-10-05

* Fix to subscriber bug - client identifier needs to be unique across topics.

## 2.0.0 - 2016-10-05

* Add new `$clientIdentifier` & `setClientIdentifier()` functionality to allow subscribers to use the same, or unique identifiers.

## 1.0.2 - 2016-09-26

* Allow for google/cloud ^0.8.0 and ^0.9.0

## 1.0.1 - 2016-09-15

* Ack messages individually after callable returns successfully
* Add functionality to enable/disable auto topic & subscription creation

## 1.0.0 - 2016-09-05

* Initial release