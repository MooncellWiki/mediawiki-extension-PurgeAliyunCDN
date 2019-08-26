<?php
require_once(dirname(dirname(__FILE__)) . '/vendor/autoload.php');
use MediaWiki\MediaWikiServices;
use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;
class SpecialCDNPurgeLog extends SpecialPage{
    function __construct(){
        parent::__construct('cdn_purge_log','purgeCDN');
    }
    function getGroupName() {
        return 'CDN';
    }
    function execute($par){
        global $wgAliyunAccessKeyId,$wgAliyunAccessSecret,$wgAliyunDomain;
        if (!$this->getUser()->isAllowed( 'purgeCDN' ) ) {
			throw new PermissionsError( 'purgeCDN' );
		}
        $output=$this->getOutput();
        $this->setHeaders();
        if(!isset($wgAliyunAccessKeyId)){
            $output->addHTML($this->msg('cdn-require-AccessKeyId')->plain());
            return false;
        }
        if(!isset($wgAliyunAccessSecret)){
            $output->addHTML($this->msg('cdn-require-AccessSecret')->plain());
            return false;
        }
        if(!isset($wgAliyunDomain)){
            $output->addHTML($this->msg('cdn-require-domain')->plain());
            return false;
        }
        AlibabaCloud::accessKeyClient($wgAliyunAccessKeyId, $wgAliyunAccessSecret)
                        ->regionId('cn-hangzhou') // replace regionId as you need
                        ->asDefaultClient();

        try {
            $result='';
            $fileResult = AlibabaCloud::rpc()
                          ->product('Cdn')
                          //->scheme('https') // https | http
                          ->version('2018-05-10')
                          ->action('DescribeRefreshTasks')
                          ->method('POST')
                          ->host('cdn.aliyuncs.com')
                          ->options([
                                        'query' => [
                                          'RegionId' => "default",
                                          'ObjectType' => "file",
                                          'DomainName' => $wgAliyunDomain,
                                        ],
                                    ])
                          ->request();

            $rows='';
            foreach ($fileResult->toArray()['Tasks']['CDNTask'] as $value) {
                $rows=$rows.'<tr><td>'.urldecode($value['ObjectPath']).'</td><td>'.date('Y-m-d H:i:s',strtotime($value['CreationTime'])).'</td><td>'.$value['Status'].'</td><td>'.$value['Process'].'</td></tr>';
            }
            $output->addHTML('<h1>'.wfMessage('cdn-refresh-url').'</h1><table class="wikitable"><tbody><tr>'.wfMessage('cdn-table-head')->text().'</tr>'.$rows.'</tbody></table>');
            //$output->addHTML(json_encode($fileResult->toArray()));
            $UrlResult = AlibabaCloud::rpc()
                          ->product('Cdn')
                          //->scheme('https') // https | http
                          ->version('2018-05-10')
                          ->action('DescribeRefreshTasks')
                          ->method('POST')
                          ->host('cdn.aliyuncs.com')
                          ->options([
                                        'query' => [
                                          'RegionId' => "default",
                                          'ObjectType' => "directory",
                                          'DomainName' => $wgAliyunDomain,
                                        ],
                                    ])
                          ->request();
            $rows='';
            foreach ($UrlResult->toArray()['Tasks']['CDNTask'] as $value) {
                $rows=$rows.'<tr><td>'.urldecode($value['ObjectPath']).'</td><td>'.strtotime($value['CreationTime']).'</td><td>'.$value['Status'].'</td><td>'.$value['Process'].'</td></tr>';
            }
            $output->addHTML('<h1>'.wfMessage('cdn-refresh-dir').'</h1><table class="wikitable"><tbody><tr>'.wfMessage('cdn-table-head')->text().'</tr>'.$rows.'</tbody></table>');
        } catch (ClientException $e) {
            echo $e->getErrorMessage() . PHP_EOL;
        } catch (ServerException $e) {
            echo $e->getErrorMessage() . PHP_EOL;
        }
    }
}
