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

    function validateParameters(&$params) {
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

    function insertMissingParameters(&$params, $title=false, $description=false, $tag=false) {
        global $INFO;
        $meta = p_get_metadata($INFO['id']);

        // Support deprecated parameters
        $params = array_merge($params, array_filter(compact('title', 'description', 'tag')));

        foreach ($this->validParameters as $p) {
            if (!isset($params[$p])) {
                switch ($p) {
                    case 'uid': {
                        if (trim($this->getConf('default_uid')) != '') {
                            $params['uid'] = $this->getConf('default_uid');
                        }
                        break;
                    }
                    case 'category': {
                        if (trim($this->getConf('default_category')) != '') {
                            $params['category'] = $this->getConf('default_category');
                        }
                        break;
                    }
                    case 'title': {
                        $params['title'] = tpl_pagetitle($INFO['id'], true);
                        break;
                    }
                    case 'description': {
                        $params['description'] = $meta['description']['abstract'];
                        break;
                    }
                    case 'language': {
                        if ($this->getConf('default_language')) {
                            $params['language'] = $this->getConf('default_language');
                        }
                        break;
                    }
                    case 'url': {
                        $params['url'] = wl($INFO['id'], '', true);
                        break;
                    }
                    case 'align': {
                        $params['align'] = 'left';
                        break;
                    }
                    case 'tag': {
                        $tags = $meta['subject'];
                        if (!is_array($tags)) $tags = explode(' ', $tags);
                        $params['tag'] = implode(',', $tags);
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
        // Map param names to flattr rev attribute keys
        $revmappings = array('uid'      => 'uid',
                             'category' => 'category',
                             'language' => 'language',
                             'tag'      => 'tags',
                             'button'   => 'button');
        $rev_params = array();
        foreach ($revmappings as $from => $to) {
            if (isset($params[$to])) {
                $rev_params[$to] = $params[$to];
            } elseif (isset($params[$from])) {
                $rev_params[$to] = $params[$from];
            }
        }

        // Check if mandatory params are given
        $mandatories = array('uid', 'title', 'description', 'category', 'language', 'url');
        $failed = array_diff($mandatories, array_keys($params));
        if (count($failed) > 0) {
            return '[n/a: ' . implode(', ', $failed) . ' not set]';
        }

        // Write flattr button definition
        $code = '<a class="FlattrButton" style="display:none;" ';
        $code .= 'title="'.hsc($params['title']).'" ';
        $code .= 'href="'.hsc($params['url']).'" ';
        $code .= 'rev="flattr;';
        foreach ($rev_params as $key => $value) {
            $code .= hsc($key . ':' . $value . ';');
        }
        $code .= '">';
        $code .= str_replace("\n", "<br />", hsc($params['description']));
        $code .= '</a>';

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

    function tpl_flattrbtn($params = array(), $ret = false) {
        $this->insertMissingParameters($params);
        $btn = $this->getEmbedCode($params);
        if ($ret) return $btn;
        echo $btn;
    }

    function _xmlEntities($string) {
        return htmlspecialchars($string,ENT_QUOTES,'UTF-8');
    }

}
