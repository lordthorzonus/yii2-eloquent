<?php


namespace leinonen\Yii2Eloquent\Tests\Fixtures;


use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Schema\Blueprint;
use leinonen\Yii2Eloquent\Tests\Helpers\Order;
use leinonen\Yii2Eloquent\Tests\Helpers\OrderFixture;
use leinonen\Yii2Eloquent\Tests\TestCase;
use leinonen\Yii2Eloquent\Yii2Eloquent;
use Yii;
use yii\test\FixtureTrait;

class EloquentFixtureTest extends TestCase
{
    use FixtureTrait;

    /**
     * @var Manager
     */
    protected $db;

    public function setUp()
    {
        parent::setUp();
        $this->mockWebApplication();
        $this->db = Yii::$app->db;
        $this->db->schema()->dropIfExists('order');
        $this->db->schema()->create('order', function(Blueprint $table){
            $table->increments('id');
            $table->string('address');
            $table->timestamps();
        });

        $this->unloadFixtures();
        $this->loadFixtures();
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function fixtures()
    {
        return [
            'order' => OrderFixture::class
        ];
    }

    /** @test */
    public function fixtures_are_usable_in_test_like_in_yii2_they_should_be()
    {
        $this->assertInstanceOf(Order::class, $this->getFixture('order')->getModel('example1'));
        $this->assertEquals('Test address', $this->getFixture('order')->getModel('example1')->address);
        $this->assertEquals('Test address', $this->getFixture('order')['example1']['address']);
    }

    /** @test */
    public function fixtures_are_loaded_into_database_as_expected()
    {
        $order = Order::where(['address' => 'Test address'])->firstOrFail();
        $this->assertEquals(count($this->getFixture('order')), count(Order::all()));
        $this->assertInstanceOf(Order::class, $order);
        $this->assertEquals('Test address', $order->address);
    }

}