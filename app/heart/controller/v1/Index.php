<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/7/20 0020
 * Time: 23:10
 */

namespace app\heart\controller\v1;

use app\BaseController;
use app\common\model\Ad;
use app\common\model\heart\HeartCategory;
use app\common\model\heart\HeartExamResult;
use app\common\model\heart\HeartQuestion;
use app\common\model\heart\HeartQuestionItem;
use app\common\model\heart\HeartUser;
use app\common\model\Search;
use app\common\model\Share;
use app\common\model\ShareLog;
use app\common\model\UserBehavior;
use app\common\model\UserBehaviorLog;
use app\Request;
use GuzzleHttp\Client;
use think\Exception;
use think\facade\Db;

class Index extends BaseController
{
    // 首页
    public function index()
    {
        // 轮播图（最多三张）
        $carousel = HeartQuestion::field(['id', 'thumb' => 'image', 'name' => 'title'])
            ->where('is_carousel', 1)
            ->order('create_time desc')->limit(5)->select();

        // 随机两条记录
        $rand_two_question = HeartQuestion::field(['id', 'name', 'real_view', 'thumb'])
            ->orderRaw('rand()')->limit(2)->select();

        // 首页分类（最多四个分类）
        $category = HeartCategory::field(['id', 'name', 'icon'])->order('sort desc, create_time desc')->limit(4)->select();

        // 热门推荐（最多五个推荐）
        $hot_recommend = HeartQuestion::field(['q.id', 'q.name', 'q.thumb', 'c.name' => 'category_name'])
            ->alias('q')
            ->where('q.is_recommend', 1)
            ->leftJoin('heart_category c', 'q.cid = c.id')
            ->order('q.update_time desc')->limit(5)->select();

        // 精选测试
        $choice_question = HeartQuestion::
            field(['id', 'name', 'content', 'thumb', 'real_view'])
            ->where('is_recommend', 0)
            ->where('is_carousel', 0)
            ->order('real_view desc, create_time desc')
            ->limit(5)->select();

        return $this->sendSuccess([
            'carousel' => $carousel,
            'category' => $category,
            'rand_two_question' => $rand_two_question,
            'hot_recommend' => $hot_recommend,
            'choice_question' => $choice_question
        ]);
    }

    // 答题界面
    public function detail()
    {
        $id = request()->param('id');

        $field = [
            'name', 'content', 'thumb', 'atitle', 'adesc', 'btitle', 'bdesc',
            'ctitle', 'cdesc', 'dtitle', 'ddesc'
        ];

        $question = HeartQuestion::field($field)->find($id);

        $items = HeartQuestionItem::where('eid', $id)->select();

        $question_list = [];

        if (sizeof($items)) {
            foreach ($items as $key => $item) {
                $question_list[] = [
                    'questionID' => $item['id'],
                    'fldName' => $item['title'],
                    'fldAnswer' => null,
                    'QuestionOptionList' => [
                        [
                            'optionID' => $item['id']. '-one',
                            'fldOptionText' => $item['one'],
                            'fldOptionIndex' => 1
                        ],
                        [
                            'optionID' => $item['id']. '-two',
                            'fldOptionText' => $item['two'],
                            'fldOptionIndex' => 2
                        ],
                        [
                            'optionID' => $item['id']. '-three',
                            'fldOptionText' => $item['three'],
                            'fldOptionIndex' => 3
                        ]
                    ]
                ];
            }
        }

        return $this->sendSuccess([
            'question' => $question,
            'question_item' => $question_list,
            'normal_ad' => '3kkagdm5b6p1b10iqh',
            'reward_ad' => 'imnra7o9je68ifdsoe'
        ]);
    }

