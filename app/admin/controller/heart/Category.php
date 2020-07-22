<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/7/20 0020
 * Time: 18:19
 */

namespace app\admin\controller\heart;

use app\admin\request\heart\HeartCategoryRequest;
use app\admin\service\heart\HeartCategoryService;
use app\BaseController;

class Category extends BaseController
{
    public function __construct(HeartCategoryService $service)
    {
        $this->service = $service;
    }

    public function list($pageNo, $pageSize)
    {
        $send = $this->service->getList((int)$pageNo, (int)$pageSize);

        return $this->sendSuccess($send);
    }

    public function add() {
        if ($this->service->create(request()->post()) === false) {
            return $this->sendError($this->service->getError());
        }

        return $this->sendSuccess();
    }

    public function update($id, HeartCategoryRequest $request)
    {
        if (false === $this->service->renew($id, $request->param('name'))) {
            return $this->sendError($this->service->getError());
        }

        return $this->sendSuccess();
    }

    public function delete($id)
    {
        if ($this->service->remove($id) === false) {
            return $this->sendError('操作失败');
        }

        return $this->sendSuccess();
    }
}
