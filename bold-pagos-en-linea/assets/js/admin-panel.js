let notifier = new AWN({
  durations: {
    global: 3500,
  },
  position: "top-right",
  icons: {
    prefix: "<img src='",
    success:
      "data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGVsbGlwc2UgY3g9IjE5Ljk5OTkiIGN5PSIyMCIgcng9IjE5Ljk5OTkiIHJ5PSIyMCIgZmlsbD0iIzZDRENBQiIvPgo8cGF0aCBmaWxsLXJ1bGU9ImV2ZW5vZGQiIGNsaXAtcnVsZT0iZXZlbm9kZCIgZD0iTTE3LjQyNDIgMjkuMDM1NEw5Ljk0OTg5IDIxLjY2ODZDOC45MDkxNSAyMC42NDMzIDguOTA1MDcgMTguOTc2NSA5LjkzOTU2IDE3Ljk0NThDMTAuOTc0NiAxNi45MTU0IDEyLjY1NjIgMTYuOTEwOCAxMy42OTY3IDE3LjkzNjFMMTcuNDc0IDIxLjY1ODZMMjcuMTExMSAxMi40MDgyQzI4LjE2NTQgMTEuMzk2NiAyOS44NDY1IDExLjQyMzUgMzAuODY4IDEyLjQ2NzFDMzEuODg4MyAxMy41MTE1IDMxLjg2MTcgMTUuMTc4MSAzMC44MDc5IDE2LjE4OTZMMTcuNDI0MiAyOS4wMzU0WiIgZmlsbD0id2hpdGUiLz4KPC9zdmc+Cg==",
    alert:
      "data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA0MCA2MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGVsbGlwc2UgY3g9IjE5Ljk5OTkiIGN5PSIzMCIgcng9IjE5Ljk5OTkiIHJ5PSIyMCIgZmlsbD0iIzkxMDAyMiIvPgo8cGF0aCBmaWxsLXJ1bGU9ImV2ZW5vZGQiIGNsaXAtcnVsZT0iZXZlbm9kZCIgZD0iTTI4LjE4OSAzOC4xODkyQzI3LjEzNTYgMzkuMjQyNCAyNS40Mjg4IDM5LjI0MjQgMjQuMzc1NCAzOC4xODkyTDE5LjkwNjIgMzMuNzE5NkwxNS40MzczIDM4LjE4ODZDMTQuNTUxOCAzOS4wNzM4IDEzLjIwNTQgMzkuMjExNyAxMi4xNzI4IDM4LjYwODRDMTEuOTc3MSAzOC40OTQzIDExLjc5MTMgMzguMzU2MSAxMS42MjM2IDM4LjE4ODZDMTAuNTcwOCAzNy4xMzU3IDEwLjU3MDggMzUuNDI4MyAxMS42MjM2IDM0LjM3NTRMMTYuMDkyOCAyOS45MDY0TDExLjYyMzQgMjUuNDM2NUMxMC41NzAyIDI0LjM4MzMgMTAuNTcwMiAyMi42NzU5IDExLjYyMzQgMjEuNjIzM0MxMi42NzY1IDIwLjU3MDEgMTQuMzgzOCAyMC41NzAxIDE1LjQzNjcgMjEuNjIzM0wxOS45MDYyIDI2LjA5MjlMMjQuMzc1NCAyMS42MjM1QzI1LjQyOCAyMC41NzA5IDI3LjEzNTMgMjAuNTcwOSAyOC4xODg1IDIxLjYyMzVDMjguMzM5NSAyMS43NzQ2IDI4LjQ2NjIgMjEuOTQwMSAyOC41NzQgMjIuMTE0MUMyOS4yMTczIDIzLjE1MzkgMjkuMDkwNiAyNC41MzQ5IDI4LjE4ODUgMjUuNDM3TDIzLjcxOTggMjkuOTA2NEwyOC4xODkgMzQuMzc1N0MyOS4yNDIyIDM1LjQyOTEgMjkuMjQyMiAzNy4xMzYgMjguMTg5IDM4LjE4OTJaIiBmaWxsPSJ3aGl0ZSIvPgo8L3N2Zz4K",
    suffix: "'/>",
  },
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
          notifier?.success("Copiaste la URL de Webhook.");
        })
        .catch(function () {
          notifier?.alert("Ocurrió un error");
        });
    }
  } catch (error) {
    console.error(error);
    notifier?.alert("Ocurrió un error");
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
    notifier?.alert(`El prefijo no puede iniciar con la palabra "Test".`);
    jQuery(el).val("");
  }
  var regex = /^[a-zA-Z0-9_-]+$/;
  if (!regex.test(inputText)) {
    notifier?.alert(
      `Sólo se aceptan valores alfanuméricos, guiones bajos “_” y medios “-”`
    );
    jQuery(el).val("");
  }
}

function redirectValidation(el, e) {
  e.preventDefault();
  var savedConfig = parseInt(jQuery(el).data("saved-config"));
  if (savedConfig === 0) {
    notifier?.alert(
      "Antes de ir a habilitar el método de pago, debes hacer las configuraciones."
    );
    return false;
  } else {
    var target = jQuery(el).data("target") ?? "_self";
    window.open(jQuery(el).data("href"), target);
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
