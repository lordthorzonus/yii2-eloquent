<?php

namespace leinonen\Yii2Eloquent\Tests\Helpers;

use leinonen\Yii2Eloquent\Fixtures\EloquentFixture;

class OrderFixture extends EloquentFixture
{
    public $modelClass = Order::class;

    public function getData()
    {
        return [
            'example1' => [
                'address' => 'Test address',
            ],
        ];
    }
}
