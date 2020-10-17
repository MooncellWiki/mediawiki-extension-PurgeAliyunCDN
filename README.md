```
cd extensions/
git clone https://github.com/mooncellwiki/PurgeAliyunCDN.git
cd PurgeAliyunCDN/
composer install  
```
在LocalSetting.php中加入  
```php
wfLoadExtension( 'PurgeAliyunCDN' );
$wgAliyunAccessSecret="";//阿里云AccessKey Secret
$wgAliyunAccessKeyId="";//阿里云AccessKey ID
$wgAliyunDomain="";//要刷新的域名
```
