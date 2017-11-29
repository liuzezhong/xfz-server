<?php
/**
 * Created by PhpStorm.
 * User: liuzezhong
 * Date: 2017/10/3
 * Time: 12:55
 */

namespace Sapp\Model;


use Think\Model;

class GroupModel extends Model
{
    private $_db = '';

    public function __construct()
    {
        $this->_db = M('group');
    }

    public function addGroup($groupInfo = array()) {
        if(!$groupInfo || !is_array($groupInfo)) {
            throw_exception('GroupModel addGroup groupInfo is null');
        }
        return $this->_db->add($groupInfo);
    }

    public function findGroupByGroupID($group_id = 0) {
        if(!$group_id) {
            throw_exception('GroupModel findGroupByGroupID group_id is null');
        }
        return $this->_db->where('group_id = ' . $group_id)->find();
    }

    public function selectGroupByUserID($user_id = 0) {
        if(!$user_id) {
            throw_exception('GroupModel selectGroupByUserID user_id is null');
        }
        return $this->_db->where('user_id = ' . $user_id)->select();
    }

    public function updateGroup($group_id = 0 ,$groupInfo = array()) {
        if(!$groupInfo || !is_array($groupInfo)) {
            throw_exception('GroupModel updateGroup groupInfo is null');
        }
        if(!$group_id) {
            throw_exception('GroupModel updateGroup group_id is null');
        }
        return $this->_db->where('group_id = ' . $group_id)->save($groupInfo);
    }

    /**
     * 根据分组ID批量查询用户信息
     * @param array $groupIDList
     */
    public function listGroupByIDList($groupIDList = array()) {
        if(!$groupIDList || !is_array($groupIDList)) {
            throw_exception('GroupModel listGroupByIDList groupIDList is null');
        }
        $condition['group_id'] = array('IN',$groupIDList);
        return $this->_db->where($condition)->select();
    }

}