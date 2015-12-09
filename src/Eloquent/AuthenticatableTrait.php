<?php

namespace leinonen\Yii2Eloquent\Eloquent;

use yii\base\NotSupportedException;

trait AuthenticatableTrait
{
    /**
     * @see \yii\web\IdentityInterface::findIdentity()
     */
    public static function findIdentity($id)
    {
        return static::findOrFail($id);
    }

    /**
     * @see \yii\web\IdentityInterface::findIdentityByAccessToken()
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * @see \yii\web\IdentityInterface::getId()
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @see \yii\web\IdentityInterface::getAuthKey()
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @see \yii\web\IdentityInterface::validateAuthKey()
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }
}
