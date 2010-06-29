<?php
/**
 * Flattr Plugin
 *
 * Inserts a flattr button into the current wikipage
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author  Gina Haeussge <osd@foosel.net>
 */

if (!defined('DOKU_INC'))
    define('DOKU_INC', realpath(dirname(__FILE__) . '/../../') . '/');
if (!defined('DOKU_PLUGIN'))
    define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
require_once (DOKU_INC . 'inc/template.php');
require_once (DOKU_INC . 'inc/pageutils.php');

class helper_plugin_flattr extends DokuWiki_Plugin {

    var $validParameters = array(
        'uid', 'title', 'description', 'category', 'language', 'tag', 'url', 'button', 'align', 'thing'
    );

    function validateParameters($params) {
        if (isset($params['align'])) {
            if (!in_array($params['align'], array('left', 'center', 'right')))
                $params['align'] = 'left';
        }

        if (isset($params['button'])) {
            if (!in_array($params['button'], array('normal', 'compact', 'static')))
                $params['button'] = 'normal';
        }

        if (isset($params['category'])) {
            if (!in_array($params['category'], array('text', 'images', 'video', 'audio', 'software', 'rest')))
                $params['category'] = $this->getConf('default_category');
        }

        if (isset($params['uid'])) {
            if (preg_match('#^[1-9][0-9]*$#', $params['uid']) != 1)
                unset($params['uid']);
        }

        if (isset($params['thing'])) {
            if (preg_match('#^[0-9]+$#', $params['thing']) != 1)
                unset($params['thing']);
        }
    }

    function insertMissingParameters(&$params, $title, $description) {
        foreach ($this->validParameters as $p) {
            if (!isset($params[$p])) {
                switch ($p) {
                    case 'uid': {
                        $params['uid'] = $this->getConf('default_uid');
                        break;
                    }
                    case 'category': {
                        $params['category'] = $this->getConf('default_category');
                        break;
                    }
                    case 'title': {
                        $params['title'] = $title;
                        break;
                    }
                    case 'description': {
                        $params['description'] = $description;
                        break;
                    }
                    case 'language': {
                        $params['language'] = $this->getConf('default_language');
                        break;
                    }
                    case 'align': {
                        $params['align'] = 'left';
                        break;
                    }
                }
            }
        }
    }

    function getEmbedCode($params) {
        if (!isset($params['align']))
            return '[n/a: alignment not set]';

        $code = '<div class="flattr_'.$this->_xmlEntities($params['align']).'">';
        switch ($params['button']) {
            case 'static':
                $code .= $this->getStaticEmbedCode($params);
                break;
            default:
                $code .= $this->getJsEmbedCode($params);
                break;
        }
        $code .= '</div>';

        return $code;
    }

    function getJsEmbedCode($params) {
        if (!isset($params['uid']))
            return '[n/a: uid not set]';
        if (!isset($params['title']))
            return '[n/a: title not set]';
        if (!isset($params['description']))
            return '[n/a: description not set]';
        if (!isset($params['category']))
            return '[n/a: category not set]';
        if (!isset($params['language']))
            return '[n/a: language not set]';

        $code = '<script type="text/javascript">';
        $code .= 'var flattr_uid = \''.$this->_xmlEntities($params['uid']).'\';'.DOKU_LF;
        $code .= 'var flattr_tle = \''.$this->_xmlEntities($params['title']).'\';'.DOKU_LF;
        $code .= 'var flattr_dsc = \''.str_replace("\n", "", nl2br($this->_xmlEntities($params['description']))).'\';'.DOKU_LF;
        $code .= 'var flattr_cat = \''.$this->_xmlEntities($params['category']).'\';'.DOKU_LF;
        $code .= 'var flattr_lng = \''.$this->_xmlEntities($params['language']).'\';'.DOKU_LF;
        if (isset($params['tag'])) // optional
            $code .= 'var flattr_tag = \''.$this->_xmlEntities($params['tag']).'\';'.DOKU_LF;
        if (isset($params['url'])) // optional
            $code .= 'var flattr_url = \''.$this->_xmlEntities($params['url']).'\';'.DOKU_LF;
        if (isset($params['button'])) // optional
            $code .= 'var flattr_btn = \''.$this->_xmlEntities($params['button']).'\';'.DOKU_LF;
        $code .= '</script>';
        $code .= '<script src="http://api.flattr.com/button/load.js" type="text/javascript"></script>';

        return $code;
    }

    function getStaticEmbedCode($params) {
        if (!isset($params['thing'])) {
            return '[n/a: thing id not set]';
        }

        $code = '<a href="https://flattr.com/thing/'.$this->_xmlEntities($params['thing']).'">'.DOKU_LF;
        $code .= '<img src="'.DOKU_URL.'/lib/plugins/flattr/button-static.png" />'.DOKU_LF;
        $code .= '</a>'.DOKU_LF;

        return $code;
    }

    function _xmlEntities($string) {
        return htmlspecialchars($string,ENT_QUOTES,'UTF-8');
    }

}