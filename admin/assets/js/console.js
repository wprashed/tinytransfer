(function($) {
    "use strict";

    jQuery.ajax({
        url: "/admins/console/chart",
        type: "GET",
        success: function(response) {
            if (response.code == 200) {
                const data = {
                    labels: response.data.labels,
                    datasets: [{
                        type: "bar",
                        values: response.data.values
                    }]
                }

                const chart = new frappe.Chart("#chart-canvas", {
                    data: data,
                    type: 'axis-mixed',
                    height: 350,
                    colors: ['#5d78ff'],
                    lineOptions: {
                        regionFill: 1
                    },
                });
            }
        }
    });


})(jQuery);