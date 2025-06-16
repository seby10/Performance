// filepath: c:\xampp\htdocs\Performance\ajax\clientes.js
let clienteModal;

$(document).ready(function() {
    clienteModal = new bootstrap.Modal(document.getElementById('clienteModal'));
    cargarClientes();
});

function cargarClientes() {
    $.ajax({
        url: '../api/clientes.php',
        type: 'GET',
        success: function(response) {
            if (response.success) {
                let html = '';
                response.data.forEach(cliente => {
                    html += `
                        <tr>
                            <td>${cliente.CED_CLI}</td>
                            <td>${cliente.NOM_CLI}</td>
                            <td>${cliente.APE_CLI}</td>
                            <td>${cliente.DIR_CLI}</td>
                            <td>${cliente.TEL_CLI}</td>
                        </tr>
                    `;
                });
                $('#clientesTableBody').html(html);
            } else {
                alert('Error al cargar clientes: ' + response.message);
            }
        },
        error: function() {
            alert('Error al conectar con el servidor');
        }
    });
}

function showClienteModal() {
    document.getElementById('clienteForm').reset();
    clienteModal.show();
}

function guardarCliente() {
    const formData = {
        cedula: $('[name=cedula]').val(),
        nombre: $('[name=nombre]').val(),
        apellido: $('[name=apellido]').val(),
        direccion: $('[name=direccion]').val(),
        telefono: $('[name=telefono]').val()
    };

    $.ajax({
        url: '../api/clientes.php',
        type: 'POST',
        data: JSON.stringify(formData),
        contentType: 'application/json',
        success: function(response) {
            if (response.success) {
                clienteModal.hide();
                cargarClientes();
            } else {
                alert('Error al guardar cliente: ' + response.message);
            }
        },
        error: function() {
            alert('Error al conectar con el servidor');
        }
    });
}