$('#ooui-php-2').on('focus', function (e) {
    var t = $(e.currentTarget);
    var val = t.val();
    console.log(val);
    if (val == '' || val == undefined) {
        if ($('#url-warn').length == 0) {
            t.after('<span id="url-warn" style="color:red;">请输入URL</span>');
        } else {
            $('#url-warn').css('display', 'inline');
            $('#url-warn').html('<span id="url-warn" style="color:red;">请输入URL</span>');
        }
        $("#ooui-php-5 > button").attr('disabled', "true")
    }
});
$('#ooui-php-2').on('input', function (e) {
    var val = $(e.currentTarget).val();
    if (val == '' || val == undefined) {
        $('#url-warn').css('display', 'inline');
        $('#url-warn').html('<span id="url-warn" style="color:red;">请输入URL</span>');
        $("#ooui-php-5 > button").attr('disabled', "true");
    } else {
        var arr = val.split('\n');
        var flag = true;
        for (var i in arr) {
            if (arr[i].indexOf('https://') != 0 && arr[i].indexOf('http://') != 0) {
                $('#url-warn').html('<span id="url-warn" style="color:red;">请输入正确的URL，必须以http://或https://开头，不能重复，刷新目录下应该用/结尾</span>');
                flag = false;
                break;
            }
        }
        if (flag) {
            $('#url-warn').css('display', 'none');
            $("#ooui-php-5 > button").removeAttr("disabled");
        } else {
            $('#url-warn').css('display', 'inline');
        }
    }
});