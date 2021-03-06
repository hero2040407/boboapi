<?php

namespace app\backstage\controller;

use app\backstage\service\SetRaceStatus;
use BBExtend\backmodel\RaceField;
use BBExtend\backmodel\RaceRegistration;
use BBExtend\Sys;
use BBExtend\DbSelect;

/**
 * 赛区接口
 *
 * @author Administrator
 *
 */
class Field extends Common
{
    const STOP = 0;
    const SIGN_IN = 1;
    const MATCH = 2;
    const FINISH = 3;
    /**
     * 赛
     *
     * @return number[]|string[]|number[]
     */
    public function index($status = null, $is_valid = null, $per_page = 10, $page = 1, $ds_id = null, $field_id = null,
                          $proxy_id = null)
    {
        $db = Sys::get_container_db_eloquent();

        //    $proxy_id = input("get.proxy_id");


        $paginator = $db::table('ds_race_field')->select(['id',]);

        if ($status != null) {
            $paginator = $paginator->where("status", $status);

        }
        if ($is_valid != null) {
            $paginator = $paginator->where("is_valid", $is_valid);

        }
        if ($ds_id != null) {
            $paginator = $paginator->where("race_id", $ds_id);

        }
        if ($field_id != null) {
            $paginator = $paginator->where("id", $field_id);

        }

        if ($proxy_id) {
            $paginator = $paginator->whereExists(function ($query) use ($proxy_id, $db) {
//                 $db = Sys::get_container_db_eloquent();
                $query->select($db::raw(1))
                    ->from('ds_race')
                    ->whereRaw('ds_race.id = ds_race_field.race_id')
                    ->whereRaw('ds_race.proxy_id=' . intval($proxy_id));
            });

        }


        $paginator = $paginator->orderBy('id', 'asc')->paginate($per_page, ['*'], 'page', $page);
        $result = [];
        foreach ($paginator as $v) {
            $result[] = $v->id;
        }


        $new = [];
        foreach ($result as $v) {
            $temp = \BBExtend\backmodel\RaceField::find($v);
            $new[] = $temp->display();
        }
        return ['code' => 1, 'data' => ['list' => $new,
            'pageinfo' => $this->get_pageinfo($paginator, $per_page)]];
    }


    private function create_channel_account($parent)
    {
        $obj = new \BBExtend\backmodel\Admin();
        $account_arr = $this->get_full_account(2);
        $db = Sys::get_container_db_eloquent();
        //   $db::table('')
    }


    // 添加赛区
    public function add($race_id, $address, $title, $realname, $phone)
    {
        $obj = \BBExtend\backmodel\Race::find($race_id);
        if (!$obj) {
            return ['code' => 400, 'message' => 'race_id错误'];
        }
        if ($obj->level != 1) {
            return ['code' => 400, 'message' => 'race_id权限错误'];
        }
        if ($obj->proxy_id == 0) {
            return ['code' => 400, 'message' => '该大赛还没有代理人'];
        }
        $proxy_id = $obj->proxy_id;

        if (empty($address) || empty($title) || empty($realname) || empty($phone)) {
            return ['code' => 400, 'message' => '缺少参数'];
        }


        $account_arr = $this->get_full_account(2);


        $account = new \BBExtend\backmodel\Admin();
        $account->realname = $realname;
        $account->phone = $phone;
        $account->level = 2;
        $account->parent = $proxy_id;
        $account->account = $account_arr['account'];
        $account->pwd = $account_arr['pwd'];
        $account->pwd_original = $account_arr['pwd_original'];
        $account->is_valid = 1;
        $account->create_time = time();
        $account->save();


        $obj = new RaceField();
        $obj->address = $address;
        $obj->title = $title;
        $obj->channel_id = $account->id;
        $obj->status = 0; //默认等待中。
        $obj->is_valid = 1; // 有效
        $obj->create_time = time();
        $obj->race_id = $race_id;

        $obj->save();


        $admin_race = new \BBExtend\backmodel\AdminRace();
        $admin_race->account_id = $account->id;
        $admin_race->race_id = $race_id;
        $admin_race->field_id = $obj->id;
        $res = $admin_race->save();

        if ($res) $this->adminActionLog('新增了赛区,id为'.$obj->id);

        $result = ['code' => 1, 'data' => [
            'insert_id' => $obj->id,
            'account' => $account->account,
            'pwd' => $account->pwd_original,
            'channel_id' => $account->id,
            //''
        ]];

        return $result;
    }


    /**
     * 修改赛区。
     *
     * @param unknown $address
     * @param unknown $title
     * @param unknown $field_id
     * @param unknown $is_valid
     * @param unknown $status
     * @return number[]|string[]|number[]
     */
    public function edit($address = '', $title = '', $field_id, $is_valid = '', $status = '')
    {
        $id = \BBExtend\Session::get_my_id();

        $field = RaceField::find($field_id);

        if (!$field_id) {
            return ['code' => 400, 'message' => '赛区id错误。'];
        }

        if (!empty($address)) $field->address = $address;
        if (!empty($title)) $field->title = $title;
        if (!empty($is_valid)) $field->is_valid = $is_valid;
        if (!empty($status)) $field->status = $status;

        $field->save();
        $this->adminActionLog('修改了赛区,id为'.$field_id);
        return ['code' => 1];
    }

    /**
     * Notes: 复赛
     * Date: 2018/9/6 0006
     * Time: 下午 2:08
     * @throws
     */
    public function repeat($area_id = '')
    {
        if (empty($area_id))
            $this->error('area_id必须');

        $register = new SetRaceStatus();
        $model = new RaceField();

        $res = $register->setAreaId($area_id)->repeat();

        if ($res){
            $model->where('id',$area_id)->increment('round');
            $model->where('id',$area_id)->update(['status' => self::SIGN_IN]);
            $this->success('成功开启复赛');
        }
        $this->error('没有可以参加复赛的选手');
    }

    /**
     * Notes:按身高排序
     * Date: 2018/9/17 0017
     * Time: 下午 1:18
     * @throws
     */
    public function sortByHeight($race_id = '', $area_id = '')
    {
        if (empty($race_id) || empty($area_id))
            $this->error('race_id或area_id必须');

        if ($area_id){
            $res = (new RaceField())->where('id',$area_id)->update(['status' => 2]);
            if (!$res) $this->error('请勿重复排序');
        }

        $set_status = new SetRaceStatus();
        $set_status->setAreaId($area_id);
        $set_status->setRaceId($race_id);
        $set_status->sort();
        $this->success('排序成功');
    }

    /**
     * Notes: 把报名信息归到某个赛区
     * Date: 2018/9/6 0006
     * Time: 上午 9:21
     * @throws
     */
    public function merge($race_id = '', $area_id = '', $to_area_id = '')
    {
        if (empty($area_id) || empty($to_area_id) || empty($race_id))
            $this->error('race_id,area_id,to_area_id必须');

//        $race_field = new RaceField();
//        $to_race = $race_field->where('id',$to_area_id)->value('race_id');
//        $races = $race_field->where('id','in',$area_id)->select('race_id');
//        foreach ($races as $item){
//            if ($item['race_id'] != $to_race){
//                $this->error('请勿合并不同大赛的赛区'.$item['']);
//            }
//        }
        $area_id = (array)$area_id;

        $res = (new RaceRegistration())
            ->where([
                'zong_ds_id' => $race_id,
                'ds_id' => ['in',$area_id]
            ])->update(['ds_id' => $to_area_id]);
//        RaceField::destroy($area_id);

        if ($res) $this->success('赛区合并成功');
        $this->error('赛区合并失败');
    }
}






