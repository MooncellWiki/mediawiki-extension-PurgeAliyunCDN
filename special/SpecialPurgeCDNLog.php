<?php
namespace PurgeAliyunCDN;

use MediaWiki\MediaWikiServices;

class SpecialPurgeCDNLog extends \SpecialPage {
    
    function __construct(){
        parent::__construct('CDNPurgeLog','purge-aliyun-cdn');
    }
    
    function getGroupName() {
        return 'CDN';
    }
    
    function execute($par){
        global $wgAliyunCloudFuncUrl, $wgAliyunCloudFuncToken;
        
        if (!$this->getUser()->isAllowed( 'purge-aliyun-cdn' ) ) {
			throw new PermissionsError( 'purge-aliyun-cdn' );
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
        try {
            $payload = array(
                'token' => $wgAliyunCloudFuncToken,
                'action' => 'Tasks',
            );
            $result = Utils::PostJson($wgAliyunCloudFuncUrl, $payload);
            $fileResult = json_decode($result[1], true);
            
            $rows='';
            foreach ($fileResult['Tasks']['CDNTask'] as $value) {
                $rows=$rows.'<tr><td>'.urldecode($value['ObjectPath']).'</td><td>'.date('Y-m-d H:i:s',strtotime($value['CreationTime'])).'</td><td>'.$value['Status'].'</td><td>'.$value['Process'].'</td></tr>';
            }
            $output->addHTML('<table class="wikitable"><tbody><tr>'.wfMessage('cdn-table-head')->text().'</tr>'.$rows.'</tbody></table>');
        } catch (\Throwable $e) {
            echo $e->getErrorMessage() . PHP_EOL;
        }
    }
}
