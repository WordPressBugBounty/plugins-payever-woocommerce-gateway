<?php

/**
 * PHP version 5.4 and 8
 *
 * @category  RequestEntity
 * @package   Payever\Payments
 * @author    payever GmbH <service@payever.de>
 * @author    Hennadii Shymanskyi <gendosua@gmail.com>
 * @copyright 2017-2023 payever GmbH
 * @license   MIT <https://opensource.org/licenses/MIT>
 * @link      https://docs.payever.org/shopsystems/api/getting-started
 */

namespace Payever\Sdk\Payments\Http\RequestEntity;

use Payever\Sdk\Core\Http\RequestEntity;
use Payever\Sdk\Payments\Notification\MessageEntity\NotificationResultEntity;
use Payever\Sdk\Payments\Notification\MessageEntity\NotificationActionResultEntity;

/**
 * @method string getNotificationType()
 * @method array getNotificationTypesAvailable()
 * @method setNotificationType(string $notificationType)
 * @method setNotificationTypesAvailable(array $notificationTypes)
 * @method \DateTime|false getCreatedAt()
 * @method NotificationResultEntity getPayment()
 * @method null|NotificationActionResultEntity getAction()
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class NotificationRequestEntity extends RequestEntity
{
    /** @var string */
    protected $notificationType;

    /** @var array */
    protected $notificationTypesAvailable;

    /** @var \DateTime|bool */
    protected $createdAt;

    /** @var NotificationResultEntity */
    protected $payment;

    /** @var null|NotificationActionResultEntity */
    protected $action;

    /**
     * @param string $createdAt
     * @return static
     */
    public function setCreatedAt($createdAt)
    {
        if ($createdAt) {
            $this->createdAt = date_create($createdAt);
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        $types = $this->getNotificationTypesAvailable();

        return is_array($types)
            && in_array($this->getNotificationType(), $types)
        ;
    }

    /**
     * @param array $data
     *
     * @return static
     */
    public function setData(array $data)
    {
        $this->payment = new NotificationResultEntity($data['payment']);

        if (array_key_exists('action', $data)) {
            $this->action = new NotificationActionResultEntity($data['action']);
        }

        return $this;
    }
}
