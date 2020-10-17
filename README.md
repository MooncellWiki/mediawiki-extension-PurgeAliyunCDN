1. [配置阿里云函数计算](https://github.com/MooncellWiki/PurgeAliyunCDN-fc)
2. 安装拓展
```
cd extensions/
git clone https://github.com/mooncellwiki/PurgeAliyunCDN.git
```
在LocalSetting.php中加入  
```php
wfLoadExtension( 'PurgeAliyunCDN' );
$wgAliyunCloudFuncUrl="";//云函数入口点
$wgAliyunCloudFuncToken="";//云函数Token
```
