(function($) {

    $.entwine('ss', function($) {

        $('.ss-seo-editor .ss-gridfield-item input, .ss-seo-editor .ss-gridfield-item textarea').entwine({

            onchange: function() {

                // prevent changes to the form / popup
                $('.cms-edit-form').removeClass('changed');

                var $this = $(this);
                var id = $this.closest('tr').attr('data-id');
                var url = $this.closest('.ss-gridfield').attr('data-url') + "/update/" + id;
                var data = $this.attr('name') + '=' + $(this).val();

                $.post(
                    url,
                    data,
                    function(data, textStatus) {
                        statusMessage(data.message, data.type);

                        $this.closest('td').removeClass();
                        if (data.errors.length) {
                            $this.closest('td').addClass('seo-editor-error');
                            data.errors.forEach(function(error) {
                                $this.closest('td').addClass(error)
                            });
                        } else {
                            $this.closest('td').addClass('seo-editor-valid');
                        }

                    },
                    'json'
                );
            }
        });

    });

}(jQuery));
