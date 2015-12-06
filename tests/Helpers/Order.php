<?php

namespace leinonen\Yii2Eloquent\Tests\Helpers;

use leinonen\Yii2Eloquent\Eloquent\Model;

class Order extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'order';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name'];

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['address', 'required'],
            ['address', 'string', 'min' => 3],
        ];
    }
}
