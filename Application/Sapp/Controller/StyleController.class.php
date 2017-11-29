<?php
/**
 * Created by PhpStorm.
 * User: liuzezhong
 * Date: 2017/10/2
 * Time: 22:04
 */

namespace Sapp\Controller;


use Think\Controller;
use Think\Exception;

class StyleController extends Controller
{
    public function getAllStyle() {
        try {
            $styles= D('Style')->getAllStyle();
            if($styles){
                $styleNameArray = array_column($styles,'style_name');
                $styleKeyArray = array_column($styles,'style_id');
                $this->ajaxReturn(array(
                    'status' => 1,
                    'message' => '分组方式获取成功',
                    'styleNameArray' => $styleNameArray,
                    'styleKeyArray' => $styleKeyArray,
                ));
            }else {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '分组方式获取失败',
                ));
            }
        } catch (Exception $exception) {
            $this->ajaxReturn($exception->getMessage());
        }
    }
}