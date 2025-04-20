document.addEventListener('DOMContentLoaded', function() {
    // Validación para el formulario de agregar
    const addCartForm = document.getElementById('addCartForm');
    if (addCartForm) {
        addCartForm.addEventListener('submit', function(event) {
            const libroSelect = document.getElementById('libroSelect');
            const cantidadAdd = document.getElementById('cantidadAdd');
            let isValid = true;

            // Validar selección de libro
            if (!libroSelect.value) {
                 showValidationError(libroSelect, 'Debes seleccionar un libro.');
                 isValid = false;
            } else {
                 clearValidationError(libroSelect);
            }

            // Validar cantidad
            if (!cantidadAdd.value || parseInt(cantidadAdd.value) < 1) {
                 showValidationError(cantidadAdd, 'La cantidad debe ser al menos 1.');
                 isValid = false;
            } else {
                 clearValidationError(cantidadAdd);
            }

            if (!isValid) {
                event.preventDefault(); // Detiene el envío si no es válido
            }
        });
    }

     // Validación para formularios de actualizar cantidad (ejemplo simple)
     const updateForms = document.querySelectorAll('.updateCartForm');
     updateForms.forEach(form => {
         form.addEventListener('submit', function(event) {
             const cantidadInput = form.querySelector('input[name="cantidad"]');
             const errorDiv = form.querySelector('.invalid-feedback');
             if (!cantidadInput.value || parseInt(cantidadInput.value) < 1) {
                  errorDiv.textContent = 'Cantidad inválida.';
                  cantidadInput.classList.add('is-invalid');
                  event.preventDefault();
             } else {
                  errorDiv.textContent = '';
                   cantidadInput.classList.remove('is-invalid');
             }
         });
     });

     // Podrías añadir confirmación para eliminar
     const deleteForms = document.querySelectorAll('.deleteCartForm');
      deleteForms.forEach(form => {
         form.addEventListener('submit', function(event) {
             if (!confirm('¿Estás seguro de que deseas eliminar este item del carrito?')) {
                 event.preventDefault();
             }
         });
     });

    // --- Funciones auxiliares de validación (puedes reutilizar/adaptar las de loginValidation.js) ---
    function showValidationError(inputElement, message) {
         inputElement.classList.add('is-invalid');
         const errorElement = inputElement.parentElement.querySelector('.invalid-feedback');
         if (errorElement) {
             errorElement.textContent = message;
             errorElement.style.display = 'block'; // Asegúrate que sea visible si usas .invalid-feedback de Bootstrap
         }
    }

    function clearValidationError(inputElement) {
         inputElement.classList.remove('is-invalid');
         inputElement.classList.add('is-valid'); // Opcional
         const errorElement = inputElement.parentElement.querySelector('.invalid-feedback');
          if (errorElement) {
              errorElement.textContent = '';
             // errorElement.style.display = 'none'; // Puede que no necesites ocultarlo si Bootstrap lo maneja
          }
    }

});