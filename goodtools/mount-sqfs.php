#!/usr/bin/php
<?php
include( 'functions.inc.php' );
include( 'goodtools.inc.php' );

class Mount {
    public function usage() {
        global $argv;
        
        $usage = array(
            $argv[ 0 ].' is a tool that help you to mount squash fs images.',
            'Usage: '.$argv[ 0 ].' [OPTION]...',
            '',
            '-s path, --source[=] path'.TAB.TAB.TAB.'[Required] - Define the path where to check for sqfs files.',
            '-m path, --mountpoint[=] path'.TAB.TAB.TAB.'[Optional] - Define the path where to mount the sqfs files, default is /mnt.',
            '-t tool1,tool2..., --tools[=] tool1,tool2...'.TAB.'[Optional] - Define the list of good tools to mount, default is all.',
            '',
            'Availables tools: '.implode( ', ', array_keys( GoodToolsBackends::backends() ) ).'.',
        );
        
        foreach ( $usage as $value ) {
            echo $value.NL;
        }
        
        exit( 1 );
    }
    
    public function options() {
        $shorts = "s:m:t:";
        $longs = array(
            'source:', // Required: The source path where to look for sqfs files
            'mountpoint:', // Required: The mount point path
            'tools:', // Optional: The good tools to mount, default all
        );
        
        $opt = getopt( $shorts, $longs );
        $options = array();
        
        foreach( $opt as $key => $value ) {
            if ( $key == 's' || $key == 'source' ) {
                $options[ 'source' ] = realpath( $value );
            }
            else if ( $key == 'm' || $key == 'mountpoint' ) {
                $options[ 'mountpoint' ] = realpath( $value );
            }
            else if ( $key == 't' || $key == 'tools' ) {
                $options[ 'tools' ] = array_map( 'strtoupper', array_map( 'trim', explode( ',', $value ) ) );
            }
        }
        
        if ( count( $options ) == 0 ) {
            $this->usage();
        }
        
        if (
            !array_key_exists( 'source', $options ) ||
            !array_key_exists( 'mountpoint', $options )
            ) {
            $this->usage();
        }
        
        if (
            !file_exists( $options[ 'source' ] ) ||
            !file_exists( $options[ 'mountpoint' ] )
            ) {
            echo 'source or mountpoint folder does not exists.'.NL;
            $this->usage();
        }
        
        /*if ( array_key_exists( 'tools', $options ) ) {
            foreach ( $options[ 'tools' ] as $backend ) {
                if ( !array_key_exists( $backend, GoodToolsBackends::backends() ) ) {
                    echo 'The backend "'.$backend.'" does not exists.'.NL;
                    $this->usage();
                }
            }
        }
        else {
            $options[ 'tools' ] = array_keys( GoodToolsBackends::backends() );
        }*/
        
        return $options;
    }
    
    public function doMount( $filePath, Array $options ) {
        $tmp = $options[ 'mountpoint' ];
        $fileName = $filePath;
        $dirName = dirname( $fileName );
        $baseName = basename( $fileName, '.sqfs' );
        $tmpFolder = $tmp.'/'.$baseName;
        $ok = false;
        
        if ( Tools::isMounted( $tmpFolder ) ) {
            Tools::echoLine( "'$tmpFolder' ($baseName) already mounted, skipping..." );
            $ok = true;
        }
        else {
            if ( !file_exists( $tmpFolder ) && !mkdir( $tmpFolder, 0777, true ) ) {
                Tools::echoLine( "Can't create tmp folder '$tmpFolder' ($baseName)." );
            }
            else {
                $output = null;
                $exitCode = null;
                
                exec( 'sudo mount -o loop,ro -t squashfs "'.$fileName.'" "'.$tmpFolder.'" 2>&1', $output, $exitCode );
                
                if ( (int)( $exitCode ) !== 0 ) {
                    Tools::echoLine( "A problem occurs when mounting '$tmpFolder' ($baseName):".NL.implode( NL, $output )."." );
                    rmdir( $tmpFolder );
                }
                else {
                    Tools::echoLine( "Successfully mounted '$tmpFolder' ($baseName)." );
                }
                
                $ok = (int)( $exitCode ) === 0;
            }
        }
        
        return $ok;
    }
    
    public function exec() {
        $options = $this->options();
        $files = Tools::getFiles( $options[ 'source' ], Tools::createFnMatchMasks( '*%1*.sqfs', $options[ 'tools' ] ) );
        
        foreach ( $files as $file ) {
            if ( !$this->doMount( $file, $options ) ) {
            }
        }
    }
}

$m = new Mount;
$m->exec();
unset( $m );
?>