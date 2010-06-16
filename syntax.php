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
        try {
            return parent::getInfo();
        } catch (Exception $e) {
            return array (
                'author' => 'Gina Haeussge',
                'email' => 'osd@foosel.net',
                'date' => @file_get_contents(DOKU_PLUGIN.'flattr/VERSION'),
                'name' => 'Flattr Plugin',
                'desc' => 'Inserts a flattr button into the current wikipage',
                'url' => 'http://foosel.org/snippets/dokuwiki/flattr',
            );
        }
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
        // do not allow the syntax in comments
        if (isset($_REQUEST['comment']))
            return false;

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
        $helper =& plugin_load('helper', 'flattr');
        $helper->validateParameters($params);

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
            $helper =& plugin_load('helper', 'flattr');
            $helper->insertMissingParameters($params, tpl_pagetitle($ID, true), $meta['description']['abstract']);

            //~~ render
            $renderer->doc .= $helper->getEmbedCode($params);
            return true;
        }
    }
}

//Setup VIM: ex: et ts=4 enc=utf-8 :