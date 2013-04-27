<?php
define( 'NL', "\n" );
define( 'TAB', "\t" );

function array_ikey_exists( $key, $array ) {
    return in_array( strtolower( $key ), array_map( 'strtolower', array_keys( $array ) ) );
} 

$goodToolsBackends = array(
    '2600' => array(
        'suffixes' => array( 'bin' ),
        'suffixe' => 'a26'
    ),
    '5200' => array(
        'suffixes' => array( 'bin' ),
        'suffixe' => 'a52'
    ),
    '7800' => array(
        'suffixes' => array( 'bin' ),
        'suffixe' => 'a78'
    ),
    'CHAF' => array(
        'suffixes' => array( 'bin' ),
        'suffixe' => 'chf'
    ),
    'COCO' => array(
        'suffixes' => array( 'rom' ),
        'suffixe' => 'ccc'
    ),
    'COL' => array(
        'suffixes' => array( 'rom' ),
        'suffixe' => 'col'
    ),
    'CPC' => array(
        'suffixes' => array( 'cpr' ),
        'suffixe' => 'dsk'
    ),
    'GBA' => array(
        'suffixes' => array( 'agb', 'mb', 'bin', 'e+', 'e++', 'mbz' ),
        'suffixe' => 'gba'
    ),
    'GBX' => array(
        'suffixes' => array( 'sgb', 'boy', 'cgb', 'gbc' ),
        'suffixe' => 'gb'
    ),
    'GCOM' => array(
        'suffixes' => array( 'bin' ),
        'suffixe' => 'tgc'
    ),
    'GEN' => array(
        'suffixes' => array( 'md', 'bin', 'gen', 'rom', 'mdx', '32x' ),
        'suffixe' => 'smd'
    ),
    'GG' => array(
        'suffixes' => array( 'sms', 'sg', 'sc', 'bin' ),
        'suffixe' => 'gg'
    ),
    'INTV' => array(
        'suffixes' => array( 'bin', 'rom' ),
        'suffixe' => 'int'
    ),
    'JAG' => array(
        'suffixes' => array( 'bin', 'rom', 'abs', 'cof', 'jag' ),
        'suffixe' => 'j64'
    ),
    'LYNX' => array(
        'suffixes' => array( 'lyx', 'com', 'o', 'bin' ),
        'suffixe' => 'lnx'
    ),
    'MO5' => array(
        'suffixes' => array( 'k7', 'sap' ),
        'suffixe' => 'mo5'
    ),
    'MSX1' => array(
        'suffixes' => array( 'rom' ),
        'suffixe' => 'mx1'
    ),
    'MSX2' => array(
        'suffixes' => array( 'rom' ),
        'suffixe' => 'mx2'
    ),
    'MTX' => array(
        'suffixes' => array( 'mtb' ),
        'suffixe' => 'mtx'
    ),
    'N64' => array(
        'suffixes' => array( 'v64', 'z64', 'rom', 'jap', 'usa', 'pal', 'bin', 'u64', 'ndd' ),
        'suffixe' => 'n64'
    ),
    'NES' => array(
        'suffixes' => array( 'unf', 'unif' ),
        'suffixe' => 'nes'
    ),
    'NGPX' => array(
        'suffixes' => array( 'npc', 'ngc', 'bin' ),
        'suffixe' => 'ngp'
    ),
    'ORIC' => array(
        'suffixes' => array( 'tap', 'ta1', 'ta2', 'ta3', 'ta4', 'ta5', 'ta6', 'ta7', 'ta8', 'ta9', 't10', 'did', 'rom' ),
        'suffixe' => 'dsk'
    ),
    'PCE' => array(
        'suffixes' => array( 'sgx', 'tgx', 'csc', 'ecp' ),
        'suffixe' => 'pce'
    ),
    'PICO' => array(
        'suffixes' => array( 'bin' ),
        'suffixe' => 'pco'
    ),
    'PSID' => array(
        'suffixes' => array(),
        'suffixe' => 'sid'
    ),
    'SAMC' => array(
        'suffixes' => array( 'sad', 'sdf', 'td0' ),
        'suffixe' => 'dsk'
    ),
    'SMS' => array(
        'suffixes' => array( 'sg', 'sc', 'mv', 'sf7', 'gg', 'bin' ),
        'suffixe' => 'sms'
    ),
    'SNES' => array(
        'suffixes' => array( 'fig', 'rom', '058', '078', 'sfc', 'swc', 'st', 'bs', '048', '1' ),
        'suffixe' => 'smc'
    ),
    'SPC' => array(
        'suffixes' => array( 'sp1', 'sp2', 'sp3', 'sp4', 'sp5', 'sp6', 'sp7', 'sp8', 'sp9', 's10', 's11', 's12', 's13', 's14', 's15', 's16', 's17', 's18', 's19' ),
        'suffixe' => 'spc'
    ),
    'SV' => array(
        'suffixes' => array( 'bin' ),
        'suffixe' => 'sv'
    ),
    'VBOY' => array(
        'suffixes' => array(),
        'suffixe' => 'vb'
    ),
    'VECT' => array(
        'suffixes' => array( 'vex', 'gam', 'bin' ),
        'suffixe' => 'vec'
    ),
    'WSX' => array(
        'suffixes' => array( 'wsc', 'bin' ),
        'suffixe' => 'ws'
    ),
);
?>