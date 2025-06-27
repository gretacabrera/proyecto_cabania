// Sistema de validaciones JavaScript para proyecto_cabania
// Mantiene la estructura procedural sin orientación a objetos

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

// Funciones auxiliares
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
