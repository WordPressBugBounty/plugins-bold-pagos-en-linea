=== Bold pagos en linea ===
Contributors: boldplugins
Tags: woocommerce, payment gateway, bold, ecommerce, payment method
Requires at least: 5.5.2
Tested up to: 6.8
Requires PHP: 7.4
Network: true
Stable tag: 3.2.1
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Recibe pagos en tu tienda de forma segura con diferentes métodos de pago confiables.

== Description ==
Intégralo a tu página web sin complicaciones ni papeleos. Recibe pagos con tarjetas nacionales e internacionales, PSE y billeteras digitales de manera sencilla. Recibe el dinero de tus ventas al siguiente día hábil en la cuenta que escojas, sin importar el banco.

- Intégralo a tu página web sin complicaciones ni papeleos.
- Recibe pagos con tarjetas nacionales e internacionales, PSE y billeteras digital
- Recibe el dinero de tus ventas al siguiente día hábil en la cuenta que escojas.

### Tarifas que se adaptan a cada tipo de negocio

Conoce nuestras tarifas para negocios que venden hasta $20 millones con Bold al mes. Conoce más [aquí](https://bold.co/tarifas)

Si tus ventas son mayores, te podemos ofrecer una tarifa especial.

Para más información, visita https://bold.co. Conoce nuestros términos y condiciones [aquí](https://bold.co/legal) y nuestro portal de desarrolladores [aquí](https://developers.bold.co).

### Características Destacadas

- Integración con la pasarela de pagos de Bold Colombia.
- Personalización de la apariencia de los botones de pago.
- Actualización automática del estado de pago de las órdenes.
- Soporte para la moneda colombiana (COP) y dólares americanos (USD).

== Installation ==

## Requisitos

- PHP > 7.4
- WordPress instalado y configurado.
- WooCommerce instalado y activado.
- Llaves de autenticación. Para encontrarlas puedes seguir la [documentación oficial de Bold](https://developers.bold.co/pagos-en-linea/llaves-de-integracion)

1. Ve a tu panel de administración de WordPress.
2. Navega a **Plugins > Añadir Nuevo (Plugins > Add New Plugin)**.
4. Haz clic en buscador y digita **Bold pagos en línea** y da clic en instalar.
5. Activa el plugin una vez que se haya completado la instalación.

Si no encuentras el plugin puedes descargar el archivo zip del plugin desde [aquí](https://developers.bold.co/pagos-en-linea/boton-de-pagos/plugins/wordpress).

## Configuración

1. Obtén las Llaves de autenticación:
   - Antes de comenzar, sigue los requisitos previos para obtener las llaves de identidad y secreta para la integración.
2. Configuración en WooCommerce:
   - Navega al menú: **Bold** y configura tus llaves tanto de producción como de pruebas
3. Configuración de Credenciales:
   - Completa los campos requeridos con las credenciales de llave de identidad y secreta.
4. Configuración Adicional:

   - Completa los siguientes campos según tus preferencias y necesidades, ten en cuenta que estos tendrán valores por defecto:

   a. Modo de Prueba:

   - Activa las transacciones en el modo de prueba.

   b. Color del botón:

   - Al seleccionar esta opción se mostrará el botón a color de Bold en tu página web. De lo contrario, se mostrará un botón en tonos de gris.

   c. Prefijo(opcional):

   - Establece un prefijo único para evitar duplicaciones de números de orden entre tiendas, este se combinará con el ID de la orden (prefijo + orderid). El valor predeterminado es el nombre de la tienda. Se recomienda personalizar el prefijo en sistemas multi-tienda para garantizar identificadores.

    Haz clic en **Guardar Cambios** para aplicar la configuración.

5. Habilita el método de pago con Bold:
   - Activa la opción **Habilitar el método de pago** para habilitar la pasarela de pago. Te llevara a los métodos de pago de WooCommerce y marca la casilla \"Habilitar plugin Bold\", luego haz clic en Guardar los cambios para aplicar la configuración.

¡Tu plugin Bold Payments está ahora configurado y listo para procesar pagos en tu tienda WooCommerce!

== Frequently Asked Questions ==
En esta sección encuentras una [guía](https://developers.bold.co/pagos-en-linea/boton-de-pagos/dudas-integracion) de resolución de problemas generales de la integración con Bold así como una sección de preguntas frecuentes sobre el botón de pagos.

## Documentación Adicional

Consulta la [documentación oficial de Bold](https://developers.bold.co/pagos-en-linea) para obtener información detallada sobre la integración con Bold Colombia.

## Soporte y Contacto

- **Configuración**: Accede a la configuración del plugin desde el menú `Bold`.
- **Documentación adicional**: [Enlace a la documentación oficial de Bold](https://developers.bold.co/pagos-en-linea).
- **Soporte técnico**: Contacta al equipo de soporte de Bold a través de [soporte.online@bold.co](mailto:soporte.online@bold.co).

== Screenshots ==
1. Configura los ajustes de Bold en la sección correspondiente.
2. Asegúrate de que el plugin esté habilitado como método de pago si usas WooCommerce.
3. Realiza una transacción de prueba para verificar la integración.

== Changelog ==

### 3.2.1

- Soporte de impuestos con WooCommerce con los impuestos IVA e IAC Colombianos.

[Ver registro de cambios para todas las versiones.](CHANGELOG.md).

== Upgrade Notice ==
Última versión 3.2.1