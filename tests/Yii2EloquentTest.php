<?php

namespace leinonen\Yii2Eloquent\Tests;

use Exception;
use Illuminate\Database\Capsule\Manager;
use leinonen\Yii2Eloquent\Tests\Helpers\Order;
use leinonen\Yii2Eloquent\Yii2Eloquent;
use Yii;

class Yii2EloquentTest extends TestCase
{
    /**
     * @var Manager
     */
    protected $db;

    public function setUp()
    {
        $databaseConfig = [
            'bootstrap' => ['db'],
            'components' => [
                'db' => [
                    'class'     => Yii2Eloquent::class,
                    'driver'    => getenv('DB_DRIVER'),
                    'database'  => getenv('DB_NAME'),
                    'prefix'    => '',
                    'host'      => getenv('DB_HOST'),
                    'username'  => getenv('DB_USERNAME'),
                    'password'  => getenv('DB_PASSWORD'),
                    'charset'   => 'utf8',
                    'collation' => 'utf8_unicode_ci',

                ]
            ]
        ];

        $this->mockWebApplication($databaseConfig);

        $this->db = Yii::$app->db;
    }

    public function tearDown()
    {
        $this->db->schema()->dropIfExists('user');
        $this->db->schema()->dropIfExists('order');
        parent::tearDown();
    }

    /** @test */
    public function it_bootstraps_itself_into_yii_like_yii_component_should()
    {
        $this->assertInstanceOf(Manager::class, Yii::$app->db);
        $this->assertInstanceOf(Manager::class, Yii::$app->getDb());
    }

    /** @test */
    public function the_schema_builder_is_usable()
    {
        $this->db->schema()->create('user', function($table){
            $table->increments('id');
            $table->string('email')->unique();
            $table->timestamps();
        });

        $tableMetaData = $this->getConnection()->createQueryTable('user', 'SELECT * FROM user')->getTableMetaData();

        $this->assertEquals([
            'id',
            'email',
            'created_at',
            'updated_at'
        ], $tableMetaData->getColumns());

        $this->db->schema()->dropIfExists('user');

        // The table is deleted so it should throw now an exception
        $this->setExpectedException(Exception::class);
        $this->getConnection()->createQueryTable('user', 'SELECT * FROM user')->getTableMetaData();
    }
    
    /** @test */
    public function eloquent_works_as_expected()
    {
        $this->createAndSeedOrdersTable();
        $this->assertCount(2,Order::all());

        $model = Order::where('name', 'Test address')->firstOrFail();
        $this->assertEquals('Test address', $model->name);

        Order::create(['name' => 'New name']);
        $this->assertCount(3,Order::all());
        $model = Order::where('name', 'New name')->firstOrFail();
        $this->assertEquals('New name', $model->name);
    }

    /**
     * Creates and seeds the order table
     */
    protected function createAndSeedOrdersTable()
    {
        /**
         * @var $db Manager
         */
        $this->db = Yii::$app->db;

        $this->db->schema()->create('order', function($table){
            $table->increments('id');
            $table->string('name')->unique();
            $table->timestamps();
        });

        Order::create(['name' => 'Test address']);
        Order::create(['name' => 'Another test Address']);
    }




    
    
}