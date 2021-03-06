<?php
/**
 * RSessionManager class file.
 * @author: Raysmond
 */

class RSessionManager {

    public $prefix = "RAYS_SESSION";
    public $cookieDuration = 86400; // 3600*24

    //public $flashMessage = array();

    public function __construct(){
        session_start();
        //$this->prefix = $_SERVER['HTTP_POST'];

        //if($this->get('flash')) {
        //    foreach($_SESSION[$this->prefix]['flash'] as $name=>$vals) {
        //        ++$_SESSION[$this->prefix]['flash'][$name]['counter'];
        //        if($_SESSION[$this->prefix]['flash'][$name]['counter']>1) {
        //            unset($_SESSION[$this->prefix]['flash'][$name]);
        //        }
         //   }
        //}
    }

    function get($id) {
        if(isset($_SESSION[$this->prefix][$id]))
            return $_SESSION[$this->prefix][$id];
        else return false;
    }

    function getCookie($name) {
        return ((isset($_COOKIE[$name])) ? $_COOKIE[$name] : false);
    }

    function set($id,$value) {
        $_SESSION[$this->prefix][$id] = $value;
    }

    function deleteSession($id){
        if(isset($_SESSION[$this->prefix][$id])){
            unset($_SESSION[$this->prefix][$id]);
        }
    }

    function setCookie($name,$value) {
        setcookie($name,$value,time() + $this->cookieDuration,'/');
    }

    function del() {
        $ids = func_get_args();
        foreach($ids as $id) {
            if($this->get($id)||is_array($this->get($id))) {
                unset($_SESSION[$this->prefix][$id]);
            }
        }
    }

    function del_cookie() {
        $cookies = func_get_args();
        foreach($cookies as $cookie) {
            if($this->get_cookie($cookie)) {
                setcookie($cookie,'del',time()- 3600,'/');
            }
        }
    }

    function flash($id,$value) {
        if(!isset($_SESSION[$this->prefix]['flash'][$id]))
            $_SESSION[$this->prefix]['flash'][$id] = array();
        array_push($_SESSION[$this->prefix]['flash'][$id],$value);
    }

    function getFlash($id) {
        if(isset($_SESSION[$this->prefix]['flash'][$id])){
            $val =$_SESSION[$this->prefix]['flash'][$id];
            unset($_SESSION[$this->prefix]['flash'][$id]);
            return $val;
        }
        else return false;
    }
}