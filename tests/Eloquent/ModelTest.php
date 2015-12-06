<?php


namespace leinonen\Yii2Eloquent\tests\Eloquent;


use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Schema\Blueprint;
use leinonen\Yii2Eloquent\Tests\Helpers\Order;
use leinonen\Yii2Eloquent\Tests\TestCase;

class ModelTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->mockWebApplication();
        Manager::schema()->dropIfExists('order');
        Manager::schema()->create('order', function(Blueprint $table){
            $table->increments('id');
            $table->string('address')->unique();
            $table->timestamps();
        });
    }
    
    /** @test */
    public function it_validates_as_a_basic_yii_model_should()
    {
        $order = new Order();
       $this->assertFalse($order->validate());

        $order->address = "Test address";
        $this->assertTrue($order->validate());

        $order->address = "12";
        $this->assertFalse($order->validate());

    }
}