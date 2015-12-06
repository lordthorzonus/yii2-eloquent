<?php


namespace leinonen\Yii2Eloquent\Eloquent;


use ReflectionClass;
use yii\helpers\Inflector;

trait EloquentYiiModelAdapterTrait
{
    abstract public function attributeLabels();

    abstract public function attributeHints();

    abstract public function beforeValidate();

    abstract public function afterValidate();

    /**
     * @return DynamicModelAdapter
     */
    abstract protected function getDummyDynamicModel();

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

    public function isAttributeRequired($attribute)
    {
        $dummyModel = $this->getDummyDynamicModel();

        return $dummyModel->isAttributeRequired($attribute);
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
     * @see \yii\base\Model::getFirstError()
     *
     * @param $attribute
     *
     * @return string
     */
    public function getFirstError($attribute)
    {
        $dummyModel = $this->getDummyDynamicModel();
        return $dummyModel->getFirstError($attribute);
    }

    public function hasErrors($attribute)
    {
        $dummyModel = $this->getDummyDynamicModel();
        return $dummyModel->hasErrors($attribute);
    }

    public function activeAttributes()
    {
        $dummyModel = $this->getDummyDynamicModel();
        return $dummyModel->activeAttributes();
    }

    public function getActiveValidators($attribute = null)
    {
        $dummyModel = $this->getDummyDynamicModel();
        return $dummyModel->getActiveValidators($attribute);
    }

    /**
     * Delecates the model validation to base yii objects.
     * @see \yii\base\Model::validate()
     * @param null $attributeNames
     * @param bool|true $clearOnError
     *
     * @return bool
     */
    public function validate($attributeNames = null, $clearOnError = true)
    {
        $this->beforeValidate();

        $dummyModel = $this->getDummyDynamicModel();
        $validation = $dummyModel->validate($attributeNames, $clearOnError);

        $this->afterValidate();

        return $validation;
    }

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
}

