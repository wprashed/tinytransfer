(function ($) {
  "use strict";

  jQuery("input[type='password'][data-eye]").each(function (i) {
    var $this = jQuery(this);
    $this.wrap(
      jQuery("<div/>", {
        style: "position:relative"
      })
    );

    $this.css({
      paddingRight: 60
    });
    $this.after(
      jQuery("<div/>", {
        html: "Show",
        class: "btn btn-primary btn-sm",
        id: "passeye-toggle-" + i,
        style:
          "position:absolute;right:10px;top:50%;transform:translate(0,-50%);padding: 2px 7px;font-size:12px;cursor:pointer;"
      })
    );
    $this.after(
      jQuery("<input/>", {
        type: "hidden",
        id: "passeye-" + i
      })
    );
    $this.on("keyup paste", function () {
      jQuery("#passeye-" + i).val(jQuery(this).val());
    });
    jQuery("#passeye-toggle-" + i).on("click", function () {
      if ($this.hasClass("show")) {
        $this.attr("type", "password");
        $this.removeClass("show");
        jQuery(this).removeClass("btn-outline-primary");
      } else {
        $this.attr("type", "text");
        $this.val(jQuery("#passeye-" + i).val());
        $this.addClass("show");
        jQuery(this).addClass("btn-outline-primary");
      }
    });
  });

  jQuery("#loginForm").on("submit", function (event) {
    var $this = jQuery(this),
      $formActionURL = $this.attr("action");

    //  Form Input Value
    var name = jQuery("#name")
      .val()
      .trim(),
      password = jQuery("#password")
        .val()
        .trim();

    $this.find(":submit").addClass('loading');
    $this.find(":submit").attr("disabled", "true");

    // ajax
    jQuery.ajax({
      url: $formActionURL,
      data: {
        name: name,
        password: password
      },
      type: "POST",
      success: function (response) {
        jQuery("#loginForm")[0].reset();
        $this
          .find(":submit")
          .removeClass("loading");

        if (response.code == 200) {
          spop({
            template: "Login successfully!",
            position: "top-center",
            style: "success",
            autoclose: 1200,
            onClose: function () {
              window.location.href = "/admins/console";
            }
          });
        } else {
          spop({
            template: "Wrong name or password",
            position: "top-center",
            style: "error",
            autoclose: 1200
          });

          jQuery("#loginForm")
            .find(":submit")
            .removeAttr("disabled");
        }
      }
    });

    event.preventDefault();
  });
})(jQuery);
