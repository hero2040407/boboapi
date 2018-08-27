<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/20 0020
 * Time: 下午 3:56
 */
namespace app\backstage\service;

use BBExtend\backmodel\RaceRecord;
use BBExtend\backmodel\RaceRegistration;



// 设置ds_register_log 中的 race_status
class SetRaceStatus
{
    // 签到
    const SING_IN = 11;
// 晋级
    const ADVANCE = 12;
// 淘汰
    const LOST = 13;
    private $advanceUids;
    private $race_id;
    private $area_id;
    private $log_model;

    public function __construct()
    {
        $this->log_model = new RaceRegistration();
    }

    /**
     * @param mixed $area_id
     */
    public function setAreaId($area_id)
    {
        $this->area_id = $area_id;
        return $this;
    }

    public function setRaceId($race_id)
    {
        $this->area_id = $race_id;
        return $this;
    }

    /**
     * Notes: 签到设置
     * Date: 2018/8/20 0020
     * Time: 下午 4:06
     * @param $id
     */
    public function signIn($id)
    {
        $this->log_model->save(
            ['race_status' => self::SING_IN, 'signin_time' =>time()],
            ['id' => $id]
        );
    }

    /**
     * Notes: 获取区域晋级uids
     * Date: 2018/8/20 0020
     * Time: 下午 4:55
     */
    public function getAdvanceUids()
    {
        $this->advanceUids = (new RaceRecord())->where([
            'area_id' => $this->area_id,
            'delete_time' => 0
        ])->column('uid');
    }

    /**
     * Notes: 最终晋级设置
     * Date: 2018/8/20 0020
     * Time: 下午 4:05
     * @throws
     */
    public function advance()
    {
        $uids = $this->advanceUids;

        if ($uids)
        $this->log_model->where([
            'uid' => ['in',$uids],
            'ds_id' => $this->area_id
        ])->update([
            'race_status' => self::ADVANCE,
            'is_finish' => 1
        ]);
    }

    /**
     * Notes: 最终淘汰
     * Date: 2018/8/20 0020
     * Time: 下午 4:07
     * @throws
     */
    public function lost()
    {
        $this->log_model->where([
            'race_status' => self::SING_IN,
            'ds_id' => $this->area_id
        ])->update([
            'race_status' => self::LOST
        ]);
    }

    /**
     * Notes: 获取大赛晋级uids
     * Date: 2018/8/21 0021
     * Time: 上午 11:17
     * @throws
     */
    public function getRaceAdvanceUids()
    {
        $this->advanceUids = (new RaceRecord())->where([
            'race_id' => $this->race_id,
            'delete_time' => 0,
            'area_id' => 0
        ])->column('uid');
    }

    /**
     * Notes: 大赛晋级
     * Date: 2018/8/21 0021
     * Time: 上午 11:20
     * @throws
     */
    public function raceAdvance()
    {
        $uids = $this->advanceUids;

        if ($uids){
            $this->log_model->where([
                'uid' => ['in',$uids],
                'zong_ds_id' => $this->race_id
            ])->update([
                'is_finish' => 1,
                'race_status' => self::ADVANCE
            ]);
        }
    }

    /**
     * Notes: 最终淘汰
     * Date: 2018/8/20 0020
     * Time: 下午 4:07
     * @throws
     */
    public function raceLost()
    {
        $this->log_model->save(
            ['race_status' => self::LOST],
            [
                'zong_ds_id' => $this->race_id,
                'is_finish' => 0
            ]
        );
    }
}