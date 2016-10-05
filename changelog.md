# Changelog

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