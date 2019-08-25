<?php
require dirname(dirname(__FILE__)) . '/vendor/autoload.php';
use MediaWiki\MediaWikiServices;

class SpecialCDNPurgeLog extends SpecialPage{
    function __construct(){
        parent::__construct('cdn_purge_log','purgeCDN');
    }
    function getGroupName() {
        return 'CDN';
    }
    function execute($par){
        global $wgAliyunAccessKeyId,$wgAliyunAccessSecret;
    }
}
