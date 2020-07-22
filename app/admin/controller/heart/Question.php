<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/7/20 0020
 * Time: 17:15
 */

namespace app\admin\controller\heart;


use app\admin\service\heart\HeartQuestionService;
use app\BaseController;

class Question extends BaseController
{
    public function __construct(HeartQuestionService $service)
    {
        $this->service = $service;
    }

    public function list($pageNo, $pageSize)
    {
        $send = $this->service->getList((int) $pageNo, (int) $pageSize);

        return $this->sendSuccess($send);
    }

    public function delete($id)
    {
        if ($this->service->remove($id) === false) {
            return $this->sendError('操作失败');
        }

        return $this->sendSuccess();
    }
}
