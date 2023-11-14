<?php

declare(strict_types=1);

/**
 * Contains the DefaultSceneResolver class.
 *
 * @copyright   Copyright (c) 2023 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2023-11-14
 *
 */

namespace Konekt\History\Scenes;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Request;
use Konekt\History\Contracts\SceneResolver;
use Konekt\History\Contracts\Via;
use Konekt\History\Models\Via as ViaEnum;

class DefaultSceneResolver implements SceneResolver
{
    public function via(): ?Via
    {
        return $this->get()[0];
    }

    public function scene(): ?string
    {
        return $this->get()[1];
    }

    public function get(): array
    {
        if (App::runningInConsole()) {
            /** @var null|JobInfo $jobInfo */
            $jobInfo = App::get(JobInfo::SERVICE_NAME);
            if (null === $jobInfo) {
                $via = ViaEnum::CLI;
                $scene = $_SERVER['argv'][1] ?? $_SERVER['argv'][0] ?? null;
                if (is_string($scene) && str_starts_with($scene, '-')) {
                    $scene = $_SERVER['argv'][0] ?? null;
                }
            } else {
                $via = ViaEnum::QUEUE;
                $scene = $jobInfo->job;
            }
        } else {
            $via = ViaEnum::WEB;
            $scene = Request::url();
        }

        return [$via, $scene];
    }
}
