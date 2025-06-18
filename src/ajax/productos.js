let productoModal;
let productos = [];
let productosPorPagina = 10;
let paginaActual = 1;

$(document).ready(function () {
    productoModal = new bootstrap.Modal(document.getElementById('productoModal'));
    cargarProductos();
});

function cargarProductos() {
    $.ajax({
        url: '../api/productos.php',
        type: 'GET',
        success: function (response) {
            if (response.success) {
                productos = response.data;
                paginaActual = 1; // Reiniciar a la primera página
                mostrarProductosPaginados();
                generarPaginacion();
            } else {
                alert('Error al cargar productos: ' + response.message);
            }
        },
        error: function () {
            alert('Error al conectar con el servidor');
        }
    });
}

function mostrarProductosPaginados() {
    const inicio = (paginaActual - 1) * productosPorPagina;
    const fin = inicio + productosPorPagina;
    const productosPagina = productos.slice(inicio, fin);

    let html = '';
    productosPagina.forEach(producto => {
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
}

function generarPaginacion() {
  const totalPaginas = Math.ceil(productos.length / productosPorPagina);

  // Texto central: Página X de Y
  $("#pagina-actual").text(`Página ${paginaActual} de ${totalPaginas}`);

  // Habilitar/deshabilitar botones
  $("#prev-page").toggleClass("disabled", paginaActual <= 1);
  $("#next-page").toggleClass("disabled", paginaActual >= totalPaginas);

  // Eventos de navegación
  $("#prev-page a")
    .off("click")
    .on("click", function (e) {
      e.preventDefault();
      if (paginaActual > 1) {
        paginaActual--;
        mostrarProductosPaginados();
        generarPaginacion();
      }
    });

  $("#next-page a")
    .off("click")
    .on("click", function (e) {
      e.preventDefault();
      if (paginaActual < totalPaginas) {
        paginaActual++;
        mostrarProductosPaginados();
        generarPaginacion();
      }
    });
}

function cambiarPagina(pagina) {
    paginaActual = pagina;
    mostrarProductosPaginados();
    generarPaginacion();
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