class BoldCheckoutElement extends HTMLElement {
  static get observedAttributes() {
    return ["plugin_url", "test_mode", "is_light"];
  }

  constructor() {
    super();
    this.shadowDOM = this.attachShadow({ mode: "closed" });

    this.templateHTML = "";
    this.cssText = "";

    this.loadTemplateAndCSS();
  }

  async loadTemplateAndCSS() {
    const pluginUrl = this.getAttribute("plugin_url") || "";
    try {
      const cssResponse = await fetch(`${pluginUrl}../assets/css/bold-checkout-ui.css`);

      this.cssText = await cssResponse.text();

      this.render();
    } catch (error) {
      console.error("Error loading templates:", error);
    }
  }

  connectedCallback() {
    if (this.cssText) {
      this.render();
    }
  }

  attributeChangedCallback(name) {
    if (this.constructor.observedAttributes.includes(name)) {
      this.render();
    }
  }

  render() {
    if (!this.cssText) {
      return;
    }

    this.templateHTML = this.innerHTML;

    const pluginUrl = this.getAttribute("plugin_url") || "";
    const testMode = ["yes", "1", true, 1, "true"].includes(this.getAttribute("test_mode"));
    const isLight = ["yes", "1", true, 1, "true"].includes(this.getAttribute("is_light"));

    const filledTemplate = this.fillTemplate(this.templateHTML, pluginUrl, isLight, testMode);
    this.shadowDOM.innerHTML = `<style>${this.cssText}</style>${filledTemplate}`;
  }

  fillTemplate(templateHTML, pluginUrl, isLight, testMode) {
    let processedTemplate = templateHTML
      .replace(/\{\{plugin_url\}\}/g, pluginUrl);

    const parser = new DOMParser();
    const doc = parser.parseFromString(processedTemplate, "text/html");

    if (!testMode) {
      const testModeElement = doc.getElementById("bold_co_checkout_page_body_test_mode");
      if (testModeElement) {
        testModeElement.remove();
      }
    }

    const containerBackgroundElement = doc.getElementById("bold_co_container_info_checkout_page");
    if(isLight){
      containerBackgroundElement.classList.add("is_light");
    }else{
      containerBackgroundElement.classList.remove("is_light");
    }

    processedTemplate = doc.body.innerHTML;

    return processedTemplate;
  }
}

window.customElements.define("bold-checkout-element", BoldCheckoutElement);
