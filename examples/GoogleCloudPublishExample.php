<?php

include __DIR__ . '/../vendor/autoload.php';

putenv('GOOGLE_APPLICATION_CREDENTIALS=' . __DIR__ . '/../your-gcloud-key.json');

$client = new \Google\Cloud\PubSub\PubSubClient([
    'projectId' => 'your-project-id-here',
]);

$adapter = new \Superbalist\PubSub\GoogleCloud\GoogleCloudPubSubAdapter($client);

$adapter->publish('my_channel', 'Hello World');
$adapter->publish('my_channel', ['lorem' => 'ipsum']);
$adapter->publish('my_channel', '{"blah": "bleh"}');
