```
cd extensions/
git clone https://github.com/mooncellwiki/PurgeAliyunCDN.git
cd PurgeAliyunCDN/
```
在LocalSetting.php中加入  
```php
wfLoadExtension( 'PurgeAliyunCDN' );
$wgAliyunCloudFuncUrl="";//云函数入口点
$wgAliyunCloudFuncToken="";//云函数Token
```
