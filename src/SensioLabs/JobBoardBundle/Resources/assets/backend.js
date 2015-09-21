$(function() {
    $("#job_endedAt").datepicker({'dateFormat' : 'mm/dd/yy'});
    $("#job_publishedAt").datepicker({'dateFormat' : 'mm/dd/yy'});
    $('#job_description').markItUp(mySettings);

    function split( val ) {
        return val.split( /,\s*/ );
    }
    function extractLast( term ) {
        return split( term ).pop();
    }

    $('#sendForm').bind('click', function() {
        var error = false;
        $('.formError').each( function () {
            $(this).removeClass('formError');
        });
        $('#error').css('display', 'none');
        $('#error ul>li').each( function () {
            $(this).css('display', 'none');
        });

        $('input[type="text"]').each (function () {
            if ($(this).attr('id') != 'job_howToApply') {
                if (($(this).val().length < 2 || $(this).val() == $(this).attr('placeholder')) ||
                        ($(this).attr('id') == 'search_country' && $('input#job_location_country').val().length == 0)) {

                    $(this).addClass('formError');
                    $('#' + $(this).attr('id') + '_error').css('display', '');
                    error = true;
                }
            }
        })
        $('select').each (function () {
            if ($(this).attr('value') === $('#' + $(this).attr('id') + ' option:first').val()) {
                $(this).parent().addClass('formError');
                $('#' + $(this).attr('id') + '_error').css('display', '');
                error = true;
            }
        })
        if ($('#job_description').val().length < 10) {
            $('#job_description').addClass('formError');
            $('#job_description_error').css('display', '');
            error = true;
        }

        if (error === false) {
            $('#addJob').submit();
        } else {
            $('#error').css('display', 'block');
            $('html, body').animate({
                scrollTop: $("#breadcrumb").offset().top
            }, 100);
        }
    });
});
