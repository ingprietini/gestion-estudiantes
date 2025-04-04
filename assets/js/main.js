// Función para confirmar eliminación
function confirmarEliminar(event, mensaje) {
  if (!confirm(mensaje || "¿Está seguro de que desea eliminar este registro?")) {
    event.preventDefault()
  }
}

// Función para validar formularios
function validarFormulario(formId, reglas) {
  const form = document.getElementById(formId)

  if (!form) return

  form.addEventListener("submit", (event) => {
    let esValido = true

    // Recorrer todas las reglas
    for (const campo in reglas) {
      const input = form.querySelector(`[name="${campo}"]`)
      const errorElement = document.getElementById(`${campo}-error`)

      if (!input) continue

      // Limpiar mensaje de error anterior
      if (errorElement) {
        errorElement.textContent = ""
      }

      // Validar campo requerido
      if (reglas[campo].required && input.value.trim() === "") {
        if (errorElement) {
          errorElement.textContent = reglas[campo].mensajes?.required || "Este campo es obligatorio"
        }
        esValido = false
      }

      // Validar email
      if (reglas[campo].email && input.value.trim() !== "") {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
        if (!emailRegex.test(input.value.trim())) {
          if (errorElement) {
            errorElement.textContent = reglas[campo].mensajes?.email || "Ingrese un email válido"
          }
          esValido = false
        }
      }

      // Validar fecha
      if (reglas[campo].fecha && input.value.trim() !== "") {
        const fechaRegex = /^\d{4}-\d{2}-\d{2}$/
        if (!fechaRegex.test(input.value.trim())) {
          if (errorElement) {
            errorElement.textContent = reglas[campo].mensajes?.fecha || "Ingrese una fecha válida (YYYY-MM-DD)"
          }
          esValido = false
        }
      }

      // Validar número
      if (reglas[campo].numero && input.value.trim() !== "") {
        const numeroRegex = /^-?\d*\.?\d+$/
        if (!numeroRegex.test(input.value.trim())) {
          if (errorElement) {
            errorElement.textContent = reglas[campo].mensajes?.numero || "Ingrese un número válido"
          }
          esValido = false
        }
      }
    }

    if (!esValido) {
      event.preventDefault()
    }
  })
}

// Inicializar cuando el DOM esté listo
document.addEventListener("DOMContentLoaded", () => {
  // Ejemplo de uso de validación
  if (document.getElementById("form-estudiante")) {
    validarFormulario("form-estudiante", {
      nombre: {
        required: true,
        mensajes: { required: "El nombre es obligatorio" },
      },
      apellido: {
        required: true,
        mensajes: { required: "El apellido es obligatorio" },
      },
      email: {
        required: true,
        email: true,
        mensajes: {
          required: "El email es obligatorio",
          email: "Ingrese un email válido",
        },
      },
      fecha_nacimiento: {
        fecha: true,
        mensajes: { fecha: "Ingrese una fecha válida (YYYY-MM-DD)" },
      },
    })
  }

  if (document.getElementById("form-evaluacion")) {
    validarFormulario("form-evaluacion", {
      estudiante_id: {
        required: true,
        mensajes: { required: "Seleccione un estudiante" },
      },
      materia_id: {
        required: true,
        mensajes: { required: "Seleccione una materia" },
      },
      calificacion: {
        required: true,
        numero: true,
        mensajes: {
          required: "La calificación es obligatoria",
          numero: "Ingrese un número válido",
        },
      },
      fecha: {
        required: true,
        fecha: true,
        mensajes: {
          required: "La fecha es obligatoria",
          fecha: "Ingrese una fecha válida (YYYY-MM-DD)",
        },
      },
    })
  }
})

