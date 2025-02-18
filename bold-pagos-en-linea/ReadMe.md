# Bold pagos en linea

- Contributors: Bold, Luis Herrera
- Tags: woocommerce, payment gateway, bold, colombia, ecommerce
- Requires at least: 5.5.2
- Requires PHP: 7.4
- Tested up to: 6.7
- Stable tag: 3.1.6
- Network: true
- License: GPLv3 or later
- License URI: https://www.gnu.org/licenses/gpl-3.0.html

Este plugin permite integrar tu tienda en línea de WordPress con la pasarela de pagos de Bold Colombia.

== Description ==

Bienvenido a la documentación oficial del plugin **Bold pagos en línea**. Este plugin permite integrar tu tienda en línea de WordPress con la pasarela de pagos de Bold Colombia. Con esta integración, podrás ofrecer a tus clientes la opción de realizar pagos de manera segura y eficiente.

### Conexión con Bold para Pagos en Línea

Este plugin establece conexiones externas con Bold para integrar funcionalidades de pagos en línea. Se utilizan las siguientes URL para dichas conexiones:

- Script para Botón de Pago de Bold:
  - URL: https://checkout.bold.co/library/boldPaymentButton.js
  - Descripción: Esta URL proporciona el script necesario para mostrar el botón de pago de Bold de forma automática en el proceso de compra.

- Actualización de Estado de Transacción:
  - URL: https://payments.api.bold.co/v2/payment-voucher/
  - Descripción: Esta URL se utiliza para actualizar el estado de la transacción de las órdenes creadas a través de Bold. Permite mantener sincronizados los registros de transacciones y proporcionar una experiencia fluida al usuario final.

- Validación Webhook configurado en panel de comercios
   - URL: https://merchants-cde.api.bold.co/merchants/myself/configurations/webhook
   - Descripción: Esta URL se utiliza para listar los webhooks configurados por el comercio y validar si el actual del wordpress existe en ese listado, generando una alerta en la configuración.

- Generar URL ofuscada para redirigir a pagar en Checkout de Bold
   - URL: https://online-cde.api.bold.co/ecommerce/proxy/v1/obfuscate
   - Descripción: Esta URL valida los datos de la compra a realizar y genera una url segura para redirigir al usuario a finalizar la compra de la orden realizada en WordPress con WooCommerce.

Para más información, visita https://bold.co. Conoce nuestros términos y condiciones [aquí](https://bold.co/legal) y nuestro portal de desarrolladores [aquí](https://developers.bold.co).

### Bibliotecas
- [BoldCheckout](https://developers.bold.co/pagos-en-linea/boton-de-pagos/integracion-manual/integracion-personalizada): Usamos nuestra librería ligera de JavaScript para generar el botón de pagos personalizado y también para redirigir al pago desde la pasarela de pagos.
- [Notiflix](https://github.com/notiflix/Notiflix): Usamos esta librería de notificaciones ligera para mostrar mensajes al usuario.

### Características Destacadas

- Integración con la pasarela de pagos de Bold Colombia.
- Personalización de la apariencia de los botones de pago.
- Actualización automática del estado de pago de las órdenes.
- Soporte para la moneda colombiana (COP) y dólares americanos (USD).

## Requisitos

- PHP > 7.4
- WordPress instalado y configurado.
- WooCommerce instalado y activado.
- Llaves de autenticación. Para encontrarlas puedes seguir la [documentación oficial de Bold](https://developers.bold.co/pagos-en-linea/llaves-de-integracion)

## Instalación

1. Descarga el archivo zip del plugin desde [aquí](https://developers.bold.co/pagos-en-linea/boton-de-pagos/plugins/wordpress).
2. Ve a tu panel de administración de WordPress.
3. Navega a **Plugins > Añadir Nuevo (Plugins > Add New Plugin)**.
4. Haz clic en **Subir plugin (Upload Plugin)** y selecciona el archivo zip descargado.
5. Activa el plugin una vez que se haya completado la instalación.

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
   - Activa la opción **Habilitar el método de pago** para habilitar la pasarela de pago. Te llevara a los métodos de pago de WooCommerce y marca la casilla "Habilitar plugin Bold", luego haz clic en Guardar los cambios para aplicar la configuración.

¡Tu plugin Bold Payments está ahora configurado y listo para procesar pagos en tu tienda WooCommerce!

== Screenshots ==

1. Configura los ajustes de Bold en la sección correspondiente.
2. Asegúrate de que el plugin esté habilitado.
3. Realiza una transacción de prueba para verificar la integración.

== Frequently Asked Questions ==

En esta sección encuentras una [guía](https://developers.bold.co/pagos-en-linea/boton-de-pagos/dudas-integracion) de resolución de problemas generales de la integración con Bold así como una sección de preguntas frecuentes sobre el botón de pagos.

## Documentación Adicional

Consulta la [documentación oficial de Bold](https://developers.bold.co/pagos-en-linea) para obtener información detallada sobre la integración con Bold Colombia.

## Soporte y Contacto

- **Configuración**: Accede a la configuración del plugin desde `WooCommerce` > `Ajustes` > `Pagos` > `Bold`.
- **Documentación adicional**: [Enlace a la documentación oficial de Bold](https://developers.bold.co/pagos-en-linea).
- **Soporte técnico**: Contacta al equipo de soporte de Bold a través de [soporte.online@bold.co](mailto:soporte.online@bold.co).

== Upgrade Notice ==

Última versión 3.1.6

== Changelog ==

[Ver registro de cambios para todas las versiones.](CHANGELOG.md).

### 3.1.6

- Change in the redirect flow to complete the payment at checkout, improving security and blocking errors.

== Créditos ==

Este plugin fue desarrollado por Bold.

== Licencia ==

Este plugin está distribuido bajo la licencia GPLv3 o posterior - [Enlace a la licencia](https://www.gnu.org/licenses/gpl-3.0.html).
