<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/19 0019
 * Time: 上午 9:18
 */

namespace app\backstage\controller;

use app\backstage\service\MatchList;
use app\backstage\service\SetRaceStatus;
use BBExtend\backmodel\RaceField;
use BBExtend\backmodel\RaceRecord;
use BBExtend\backmodel\RaceRecordHistory;
use BBExtend\backmodel\RaceRegistration;
use BBExtend\Sys;
use think\cache\driver\File;

//等待比赛 过号 弃权
class Match extends Common
{

    /**
     * Notes: 大赛所有列表数据
     * Date: 2018/8/19 0019
     * Time: 下午 1:34
     * @param string $area_id
     * @param string $round
     * @param string $age
     * @param string $race_id
     * @throws
     */
    public function index($area_id = '', $age = '', $race_id = '', $sex = '')
    {
        $list = MatchList::getInstance();

//        setRound和setMatchList方法必须在最后调用,并且setRound在前
        if (!empty($area_id)) {
            $res = RaceField::find($area_id);
            switch($res['status'])
            {
                case '0':
                    $this->success('','',[
                        'error_code' => 40000,
                        'data' => '比赛状态错误'
                    ]);
                    break;
                case '1':
                    $this->success('','',[
                        'error_code' => 40001,
                        'data' => '比赛状态错误'
                    ]);
                    break;
                case '3':
                    $this->success('','',[
                        'error_code' => 40003,
                        'data' => '比赛状态错误'
                    ]);
                    break;
            }
            $list->setAreaId($area_id);
        }
        if (!empty($race_id)) $list->setRaceId($race_id);
        if ($sex !== '') $list->setSex($sex);
        if (empty($age)) $this->error('age必须');

        $list->setAge($age);
        $list->setRound();
        $list->setMatchList();

        $data = [
            'normal_list' => $list->normalIndex(),
            'late_list' => $list->lateIndex(),
        ];

        $this->success('', '', $data);
    }

    /**
     * Notes: 评分列表
     * Date: 2018/8/28 0028
     * Time: 上午 11:43
     * @param string $area_id
     * @param string $age
     * @param string $race_id
     * @throws
     */
    public function scoreIndex($area_id = '', $age = '', $race_id = '', $round = '')
    {
        $list = MatchList::getInstance();

        if (!empty($area_id)) $list->setAreaId($area_id);
        if (!empty($race_id)) $list->setRaceId($race_id);
        if ($age) $list->setAge($age);
        $list->setRound($round);

        $data = $list->scoreIndex();

        $this->success('', '', $data);
    }

    /**
     * Notes: 淘汰列表
     * Date: 2018/8/29 0029
     * Time: 上午 10:19
     * @param string $area_id
     * @param string $age
     * @param string $race_id
     * @throws
     */
    public function lostIndex($area_id = '', $age = '', $race_id = '')
    {
        $list = MatchList::getInstance();
        if (!empty($area_id)) {
            $res = RaceField::find($area_id);
            if ($res['status'] != 2) $this->success('','',[
                'error_code' => 40001,
                'data' => '比赛状态错误'
            ]);
            $list->setAreaId($area_id);
        }
        if (empty($age)) $this->error('age必须');
        if (!empty($race_id)) $list->setRaceId($race_id);

        $list->setAge($age);
        $list->setRound();
        $data = (new MatchList())->lostIndex();

        $this->success('','',$data);
    }

    /**
     * Notes: 签到
     * Date: 2018/8/19 0019
     * Time: 上午 9:20
     * @param string $uid
     * @throws
     */
    public function signIn($id = '')
    {
        if (empty($id)) {
            $this->error('id必须');
        }
        $register_model = new RaceRegistration();
        $record_model = new RaceRecord();

        $user_info = $register_model->where([
            'id' => $id,])->find();

        if ($user_info['race_status'] == 11) {
            $this->error('此用户已签到');
        }

        $max_sort = (new SetRaceStatus())->signIn($id, $user_info);

        if (!$max_sort) $this->error('此大赛还未设置分组,请先设置分组');

        $record_model->uid = $user_info['uid'];
        $record_model->name = $user_info['name'];
        $record_model->age = (date("Y") - substr($user_info['birthday'], 0, 4));
        $record_model->sex = $user_info['sex'];
        $record_model->height = $user_info['height'];
        $record_model->race_id = $user_info['zong_ds_id'];
        $record_model->sort = $max_sort;
        $record_model->round = 1;
//        设置签到人的签到顺序
        $record_model->area_id = $user_info['ds_id'];

        $res = $record_model->save();

        if ($res) $this->success('签到成功');
        $this->error('签到失败');
    }

