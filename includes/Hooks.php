<?php
namespace PurgeAliyunCDN;

class Hooks {
    
	public static function onUploadVerifyUpload( \UploadBase $upload, \User $user, $props, $comment, $pageText, &$error ) {
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
        
        $logEntry = new \ManualLogEntry('purgecdn', 'purge');
		$logEntry->setPerformer($user);
		$logEntry->setTarget($upload->getTitle());
		$logId = $logEntry->insert();
		$logEntry->publish($logId);

		return true;
	}
	
}