<?php
/**
 * 豆瓣书单插件:展示你的豆瓣网正在读的书与已读的书条目.<br>在文章中插入<b>[douban_id:YourID]</b>(YourID为你的豆瓣id,非昵称).<br>1.0.0版本于2016-04-26发布 | <a href="http://muguang.me/guff/typecho-plugin-booklist.html" target="_blank">点此插件报错</a>
 * 
 * @package Douban Booklist
 * @author Patrick95
 * @version 1.0.0
 * @link http://muguang.me/
 */

class DoubanBooklist_Plugin implements Typecho_Plugin_Interface
{
    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     * 
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate()
    {
        /** 前端输出处理接口 */
        Typecho_Plugin::factory('Widget_Abstract_Contents')->contentEx = array('DoubanBooklist_Plugin', 'parse');
        Typecho_Plugin::factory('Widget_Abstract_Contents')->excerptEx = array('DoubanBooklist_Plugin', 'parse');
        Typecho_Plugin::factory('Widget_Archive')->header = array('DoubanBooklist_Plugin', 'header');
        Typecho_Plugin::factory('Widget_Archive')->footer = array('DoubanBooklist_Plugin', 'footer');
    }
    
    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     * 
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function deactivate(){}
    
    /**
     * 获取插件配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form){}
    
    /**
     * 个人用户的配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form){}

    /**
     * 头部css
     *
     * @access public
     * @param unknown $header
     * @return unknown
     */
    public static function header() {
        $cssUrl = Helper::options()->pluginUrl . '/DoubanBooklist/static/booklist.css';
        echo '<link rel="stylesheet" type="text/css" href="' . $cssUrl . '" />
        ';
    }

    /**
     * 输出尾部js
     *
     * @access public
     * @param unknown $header
     * @return unknown
     */
    public static function footer() {
        echo '<script type="text/javascript" src="http://apps.bdimg.com/libs/jquery/2.1.4/jquery.min.js"></script>
        ';
        $jsUrl = Helper::options()->pluginUrl . '/DoubanBooklist/static/booklist.js';
        echo '<script type="text/javascript" src="'. $jsUrl .'"></script>
        ';
    }


    /**
     * 解析
     *
     * @access public
     * @param array $matches 解析值
     * @return string
     */
    public static function parseCallback($matches)
    {
        $DoubanID = $matches[1];
        $url = "https://api.douban.com/v2/book/user/$DoubanID/collections?count=100"; //最多取100条数据
        $res=json_decode(file_get_contents($url),true); //读取api得到json
        $res = $res['collections'];
        foreach($res as $v){
            //已经读过的书
            if($v['status']=="read"){
                $book_name=$v['book']['title'];
                $book_img = $v['book']['images']['medium'];
                $book_url = $v['book']['alt'];
                $readlist[] = array("name"=>$book_name,"img"=>$book_img,"url"=>$book_url);
            }elseif($v['status']=="reading"){
                //正在读的书
                $book_name=$v['book']['title'];
                $book_img = $v['book']['images']['medium'];
                $book_url = $v['book']['alt'];
                $readinglist[] = array("name"=>$book_name,"img"=>$book_img,"url"=>$book_url);
            }
        }
        $html='
        <div class="booklist">
        <div class="section">
        <h4>正在读的书</h4>
        <ul class="clearfix">';
        foreach($readinglist as $v){
            $html .= '<li>
                <div class="photo"><img src="'.$v['img'].'" width="98" height="151" /></div>
                <div class="rsp"></div>
                <div class="text"><a href="'.$v['url'].'" target="_blank"><h3>'.$v['name'].'</h3></a></div>
            </li>';
        }
        $html .= ' </ul>
                    </div>
                <div class="section">
                <h4>已读的书</h4>
                <ul  class="clearfix">';
                foreach($readlist as $v){
                  $html .= '<li>
                    <div class="photo"><img src="'.$v['img'].'" width="98" height="151" /></div>
                    <div class="rsp"></div>
                    <div class="text"><a href="'.$v['url'].'" target="_blank"><h3>'.$v['name'].'</h3></a></div>
                    </li>';
        }
        $html .= '</ul>
        </div>
        <span class="copyright">豆瓣书单插件来自：<a href="http://muguang.me/" target="_blank">暮光博客</a></span>
        </div>';
        return $html;
    }

    /**
     * 插件实现方法
     *
     * @access public
     * @return void
     */
    public static function parse($text, $widget, $lastResult)
    {
        $text = empty($lastResult) ? $text : $lastResult;

        if ($widget instanceof Widget_Archive) {
            return preg_replace_callback("/\[douban_id:(.*?)]/i", array('DoubanBooklist_Plugin', 'parseCallback'), $text);
        } else {
            return $text;
        }
    }

}