#!/usr/bin/php
<?php
include( 'functions.inc.php' );
include( 'goodtools.inc.php' );

class GoodTools {
    static private $_modes = array( 'update', 'sqfs' );
    
    public function usage() {
        global $argv;
        
        $usage = array(
            $argv[ 0 ].' is a tool that help you to update and maintain easily your roms collections using the Cowering GoodTools and SquashFS.',
            'Usage: '.$argv[ 0 ].' [OPTION]...',
            '',
            '-b path, --binaries[=] path'.TAB.TAB.TAB.'Define the path where the original GoodTools archives are read.',
            '-r path, --roms[=] path'.TAB.TAB.TAB.TAB.'Define the path where the roms for your GoodTools will be scaned / organized.',
            '-t tool1,tool2..., --tools[=] tool1,tool2...'.TAB.'Define the list of good tools to execute, default is all.',
            '-m mode, --mode[=] mode'.TAB.TAB.TAB.TAB.'Define the mode to use, default: update.',
            '-n name, --name[=] name'.TAB.TAB.TAB.TAB.'Define the base name for the squash fs.',
            '',
            'Availables tools: '.implode( ', ', array_keys( GoodToolsBackends::backends() ) ).'.',
            'Availables modes: '.implode( ', ', GoodTools::$_modes ).'.',
        );
        
        foreach ( $usage as $value ) {
            echo $value.NL;
        }
        
        exit( 1 );
    }
    
    public function options() {
        $shorts = "b:r:t:m:n:";
        $longs = array(
            'binaries:', // compressed good tools archives
            'roms:', // the roms scanned per good tools name
            'tools:', // The good tools to run, default all
            'mode:', // The mode to use
            'name:', // The base name to use for the squash fs
        );
        
        $opt = getopt( $shorts, $longs );
        $options = array();
        
        // fill options from parsed opt
        foreach( $opt as $key => $value ) {
            if ( $key == 'b' || $key == 'binaries' ) {
                $options[ 'binaries' ] = realpath( $value );
            }
            else if ( $key == 'r' || $key == 'roms' ) {
                $options[ 'roms' ] = realpath( $value );
            }
            else if ( $key == 't' || $key == 'tools' ) {
                $options[ 'tools' ] = array_map( 'strtoupper', array_map( 'trim', explode( ',', $value ) ) );
            }
            else if ( $key == 'm' || $key == 'mode' ) {
                $options[ 'mode' ] = strtolower( $value );
            }
            else if ( $key == 'n' || $key == 'name' ) {
                $options[ 'name' ] = $value;
            }
        }
        
        if ( count( $options ) == 0 ) {
            $this->usage();
        }
        
        // check / set mode
        if ( array_key_exists( 'mode', $options ) ) {
            if ( !in_array( $options[ 'mode' ], GoodTools::$_modes ) ) {
                echo 'The mode "'.$options[ 'mode' ].'" does not exists.'.NL;
                $this->usage();
            }
        }
        else {
            $options[ 'mode' ] = 'update';
        }
        
        // check / set tools
        if ( array_key_exists( 'tools', $options ) ) {
            foreach ( $options[ 'tools' ] as $backend ) {
                if ( !array_key_exists( $backend, GoodToolsBackends::backends() ) ) {
                    echo 'The backend "'.$backend.'" does not exists.'.NL;
                    $this->usage();
                }
            }
        }
        else {
            $options[ 'tools' ] = array_keys( GoodToolsBackends::backends() );
        }
        
        // test parameters validity
        switch ( $options[ 'mode' ] ) {
            case 'update': {
                if (
                    !array_key_exists( 'binaries', $options ) ||
                    !array_key_exists( 'roms', $options )
                    ) {
                    $this->usage();
                }
                
                if (
                    !file_exists( $options[ 'binaries' ] ) ||
                    !file_exists( $options[ 'roms' ] )
                    ) {
                    echo 'binaries or roms folder does not exists.'.NL;
                    $this->usage();
                }
                
                break;
            }
            case 'sqfs': {
                if ( !array_key_exists( 'roms', $options ) ) {
                    $this->usage();
                }
                
                if ( !file_exists( $options[ 'roms' ] ) ) {
                    echo 'Roms folder does not exists.'.NL;
                    $this->usage();
                }
                
                /*if ( !array_key_exists( 'name', $options ) ) {
                    echo 'The name is not defined.'.NL;
                    $this->usage();
                }*/
                
                break;
            }
        }
        
        return $options;
    }
    
