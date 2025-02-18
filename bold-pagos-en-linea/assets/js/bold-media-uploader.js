jQuery(document).ready(function($) {
    const { __ } = wp.i18n;
    let mediaUploader;

    if(window.Notiflix){
        window.Notiflix.Notify.init({
            timeout: 3500,
            position: 'right-top',
            zindex: 99999
        });
    }

    $('#upload_button').on('click', '#upload_icon_default, #upload_text, #upload_icon_action', function(e) {
        e.preventDefault();

        if (!mediaUploader) {
            mediaUploader = wp.media({
                title: __('Seleccionar imagen', 'bold-pagos-en-linea'),
                button: {
                    text: __('Usar esta Imagen', 'bold-pagos-en-linea'),
                },
                multiple: false,
                library: {
                    type: ['image'],
                },
            });

            mediaUploader.on('select', function() {
                const attachment = mediaUploader.state().get('selection').first().toJSON();
                const messageWarning = __('Por favor, selecciona un archivo de imagen válido (JPG, PNG, WEBP)', 'bold-pagos-en-linea');
                const validMimeTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
                if (!validMimeTypes.includes(attachment.mime)) {
                    if(window.Notiflix){
                        window?.Notiflix.Notify.warning(messageWarning);
                    }else{
                        alert(messageWarning);
                    }
                    return;
                }

                const urlImage = attachment?.sizes.thumbnail.url || attachment.url
                $('#additional__settings__image_checkout__input').val(urlImage);
                $('#upload_icon_default>img').attr('src', attachment.url);
                $('#upload_text').html(attachment.name || attachment.filename);
                $('#upload_icon_action').addClass('bold-hidden');
                $('#upload_icon_delete').removeClass('bold-hidden');
            });
        }

        mediaUploader.open();
    });

    $('#upload_button #actions_container').on('click', '#upload_icon_delete', function() {
        $('#additional__settings__image_checkout__input').val('');
        $('#upload_icon_default>img').attr('src', BoldPlugin.pluginUrl+'/assets/img/admin-panel/default_image.svg');
        $('#upload_text').html( __('Seleccionar imagen', 'bold-pagos-en-linea') );
        $('#upload_icon_action').removeClass('bold-hidden');
        $('#upload_icon_delete').addClass('bold-hidden');
    });
});
