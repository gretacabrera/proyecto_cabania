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

// ================================================================================
// FUNCIONES DE VALIDACIÓN MIGRADAS DE js/validaciones.js
// ================================================================================

// Función para validar formulario de login
function validar_login() {
    var usuario = document.getElementsByName('usuario_nombre')[0];
    var password = document.getElementsByName('usuario_contrasenia')[0];
    
    // Limpiar errores previos
    limpiar_errores_validacion();
    
    var errores = [];
    
    // Validar usuario
    if (!usuario.value.trim()) {
        mostrar_error_campo(usuario, 'El nombre de usuario es obligatorio');
        errores.push('usuario');
    } else if (usuario.value.length < 3) {
        mostrar_error_campo(usuario, 'El usuario debe tener al menos 3 caracteres');
        errores.push('usuario');
    }
    
    // Validar contraseña
    if (!password.value.trim()) {
        mostrar_error_campo(password, 'La contraseña es obligatoria');
        errores.push('password');
    }
    
    // Si hay errores, no enviar formulario
    if (errores.length > 0) {
        return false;
    }
    
    // Si no hay errores, permitir envío del formulario
    return true;
}

// Función para validar formulario de registro
function validar_registro() {
    limpiar_errores_validacion();
    
    var errores = [];
    
    // Validar credenciales
    var usuario = document.getElementsByName('usuario_nombre')[0];
    var password = document.getElementsByName('usuario_contrasenia')[0];
    var confirmPassword = document.getElementsByName('confirmacion_contrasenia')[0];
    
    if (!usuario.value.trim()) {
        mostrar_error_campo(usuario, 'El nombre de usuario es obligatorio');
        errores.push('usuario');
    } else if (usuario.value.length < 3) {
        mostrar_error_campo(usuario, 'El usuario debe tener al menos 3 caracteres');
        errores.push('usuario');
    }
    
    if (!password.value) {
        mostrar_error_campo(password, 'La contraseña es obligatoria');
        errores.push('password');
    }
    
    if (password.value !== confirmPassword.value) {
        mostrar_error_campo(confirmPassword, 'Las contraseñas no coinciden');
        errores.push('confirmPassword');
    }
    
    // Validar datos personales
    var nombre = document.getElementsByName('persona_nombre')[0];
    var apellido = document.getElementsByName('persona_apellido')[0];
    var fechaNac = document.getElementsByName('persona_fechanac')[0];
    var email = document.getElementsByName('contacto_email')[0];
    
    if (!nombre.value.trim()) {
        mostrar_error_campo(nombre, 'El nombre es obligatorio');
        errores.push('nombre');
    }
    
    if (!apellido.value.trim()) {
        mostrar_error_campo(apellido, 'El apellido es obligatorio');
        errores.push('apellido');
    }
    
    if (!fechaNac.value) {
        mostrar_error_campo(fechaNac, 'La fecha de nacimiento es obligatoria');
        errores.push('fechaNac');
    } else if (!validar_mayor_edad(fechaNac.value)) {
        mostrar_error_campo(fechaNac, 'Debe ser mayor de 18 años');
        errores.push('fechaNac');
    }
    
    if (!email.value.trim()) {
        mostrar_error_campo(email, 'El email es obligatorio');
        errores.push('email');
    } else if (!validar_email(email.value)) {
        mostrar_error_campo(email, 'El formato del email es incorrecto');
        errores.push('email');
    }
    
    if (errores.length > 0) {
        return false;
    }
    
    return true;
}

// Validación AJAX para verificar usuario disponible
function validar_usuario_disponible(input) {
    var usuario = input.value.trim();
    
    if (usuario.length < 3) {
        return;
    }
    
    // Crear petición AJAX sin jQuery (JavaScript puro)
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'validar_usuario_ajax.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            var respuesta = JSON.parse(xhr.responseText);
            
            if (respuesta.disponible === false) {
                mostrar_error_campo(input, 'Este nombre de usuario ya está en uso');
                input.classList.add('campo-error');
            } else {
                limpiar_error_campo(input);
                input.classList.add('campo-valido');
            }
        }
    };
    
    xhr.send('usuario_nombre=' + encodeURIComponent(usuario));
}

