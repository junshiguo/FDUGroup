<?php
/**
 * RHtmlHelper class file.
 * @author: Raysmond
 */

class RHtmlHelper
{

    public static function encode($content)
    {
        return htmlspecialchars($content, ENT_QUOTES, Rays::app()->charset);
    }

    public static function decode($content)
    {
        return htmlspecialchars_decode($content, ENT_QUOTES);
    }

    public static function tryCleanLink($link)
    {
        if (Rays::app()->isCleanUri())
            return str_replace("?q=", "", $link);
        else return $link;
    }

    /**
     * Return site url
     * @param $url like "site/about"
     */
    public static function siteUrl($url)
    {
        return self::tryCleanLink(Rays::app()->getBaseUrl() . "/" . $url);
    }

    public static function linkAction($controller, $name, $action = null, $params = null)
    {
        $link = "?q=" . $controller;
        if (isset($action) && $action != '')
            $link .= "/" . $action;
        if (isset($params)) {
            if (!is_array($params)) {
                $link .= "/" . $params;
            } else {
                foreach ($params as $param) {
                    $link .= "/" . $param;
                }
            }
        }
        return self::link($name, $name, Rays::app()->getBaseUrl() . "/" . $link);
    }

    public static function link($title, $content, $href)
    {
        return '<a title="' . $title . '" href="' . self::tryCleanLink($href) . '" >' . self::encode($content) . '</a>';
    }

    public static function linkWithTarget($title, $content, $href, $target)
    {
        return '<a title="' . $title . '" href="' . self::tryCleanLink($href) . '" target="' . $target . '" >' . self::encode($content) . '</a>';
    }

    public static function linkCssArray($cssArray)
    {
        if (!is_array($cssArray))
            return "";
        else {
            $html = "";
            foreach ($cssArray as $css) {
                $html .= self::css($css) . "\n";
            }
            return $html;
        }
    }

    public static function linkScriptArray($scriptArray)
    {
        if (!is_array($scriptArray))
            return "";
        else {
            $html = "";
            foreach ($scriptArray as $script) {
                $html .= self::script($script) . "\n";
            }
            return $html;
        }
    }

    public static function css($cssPath)
    {
        return '<link rel="stylesheet" type="text/css" href="' . Rays::app()->getBaseUrl() . $cssPath . '" />';
    }

    public static function script($scriptPath)
    {
        return '<script type="text/javascript" src="' . Rays::app()->getBaseUrl() . $scriptPath . '"></script>';
    }

    public static function showFlashMessages(){
        $session = Rays::app()->getHttpSession();
        $messages = '';
        if(($message = $session->getFlash("message"))!=false){
            //print_r($message);
            foreach($message as $m)
                $messages.='<div class="alert alert-info">' .$m. '</div>';
        }
        if(($warnings = $session->getFlash("warning"))!=false){
            foreach($warnings as $warning)
                $messages.='<div class="alert alert-warning">' .$warning. '</div>';
        }
        if(($errors = $session->getFlash("error"))!=false){
            foreach($errors as $error)
                $messages.='<div class="alert alert-danger">' .$error. '</div>';
        }
        return $messages;
    }

}