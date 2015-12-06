<?php

namespace leinonen\Yii2Eloquent\Tests\Helpers;

use leinonen\Yii2Eloquent\MigrateController;

/**
 * MigrateController that writes output via echo instead of using output stream. Allows buffering and checking buffer for tests.
 */
class BufferableMigrateController extends MigrateController
{
    /**
     * {@inheritdoc}
     */
    public function stdout($string)
    {
        echo $string;
    }
}
