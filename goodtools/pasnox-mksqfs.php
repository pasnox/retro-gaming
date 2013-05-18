#!/usr/bin/php
<?php
require_once( 'functions.inc.php' );
require_once( 'goodtools.inc.php' );

$packs = array(
    'oldies' => array(
        '2600',
        '5200',
        '7800',
        'CHAF',
        'COL',
        'INTV',
        'VECT',
    ),
    'computers' => array(
        'COCO',
        'CPC',
        'MO5',
        'MSX1',
        'MSX2',
        'MTX',
        'ORIC',
        'SAMC',
    ),
    'handleds' => array(
        'GBA',
        'GBX',
        'GCOM',
        'GG',
        'LYNX',
        'NGPX',
        'SV',
        'WSX',
    ),
    '8bits' => array(
        'NES',
        'SMS',
        'PCE',
    ),
    '16bits' => array(
        'GEN',
        'SNES',
        'PICO',
    ),
    '32bits' => array(
        'VBOY',
    ),
    '64bits' => array(
        'JAG',
        'N64',
    ),
    'sound' => array(
        'PSID',
        'SPC',
    ),
);

$tools = array();

if ( count( $argv ) === 1 ) {
    Tools::echoLine( $argv[ 0 ].' requires at least 2 parameters.' );
    Tools::echoLine( $argv[ 0 ].' SQFS_PATH TOOL... [TOOL...]' );
    exit( 1 );
}

if ( !file_exists( $argv[ 1 ] ) ) {
    Tools::echoLine( 'The given folder does not exists.' );
    exit( 2 );
}

for ( $i = 2; $i < count( $argv ); $i++ ) {
    $key = strtolower( $argv[ $i ] );
    
    if ( array_key_exists( $key, $packs ) ) {
        $tools = array_merge( $tools, $packs[ $key ] );
    }
    else if ( GoodToolsBackends::hasBackend( $key ) ) {
        $tools[] = strtoupper( $key );
    }
    else {
        Tools::echoLine( "Unknown backend: $key" );
    }
}

if ( count( $tools ) === 0 ) {
    Tools::echoLine( 'No backend to make sqfs images, canceled.' );
    exit( 3 );
}

$command = './goodtools.php -r '.$argv[ 1 ].' -m sqfs -t '.implode( ',', $tools );
$exitCode = null;

passthru( $command, $exitCode );
exit( $exitCode );

?>