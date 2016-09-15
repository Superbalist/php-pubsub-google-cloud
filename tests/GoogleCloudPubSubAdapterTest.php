<?php

namespace Tests;

use Google\Cloud\PubSub\PubSubClient;
use Google\Cloud\PubSub\Subscription;
use Google\Cloud\PubSub\Topic;
use Mockery;
use PHPUnit\Framework\TestCase;
use Superbalist\PubSub\GoogleCloud\GoogleCloudPubSubAdapter;

class GoogleCloudPubSubAdapterTest extends TestCase
{
    public function testGetClient()
    {
        $client = Mockery::mock(PubSubClient::class);
        $adapter = new GoogleCloudPubSubAdapter($client);
        $this->assertSame($client, $adapter->getClient());
    }

    public function testGetSetAutoCreateTopics()
    {
        $client = Mockery::mock(PubSubClient::class);
        $adapter = new GoogleCloudPubSubAdapter($client);
        $this->assertTrue($adapter->areTopicsAutoCreated());

        $adapter->setAutoCreateTopics(false);
        $this->assertFalse($adapter->areTopicsAutoCreated());
    }

    public function testGetSetAutoCreateSubscriptions()
    {
        $client = Mockery::mock(PubSubClient::class);
        $adapter = new GoogleCloudPubSubAdapter($client);
        $this->assertTrue($adapter->areSubscriptionsAutoCreated());

        $adapter->setAutoCreateSubscriptions(false);
        $this->assertFalse($adapter->areSubscriptionsAutoCreated());
    }

    public function testPublishWhenTopicMustBeCreated()
    {
        $topic = Mockery::mock(Topic::class);
        $topic->shouldReceive('exists')
            ->once()
            ->andReturn(false);
        $topic->shouldReceive('create')
            ->once();
        $topic->shouldReceive('publish')
            ->with([
                'data' => 'a:1:{s:5:"hello";s:5:"world";}',
            ])
            ->once();

        $client = Mockery::mock(PubSubClient::class);
        $client->shouldReceive('topic')
            ->with('channel_name')
            ->once()
            ->andReturn($topic);

        $adapter = new GoogleCloudPubSubAdapter($client);

        $adapter->publish('channel_name', ['hello' => 'world']);
    }

    public function testPublishWhenTopicExists()
    {
        $topic = Mockery::mock(Topic::class);
        $topic->shouldReceive('exists')
            ->once()
            ->andReturn(true);
        $topic->shouldNotHaveReceived('create');
        $topic->shouldReceive('publish')
            ->with([
                'data' => 'a:1:{s:5:"hello";s:5:"world";}',
            ])
            ->once();

        $client = Mockery::mock(PubSubClient::class);
        $client->shouldReceive('topic')
            ->with('channel_name')
            ->once()
            ->andReturn($topic);

        $adapter = new GoogleCloudPubSubAdapter($client);

        $adapter->publish('channel_name', ['hello' => 'world']);
    }

    public function testPublishWhenAutoTopicCreationIsDisabled()
    {
        $topic = Mockery::mock(Topic::class);
        $topic->shouldNotHaveReceived('exists');
        $topic->shouldNotHaveReceived('create');
        $topic->shouldReceive('publish')
            ->with([
                'data' => 'a:1:{s:5:"hello";s:5:"world";}',
            ])
            ->once();

        $client = Mockery::mock(PubSubClient::class);
        $client->shouldReceive('topic')
            ->with('channel_name')
            ->once()
            ->andReturn($topic);

        $adapter = new GoogleCloudPubSubAdapter($client, false);

        $adapter->publish('channel_name', ['hello' => 'world']);
    }

    public function testSubscribeWhenSubscriptionMustBeCreated()
    {
        $messageBatch1 = [
            [
                'ackId' => 1,
                'message' => [
                    'data' => base64_encode('a:1:{s:5:"hello";s:5:"world";}')
                ],
            ],
            [
                'ackId' => 2,
                'message' => [
                    'data' => base64_encode('this is a string')
                ],
            ],
        ];
        $messageBatch2 = [
            [
                'ackId' => 3,
                'message' => [
                    'data' => base64_encode('unsubscribe')
                ],
            ],
        ];

        $subscription = Mockery::mock(Subscription::class);
        $subscription->shouldReceive('exists')
            ->once()
            ->andReturn(false);
        $subscription->shouldReceive('create')
            ->once();
        $subscription->shouldReceive('pull')
            ->once()
            ->andReturn($messageBatch1);
        $subscription->shouldReceive('acknowledge')
            ->with(1)
            ->once();
        $subscription->shouldReceive('acknowledge')
            ->with(2)
            ->once();
        $subscription->shouldReceive('pull')
            ->once()
            ->andReturn($messageBatch2);
        $subscription->shouldReceive('acknowledge')
            ->with(3)
            ->once();

        $topic = Mockery::mock(Topic::class);
        $topic->shouldReceive('exists')
            ->once()
            ->andReturn(true);
        $topic->shouldNotHaveReceived('create');
        $topic->shouldReceive('subscription')
            ->with('channel_name')
            ->once()
            ->andReturn($subscription);

        $client = Mockery::mock(PubSubClient::class);
        $client->shouldReceive('topic')
            ->with('channel_name')
            ->once()
            ->andReturn($topic);

        $adapter = new GoogleCloudPubSubAdapter($client);

        $handler1 = Mockery::mock(\stdClass::class);
        $handler1->shouldReceive('handle')
            ->with(['hello' => 'world'])
            ->once();
        $handler1->shouldReceive('handle')
            ->with('this is a string')
            ->once();

        $adapter->subscribe('channel_name', [$handler1, 'handle']);
    }

