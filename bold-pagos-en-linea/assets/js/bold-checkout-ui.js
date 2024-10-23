class BoldCheckoutElement extends HTMLElement {
  static get observedAttributes() {
    return ["plugin_url", "test_mode"];
  }

  constructor() {
    super();
    this.shadowDOM = this.attachShadow({ mode: "closed" });
  }

  connectedCallback() {
    this.render();
    this.initComponent();
  }

  attributeChangedCallback(name) {
    if (name === "plugin_url" || name === "test_mode") {
      this.render();
    }
  }
  getIcons(pluginUrl, isLight) {
    const iconsDefault = [
      `${pluginUrl}../assets/img/payments-method/amex.png`,
      `${pluginUrl}../assets/img/payments-method/diners.png`,
      `${pluginUrl}../assets/img/payments-method/discover.png`,
    ];
    if (isLight) {
      return [
        `${pluginUrl}../assets/img/payments-method/pse_light.png`,
        `${pluginUrl}../assets/img/payments-method/visa_light.png`,
        `${pluginUrl}../assets/img/payments-method/mastercard_light.png`,
        ...iconsDefault,
        `${pluginUrl}../assets/img/payments-method/bancolombia_light.png`,
        `${pluginUrl}../assets/img/payments-method/nequi_light.png`,
      ];
    }
    return [
      `${pluginUrl}../assets/img/payments-method/pse.png`,
      `${pluginUrl}../assets/img/payments-method/visa.png`,
      `${pluginUrl}../assets/img/payments-method/mastercard.png`,
      ...iconsDefault,
      `${pluginUrl}../assets/img/payments-method/bancolombia.png`,
      `${pluginUrl}../assets/img/payments-method/nequi.png`,
    ];
  }

  async render() {
    try {
      const pluginUrl = this.getAttribute("plugin_url") || "";
      const testMode = ["yes", "1", true, 1, "true"].includes(
        this.getAttribute("test_mode")
      );

      const [templateResponse, cssResponse] = await Promise.all([
        fetch(`${pluginUrl}../assets/html/bold-checkout-ui.html`),
        fetch(`${pluginUrl}../assets/css/bold-checkout-ui.css`),
      ]);

      const [templateHTML, cssText] = await Promise.all([
        templateResponse.text(),
        cssResponse.text(),
      ]);
      const parser = new DOMParser();
      const doc = parser.parseFromString(templateHTML, "text/html");
      const modifyTemplate = this.modifyTemplate(doc, templateHTML, testMode);

      const isLight = ["yes", "1", true, 1, "true"].includes(
        this.getAttribute("is_light")
      );

      const finalModifyTemplate = this.updateThemeVariable(
        doc,
        cssText,
        modifyTemplate,
        pluginUrl,
        isLight
      );

      const filledTemplate = this.fillTemplate(finalModifyTemplate, pluginUrl);

      this.shadowDOM.innerHTML = `
          <style>${cssText}</style>
          ${filledTemplate}
        `;
    } catch (error) {
      console.error("Error loading templates:", error);
    }
  }

  fillTemplate(templateHTML, pluginUrl) {
    return templateHTML.replace(/\{\{plugin_url\}\}/g, pluginUrl);
  }

  updateThemeVariable(doc, cssText, templateHTML, pluginUrl, isLight) {
    const elementToRemove = doc.querySelector(
      "#bold_co_checkout_page_body_payments_method"
    );

    const icons = this.getIcons(pluginUrl, isLight);
    icons.forEach((iconUrl) => {
      const imgElement = doc.createElement("img");
      imgElement.setAttribute("src", iconUrl);
      imgElement.setAttribute("alt", "icon");
      elementToRemove.appendChild(imgElement);
    });

    this.updateCSSVariable(doc, isLight);
    return doc.body.innerHTML;
  }

  modifyTemplate(doc, templateHTML, testMode) {
    if (!testMode) {
      const elementToRemove = doc.querySelector(
        "#bold_co_checkout_page_body_test_mode"
      );

      if (elementToRemove) {
        elementToRemove.remove();
      }

      return doc.body.innerHTML;
    }

    return templateHTML;
  }

  updateCSSVariable(doc, isLight) {
    if (!isLight) {
      return;
    }
    const element = doc.getElementById("bold_co_checkout_page");
    element.style.color = "#ffffff";
  }

  initComponent() {
    this.$tag = this.shadowDOM.querySelector(".bold_checkout_element");
  }

  disconnectedCallback() {
    this.remove();
  }
}

window.customElements.define("bold-checkout-element", BoldCheckoutElement);
