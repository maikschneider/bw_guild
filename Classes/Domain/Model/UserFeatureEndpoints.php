<?php

namespace Blueways\BwGuild\Domain\Model;

use SourceBroker\T3api\Annotation\ApiResource;

/**
 * @ApiResource(
 *     collectionOperations={
 *          "get"={
 *              "path"="/userfeature",
 *          },
 *           "post"={
 *              "method"="POST",
 *              "path"="/userfeature",
 *          },
 *     },
 *     itemOperations={
 *          "get"={
 *              "path"="/userfeature/{id}",
 *          }
 *     },
 * )
 *
 */
class UserFeatureEndpoints extends AbstractUserFeature
{
}
