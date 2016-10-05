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
     * @var string
     */
    protected $clientIdentifier;

    /**
     * @var bool
     */
    protected $autoCreateTopics;

    /**
     * @var bool
     */
    protected $autoCreateSubscriptions;

    /**
     * @param PubSubClient $client
     * @param string $clientIdentifier
     * @param bool $autoCreateTopics
     * @param bool $autoCreateSubscriptions
     */
    public function __construct(PubSubClient $client, $clientIdentifier = null, $autoCreateTopics = true, $autoCreateSubscriptions = true)
    {
        $this->client = $client;
        $this->clientIdentifier = $clientIdentifier;
        $this->autoCreateTopics = $autoCreateTopics;
        $this->autoCreateSubscriptions = $autoCreateSubscriptions;
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
     * Set the unique client identifier.
     *
     * The client identifier is used when creating a subscription to a topic.
     *
     * A topic can have multiple subscribers connected.
     * If all subscribers use the same client identifier, the messages will load-balance across them.
     * If all subscribers have different client identifiers, the messages will be dispatched all of them.
     *
     * @param string $clientIdentifier
     */
    public function setClientIdentifier($clientIdentifier)
    {
        $this->clientIdentifier = $clientIdentifier;
    }

    /**
     * Return the unique client identifier.
     *
     * @return string
     */
    public function getClientIdentifier()
    {
        return $this->clientIdentifier;
    }

    /**
     * Set whether or not topics will be auto created.
     *
     * @param bool $autoCreateTopics
     */
    public function setAutoCreateTopics($autoCreateTopics)
    {
        $this->autoCreateTopics = $autoCreateTopics;
    }

    /**
     * Check whether or not topics will be auto created.
     *
     * @return bool
     */
    public function areTopicsAutoCreated()
    {
        return $this->autoCreateTopics;
    }

    /**
     * Set whether or not subscriptions will be auto created.
     *
     * @param bool $autoCreateSubscriptions
     */
    public function setAutoCreateSubscriptions($autoCreateSubscriptions)
    {
        $this->autoCreateSubscriptions = $autoCreateSubscriptions;
    }

    /**
     * Check whether or not subscriptions will be auto created.
     *
     * @return bool
     */
    public function areSubscriptionsAutoCreated()
    {
        return $this->autoCreateSubscriptions;
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
            $messages = $subscription->pull();

            foreach ($messages as $message) {
                // the cloud library base64 encodes messages
                $payload = base64_decode($message['message']['data']);
                $payload = Utils::unserializeMessagePayload($payload);

                if ($payload === 'unsubscribe') {
                    $isSubscriptionLoopActive = false;
                } else {
                    call_user_func($handler, $payload);
                }

                $subscription->acknowledge($message['ackId']);
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
        if ($this->autoCreateTopics && !$topic->exists()) {
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
        $clientIdentifier = $this->clientIdentifier ? $this->clientIdentifier : 'default';
        $subscription = $topic->subscription($clientIdentifier);
        if ($this->autoCreateSubscriptions && !$subscription->exists()) {
            $subscription->create();
        }
        return $subscription;
    }
}
