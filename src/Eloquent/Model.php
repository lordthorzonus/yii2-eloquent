<?php

namespace leinonen\Yii2Eloquent\Eloquent;

use Illuminate\Database\Eloquent\Model as Eloquent;

/**
 * Class Model
 * An alternative Eloquent base class that brings some Yii functionality to Eloquent.
 * Including validation and the ability to feed it to Yii widgets.
 *
 * @package leinonen\Yii2Eloquent\Eloquent
 */
class Model extends Eloquent
{
    use EloquentYiiModelAdapterTrait;

    /**
     * The name of the default scenario.
     */
    const SCENARIO_DEFAULT = 'default';

    /**
     * @event an event raised at the beginning of validate().
     */
    const EVENT_BEFORE_VALIDATE = 'beforeValidate';

    /**
     * @event an event raised at the end of validate()
     */
    const EVENT_AFTER_VALIDATE = 'afterValidate';

    /**
     * {@inheritdoc]
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->dummyModel = new DynamicModelAdapter($this->getAttributesForYiiModels());
        $this->dummyModel->setRules($this->rules());
        $this->dummyModel->setScenarios($this->scenarios());
    }

    /**
     * @see \yii\base\Model::rules()
     */
    public function rules()
    {
        return [];
    }

    /**
     * @see \yii\base\Model::scenarios()
     */
    public function scenarios()
    {
        return [];
    }

    /**
     * @see \yii\base\model::attributeLabels()
     */
    public function attributeLabels()
    {
        return [];
    }

    /**
     * @see \yii\base\model::attributeHints()
     */
    public function attributeHints()
    {
        return [];
    }

    /**
     * @see \yii\base\model::beforeValidate()
     */
    public function beforeValidate()
    {
        $this->fireModelEvent(self::EVENT_BEFORE_VALIDATE);
    }

    /**
     * @see \yii\base\model::afterValidate()
     */
    public function afterValidate()
    {
        $this->fireModelEvent(self::EVENT_AFTER_VALIDATE);
    }

    /**
     * @see \yii\base\model::getAttributes()
     */
    protected function getAttributesForYiiModels($names = null, $except = [])
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

    /**
     * @see \yii\base\model::validate()
     */
    public function validate($attributeNames = null, $clearOnError = true)
    {
        $this->beforeValidate();
        $this->dummyModel->setAttributes($this->getAttributesForYiiModels());
        $validation = $this->dummyModel->validate($attributeNames, $clearOnError);

        $this->afterValidate();

        return $validation;
    }

}
