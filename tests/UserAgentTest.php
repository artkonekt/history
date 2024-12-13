<?php

declare(strict_types=1);

/**
 * Contains the UserAgentTest class.
 *
 * @copyright   Copyright (c) 2024 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2024-12-13
 *
 */

namespace Konekt\History\Tests;

use Illuminate\Http\Request;
use Konekt\History\History;
use Konekt\History\Tests\Dummies\SampleTask;

class UserAgentTest extends TestCase
{
    private const STUPID_UA_STRING = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_6_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/21G93 [FBAN/FBIOS;FBAV/492.0.0.101.111;FBBV/670308045;FBDV/iPhone14,5;FBMD/iPhone;FBSN/iOS;FBSV/17.6.1;FBSS/3;FBID/phone;FBLC/de_DE;FBOP/5;FBRV/673456666]';

    /** @test */
    public function it_truncates_user_agent_strings_longer_than_255_characters_without_errors()
    {
        $request = Request::create('/example-route', 'GET', [], [], [], [
            'HTTP_USER_AGENT' => self::STUPID_UA_STRING,
        ]);
        app()->forgetInstance('request');
        app()->singleton('request', fn () => $request);

        $task = SampleTask::create(['title' => 'Task With a stupid Browser Agent String', 'status' => 'in-progress']);
        $entry = History::begin($task);
        $entry = $entry->fresh();

        $this->assertEquals(substr(self::STUPID_UA_STRING, 0, 255), $entry->user_agent);
    }
}
