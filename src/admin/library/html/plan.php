<?php
/**
 * @package   Simplerenew
 * @contact   www.ostraining.com, support@ostraining.com
 * @copyright 2014 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

use Simplerenew\Api\Plan;

defined('_JEXEC') or die();

abstract class JHtmlPlan
{
    /**
     * Return a standard format plan name
     *
     * @param string $name
     * @param string $currency
     * @param string $amount
     * @param int    $length
     * @param string $unit
     * @param int    $trial_length
     * @param string $trial_unit
     *
     * @return string
     */
    public static function name(
        $name,
        $currency = null,
        $amount = null,
        $length = null,
        $unit = null,
        $trial_length = null,
        $trial_unit = null
    ) {

        $text = $name;

        if ($amount > 0) {
            $text .= ' ' . JHtml::_('currency.format', $amount, $currency);
        }

        if ($length > 0 && $unit) {
            $text .= ' ' . JText::plural('COM_SIMPLERENEW_PLAN_LENGTH_' . $unit, $length, $length);
        }

        if ($trial_length > 0 && $trial_unit) {
            $text .= ' - ' . self::trial($trial_length, $trial_unit);
        }

        return $text;
    }

    /**
     * Return a standard format duration text
     *
     * @param int    $length
     * @param string $unit
     * @param bool   $trial
     *
     * @return string
     */
    public static function length($length, $unit = null, $trial = false)
    {
        if (func_num_args() == 1) {
            if (is_object($length)) {
                $plan = (array)$length;
            }
            $length = empty($plan['length']) ? 0 : $plan['length'];
            $unit   = empty($plan['unit']) ? '' : $plan['unit'];
        }

        if ($length && $unit) {
            $string = 'COM_SIMPLERENEW_PLAN_LENGTH_' . ($trial ? 'TRIAL_' : '') . $unit;
            if (SimplerenewFactory::getLanguage()->hasKey($string)) {
                return JText::plural($string, $length);
            }
        }

        return '';
    }

    /**
     * Return standard format trial period text
     *
     * @param mixed  $length
     * @param string $unit
     *
     * @return string
     */
    public static function trial($length, $unit = null)
    {
        if (func_num_args() == 1) {
            if (is_object($length)) {
                $plan = (array)$length;
            }
            $length = empty($plan['trial_length']) ? 0 : $plan['trial_length'];
            $unit   = empty($plan['trial_unit']) ? '' : $plan['trial_unit'];
        }
        return self::length($length, $unit, true);
    }
}