    /**
     * Notes: 叫号
     * Date: 2018/8/19 0019
     * Time: 上午 9:57
     * @throws
     */
    public function read($id = '')
    {
        if (empty($id))
            $this->error('id必须');
        $record_model = new RaceRecord();
        $record = $record_model->get($id);

        $area_id = $record->area_id;
        $map['uid'] = $record->uid;
        $map['zong_ds_id'] = $record->race_id;

        if ($area_id != 0) {
            $map['ds_id'] = $area_id;
        } else $map['is_finish'] = 1;

        $user_info = (new RaceRegistration())->where($map)->find();
        $user_info['sort'] = $record->sort;
//        $user_info['score_record'] = (new RaceRecordHistory())->where([
//            'race_id' => $record->race_id,
//            'uid' => $record->uid
//        ])->select();
        $this->success('', '', $user_info);
    }

    /**
     * Notes: 过号
     * Date: 2018/8/19 0019
     * Time: 上午 11:20
     * @throws
     */
    public function late($id = '')
    {
        if (empty($id)) {
            $this->error('id必须');
        }

        $res = (new RaceRecord())->where('id', $id)
            ->update(['update_time' => time()]);

        if ($res) $this->success('过号成功');
        $this->error('过号失败');
    }

    /**
     * Notes: 打分
     * Date: 2018/8/19 0019
     * Time: 上午 9:56
     */
    public function marking($id = '', $score = '')
    {
        if (empty($id) || empty($score))
            $this->error('id和score必须');

        $res = (new RaceRecord())->save(
            ['score' => $score, 'delete_time' => time()]
            , ['id' => $id]
        );

        if ($res) $this->success('评分成功');
        $this->error('评分失败');
    }

    /**
     * Notes: 晋级
     * Date: 2018/8/19 0019
     * Time: 上午 9:43
     * @throws
     */
    public function advance($id = '')
    {
        $record_model = new RaceRecord();
        if (empty($id))
            $this->error('id必须');

        $ids = (array)$id;
        $map['id'] = ['in', $ids];

        $records = $record_model
            ->field('id,create_time,update_time,delete_time,score', true)
            ->where($map)->select();

        $records = json_decode(json_encode($records), true);

        $uids = [];
        foreach ($records as &$item) {
            $round = $item['round'];
            $item['round'] = $item['round'] + 1;
            $uids[] = $item['uid'];
            $area_id = $item['area_id'];
            $race_id = $item['race_id'];
        }

        $this->finishField($area_id, $round);

//        $groups = (new File())->get($race_id.'age_group');
//        foreach ($groups as $key => $item){
//            $is_finish = $redis->get($area_id.$item['age'].'finish');
//            if ($is_finish != 1) $this->error('请先结束所有组别的比赛场次后再晋级选手');
//        }
//        foreach ($groups as $item){
//            $redis->set($area_id.$item['age'].'finish', null);
//        }

        if ($area_id) {
            $res = $record_model->where([
                'uid' => ['in', $uids],
                'round' => $round + 1,
                'area_id' => $area_id
            ])->find();
        } else {
            $res = $record_model->where([
                'uid' => ['in', $uids],
                'round' => $round + 1,
                'race_id' => $race_id
            ])->find();
        }

        if ($res)
            $this->error('不能重复晋级序号为'.$res['sort'].'的选手');

        $res = $record_model->saveAll($records);
        if ($res) $this->success('晋级成功');
        $this->error('晋级失败');
    }

    /**
     * Notes: 进行下一轮比赛
     * Date: 2018/8/20 0020
     * Time: 下午 3:52
     * @throws
     */
    public function finishRound($area_id = '', $age = '')
    {
        if (empty($area_id) || empty($age))
            $this->error('area_id和age必须');

        $list = MatchList::getInstance();
        $list->setAreaId($area_id);
        $list->setAge($age);
        $round = $list->setRound();

        (new RaceRecord())->save(
            ['delete_time' => time()],
            [
                'area_id' => $area_id,
                'age' => ['between', $age],
                'round' => $round,
                'delete_time' => 0
            ]);

        $list = (new RaceRecord())
            ->field('uid,round,name,sex,age,sort,race_id,area_id,height,create_time')
            ->where([
            'area_id' => $area_id,
            'age' => ['between', $age],
            'round' => $round,
        ])->group('uid')->order('id')->select();

        $list = json_decode(json_encode($list),true);

        $res = (new RaceRecord())->insertAll($list);

        if ($res) {
            $this->success('本轮比赛结束成功');
        }
        $this->error('本轮比赛结束失败');
    }