// Funciones auxiliares de validación
function mostrar_error_campo(campo, mensaje) {
    campo.classList.add('campo-error');
    
    // Remover mensaje anterior si existe
    var mensajeAnterior = campo.parentNode.querySelector('.mensaje-error-campo');
    if (mensajeAnterior) {
        mensajeAnterior.remove();
    }
    
    // Crear nuevo mensaje de error
    var mensajeError = document.createElement('div');
    mensajeError.className = 'mensaje-error-campo';
    mensajeError.textContent = mensaje;
    
    // Insertar después del campo
    campo.parentNode.insertBefore(mensajeError, campo.nextSibling);
}

function limpiar_error_campo(campo) {
    campo.classList.remove('campo-error');
    campo.classList.remove('campo-valido');
    
    var mensajeError = campo.parentNode.querySelector('.mensaje-error-campo');
    if (mensajeError) {
        mensajeError.remove();
    }
}

function limpiar_errores_validacion() {
    // Remover todas las clases de error
    var camposError = document.querySelectorAll('.campo-error');
    camposError.forEach(function(campo) {
        campo.classList.remove('campo-error');
    });
    
    var camposValidos = document.querySelectorAll('.campo-valido');
    camposValidos.forEach(function(campo) {
        campo.classList.remove('campo-valido');
    });
    
    // Remover todos los mensajes de error
    var mensajesError = document.querySelectorAll('.mensaje-error-campo');
    mensajesError.forEach(function(mensaje) {
        mensaje.remove();
    });
}

function validar_email(email) {
    var regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

function validar_mayor_edad(fechaNacimiento) {
    var hoy = new Date();
    var fechaNac = new Date(fechaNacimiento);
    var edad = hoy.getFullYear() - fechaNac.getFullYear();
    var mes = hoy.getMonth() - fechaNac.getMonth();
    
    if (mes < 0 || (mes === 0 && hoy.getDate() < fechaNac.getDate())) {
        edad--;
    }
    
    return edad >= 18;
}

function cerrarMensaje() {
    var mensaje = document.getElementById('mensaje-global');
    if (mensaje) {
        mensaje.style.display = 'none';
    }
}

// Event listeners para validación en tiempo real
document.addEventListener('DOMContentLoaded', function() {
    // Detectar si estamos en página de registro o login
    var esRegistro = document.getElementsByName('confirmacion_contrasenia').length > 0;
    var esLogin = document.getElementById('form-login') !== null;
    
    var inputUsuario = document.getElementsByName('usuario_nombre')[0];
    var inputPassword = document.getElementsByName('usuario_contrasenia')[0];
    var inputConfirmPassword = document.getElementsByName('confirmacion_contrasenia')[0];
    
    // Solo aplicar validación AJAX de usuario disponible en formularios de registro
    if (esRegistro && inputUsuario) {
        console.log('Aplicando validación AJAX para registro');
        inputUsuario.addEventListener('blur', function() {
            if (this.value.trim().length >= 3) {
                validar_usuario_disponible(this);
            }
        });
    }
    
    // Validar confirmación de contraseña en tiempo real (solo en registro)
    if (esRegistro && inputPassword && inputConfirmPassword) {
        console.log('Aplicando validación de confirmación de contraseña');
        inputConfirmPassword.addEventListener('input', function() {
            if (inputPassword.value !== this.value && this.value !== '') {
                mostrar_error_campo(this, 'Las contraseñas no coinciden');
            } else {
                limpiar_error_campo(this);
            }
        });
    }
    
    // Logging para debug
    if (esLogin) {
        console.log('Página de login detectada - sin validaciones AJAX');
    }
    if (esRegistro) {
        console.log('Página de registro detectada - con validaciones AJAX');
    }
});