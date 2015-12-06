<?php


namespace leinonen\Yii2Eloquent\Eloquent;


use ArrayObject;
use yii\base\InvalidConfigException;
use yii\base\InvalidParamException;
use yii\validators\RequiredValidator;
use yii\validators\Validator;

trait ValidationTrait
{

    /**
     * @var array validation errors (attribute name => array of errors)
     */
    private $errors;

    /**
     * @var ArrayObject list of validators
     */
    private $validators;

    /**
     * @var string current scenario
     */
    private $scenario = self::SCENARIO_DEFAULT;


    /**
     * Returns the validation rules for attributes.
     *
     * Validation rules are used by [[validate()]] to check if attribute values are valid.
     * Child classes may override this method to declare different validation rules.
     *
     * Each rule is an array with the following structure:
     *
     * ~~~
     * [
     *     ['attribute1', 'attribute2'],
     *     'validator type',
     *     'on' => ['scenario1', 'scenario2'],
     *     ...other parameters...
     * ]
     * ~~~
     *
     * where
     *
     *  - attribute list: required, specifies the attributes array to be validated, for single attribute you can pass a string;
     *  - validator type: required, specifies the validator to be used. It can be a built-in validator name,
     *    a method name of the model class, an anonymous function, or a validator class name.
     *  - on: optional, specifies the [[scenario|scenarios]] array in which the validation
     *    rule can be applied. If this option is not set, the rule will apply to all scenarios.
     *  - additional name-value pairs can be specified to initialize the corresponding validator properties.
     *    Please refer to individual validator class API for possible properties.
     *
     * A validator can be either an object of a class extending [[Validator]], or a model class method
     * (called *inline validator*) that has the following signature:
     *
     * ~~~
     * // $params refers to validation parameters given in the rule
     * function validatorName($attribute, $params)
     * ~~~
     *
     * In the above `$attribute` refers to the attribute currently being validated while `$params` contains an array of
     * validator configuration options such as `max` in case of `string` validator. The value of the attribute currently being validated
     * can be accessed as `$this->$attribute`. Note the `$` before `attribute`; this is taking the value of the variable
     * `$attribute` and using it as the name of the property to access.
     *
     * Yii also provides a set of [[Validator::builtInValidators|built-in validators]].
     * Each one has an alias name which can be used when specifying a validation rule.
     *
     * Below are some examples:
     *
     * ~~~
     * [
     *     // built-in "required" validator
     *     [['username', 'password'], 'required'],
     *     // built-in "string" validator customized with "min" and "max" properties
     *     ['username', 'string', 'min' => 3, 'max' => 12],
     *     // built-in "compare" validator that is used in "register" scenario only
     *     ['password', 'compare', 'compareAttribute' => 'password2', 'on' => 'register'],
     *     // an inline validator defined via the "authenticate()" method in the model class
     *     ['password', 'authenticate', 'on' => 'login'],
     *     // a validator of class "DateRangeValidator"
     *     ['dateRange', 'DateRangeValidator'],
     * ];
     * ~~~
     *
     * Note, in order to inherit rules defined in the parent class, a child class needs to
     * merge the parent rules with child rules using functions such as `array_merge()`.
     *
     * @return array validation rules
     * @see scenarios()
     */
    public function rules()
    {
        return [];
    }

