(()=>{"use strict";const e=window.wp.components,l=window.wp.blocks,t=window.wp.i18n,a=window.wp.blockEditor,o=window.wp.element,n=window.ReactJSXRuntime;(0,l.registerBlockType)("bold/button-block",{title:(0,t.__)("Bóton manual","bold-pagos-en-linea"),icon:()=>(0,n.jsx)("img",{src:boldBlockData.iconUrl,alt:(0,t.__)("Icono del botón de pagos Bold","bold-pagos-en-linea"),style:{width:"24px"}}),category:"bold-category",attributes:{amount:{type:"string",default:0},currency:{type:"string",default:"COP"},description:{type:"string",default:""},redirectionUrl:{type:"string",default:""},color:{type:"string",default:"dark"},size:{type:"string",default:"L"}},edit:({attributes:l,setAttributes:i})=>{const{amount:r,currency:s,description:d,redirectionUrl:c,color:u,size:p}=l,[b,g]=(0,o.useState)(!0);return(0,n.jsxs)(n.Fragment,{children:[(0,n.jsx)(a.InspectorControls,{children:(0,n.jsxs)(e.PanelBody,{title:(0,t.__)("Configuración del botón","bold-pagos-en-linea"),children:[(0,n.jsx)(e.TextControl,{type:"number",label:(0,t.__)("Monto a cobrar","bold-pagos-en-linea"),value:r,onChange:e=>i({amount:e}),help:(0,t.__)("Si quieres que tu cliente decida cuánto quiere pagar el monto deberá ser cero","bold-pagos-en-linea"),required:"true",min:"0"}),(0,n.jsx)(e.SelectControl,{label:(0,t.__)("Divisa","bold-pagos-en-linea"),value:s,options:[{label:"COP",value:"COP"},{label:"USD",value:"USD"}],default:"COP",onChange:e=>i({currency:e}),help:(0,t.__)("Si el monto es cero, se cobrará en COP","bold-pagos-en-linea")}),(0,n.jsx)(e.TextControl,{label:(0,t.__)("Descripción de la venta","bold-pagos-en-linea"),value:d,onChange:e=>i({description:e}),help:(0,t.__)("Opcional","bold-pagos-en-linea"),maxlength:"100",minlength:"2"}),(0,n.jsx)(e.TextControl,{type:"url",label:(0,t.__)("URL a la que redirigir al cliente tras finalizar una transacción","bold-pagos-en-linea"),value:c,onChange:e=>{i({redirectionUrl:e}),0===e.length||/https:\/\/(localhost|(?:[a-zA-Z0-9-]+\.)+[a-zA-Z]{2,})(\/.*)?|http:\/\/localhost(\/.*)?/.test(e)?g(!0):g(!1)},help:(0,t.__)("Opcional","bold-pagos-en-linea")+". "+(0,t.__)("Debe ser una URL válida que comience con https://"),pattern:"https://.+|http://localhost(/.*)?",className:b?"":"bold-invalid-input"}),(0,n.jsx)(e.SelectControl,{label:(0,t.__)("Color","bold-pagos-en-linea"),value:u,options:[{label:"Dark",value:"dark"},{label:"Light",value:"light"}],default:"dark",onChange:e=>i({color:e})}),(0,n.jsx)(e.SelectControl,{label:(0,t.__)("Tamaño","bold-pagos-en-linea"),value:p,options:[{label:"L (48px)",value:"L"},{label:"M (40px)",value:"M"},{label:"S (32px)",value:"S"}],default:"L",onChange:e=>i({size:e})})]})}),(0,n.jsx)("div",{className:`bold-button-preview bold-button-${u} size-${p}`,style:{backgroundImage:"url("+("dark"==u?boldBlockData.exampleButtonDark:boldBlockData.exampleButtonLight)+")"}})]})},save:()=>null,example:{attributes:{amount:1e4,currency:"COP",description:"Descripción de ejemplo",redirectionUrl:"https://example.com/finaliza-compra",color:"dark"},innerHTML:'<div style="text-align:center"><div className="bold-button-preview bold-button-light" style="background-image:url('+boldBlockData.exampleButtonLight+'></div><div className="bold-button-preview bold-button-dark" style="background-image:url('+boldBlockData.exampleButtonDark+"></div></div>"}})})();