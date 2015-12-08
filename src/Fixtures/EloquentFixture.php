<?php

namespace leinonen\Yii2Eloquent\Fixtures;

use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Eloquent\Model;
use Yii;
use yii\base\ArrayAccessTrait;
use yii\base\InvalidConfigException;
use yii\test\Fixture;

class EloquentFixture extends Fixture implements \IteratorAggregate, \ArrayAccess, \Countable
{
    use ArrayAccessTrait;

    /**
     * @var Manager
     */
    protected $db;

    /**
     * @var string the Eloquent model class associated with this fixture.
     */
    public $modelClass;

    /**
     * @var array the data rows. Each array element represents one row of data (column name => column value).
     */
    public $data = [];

    /**
     * @var string|bool the file path or path alias of the data file that contains the fixture data
     * to be returned by [[getData()]]. You can set this property to be false to prevent loading any data.
     */
    public $dataFile;

    /**
     * @var Model[] the loaded Eloquent models
     */
    protected $models = [];

    /**
     * @var string the name of the table that this fixture is associated with
     */
    protected $tableName;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        $this->db = Yii::$app->db;
        if (! isset($this->modelClass)) {
            throw new InvalidConfigException('"modelClass" must be set.');
        }
    }

    /**
     * {@inheritdoc]
     */
    public function load()
    {
        $this->resetTable();
        $this->data = [];
        $tableName = $this->getTableName();

        /* @var $modelClass Model*/
        $modelClass = $this->modelClass;
        $primaryKeyName = with(new $modelClass)->getKeyName();

        foreach ($this->getData() as $alias => $row) {
            $primaryKey = $this->db->table($tableName)->insertGetId($row);
            $primaryKey = [$primaryKeyName => $primaryKey];
            $this->data[$alias] = array_merge($row, $primaryKey);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function unload()
    {
        parent::unload();
        $this->data = [];
        $this->models = [];
    }

    /**
     * Returns the Eloquent model by the specified model name.
     * A model name is the key of the corresponding data row in [[data]].
     * @param string $name the model name.
     *
     * @return null|\Illuminate\Database\Eloquent\Model the Eloquent model, or null if the model cannot be found in the database
     * @throws \yii\base\InvalidConfigException if [[modelClass]] is not set.
     */
    public function getModel($name)
    {
        if (! isset($this->data[$name])) {
            return;
        }
        if (array_key_exists($name, $this->models)) {
            return $this->models[$name];
        }

        if ($this->modelClass === null) {
            throw new InvalidConfigException('The "modelClass" property must be set.');
        }

        /* @var $modelClass Model*/
        $modelClass = $this->modelClass;
        $primaryKeyName = with(new $modelClass)->getKeyName();

        $row = $this->data[$name];
        $primaryKey = isset($row[$primaryKeyName]) ? $row[$primaryKeyName] : null;

        return $this->models[$name] = $modelClass::find($primaryKey);
    }

    /**
     * Returns the fixture data
     * @see \yii\test\BaseActiveFixture::getData()
     */
    protected function getData()
    {
        if ($this->dataFile === null) {
            $class = new \ReflectionClass($this);
            $dataFile = dirname($class->getFileName()) . '/data/' . $this->getTableName() . '.php';
        } else {
           $dataFile = Yii::getAlias($this->dataFile);
        }

        return is_file($dataFile) ? require($dataFile) : [];
    }

    /**
     * @see \yii\test\ActiveFixture::resetTable()
     */
    protected function resetTable()
    {
        $this->db->table($this->getTableName())->truncate();
    }

    /**
     * Returns the table name used by the defined model in $modelClass
     *
     * @return string
     */
    protected function getTableName()
    {
        if ($this->tableName != null) {
            return $this->tableName;
        }

        /* @var $modelClass Model */
        $modelClass = $this->modelClass;
        $tableName = with(new $modelClass)->getTable();

        return $tableName;
    }
}
