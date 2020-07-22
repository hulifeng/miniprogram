<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/7/20 0020
 * Time: 12:55
 */

namespace app\admin\controller\heart;

use app\admin\service\heart\HeartCarouselService;
use app\BaseController;

class Carousel extends BaseController
{
    public function __construct(HeartCarouselService $service)
    {
        $this->service = $service;
    }

    public function list($pageNo, $pageSize = 10)
    {
        $send = $this->service->getList((int) $pageNo, (int) $pageSize);

        return $this->sendSuccess($send);
    }
}
