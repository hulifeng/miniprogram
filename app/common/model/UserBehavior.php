<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/7/21 0021
 * Time: 19:02
 */

namespace app\common\model;

use TAnt\Abstracts\AbstractModel;

class UserBehavior extends AbstractModel
{
    protected $table = 'user_behavior';

    protected $autoWriteTimestamp = false;
}
