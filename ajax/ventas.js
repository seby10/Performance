let carrito = [];
let clienteActual = null;

function buscarCliente() {
    const cedula = $('#cedula').val();
    $.ajax({
        url: `../api/clientes.php?cedula=${cedula}`,
        type: 'GET',
        success: function(response) {
            if (response.success && response.data) {
                clienteActual = response.data;
                $('#nombreCliente').text(`${response.data.NOM_CLI} ${response.data.APE_CLI}`);
                $('#telefonoCliente').text(response.data.TEL_CLI);
                $('#clienteInfo').removeClass('d-none');
            } else {
                alert('Cliente no encontrado');
                $('#clienteInfo').addClass('d-none');
                clienteActual = null;
            }
        }
    });
}

function buscarProducto() {
    const codigo = $('#codigoProducto').val();
    $.ajax({
        url: `../api/productos.php?codigo=${codigo}`,
        type: 'GET',
        success: function(response) {
            if (response.success && response.data) {
                agregarAlCarrito(response.data);
            } else {
                alert('Producto no encontrado');
            }
        }
    });
}

function agregarAlCarrito(producto) {
    const cantidad = parseInt($('#cantidad').val());
    if (cantidad <= 0) {
        alert('La cantidad debe ser mayor a 0');
        return;
    }

    if (cantidad > producto.EXISTENCIA) {
        alert('No hay suficiente stock');
        return;
    }

    const item = {
        codigo: producto.COD_PRO,
        nombre: producto.NOM_PRO,
        cantidad: cantidad,
        precio: producto.PRE_UNI_PRO,
        subtotal: cantidad * producto.PRE_UNI_PRO
    };

    carrito.push(item);
    actualizarTablaCarrito();
    $('#codigoProducto').val('');
    $('#cantidad').val(1);
}

function actualizarTablaCarrito() {
    let html = '';
    let total = 0;

    carrito.forEach((item, index) => {
        html += `
            <tr>
                <td>${item.codigo}</td>
                <td>${item.nombre}</td>
                <td>${item.cantidad}</td>
                <td>${item.precio}</td>
                <td>${item.subtotal}</td>
                <td>
                    <button class="btn btn-danger btn-sm" onclick="eliminarItem(${index})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        total += item.subtotal;
    });

    $('#detalleVenta').html(html);
    $('#totalVenta').text(total.toFixed(2));
}

function eliminarItem(index) {
    carrito.splice(index, 1);
    actualizarTablaCarrito();
}

function procesarVenta() {
    if (!clienteActual) {
        alert('Debe seleccionar un cliente');
        return;
    }

    if (carrito.length === 0) {
        alert('El carrito está vacío');
        return;
    }

    const venta = {
        cedula: clienteActual.CED_CLI,
        total: parseFloat($('#totalVenta').text()),
        detalle: carrito.map(item => ({
            codigo_producto: item.codigo,
            cantidad: item.cantidad
        }))
    };

    $.ajax({
        url: '../api/ventas.php',
        type: 'POST',
        data: JSON.stringify(venta),
        contentType: 'application/json',
        success: function(response) {
            if (response.success) {
                alert('Venta procesada exitosamente');
                carrito = [];
                clienteActual = null;
                $('#clienteInfo').addClass('d-none');
                $('#cedula').val('');
                actualizarTablaCarrito();
            } else {
                alert('Error al procesar la venta: ' + response.message);
            }
        }
    });
}