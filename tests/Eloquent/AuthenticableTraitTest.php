<?php

namespace leinonen\Yii2Eloquent\tests\Eloquent;

use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Schema\Blueprint;
use leinonen\Yii2Eloquent\Tests\Helpers\User;
use leinonen\Yii2Eloquent\Tests\TestCase;

class AuthenticableTraitTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->mockWebApplication();

        Manager::schema()->dropIfExists('users');
        Manager::schema()->create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('username');
            $table->string('auth_key');
            $table->timestamps();
        });
    }

    /** @test */
    public function it_implements_identity_interface_correctly()
    {
        $userId = Manager::table('users')->insertGetId(['username' => 'test', 'auth_key' => 'foobar']);

        $user = User::findIdentity($userId);

        $this->assertEquals($userId, $user->getId());
        $this->assertEquals($user->getAuthKey(), 'foobar');

        $this->assertTrue($user->validateAuthKey('foobar'));
        $this->assertFalse($user->validateAuthKey('foobaz'));
    }
}