    /**
     * Returns a list of scenarios and the corresponding active attributes.
     * An active attribute is one that is subject to validation in the current scenario.
     * The returned array should be in the following format:
     *
     * ```php
     * [
     *     'scenario1' => ['attribute11', 'attribute12', ...],
     *     'scenario2' => ['attribute21', 'attribute22', ...],
     *     ...
     * ]
     * ```
     *
     * By default, an active attribute is considered safe and can be massively assigned.
     * If an attribute should NOT be massively assigned (thus considered unsafe),
     * please prefix the attribute with an exclamation character (e.g. `'!rank'`).
     *
     * The default implementation of this method will return all scenarios found in the [[rules()]]
     * declaration. A special scenario named [[SCENARIO_DEFAULT]] will contain all attributes
     * found in the [[rules()]]. Each scenario will be associated with the attributes that
     * are being validated by the validation rules that apply to the scenario.
     *
     * @return array a list of scenarios and the corresponding active attributes.
     */
    public function scenarios()
    {
        $scenarios = [self::SCENARIO_DEFAULT => []];
        foreach ($this->getValidators() as $validator) {
            foreach ($validator->on as $scenario) {
                $scenarios[$scenario] = [];
            }
            foreach ($validator->except as $scenario) {
                $scenarios[$scenario] = [];
            }
        }
        $names = array_keys($scenarios);

        foreach ($this->getValidators() as $validator) {
            if (empty($validator->on) && empty($validator->except)) {
                foreach ($names as $name) {
                    foreach ($validator->attributes as $attribute) {
                        $scenarios[$name][$attribute] = true;
                    }
                }
            } elseif (empty($validator->on)) {
                foreach ($names as $name) {
                    if (!in_array($name, $validator->except, true)) {
                        foreach ($validator->attributes as $attribute) {
                            $scenarios[$name][$attribute] = true;
                        }
                    }
                }
            } else {
                foreach ($validator->on as $name) {
                    foreach ($validator->attributes as $attribute) {
                        $scenarios[$name][$attribute] = true;
                    }
                }
            }
        }

        foreach ($scenarios as $scenario => $attributes) {
            if (!empty($attributes)) {
                $scenarios[$scenario] = array_keys($attributes);
            }
        }

        return $scenarios;
    }

    /**
     * Performs the data validation.
     *
     * This method executes the validation rules applicable to the current [[scenario]].
     * The following criteria are used to determine whether a rule is currently applicable:
     *
     * - the rule must be associated with the attributes relevant to the current scenario;
     * - the rules must be effective for the current scenario.
     *
     * This method will call [[beforeValidate()]] and [[afterValidate()]] before and
     * after the actual validation, respectively. If [[beforeValidate()]] returns false,
     * the validation will be cancelled and [[afterValidate()]] will not be called.
     *
     * Errors found during the validation can be retrieved via [[getErrors()]],
     * [[getFirstErrors()]] and [[getFirstError()]].
     *
     * @param array $attributeNames list of attribute names that should be validated.
     * If this parameter is empty, it means any attribute listed in the applicable
     * validation rules should be validated.
     * @param boolean $clearErrors whether to call [[clearErrors()]] before performing validation
     * @return boolean whether the validation is successful without any error.
     * @throws InvalidParamException if the current scenario is unknown.
     */
    public function validate($attributeNames = null, $clearErrors = true)
    {
        if ($clearErrors) {
            $this->clearErrors();
        }

        if (!$this->beforeValidate()) {
            return false;
        }

        $scenarios = $this->scenarios();
        $scenario = $this->getScenario();
        if (!isset($scenarios[$scenario])) {
            throw new InvalidParamException("Unknown scenario: $scenario");
        }

        if ($attributeNames === null) {
            $attributeNames = $this->activeAttributes();
        }

        foreach ($this->getActiveValidators() as $validator) {
            $validator->validateAttributes($this, $attributeNames);
        }
        $this->afterValidate();

        return !$this->hasErrors();
    }

    /**
     * This method is invoked before validation starts.
     * The default implementation raises a `beforeValidate` event.
     * You may override this method to do preliminary checks before validation.
     * Make sure the parent implementation is invoked so that the event can be raised.
     * @return boolean whether the validation should be executed. Defaults to true.
     * If false is returned, the validation will stop and the model is considered invalid.
     */
    public function beforeValidate()
    {
        $this->fireModelEvent(self::EVENT_BEFORE_VALIDATE);

        return true;
    }

    /**
     * This method is invoked after validation ends.
     * The default implementation raises an `afterValidate` event.
     * You may override this method to do postprocessing after validation.
     * Make sure the parent implementation is invoked so that the event can be raised.
     */
    public function afterValidate()
    {
        $this->fireModelEvent(self::EVENT_AFTER_VALIDATE);
    }

    /**
     * Returns all the validators declared in [[rules()]].
     *
     * This method differs from [[getActiveValidators()]] in that the latter
     * only returns the validators applicable to the current [[scenario]].
     *
     * Because this method returns an ArrayObject object, you may
     * manipulate it by inserting or removing validators (useful in model behaviors).
     * For example,
     *
     * ~~~
     * $model->validators[] = $newValidator;
     * ~~~
     *
     * @return ArrayObject|\yii\validators\Validator[] all the validators declared in the model.
     */
    public function getValidators()
    {
        if ($this->validators === null) {
            $this->validators = $this->createValidators();
        }
        return $this->validators;
    }

