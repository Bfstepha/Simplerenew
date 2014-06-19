<?php
/**
 * @package   com_simplerenew
 * @contact   www.ostraining.com, support@ostraining.com
 * @copyright 2014 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

abstract class SimplerenewToolbarHelper extends JToolbarHelper
{
    public static function custom($task = '', $icon = '', $iconOver = '', $alt = '', $listSelect = true, $iconColor = '#333')
    {
        $img = JHtml::_('image', "com_simplerenew/icon-32-{$icon}.png", null, null, true, true);
        if ($img) {
            $doc = JFactory::getDocument();
            /* The first style declaration is for Joomla 2.5 and the second is for Joomla 3 */
            $doc->addStyleDeclaration(".icon-32-{$icon} { background-image: url({$img}); background-repeat: no-repeat; }");
            $doc->addStyleDeclaration(".icon-{$icon}:before { color: {$iconColor}; }");
        }
        parent::custom($task, $icon, $iconOver, $alt, $listSelect, $iconColor);
    }
}
