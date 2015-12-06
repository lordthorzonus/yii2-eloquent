<?php
/**
 * This view is used by MigrateController.php
 * The following variables are available in this view:.
 */

/* @var $className string the new migration class name */

echo "<?php\n";
?>

use Illuminate\Database\Capsule\Manager;
use yii\db\MigrationInterface;

class <?php echo $className ?> implements MigrationInterface
{
public function up()
{

}

public function down()
{
echo "<?php echo $className ?> cannot be reverted.\n";

return false;
}


}

