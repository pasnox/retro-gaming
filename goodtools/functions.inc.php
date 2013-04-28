<?php
define( 'NL', "\n" );
define( 'TAB', "\t" );

function array_ikey_exists( $key, $array ) {
    return in_array( strtolower( $key ), array_map( 'strtolower', array_keys( $array ) ) );
}

class Tools {
    public static function echoLine( $string ) {
        echo $string.NL;
    }
    
    public static function createFnMatchMasks( $mask, $extensions ) {
        $masks = array();
        
        if ( is_array( $extensions ) ) {
            if ( empty( $extensions ) ) {
                $masks[] = str_replace( '%1', '', $mask );
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
    
    public static function getFiles( $pathName, $masks, $flags = FNM_CASEFOLD ) {
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
    
    public static function getDirectories( $pathName, $masks, $flags = FNM_CASEFOLD ) {
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
    
    public static function moveFiles( $sourcePath, $targetPath, $wildcard = array( '*' ) ) {
        $files = Tools::getFiles( $sourcePath, $wildcard );
        
        foreach ( $files as $file ) {
            $newFile = $targetPath.'/'.basename( $file );
            
            if ( !rename( $file, $newFile ) ) {
                Tools::echoLine( 'Can not move '.$file.' to '.$newFile.'.' );
            }
        }
    }
    
    public static function isMounted( $folderPath ) {
        $command = "mount | grep \"$folderPath\"";
        $output = null;
        $exitCode = null;
        
        exec( $command, $output, $exitCode );
        return strpos( implode( NL, $output ), $folderPath ) !== false;
    }
}
?>
