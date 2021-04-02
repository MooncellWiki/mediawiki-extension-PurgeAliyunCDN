<?php
namespace PurgeAliyunCDN;

class Utils {
    
    public static function PostJson( $url, $array ) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($array));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return array($httpCode, $response);
    }
    
    public static function getThumbnails( $image ) {
        $dir = $image->getThumbPath();
        $backend = $image->getRepo()->getBackend();
        $files = [ $dir ];
        try {
            $iterator = $backend->getFileList( [ 'dir' => $dir , 'topOnly' => true ] );
            if ( $iterator !== null ) {
                foreach ( $iterator as $file ) {
                    $files[] = $file;
                }
            }
        } catch ( FileBackendError $e ) { }
        return $files;
    }
    
    
    public static function getThumbnailsURL( $image ) {
        global $wgServer;
        
        $basedir = $image->getThumbUrl();
        $thumbs = Utils::getThumbnails( $image );
  
        $dir = array_shift( $thumbs );
        $urls = [];
        foreach ( $thumbs as $thumb ) {
            $urls[] = "{$wgServer}{$basedir}/{$thumb}";
        }
        return $urls;
     }
    
}