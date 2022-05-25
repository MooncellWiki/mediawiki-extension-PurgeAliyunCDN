<?php
namespace PurgeAliyunCDN;

class Hooks {
	
	public static function onUploadComplete( $image ) {
		global $wgAliyunCloudFuncUrl, $wgAliyunCloudFuncToken, $wgServer;
		
		$history = $image->getLocalFile()->getHistory();
		if (count($history) != 0) {
			$url = wfExpandUrl($image->getLocalFile()->url);
			//purge source
			$path = [$url];
			$payload = [
					'token' => $wgAliyunCloudFuncToken,
					'action' => 'purge',
					'isFolder' => false,
					'path' => $path
				];
			$result = Utils::PostJson($wgAliyunCloudFuncUrl, $payload);
			//purge thumb
			$thumbUrl = str_replace("{$wgServer}/images", "{$wgServer}/images/thumb", $url);
			$thumbPath = [$thumbUrl];
			$thumbPayload = [
					'token' => $wgAliyunCloudFuncToken,
					'action' => 'purge',
					'isFolder' => false,
					'path' => $thumbPath
				];
			$result = Utils::PostJson($wgAliyunCloudFuncUrl, $thumbPayload);
			
			$user = $image->getLocalFile()->getUser('object');
			$title = $image->getTitle();
			
			$logEntry = new \ManualLogEntry('purgecdn', 'purge');
			$logEntry->setPerformer($user);
			$logEntry->setTarget($title);
			$logId = $logEntry->insert();
			$logEntry->publish($logId);
		}
		
		return true;
	}
	
	public static function onSkinTemplateNavigation_Universal(\SkinTemplate $skinTemplate, array &$links) {
		$isAdmin = $skinTemplate->getUser()->isAllowed('purge-aliyun-cdn');
		$title = $skinTemplate->getRelevantTitle();
		
		if ( $title->getNamespace() === NS_FILE && $isAdmin ) {
			// add a new action
			$links['actions']['aliyunpurgethumb'] = [
				'id' => 'ca-aliyunpurgethumb',
				'text' => wfMessage('aliyunpurgethumb')->text(),
				'href' => \SpecialPage::getTitleFor('CDNPurgeThumb', $title->getPrefixedDBKey())->getLocalURL()
			];
		}

		return true;
	}
	
}