    /**
     * Returns the validators applicable to the current [[scenario]].
     * @param string $attribute the name of the attribute whose applicable validators should be returned.
     * If this is null, the validators for ALL attributes in the model will be returned.
     * @return \yii\validators\Validator[] the validators applicable to the current [[scenario]].
     */
    public function getActiveValidators($attribute = null)
    {
        $validators = [];
        $scenario = $this->getScenario();
        foreach ($this->getValidators() as $validator) {
            if ($validator->isActive($scenario) && ($attribute === null || in_array($attribute, $validator->attributes, true))) {
                $validators[] = $validator;
            }
        }
        return $validators;
    }

    /**
     * Creates validator objects based on the validation rules specified in [[rules()]].
     * Unlike [[getValidators()]], each time this method is called, a new list of validators will be returned.
     * @return ArrayObject validators
     * @throws InvalidConfigException if any validation rule configuration is invalid
     */
    public function createValidators()
    {
        $validators = new ArrayObject;
        foreach ($this->rules() as $rule) {
            if ($rule instanceof Validator) {
                $validators->append($rule);
            } elseif (is_array($rule) && isset($rule[0], $rule[1])) { // attributes, validator type
                $validator = Validator::createValidator($rule[1], $this, (array) $rule[0], array_slice($rule, 2));
                $validators->append($validator);
            } else {
                throw new InvalidConfigException('Invalid validation rule: a rule must specify both attribute names and validator type.');
            }
        }
        return $validators;
    }