    /**
     * Notes: 结束本场比赛
     * Date: 2018/8/28 0028
     * Time: 上午 9:55
     * @throws
     */
    private function finishField($area_id, $round)
    {
        if (empty($area_id))
            $this->error('area_id必须');

        $res = (new RaceRecord())->where([
            'area_id' => $area_id,
            'round' => $round,
            'update_time' => 0,
            'delete_time' => 0
        ])->find();

        if ($res) $this->error('比赛还未完成,请勿晋级');

//        $list = MatchList::getInstance();
//        $list->setAreaId($area_id);
//        $list->setAge($age);
//        $round = $list->setRound();
        (new RaceRecord())->save(
            ['delete_time' => time()],
            [
                'area_id' => $area_id,
                'round' => $round,
                'delete_time' => 0
            ]);
//        $res = Sys::get_container_redis()->set($area_id.$age.'finish', 1);
    }

    /**
     * Notes: 结束当天的比赛
     * Date: 2018/8/30 0030
     * Time: 下午 4:41
     * @param string $race_id
     * @param string $area_id
     * @throws
     */
    public function finishDays($race_id = '', $area_id = '')
    {
        if (empty($area_id) || empty($race_id))
            $this->error('area_id,race_id必须');

        $change = (new SetRaceStatus())->setAreaId($area_id);
        $change->setRaceId($race_id);
        $change->clearAllRedis();
        $change->moveToRecordHistory();
        $this->success('比赛结束成功');
    }

    /**
     * Notes: 本区域比赛全部结束
     * Date: 2018/8/19 0019
     * Time: 上午 10:40
     * @throws
     */
    public function finishArea($race_id = '', $area_id = '')
    {
        if (empty($area_id) || empty($race_id))
            $this->error('area_id,race_id必须');

        $change = (new SetRaceStatus())->setAreaId($area_id);
        $change->setRaceId($race_id);
        $change->advance();
        $change->lost();
        $change->clearAllRedis();
        $change->moveToRecordHistory(1);
        $res = RaceField::where('id', $area_id)->update(['status' => 3]);
        if ($res) $this->success('比赛结束成功');
        $this->error('比赛结束失败');
    }


    /**
     * Notes: 本次大赛结束
     * Date: 2018/8/21 0021
     * Time: 上午 11:14
     */
    public function finishRace($race_id = '')
    {
        if (empty($race_id))
            $this->error('race_id必须');

        $change = (new SetRaceStatus())->setRaceId($race_id);
        $change->getRaceAdvanceUids();
        $change->raceAdvance();
        $change->raceLost();

        $this->success('本次大赛结束成功');
    }

    /**
     * Notes: 最终晋级名单
     * Date: 2018/8/20 0020
     * Time: 下午 1:37
     * @throws
     */
    public function finalAdvanceList($area_id = '', $age = '')
    {
        if (empty($age) || empty($area_id))
            $this->error('age,area_id必须');

        $list = MatchList::getInstance();

        $list->setAge($age);
        if (!empty($area_id))
            $list->setAreaId($area_id);

        $round = $list->setRound();
        $list->setMatchList();
        $arr = $list->normalIndex();
        $uids = [];

        foreach ($arr as $item) {
            $uids[] = $item['uid'];
        }

        $list = (new RaceRecord())->where([
            'uid' => ['in', $uids],
            'round' => $round - 1
        ])->order('score desc')->select();

        $this->success('', '', $list);
    }

    /**
     * Notes: 添加用户
     * Date: 2018/8/20 0020
     * Time: 上午 11:22
     * @throws
     */
    public function updateUsers($race_id = '', $area_id = '')
    {
        $map['ds_id'] = 19;
        $arr = (new RaceRegistration())->where($map)->select();


        $arr = json_decode(json_encode($arr), true);

        foreach ($arr as $item) {
            $map1['id'] = $item['id'];
            $age['age'] = rand(1, 16);
            (new RaceRegistration())->save($age, $map1);
        }
        $this->success('修改用户成功');
    }

    /**
     * Notes:
     * Date: 2018/8/23 0023
     * Time: 下午 4:12
     * @throws
     */
    public function moveData($area_id = '')
    {

//        $map = [
//            'area_id' => $area_id,
//        ];
//        $arr = (new RaceRecord())->where($map)->select();
//        $data = json_decode(json_encode($arr), true);

//        $res = (new RaceRecordOne())->saveAll($data);

//        if ($res) $this->success('数据移动成功');
//        $this->error('数据移动失败');
    }
}