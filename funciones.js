var tableToExcel = (function() {
  var uri = 'data:application/vnd.ms-excel;base64,'
    , template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>{worksheet}</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head><body><table>{table}</table></body></html>'
    , base64 = function(s) { return window.btoa(unescape(encodeURIComponent(s))) }
    , format = function(s, c) { return s.replace(/{(\w+)}/g, function(m, p) { return c[p]; }) }
  return function(table, name) {
    if (!table.nodeType) table = document.getElementById(table)
    var ctx = {worksheet: name || 'Worksheet', table: table.innerHTML}
    window.location.href = uri + base64(format(template, ctx))
  }
})()

// Función para confirmar eliminación con modal personalizado y AJAX
function confirmarEliminacion(url, accion = 'eliminar') {
    // Crear el modal si no existe
    if (!document.getElementById('modal-confirmacion')) {
        crearModalConfirmacion();
    }
    
    var modal = document.getElementById('modal-confirmacion');
    var mensaje = document.getElementById('mensaje-confirmacion');
    var btnConfirmar = document.getElementById('btn-confirmar');
    var btnCancelar = document.getElementById('btn-cancelar');
    
    // Configurar el mensaje
    mensaje.innerHTML = '¿Seguro que deseas ' + accion + '?';
    
    // Mostrar el modal
    modal.style.display = 'flex';
    
    // Configurar eventos de los botones
    btnConfirmar.onclick = function() {
        modal.style.display = 'none';
        
        // Realizar petición AJAX directamente sin mostrar modales adicionales
        fetch(url, {
            method: 'GET'
        })
        .then(response => response.text())
        .then(data => {
            if (data.includes('Error') || data.includes('error')) {
                // Redirigir con mensaje de error usando sistema PHP
                var indexUrl = 'index.php';
                var separador = indexUrl.includes('?') ? '&' : '?';
                indexUrl += separador + 'mensaje=' + encodeURIComponent('Error al procesar la solicitud') + '&tipo=error';
                window.location.href = indexUrl;
            } else {
                var mensaje_exito = data.trim() || 'Operación completada correctamente';
                // Redirigir con mensaje de éxito usando sistema PHP
                var indexUrl = 'index.php';
                var separador = indexUrl.includes('?') ? '&' : '?';
                indexUrl += separador + 'mensaje=' + encodeURIComponent(mensaje_exito) + '&tipo=exito';
                window.location.href = indexUrl;
            }
        })
        .catch(error => {
            // Redirigir con mensaje de error usando sistema PHP
            var indexUrl = 'index.php';
            var separador = indexUrl.includes('?') ? '&' : '?';
            indexUrl += separador + 'mensaje=' + encodeURIComponent('Error de conexión') + '&tipo=error';
            window.location.href = indexUrl;
        });
    };
    
    btnCancelar.onclick = function() {
        modal.style.display = 'none';
    };
    
    // Cerrar modal al hacer clic fuera de él
    modal.onclick = function(e) {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    };
}

// Función para crear el modal de confirmación
function crearModalConfirmacion() {
    var modalHTML = `
        <div id="modal-confirmacion" class="modal-overlay">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Confirmar Acción</h3>
                </div>
                <div class="modal-body">
                    <p id="mensaje-confirmacion"></p>
                </div>
                <div class="modal-footer">
                    <button id="btn-cancelar" class="btn-cancelar">Cancelar</button>
                    <button id="btn-confirmar" class="btn-confirmar">Confirmar</button>
                </div>
            </div>
        </div>
    `;
    
    // Insertar el modal en el body
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    

}

var limpiarFormulario = (function () {
	return function(button) {
		var form = button.parentElement;
		var inputs = form.getElementsByTagName("input");
		for (var i = 0; i < inputs.length; i++) {
			if (inputs[i].type != "button" && inputs[i].type != "submit") {
				inputs[i].value = "";
			}
		}
		
		var selects = form.getElementsByTagName("select");
		for (var i = 0; i < selects.length; i++) {
			selects[i].selectedIndex = 0;
		}
  	}
})()

// Sistema de procesamiento asíncrono de formularios (sin modales)
function procesarFormularioAsincrono(formElement, mensaje_exito, redirigir_a = null) {
    var formData = new FormData(formElement);
    
    fetch(formElement.action, {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        // Verificar si hay error en la respuesta
        if (data.includes('error') || data.includes('Error') || data.includes('die')) {
            // Redirigir con mensaje de error usando sistema PHP
            var url = redirigir_a || 'index.php';
            var separador = url.includes('?') ? '&' : '?';
            url += separador + 'mensaje=' + encodeURIComponent('Error al procesar la solicitud') + '&tipo=error';
            window.location.href = url;
        } else {
            // Redirigir con mensaje de éxito usando sistema PHP
            var url = redirigir_a || 'index.php';
            var separador = url.includes('?') ? '&' : '?';
            url += separador + 'mensaje=' + encodeURIComponent(mensaje_exito) + '&tipo=exito';
            window.location.href = url;
        }
    })
    .catch(error => {
        // Redirigir con mensaje de error usando sistema PHP
        var url = redirigir_a || 'index.php';
        var separador = url.includes('?') ? '&' : '?';
        url += separador + 'mensaje=' + encodeURIComponent('Error de conexión') + '&tipo=error';
        window.location.href = url;
    });
    
    return false; // Prevenir envío normal del formulario
}