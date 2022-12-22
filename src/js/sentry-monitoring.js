jQuery(document).ready(function ($) {

    class SentryMonitoring {

        constructor() {
            this.test = $('#sentry_test_error');
            this.wrapper = $('#test_generator');

            this.addListeners();
        }

        sendTestError() {
            let self = this;
            this.wrapper.append('<div id="sending_message" style="font-weight: bolder;">Sending...</div>');

            $.ajax({
                type: "post",
                dataType: 'json',
                url: "/wp-admin/admin-ajax.php",
                data: {
                    action: 'send_test_error_to_sentry',
                },
                success: function (data) {
                    if (data.message) {
                        $('#sending_message').html(data.message);
                    }
                    setTimeout(function () {
                        $('#sending_message').remove();
                    }, 3000);
                },
                error: function (msg) {
                    $('#sending_message').html('Unable to send test error.');
                }
            });
        }

        addListeners() {
            let self = this;

            this.test.on('click', function () {
                self.sendTestError();
            })
        }
    }

    new SentryMonitoring();
});