<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/7/20 0020
 * Time: 13:04
 */

namespace app\admin\service\heart;

use app\BaseService;
use app\common\model\heart\HeartCarousel;

class HeartCarouselService extends BaseService
{
    public function __construct(HeartCarousel $carousel)
    {
        $this->model = $carousel;
    }

    public function getList($pageNo, $pageSize)
    {
        $count = $this->model->where('status', 1)->count();

        $carousels = $this->model->where('status', 1)->select();

        $totalPage = ceil($count / $pageSize);

        return [
            'data'       => $carousels,
            'pageSize'   => $pageSize,
            'pageNo'     => $pageNo,
            'totalPage'  => $totalPage,
            'totalCount' => sizeof($carousels),
        ];
    }
}
