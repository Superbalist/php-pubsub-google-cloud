<?php

namespace Superbalist\PubSub\GoogleCloud;

use Google\Cloud\PubSub\PubSubClient;
use Superbalist\PubSub\PubSubAdapterInterface;
use Superbalist\PubSub\Utils;

class GoogleCloudPubSubAdapter implements PubSubAdapterInterface
{
    /**
     * @var PubSubClient
     */
    protected $client;

    /**
     * @param PubSubClient $client
     */
    public function __construct(PubSubClient $client)
    {
        $this->client = $client;
    }

    /**
     * Return the Google PubSubClient.
     *
     * @return PubSubClient
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Subscribe a handler to a channel.
     *
     * @param string $channel
     * @param callable $handler
     */
    public function subscribe($channel, callable $handler)
    {
        $subscription = $this->getSubscriptionForChannel($channel);

        $isSubscriptionLoopActive = true;

        while ($isSubscriptionLoopActive) {
            $ackIds = [];
            $payloads = [];

            $messages = $subscription->pull();

            foreach ($messages as $message) {
                $ackIds[] = $message['ackId'];

                // the cloud library base64 encodes messages
                $payload = base64_decode($message['message']['data']);
                $payload = Utils::unserializeMessagePayload($payload);

                if ($payload === 'unsubscribe') {
                    $isSubscriptionLoopActive = false;
                } else {
                    $payloads[] = $payload;
                }
            }

            if (!empty($ackIds)) {
                $subscription->acknowledgeBatch($ackIds);
            }

            foreach ($payloads as $payload) {
                call_user_func($handler, $payload);
            }
        }
    }

    /**
     * Publish a message to a channel.
     *
     * @param string $channel
     * @param mixed $message
     */
    public function publish($channel, $message)
    {
        $topic = $this->getTopicForChannel($channel);
        $topic->publish(['data' => Utils::serializeMessage($message)]);
    }

    /**
     * Return a `Topic` instance from a channel name.
     *
     * If the topic doesn't exist, the topic is first created.
     *
     * @param string $channel
     * @return \Google\Cloud\PubSub\Topic
     */
    protected function getTopicForChannel($channel)
    {
        $topic = $this->client->topic($channel);
        if (!$topic->exists()) {
            $topic->create();
        }
        return $topic;
    }

    /**
     * Return a `Subscription` instance from a channel name.
     *
     * If the subscription doesn't exist, the subscription is first created.
     *
     * @param string $channel
     * @return \Google\Cloud\PubSub\Subscription
     */
    protected function getSubscriptionForChannel($channel)
    {
        $topic = $this->getTopicForChannel($channel);
        $subscription = $topic->subscription($channel);
        if (!$subscription->exists()) {
            $subscription->create();
        }
        return $subscription;
    }
}
