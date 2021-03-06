<?php
/**
 * @package   Simplerenew
 * @contact   www.ostraining.com, support@ostraining.com
 * @copyright 2014 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

abstract class JHtmlSr
{
    protected static $jqueryLoaded = array();
    protected static $utilitiesLoaded = false;

    /**
     * Generate link to Terms & Conditions page
     *
     * @return string
     */
    public static function terms()
    {
        $params = SimplerenewComponentHelper::getParams();

        if ($itemid = $params->get('basic.terms')) {
            $link = JHtml::_(
                'link',
                JRoute::_('index.php?Itemid=' . $itemid),
                JText::_('COM_SIMPLERENEW_TERMS_OF_AGREEMENT_LINK_TEXT'),
                'target="_blank"'
            );

            return JText::sprintf('COM_SIMPLERENEW_TERMS_OF_AGREEMENT', $link);
        }
        return '';
    }

    /**
     * Load jQuery core
     *
     * @param bool $utilities
     * @param bool $noConflict
     * @param bool $debug
     */
    public static function jquery($utilities = false, $noConflict = true, $debug = null)
    {
        $params = SimplerenewComponentHelper::getParams();

        if ($params->get('advanced.jquery', 1)) {
            // Only load once
            if (empty(static::$jqueryLoaded[__METHOD__])) {
                if (version_compare(JVERSION, '3', 'ge')) {
                    JHtml::_('jquery.framework', $noConflict, $debug);
                } else {
                    // pre 3.0 manual loading

                    // If no debugging value is set, use the configuration setting
                    if ($debug === null) {
                        $config = JFactory::getConfig();
                        $debug  = (boolean)$config->get('debug');
                    }

                    JHtml::_('script', 'com_simplerenew/jquery.js', false, true, false, false, $debug);

                    // Check if we are loading in noConflict
                    if ($noConflict) {
                        JHtml::_('script', 'com_simplerenew/jquery-noconflict.js', false, true, false, false, false);
                    }
                }
            }
        }

        static::$jqueryLoaded[__METHOD__] = true;

        if ($utilities && !static::$utilitiesLoaded) {
            JHtml::_('script', 'com_simplerenew/utilities.js', false, true);
            static::$utilitiesLoaded = true;
        }
    }

    /**
     * Setup tabbed areas
     *
     * @param string       $selector jQuery selector for tab headers
     * @param array|string $options  Associative array or JSON string of tabber options
     *
     * @return void
     */
    public static function tabs($selector, $options = null)
    {
        static::jquery(true);

        if ($options && is_string($options)) {
            $options = json_decode($options, true);
        }
        if (!is_array($options)) {
            $options = array();
        }
        $options['selector'] = $selector;

        $options = json_encode($options);
        static::onready("jQuery.Simplerenew.tabs({$options});");
    }

    /**
     * Setup simple sliders
     *
     * @param string $selector
     * @param bool   $visible
     *
     * @return void
     */
    public static function sliders($selector, $visible = false)
    {
        static::jquery(true);

        $options = json_encode(
            array(
                'selector' => $selector,
                'visible'  => (bool)$visible
            )
        );

        static::onready("jQuery.Simplerenew.sliders({$options});");
    }

    /**
     * Create a clickable area for radio buttons and checkboxes.
     * Will accept a string as the jQuery selector for areas or
     * more detailed options as either json string or an array
     *
     * @param mixed $options
     *
     * @return void
     */
    public static function clickarea($options)
    {
        static::jquery(true);

        $arrayOptions = array();
        if (is_string($options)) {
            $arrayOptions = json_decode($options, true);
            if (!$arrayOptions) {
                $arrayOptions = array(
                    'selector' => $options
                );
            }
        } elseif (is_array($options)) {
            $arrayOptions = $options;
        }

        $jsonOptions = json_encode($arrayOptions);
        static::onready("jQuery.Simplerenew.clickArea({$jsonOptions});");
    }

    /**
     * Add a script to run when dom ready
     *
     * @param string $js
     *
     * @return void
     */
    public static function onready($js)
    {
        $js = "jQuery(document).ready(function () { " . $js . " });";
        SimplerenewFactory::getDocument()->addScriptDeclaration($js);
    }

    /**
     * Create an input form field
     *
     * @param string $name
     * @param mixed  $attribs
     * @param string $selected
     * @param mixed  $idtag
     *
     * @return string
     */
    public static function inputfield($name, $attribs = null, $selected = null, $idtag = false)
    {

        if ($attribs && !is_array($attribs)) {
            $attribs = JUtility::parseAttributes($attribs);
        }

        $attribs['name']  = $name;
        $attribs['id']    = $idtag ?: preg_replace('/(\[\]|\[|\])/', '_', $name);
        $attribs['type']  = 'text';
        $attribs['value'] = $selected;
        return '<input ' . JArrayHelper::toString($attribs) . '/>';
    }
}
