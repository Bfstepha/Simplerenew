<?php
/**
 * @package   com_simplerenew
 * @contact   www.ostraining.com, support@ostraining.com
 * @copyright 2014 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace Simplerenew\Gateway\Recurly;

use Simplerenew\Api\Billing;
use Simplerenew\Exception;
use Simplerenew\Gateway\BillingInterface;
use Simplerenew\Primitive\Address;
use Simplerenew\Primitive\CreditCard;

defined('_JEXEC') or die();

class BillingImp extends AbstractRecurlyBase implements BillingInterface
{
    protected $fieldMap = array(
        'firstname' => 'first_name',
        'lastname'  => 'last_name',
        'region'    => 'state',
        'postal'    => 'zip',
        'ipaddress' => 'ip_address'
    );

    /**
     * @var array Associative array of \Recurly_BillingInfo objects already loaded
     */
    protected $accountsLoaded = array();

    /**
     * @param Billing $parent
     *
     * @return void
     * @throws Exception
     */
    public function load(Billing $parent)
    {
        $parent->clearProperties();

        $billing = $this->getBilling($parent->account->code);
        if ($billing) {
            $parent->setProperties($billing, $this->fieldMap);

            if ($billing->address && $parent->address instanceof Address) {
                $parent->address->setProperties(
                    $billing->address,
                    array(
                        'region' => 'state',
                        'postal' => 'zip'
                    )
                );
            }
        }
    }

    public function save(Billing $parent)
    {
        $billing = $this->getBilling($parent->account->code);

        $billing->first_name = $parent->firstname;
        $billing->last_name  = $parent->lastname;
        $billing->phone      = $parent->phone;
        $billing->ip_address = $parent->ipaddress;

        $billing->address1   = $parent->address->address1;
        $billing->address2   = $parent->address->address2;
        $billing->city       = $parent->address->city;
        $billing->state      = $parent->address->region;
        $billing->country    = $parent->address->country;
        $billing->zip        = $parent->address->postal;

        if ($parent->payment instanceof CreditCard) {
            /** @var CreditCard $cc */
            $cc = $parent->payment;
            $billing->number = $cc->number;
            $billing->month  = $cc->month;
            $billing->year   = $cc->year;
        }
        $billing->update();
    }

    /**
     * @param $accountCode
     *
     * @return \Recurly_BillingInfo
     * @throws \Simplerenew\Exception
     */
    protected function getBilling($accountCode)
    {
        try {
            if (empty($this->accountsLoaded[$accountCode])) {
                $this->accountsLoaded[$accountCode] = \Recurly_BillingInfo::get($accountCode, $this->client);
            }

        } catch (\Recurly_NotFoundError $e) {
            $newBilling = new \Recurly_BillingInfo(null, $this->client);
            $newBilling->account_code = $accountCode;
            $this->accountsLoaded[$accountCode] = $newBilling;

        } catch (\Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode(), $e);
        }

        return $this->accountsLoaded[$accountCode];
    }
}
