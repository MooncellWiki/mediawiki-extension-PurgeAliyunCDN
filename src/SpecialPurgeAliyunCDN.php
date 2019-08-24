<?php

use MediaWiki\MediaWikiServices;

class SpecialPurgeAliyunCDN extends SpecialPage {
    function __construct(){
        parent::__construct('purgeAliyunCDN','purgeAliyunCDN');
    }
    
    function execute($par){
        $request=$this.getRequest();
        $output=$this->getOutput();
        $this->setHeaders();

        $param = $request->getText('param');
        $wikitext="Hello world";
        $output->AddWikiText($wikitext);
    }
}
?>