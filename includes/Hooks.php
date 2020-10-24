<?php
namespace PurgeAliyunCDN;

class Hooks {
	
	public static function onUploadComplete( $image ) {
		global $wgAliyunCloudFuncUrl,$wgAliyunCloudFuncToken,$wgServer;
		
		$history = $image->getLocalFile()->getHistory();
		if (count($history) != 0) {
			$url = wfExpandUrl($image->getLocalFile()->url);
		
			$path = [$url];
			$payload = [
					'token' => $wgAliyunCloudFuncToken,
					'action' => 'purge',
					'isFolder' => false,
					'path' => $path
				];
			$result = Utils::PostJson($wgAliyunCloudFuncUrl, $payload);
			
			$user = $image->getLocalFile()->user;
			$logEntry = new \ManualLogEntry('purgecdn', 'purge');
			$logEntry->setPerformer($user);
			$logEntry->setTarget($upload->getTitle());
			$logId = $logEntry->insert();
			$logEntry->publish($logId);
		}
		
		return true;
	}
	
}