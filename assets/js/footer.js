jQuery(document).ready(function ($) {
    var IS_IPAD = navigator.userAgent.match(/iPad/i) != null,
        IS_IPHONE = !IS_IPAD && ((navigator.userAgent.match(/iPhone/i) != null) || (navigator.userAgent.match(/iPod/i) != null)),
        IS_IOS = IS_IPAD || IS_IPHONE,
        IS_ANDROID = !IS_IOS && navigator.userAgent.match(/android/i) != null,
        IS_MOBILE = IS_IOS || IS_ANDROID;
    var application_apple_id = $('.panel-open-application #application_apple_id');
    var application_package = $('.panel-open-application #application_package');
    var buttonOpen = $('.panel-open-application #open_application');
    var buttonClose = $('.panel-open-application #close_application');
    var open_application = function () {
        var scheme = buttonOpen.attr('data-scheme');
        var apple_id = application_apple_id.val();
        var package = application_package.val();
        if (!scheme) return;
        if (IS_IOS) {
            window.location = scheme + '://' + window.location.href;
            if (apple_id) setTimeout(function () {
                if (!document.webkitHidden) {
                    window.location = 'https://itunes.apple.com/app/id' + apple_id;
                }
            }, 25);
        } else if (IS_ANDROID) {
            window.location = 'intent://' + window.location.href + '#Intent;package=' + package + ';scheme=' + scheme + ';end;';
        }
    };
    buttonOpen.bind('click', function () {
        open_application();
    });
    if (buttonOpen.attr('data-auto') == 1) setTimeout(function () { buttonOpen.click(); }, 200);
    buttonClose.bind('click', function () {
        var date = new Date();
        date.setTime(date.getTime() + (Number(buttonClose.data('second')) || 0) * 1000);
        document.cookie = 'stionic_hide_deeplinks=1; expires=' + (buttonClose.data('second') == -1 ? 0 : date.toUTCString()) + ';  path=/';
        $('.panel-open-application').hide();
    });
});