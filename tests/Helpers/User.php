<?php


namespace leinonen\Yii2Eloquent\Tests\Helpers;


use leinonen\Yii2Eloquent\Eloquent\AuthenticatableTrait;
use leinonen\Yii2Eloquent\Eloquent\Model;
use yii\web\IdentityInterface;


class User extends Model implements IdentityInterface
{
    use AuthenticatableTrait;
}