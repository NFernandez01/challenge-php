document.addEventListener('DOMContentLoaded', () => {
    // Cargar productos al iniciar
    cargarProductos();

    // Manejar envío del formulario de crear producto
    const form = document.getElementById('productoForm');
    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const data = {
            nombre: document.getElementById('nombre').value.trim(),
            descripcion: document.getElementById('descripcion').value.trim(),
            precio: parseFloat(document.getElementById('precio').value)
        };

        try {
            await agregarProducto(data);
            form.reset();
            await cargarProductos();
        } catch (error) {
            console.error('Error en el formulario:', error);
        }
    });

    // Manejar envío del formulario de editar producto (modal)
    const editarForm = document.getElementById('editarForm');
    editarForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const id = document.getElementById('editarId').value;
        const data = {
            nombre: document.getElementById('editarNombre').value.trim(),
            descripcion: document.getElementById('editarDescripcion').value.trim(),
            precio: parseFloat(document.getElementById('editarPrecio').value)
        };

        try {
            await actualizarProducto(id, data);
            cerrarModal();
            await cargarProductos();
        } catch (error) {
            console.error('Error en el formulario de edición:', error);
        }
    });
});