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

    function insertMissingParameters(&$params, $title, $description, $tags) {
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
                    case 'tag': {
                        if ($tags) $params['tag'] = $tags;
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
        // Map long param names to flattr names
        $mappings = array('uid'         => 'uid',
                          'title'       => 'tle',
                          'description' => 'dsc',
                          'category'    => 'cat',
                          'language'    => 'lng',
                          'tag'         => 'tag',
                          'url'         => 'url',
                          'btn'         => 'btn');

        $m_params = array();
        foreach($mappings as $from => $to) {
            if (isset($params[$to])) {
                $m_params[$to] = $params[$to];
            } elseif (isset($params[$from])) {
                $m_params[$to] = $params[$from];
            }
        }
        $params = $m_params;

        // Check if one of the two mandatory params sets are given
        $mandatories = array(array('url'),
                             array('uid', 'tle', 'dsc', 'cat', 'lng'));
        foreach($mandatories as $mand) {
            $failed = array_diff($mand, array_keys($params));
            if (count($failed) === 0) {
                break;
            }
        }
        if (count($failed) > 0) {
            return '[n/a: ' . implode(', ', $failed) . ' not set]';
        }

        // Write flattr params
        if (isset($params['dsc'])) {
            $params['dsc'] = str_replace("\n", ' ', $params['dsc']);
        }
        $code = '<script type="text/javascript">';
        foreach($params as $k => $v) {
            $code .= 'var flattr_' . $k . ' = \'' . $this->_xmlEntities($v) . '\'' . DOKU_LF;
        }
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