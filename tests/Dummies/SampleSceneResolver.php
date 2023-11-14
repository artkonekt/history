<?php

declare(strict_types=1);

/**
 * Contains the SampleSceneResolver class.
 *
 * @copyright   Copyright (c) 2023 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2023-11-14
 *
 */

namespace Konekt\History\Tests\Dummies;

use Konekt\History\Contracts\SceneResolver;
use Konekt\History\Contracts\Via;

class SampleSceneResolver implements SceneResolver
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
        return [\Konekt\History\Models\Via::QUEUE, 'Meh meh'];
    }
}
