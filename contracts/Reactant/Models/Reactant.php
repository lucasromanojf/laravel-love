<?php

/*
 * This file is part of Laravel Love.
 *
 * (c) Anton Komarev <a.komarev@cybercog.su>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Cog\Contracts\Love\Reactant\Models;

use Cog\Contracts\Love\Reactable\Models\Reactable;
use Cog\Contracts\Love\Reactant\ReactionCounter\Models\ReactionCounter;
use Cog\Contracts\Love\Reactant\ReactionTotal\Models\ReactionTotal;
use Cog\Contracts\Love\Reacter\Models\Reacter;
use Cog\Contracts\Love\ReactionType\Models\ReactionType;

interface Reactant
{
    public function getReactable(): Reactable;

    /**
     * @return iterable|\Cog\Contracts\Love\Reaction\Models\Reaction[]
     */
    public function getReactions(): iterable;

    /**
     * @return iterable|\Cog\Contracts\Love\Reactant\ReactionCounter\Models\ReactionCounter[]
     */
    public function getReactionCounters(): iterable;

    public function getReactionCounterOfType(ReactionType $reactionType): ReactionCounter;

    public function getReactionTotal(): ReactionTotal;

    public function isReactedBy(Reacter $reacter): bool;

    public function isNotReactedBy(Reacter $reacter): bool;

    public function isReactedByWithType(Reacter $reacter, ReactionType $reactionType): bool;

    public function isNotReactedByWithType(Reacter $reacter, ReactionType $reactionType): bool;

    public function isNull(): bool;
}
