<?php
/**
 * @package   Simplerenew
 * @contact   www.ostraining.com, support@ostraining.com
 * @copyright 2014 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

use Simplerenew\Primitive as Payment;

defined('_JEXEC') or die();

/**
 * @var SimplerenewViewAccount $this
 * @var Payment\PayPal         $paypal
 * @var Payment\CreditCard     $creditCard
 */

JHtml::_('sr.validation.billing');

$billing = $this->billing;

if (!$billing) {
    $billing = (object)array(
        'firstname' => null,
        'lastname'  => null
    );

} else {
    if ($billing->payment instanceof Payment\PayPal) {
        $paypal = $billing->payment;
    } else {
        $creditCard = $billing->payment;
    }
}
if (empty($creditCard)) {
    $creditCard = new Payment\CreditCard();
}

echo $this->stepHeading(JText::_('COM_SIMPLERENEW_HEADING_BILLING'));

if (!empty($paypal)): ?>
    <div class="ost-alert-notify m-bottom">
        <?php echo JText::sprintf('COM_SIMPLERENEW_BILLING_EDIT_PAYPAL', $paypal->agreementId); ?>
    </div>
    <?php
endif; ?>

<div class="ost-section">
    <div class="block3 tab-enabled" id="tab_card">
        <h4>
            <i class="fa fa-credit-card"></i>
            <?php echo JText::_('COM_SIMPLERENEW_CREDITCARD'); ?>
        </h4>
    </div>
</div>
<!-- /.ost-section -->

<div id="content_card" class="content-enabled m-bottom">
    <div class="p-full">
        <div class="ost-section">
            <div class="block6">
                <label for="billing_firstname"><?php echo JText::_('COM_SIMPLERENEW_FIRSTNAME'); ?></label>
                <input
                    id="billing_firstname"
                    name="billing[firstname]"
                    type="text"
                    value="<?php echo $billing->firstname; ?>"/>
            </div>
            <div class="block6">
                <label for="billing_lastname"><?php echo JText::_('COM_SIMPLERENEW_LASTNAME'); ?></label>
                <input
                    id="billing_lastname"
                    name="billing[lastname]"
                    type="text"
                    value="<?php echo $billing->lastname; ?>"/>
            </div>
        </div>
        <!-- /.ost-section -->

        <?php echo $this->loadTemplate('address'); ?>

        <div class="ost-section">
            <div class="block5">
                <label><?php echo JText::_('COM_SIMPLERENEW_CC_NUMBER'); ?></label>
                <input
                    id="billing_cc_number"
                    type="text"
                    value=""
                    placeholder="<?php echo JHtml::_('creditcard.mask', $creditCard->lastFour); ?>"
                    class="check_ccnumber"
                    data-msg-ccnumber="<?php echo JText::_('COM_SIMPLERENEW_VALIDATE_BILLING_CC_NUMBER_INVALID'); ?>"/>
            </div>
            <div class="block2">
                <label><?php echo JText::_('COM_SIMPLERENEW_CC_CVV'); ?></label>
                <input
                    id="billing_cc_cvv"
                    type="text"
                    value=""
                    class="check_cvv small-width"
                    data-ccnumber="#billing_cc_number"
                    data-msg-cvv="<?php echo JText::_('COM_SIMPLERENEW_VALIDATE_BILLING_CC_CVV_INVALID'); ?>"/>
            </div>
            <div class="block5">
                <label><?php echo JText::_('COM_SIMPLERENEW_CC_EXPIRATION'); ?></label>
                <?php
                echo JHtml::_(
                    'srselect.ccyear',
                    'billing[cc][year]',
                    array(
                        'class'           => 'check_date small-width',
                        'data-partner'    => '#billing_cc_month',
                        'data-msg-ccdate' => JText::_('COM_SIMPLERENEW_VALIDATE_BILLING_CC_DATE_INVALID')
                    ),
                    $creditCard->year,
                    'billing_cc_year'
                ); ?>
                <?php
                echo JHtml::_(
                    'srselect.ccmonth',
                    'billing[cc][month]',
                    'class="medium-width"',
                    $creditCard->month,
                    'billing_cc_month'
                ); ?>
            </div>
        </div>
        <!-- /.ost-section -->
    </div>
</div>

<input type="hidden" name="billing[token]" id="billing_token" value=""/>
