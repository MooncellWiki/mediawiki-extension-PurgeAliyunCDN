<?php
namespace PurgeAliyunCDN;

use MediaWiki\MediaWikiServices;

class SpecialPurgeThumb extends \FormSpecialPage {
    
    protected $title;
    
    public function __construct(){
        parent::__construct('CDNPurgeThumb','purge-aliyun-cdn', false);
    }
    
    public function doesWrites() {
		return true;
	}
	
	protected function getDisplayFormat() {
		return 'ooui';
	}
    
    protected function getFormFields() {
        $fileList = array( $this->title->getDBKey() );
        $images = \RepoGroup::singleton()->findFiles( $fileList );
        $urls = Utils::getThumbnailsURL( array_shift( $images ) );
        
        $options = [];
        foreach ( $urls as $url ) {
            $options["<img src=\"{$url}\" />"] = $url;
        }
        
		$fields['ThumbSelection'] = [
		    'type' => 'multiselect',
		    'label-message' => 'cdnpurgethumb-select-label',
		    'options' => $options,
		    'default' => $urls,
        ];
        
		return $fields;
    }
    
    public function onSubmit( array $data ) {
        global $wgAliyunCloudFuncUrl, $wgAliyunCloudFuncToken;
        
        $payload = [
                'token' => $wgAliyunCloudFuncToken,
                'action' => 'purge',
                'path' => $data['ThumbSelection']
            ];
        $result = Utils::PostJson($wgAliyunCloudFuncUrl, $payload);
        return 'cdn-send-request-success';
    }
    
    protected function setParameter( $par ) {
		$title = \Title::newFromText( $par );
		$this->title = $title;

		if ( !$title ) {
			throw new \ErrorPageError( 'notargettitle', 'notargettext' );
		}
		if ( !$title->exists() ) {
			throw new \ErrorPageError( 'nopagetitle', 'nopagetext' );
		}
		
		if ( !$title->getNamespace() === NS_FILE ) {
		    throw new \ErrorPageError( 'notargettitle', 'notargettext' );
		}

        if (!$this->getUser()->isAllowed( 'purge-aliyun-cdn' ) ) {
			throw new PermissionsError( 'purge-aliyun-cdn' );
        }
        
    }
    public function execute($par){
        global $wgAliyunCloudFuncUrl, $wgAliyunCloudFuncToken;
        
        parent::execute( $par );
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
        
    }
}
