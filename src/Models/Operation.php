<?php

declare(strict_types=1);

/**
 * Contains the Operation class.
 *
 * @copyright   Copyright (c) 2023 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2023-11-14
 *
 */

namespace Konekt\History\Models;

use Konekt\Enum\Enum;
use Konekt\History\Contracts\Operation as OperationContract;

/**
 * @method static self UNDEFINED()
 * @method static self CREATE()
 * @method static self UPDATE()
 * @method static self DELETE()
 * @method static self RETRIEVE()
 * @method static self COMMENT()
 * @method static self ACTION()
 *
 * @property-read bool $is_undefined
 * @property-read bool $is_create
 * @property-read bool $is_update
 * @property-read bool $is_delete
 * @property-read bool $is_retrieve
 * @property-read bool $is_comment
 * @property-read bool $is_action
 */
class Operation extends Enum implements OperationContract
{
    public const __DEFAULT = self::UNDEFINED;
    public const UNDEFINED = null;
    public const CREATE = 'create';
    public const UPDATE = 'update';
    public const DELETE = 'delete';
    public const RETRIEVE = 'retrieve';
    public const COMMENT = 'comment';

    public const ACTION = 'action';
}
