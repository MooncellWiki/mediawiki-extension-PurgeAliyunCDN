下载 解压 塞到extensions里  
在 PurgeAliyunCDN目录下 运行  
```
php composer.phar install
```
或
```
composer install  
```
LocalSetting.php中加入  
```php
wfLoadExtension( 'PurgeAliyunCDN' );
$wgAliyunAccessSecret="";//阿里云AccessKey Secret
$wgAliyunAccessKeyId="";//阿里云AccessKey ID
```