    public function getZipInformations( $zipFilePath, $goodToolsSetsPath ) {
        if ( !file_exists( $zipFilePath ) ) {
            return null;
        }
        
        $matches = null;
        $result = preg_match( '/Good([^_]+)_(.*)\.zip/', basename( $zipFilePath ), $matches );
        
        if ( $result !== 1 ) {
            return null;
        }
        
        $name = strtoupper( $matches[ 1 ] );
        $version = $matches[ 2 ];
        $pathName = $goodToolsSetsPath.'/'.$name.'_'.$version;
        $matchingPath = Tools::getDirectories( $goodToolsSetsPath, array( $name.'*' ) );
        
        return array(
            $name,
            $version,
            $pathName,
            empty( $matchingPath ) ? null : $matchingPath[ 0 ]
        );
    }
    
    public function prepareBackend( $zipInfo ) {
        $backend = GoodToolsBackends::backend( $zipInfo[ 0 ] );
        
        if ( !$backend ) {
            Tools::echoLine( 'No backend to prepare: '.$zipInfo[ 0 ].').' );
            return -1;
        }
        
        $pathName = $zipInfo[ 2 ].'/Unknown';
        
        if ( is_dir( $pathName ) ) {
            Tools::moveFiles( $pathName, $zipInfo[ 2 ] );
        }
        
        $files = Tools::getFiles( $zipInfo[ 2 ], Tools::createFnMatchMasks( '*.%1', $backend[ 'suffixes' ] ) );
        
        foreach ( $files as $file ) {
            $dirname = dirname( $file );
            $basename = basename( $file );
            $basename = substr( $basename, 0, strrpos( $basename, '.' ) +1 ).$backend[ 'suffixe' ];
            $newFile = $dirname.'/'.$basename;
            
            if ( !file_exists( $newFile ) ) {
                if ( !rename( $file, $newFile ) ) {
                    Tools::echoLine( 'Can not move '.$file.' to '.$newFile.'.' );
                }
            }
        }
        
        $suffixes = $backend[ 'suffixes' ];
        $suffixes[] = $backend[ 'suffixe' ];
        $files = Tools::getFiles( $zipInfo[ 2 ], Tools::createFnMatchMasks( '*.%1', $suffixes ) );
        
        return count( $files );
    }
    
    public function finishBackend( $zipInfo ) {
        $backend = GoodToolsBackends::backend( $zipInfo[ 0 ] );
        
        if ( !$backend ) {
            Tools::echoLine( 'No backend to finish: '.$zipInfo[ 0 ].').' );
            return;
        }
        
        $pathName = $zipInfo[ 2 ].'/Unknown';
        
        if ( !is_dir( $pathName ) ) {
            if ( !mkdir( $pathName, 0777, true ) ) {
                Tools::echoLine( 'Can not create '.$pathName.' ('.$zipInfo[ 0 ].').' );
                return;
            }
        }
        
        $suffixes = $backend[ 'suffixes' ];
        $suffixes[] = $backend[ 'suffixe' ];
        
        Tools::moveFiles( $zipInfo[ 2 ], $pathName, Tools::createFnMatchMasks( '*.%1', $suffixes ) );
    }
    
