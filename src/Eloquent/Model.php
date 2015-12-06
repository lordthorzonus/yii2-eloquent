<?php


namespace leinonen\Yii2Eloquent\Eloquent;


use ArrayObject;
use Illuminate\Database\Eloquent\Model as Eloquent;
use ReflectionClass;
use yii\base\InvalidConfigException;
use yii\base\InvalidParamException;
use yii\helpers\Inflector;
use yii\validators\RequiredValidator;
use yii\validators\Validator;

class Model extends Eloquent
{
    use ValidationTrait;

    /**
     * The name of the default scenario.
     */
    const SCENARIO_DEFAULT = 'default';

    /**
     * @event ModelEvent an event raised at the beginning of [[validate()]]. You may set
     * [[ModelEvent::isValid]] to be false to stop the validation.
     */
    const EVENT_BEFORE_VALIDATE = 'beforeValidate';

    /**
     * @event Event an event raised at the end of [[validate()]]
     */
    const EVENT_AFTER_VALIDATE = 'afterValidate';

    /**
     * Returns the form name that this model class should use.
     *
     * The form name is mainly used by [[\yii\widgets\ActiveForm]] to determine how to name
     * the input fields for the attributes in a model. If the form name is "A" and an attribute
     * name is "b", then the corresponding input name would be "A[b]". If the form name is
     * an empty string, then the input name would be "b".
     *
     * By default, this method returns the model class name (without the namespace part)
     * as the form name. You may override it when the model is used in different forms.
     *
     * @return string the form name of this model class.
     */
    public function formName()
    {
        $reflector = new ReflectionClass($this);

        return $reflector->getShortName();
    }

    /**
     * Returns the list of all attribute names of the record.
     *
     * @return array list of attribute names.
     */
    public function attributes()
    {
        return $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable());
    }

    /**
     * Returns a value indicating whether the record has an attribute with the specified name.
     *
     * @param string $name the name of the attribute
     *
     * @return boolean whether the record has an attribute with the specified name.
     */
    public function hasAttribute($name)
    {
        return array_key_exists($name, $this->attributes);
    }

    /**
     * Returns the attribute labels.
     *
     * Attribute labels are mainly used for display purpose. For example, given an attribute
     * `firstName`, we can declare a label `First Name` which is more user-friendly and can
     * be displayed to end users.
     *
     * By default an attribute label is generated using [[generateAttributeLabel()]].
     * This method allows you to explicitly specify attribute labels.
     *
     * Note, in order to inherit labels defined in the parent class, a child class needs to
     * merge the parent labels with child labels using functions such as `array_merge()`.
     *
     * @return array attribute labels (name => label)
     * @see generateAttributeLabel()
     */
    public function attributeLabels()
    {
        return [];
    }

    /**
     * Returns the attribute hints.
     *
     * Attribute hints are mainly used for display purpose. For example, given an attribute
     * `isPublic`, we can declare a hint `Whether the post should be visible for not logged in users`,
     * which provides user-friendly description of the attribute meaning and can be displayed to end users.
     *
     * Unlike label hint will not be generated, if its explicit declaration is omitted.
     *
     * Note, in order to inherit hints defined in the parent class, a child class needs to
     * merge the parent hints with child hints using functions such as `array_merge()`.
     *
     * @return array attribute hints (name => hint)
     * @since 2.0.4
     */
    public function attributeHints()
    {
        return [];
    }

    /**
     * Returns the text label for the specified attribute.
     * @param string $attribute the attribute name
     * @return string the attribute label
     * @see generateAttributeLabel()
     * @see attributeLabels()
     */
    public function getAttributeLabel($attribute)
    {
        $labels = $this->attributeLabels();
        return isset($labels[$attribute]) ? $labels[$attribute] : Inflector::camel2words($attribute, true);
    }

    /**
     * Returns the text hint for the specified attribute.
     * @param string $attribute the attribute name
     * @return string the attribute hint
     * @see attributeHints()
     * @since 2.0.4
     */
    public function getAttributeHint($attribute)
    {
        $hints = $this->attributeHints();
        return isset($hints[$attribute]) ? $hints[$attribute] : '';
    }

    /**
     * Returns attribute values.
     * @param array $names list of attributes whose value needs to be returned.
     * Defaults to null, meaning all attributes listed in [[attributes()]] will be returned.
     * If it is an array, only the attributes in the array will be returned.
     * @param array $except list of attributes whose value should NOT be returned.
     * @return array attribute values (name => value).
     */
    public function getAttributes($names = null, $except = [])
    {
        $values = [];
        if ($names === null) {
            $names = $this->attributes();
        }
        foreach ($names as $name) {
            $values[$name] = $this->$name;
        }
        foreach ($except as $name) {
            unset($values[$name]);
        }

        return $values;
    }


}
