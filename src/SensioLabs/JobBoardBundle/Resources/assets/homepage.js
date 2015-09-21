$(function () {
    var load = false;
    var offset = $('.box:last').offset();
    var loader = $('div#loadmoreajaxloader');

    $(window).scroll(function() {
        if ((offset.top-$(window).height() <= $(window).scrollTop()) && load === false) {
            load = true;
            loader.show();

            var page = parseInt(loader.attr('data-page')) + 1;

            $.ajax({
                type: "GET",
                url: loader.attr('data-url'),
                data: {'page' : page},
                success: function(msg){
                    if (msg.length > 0) {
                        $('div#loadmoreajaxloader').hide();
                        $('div#job-container').append(msg);
                        $('#loadmoreajaxloader').attr('data-page', page.toString());
                        offset = $('.box:last').offset();
                        load = false;
                    } else {
                        $('div#loadmoreajaxloader').html(loader.attr('data-empty'));
                    }
                }
            });
        }
    });
});
