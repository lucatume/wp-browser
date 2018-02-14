<?php

/**
 * An abstract class that serves as a basis for all WordPress object-type factory classes.
 */
abstract class WP_UnitTest_Factory_For_Thing
{
    public $default_generation_definitions;
    public $factory;

    /**
     * Creates a new factory, which will create objects of a specific Thing.
     *
     * @param object $factory                        Global factory that can be used to create other objects on the system
     * @param array  $default_generation_definitions Defines what default values should the properties of the object have. The default values
     *                                               can be generators -- an object with next() method. There are some default generators: {@link WP_UnitTest_Generator_Sequence},
     *                                               {@link WP_UnitTest_Generator_Locale_Name}, {@link WP_UnitTest_Factory_Callback_After_Create}.
     */
    public function __construct($factory, $default_generation_definitions = [])
    {
        $this->factory = $factory;
        $this->default_generation_definitions = $default_generation_definitions;
    }

    abstract public function create_object($args);

    abstract public function update_object($object, $fields);

    public function create($args = [], $generation_definitions = null)
    {
        if (is_null($generation_definitions)) {
            $generation_definitions = $this->default_generation_definitions;
        }

        $generated_args = $this->generate_args($args, $generation_definitions, $callbacks);
        $created = $this->create_object($generated_args);
        if (!$created || is_wp_error($created)) {
            return $created;
        }

        if ($callbacks) {
            $updated_fields = $this->apply_callbacks($callbacks, $created);
            $save_result = $this->update_object($created, $updated_fields);
            if (!$save_result || is_wp_error($save_result)) {
                return $save_result;
            }
        }

        return $created;
    }

    public function create_and_get($args = [], $generation_definitions = null)
    {
        $object_id = $this->create($args, $generation_definitions);

        return $this->get_object_by_id($object_id);
    }

    abstract public function get_object_by_id($object_id);

    public function create_many($count, $args = [], $generation_definitions = null)
    {
        $results = [];
        for ($i = 0; $i < $count; $i++) {
            $results[] = $this->create($args, $generation_definitions);
        }

        return $results;
    }

    public function generate_args($args = [], $generation_definitions = null, &$callbacks = null)
    {
        $callbacks = [];
        if (is_null($generation_definitions)) {
            $generation_definitions = $this->default_generation_definitions;
        }

        // Use the same incrementor for all fields belonging to this object.
        $gen = new WP_UnitTest_Generator_Sequence();
        $incr = $gen->get_incr();

        foreach (array_keys($generation_definitions) as $field_name) {
            if (!isset($args[$field_name])) {
                $generator = $generation_definitions[$field_name];
                if (is_scalar($generator)) {
                    $args[$field_name] = $generator;
                } elseif (is_object($generator) && method_exists($generator, 'call')) {
                    $callbacks[$field_name] = $generator;
                } elseif (is_object($generator)) {
                    $args[$field_name] = sprintf($generator->get_template_string(), $incr);
                } else {
                    return new WP_Error('invalid_argument', 'Factory default value should be either a scalar or an generator object.');
                }
            }
        }

        return $args;
    }

    public function apply_callbacks($callbacks, $created)
    {
        $updated_fields = [];
        foreach ($callbacks as $field_name => $generator) {
            $updated_fields[$field_name] = $generator->call($created);
        }

        return $updated_fields;
    }

    public function callback($function)
    {
        return new WP_UnitTest_Factory_Callback_After_Create($function);
    }

    public function addslashes_deep($value)
    {
        if (is_array($value)) {
            $value = array_map([$this, 'addslashes_deep'], $value);
        } elseif (is_object($value)) {
            $vars = get_object_vars($value);
            foreach ($vars as $key=>$data) {
                $value->{$key} = $this->addslashes_deep($data);
            }
        } elseif (is_string($value)) {
            $value = addslashes($value);
        }

        return $value;
    }
}
