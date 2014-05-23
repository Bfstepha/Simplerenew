<?php
/**
 * @package   com_simplerenew
 * @contact   www.ostraining.com, support@ostraining.com
 * @copyright 2014 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace Simplerenew;

defined('_JEXEC') or die();

class Object
{
    /**
     * @var \ReflectionObject
     */
    private $reflection = null;

    /**
     * Retrieve all public properties and their values
     * Although this duplicates get_object_vars(), it
     * is mostly useful for internal calls when we need
     * to filter out the non-public properties. Note
     * we cache the property names assuming the public
     * facing properties will not change in the lifetime
     * of the object
     *
     * @param bool $publicOnly
     *
     * @return array
     */
    public function getProperties($publicOnly = true)
    {
        $properties = $this->getReflection()->getProperties(\ReflectionProperty::IS_PUBLIC);
        if (!$publicOnly) {
            $properties = array_merge(
                $properties,
                $this->reflection->getProperties(\ReflectionProperty::IS_PROTECTED)
            );
        }

        $data   = array();
        foreach ($properties as $property) {
            $name        = $property->name;
            $data[$name] = $this->$name;
        }

        return $data;
    }

    /**
     * Set the public properties from the passed array/object
     *
     * @param mixed $data Associative array or object with properties to copy to $this
     * @param array $map  Use fields from $data translated using a field map
     *
     * @return void
     * @throws Exception
     */
    public function setProperties($data, array $map = null)
    {
        $properties = $this->getProperties();
        if ($map !== null) {
            $data = $this->map($data, array_keys($properties), $map);
        } elseif (is_object($data)) {
            $data = get_object_vars($data);
        }
        if (!is_array($data)) {
            throw new Exception('Invalid argument given - ' . gettype($data));
        }

        foreach ($data as $k => $v) {
            if (array_key_exists($k, $properties)) {
                $this->$k = $data[$k];
            }
        }
    }

    /**
     * Set all properties to null
     *
     * @param bool $publicOnly Pass false to include protected properties as well
     *
     * @return void
     */
    public function clearProperties($publicOnly = true)
    {
        $properties = array_keys($this->getProperties($publicOnly));
        foreach ($properties as $property) {
            $this->$property = null;
        }
    }

    /**
     * Map values in a source object/array to Simplerenew keys using a map
     * of key equivalences. Any fields in $keys not present in $map will be
     * mapped name to name. Map fields mapped to null will be ignored.
     *
     * Special mappings for field values are recognized with another array. e.g.:
     *
     * $map['status'] = array(
     *     'state' => array(
     *         'active' => 1,
     *         'closed' => 0,
     *         '::'     => -1
     *     )
     * )
     * Will map the Simplerenew field 'status' to the source field 'state' and
     * set status based on the value in the state field. If no match, '::' will be used for
     * the unknown value.
     *
     *
     * @param mixed $source Object or associative array of source data to be mapped
     * @param array $keys   Simplerenew keys for which values are being requested
     * @param array $map    Associative array where key=Simplerenew Key, value=Source Key
     *
     * @return array An array of all specified keys with values filled in based on map
     * @throws \Simplerenew\Exception
     */
    public function map($source, array $keys, array $map = array())
    {
        if (!is_object($source) && !is_array($source)) {
            throw new Exception('Expected array or object for source argument');
        }

        $result = array_fill_keys($keys, null);
        foreach ($keys as $srKey) {
            $value = null;
            if (array_key_exists($srKey, $map)) {
                if (is_array($map[$srKey])) {
                    $values   = reset($map[$srKey]);
                    $field    = key($map[$srKey]);
                    $selected = is_object($source) ? $source->$field : $source[$field];
                    if (isset($values[$selected])) {
                        $value = $values[$selected];
                    } elseif (isset($values['::'])) {
                        $value = $values['::'];
                    }
                } elseif ($map[$srKey] === null) {
                    $value = null;
                } else {
                    $field = $map[$srKey];
                    $value = is_object($source) ? $source->$field : $source[$field];
                }
            } else {
                $value = is_object($source) ? $source->$srKey : $source[$srKey];
            }

            $result[$srKey] = $value;
        }

        return $result;
    }

    /**
     * Get the reflection object for $this
     *
     * @return \ReflectionObject
     */
    private function getReflection()
    {
        if ($this->reflection === null) {
            $this->reflection = new \ReflectionObject($this);
        }
        return $this->reflection;
    }

    /**
     * Default string rendering for the object. Subclasses should feel
     * free to override as desired.
     *
     * @return string
     */
    public function __toString()
    {
        return get_class($this);
    }
}
