<?php

declare(strict_types=1);

/**
 * Contains the SampleTask class.
 *
 * @copyright   Copyright (c) 2023 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2023-11-13
 *
 */

namespace Konekt\History\Tests\Dummies;

use Illuminate\Database\Eloquent\Model;

class SampleTask extends Model
{
    protected $guarded = ['id'];
}
