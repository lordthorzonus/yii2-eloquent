<?php

namespace leinonen\Yii2Eloquent\Eloquent;

use yii\base\Model as YiiBaseModel;

class YiiDynamicBaseModelAdapter extends YiiBaseModel
{
    /**
     * @see YiiBaseModel::rules()
     */
    protected $rules = [];

    /**
     * @see YiiBaseModel::scenarios()
     */
    protected $scenarios;

    /**
     * @var array the properties used by this model
     */
    protected $properties;

    /**
     * Initates a new YiiDynamicBaseModelAdapter.
     *
     * @param array $properties the properties this model consists of eq [ 'myProperty' => 'value ]
     * @param array $config
     */
    public function __construct(array $properties, $config = [])
    {
        $this->properties = $properties;
        parent::__construct($config);
    }

    /**
     * @return array
     */
    public function rules()
    {
        return $this->rules;
    }

    /**
     * Sets the rules used for validation.
     *
     * @param array $rules
     */
    public function setRules(array $rules)
    {
        $this->rules = $rules;
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();

        $definedScenarios = $this->scenarios;

        foreach ($definedScenarios as $definedScenarioName => $attributes) {
            $scenarios[$definedScenarioName] = $attributes;
        }

        return $scenarios;
    }

    /**
     * Sets the scenarios for this model.
     *
     * @param array $scenarios
     */
    public function setScenarios(array $scenarios)
    {
        $this->scenarios = $scenarios;
    }

    /**
     * {@inheritdoc}
     */
    public function attributes()
    {
        return array_keys($this->properties);
    }

    /**
     * {@inheritdoc}
     */
    public function __get($name)
    {
        if (array_key_exists($name, $this->properties)) {
            return $this->properties[$name];
        }

        return parent::__get($name);
    }
}
