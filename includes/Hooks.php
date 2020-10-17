<?php
namespace PurgeAliyunCDN;

class Hooks {
    
	public static function onUploadVerifyFile( $upload, $mime, &$error ) {
	    global $wgAliyunCloudFuncUrl,$wgAliyunCloudFuncToken,$wgServer;
	    
	    $url = $wgServer.$upload->getLocalFile()->url;
	    $isExists = $upload->getLocalFile()->exists();
        if($isExists != true) {
            return true;
        }
        
        $path = explode("\n",$url);
        $payload = array(
                'token' => $wgAliyunCloudFuncToken,
                'action' => 'purge',
                'isFolder' => false,
                'path' => $path
            );
        $result = Utils::PostJson($wgAliyunCloudFuncUrl, $payload);

		return true;
	}
	
}