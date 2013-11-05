<?php

/**
 * script compiles css from given less file   
 * reference: https://github.com/stdclass/ezless/issues/15#issuecomment-15904797 
 * @copyright land in sicht ag 2013
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version 0.1
 * @eZversion > 4.x
 */

class lisLessCompiler extends ezjscServerFunctions
{    
    /**
     * Compiles the given less-file and appends the generated css-file to the list.
     * Usage in templates: {ezcss_require('myprefix::lessc::file.less')}
     *
     * @param array $args
     * @param array $packerFiles ByRef list of files to pack (by ezjscPacker)
     */
    public static function lessc( $args, &$packerFiles )
    {
        $lessFile = is_array($args) ? $args[0] : $args;
        $cssFile = str_replace('.less', '.css', $lessFile);

        // Only compile less-stylesheets in development mode, in production just return the css-version.
        if ( eZINI::instance()->variable( 'TemplateSettings', 'DevelopmentMode' ) === 'enabled' ) {

            // Find first matching file in design resources
            $bases = eZTemplateDesignResource::allDesignBases();
            $triedFiles = array();
            $match = eZTemplateDesignResource::fileMatch( $bases, 'stylesheets', $lessFile, $triedFiles );
            
            if ( $match === false ) {
            
                eZDebug::writeError( "Could not find: $lessFile", __METHOD__ );
            
            } else {
            
                $lessPath = $match['path'];
                $cssPath = $match['resource'] . '/' . $cssFile;

                // Compile the less-file
                $less = new lessc;
                try {
                    $less->compileFile($lessPath, $cssPath);
                } catch (exception $e) {
                    eZDebug::writeError($e->getMessage(), __METHOD__ );
                }
            
            }
        }

        array_unshift($packerFiles, $cssFile);
        return '';
    }

    public static function getCacheTime( $functionName )
    {
        // Functions that always needs to be executed, since they append other files dynamically
        if ( $functionName === 'lessc' )
            return -1;

        static $mtime = null;
        if ( $mtime === null )
        {
            $mtime = filemtime( __FILE__ );
        }
        return $mtime;
    }
}

?>