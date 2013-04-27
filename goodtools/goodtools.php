#!/usr/bin/php
<?php
include( 'goodtools.inc.php' );

    class GoodTools {
        private $_backends = null;
        
        function __construct() {
            global $goodToolsBackends;
            $this->_backends = $goodToolsBackends;
        }
        
        public static function usage() {
            global $goodToolsBackends;
            global $argv;
            
            $usage = array(
                $argv[ 0 ].' is a tool that help you to update and maintain easily your roms collections using the Cowering GoodTools.',
                'Usage: '.$argv[ 0 ].' [OPTION]...',
                '',
                '-b path, --binaries[=] path'.TAB.TAB.TAB.'[Required] - Define the path where the original GoodTools archives are read',
                '-r path, --roms[=] path'.TAB.TAB.TAB.TAB.'[Required] - Define the path where the roms for your GoodTools will be scaned / organized',
                '-t tool1,tool2..., --tools[=] tool1,tool2...'.TAB.'[Optional] - Define the list of good tools to execute, default is all',
                '',
                'Availables tools: '.implode( ', ', array_keys( $goodToolsBackends ) )
            );
            
            foreach ( $usage as $value ) {
                echo $value.NL;
            }
            
            exit( 1 );
        }
        
        public static function options() {
            global $goodToolsBackends;
            
            $shorts = "b:r:t:";
            $longs = array(
                'binaries:', // Required: compressed good tools archives
                'roms:', // Required: the roms scanned per good tools name
                'tools:' // Optional: The good tools to run, default all
            );
            
            $opt = getopt( $shorts, $longs );
            $options = array();
            
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
            }
            
            if (
                !array_key_exists( 'binaries', $options ) ||
                !array_key_exists( 'roms', $options )
               ) {
               GoodTools::usage();
            }
            
            if (
                !file_exists( $options[ 'binaries' ] ) ||
                !file_exists( $options[ 'roms' ] )
               ) {
               echo 'binaries or roms folder does not exists.'.NL;
               GoodTools::usage();
            }
            
            if ( array_key_exists( 'tools', $options ) ) {
                foreach ( $options[ 'tools' ] as $backend ) {
                    if ( !array_key_exists( $backend, $goodToolsBackends ) ) {
                        echo 'The backend "'.$backend.'" does not exists.'.NL;
                        GoodTools::usage();
                    }
                }
            }
            else {
                $options[ 'tools' ] = array_keys( $goodToolsBackends );
            }
            
            return $options;
        }
        
        public static function echoLine( $string ) {
            echo $string.NL;
        }
        
        public function backends() {
            return $this->_backends;
        }
        
        public function backend( $name ) {
            return array_key_exists( $name, $this->_backends ) ? $this->_backends[ $name ] : null;
        }
        
        public function createFnMatchMasks( $mask, $extensions ) {
            $masks = array();
            
            if ( is_array( $extensions ) ) {
                if ( empty( $extensions ) ) {
                    $masks[] = str_replace( '%1', '', $mask );
                }
                else if ( count( $extensions ) === 1 ) {
                    $masks[] = str_replace( '%1', $extensions[ 0 ], $mask );
                }
                else {
                    foreach ( $extensions as $extension ) {
                        $masks[] = str_replace( '%1', $extension, $mask );
                    }
                }
            }
            else {
                $masks[] = str_replace( '%1', $extensions, $mask );
            }
            
            return $masks;
        }
        
        public function getFiles( $pathName, $masks, $flags = FNM_CASEFOLD ) {
            $files = array();
            $dir = dir( $pathName );
            
            if ( $dir !== null ) {
                if ( $dir !== false ) {
                    while ( ( $entry = $dir->read() ) !== false ) {
                        if ( $entry == '.' || $entry == '..' ) {
                            continue;
                        }
                        
                        $found = false;
                        
                        foreach ( $masks as $mask ) {
                            if ( fnmatch( $mask, $entry, $flags ) ) {
                                $found = true;
                            }
                            
                            if ( $found ) {
                                break;
                            }
                        }
                        
                        if ( !$found ) {
                            continue;
                        }
                        
                        $entry = $pathName.'/'.$entry;
                        
                        if ( is_file( $entry ) ) {
                            $files[] = $entry;
                        }
                    }
                    
                    $dir->close();
                }
            }
            
            return $files;
        }
        
        public function getDirectories( $pathName, $masks, $flags = FNM_CASEFOLD ) {
            $directories = array();
            $dir = dir( $pathName );
            
            if ( $dir !== null ) {
                if ( $dir !== false ) {
                    while ( ( $entry = $dir->read() ) !== false ) {
                        if ( $entry == '.' || $entry == '..' ) {
                            continue;
                        }
                        
                        $found = false;
                        
                        foreach ( $masks as $mask ) {
                            if ( fnmatch( $mask, $entry, $flags ) ) {
                                $found = true;
                            }
                            
                            if ( $found ) {
                                break;
                            }
                        }
                        
                        if ( !$found ) {
                            continue;
                        }
                        
                        $entry = $pathName.'/'.$entry;
                        
                        if ( is_dir( $entry ) ) {
                            $directories[] = $entry;
                        }
                    }
                    
                    $dir->close();
                }
            }
            
            return $directories;
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
            $matchingPath = $this->getDirectories( $goodToolsSetsPath, array( $name.'*' ) );
            
            return array(
                $name,
                $version,
                $pathName,
                empty( $matchingPath ) ? null : $matchingPath[ 0 ]
            );
        }
        
        public function moveFiles( $sourcePath, $targetPath, $wildcard = array( '*' ) ) {
            $files = $this->getFiles( $sourcePath, $wildcard );
            
            foreach ( $files as $file ) {
                $newFile = $targetPath.'/'.basename( $file );
                
                if ( !rename( $file, $newFile ) ) {
                    GoodTools::echoLine( 'Can not move '.$file.' to '.$newFile.'.' );
                }
            }
        }
        
        public function prepareBackend( $zipInfo ) {
            $backend = $this->backend( $zipInfo[ 0 ] );
            
            if ( !$backend ) {
                GoodTools::echoLine( 'No backend to prepare: '.$zipInfo[ 0 ].').' );
                return -1;
            }
            
            $pathName = $zipInfo[ 2 ].'/Unknown';
            
            if ( is_dir( $pathName ) ) {
                $this->moveFiles( $pathName, $zipInfo[ 2 ] );
            }
            
            $files = $this->getFiles( $zipInfo[ 2 ], $this->createFnMatchMasks( '*.%1', $backend[ 'suffixes' ] ) );
            
            foreach ( $files as $file ) {
                $dirname = dirname( $file );
                $basename = basename( $file );
                $basename = substr( $basename, 0, strrpos( $basename, '.' ) +1 ).$backend[ 'suffixe' ];
                $newFile = $dirname.'/'.$basename;
                
                if ( !file_exists( $newFile ) ) {
                    if ( !rename( $file, $newFile ) ) {
                        GoodTools::echoLine( 'Can not move '.$file.' to '.$newFile.'.' );
                    }
                }
            }
            
            $suffixes = $backend[ 'suffixes' ];
            $suffixes[] = $backend[ 'suffixe' ];
            $files = $this->getFiles( $zipInfo[ 2 ], $this->createFnMatchMasks( '*.%1', $suffixes ) );
            
            return count( $files );
        }
        
        public function finishBackend( $zipInfo ) {
            $backend = $this->backend( $zipInfo[ 0 ] );
            
            if ( !$backend ) {
                GoodTools::echoLine( 'No backend to finish: '.$zipInfo[ 0 ].').' );
                return;
            }
            
            $pathName = $zipInfo[ 2 ].'/Unknown';
            
            if ( !is_dir( $pathName ) ) {
                if ( !mkdir( $pathName, 0777, true ) ) {
                    GoodTools::echoLine( 'Can not create '.$pathName.' ('.$zipInfo[ 0 ].').' );
                    return;
                }
            }
            
            $suffixes = $backend[ 'suffixes' ];
            $suffixes[] = $backend[ 'suffixe' ];
            
            $this->moveFiles( $zipInfo[ 2 ], $pathName, $this->createFnMatchMasks( '*.%1', $suffixes ) );
        }
        
        public function updateSets() {
            $options = GoodTools::options();
            $zips = $this->getFiles( $options[ 'binaries' ], array( '*.zip' ) );
            
            foreach ( $zips as $zip ) {
                $zipInfo = $this->getZipInformations( $zip, $options[ 'roms' ] );
                
                if ( !$zipInfo ) {
                    GoodTools::echoLine( 'Can not get informations for '.$zip.' ('.$zipInfo[ 0 ].').' );
                    continue;
                }
                
                if ( !in_array( $zipInfo[ 0 ], $options[ 'tools' ] ) ) {
                    continue;
                }
                
                if ( is_dir( $zipInfo[ 3 ] ) ) {
                    if ( $zipInfo[ 2 ] != $zipInfo[ 3 ] ) {
                        if ( !rename( $zipInfo[ 3 ], $zipInfo[ 2 ] ) ) {
                            GoodTools::echoLine( 'Can not move '.$zipInfo[ 3 ].' to '.$zipInfo[ 2 ].' ('.$zipInfo[ 0 ].').' );
                            continue;
                        }
                    }
                }
                else {
                    if ( !mkdir( $zipInfo[ 2 ], 0777, true ) ) {
                        GoodTools::echoLine( 'Can not create '.$zipInfo[ 2 ].' ('.$zipInfo[ 0 ].').' );
                        continue;
                    }
                }
                
                $currentPath = getcwd();
                $output = null;
                $exitCode = null;
                
                chdir( $zipInfo[ 2 ] );
                
                exec( 'unzip -o '.$zip.' Good*.exe Good*.cfg', $output, $exitCode );
                
                if ( $exitCode === 0 ) {
                    GoodTools::echoLine( 'Successfully update GoodTool '.$zipInfo[ 2 ].' ('.$zipInfo[ 0 ].').' );
                }
                else {
                    GoodTools::echoLine( 'Can not update GoodTool '.$zipInfo[ 2 ].' ('.$zipInfo[ 0 ].').' );
                    continue;
                }
                
                if ( $this->prepareBackend( $zipInfo ) > 0 ) {
                    exec( 'wine *ood*.exe rename dirs', $output, $exitCode );
                    
                    if ( $exitCode === 0 ) {
                        GoodTools::echoLine( 'Successfully update '.$zipInfo[ 2 ].' ('.$zipInfo[ 0 ].').' );
                    }
                    else {
                        GoodTools::echoLine( 'Can not update '.$zipInfo[ 2 ].' ('.$zipInfo[ 0 ].').' );
                    }
                }
                
                $this->finishBackend( $zipInfo );
                
                chdir( $currentPath );
            }
        }
    }
    
    $gt = new GoodTools;
    $gt->updateSets();
    unset( $gt );
?>