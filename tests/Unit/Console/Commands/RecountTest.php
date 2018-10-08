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

namespace Cog\Tests\Laravel\Love\Unit\Console\Commands;

use Cog\Contracts\Love\Reactable\Exceptions\ReactableInvalid;
use Cog\Laravel\Love\Reactant\ReactionCounter\Models\ReactionCounter;
use Cog\Laravel\Love\Reacter\Models\Reacter;
use Cog\Laravel\Love\ReactionType\Models\ReactionType;
use Cog\Tests\Laravel\Love\Stubs\Models\Article;
use Cog\Tests\Laravel\Love\Stubs\Models\Bot;
use Cog\Tests\Laravel\Love\Stubs\Models\Entity;
use Cog\Tests\Laravel\Love\Stubs\Models\EntityWithMorphMap;
use Cog\Tests\Laravel\Love\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RecountTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (starts_with($this->app->version(), '5.7')) {
            $this->withoutMockingConsoleOutput();
        }
    }

    /** @test */
    public function it_can_recount_all_models_likes()
    {
        $reacter1 = factory(Reacter::class)->create();
        $reacter2 = factory(Reacter::class)->create();
        $reacter3 = factory(Reacter::class)->create();
        $reacter4 = factory(Reacter::class)->create();
        $entity1 = factory(Entity::class)->create()->reactant;
        $entity2 = factory(EntityWithMorphMap::class)->create()->reactant;
        $entity3 = factory(Article::class)->create()->reactant;
        $like = factory(ReactionType::class)->create([
            'name' => 'Like',
        ]);
        $dislike = factory(ReactionType::class)->create([
            'name' => 'Dislike',
        ]);

        $reacter1->reactTo($entity1, $like);
        $reacter1->reactTo($entity2, $like);
        $reacter1->reactTo($entity3, $dislike);
        $reacter2->reactTo($entity1, $like);
        $reacter2->reactTo($entity2, $dislike);
        $reacter2->reactTo($entity3, $like);
        $reacter3->reactTo($entity1, $like);
        $reacter3->reactTo($entity2, $like);
        $reacter3->reactTo($entity3, $like);
        $reacter4->reactTo($entity3, $dislike);

        ReactionCounter::truncate();

        $status = $this->artisan('love:recount', [
            'type' => 'Like',
        ]);

        $this->assertSame(0, $status);

        $counters = ReactionCounter::query()->count();
        $this->assertSame(3, $counters);
        $this->assertSame(3, $entity1->reactionCounters()->where('reaction_type_id', $like->getKey())->first()->count);
        $this->assertSame(2, $entity2->reactionCounters()->where('reaction_type_id', $like->getKey())->first()->count);
        $this->assertSame(2, $entity3->reactionCounters()->where('reaction_type_id', $like->getKey())->first()->count);
        $this->assertNull($entity1->reactionCounters()->where('reaction_type_id', $dislike->getKey())->first());
        $this->assertNull($entity2->reactionCounters()->where('reaction_type_id', $dislike->getKey())->first());
        $this->assertNull($entity3->reactionCounters()->where('reaction_type_id', $dislike->getKey())->first());
    }

    /** @test */
    public function it_can_recount_model_likes()
    {
        $reacter1 = factory(Reacter::class)->create();
        $reacter2 = factory(Reacter::class)->create();
        $reacter3 = factory(Reacter::class)->create();
        $reacter4 = factory(Reacter::class)->create();
        $entity1 = factory(Entity::class)->create()->reactant;
        $entity2 = factory(EntityWithMorphMap::class)->create()->reactant;
        $entity3 = factory(Entity::class)->create()->reactant;
        $like = factory(ReactionType::class)->create([
            'name' => 'Like',
        ]);
        $dislike = factory(ReactionType::class)->create([
            'name' => 'Dislike',
        ]);

        $reacter1->reactTo($entity1, $like);
        $reacter1->reactTo($entity2, $like);
        $reacter1->reactTo($entity3, $dislike);
        $reacter2->reactTo($entity1, $like);
        $reacter2->reactTo($entity2, $dislike);
        $reacter2->reactTo($entity3, $like);
        $reacter3->reactTo($entity1, $like);
        $reacter3->reactTo($entity2, $like);
        $reacter3->reactTo($entity3, $like);
        $reacter4->reactTo($entity3, $dislike);

        ReactionCounter::truncate();

        $status = $this->artisan('love:recount', [
            'model' => Entity::class,
            'type' => 'Like',
        ]);

        $this->assertSame(0, $status);

        $this->assertSame(2, ReactionCounter::query()->count());
        $this->assertSame(3, $entity1->reactionCounters()->where('reaction_type_id', $like->getKey())->first()->count);
        $this->assertNull($entity2->reactionCounters()->where('reaction_type_id', $like->getKey())->first());
        $this->assertSame(2, $entity3->reactionCounters()->where('reaction_type_id', $like->getKey())->first()->count);
        $this->assertNull($entity1->reactionCounters()->where('reaction_type_id', $dislike->getKey())->first());
        $this->assertNull($entity2->reactionCounters()->where('reaction_type_id', $dislike->getKey())->first());
        $this->assertNull($entity3->reactionCounters()->where('reaction_type_id', $dislike->getKey())->first());
    }

    /** @test */
    public function it_can_recount_model_likes_using_morph_map()
    {
        $reacter1 = factory(Reacter::class)->create();
        $reacter2 = factory(Reacter::class)->create();
        $reacter3 = factory(Reacter::class)->create();
        $reacter4 = factory(Reacter::class)->create();
        $entity1 = factory(EntityWithMorphMap::class)->create()->reactant;
        $entity2 = factory(Entity::class)->create()->reactant;
        $entity3 = factory(EntityWithMorphMap::class)->create()->reactant;
        $like = factory(ReactionType::class)->create([
            'name' => 'Like',
        ]);
        $dislike = factory(ReactionType::class)->create([
            'name' => 'Dislike',
        ]);

        $reacter1->reactTo($entity1, $like);
        $reacter1->reactTo($entity2, $like);
        $reacter1->reactTo($entity3, $dislike);
        $reacter2->reactTo($entity1, $like);
        $reacter2->reactTo($entity2, $dislike);
        $reacter2->reactTo($entity3, $like);
        $reacter3->reactTo($entity1, $like);
        $reacter3->reactTo($entity2, $like);
        $reacter3->reactTo($entity3, $like);
        $reacter4->reactTo($entity3, $dislike);

        ReactionCounter::truncate();

        $status = $this->artisan('love:recount', [
            'model' => 'entity-with-morph-map',
            'type' => 'Like',
        ]);

        $this->assertSame(0, $status);

        $this->assertSame(2, ReactionCounter::query()->count());
        $this->assertSame(3, $entity1->reactionCounters()->where('reaction_type_id', $like->getKey())->first()->count);
        $this->assertNull($entity2->reactionCounters()->where('reaction_type_id', $like->getKey())->first());
        $this->assertSame(2, $entity3->reactionCounters()->where('reaction_type_id', $like->getKey())->first()->count);
        $this->assertNull($entity1->reactionCounters()->where('reaction_type_id', $dislike->getKey())->first());
        $this->assertNull($entity2->reactionCounters()->where('reaction_type_id', $dislike->getKey())->first());
        $this->assertNull($entity3->reactionCounters()->where('reaction_type_id', $dislike->getKey())->first());
    }

    /** @test */
    public function it_can_recount_model_likes_with_morph_map_using_full_class_name()
    {
        $reacter1 = factory(Reacter::class)->create();
        $reacter2 = factory(Reacter::class)->create();
        $reacter3 = factory(Reacter::class)->create();
        $reacter4 = factory(Reacter::class)->create();
        $entity1 = factory(EntityWithMorphMap::class)->create()->reactant;
        $entity2 = factory(Entity::class)->create()->reactant;
        $entity3 = factory(EntityWithMorphMap::class)->create()->reactant;
        $like = factory(ReactionType::class)->create([
            'name' => 'Like',
        ]);
        $dislike = factory(ReactionType::class)->create([
            'name' => 'Dislike',
        ]);

        $reacter1->reactTo($entity1, $like);
        $reacter1->reactTo($entity2, $like);
        $reacter1->reactTo($entity3, $dislike);
        $reacter2->reactTo($entity1, $like);
        $reacter2->reactTo($entity2, $dislike);
        $reacter2->reactTo($entity3, $like);
        $reacter3->reactTo($entity1, $like);
        $reacter3->reactTo($entity2, $like);
        $reacter3->reactTo($entity3, $like);
        $reacter4->reactTo($entity3, $dislike);

        ReactionCounter::truncate();

        $status = $this->artisan('love:recount', [
            'model' => EntityWithMorphMap::class,
            'type' => 'Like',
        ]);

        $this->assertSame(0, $status);

        $this->assertSame(2, ReactionCounter::query()->count());
        $this->assertSame(3, $entity1->reactionCounters()->where('reaction_type_id', $like->getKey())->first()->count);
        $this->assertNull($entity2->reactionCounters()->where('reaction_type_id', $like->getKey())->first());
        $this->assertSame(2, $entity3->reactionCounters()->where('reaction_type_id', $like->getKey())->first()->count);
        $this->assertNull($entity1->reactionCounters()->where('reaction_type_id', $dislike->getKey())->first());
        $this->assertNull($entity2->reactionCounters()->where('reaction_type_id', $dislike->getKey())->first());
        $this->assertNull($entity3->reactionCounters()->where('reaction_type_id', $dislike->getKey())->first());
    }

    /** @test */
    public function it_can_recount_all_models_all_like_types()
    {
        $reacter1 = factory(Reacter::class)->create();
        $reacter2 = factory(Reacter::class)->create();
        $reacter3 = factory(Reacter::class)->create();
        $reacter4 = factory(Reacter::class)->create();
        $entity1 = factory(Entity::class)->create()->reactant;
        $entity2 = factory(EntityWithMorphMap::class)->create()->reactant;
        $entity3 = factory(Article::class)->create()->reactant;
        $like = factory(ReactionType::class)->create([
            'name' => 'Like',
        ]);
        $dislike = factory(ReactionType::class)->create([
            'name' => 'Dislike',
        ]);

        $reacter1->reactTo($entity1, $like);
        $reacter1->reactTo($entity2, $like);
        $reacter1->reactTo($entity3, $dislike);
        $reacter2->reactTo($entity1, $like);
        $reacter2->reactTo($entity2, $dislike);
        $reacter2->reactTo($entity3, $like);
        $reacter3->reactTo($entity1, $like);
        $reacter3->reactTo($entity2, $like);
        $reacter3->reactTo($entity3, $like);
        $reacter4->reactTo($entity3, $dislike);

        ReactionCounter::truncate();

        $status = $this->artisan('love:recount');

        $this->assertSame(0, $status);

        $counters = ReactionCounter::query()->count();
        $this->assertSame(5, $counters);
        $this->assertSame(3, $entity1->reactionCounters()->where('reaction_type_id', $like->getKey())->first()->count);
        $this->assertSame(2, $entity2->reactionCounters()->where('reaction_type_id', $like->getKey())->first()->count);
        $this->assertSame(2, $entity3->reactionCounters()->where('reaction_type_id', $like->getKey())->first()->count);
        $this->assertNull($entity1->reactionCounters()->where('reaction_type_id', $dislike->getKey())->first());
        $this->assertSame(1, $entity2->reactionCounters()->where('reaction_type_id', $dislike->getKey())->first()->count);
        $this->assertSame(2, $entity3->reactionCounters()->where('reaction_type_id', $dislike->getKey())->first()->count);
    }

    /** @test */
    public function it_can_recount_model_all_like_types()
    {
        $reacter1 = factory(Reacter::class)->create();
        $reacter2 = factory(Reacter::class)->create();
        $reacter3 = factory(Reacter::class)->create();
        $reacter4 = factory(Reacter::class)->create();
        $entity1 = factory(Entity::class)->create()->reactant;
        $entity2 = factory(EntityWithMorphMap::class)->create()->reactant;
        $entity3 = factory(Entity::class)->create()->reactant;
        $like = factory(ReactionType::class)->create([
            'name' => 'Like',
        ]);
        $dislike = factory(ReactionType::class)->create([
            'name' => 'Dislike',
        ]);

        $reacter1->reactTo($entity1, $like);
        $reacter1->reactTo($entity2, $like);
        $reacter1->reactTo($entity3, $dislike);
        $reacter2->reactTo($entity1, $like);
        $reacter2->reactTo($entity2, $dislike);
        $reacter2->reactTo($entity3, $like);
        $reacter3->reactTo($entity1, $like);
        $reacter3->reactTo($entity2, $like);
        $reacter3->reactTo($entity3, $like);
        $reacter4->reactTo($entity3, $dislike);

        ReactionCounter::truncate();

        $status = $this->artisan('love:recount', [
            'model' => Entity::class,
        ]);

        $this->assertSame(0, $status);

        $counters = ReactionCounter::query()->count();
        $this->assertSame(3, $counters);
        $this->assertSame(3, $entity1->reactionCounters()->where('reaction_type_id', $like->getKey())->first()->count);
        $this->assertNull($entity2->reactionCounters()->where('reaction_type_id', $like->getKey())->first());
        $this->assertSame(2, $entity3->reactionCounters()->where('reaction_type_id', $like->getKey())->first()->count);
        $this->assertNull($entity1->reactionCounters()->where('reaction_type_id', $dislike->getKey())->first());
        $this->assertNull($entity2->reactionCounters()->where('reaction_type_id', $dislike->getKey())->first());
        $this->assertSame(2, $entity3->reactionCounters()->where('reaction_type_id', $dislike->getKey())->first()->count);
    }

    /** @test */
    public function it_can_recount_model_all_like_types_using_morph_map()
    {
        $reacter1 = factory(Reacter::class)->create();
        $reacter2 = factory(Reacter::class)->create();
        $reacter3 = factory(Reacter::class)->create();
        $reacter4 = factory(Reacter::class)->create();
        $entity1 = factory(EntityWithMorphMap::class)->create()->reactant;
        $entity2 = factory(Entity::class)->create()->reactant;
        $entity3 = factory(EntityWithMorphMap::class)->create()->reactant;
        $like = factory(ReactionType::class)->create([
            'name' => 'Like',
        ]);
        $dislike = factory(ReactionType::class)->create([
            'name' => 'Dislike',
        ]);

        $reacter1->reactTo($entity1, $like);
        $reacter1->reactTo($entity2, $like);
        $reacter1->reactTo($entity3, $dislike);
        $reacter2->reactTo($entity1, $like);
        $reacter2->reactTo($entity2, $dislike);
        $reacter2->reactTo($entity3, $like);
        $reacter3->reactTo($entity1, $like);
        $reacter3->reactTo($entity2, $like);
        $reacter3->reactTo($entity3, $like);
        $reacter4->reactTo($entity3, $dislike);

        ReactionCounter::truncate();

        $status = $this->artisan('love:recount', [
            'model' => 'entity-with-morph-map',
        ]);

        $this->assertSame(0, $status);

        $counters = ReactionCounter::query()->count();
        $this->assertSame(3, $counters);
        $this->assertSame(3, $entity1->reactionCounters()->where('reaction_type_id', $like->getKey())->first()->count);
        $this->assertNull($entity2->reactionCounters()->where('reaction_type_id', $like->getKey())->first());
        $this->assertSame(2, $entity3->reactionCounters()->where('reaction_type_id', $like->getKey())->first()->count);
        $this->assertNull($entity1->reactionCounters()->where('reaction_type_id', $dislike->getKey())->first());
        $this->assertNull($entity2->reactionCounters()->where('reaction_type_id', $dislike->getKey())->first());
        $this->assertSame(2, $entity3->reactionCounters()->where('reaction_type_id', $dislike->getKey())->first()->count);
    }

    /** @test */
    public function it_can_recount_model_all_like_types_with_morph_map_using_full_class_name()
    {
        $reacter1 = factory(Reacter::class)->create();
        $reacter2 = factory(Reacter::class)->create();
        $reacter3 = factory(Reacter::class)->create();
        $reacter4 = factory(Reacter::class)->create();
        $entity1 = factory(EntityWithMorphMap::class)->create()->reactant;
        $entity2 = factory(Entity::class)->create()->reactant;
        $entity3 = factory(EntityWithMorphMap::class)->create()->reactant;
        $like = factory(ReactionType::class)->create([
            'name' => 'Like',
        ]);
        $dislike = factory(ReactionType::class)->create([
            'name' => 'Dislike',
        ]);

        $reacter1->reactTo($entity1, $like);
        $reacter1->reactTo($entity2, $like);
        $reacter1->reactTo($entity3, $dislike);
        $reacter2->reactTo($entity1, $like);
        $reacter2->reactTo($entity2, $dislike);
        $reacter2->reactTo($entity3, $like);
        $reacter3->reactTo($entity1, $like);
        $reacter3->reactTo($entity2, $like);
        $reacter3->reactTo($entity3, $like);
        $reacter4->reactTo($entity3, $dislike);

        ReactionCounter::truncate();

        $status = $this->artisan('love:recount', [
            'model' => EntityWithMorphMap::class,
        ]);

        $this->assertSame(0, $status);

        $counters = ReactionCounter::query()->count();
        $this->assertSame(3, $counters);
        $this->assertSame(3, $entity1->reactionCounters()->where('reaction_type_id', $like->getKey())->first()->count);
        $this->assertNull($entity2->reactionCounters()->where('reaction_type_id', $like->getKey())->first());
        $this->assertSame(2, $entity3->reactionCounters()->where('reaction_type_id', $like->getKey())->first()->count);
        $this->assertNull($entity1->reactionCounters()->where('reaction_type_id', $dislike->getKey())->first());
        $this->assertNull($entity2->reactionCounters()->where('reaction_type_id', $dislike->getKey())->first());
        $this->assertSame(2, $entity3->reactionCounters()->where('reaction_type_id', $dislike->getKey())->first()->count);
    }

    /** @test */
    public function it_can_throw_model_invalid_exception_on_not_exist_morph_map()
    {
        $this->expectException(ReactableInvalid::class);

        $status = $this->artisan('love:recount', [
            'model' => 'not-exist-model',
        ]);

        $this->assertSame(1, $status);
    }

    /** @test */
    public function it_can_throw_model_invalid_exception_if_class_not_implemented_reactable_interface()
    {
        $this->expectException(ReactableInvalid::class);

        $status = $this->artisan('love:recount', [
            'model' => Bot::class,
        ]);

        $this->assertSame(1, $status);
    }
}