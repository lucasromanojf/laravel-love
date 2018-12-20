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

namespace Cog\Tests\Laravel\Love\Unit\Reacter\Models;

use Cog\Contracts\Love\Reacter\Exceptions\ReacterInvalid;
use Cog\Laravel\Love\Reactant\Models\NullReactant;
use Cog\Laravel\Love\Reactant\Models\Reactant;
use Cog\Laravel\Love\Reacter\Models\NullReacter;
use Cog\Laravel\Love\ReactionType\Models\ReactionType;
use Cog\Tests\Laravel\Love\Stubs\Models\Article;
use Cog\Tests\Laravel\Love\Stubs\Models\Bot;
use Cog\Tests\Laravel\Love\Stubs\Models\User;
use Cog\Tests\Laravel\Love\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

final class NullReacterTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_throws_exception_on_get_reacterable()
    {
        $this->expectException(ReacterInvalid::class);

        $reacter = new NullReacter(new User());

        $reacter->getId();
    }

    /** @test */
    public function it_can_get_reacterable()
    {
        $reacterable = new User();
        $reacter = new NullReacter($reacterable);

        $assertReacterable = $reacter->getReacterable();

        $this->assertSame($reacterable, $assertReacterable);
    }

    /** @test */
    public function it_can_get_reactions(): void
    {
        $reacter = new NullReacter(new User());

        $reactions = $reacter->getReactions();

        $this->assertCount(0, $reactions);
        $this->assertInternalType('iterable', $reactions);
    }

    /** @test */
    public function it_throws_reactant_invalid_on_react_to(): void
    {
        $this->expectException(ReacterInvalid::class);

        $reactionType = factory(ReactionType::class)->create();
        $reacter = new NullReacter(new Bot());
        $reactable = factory(Article::class)->create();
        $reactant = $reactable->reactant;

        $reacter->reactTo($reactant, $reactionType);
    }

    /** @test */
    public function it_throws_reactant_invalid_on_react_to_when_reactant_is_null_object(): void
    {
        $this->expectException(ReacterInvalid::class);

        $reactionType = factory(ReactionType::class)->create();
        $reacter = new NullReacter(new Bot());
        $reactant = new NullReactant(new Article());

        $reacter->reactTo($reactant, $reactionType);
    }

    /** @test */
    public function it_throws_reactant_invalid_on_unreact_to(): void
    {
        $this->expectException(ReacterInvalid::class);

        $reactionType = factory(ReactionType::class)->create();
        $reacter = new NullReacter(new Bot());
        $reactable = factory(Article::class)->create();
        $reactant = $reactable->reactant;

        $reacter->unreactTo($reactant, $reactionType);
    }

    /** @test */
    public function it_throws_reactant_invalid_on_unreact_to_when_reactant_is_null_object(): void
    {
        $this->expectException(ReacterInvalid::class);

        $reactionType = factory(ReactionType::class)->create();
        $reacter = new NullReacter(new Bot());
        $reactant = new NullReactant(new Article());

        $reacter->unreactTo($reactant, $reactionType);
    }

    /** @test */
    public function it_can_determine_is_reacted_to(): void
    {
        $reacterable = new User();
        $reacter = new NullReacter($reacterable);
        $reactant = factory(Reactant::class)->make();

        $isReacted = $reacter->isReactedTo($reactant);

        $this->assertFalse($isReacted);
    }

    /** @test */
    public function it_can_determine_is_not_reacted_to(): void
    {
        $reacterable = new User();
        $reacter = new NullReacter($reacterable);
        $reactant = factory(Reactant::class)->make();

        $isReacted = $reacter->isNotReactedTo($reactant);

        $this->assertTrue($isReacted);
    }

    /** @test */
    public function it_can_determine_is_reacted_to_with_type(): void
    {
        $reacterable = new User();
        $reacter = new NullReacter($reacterable);
        $reactant = factory(Reactant::class)->make();
        $reactionType = new ReactionType();

        $isReacted = $reacter->isReactedToWithType($reactant, $reactionType);

        $this->assertFalse($isReacted);
    }

    /** @test */
    public function it_can_determine_is_not_reacted_to_with_type(): void
    {
        $reacterable = new User();
        $reacter = new NullReacter($reacterable);
        $reactant = factory(Reactant::class)->make();
        $reactionType = new ReactionType();

        $isReacted = $reacter->isNotReactedToWithType($reactant, $reactionType);

        $this->assertTrue($isReacted);
    }
}
