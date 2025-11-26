<form method="POST" action="<?= url('/auth/login') ?>" class="modern-form">
    <div class="form-group mb-3">
        <label for="usuario_nombre" class="mb-2">
            <i class="fas fa-user"></i>
            Usuario
        </label>
        <input 
            type="text" 
            id="usuario_nombre" 
            name="usuario_nombre" 
            class="form-control form-control-lg"
            placeholder="Ingresa tu usuario"
            required 
            autocomplete="username"
        >
    </div>
    
    <div class="form-group mb-3">
        <label for="usuario_contrasenia" class="mb-2">
            <i class="fas fa-lock"></i>
            Contraseña
        </label>
        <div class="password-input-group">
            <input 
                type="password" 
                id="usuario_contrasenia" 
                name="usuario_contrasenia" 
                class="form-control form-control-lg"
                placeholder="Ingresa tu contraseña"
                required 
                autocomplete="current-password"
            >
            <button type="button" class="password-toggle" onclick="togglePassword()">
                <i class="fas fa-eye" id="password-toggle-icon"></i>
            </button>
        </div>
    </div>
    
    <div class="form-group">
        <div class="custom-control custom-checkbox">
            <input type="checkbox" class="custom-control-input" id="remember_me" name="remember_me">
            <label class="custom-control-label" for="remember_me">
                Recordarme
            </label>
        </div>
    </div>
    
    <button type="submit" class="btn btn-primary btn-lg w-100">
        <i class="fas fa-sign-in-alt"></i>
        Iniciar Sesión
    </button>
</form>

<div class="auth-divider my-3">
    <span>o</span>
</div>

<div class="auth-alternative">
    <p class="mb-2">¿No tienes una cuenta?</p>
    <a href="<?= url('/auth/register') ?>" class="btn btn-outline-primary w-100">
        <i class="fas fa-user-plus"></i>
        Crear cuenta nueva
    </a>
</div>

<div class="auth-help mt-3 text-center">
    <a href="<?= url('/auth/forgot-password') ?>" class="text-muted">
        <i class="fas fa-question-circle"></i>
        ¿Olvidaste tu contraseña?
    </a>
</div>

