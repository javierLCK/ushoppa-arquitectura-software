document.addEventListener('DOMContentLoaded', function () {

    const roleButtons  = document.querySelectorAll('.role-switch__btn');
    const rolInput     = document.getElementById('rolInput');
    const emailInput   = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const quickFillBtn = document.getElementById('quickFillBtn');
    const loginForm    = document.getElementById('loginForm');
    const alertBox     = document.getElementById('alertBox');

    // Credenciales de ejemplo por rol, solo para el botón "Acceso rápido (mockup)"
    const demoCredentials = {
        comprador: { email: 'maria@email.com', password: 'comprador123' },
        vendedor: { email: 'vendedor@email.com', password: 'vendedor123' },
        admin: { email: 'admin@email.com', password: 'admin123' }
    };

    let currentRole = 'comprador';

    function mostrarAlerta(mensaje, tipo) {
        alertBox.textContent = mensaje;
        alertBox.className = 'alert alert--' + tipo;
        alertBox.hidden = false;
    }

    function ocultarAlerta() {
        alertBox.hidden = true;
    }

    // Cambiar de rol (Comprador / Vendedor / Admin)
    roleButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            roleButtons.forEach(function (b) { b.classList.remove('is-active'); });
            btn.classList.add('is-active');

            currentRole = btn.dataset.role;
            rolInput.value = currentRole;
            ocultarAlerta();
        });
    });

    // Autocompletar credenciales de ejemplo (solo entorno de mockup/pruebas)
    quickFillBtn.addEventListener('click', function () {
        const creds = demoCredentials[currentRole];
        if (creds) {
            emailInput.value = creds.email;
            passwordInput.value = creds.password;
            ocultarAlerta();
        }
    });

    // Validación básica en el cliente antes de enviar el formulario.
    // El envío real (verificar usuario/contraseña) lo hace el backend
    // en "action" del formulario (por ejemplo, php/login.php).
    loginForm.addEventListener('submit', function (event) {
        const email = emailInput.value.trim();
        const password = passwordInput.value;
        const emailValido = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);

        if (!email || !password) {
            event.preventDefault();
            mostrarAlerta('Completa correo y contraseña.', 'error');
            return;
        }

        if (!emailValido) {
            event.preventDefault();
            mostrarAlerta('El correo electrónico no es válido.', 'error');
            return;
        }

        ocultarAlerta();
        // Si no hay backend conectado, el formulario no tiene a dónde enviarse
        // de verdad; se deja pasar para que el desarrollador conecte su propio
        // endpoint en el atributo "action" del formulario.
    });
});
