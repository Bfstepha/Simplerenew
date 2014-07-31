<?php
/**
 * @package   Simplerenew
 * @contact   www.ostraining.com, support@ostraining.com
 * @copyright 2014 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace Simplerenew\Api;

use Simplerenew\Gateway\AccountInterface;
use Simplerenew\Gateway\BillingInterface;
use Simplerenew\Gateway\NotificationInterface;
use Simplerenew\Gateway\SubscriptionInterface;
use Simplerenew\User\Adapter\UserInterface;

defined('_JEXEC') or die();

class Notification extends AbstractApiBase
{
    // Notification types
    const TYPE_ACCOUNT      = 'account';
    const TYPE_BILLING      = 'billing';
    const TYPE_INVOICE      = 'invoice';
    const TYPE_SUBSCRIPTION = 'subscription';
    const TYPE_PAYMENT      = 'payment';
    const TYPE_UNKNOWN      = 'unknown';

    // Notification actions
    const ACTION_NEW        = 'new';
    const ACTION_CANCEL     = 'cancel';
    const ACTION_UPDATE     = 'update';
    const ACTION_REACTIVATE = 'reactivate';
    const ACTION_CLOSE      = 'close';
    const ACTION_PAST_DUE   = 'past_due';
    const ACTION_EXPIRE     = 'expire';
    const ACTION_RENEW      = 'renew';
    const ACTION_SUCCESS    = 'success';
    const ACTION_FAIL       = 'fail';
    const ACTION_REFUND     = 'refund';
    const ACTION_VOID       = 'void';
    const ACTION_UNKNOWN    = 'unknown';

    /**
     * @var string
     */
    public $type = null;

    /**
     * @var string
     */
    public $action = null;

    /**
     * @var object
     */
    public $account = null;

    /**
     * @var object
     */
    public $billing = null;

    /**
     * @var object
     */
    public $subscription = null;

    /**
     * @var NotificationInterface
     */
    protected $imp = null;

    public function __construct(NotificationInterface $imp, array $config = array())
    {
        $this->imp = $imp;
    }

    public function loadPackage($package)
    {
        $this->imp->loadPackage($this, $package);
    }
}
