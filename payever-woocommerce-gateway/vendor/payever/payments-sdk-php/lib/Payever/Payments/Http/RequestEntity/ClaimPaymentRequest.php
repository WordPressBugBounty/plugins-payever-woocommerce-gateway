<?php

/**
 * PHP version 5.4 and 8
 *
 * @category  RequestEntity
 * @package   Payever\Payments
 * @author    payever GmbH <service@payever.de>
 * @copyright 2017-2023 payever GmbH
 * @license   MIT <https://opensource.org/licenses/MIT>
 * @link      https://docs.payever.org/shopsystems/api/getting-started
 */

namespace Payever\Sdk\Payments\Http\RequestEntity;

use Payever\Sdk\Core\Http\RequestEntity;

/**
 * This class represents Claim Payment RequestInterface Entity
 *
 * @method bool   getIsNonInclusive()
 * @method bool   getIsLegal()
 * @method bool   getIsDisputed()
 * @method bool   getIsGuaranteeExisting()
 * @method int    getPolicyId()
 * @method string getBusinessUnitCode()
 * @method string getExtensionId()
 * @method self   setIsNonInclusive(bool $isNonInclusive)
 * @method self   setIsLegal(bool $isLegal)
 * @method self   setIsDisputed(bool $isDisputed)
 * @method self   setIsGuaranteeExisting(bool $isGuaranteeExisting)
 * @method self   setPolicyId(string $policyId)
 * @method self   setBusinessUnitCode(string $businessUnitCode)
 * @method self   setExtensionId(string $extensionId)
 */
class ClaimPaymentRequest extends RequestEntity
{
    /** @var bool $isNonInclusive */
    protected $isNonInclusive = false;

    /** @var bool $isLegal */
    protected $isLegal = false;

    /** @var bool $isDisputed */
    protected $isDisputed = false;

    /** @var bool $isGuaranteeExisting */
    protected $isGuaranteeExisting = false;

    /** @var string $policyId */
    protected $policyId;

    /** @var string $businessUnitCode */
    protected $businessUnitCode;

    /** @var string $extensionId */
    protected $extensionId;

    /**
     * {@inheritdoc}
     */
    public function isValid()
    {
        return parent::isValid()
            && is_bool($this->isNonInclusive)
            && is_bool($this->isLegal)
            && is_bool($this->isDisputed)
            && is_bool($this->isGuaranteeExisting)
            && (!$this->policyId || is_string($this->policyId))
            && (!$this->businessUnitCode || is_string($this->businessUnitCode))
            && (!$this->extensionId || is_string($this->extensionId));
    }

    /**
     * {@inheritdoc}
     */
    public function toArray($object = null)
    {
        return $object ? get_object_vars($object) : get_object_vars($this);
    }
}
