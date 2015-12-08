<?php

namespace leinonen\Yii2Eloquent\Eloquent;

use ReflectionClass;
use yii\helpers\Inflector;

/**
 * Class EloquentYiiModelAdapterTrait
 * Trait that delegates all method calls that yii widgets / validation makes to the dummy DynamicModel.
 */
trait EloquentYiiModelAdapterTrait
{
    /**
     * @see \yii\base\model::attributeLabels()
     */
    abstract public function attributeLabels();

    /**
     * @see \yii\base\model::attributeHints()
     */
    abstract public function attributeHints();

    /**
     * @see \yii\base\model::beforeValidate()
     */
    abstract public function beforeValidate();

    /**
     * @see \yii\base\model::afterValidate()
     */
    abstract public function afterValidate();

    /**
     * @see \yii\base\model::scenarios()
     */
    abstract public function scenarios();

    /**
     * @see \yii\base\model::rules()
     */
    abstract public function rules();

    /**
     * @var DynamicModelAdapter
     */
    protected $dummyModel;

    /**
     * @see \yii\base\model::attributes()
     */
    public function attributes()
    {
        return $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable());
    }

    /**
     * @see \yii\base\model::getAttributeLabels()
     */
    public function getAttributeLabel($attribute)
    {
        $labels = $this->attributeLabels();

        return isset($labels[$attribute]) ? $labels[$attribute] : Inflector::camel2words($attribute, true);
    }

    /**
     * @see \yii\base\model::isAttributeRequired()
     */
    public function isAttributeRequired($attribute)
    {
        return $this->dummyModel->isAttributeRequired($attribute);
    }

    /**
     * @see \yii\base\model::getAttributeHint()
     */
    public function getAttributeHint($attribute)
    {
        $hints = $this->attributeHints();

        return isset($hints[$attribute]) ? $hints[$attribute] : '';
    }

    /**
     * @see \yii\base\model::getFirstError()
     */
    public function getFirstError($attribute)
    {
        return $this->dummyModel->getFirstError($attribute);
    }

    /**
     * @see \yii\base\model::getErrors()
     */
    public function getErrors($attribute = null)
    {
        return $this->dummyModel->getErrors($attribute);
    }

    /**
     * @see \yii\base\model::addError()
     */
    public function addError($attribute, $error = '')
    {
        return $this->dummyModel->addError($attribute, $error);
    }

    /**
     * @see \yii\base\model::addErrors()
     */
    public function addErrors(array $items)
    {
        return $this->dummyModel->addErrors($items);
    }

    /**
     * @see \yii\base\model::hasErrors()
     */
    public function hasErrors($attribute)
    {
        return $this->dummyModel->hasErrors($attribute);
    }

    /**
     * @see \yii\base\model::activeAttributes()
     */
    public function activeAttributes()
    {
        return $this->dummyModel->activeAttributes();
    }

    /**
     * @see \yii\base\model::getActiveValidators()
     */
    public function getActiveValidators($attribute = null)
    {
        return $this->dummyModel->getActiveValidators($attribute);
    }

    /**
     * @see \yii\base\model::formName()
     */
    public function formName()
    {
        $reflector = new ReflectionClass($this);

        return $reflector->getShortName();
    }
}
