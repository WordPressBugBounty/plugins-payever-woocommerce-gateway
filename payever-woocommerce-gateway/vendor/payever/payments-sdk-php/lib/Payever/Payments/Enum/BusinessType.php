<?php

/**
 * PHP version 5.4 and 8
 *
 * @category  Enum
 * @package   Payever\Payments
 * @author    payever GmbH <service@payever.de>
 * @copyright 2017-2023 payever GmbH
 * @license   MIT <https://opensource.org/licenses/MIT>
 * @link      https://docs.payever.org/shopsystems/api/getting-started
 */

namespace Payever\Sdk\Payments\Enum;

use Payever\Sdk\Core\Base\EnumerableConstants;

/**
 * List of business types
 */
class BusinessType extends EnumerableConstants
{
    const B2C = 'b2c';
    const B2B = 'b2b';
    const MIXED = 'mixed';
}
