<?php

namespace CedricZiel\Symfony\Messenger\Bridge\GcpPubSub\Transport;

use Symfony\Component\Messenger\Stamp\NonSendableStampInterface;

/**
 * This stamp should be applied to indicate a web-push subscription.
 */
class PubSubPushStamp implements NonSendableStampInterface
{

}
