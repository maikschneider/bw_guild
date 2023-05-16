<?php

namespace Blueways\BwGuild\Domain\Model;

use SourceBroker\T3api\Annotation\ApiResource;

/**
 * @ApiResource(
 *     collectionOperations={
 *          "get"={
 *              "path"="/offer",
 *          },
 *           "post"={
 *              "method"="POST",
 *              "path"="/offer",
 *          },
 *     },
 *     itemOperations={
 *          "get"={
 *              "path"="/offer/{id}",
 *          }
 *     },
 * )
 *
 */
class OfferEndpoint extends Offer
{
}