    /**
     * Returns a value indicating whether the attribute is required.
     * This is determined by checking if the attribute is associated with a
     * [[\yii\validators\RequiredValidator|required]] validation rule in the
     * current [[scenario]].
     *
     * Note that when the validator has a conditional validation applied using
     * [[\yii\validators\RequiredValidator::$when|$when]] this method will return
     * `false` regardless of the `when` condition because it may be called be
     * before the model is loaded with data.
     *
     * @param string $attribute attribute name
     * @return boolean whether the attribute is required
     */
    public function isAttributeRequired($attribute)
    {
        foreach ($this->getActiveValidators($attribute) as $validator) {
            if ($validator instanceof RequiredValidator && $validator->when === null) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns a value indicating whether the attribute is safe for massive assignments.
     * @param string $attribute attribute name
     * @return boolean whether the attribute is safe for massive assignments
     * @see safeAttributes()
     */
    public function isAttributeSafe($attribute)
    {
        return in_array($attribute, $this->safeAttributes(), true);
    }

    /**
     * Returns a value indicating whether the attribute is active in the current scenario.
     * @param string $attribute attribute name
     * @return boolean whether the attribute is active in the current scenario
     * @see activeAttributes()
     */
    public function isAttributeActive($attribute)
    {
        return in_array($attribute, $this->activeAttributes(), true);
    }


    /**
     * Returns a value indicating whether there is any validation error.
     * @param string|null $attribute attribute name. Use null to check all attributes.
     * @return boolean whether there is any error.
     */
    public function hasErrors($attribute = null)
    {
        return $attribute === null ? !empty($this->errors) : isset($this->errors[$attribute]);
    }

    /**
     * Returns the errors for all attribute or a single attribute.
     * @param string $attribute attribute name. Use null to retrieve errors for all attributes.
     * @property array An array of errors for all attributes. Empty array is returned if no error.
     * The result is a two-dimensional array. See [[getErrors()]] for detailed description.
     * @return array errors for all attributes or the specified attribute. Empty array is returned if no error.
     * Note that when returning errors for all attributes, the result is a two-dimensional array, like the following:
     *
     * ~~~
     * [
     *     'username' => [
     *         'Username is required.',
     *         'Username must contain only word characters.',
     *     ],
     *     'email' => [
     *         'Email address is invalid.',
     *     ]
     * ]
     * ~~~
     *
     * @see getFirstErrors()
     * @see getFirstError()
     */
    public function getErrors($attribute = null)
    {
        if ($attribute === null) {
            return $this->errors === null ? [] : $this->errors;
        } else {
            return isset($this->errors[$attribute]) ? $this->errors[$attribute] : [];
        }
    }

    /**
     * Returns the first error of every attribute in the model.
     * @return array the first errors. The array keys are the attribute names, and the array
     * values are the corresponding error messages. An empty array will be returned if there is no error.
     * @see getErrors()
     * @see getFirstError()
     */
    public function getFirstErrors()
    {
        if (empty($this->errors)) {
            return [];
        } else {
            $errors = [];
            foreach ($this->errors as $name => $es) {
                if (!empty($es)) {
                    $errors[$name] = reset($es);
                }
            }

            return $errors;
        }
    }

    /**
     * Returns the first error of the specified attribute.
     * @param string $attribute attribute name.
     * @return string the error message. Null is returned if no error.
     * @see getErrors()
     * @see getFirstErrors()
     */
    public function getFirstError($attribute)
    {
        return isset($this->errors[$attribute]) ? reset($this->errors[$attribute]) : null;
    }

    /**
     * Adds a new error to the specified attribute.
     * @param string $attribute attribute name
     * @param string $error new error message
     */
    public function addError($attribute, $error = '')
    {
        $this->errors[$attribute][] = $error;
    }

    /**
     * Adds a list of errors.
     * @param array $items a list of errors. The array keys must be attribute names.
     * The array values should be error messages. If an attribute has multiple errors,
     * these errors must be given in terms of an array.
     * You may use the result of [[getErrors()]] as the value for this parameter.
     * @since 2.0.2
     */
    public function addErrors(array $items)
    {
        foreach ($items as $attribute => $errors) {
            if (is_array($errors)) {
                foreach ($errors as $error) {
                    $this->addError($attribute, $error);
                }
            } else {
                $this->addError($attribute, $errors);
            }
        }
    }

    /**
     * Removes errors for all attributes or a single attribute.
     * @param string $attribute attribute name. Use null to remove errors for all attribute.
     */
    public function clearErrors($attribute = null)
    {
        if ($attribute === null) {
            $this->errors = [];
        } else {
            unset($this->errors[$attribute]);
        }
    }


    /**
     * Returns the scenario that this model is used in.
     *
     * Scenario affects how validation is performed and which attributes can
     * be massively assigned.
     *
     * @return string the scenario that this model is in. Defaults to [[SCENARIO_DEFAULT]].
     */
    public function getScenario()
    {
        return $this->scenario;
    }

    /**
     * Sets the scenario for the model.
     * Note that this method does not check if the scenario exists or not.
     * The method [[validate()]] will perform this check.
     * @param string $value the scenario that this model is in.
     */
    public function setScenario($value)
    {
        $this->scenario = $value;
    }

    /**
     * Returns the attribute names that are safe to be massively assigned in the current scenario.
     * @return string[] safe attribute names
     */
    public function safeAttributes()
    {
        $scenario = $this->getScenario();
        $scenarios = $this->scenarios();
        if (!isset($scenarios[$scenario])) {
            return [];
        }
        $attributes = [];
        foreach ($scenarios[$scenario] as $attribute) {
            if ($attribute[0] !== '!') {
                $attributes[] = $attribute;
            }
        }

        return $attributes;
    }

    /**
     * Returns the attribute names that are subject to validation in the current scenario.
     * @return string[] safe attribute names
     */
    public function activeAttributes()
    {
        $scenario = $this->getScenario();
        $scenarios = $this->scenarios();
        if (!isset($scenarios[$scenario])) {
            return [];
        }
        $attributes = $scenarios[$scenario];
        foreach ($attributes as $i => $attribute) {
            if ($attribute[0] === '!') {
                $attributes[$i] = substr($attribute, 1);
            }
        }
        return $attributes;
    }

    /**
     * Returns a value indicating whether a method is defined.
     *
     * The default implementation is a call to php function `method_exists()`.
     * You may override this method when you implemented the php magic method `__call()`.
     * @param string $name the method name
     * @return boolean whether the method is defined
     */
    public function hasMethod($name)
    {
        return method_exists($this, $name);
    }
}
