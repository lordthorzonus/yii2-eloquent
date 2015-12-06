<?php

namespace leinonen\Yii2Eloquent\Tests;

use leinonen\Yii2Eloquent\Yii2Eloquent;
use PDO;
use yii\di\Container;
use yii\helpers\ArrayHelper;
use Yii;

abstract class TestCase extends \PHPUnit_Extensions_Database_TestCase
{
    private $databaseConfig;

    public function setUp()
    {
        $this->databaseConfig = [
            'class' => Yii2Eloquent::class,
            'driver' => getenv('DB_DRIVER'),
            'database' => getenv('DB_NAME'),
            'prefix' => '',
            'host' => getenv('DB_HOST'),
            'username' => getenv('DB_USERNAME'),
            'password' => getenv('DB_PASSWORD'),
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
        ];

        //Don't setup the parent so we can control the database with Illuminates schema builder
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->destroyApplication();
    }

    /**
     * {@inheritdoc}
     */
    public function getConnection()
    {
        $driver = getenv('DB_DRIVER');
        $db = getenv('DB_NAME');
        $host = getenv('DB_HOST');
        $dsn = "{$driver}:dbname={$db};host={$host}";
        $pdo = new PDO($dsn, getenv('DB_USERNAME'), getenv('DB_PASSWORD'));

        return $this->createDefaultDBConnection($pdo, $db);
    }

    /**
     * {@inheritdoc}
     */
    public function getDataSet()
    {
        return $this->createXMLDataSet(dirname(__FILE__) . '/_files/schema.xml');
    }

    /**
     * Mocks an Yii web application.
     *
     * @param array $config
     * @param string $appClass
     */
    protected function mockWebApplication($config = [], $appClass = '\yii\web\Application')
    {
        new $appClass(ArrayHelper::merge([
            'id' => 'testapp',
            'basePath' => __DIR__,
            'vendorPath' => dirname(__DIR__) . '/vendor',
            'bootstrap' => ['db'],
            'components' => [
                'request' => [
                    'cookieValidationKey' => 'iAmASecretKey',
                    'scriptFile' => __DIR__ . '/index.php',
                    'scriptUrl' => '/index.php',
                ],
                'db' => $this->databaseConfig,
                'assetManager' => [
                    'bundles' => false,
                ],
            ],
        ], $config));
    }

    /**
     * Mocks an Yii console application.
     * @param array $config
     * @param string $appClass
     */
    protected function mockConsoleApplication($config = [], $appClass = '\yii\console\Application')
    {
        new $appClass(ArrayHelper::merge([
            'id' => 'testapp',
            'basePath' => __DIR__,
            'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
            'bootstrap' => ['db'],
            'components' => [
                'db' => $this->databaseConfig,
            ],
        ], $config));
    }

    /**
     * Destroys application in Yii::$app.
     */
    protected function destroyApplication()
    {
        Yii::$app = null;
        Yii::$container = new Container();
    }
}
