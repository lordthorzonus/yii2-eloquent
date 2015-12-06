<?php

namespace leinonen\Yii2Eloquent\tests\Eloquent;

use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Schema\Blueprint;
use leinonen\Yii2Eloquent\Tests\Helpers\Order;
use leinonen\Yii2Eloquent\Tests\TestCase;
use yii\widgets\ActiveForm;

class ModelTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->mockWebApplication();
        Manager::schema()->dropIfExists('order');
        Manager::schema()->create('order', function (Blueprint $table) {
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

        $order->address = 'Test address';
        $this->assertTrue($order->validate());

        $order->address = '12';
        $this->assertFalse($order->validate());
    }

    /** @test */
    public function it_can_be_fed_to_active_form_normally()
    {
        ob_start();
        $form = new ActiveForm(['action' => '/something']);
        $order = new Order();
        $order->address= 'Test';


        echo $form->field($order, 'address');
        $content = ob_get_clean();

        $this->assertContains("id=\"order-address\" class=\"form-control\" name=\"Order[address]\" value=\"Test\"", $content);
        $this->assertContains("<label class=\"control-label\" for=\"order-address\">Address</label>", $content);

        $secondOrder = new Order();
        $secondOrder->validate();

        echo $form->field($secondOrder, 'address');

        $content = ob_get_clean();

        var_dump($content); die;

    }

}
