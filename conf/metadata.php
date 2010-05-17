<?php
/**
 * Metadata for configuration manager plugin
 * Additions for the flattr plugin
 *
 * @author Gina Haeussge <osd@foosel.net>
 */

$meta['default_uid'] = array( 'string', '_pattern' => '#^[1-9][0-9]*$#' );
$meta['default_category'] = array( 'multichoice', '_choices' => array( 'text', 'images', 'video', 'audio', 'software', 'rest' ) );
$meta['default_language'] = array( 'string' );