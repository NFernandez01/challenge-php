const API_URL = 'http://localhost:8080/api/productos';

// Función para cargar todos los productos
async function cargarProductos() {
    try {
        const response = await fetch(API_URL);
        
        if (!response.ok) {
            throw new Error('Error al cargar productos');
        }

        const productos = await response.json();
        mostrarProductos(productos);
    } catch (error) {
        console.error('Error:', error);
        mostrarMensaje('Error al cargar los productos', 'error');
        document.getElementById('tablaProductosBody').innerHTML = 
            '<tr><td colspan="6" class="error">Error al cargar productos</td></tr>';
    }
}

// Función para mostrar productos en la tabla
function mostrarProductos(productos) {
    const tbody = document.getElementById('tablaProductosBody');
    
    if (productos.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="empty">No hay productos registrados</td></tr>';
        return;
    }

    tbody.innerHTML = productos.map(producto => `
        <tr>
            <td data-label="ID">${producto.id}</td>
            <td data-label="Nombre">${producto.nombre}</td>
            <td data-label="Descripción">${producto.descripcion || '-'}</td>
            <td data-label="Precio (ARS)">$${parseFloat(producto.precio).toFixed(2)}</td>
            <td data-label="Precio (USD)">USD $${parseFloat(producto.precio_usd).toFixed(2)}</td>
            <td data-label="Acciones" class="actions">
                <button class="btn btn-small btn-edit" onclick="editarProducto(${producto.id})">Editar</button>
                <button class="btn btn-small btn-delete" onclick="eliminarProducto(${producto.id})">Eliminar</button>
            </td>
        </tr>
    `).join('');
}

// Función para agregar un producto
async function agregarProducto(data) {
    try {
        const response = await fetch(API_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });

        if (!response.ok) {
            const error = await response.json();
            throw new Error(error.error || 'Error al crear producto');
        }

        const producto = await response.json();
        mostrarMensaje('Producto creado exitosamente', 'success');
        return producto;
    } catch (error) {
        console.error('Error:', error);
        mostrarMensaje(error.message, 'error');
        throw error;
    }
}

// Función para obtener un producto por ID
async function obtenerProducto(id) {
    try {
        const response = await fetch(`${API_URL}/${id}`);
        
        if (!response.ok) {
            throw new Error('Producto no encontrado');
        }

        return await response.json();
    } catch (error) {
        console.error('Error:', error);
        mostrarMensaje('Error al obtener el producto', 'error');
        throw error;
    }
}

// Función para actualizar un producto
async function actualizarProducto(id, data) {
    try {
        const response = await fetch(`${API_URL}/${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });

        if (!response.ok) {
            const error = await response.json();
            throw new Error(error.error || 'Error al actualizar producto');
        }

        const producto = await response.json();
        mostrarMensaje('Producto actualizado exitosamente', 'success');
        return producto;
    } catch (error) {
        console.error('Error:', error);
        mostrarMensaje(error.message, 'error');
        throw error;
    }
}

// Función para eliminar un producto
function eliminarProducto(id) {
    abrirModalConfirmar(id);
}

// Función para confirmar y ejecutar la eliminación
async function confirmarEliminarProducto(id) {
    try {
        const response = await fetch(`${API_URL}/${id}`, {
            method: 'DELETE'
        });

        if (!response.ok) {
            const error = await response.json();
            throw new Error(error.error || 'Error al eliminar producto');
        }

        cerrarModalConfirmar();
        mostrarMensaje('Producto eliminado exitosamente', 'success');
        await cargarProductos();
    } catch (error) {
        console.error('Error:', error);
        mostrarMensaje(error.message, 'error');
    }
}

// Función para preparar edición de un producto
async function editarProducto(id) {
    try {
        const producto = await obtenerProducto(id);
        
        document.getElementById('editarId').value = producto.id;
        document.getElementById('editarNombre').value = producto.nombre;
        document.getElementById('editarDescripcion').value = producto.descripcion || '';
        document.getElementById('editarPrecio').value = producto.precio;
        
        abrirModal();
    } catch (error) {
        console.error('Error:', error);
    }
}

// Función para abrir el modal
function abrirModal() {
    const modal = document.getElementById('modalEditar');
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
}

// Función para cerrar el modal
function cerrarModal() {
    const modal = document.getElementById('modalEditar');
    modal.classList.remove('show');
    document.body.style.overflow = 'auto';
    document.getElementById('editarForm').reset();
}

// Función para abrir el modal de confirmación
let productoIdEliminar = null;

function abrirModalConfirmar(id) {
    productoIdEliminar = id;
    const modal = document.getElementById('modalConfirmar');
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
    
    // Asignar evento al botón confirmar
    const btnConfirmar = document.getElementById('btnConfirmarEliminar');
    btnConfirmar.onclick = () => confirmarEliminarProducto(productoIdEliminar);
}

// Función para cerrar el modal de confirmación
function cerrarModalConfirmar() {
    const modal = document.getElementById('modalConfirmar');
    modal.classList.remove('show');
    document.body.style.overflow = 'auto';
    productoIdEliminar = null;
}

// Función para mostrar mensajes
function mostrarMensaje(texto, tipo = 'info') {
    const mensajeDiv = document.getElementById('mensaje');
    mensajeDiv.textContent = texto;
    mensajeDiv.className = `mensaje ${tipo}`;
    mensajeDiv.style.display = 'block';

    setTimeout(() => {
        mensajeDiv.style.display = 'none';
    }, 5000);
}