    public function updateSets( Array $options ) {
        $zips = Tools::getFiles( $options[ 'binaries' ], array( '*.zip' ) );
        
        foreach ( $zips as $zip ) {
            $zipInfo = $this->getZipInformations( $zip, $options[ 'roms' ] );
            
            if ( !$zipInfo ) {
                Tools::echoLine( 'Can not get informations for '.$zip.' ('.$zipInfo[ 0 ].').' );
                continue;
            }
            
            if ( !in_array( $zipInfo[ 0 ], $options[ 'tools' ] ) ) {
                continue;
            }
            
            if ( is_dir( $zipInfo[ 3 ] ) ) {
                if ( $zipInfo[ 2 ] != $zipInfo[ 3 ] ) {
                    if ( !rename( $zipInfo[ 3 ], $zipInfo[ 2 ] ) ) {
                        Tools::echoLine( 'Can not move '.$zipInfo[ 3 ].' to '.$zipInfo[ 2 ].' ('.$zipInfo[ 0 ].').' );
                        continue;
                    }
                }
            }
            else {
                if ( !mkdir( $zipInfo[ 2 ], 0777, true ) ) {
                    Tools::echoLine( 'Can not create '.$zipInfo[ 2 ].' ('.$zipInfo[ 0 ].').' );
                    continue;
                }
            }
            
            $currentPath = getcwd();
            $output = null;
            $exitCode = null;
            
            chdir( $zipInfo[ 2 ] );
            
            exec( 'unzip -o '.$zip.' Good*.exe Good*.cfg', $output, $exitCode );
            
            if ( $exitCode === 0 ) {
                Tools::echoLine( 'Successfully update GoodTool '.$zipInfo[ 2 ].' ('.$zipInfo[ 0 ].').' );
            }
            else {
                Tools::echoLine( 'Can not update GoodTool '.$zipInfo[ 2 ].' ('.$zipInfo[ 0 ].').' );
                continue;
            }
            
            if ( $this->prepareBackend( $zipInfo ) > 0 ) {
                exec( 'wine *ood*.exe rename dirs', $output, $exitCode );
                
                if ( $exitCode === 0 ) {
                    Tools::echoLine( 'Successfully update '.$zipInfo[ 2 ].' ('.$zipInfo[ 0 ].').' );
                }
                else {
                    Tools::echoLine( 'Can not update '.$zipInfo[ 2 ].' ('.$zipInfo[ 0 ].').' );
                }
            }
            
            $this->finishBackend( $zipInfo );
            
            chdir( $currentPath );
        }
    }
    
    public function createSquashFS( $folderPath, Array $options ) {
        $folders = Tools::getDirectories( $folderPath, array( '*Ren' ) );
        
        if ( count( $folders ) !== 1 ) {
            Tools::echoLine( 'Can not found renamed folder for '.$folderPath );
            return false;
        }
        
        $renamedFolder = $folders[ 0 ];
        $fsCompression = "xz";
        $fsOptions = "-Xdict-size 1M";
        $targetFilePath = $options[ 'roms' ].'/'.basename( $folderPath ).'-'.$fsCompression.'.sqfs';
        // -keep-as-directory
        $command = "mksquashfs \"$renamedFolder\" \"$targetFilePath\" -no-xattrs -no-exports -noappend -no-recovery -b 1M -comp $fsCompression $fsOptions";
        $exitCode = null;
        
        passthru( $command, $exitCode );
        
        if ( (int)( $exitCode ) !== 0 ) {
            Tools::echoLine( $command );
        }
        
        return (int)( $exitCode ) === 0;
    }
    
    public function makeSquashFS( Array $options ) {
        $folders = Tools::getDirectories( $options[ 'roms' ], Tools::createFnMatchMasks( '*%1*', $options[ 'tools' ] ) );
        
        foreach ( $folders as $folder ) {
            if ( !$this->createSquashFS( $folder, $options ) ) {
            }
        }
    }
    
    public function exec() {
        $options = $this->options();
        
        switch ( $options[ 'mode' ] ) {
            case 'update':
                $this->updateSets( $options );
                break;
            case 'sqfs':
                $this->makeSquashFS( $options );
                break;
        }
    }
}

$gt = new GoodTools;
$gt->exec();
unset( $gt );
?>