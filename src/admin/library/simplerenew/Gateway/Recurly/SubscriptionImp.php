<?php
/**
 * @package   Simplerenew
 * @contact   www.ostraining.com, support@ostraining.com
 * @copyright 2014 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace Simplerenew\Gateway\Recurly;

use Simplerenew\Api\Account;
use Simplerenew\Api\Coupon;
use Simplerenew\Api\Plan;
use Simplerenew\Api\Subscription;
use Simplerenew\Exception;
use Simplerenew\Exception\NotFound;
use Simplerenew\Gateway\SubscriptionInterface;
use Simplerenew\Object;

defined('_JEXEC') or die();

class SubscriptionImp extends AbstractRecurlyBase implements SubscriptionInterface
{
    protected $fieldMap = array(
        'id'           => 'uuid',
        'status'       => array(
            'state' => array(
                'active'              => Subscription::STATUS_ACTIVE,
                'canceled'            => Subscription::STATUS_CANCELED,
                'expired'             => Subscription::STATUS_EXPIRED,
                Object::MAP_UNDEFINED => Subscription::STATUS_UNKNOWN
            )
        ),
        'enrolled'     => 'activated_at',
        'canceled'     => 'canceled_at',
        'expires'      => 'expires_at',
        'period_start' => 'current_period_started_at',
        'period_end'   => 'current_period_ends_at',
        'trial_start'  => 'trial_started_at',
        'trial_end'    => 'trial_ends_at'
    );

    /**
     * Create a new subscription for the selected account in the selected plan
     *
     * @param Subscription $parent
     * @param Account      $account
     * @param Plan         $plan
     * @param Coupon       $coupon
     *
     * @return void
     * @throws Exception
     */
    public function create(Subscription $parent, Account $account, Plan $plan, Coupon $coupon = null)
    {
        try {
            $subscription = new \Recurly_Subscription(null, $this->client);

            $subscription->account     = \Recurly_Account::get($account->code, $this->client);
            $subscription->plan_code   = $plan->code;
            $subscription->coupon_code = $coupon ? $coupon->code : null;
            $subscription->currency    = $plan->currency;

            $subscription->create();
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode(), $e);
        }

        $this->bindSource($parent, $subscription);
    }

    /**
     * @param Subscription $parent
     *
     * @return void
     * @throws Exception
     */
    public function load(Subscription $parent)
    {
        $subscription = $this->getSubscription($parent->id);
        $this->bindSource($parent, $subscription);
    }

    /**
     * Map raw data from the Gateway to SR fields
     *
     * @param Subscription $parent
     * @param mixed        $data
     *
     * @return void
     */
    public function bindSource(Subscription $parent, $data)
    {
        // Find account code
        $account = $this->getKeyValue($data, 'account');

        if ($account instanceof \Recurly_Stub) {
            \Recurly_Client::$apiKey = $this->client->apiKey();
            $rawAccount              = $data->account->get();
            $accountCode             = $this->getKeyValue($rawAccount, 'account_code');
        } else {
            $accountCode = $this->getKeyValue($account, 'account_code');
        }

        // Look for a pending plan
        $pendingPlan = array(
            'plan'   => null,
            'amount' => null
        );

        $pending = $this->getKeyValue($data, 'pending_subscription');
        if ($pending) {
            $plan        = $this->getKeyValue($pending, 'plan');
            $pendingPlan = array(
                'plan'   => $this->getKeyValue($plan, 'plan_code'),
                'amount' => $this->getKeyValue($pending, 'unit_amount_in_cents') / 100
            );
        }

        $plan = $this->getKeyValue($data, 'plan');
        $parent
            ->clearProperties()
            ->setProperties($data, $this->fieldMap)
            ->setProperties(
                array(
                    'plan'           => $this->getKeyValue($plan, 'plan_code'),
                    'amount'         => $this->getKeyValue($data, 'unit_amount_in_cents') / 100,
                    'pending_plan'   => $pendingPlan['plan'],
                    'pending_amount' => $pendingPlan['amount'],
                    'account_code'   => $accountCode
                )
            );
    }

    /**
     * @param string $id
     *
     * @return \Recurly_Subscription
     * @throws Exception
     */
    protected function getSubscription($id)
    {
        if (!$id) {
            throw new Exception('No subscription selected');
        }

        try {
            $subscription          = \Recurly_Subscription::get($id, $this->client);

        } catch (\Recurly_NotFoundError $e) {
            throw new NotFound($e->getMessage(), $e->getCode(), $e);

        } catch (\Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode(), $e);
        }

        return $subscription;
    }

    /**
     * @param Subscription $template
     * @param Account      $account
     * @param int          $bitMask Subscription status codes to retrieve
     *
     * @return array
     */
    public function getList(Subscription $template, Account $account, $bitMask = null)
    {
        /**
         * @var \Recurly_Subscription $rawSubscription
         */
        $subscriptions = array();

        $list = \Recurly_SubscriptionList::getForAccount($account->code, null, $this->client);
        foreach ($list as $rawSubscription) {
            $status = $this->translateState($rawSubscription->state);
            if (!$bitMask || ($bitMask & $status)) {
                $subscription = clone $template;
                $this->bindSource($subscription, $rawSubscription);

                $subscriptions[$subscription->id] = $subscription;
            }
        }
        return $subscriptions;
    }

    /**
     * Get the most recent subscription
     *
     * @param Subscription $parent
     * @param Account      $account
     *
     * @return void
     * @throws Exception
     */
    public function loadLast(Subscription $parent, Account $account)
    {
        $rawList = iterator_to_array(
            \Recurly_SubscriptionList::getForAccount(
                $account->code,
                null,
                $this->client
            )
        );

        if (count($rawList) == 0) {
            throw new NotFound('No subscriptions found for Account ' . $account->code);
        }

        $current = array_shift($rawList);
        $this->bindSource($parent, $current);
    }

    /**
     * Translate a Recurly state to an API Status
     *
     * @param $state
     *
     * @return string
     */
    protected function translateState($state)
    {
        if (isset($this->fieldMap['status']['state'][$state])) {
            return $this->fieldMap['status']['state'][$state];
        }
        return null;
    }

    /**
     * Cancel autorenew for this subscription
     *
     * @param Subscription $parent
     *
     * @return void
     * @throws Exception
     */
    public function cancel(Subscription $parent)
    {
        $subscription = $this->getSubscription($parent->id);

        try {
            $subscription->cancel();

        } catch (\Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Turn autorenew on for this subscription
     *
     * @param Subscription $parent
     *
     * @return void
     * @throws Exception
     */
    public function reactivate(Subscription $parent)
    {
        $subscription = $this->getSubscription($parent->id);

        try {
            $subscription->reactivate();

        } catch (\Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Update subscription to a different plan
     *
     * @param Subscription $parent
     * @param Plan         $plan
     * @param Coupon       $coupon
     *
     * @return void
     * @throws Exception
     */
    public function update(Subscription $parent, Plan $plan, Coupon $coupon = null)
    {
        $subscription = $this->getSubscription($parent->id);

        try {
            if ($coupon) {
                // Need to set apiKey statically for additional info
                \Recurly_Client::$apiKey = $this->client->apiKey();

                $account     = $subscription->account->get();
                $accountCode = $account->account_code;

                try {
                    /** @var \Recurly_CouponRedemption $oldCoupon */
                    $oldCoupon = \Recurly_CouponRedemption::get($accountCode);
                    $oldCoupon->delete();
                } catch (\Recurly_NotFoundError $e) {
                    // Perfectly fine
                }

                /** @var \Recurly_Coupon $newCoupon */
                $newCoupon = \Recurly_Coupon::get($coupon->code, $this->client);
                $newCoupon->redeemCoupon($accountCode, $subscription->currency);
            }

            $subscription->plan_code = $plan->code;
            $subscription->updateAtRenewal();
        } catch (\Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode(), $e);
        }
    }
}
