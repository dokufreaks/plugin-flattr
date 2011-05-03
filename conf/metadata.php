<?php
/**
 * Metadata for configuration manager plugin
 * Additions for the flattr plugin
 *
 * @author Gina Haeussge <osd@foosel.net>
 */

$meta['default_uid'] = array( 'string', '_pattern' => '#^[0-9a-z]+$#' );
$meta['default_category'] = array( 'multichoice', '_choices' => array( 'text', 'images', 'video', 'audio', 'software', 'rest' ) );
$meta['default_language'] = array( 'string' );