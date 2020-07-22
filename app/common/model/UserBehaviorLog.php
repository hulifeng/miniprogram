<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/7/21 0021
 * Time: 19:03
 */

namespace app\common\model;

use TAnt\Abstracts\AbstractModel;

class UserBehaviorLog extends AbstractModel
{
    protected $table = 'user_behavior_log';

    protected $autoWriteTimestamp = false;
}
