<?php

declare(strict_types=1);

/**
 * Contains the DiffTest class.
 *
 * @copyright   Copyright (c) 2023 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2023-11-13
 *
 */

namespace Konekt\History\Tests;

use Konekt\History\Diff\Change;
use Konekt\History\Diff\Diff;
use Konekt\History\Diff\Undefined;
use PHPUnit\Framework\TestCase as PhpUnitTestCase;

class DiffTest extends PhpUnitTestCase
{
    /** @test */
    public function it_can_be_instantiated_from_an_array()
    {
        $diff = new Diff([
            'status' => [
                'o' => 'todo',
                'n' => 'in-progress'
            ],
            'user_id' => [
                'o' => null,
                'n' => 1
            ],
        ]);

        $this->assertEquals(2, $diff->changeCount());
    }

    /** @test */
    public function it_can_tell_if_a_field_has_changed()
    {
        $diff = new Diff([
            'status' => [
                'o' => 'todo',
                'n' => 'in-progress'
            ],
            'user_id' => [
                'o' => null,
                'n' => 1
            ],
        ]);

        $this->assertTrue($diff->hasChanged('status'));
        $this->assertTrue($diff->hasChanged('user_id'));
        $this->assertFalse($diff->hasChanged('created_at'));
    }

    /** @test */
    public function it_can_return_the_old_and_new_values_of_fields()
    {
        $diff = new Diff([
            'status' => [
                'o' => 'confirmed',
                'n' => 'completed'
            ],
            'shipment_id' => [
                'o' => null,
                'n' => 3
            ],
        ]);

        $this->assertEquals('confirmed', $diff->old('status'));
        $this->assertEquals('completed', $diff->new('status'));
        $this->assertNull($diff->old('shipment_id'));
        $this->assertEquals(3, $diff->new('shipment_id'));
    }

    /** @test */
    public function change_of_and_old_and_new_values_return_null_for_unchanged_fields()
    {
        $diff = new Diff([]);

        $this->assertTrue($diff->isUnchanged('monkeys'));
        $this->assertNull($diff->old('monkeys'));
        $this->assertNull($diff->new('monkeys'));
        $this->assertNull($diff->changeOf('monkeys'));
    }

    /** @test */
    public function a_simple_change_of_a_field_can_be_retrieved()
    {
        $diff = new Diff([
            'note' => [
                'o' => null,
                'n' => 'Send it to my mom'
            ],
        ]);

        $change = $diff->changeOf('note');
        $this->assertInstanceOf(Change::class, $change);

        $this->assertNull($change->old);
        $this->assertEquals('Send it to my mom', $change->new);

        $this->assertNull($diff->old('note'));
        $this->assertEquals('Send it to my mom', $diff->new('note'));
    }

    /** @test */
    public function it_can_handle_cases_when_the_old_value_is_undefined()
    {
        $diff = new Diff(['field' => ['n' => 'Something']]);

        $this->assertInstanceOf(Undefined::class, $diff->old('field'));
    }

    /** @test */
    public function undefined_values_are_not_present_when_converting_to_array()
    {
        $diff = new Diff(['field' => ['n' => 'Something']]);

        $this->assertArrayNotHasKey('o', $diff->toArray()['field']);
    }

    /** @test */
    public function it_can_be_instantiated_from_before_and_after_attribute_sets()
    {
        $diff = Diff::fromAttributeSets(
            before: ['status' => 'to-do', 'name' => 'Joe'],
            after: ['status' => 'in-progress', 'name' => 'Joe'],
        );

        $this->assertCount(1, $diff->changes());
        $this->assertEquals('to-do', $diff->old('status'));
        $this->assertEquals('in-progress', $diff->new('status'));
    }

    /** @test */
    public function it_can_export_the_changes_to_an_array()
    {
        $raw = [
            'status' => [
                'o' => 'confirmed',
                'n' => 'completed'
            ],
            'shipment_id' => [
                'o' => null,
                'n' => 3
            ],
        ];

        $diff = new Diff($raw);

        $this->assertEquals($raw, $diff->toArray());
    }
}
