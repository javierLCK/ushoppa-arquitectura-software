document.addEventListener('DOMContentLoaded', function () {

    const roleCards      = document.querySelectorAll('.role-card');
    const form           = document.getElementById('registroForm');
    const alertBox       = document.getElementById('alertBox');

    const nombreInput    = document.getElementById('nombre');
    const apellidoInput  = document.getElementById('apellido');
    const emailInput     = document.getElementById('email');
    const passwordInput  = document.getElementById('password');
    const confirmInput   = document.getElementById('passwordConfirm');
    const terminosInput  = document.getElementById('aceptaTerminos');

    const strengthBar    = document.getElementById('strengthBar');
    const strengthLabel  = document.getElementById('strengthLabel');

    /* ---------- Selector de tipo de cuenta ---------- */
    roleCards.forEach(function (card) {
        card.addEventListener('click', function () {
            roleCards.forEach(function (c) { c.classList.remove('is-selected'); });
            card.classList.add('is-selected');
            card.querySelector('input[type="radio"]').checked = true;
        });
    });

    /* ---------- Utilidades de mensajes ---------- */
    function mostrarAlerta(mensaje, tipo) {
        alertBox.textContent = mensaje;
        alertBox.className = 'alert alert--' + tipo;
        alertBox.hidden = false;
    }

    function ocultarAlerta() {
        alertBox.hidden = true;
    }

    function setError(input, errorEl, mensaje) {
        if (mensaje) {
            input.classList.add('is-invalid');
            errorEl.textContent = mensaje;
            return false;
        }
        input.classList.remove('is-invalid');
        errorEl.textContent = '';
        return true;
    }

    /* ---------- Medidor de fortaleza de contraseña ---------- */
    function calcularFortaleza(password) {
        let puntos = 0;

        if (password.length >= 8) puntos++;
        if (password.length >= 12) puntos++;
        if (/[A-Z]/.test(password)) puntos++;
        if (/[0-9]/.test(password)) puntos++;
        if (/[^A-Za-z0-9]/.test(password)) puntos++;

        if (puntos <= 1) return { nivel: 'debil', texto: 'Débil', ancho: '20%' };
        if (puntos <= 3) return { nivel: 'media', texto: 'Media', ancho: '60%' };
        return { nivel: 'fuerte', texto: 'Fuerte', ancho: '100%' };
    }

    function actualizarMedidor() {
        const resultado = calcularFortaleza(passwordInput.value);
        strengthBar.style.width = passwordInput.value ? resultado.ancho : '0%';
        strengthLabel.textContent = passwordInput.value ? resultado.texto : 'Débil';

        const colores = { debil: '#dc2626', media: '#f59e0b', fuerte: '#16a34a' };
        strengthBar.style.background = colores[resultado.nivel] || '#dc2626';
    }

    passwordInput.addEventListener('input', actualizarMedidor);

    /* ---------- Validaciones individuales ---------- */
    function validarNombre() {
        const valor = nombreInput.value.trim();
        return setError(
            nombreInput,
            document.getElementById('nombreError'),
            valor === '' ? 'Ingresa tu nombre.' : ''
        );
    }

    function validarApellido() {
        const valor = apellidoInput.value.trim();
        return setError(
            apellidoInput,
            document.getElementById('apellidoError'),
            valor === '' ? 'Ingresa tu apellido.' : ''
        );
    }

    function validarEmail() {
        const valor = emailInput.value.trim();
        const emailValido = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(valor);
        let mensaje = '';
        if (valor === '') mensaje = 'Ingresa tu correo electrónico.';
        else if (!emailValido) mensaje = 'El correo electrónico no es válido.';
        return setError(emailInput, document.getElementById('emailError'), mensaje);
    }

    function validarPassword() {
        const valor = passwordInput.value;
        let mensaje = '';
        if (valor === '') mensaje = 'Ingresa una contraseña.';
        else if (valor.length < 8) mensaje = 'La contraseña debe tener al menos 8 caracteres.';
        return setError(passwordInput, document.getElementById('passwordError'), mensaje);
    }

    function validarConfirmacion() {
        const valor = confirmInput.value;
        let mensaje = '';
        if (valor === '') mensaje = 'Confirma tu contraseña.';
        else if (valor !== passwordInput.value) mensaje = 'Las contraseñas no coinciden.';
        return setError(confirmInput, document.getElementById('passwordConfirmError'), mensaje);
    }

    function validarTerminos() {
        const errorEl = document.getElementById('terminosError');
        if (!terminosInput.checked) {
            errorEl.textContent = 'Debes aceptar los términos y la política de privacidad.';
            return false;
        }
        errorEl.textContent = '';
        return true;
    }

    // Validar en tiempo real al salir de cada campo
    nombreInput.addEventListener('blur', validarNombre);
    apellidoInput.addEventListener('blur', validarApellido);
    emailInput.addEventListener('blur', validarEmail);
    passwordInput.addEventListener('blur', validarPassword);
    confirmInput.addEventListener('blur', validarConfirmacion);

    /* ---------- Envío del formulario ---------- */
    form.addEventListener('submit', function (event) {
        event.preventDefault();
        ocultarAlerta();

        const validaciones = [
            validarNombre(),
            validarApellido(),
            validarEmail(),
            validarPassword(),
            validarConfirmacion(),
            validarTerminos()
        ];

        const formularioValido = validaciones.every(Boolean);

        if (!formularioValido) {
            mostrarAlerta('Revisa los campos marcados en rojo antes de continuar.', 'error');
            return;
        }

        // Aquí es donde, una vez conectado el backend, se enviaría el formulario
        // (por ejemplo con fetch() a php/registro.php). Por ahora solo mostramos
        // un mensaje de éxito en el propio front-end.
        mostrarAlerta('¡Cuenta creada correctamente! Ya puedes iniciar sesión.', 'info');
        form.reset();
        roleCards.forEach(function (c) { c.classList.remove('is-selected'); });
        roleCards[0].classList.add('is-selected');
        roleCards[0].querySelector('input[type="radio"]').checked = true;
        actualizarMedidor();
    });
});
