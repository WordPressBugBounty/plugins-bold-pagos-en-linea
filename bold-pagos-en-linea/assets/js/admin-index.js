let notifierWC = new AWN({
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

function activationValidation(el, e) {
  var methodStatus = jQuery(".bold__config__field__switch__item").attr(
    "data-status"
  );
  if (
    methodStatus === "no" &&
    jQuery(".bold__config__empty").hasClass("bold__config__empty")
  ) {
    jQuery(".bold__config__field__switch__item").attr("data-status", "yes");
    notifierWC?.alert(
      `Antes de habilitar el método de pago, debes hacer las configuraciones.`
    );

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
