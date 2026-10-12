
document.addEventListener("DOMContentLoaded", function () {

    const formulario = document.getElementById("formLogin");
    const correo = document.getElementById("correo");
    const contrasena = document.getElementById("contrasena");

    const errorCorreo = document.getElementById("errorCorreo");
    const errorContrasena = document.getElementById("errorContrasena");
    const estadoContrasena = document.getElementById("estadoContrasena");
    const mensajeLogin = document.getElementById("mensajeLogin");

    // Validar el formato del correo electrónico
    function validarCorreo() {
        const valor = correo.value.trim();

        const formatoCorreo = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        const valido = valor !== "" && formatoCorreo.test(valor);

        correo.classList.toggle("is-valid", valido);
        correo.classList.toggle("is-invalid", !valido);

        errorCorreo.textContent = valor === ""
            ? "El correo electrónico es obligatorio."
            : "Ingresa un correo electrónico válido.";

        return valido;
    }

    // Comprobar la seguridad de la contraseña
    function validarContrasena() {
        const valor = contrasena.value;

        const tieneLongitud = valor.length >= 8;
        const tieneMayuscula = /[A-Z]/.test(valor);
        const tieneMinuscula = /[a-z]/.test(valor);
        const tieneNumero = /[0-9]/.test(valor);
        const tieneSimbolo = /[^A-Za-z0-9\s]/.test(valor);

        const valido = valor !== "" &&
            tieneLongitud &&
            tieneMayuscula &&
            tieneMinuscula &&
            tieneNumero &&
            tieneSimbolo;

        contrasena.classList.toggle("is-valid", valido);
        contrasena.classList.toggle("is-invalid", !valido);

        if (valor === "") {
            estadoContrasena.textContent =
                "Ingresa tu contraseña.";
        } else {
            const requisitos = [
                [tieneLongitud, "8 caracteres como mínimo"],
                [tieneMayuscula, "una letra mayúscula"],
                [tieneMinuscula, "una letra minúscula"],
                [tieneNumero, "un número"],
                [tieneSimbolo, "un símbolo especial"]
            ];

            const pendientes = requisitos
                .filter(function (requisito) {
                    return !requisito[0];
                })
                .map(function (requisito) {
                    return requisito[1];
                });

            estadoContrasena.textContent = valido
                ? "La contraseña cumple los requisitos."
                : "Falta: " + pendientes.join(", ") + ".";
        }

        return valido;
    }

    // Validación en tiempo real
    correo.addEventListener("input", function () {
        validarCorreo();
        mensajeLogin.classList.add("d-none");
    });

    contrasena.addEventListener("input", function () {
        validarContrasena();
        mensajeLogin.classList.add("d-none");
    });

    // Validación antes de enviar el formulario
    formulario.addEventListener("submit", function (evento) {
        const correoValido = validarCorreo();
        const contrasenaValida = validarContrasena();

        if (!correoValido || !contrasenaValida) {
            evento.preventDefault();
            mensajeLogin.textContent =
                "No se puede enviar el formulario. Revisa los campos.";
            mensajeLogin.classList.remove("d-none");
        }
    });

});