    // 排行榜
    public function ranking()
    {
        $rank = HeartExamResult::alias('hes')
            ->field([
                'COUNT(hes.uid)' => 'num',
                'hes.uid', 'hu.nickname',
                'hu.avatar'
            ])
        ->leftJoin('heart_user hu', 'hes.uid = hu.id')
        ->group('hes.uid')->order('num desc')->limit(15)->select()->toArray();

        $golden = $rank[0];
        $silver = $rank[1];
        $copper = $rank[2];
        unset($rank[0]);
        unset($rank[1]);
        unset($rank[2]);

        return $this->sendSuccess([
            'golden' => $golden,
            'silver' => $silver,
            'copper' => $copper,
            'other' => $rank
        ]);
    }

    // 分类
    public function category()
    {
        $categories = HeartCategory::alias('hc')
            ->field(['hc.id', 'hc.name', 'hc.sort', 'hq.name' => 'question_name',
                'hq.id' => 'question_id', 'hq.thumb' => 'icon', 'hq.fake_view' => 'cat'])
            ->leftJoin('heart_question hq', 'hc.id = hq.cid')
            ->where('hq.delete_time = 0')
            ->select()->toArray();

        $data = [];

        foreach ($categories as $category) {
            $data[$category['id']]['name'] = $category['name'];
            $data[$category['id']]['sort'] = $category['sort'];
            $data[$category['id']]['children'][] = [
                'id' => $category['question_id'],
                'name' => $category['question_name'],
                'icon' => $category['icon'],
                'cat' => $category['cat']
            ];
        }

        $sort = array_column($data, 'sort');

        array_multisort($sort, SORT_DESC, $data);

        return $this->sendSuccess($data);
    }

    // 我的测试
    public function record()
    {
        if (!request()->param('uid')) return $this->sendError('缺少参数');

        $sql = Db::table('heart_exam_result')->alias('her')->where('her.uid', request()->param('uid'))
            ->field(['her.eid', 'hq.name', 'hq.content', 'hq.thumb', 'hq.fake_view', 'her.create_time', 'her.atitle', 'her.adesc'])
            ->leftJoin('heart_question hq', 'hq.id = her.eid')
            ->order('her.create_time desc')
            ->limit(99999999999999)
            ->buildSql();

        $data = Db::table($sql . 't')->field([
            't.name', 't.eid', 't.content', 't.fake_view', 't.thumb', 'FROM_UNIXTIME(t.create_time, "%Y-%m-%d %H:%i:%s")' => 'create_time', 't.atitle', 't.adesc'
        ])->group('t.eid')
            ->order('t.create_time desc')
            ->select();

        return $this->sendSuccess($data);
    }

    // 记录答案
    public function recordQuestion()
    {
        try {
            $res = HeartExamResult::create(request()->except(['v']));

            // 更新题测试数量
            HeartQuestion::where('id', request()->param('eid'))->inc('fake_view')->inc('real_view')->update();

            return $this->sendSuccess($res);
        } catch (Exception $exception) {
            return $this->sendError('服务器异常');
        }
    }

    // 记录搜索词
    public function recordSearch()
    {
        try {
            $res = Search::create(request()->except(['v']));

            return $this->sendSuccess($res);
        } catch (Exception $exception) {
            return $this->sendError('服务器异常');
        }
    }

    // 随机搜索
    public function search()
    {
        $questions = HeartQuestion::field([
            'id', 'name', 'content', 'thumb', 'fake_view'
        ])->orderRaw('rand()')->limit(4)->select();

        return $this->sendSuccess($questions);
    }

    // 搜索关键词
    public function search_keyword()
    {
        $keyword = request()->param('keyword');

        $result = HeartQuestion::field([
            'id', 'name', 'thumb', 'fake_view'
        ])->where('name', 'like',"%$keyword%")->select();

        return $this->sendSuccess($result);
    }

