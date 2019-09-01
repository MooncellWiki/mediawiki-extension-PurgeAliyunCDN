<?php
require_once(dirname(dirname(__FILE__)) . '/vendor/autoload.php'); 
use MediaWiki\MediaWikiServices;
use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;

class SpecialPurgeAliyunCDN extends SpecialPage{
    function __construct(){
        parent::__construct('purge_aliyun_cdn','purgeCDN');
    }
    function getGroupName() {
        return 'CDN';
    }
    function execute($par){
        global $wgAliyunAccessKeyId,$wgAliyunAccessSecret,$wgAliyunPurgeUrlRegex;
        if (!$this->getUser()->isAllowed( 'purgeCDN' ) ) {
			throw new PermissionsError( 'purgeCDN' );
		}
        $output=$this->getOutput();
        if(!isset($wgAliyunAccessKeyId)){
            $output->addHTML($this->msg('cdn-require-AccessKeyId')->plain());
            return false;
        }
        if(!isset($wgAliyunAccessSecret)){
            $output->addHTML($this->msg('cdn-require-AccessSecret')->plain());
            return false;
        }
        if(!isset($wgAliyunAccessSecret)){
            $output->addHTML($this->msg('cdn-require-AccessSecret')->plain());
            return false;
        }
        
        if(!AlibabaCloud::has('default')){
        	AlibabaCloud::accessKeyClient($wgAliyunAccessKeyId, $wgAliyunAccessSecret)
            	->regionId('cn-hangzhou') // replace regionId as you need
            	->asDefaultClient();
        }
        try {
            $result = AlibabaCloud::rpc()
                                  ->product('Cdn')
                                  ->version('2018-05-10')
                                  ->action('DescribeRefreshQuota')
                                  ->method('POST')
                                  ->host('cdn.aliyuncs.com')
                                  ->options([
                                                'query' => [
                                                  'RegionId' => "default",
                                                ],
                                            ])
                                  ->request();
            $arr=$result->toArray();
        } catch (ClientException $e) {
            echo $e->getErrorMessage() . PHP_EOL;
        } catch (ServerException $e) {
            echo $e->getErrorMessage() . PHP_EOL;
        }
        
        $this->setHeaders();
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
                'label-raw'=>wfMessage( 'cdn-refresh-limit', $arr['UrlQuota'], $arr['DirQuota'], $arr['UrlRemain'], $arr['DirRemain'])->parse(),
            ]
        ];
        $form = HTMLForm::factory('ooui',$fields,$this->getContext());
        $form
            ->setSubmitTextMsg('cdn-refresh-submit')
            ->setSubmitCallback([$this,'onSubmit'])
            ->showAlways();
        $output->addModules('ext.RefreshCDNCheck');
    }
    public function onSubmit(array $values){
        //return $values['refresh-type'].'<br/>'.$values['refresh-url'];
        try {
            $result = AlibabaCloud::rpc()
                                        ->product('Cdn')
                                        ->version('2018-05-10')
                                        ->action('RefreshObjectCaches')
                                        ->method('POST')
                                        ->host('cdn.aliyuncs.com')
                                        ->options([
                                                    'query' => [
                                                        'RegionId' => "default",
                                                        'ObjectPath' => $values['refresh-url'],
                                                        'ObjectType' => $values['refresh-type'],
                                                    ],
                                                  ])
                                        ->request();
            if(array_key_exists("RefreshTaskId",$result->toArray())&&array_key_exists("RequestId",$result->toArray())){
                return 'cdn-send-request-success' ;
            }else{
                return $result->toArray();
            }
        } catch (ClientException $e) {
            return $e->getErrorMessage() . PHP_EOL;
        } catch (ServerException $e) {
            return $e->getErrorMessage() . PHP_EOL;
        }
    }
}
?>