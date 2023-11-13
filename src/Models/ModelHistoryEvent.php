<?php

declare(strict_types=1);

/**
 * Contains the ModelHistoryEvent class.
 *
 * @copyright   Copyright (c) 2023 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2023-11-13
 *
 */

namespace Konekt\History\Models;

use Illuminate\Database\Eloquent\Model;
use Konekt\History\Contracts\ModelHistoryEvent as ModelHistoryEventContract;
use Konekt\History\Diff\Diff;

class ModelHistoryEvent extends Model implements ModelHistoryEventContract
{
    protected $table = 'model_history';

    public function diff(): Diff
    {
        // TODO: Implement diff() method.
    }

    public function summary(): string
    {
        // TODO: Implement summary() method.
    }

    public function isASingleFieldChange(): bool
    {
        // TODO: Implement isASingleFieldChange() method.
    }

    public function isNoFieldChangeEntry(): bool
    {
        // TODO: Implement isNoFieldChangeEntry() method.
    }

    public function isACommentOnlyEntry(): bool
    {
        // TODO: Implement isACommentOnlyEntry() method.
    }
}
