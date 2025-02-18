function activationValidation(el, e) {
  var methodStatus = jQuery(".bold__config__field__switch__item").attr(
    "data-status"
  );
  if (
    methodStatus === "no" &&
    jQuery(".bold__config__empty").hasClass("bold__config__empty")
  ) {
    jQuery(".bold__config__field__switch__item").attr("data-status", "yes");
    window?.Notiflix.Notify.warning(
      'Antes de habilitar el método de pago, debes hacer las configuraciones.',
      {
        timeout: 3500,
        position: 'right-top',
        zindex: 99999
      });

    setTimeout(() => {
      jQuery(".bold__config__field__switch__item").attr("data-status", "no");
    }, 300);

    return false;
  }

  jQuery(".bold__config__field__switch__item").attr(
    "data-status",
    methodStatus === "yes" ? "no" : "yes"
  );
  var updatedMethodStatus = jQuery(".bold__config__field__switch__item").attr(
    "data-status"
  );

  jQuery(".bold__config__field__woocommerce__input").prop(
    "checked",
    updatedMethodStatus === "yes"
  ).trigger('change');
}

jQuery(document).ready(function () {
  jQuery("body").on(
    "click",
    ".bold__config__field__switch__slider",
    function (e) {
      activationValidation(this, e);
    }
  );
});
