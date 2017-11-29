<?php
/**
 * Created by PhpStorm.
 * User: liuzezhong
 * Date: 2017/10/2
 * Time: 22:07
 */

namespace Sapp\Model;


use Think\Model;

class StyleModel extends Model
{
    private $_db = '';

    public function  __construct()
    {
        $this->_db = M('style');
    }

    public function getAllStyle() {
        return $this->_db->select();
    }

    public function findStyleByStyleID($style_id = 0) {
        return $this->_db->where('style_id = ' . $style_id)->find();
    }

}