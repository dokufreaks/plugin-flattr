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
require_once (DOKU_PLUGIN . 'syntax.php');
require_once (DOKU_INC . 'inc/template.php');
require_once (DOKU_INC . 'inc/pageutils.php');

class syntax_plugin_flattr extends DokuWiki_Syntax_Plugin {

    var $validParameters = array(
        'uid', 'title', 'description', 'category', 'language', 'tag', 'url', 'button', 'align'
    );

    function getInfo() {
        return array (
            'author' => 'Gina Haeussge',
            'email' => 'osd@foosel.net',
            'date' => @file_get_contents(DOKU_PLUGIN.'flattr/VERSION'),
            'name' => 'Flattr Plugin',
            'desc' => 'Inserts a flattr button into the current wikipage',
            'url' => 'http://foosel.org/snippets/dokuwiki/flattr',
        );
    }

    function getType() {
        return 'substition';
    }

    function getSort() {
        return 124;
    }

    function connectTo($mode) {
        $this->Lexer->addSpecialPattern('<flattr>.*?</flattr>', $mode, 'plugin_flattr');
        $this->Lexer->addSpecialPattern('<flattr\s*/>', $mode, 'plugin_flattr');
    }

    function handle($match, $state, $pos, & $handler) {
        $params = array();
        $match = trim($match);

        //~~ parse the long syntax if given
        if (substr($match, 0, 8) == '<flattr>') {
            $lines = explode("\n", substr($match, 8, -9));
            if (trim($lines[0]) === '')
                array_shift($lines);
            if (trim($lines[-1]) === '')
                array_pop($lines);

            foreach ($lines as $line) {
                $line = trim($line);
                list($name, $value) = explode('=', $line, 2);
                if (in_array($name, $this->validParameters))
                    $params[trim($name)] = trim($value);
            }
        }

        //~~ validation
        if (isset($params['align'])) {
            if (!in_array($params['align'], array('left', 'center', 'right')))
                $params['align'] = 'left';
        }

        if (isset($params['button'])) {
            if (!in_array($params['button'], array('compact')))
                unset($params['button']);
        }

        if (isset($params['category'])) {
            if (!in_array($params['category'], array('text', 'images', 'video', 'audio', 'software', 'rest')))
                $params['category'] = $this->getConf('default_category');
        }

        if (isset($params['uid'])) {
            if (preg_match('#^[1-9][0-9]*$#', $params['uid']) != 1)
                unset($params['uid']);
        }

        return ($params);
    }

    /**
     * Renders the flattr button. Currently only XHTML output is supported.
     *
     * @param $mode
     * @param $renderer
     * @param $indata
     * @return unknown_type
     */
    function render($mode, & $renderer, $indata) {
        global $ID;

        $params = $indata;
        if ($mode == 'xhtml') {
            //~~ insert default values for empty parameters
            $meta = p_get_metadata($ID);
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
                            $params['title'] = tpl_pagetitle($ID, true);
                            break;
                        }
                        case 'description': {
                            $params['description'] = $meta['description']['abstract'];
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

            //~~ render
            $renderer->doc .= '<div class="flattr_'.$params['align'].'">';
            $renderer->doc .= '<script type="text/javascript">';
            $renderer->doc .= 'var flattr_uid = \''.$renderer->_xmlEntities($params['uid']).'\';';
            $renderer->doc .= 'var flattr_tle = \''.$renderer->_xmlEntities($params['title']).'\';';
            $renderer->doc .= 'var flattr_dsc = \''.$renderer->_xmlEntities($params['description']).'\';';
            $renderer->doc .= 'var flattr_cat = \''.$renderer->_xmlEntities($params['category']).'\';';
            $renderer->doc .= 'var flattr_lng = \''.$renderer->_xmlEntities($params['language']).'\';';
            if (isset($params['tag'])) // optional
                $renderer->doc .= 'var flattr_tag = \''.$renderer->_xmlEntities($params['tag']).'\';';
            if (isset($params['url'])) // optional
                $renderer->doc .= 'var flattr_url = \''.$renderer->_xmlEntities($params['url']).'\';';
            if (isset($params['button'])) // optional
                $renderer->doc .= 'var flattr_btn = \''.$renderer->_xmlEntities($params['button']).'\';';
            $renderer->doc .= '</script>';
            $renderer->doc .= '<script src="http://api.flattr.com/button/load.js" type="text/javascript"></script>';
            $renderer->doc .= '</div>';
            return true;
        }
    }
}

//Setup VIM: ex: et ts=4 enc=utf-8 :