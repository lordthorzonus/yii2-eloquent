<?php

namespace leinonen\Yii2Eloquent\Eloquent;

use yii\base\DynamicModel;

class DynamicModelAdapter extends DynamicModel
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
            $scenarios[ $definedScenarioName ] = $attributes;
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

}
