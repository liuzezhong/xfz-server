<?php
/**
 * Created by PhpStorm.
 * User: liuzezhong
 * Date: 2017/10/29
 * Time: 22:56
 */

namespace Sapp\Model;


use Think\Model;

class MemberModel extends Model
{
    /**
     * 功能：新增分组成员
     * @param array $memberArray
     * @return mixed
     */
    public function createMembers($memberArray = array()) {
        if(!$memberArray || !is_array($memberArray)) {
            throw_exception('Sapp Model MemberModel createMembers memberArray is null');
        }
        return $this->add($memberArray);
    }

    /**
     * 功能：根据分组ID查找成员信息
     * @param int $group_id
     * @return mixed
     */
    public function listMemberByGroupIDLimit($group_id = 0, $limit = 0) {
        if(!$group_id) {
            throw_exception('Sapp Model MemberModel listMemberByGroupID group_id is null');
        }
        $condition['group_id'] = $group_id;
        if(!$limit) {
            return $this->where($condition)->select();
        } else {
            return $this->where($condition)->limit($limit)->select();
        }
    }

    /**
     * 根据用户ID查找分组信息
     * @param int $user_id
     * @return mixed
     */
    public function listMemberByUserID($user_id = 0) {
        if(!$user_id) {
            throw_exception('Sapp Model MemberModel listMemberByUserID user_id is null');
        }
        $condition['user_id'] = $user_id;
        $res = $this->where($condition)->order('gmt_create desc')->select();
        return $res;

    }

    /**
     * 统计分组内用户数
     * @param int $group_id
     * @return mixed
     */
    public function countGroupUser($group_id = 0) {
        if(!$group_id) {
            throw_exception('Sapp Model MemberModel countGroupUser group_id is null');
        }
        $condition['group_id'] = $group_id;
        return $this->where($condition)->count('user_id');
    }

    /**
     * 获取分组内前九名用户信息
     * @param int $group_id
     * @return mixed
     */
    public function listUserLimitNine($group_id = 0) {
        if(!$group_id) {
            throw_exception('Sapp Model MemberModel listUserLimitNine group_id is null');
        }
        $condition['group_id'] = $group_id;
        return $this->where($condition)->limit(9)->select();
    }
}