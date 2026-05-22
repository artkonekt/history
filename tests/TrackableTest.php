<?php

declare(strict_types=1);

/**
 * Contains the TrackableTest class.
 *
 * @copyright   Copyright (c) 2023 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2023-11-14
 *
 */

namespace Konekt\History\Tests;

use Konekt\History\History;
use Konekt\History\Tests\Dummies\SampleTrackableClient;
use Konekt\History\Tests\Dummies\SampleTrackableProduct;
use Konekt\History\Tests\Dummies\SampleTrackableWithDefaultRedact;
use PHPUnit\Framework\Attributes\Test;

class TrackableTest extends TestCase
{
    #[Test]
    public function list_of_fields_to_include_can_be_specified()
    {
        $product = SampleTrackableProduct::create(['name' => 'Something', 'price' => 100, 'is_active' => true, 'category' => 'Toulouse']);

        $event = History::begin($product);

        $this->assertEquals(3, $event->diff()->changeCount());
        $this->assertArrayNotHasKey('category', $event->diff()->changes());

        $product->update(['is_active' => false, 'category' => 'Latrec']);
        $event = History::logRecentUpdate($product);

        $this->assertEquals(1, $event->diff()->changeCount());
        $this->assertArrayNotHasKey('category', $event->diff()->changes());
    }

    #[Test]
    public function trackable_classes_can_customize_the_summary()
    {
        $product = SampleTrackableProduct::create(['name' => 'Something', 'price' => 19, 'is_active' => true, 'category' => 'Toulouse']);

        History::begin($product);

        $product->update(['name' => 'Good thing']);
        $event = History::logRecentUpdate($product);
        $this->assertEquals('Renamed to: Good thing', $event->summary());

        $product->update(['price' => 25]);
        $event = History::logRecentUpdate($product);
        $this->assertEquals('Price changed to 25', $event->summary());

        $product->update(['is_active' => false]);
        $event = History::logRecentUpdate($product);
        $this->assertEquals('Deactivated', $event->summary());

        $product->update(['is_active' => true]);
        $event = History::logRecentUpdate($product);
        $this->assertEquals('Activated', $event->summary());
    }

    #[Test] public function list_of_fields_to_exclude_can_be_specified()
    {
        $product = SampleTrackableClient::create(['name' => 'Copy Paste Ltd', 'country' => 'CA', 'api_key' => 'secret']);

        $event = History::begin($product);

        $this->assertEquals(2, $event->diff()->changeCount());
        $this->assertArrayNotHasKey('api_key', $event->diff()->changes());
    }

    #[Test] public function it_does_not_redact_field_values_if_redact_in_history_returns_false()
    {
        $product = SampleTrackableWithDefaultRedact::create(['name' => 'Copy Paste Ltd', 'country' => 'CA', 'api_key' => 'not so secret']);

        $event = History::begin($product);

        $this->assertEquals('CA', $event->diff()->changeOf('country')->new);
    }

    #[Test] public function it_redacts_with_the_default_redactor_if_redact_in_history_returns_true()
    {
        $product = SampleTrackableWithDefaultRedact::create(['name' => 'Copy Paste Ltd', 'country' => 'CA', 'api_key' => 'not so secret']);

        $event = History::begin($product);

        $this->assertEquals('******', $event->diff()->changeOf('api_key')->new);
    }

    #[Test] public function it_redacts_with_the_closure_if_redact_in_history_returns_a_closure()
    {
        $product = SampleTrackableWithDefaultRedact::create(['name' => 'Copy Paste Ltd', 'country' => 'CA', 'api_key' => 'not so secret']);

        $event = History::begin($product);

        $this->assertEquals('REDACTED', $event->diff()->changeOf('name')->new);

        $product->update(['name' => '42']);

        $event2 = History::logRecentUpdate($product);

        $this->assertEquals('The meaning of life', $event2->diff()->changeOf('name')->new);
    }
}
