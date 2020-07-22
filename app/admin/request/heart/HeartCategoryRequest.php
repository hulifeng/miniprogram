<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/7/20 0020
 * Time: 19:13
 */

namespace app\admin\request\heart;

use app\BaseRequest;

class HeartCategoryRequest extends BaseRequest
{
    protected $rule = [
        'name'  => 'require|unique:heart_category'
    ];

    protected $message = [
        'name.require'  => '分类名称必填',
        'name.unique' => '分类名称重复'
    ];

    protected $scene = [
        'create' => ['name']
    ];
}
