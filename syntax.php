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

    function getType() {
        return 'substition';
    }

    function getPType() {
        return 'block';
    }

    function getSort() {
        return 124;
    }

    function connectTo($mode) {
        $this->Lexer->addSpecialPattern('<flattr>.*?</flattr>', $mode, 'plugin_flattr');
        $this->Lexer->addSpecialPattern('<flattr\s*/>', $mode, 'plugin_flattr');
    }

    function handle($match, $state, $pos, Doku_Handler $handler) {
        // do not allow the syntax in comments
        if (isset($_REQUEST['comment']))
            return false;

        $params = array();
        $match = trim($match);

        //~~ parse the long syntax if given
        $helper =& plugin_load('helper', 'flattr');
        if (substr($match, 0, 8) == '<flattr>') {
            $lines = explode("\n", substr($match, 8, -9));
            if (trim($lines[0]) === '')
                array_shift($lines);
            if (trim($lines[-1]) === '')
                array_pop($lines);

            foreach ($lines as $line) {
                $line = trim($line);
                list($name, $value) = explode('=', $line, 2);
                $name  = trim(strtolower($name));
                $value = trim($value);
                if (in_array($name, $helper->validParameters))
                    $params[$name] = $value;
            }
        }

        //~~ validation
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
    function render($mode, Doku_Renderer $renderer, $data) {
        global $ID;

        $params = $indata;
        if ($mode == 'xhtml') {
            //~~ insert default values for empty parameters
            $helper =& plugin_load('helper', 'flattr');
            $helper->insertMissingParameters($params);

            //~~ render
            $renderer->doc .= $helper->getEmbedCode($params);
            return true;
        }
    }
}

//Setup VIM: ex: et ts=4 enc=utf-8 :
