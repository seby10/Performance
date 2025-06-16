let productoModal;

$(document).ready(function() {
    productoModal = new bootstrap.Modal(document.getElementById('productoModal'));
    cargarProductos();
});

function cargarProductos() {
    $.ajax({
        url: '../api/productos.php',
        type: 'GET',
        success: function(response) {
            if (response.success) {
                let html = '';
                response.data.forEach(producto => {
                    html += `
                        <tr>
                            <td>${producto.COD_PRO}</td>
                            <td>${producto.NOM_PRO}</td>
                            <td>${producto.MAR_PRO}</td>
                            <td>${producto.PRE_UNI_PRO}</td>
                            <td>${producto.EXISTENCIA}</td>
                        </tr>
                    `;
                });
                $('#productosTableBody').html(html);
            } else {
                alert('Error al cargar productos: ' + response.message);
            }
        },
        error: function() {
            alert('Error al conectar con el servidor');
        }
    });
}

function showProductoModal() {
    document.getElementById('productoForm').reset();
    productoModal.show();
}

function guardarProducto() {
    const formData = {
        codigo: $('[name=codigo]').val(),
        nombre: $('[name=nombre]').val(),
        marca: $('[name=marca]').val(),
        precio: parseInt($('[name=precio]').val()),
        existencia: parseInt($('[name=existencia]').val())
    };

    $.ajax({
        url: '../api/productos.php',
        type: 'POST',
        data: JSON.stringify(formData),
        contentType: 'application/json',
        success: function(response) {
            if (response.success) {
                productoModal.hide();
                cargarProductos();
            } else {
                alert('Error al guardar producto: ' + response.message);
            }
        },
        error: function() {
            alert('Error al conectar con el servidor');
        }
    });
}