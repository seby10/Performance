let reportes = [];
let reportesPorPagina = 10;
let paginaActual = 1;
let limiteSeleccionado = 1000;

$(document).ready(function () {
  // Inicializar con el valor por defecto
  $("#dropdownLimite").text(`Cantidad de Registros: ${limiteSeleccionado}`);

  cargaReportes();

  $(".dropdown-item").click(function (e) {
    e.preventDefault();
    limiteSeleccionado = $(this).data("value");
    $("#limite").val(limiteSeleccionado);
    $("#dropdownLimite").text(`Cantidad de Registros: ${limiteSeleccionado}`);
    cargaReportes();
  });
});

function cargaReportes() {
  const limite = $("#limite").val();
  $.ajax({
    url: `../api/ventas.php?limite=${limite}`,
    type: "GET",
    success: function (response) {
      if (response.success) {
        reportes = response.data;
        paginaActual = 1;
        mostrarProductosPaginados();
        generarPaginacion();
      } else {
        alert("Error al cargar los reportes: " + response.message);
      }
    },
    error: function () {
      alert("Error al conectar con el servidor");
    },
  });
}

function mostrarProductosPaginados() {
  const inicio = (paginaActual - 1) * reportesPorPagina;
  const fin = inicio + reportesPorPagina;
  const reportesPagina = reportes.slice(inicio, fin);

  let html = "";
  reportesPagina.forEach((reporte) => {
    html += `
            <tr>
              <td>${reporte.NUM_FAC}</td>
              <td>${reporte.FEC_FAC}</td>
              <td>${reporte.NOM_CLI + " " + reporte.APE_CLI}</td>
              <td>${reporte.NOM_PRO}</td>
              <td>${reporte.PRE_UNI_PRO}</td>
              <td>${reporte.CANTIDAD}</td>
              <td>${reporte.TOTAL}</td>
            </tr>
          `;
  });
  $("#reportesTableBody").html(html);
}

function generarPaginacion() {
  const totalPaginas = Math.ceil(reportes.length / reportesPorPagina);

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
