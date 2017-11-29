<?php
/**
 * Created by PhpStorm.
 * User: liuzezhong
 * Date: 2017/11/5
 * Time: 20:41
 */

namespace Sapp\Model;


use Think\Model;

class TeamModel extends Model
{
    /**
     * 功能：新增分组信息
     * @param array $teamArray
     * @return mixed
     */
    public function addTeam($teamArray = array()) {
        if(!$teamArray || !is_array($teamArray)) {
            throw_exception('Sapp Model TeamModel addTeam teamArray is null!');
        }
        return $this->add($teamArray);
    }

    /**
     * 功能：删除已经存在的组别信息
     * @param int $group_id
     * @return mixed
     */
    public function deleteTeamByGroupID($group_id = 0) {
        if(!$group_id) {
            throw_exception('Sapp Model TeamModel deleteTeamByGroupID group_id is null!');
        }
        $condition['group_id'] = $group_id;
        return $this->where($condition)->delete();
    }

    /**
     *  功能：列出分组的组别信息
     * @param int $group_id
     * @return mixed
     */
    public function listTeamByGroupID($group_id = 0) {
        if(!$group_id) {
            throw_exception('Sapp Model TeamModel listTeamByGroupID group_id is null!');
        }
        $condition['group_id'] = $group_id;
        return $this->where($condition)->select();
    }
}