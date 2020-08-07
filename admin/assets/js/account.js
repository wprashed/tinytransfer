(function($) {
    "use strict";

    jQuery("#settingAccountForm").on("submit", function(event) {
        var $this = jQuery(this),
            $formActionURL = $this.attr("action");

        //  Form Input Value
        var name = jQuery("#name").val().trim(),
            password = jQuery("#password").val().trim();

        $this.find(":submit").addClass('loading');
        $this.find(":submit").attr("disabled", "true");
        jQuery.ajax({
            url: $formActionURL,
            data: {
                name: name,
                password: password
            },
            type: "POST",
            success: function(response) {
                jQuery("#settingAccountForm")[0].reset();
                jQuery("#settingAccountForm")
                    .find(":submit")
                    .removeAttr("disabled");
                $this
                    .find(":submit")
                    .removeClass("loading");

                if (response.code == 200) {
                    spop({
                        template: "Update successfully!",
                        position: "top-center",
                        style: "success",
                        autoclose: 1200
                    });
                } else {
                    spop({
                        template: "Update failed!",
                        position: "top-center",
                        style: "error",
                        autoclose: 1200
                    });
                }
            }
        });

        event.preventDefault();
    });
})(jQuery);