    // 注册用户
    public function register(Request $request)
    {
        try {
            $code = $request->param('code');
            $anonymous_code = $request->param('anonymous_code');
            if (empty($code) && empty($anonymous_code)) return $this->sendError('缺少必传参数');
            $res_code = $this->code_auth($code, $anonymous_code);
            if ($res_code['error'] != 0) return $this->sendError('获取code失败');
            $user_id = HeartUser::where('openid', $res_code['openid'])->where('openid', '<>', '')->value('id');
            if (empty($user_id)) {
                $user_id = HeartUser::where('anonymous_openid', $res_code['anonymous_openid'])->where('anonymous_openid', '<>', '')->value('id');
            }
            $is_new = 0;
            $eid = 0;
            if ($user_id) {
                $user = HeartUser::find($user_id);
                $ip = $user->ip;
                $user->save(['openid' => $res_code['openid'], 'anonymous_openid' => $res_code['anonymous_openid'],
                             'province' => $request->param('province'),
                             'city' => $request->param('city')]);
            } else {
                $data = $request->except(['v', 'code', 'anonymous_code']);
                $data['system_info'] = json_encode($data['system_info'], JSON_UNESCAPED_SLASHES);
                $data['query'] = json_encode($data['query'], JSON_UNESCAPED_SLASHES);
                $data['openid'] = $res_code['openid'];
                $data['anonymous_openid'] = $res_code['anonymous_openid'];
                $data['ip'] = get_real_ip();
                $ip = $data['ip'];
                $data['create_time'] = $data['enter_time'];
                $data['precise_day'] = strtotime(date("Y-m-d", time()));
                if (isset($data['inviter_id'])) {
                    $acid = isset($data['acid']) ? $data['acid'] : 'A-' . uniqid();
                    $eid = $data['eid'];
                    unset($data['acid']);
                    unset($data['eid']);
                }
                $user = HeartUser::create($data);
                $is_new = 1;
            }
            if (isset($user->id)) {
                $user->save(['user_number' => '7' . sprintf('%06d', $user->id)]);
                if (isset($data['inviter_id'])) {
                    $share_id = Share::where('acid', $acid)->value('id');
                    if ($share_id) {
                        ShareLog::create([
                            'share_id' => $share_id,
                            'eid' => $eid,
                            'inviter_id' => $data['inviter_id'],
                            'invitee_id' => $user->id,
                            'is_reg' => $is_new ? 1 : 0
                        ]);
                    }
                }
            }
            if ($ip) {
                $this->bindArea($user->id, $ip);
            }
            return $this->sendSuccess([
                'user_id' => $user->id,
                'user_number' => $user->user_number,
                'key' => $res_code['session_key'],
                'is_new' => $is_new,
                'normal_ad' => '3kkagdm5b6p1b10iqh',
                'reward_ad' => 'imnra7o9je68ifdsoe'
            ]);
        } catch (\Exception $exception) {
            return $this->sendError('服务器异常');
        }
    }

    // 换取 openid
    public function code_auth($code, $anonymous_code)
    {
        try {
            $client = new Client();
            $data = ['appid' => config('heart.appid'), 'secret' => config('heart.secret'), 'code' => $code, 'anonymous_code' => $anonymous_code];
            $url = "https://developer.toutiao.com/api/apps/jscode2session";
            $response = $client->get($url, ['query' => $data]);
            return json_decode($response->getBody(), true);
        } catch (\Exception $exception) {
            return $this->sendError('服务器异常');
        }
    }

