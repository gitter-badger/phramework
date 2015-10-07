<?php
/**
 * Copyright 2015 Spafaridis Xenofon
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
namespace Phramework\Validate;

/**
 * BaseValidator, every validator **MUST** extend this class
 *
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Spafaridis Xenophon <nohponex@gmail.com>
 * @since 1.0.0
 */
abstract class BaseValidator
{
    /**
     * Validator's type
     * Must be overwriten, default is 'string'
     * @var string
     */
    protected static $type = 'string';
    public abstract function validate($value);
    /**
     * Get validator's type
     * @return string
     */
    public static function getType()
    {
        return static::$type;
    }

    /**
     * Validator's attributes
     * Can be overwriten
     * @var string[]
     */
    protected static $typeAttributes = [
    ];

    /**
     * Common valdator attributes
     * @var string[]
     */
    protected static $commonAttributes = [
        'title',
        'description',
        'default',
        'format'
    ];

    /**
     * Get validator's attributes
     * @return string[]
     */
    public static function getTypeAttributes()
    {
        return static::$typeAttributes;
    }

    /**
     * Objects current attributes and values
     * @var array
     */
    protected $attributes = [];

    protected function __construct()
    {
        //Append common attributes
        foreach (static::$commonAttributes as $attribute) {
            $this->attributes[$attribute] = null;
        }

        //Append type attributes
        foreach (static::$typeAttributes as $attribute) {
            $this->attributes[$attribute] = null;
        }
    }

    /**
     * Get attribute's value
     * @param  string $key Attribute's key
     * @return mixed
     * @throws \Exception If key not found
     */
    public function __get($key)
    {
        if (!array_key_exists($key, $this->attributes)) {
            throw new \Exception('Unknown key "' . $key . '" to get');
        }

        return $this->attributes[$key];
    }

    /**
     * Set attribute's value
     * @param string $key   Attribute's key
     * @param mixed $value  Attribute's value
     * @throws \Exception If key not found
     * @return BaseValidator Return's this validator object
     */
    public function __set($key, $value)
    {
        if (!array_key_exists($key, $this->attributes)) {
            throw new \Exception('Unknown key "' . $key . '" to set');
        }

        $this->attributes[$key] = $value;

        return $this;
    }

    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    public function setDescription($description)
    {
        return $this->__set('description', $description);
    }

    public function setDefault($default)
    {
        $this->default = $default;

        return $this;
    }

    /**
     * Create validator from validation object
     * @param  \stdClass $object Validation object
     * @return BaseValidator
     * @todo use $isFromBase to initialize Validator by name
     */
    public static function createFromObject($object)
    {
        $isFromBase = (static::class === self::class);

        //Test type if it's set
        if (property_exists($object, 'type') && $object->type !== static::$type) {
            throw new \Exception('Incorrect type ' . $object->type . ' from base ' . $isFromBase);
        }

        //Initialize a new Validator object, type of current class
        $class = new static();

        //For each Validator's attribute
        foreach (static::getTypeAttributes() as $attribute) {
            //Check if provided object contains this attribute
            if (property_exists($object, $attribute)) {
                if ($attribute == 'properties') {
                    $properties = (array)$object->{$attribute};

                    $createdProperties = [];

                    foreach ($properties as $key => $property) {
                        if (!is_object($property)) {
                            throw new \Exception('Expected object for property value');
                        }

                        $createdProperties[$key] =
                        BaseValidator::createFromObject($property);
                    }
                    //push to class
                    //$class->{$attribute} = $createdProperties;
                } else {
                    //Use attributes value in Validator object
                    $class->{$attribute} = $object->{$attribute};
                }
            }
        }

        return $class;
    }

    /**
     * Create validator from validation array
     * @param  array $object Validation array
     * @return BaseValidator
     */
    public static function createFromArray($array)
    {
        $object = (object)($array);
        return static::createFromObject($object);

    }

    /**
     * Create validator from validation object encoded as json object
     * @param  string $object Validation json encoded object
     * @return BaseValidator
     */
    public static function createFromJSON($json)
    {
        $object = json_decode($json);
        return static::createFromObject($object);
    }

    /**
     * Export validator to json encoded string
     * @return string
     */
    public function toJSON()
    {
        $object = $this->toArray();
        return json_encode($object);
    }

    /**
     * Export validator to json encoded string
     * @return \stdClass
     */
    public function toObject()
    {
        $object = $this->toArray();
        return (object)$object;
    }

    /**
     * Export validator to json encoded string
     * @return array
     */
    public function toArray()
    {
        $object = ['type' => static::$type];
        foreach (static::getTypeAttributes() as $attribute) {
            $value = $this->{$attribute};
            if ($value !== null) {
                $object[$attribute] = $value;
            }
        }
        return $object;
    }
}