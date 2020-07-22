<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/7/20 0020
 * Time: 18:19
 */

namespace app\admin\service\heart;

use app\BaseService;
use app\common\model\heart\HeartCategory;

class HeartCategoryService extends BaseService
{
    public function __construct(HeartCategory $category)
    {
        $this->model = $category;
    }

    public function getList($pageNo, $pageSize)
    {
        $count = $this->model->count();

        $categories = $this->model
            ->field(['id', 'name'])
            ->page($pageNo)
            ->limit($pageSize)
            ->order('sort desc, update_time desc, create_time desc')
            ->select();

        $totalPage = ceil($count / $pageSize);

        return [
            'data'       => $categories,
            'pageSize'   => $pageSize,
            'pageNo'     => $pageNo,
            'totalPage'  => $totalPage,
            'totalCount' => sizeof($categories),
        ];
    }

    public function create(array $data)
    {
        return $this->model->save($data);
    }

    public function renew($id, $name)
    {
        $category = $this->find($id);

        return $category->save(['name' => $name]);
    }

    public function remove($id)
    {
        $ids = explode(',', $id);

        if (empty($ids)) {
            return false;
        }

        $this->model->whereIn('id', $ids)->select()->delete();

        return true;
    }
}