    // 绑定区域
    public function bindArea($id, $ip)
    {
        try {
            $url = 'https://restapi.amap.com/v3/ip?key=2004f145cf3a39a72e9ca70ca4b2a1dc&ip=' . $ip;
            $client = new Client();
            $response = $client->get($url);
            $responseJson = json_decode($response->getBody()->getContents(), true);
            if ($responseJson['status']) {
                $user = HeartUser::find($id);
                $user->save([
                    'province' => $responseJson['province'],
                    'city' => $responseJson['city'],
                    'adcode' => $responseJson['adcode'],
                ]);
            }
        } catch (Exception $exception) {
            return $this->sendError('绑定ip
            失败');
        }
    }

    // 记录用户行为日志
    public function user_behavior(Request $request)
    {
        try {
            $precise_day = strtotime(date("Y-m-d", time()));
            $precise_time = strtotime(date("Y-m-d H:i:s", time()));
            if ($request->param('init')) {
                $data = [
                    'user_id' => $request->param('user_id'),
                    'is_new' => $request->param('is_new'),
                    'enter_time' => $request->param('enter_time'),
                    'precise_day' => $precise_day,
                    'precise_time' => $precise_time
                ];
                UserBehavior::create($data);
                HeartUser::where('id', $request->param('user_id'))->save(['active' => 1]);
            }
            if ($request->param('init') == 0) {

                $behavior = UserBehavior::where('enter_time', $request->param('enter_time'))->where('user_id', $request->param('user_id'))->find();
                if (isset($behavior->id) && $behavior->id) {
                    $behavior->save(['leave_time' => $request->param('leave_time')]);
                    HeartUser::where('id', $request->param('user_id'))->save(['active' => '0', 'last_login_time' => date("Y-m-d H:i:s", $request->param('leave_time'))]);
                    $behaviors = $request->param('behaviors');
                    $requestID = uniqid();
                    if (sizeof($behaviors)) {
                        $logs = [];
                        $total_behaviors = count($behaviors);
                        foreach ($behaviors as $k => $value) {
                            $logs[] = [
                                'user_id' => $value['user_id'],
                                'request_id' => $requestID,
                                'behavior_id' => $behavior->id,
                                'enter_time' => $value['type'] == '996' ? $value['enter_time'] : 0,
                                'leave_time' => $value['type'] == '996' ? $value['enter_time'] : 0,
                                'stay_time' => $value['type'] == '996' ? $value['leave_time'] - $value['enter_time'] : 0,
                                'type' => $value['type'],
                                'is_logout' => ($k + 1 == $total_behaviors) ? 1 : 0,
                                'eventDetail' => $value['eventDetail'],
                                'page_params' => json_encode($value['page_params'], JSON_UNESCAPED_SLASHES),
                                'page' => $value['page'],
                                'precise_day' => $precise_day,
                                'precise_time' => $precise_time
                            ];
                        }
                        $behaviorLog = new UserBehaviorLog();
                        $behaviorLog->saveAll($logs);
                        $stay_time = $request->param('leave_time') - $request->param('enter_time');
                        $visit_num = $behavior->visit_num + count($behaviors);
                        $open_num = $behavior->open_num;
                        $break_num = $behavior->break_num;
                        if (sizeof($behaviors) == 1) {
                            // 访问一页就退出
                            $break_num += 1;
                        }
                        $behavior->save([
                            'stay_time' => $stay_time,
                            'visit_num' => $visit_num,
                            'open_num' => $open_num + 1,
                            'break_num' => $break_num,
                            'leave_time' => $request->param('leave_time')
                        ]);
                    }
                }
            }
            return $this->sendSuccess();
        } catch (\Exception $exception) {
            file_put_contents('./log.txt', $exception->getMessage(), FILE_APPEND);
            return $this->sendError('服务器异常');
        }
    }

    // 更新用户信息
    public function updateUserInfo()
    {
        try {
            HeartUser::where('id', request()->param('id'))->save(request()->except(['v', 'id']));
            return $this->sendSuccess();
        } catch (\Exception $exception) {
            return $this->sendError('服务器异常');
        }
    }

    // 我的
    public function user()
    {
        try {
            if (!request()->param('user_id')) return $this->sendError('缺少参数');

            $user = HeartUser::field(['nickname', 'avatar', 'real_password'])->find(request()->param('user_id'))->toArray();

            return $this->sendSuccess($user);

        } catch (\Exception $exception) {
            return $this->sendError('服务器异常');
        }
    }

    // 分享
    public function share()
    {
        try {
            $data = request()->except(['v']);
            $data['precise_day'] = strtotime(date("Y-m-d", time()));
            $result = Share::create($data);
            return $this->sendSuccess($result);
        } catch (\Exception $exception) {
            return $this->sendError('服务器异常');
        }
    }

    // 保存广告记录
    public function saveAd()
    {
        try {
            $result = Ad::create(request()->except(['v']));
            return $this->sendSuccess($result);
        } catch (\Exception $exception) {
            return $this->sendError('服务器异常');
        }
    }
}
