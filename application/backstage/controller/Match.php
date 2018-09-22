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
    protected $beforeActionList = ['access'];
    protected function access()
    {
        if ($this->userInfo['level'] === 0)
            $this->error('此账号无权限');
    }

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
    public function scoreIndex($area_id = '', $race_id = '', $age = '', $round = '')
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
        if (!$user_info['sort']) $this->error('请先给选手生成参赛编号');

        $register_model->save(
            ['race_status' => SetRaceStatus::SING_IN, 'signin_time' => time()],
            ['id' => $id]
        );

        $record_model->uid = $user_info['uid'];
        $record_model->name = $user_info['name'];
        $record_model->age = (date("Y") - substr($user_info['birthday'], 0, 4));
        $record_model->sex = $user_info['sex'];
        $record_model->height = $user_info['height'];
        $record_model->race_id = $user_info['zong_ds_id'];
        $record_model->sort = $user_info['sort'];
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

        $user_info['score_record'] = (new RaceRecordHistory())->field('*,avg(score) as score')->where([
            'race_id' => $record->race_id,
            'uid' => $record->uid
        ])->group('time')->order('id desc')->select();

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
    public function advance($race_id = '', $area_id = '', $id = '', $age = '')
    {
        if (empty($id) || empty($age) || empty($race_id) || empty($area_id))
            $this->error('id,race_id,area_id,age必须');

        $list = MatchList::getInstance();
        $record_model = new RaceRecord();
        $register = new RaceRegistration();
        $ids = (array)$id;

        $time = time();

        $list->setRaceId($race_id);
        $list->setAreaId($area_id);
        $list->setAge($age);
        $list->setRound();
        $map = $list->setMap();

        $map['update_time'] = 0;
        $map['delete_time'] = 0;
        $res = $record_model->where($map)->find();
        if ($res) $this->error('请将所有选手打分或过号以后再晋级');

        $map = getValidParam($map,'area_id,race_id,age,round,delete_time');
        $record_model->save(['delete_time' => $time], $map);

        $uids = $record_model->where(['id' =>['in', $ids]])->column('uid');

        $res = $register->where([
            'zong_ds_id' => $race_id,
            'uid' => ['in',$uids]
        ])->update(['race_status' => SetRaceStatus::ADVANCE]);

        if ($res) $this->success('晋级成功');
        $this->error('请勿重复晋级');
    }

    /**
     * Notes: 进行下一轮比赛
     * Date: 2018/8/20 0020
     * Time: 下午 3:52
     * @throws
     */
    public function finishRound($race_id = '', $area_id = '', $age = '')
    {
        if (empty($race_id) || empty($age))
            $this->error('race_id和age必须');

        $list = MatchList::getInstance();
        $list->setRaceId($race_id);
        $list->setAreaId($area_id);
        $list->setAge($age);
        $list->setRound();
        $map = $list->setMap();

        $mark_map = $map;
        $mark_map['delete_time'] = 0;

        (new RaceRecord())->save(['delete_time' => time()], $mark_map);

        $list = (new RaceRecord())
            ->field('uid,round,name,sex,age,sort,race_id,area_id,height,create_time')
            ->where($map)->group('uid')->order('id')->select();

        foreach ($list as &$item){
            $item = $item->getData();
        }

        $res = (new RaceRecord())->insertAll($list);

        if ($res) {
            $this->success('本轮比赛结束成功');
        }
        $this->error('本轮比赛结束失败');
    }

    /**
     * Notes: 开始下一场比赛
     * Date: 2018/8/28 0028
     * Time: 上午 9:55
     * @throws
     */
    public function finishField($race_id = '', $area_id = '', $age = '')
    {
        if (empty($race_id) || empty($age))
            $this->error('race_id和age必须');
        $model = new RaceRecord();
        $register = new RaceRegistration();
        $list = MatchList::getInstance();
        $list->setRaceId($race_id);
        $list->setAreaId($area_id);
        $list->setAge($age);
        $round = $list->setRound();
        $map = $list->setMap();

        $map['update_time'] = 0;
        $map['delete_time'] = 0;
        $res = $model->where($map)->find();
        if ($res) $this->error('请先晋级再结束本场比赛');

        $register_map['zong_ds_id'] = $map['race_id'];
        $register_map['age'] = ['between', $age];
        if (isset($map['area_id'])) $register_map['ds_id'] = $map['area_id'];
        $register_map['race_status'] = ['<',SetRaceStatus::ADVANCE];
        $register->where($register_map)->update(['race_status' => SetRaceStatus::LOST]);

        $register_map['race_status'] = SetRaceStatus::ADVANCE;
        $records = $register->field('uid,age,name,sex,height,sort')->where($register_map)->select();

        foreach ($records as &$item) {
            $item = $item->getData();
            $item['round'] = $round + 1;
            $item['race_id'] = $race_id;
            $item['area_id'] = $area_id;
        }

        $res = $model->insertAll($records);

        if ($res) $register->where($register_map)->update(['race_status' => SetRaceStatus::SING_IN]);

        if ($res){
            $this->success('本场比赛结束');
        }
        $this->error('本场比赛结束失败');
    }

    /**
     * Notes: 结束今天的比赛
     * Date: 2018/8/19 0019
     * Time: 上午 10:40
     * @throws
     */
    public function finishDay($race_id = '', $area_id = '')
    {
        if (empty($area_id) || empty($race_id))
            $this->error('area_id,race_id必须');

        $match = new MatchList();
        $match->setRaceId($race_id);
        $match->setAreaId($area_id);
        $map = $match->setMap();

        $change = (new SetRaceStatus())->setAreaId($area_id);
        $change->setRaceId($race_id);
        $res = $change->moveToRecordHistory($map);

        if ($res) $this->success('今日比赛结束成功');
        $this->success('今日比赛结束失败');
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

        $match = new MatchList();
        $match->setRaceId($race_id);
        $match->setAreaId($area_id);
        $map = $match->setMap();

        (new RaceRegistration())->where([
            'ds_id' => $area_id,
            'zong_ds_id' => $race_id,
            'race_status' => ['<',SetRaceStatus::ADVANCE]
        ])->update([
            'race_status' => SetRaceStatus::LOST
        ]);

        $change = (new SetRaceStatus())->setAreaId($area_id);
        $change->setRaceId($race_id);
        $change->clearAllRedis();
        $res = $change->moveToRecordHistory($map);

        RaceField::where('id', $area_id)->update(['status' => Field::FINISH]);

        if ($res) $this->success('比赛结束成功');
        $this->error('比赛结束失败');
    }

//    --大赛
//        --赛区
//          --复赛轮次
//              --比赛场次
//                  --比赛场次中的轮次


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
        $change = (new SetRaceStatus())->setAreaId($area_id);
        $change->setRaceId($race_id);
        $change->clearAllRedis();
//        $map['ds_id'] = 19;
//        $arr = (new RaceRegistration())->where($map)->select();
//
//        $arr = json_decode(json_encode($arr), true);
//
//        foreach ($arr as $item) {
//            $map1['id'] = $item['id'];
//            $age['age'] = rand(1, 16);
//            (new RaceRegistration())->save($age, $map1);
//        }
//        $this->success('修改用户成功');
    }
}