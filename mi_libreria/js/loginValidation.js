document.addEventListener('DOMContentLoaded', function () {
    const loginForm = document.getElementById('loginForm');
    const emailInput = document.getElementById('emailLogin');
    const passwordInput = document.getElementById('passwordLogin');
    const emailError = document.getElementById('emailError');
    const passwordError = document.getElementById('passwordError');
    const passwordStrengthDiv = document.getElementById('passwordStrength'); // Opcional

    // --- Validación en tiempo real (al perder el foco) ---
    emailInput.addEventListener('blur', validateEmail);
    passwordInput.addEventListener('blur', validatePassword);
    // Opcional: Validación de fortaleza mientras se escribe
    passwordInput.addEventListener('input', checkPasswordStrength);

    // --- Validación al enviar el formulario ---
    loginForm.addEventListener('submit', function (event) {
        // Validar ambos campos antes de enviar
        const isEmailValid = validateEmail();
        const isPasswordValid = validatePassword();

        if (!isEmailValid || !isPasswordValid) {
            // Si alguno no es válido, previene el envío del formulario
            event.preventDefault();
            event.stopPropagation();
            console.log("Formulario no válido. Envío prevenido.");
        }
        // Si ambos son válidos, el formulario se enviará al action="procesar_login.php"
    });

    // --- Funciones de Validación ---
    function validateEmail() {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/; // Regex simple para email
        if (!emailInput.value.trim()) {
            showError(emailInput, emailError, 'El correo electrónico es requerido.');
            return false;
        } else if (!emailRegex.test(emailInput.value)) {
            showError(emailInput, emailError, 'Por favor, ingresa un correo electrónico válido.');
            return false;
        } else {
            clearError(emailInput, emailError);
            return true;
        }
    }

    function validatePassword() {
        // Ejemplo: Requerir al menos 8 caracteres
        if (!passwordInput.value) { // Verifica si está vacío
            showError(passwordInput, passwordError, 'La contraseña es requerida.');
            return false;
        } else if (passwordInput.value.length < 8) {
             showError(passwordInput, passwordError, 'La contraseña debe tener al menos 8 caracteres.');
             return false;
        } else {
             // Aquí podrías añadir más reglas (mayúsculas, números, símbolos)
             clearError(passwordInput, passwordError);
             return true;
        }
         // Si se implementa checkPasswordStrength, la validación aquí puede ser más simple
         // ya que la fortaleza se chequea en tiempo real.
    }

     // Opcional: Función para verificar fortaleza de contraseña en tiempo real
    function checkPasswordStrength() {
        const password = passwordInput.value;
        let strength = 0;
        if (password.length >= 8) strength++; // Longitud
        if (password.match(/[A-Z]/)) strength++; // Mayúsculas
        if (password.match(/[a-z]/)) strength++; // Minúsculas
        if (password.match(/[0-9]/)) strength++; // Números
        if (password.match(/[^A-Za-z0-9]/)) strength++; // Símbolos

        let strengthText = 'Débil';
        let strengthClass = 'text-danger';
        if (strength >= 5) {
             strengthText = 'Muy Fuerte';
             strengthClass = 'text-success';
        } else if (strength >= 4) {
             strengthText = 'Fuerte';
             strengthClass = 'text-warning'; // O text-success
        } else if (strength >= 3) {
             strengthText = 'Media';
             strengthClass = 'text-warning';
        }

        if (passwordStrengthDiv && password.length > 0) {
             passwordStrengthDiv.textContent = `Fortaleza: ${strengthText}`;
             passwordStrengthDiv.className = `form-text ${strengthClass}`; // Aplica clase Bootstrap
        } else if (passwordStrengthDiv) {
             passwordStrengthDiv.textContent = ''; // Limpia si está vacío
        }
    }


    // --- Funciones auxiliares para mostrar/ocultar errores ---
    function showError(inputElement, errorElement, message) {
        inputElement.classList.add('is-invalid'); // Clase Bootstrap para campo inválido
        errorElement.textContent = message;
        errorElement.style.display = 'block'; // Asegura que el mensaje de error sea visible
    }

    function clearError(inputElement, errorElement) {
        inputElement.classList.remove('is-invalid');
        inputElement.classList.add('is-valid'); // Opcional: Clase Bootstrap para campo válido
         errorElement.textContent = ''; // Limpia el mensaje
         errorElement.style.display = 'none'; // Oculta el contenedor del error
    }
});