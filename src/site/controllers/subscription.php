<?php
/**
 * @package   Simplerenew
 * @contact   www.ostraining.com, support@ostraining.com
 * @copyright 2014 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

use Simplerenew\Exception\NotFound;

defined('_JEXEC') or die();

class SimplerenewControllerSubscription extends SimplerenewControllerBase
{
    public function display($cachable = false, $urlparams = array())
    {
        parent::display($cachable, $urlparams);
        echo '<h4>subscription controller under construction</h4>h4>';
    }

    /**
     * New Subscriptions including expired
     */
    public function create()
    {
        $this->checkToken();

        SimplerenewHelper::saveFormData(
            'subscribe.create',
            array(
                'password',
                'password2',
                'billing.cc.number',
                'billing.cc.cvv'
            )
        );

        $app      = SimplerenewFactory::getApplication();
        $planCode = $app->input->getString('planCode');
        if (!$planCode) {
            return $this->callerReturn(
                JText::_('COM_SIMPLERENEW_ERROR_NOPLAN_SELECTED'),
                'error'
            );
        }

        /** @var SimplerenewModelGateway $model */
        $model = SimplerenewModel::getInstance('Gateway');

        // Create/Load the user
        try {
            $user = $model->saveUser();

        } catch (Exception $e) {
            return $this->callerReturn($e->getMessage(), 'error');
        }

        // Create the account
        try {
            $account = $model->saveAccount($user);

        } catch (Exception $e) {
            return $this->callerReturn(
                JText::sprintf('COM_SIMPLERENEW_ERROR_SUBSCRIBE_ACCOUNT', $e->getMessage()),
                'error'
            );
        }

        // Update billing
        try {
            $model->saveBilling($account);
        } catch (Exception $e) {
            return $this->callerReturn(
                JText::sprintf('COM_SIMPLERENEW_ERROR_SUBSCRIBE_BILLING', $e->getMessage()),
                'error'
            );
        }

        // All went well! Regardless of Joomla settings, log in the user if not already logged in
        $password = $app->input->getString('password');
        $user->login($password, true);

        try {
            $model->createSubscription($account, $app->input->getString('planCode'));
        } catch (Exception $e) {
            return $this->callerReturn(
                $e->getMessage(),
                'error'
            );
        }

        return $this->callerReturn('User/Account Create - need to send the user someplace');
    }

    /**
     * Change from one active subscription to another
     */
    public function change()
    {
        echo 'change subscriptions under construction';
    }

    /**
     * Change autorenew status
     */
    public function renewal()
    {
        echo 'cancel/autorenew under construction';
    }
}
