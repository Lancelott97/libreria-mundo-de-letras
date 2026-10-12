
document.addEventListener("DOMContentLoaded", () => {
    const selector = document.getElementById("libro_id");
    const cantidad = document.getElementById("cantidad");
    const total = document.getElementById("totalEstimado");
    const formulario = document.getElementById("formAgregar");

    function calcularTotal() {
        if (!selector || !cantidad || !total) return;

        const opcion = selector.options[selector.selectedIndex];
        const precio = Number(opcion?.dataset.precio || 0);
        const stock = Number(opcion?.dataset.stock || 0);
        const unidades = Number(cantidad.value || 0);

        cantidad.max = stock > 0 ? stock : 1;

        total.textContent = (precio * unidades).toLocaleString("es-CL", {
            style: "currency",
            currency: "CLP",
            maximumFractionDigits: 0
        });
    }

    selector?.addEventListener("change", calcularTotal);
    cantidad?.addEventListener("input", calcularTotal);

    formulario?.addEventListener("submit", (evento) => {
        const opcion = selector.options[selector.selectedIndex];
        const unidades = Number(cantidad.value);
        const stock = Number(opcion?.dataset.stock || 0);

        if (!selector.value) {
            evento.preventDefault();
            alert("Selecciona un libro.");
            return;
        }

        if (!Number.isInteger(unidades) ||
            unidades < 1 || unidades > stock) {
            evento.preventDefault();
            alert("Ingresa una cantidad válida según el inventario.");
        }
    });

    document.querySelectorAll(".formActualizar").forEach((form) => {
        form.addEventListener("submit", (evento) => {
            const campo = form.querySelector(".cantidad-carrito");
            const valor = Number(campo.value);
            const maximo = Number(campo.max);

            if (!Number.isInteger(valor) || valor < 1 || valor > maximo) {
                evento.preventDefault();
                alert("La cantidad no es válida para el inventario disponible.");
            }
        });
    });

    document.querySelectorAll(".formEliminar").forEach((form) => {
        form.addEventListener("submit", (evento) => {
            if (!confirm("¿Deseas eliminar este libro del carrito?")) {
                evento.preventDefault();
            }
        });
    });

    document.getElementById("formComprar")?.addEventListener("submit", (evento) => {
        if (!confirm("¿Deseas confirmar el pedido?")) {
            evento.preventDefault();
        }
    });

    calcularTotal();
});