    public function testSubscribeWhenSubscriptionExists()
    {
        $messageBatch1 = [
            [
                'ackId' => 1,
                'message' => [
                    'data' => base64_encode('a:1:{s:5:"hello";s:5:"world";}')
                ],
            ],
            [
                'ackId' => 2,
                'message' => [
                    'data' => base64_encode('this is a string')
                ],
            ],
        ];
        $messageBatch2 = [
            [
                'ackId' => 3,
                'message' => [
                    'data' => base64_encode('unsubscribe')
                ],
            ],
        ];

        $subscription = Mockery::mock(Subscription::class);
        $subscription->shouldReceive('exists')
            ->once()
            ->andReturn(true);
        $subscription->shouldNotHaveReceived('create');
        $subscription->shouldReceive('pull')
            ->once()
            ->andReturn($messageBatch1);
        $subscription->shouldReceive('acknowledge')
            ->with(1)
            ->once();
        $subscription->shouldReceive('acknowledge')
            ->with(2)
            ->once();
        $subscription->shouldReceive('pull')
            ->once()
            ->andReturn($messageBatch2);
        $subscription->shouldReceive('acknowledge')
            ->with(3)
            ->once();

        $topic = Mockery::mock(Topic::class);
        $topic->shouldReceive('exists')
            ->once()
            ->andReturn(true);
        $topic->shouldNotHaveReceived('create');
        $topic->shouldReceive('subscription')
            ->with('channel_name')
            ->once()
            ->andReturn($subscription);

        $client = Mockery::mock(PubSubClient::class);
        $client->shouldReceive('topic')
            ->with('channel_name')
            ->once()
            ->andReturn($topic);

        $adapter = new GoogleCloudPubSubAdapter($client);

        $handler1 = Mockery::mock(\stdClass::class);
        $handler1->shouldReceive('handle')
            ->with(['hello' => 'world'])
            ->once();
        $handler1->shouldReceive('handle')
            ->with('this is a string')
            ->once();

        $adapter->subscribe('channel_name', [$handler1, 'handle']);
    }

    public function testSubscribeWhenAutoTopicCreationIsDisabled()
    {
        $messageBatch1 = [
            [
                'ackId' => 1,
                'message' => [
                    'data' => base64_encode('a:1:{s:5:"hello";s:5:"world";}')
                ],
            ],
            [
                'ackId' => 2,
                'message' => [
                    'data' => base64_encode('this is a string')
                ],
            ],
        ];
        $messageBatch2 = [
            [
                'ackId' => 3,
                'message' => [
                    'data' => base64_encode('unsubscribe')
                ],
            ],
        ];

        $subscription = Mockery::mock(Subscription::class);
        $subscription->shouldNotHaveReceived('exists');
        $subscription->shouldNotHaveReceived('create');
        $subscription->shouldReceive('pull')
            ->once()
            ->andReturn($messageBatch1);
        $subscription->shouldReceive('acknowledge')
            ->with(1)
            ->once();
        $subscription->shouldReceive('acknowledge')
            ->with(2)
            ->once();
        $subscription->shouldReceive('pull')
            ->once()
            ->andReturn($messageBatch2);
        $subscription->shouldReceive('acknowledge')
            ->with(3)
            ->once();

        $topic = Mockery::mock(Topic::class);
        $topic->shouldReceive('exists')
            ->once()
            ->andReturn(true);
        $topic->shouldNotHaveReceived('create');
        $topic->shouldReceive('subscription')
            ->with('channel_name')
            ->once()
            ->andReturn($subscription);

        $client = Mockery::mock(PubSubClient::class);
        $client->shouldReceive('topic')
            ->with('channel_name')
            ->once()
            ->andReturn($topic);

        $adapter = new GoogleCloudPubSubAdapter($client, true, false);

        $handler1 = Mockery::mock(\stdClass::class);
        $handler1->shouldReceive('handle')
            ->with(['hello' => 'world'])
            ->once();
        $handler1->shouldReceive('handle')
            ->with('this is a string')
            ->once();

        $adapter->subscribe('channel_name', [$handler1, 'handle']);
    }
}
