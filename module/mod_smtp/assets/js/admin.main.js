/* -------------------------------------------------------------------
 * Author Name           : Bbfpl
 * Author URI            : https://codecanyon.net/user/bbfpl
 * Version               : 1.1.1
 * File Name             : admin.min.js
------------------------------------------------------------------- */

(function ($) {
    "use strict";

    function handle_ajax(url,button, data, type, cb) {
        button.addClass('loading');
        button.attr("disabled", "true");
        jQuery.ajax({
            url: url,
            data: JSON.stringify(data),
            contentType: "application/json",
            dataType: "json",
            type: type,
            success: function (response) {
                setTimeout(() => {
                    button.removeClass("loading");
                    button.removeAttr("disabled");
                }, 1000);

                if (response.code == 200) {
                    spop({
                        template: response.msg,
                        position: "top-center",
                        style: "success",
                        autoclose: 1200,
                        onClose: function () {
                            if (cb) {
                                cb(response.data);
                            }
                        }
                    });
                } else {
                    spop({
                        template: response.msg,
                        position: "top-center",
                        style: "error",
                        autoclose: 1200
                    });
                }
            }
        });
    }


    $(document).on("click", ".update-btn", function () {
        let button = $(this);
        // submit data
        let data = {
            smtp_host: $("#smtp_host").val().trim(),
            smtp_port: $("#smtp_port").val().trim(),
            auth_addr: $("#auth_addr").val().trim(),
            auth_pass: $("#auth_pass").val().trim(),
            from_name: $("#from_name").val().trim(),
            from_addr: $("#from_addr").val().trim()
        };
        handle_ajax("/mod_smtp/save",button, data, "POST");
    });

    $(document).on("click", ".test-settings-toggle-btn", function () {
        $(".test-settings").slideToggle(300);
    });

    $(document).on("click", ".test-send-btn", function () {
        let button = jQuery(this);

        // submit data
        let data = {
            to_addr: $("#test_to_addr").val().trim(),
            subject: $("#test_subject").val().trim(),
            body: $("#test_body").val().trim(),
        };

        handle_ajax("/mod_smtp/test",button, data, "POST");
    });
})(window.jQuery);