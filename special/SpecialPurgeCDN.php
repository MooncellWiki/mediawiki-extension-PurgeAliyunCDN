<?php
namespace PurgeAliyunCDN;

use MediaWiki\MediaWikiServices;
use PurgeAliyunCDN\Utils;

class SpecialPurgeCDN extends \SpecialPage {
    
    function __construct(){
        parent::__construct('PurgeAliyunCDN','purgeCDN');
    }
    
    function getGroupName() {
        return 'CDN';
    }
    
    function execute($par){
        global $wgAliyunCloudFuncUrl,$wgAliyunCloudFuncToken;
        
        if (!$this->getUser()->isAllowed( 'purgeCDN' )) {
			throw new PermissionsError('purgeCDN');
		}
		
        $output=$this->getOutput();
        $this->setHeaders();
        if(!isset($wgAliyunCloudFuncToken)){
            $output->addHTML($this->msg('cdn-require-FuncToken')->plain());
            return false;
        }
        
        if(!isset($wgAliyunCloudFuncUrl)){
            $output->addHTML($this->msg('cdn-require-FuncUrl')->plain());
            return false;
        }
        
        $payload = array(
                'token' => $wgAliyunCloudFuncToken,
                'action' => 'Quota',
            );
        try {
            $result = Utils::PostJson($wgAliyunCloudFuncUrl, $payload);
            $quota = json_decode($result[1], true);
        } catch (\Throwable $e) {
            echo $e->getErrorMessage() . PHP_EOL;
        }
        
        $fields=[
            'refresh-type'=>[
                'type'=>'select',
                'label'=>$this->msg('cdn-refresh-type')->plain(),
                'options'=>[
                    $this->msg('cdn-refresh-url')->plain()=>'File',
                    $this->msg('cdn-refresh-dir')->plain()=>'Directory'
                ]
            ],
            'refresh-url'=>[
                'type'=>'textarea',
                'label-raw'=>wfMessage( 'cdn-refresh-limit', $quota['UrlQuota'], $quota['DirQuota'], $quota['UrlRemain'], $quota['DirRemain'])->parse(),
            ]
        ];
        $form = \HTMLForm::factory('ooui',$fields,$this->getContext());
        $form
            ->setSubmitTextMsg('cdn-refresh-submit')
            ->setSubmitCallback([$this,'onSubmit'])
            ->showAlways();
        $output->addModules('ext.RefreshCDNCheck');
    }
    
    public function onSubmit(array $values) {
        global $wgAliyunCloudFuncUrl,$wgAliyunCloudFuncToken;
        
        $path = explode("\n",$values['refresh-url']);
        $payload = [
                'token' => $wgAliyunCloudFuncToken,
                'action' => 'purge',
                'path' => $path
            ];
        switch ($values['refresh-type']) {
        case 'File':
            $payload['isFolder'] = false;
            break;
        case 'Directory':
            $payload['isFolder'] = true;
            break;
        }
        $result = Utils::PostJson($wgAliyunCloudFuncUrl, $payload);

        return 'cdn-send-request-success';
    }
    
}
?>