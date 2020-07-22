<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/7/20 0020
 * Time: 17:16
 */

namespace app\admin\service\heart;


use app\BaseService;
use app\common\model\heart\HeartQuestion;

class HeartQuestionService extends BaseService
{
    public function __construct(HeartQuestion $exam)
    {
        $this->model = $exam;
    }

    public function getList($pageNo, $pageSize)
    {
        $count = $this->model->where('status', 1)->count();

        $carousels = $this->model
            ->field(['heart_question.id', 'heart_question.thumb', 'heart_question.name',
                'heart_question.is_recommend', 'heart_question.create_time', 'heart_category.name' => 'category_name'])
            ->where('status', 1)
            ->leftJoin('heart_category', 'heart_category.id = heart_question.cid')
            ->order('heart_question.is_recommend desc, heart_question.update_time desc, heart_question.create_time desc')
            ->select();

        $totalPage = ceil($count / $pageSize);

        return [
            'data'       => $carousels,
            'pageSize'   => $pageSize,
            'pageNo'     => $pageNo,
            'totalPage'  => $totalPage,
            'totalCount' => sizeof($carousels),
        ];
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
