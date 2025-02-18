jQuery(document).ready(function($) {
  if(window.Notiflix){
    window.Notiflix.Notify.init({
        timeout: 3500,
        position: 'right-top',
        zindex: 99999
    });
  }
});

function changeFieldValueDisplay(el, type) {
  jQuery(el).attr("type", type);
}

function copyUrlToClipboard() {
  try {
    if (navigator?.clipboard) {
      navigator.clipboard
        .writeText(jQuery("#webhook__input__url").val())
        .then(function () {
          window?.Notiflix.Notify.success('Copiaste la URL de Webhook.');
        })
        .catch(function () {
          window?.Notiflix.Notify.warning('No pudimos copiar la URL de Webhook, seleccionala y cópiala manualmente.');
        });
    }
  } catch (error) {
    console.error(error);
    window?.Notiflix.Notify.warning('No pudimos copiar la URL de Webhook, seleccionala y cópiala manualmente.');
  }
}

function changeTagStatus(el) {
  var currentParent = jQuery(el).closest(".bold-card__environment");
  var currentTag = jQuery(currentParent).find(".release__mode__item__tag");
  jQuery(currentTag).text("Activo");
  jQuery(currentTag).addClass("release__mode__item__tag--active");
  jQuery(currentTag).removeClass("release__mode__item__tag--inactive");

  var anotherParent = jQuery(currentParent).siblings(".bold-card__environment");
  var anotherTag = jQuery(anotherParent).find(".release__mode__item__tag");
  jQuery(anotherTag).text("Inactivo");
  jQuery(anotherTag).addClass("release__mode__item__tag--inactive");
  jQuery(anotherTag).removeClass("release__mode__item__tag--active");
}

function testTextValidation(el) {
  var inputText = jQuery(el).val();
  if (inputText?.toLowerCase()?.startsWith("test")) {
    window?.Notiflix.Notify.warning(`El prefijo no puede iniciar con la palabra "Test".`);
    jQuery(el).val("");
  }
  var regex = /^[a-zA-Z0-9_-]+$/;
  if (!regex.test(inputText)) {
    window?.Notiflix.Notify.warning(
      `Sólo se aceptan valores alfanuméricos, guiones bajos “_” y medios “-”`
    );
    jQuery(el).val("");
  }
}

function redirectValidation(el, e) {
  e.preventDefault();
  var savedConfig = parseInt(jQuery(el).data("saved-config"));
  if (savedConfig === 0) {
    window?.Notiflix.Notify.warning(
      "Antes de ir a habilitar el método de pago, debes hacer las configuraciones."
    );
    return false;
  } else {
    var target = jQuery(el).data("target") ?? "_self";
    window.open(jQuery(el).data("href"), target, "noopener,noreferrer");
  }
}

jQuery(document).ready(function () {
  jQuery("body").on("mousedown", "#bold__payment__method__item__btn", function (e) {
    redirectValidation(this, e);
  });
  jQuery("body").on("focus", ".bold_co_input_access_key", function () {
    changeFieldValueDisplay(this, "text");
  });
  jQuery("body").on("blur", ".bold_co_input_access_key", function () {
    changeFieldValueDisplay(this, "password");
  });
  jQuery("body").on("click", "#webhook__url__copy", function () {
    copyUrlToClipboard();
  });
  jQuery("body").on("change", ".release__mode__item__input__el", function () {
    changeTagStatus(this);
  });
  jQuery("body").on(
    "change",
    "#additional__settings__prefix__input",
    function () {
      testTextValidation(this);
    }
  );
});
