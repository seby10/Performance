let clienteModal;
let clientes = [];
let clientesPorPagina = 10;
let paginaActual = 1;

$(document).ready(function () {
  clienteModal = new bootstrap.Modal(document.getElementById("clienteModal"));
  cargarClientes();
});

function cargarClientes() {
  $.ajax({
    url: "../api/clientes.php",
    type: "GET",
    success: function (response) {
      if (response.success) {
        clientes = response.data;
        paginaActual = 1; // Reiniciar a la primera página
        mostrarClientesPaginados();
        generarPaginacion();
      } else {
        alert("Error al cargar clientes: " + response.message);
      }
    },
    error: function () {
      alert("Error al conectar con el servidor");
    },
  });
}

function mostrarClientesPaginados() {
  const inicio = (paginaActual - 1) * clientesPorPagina;
  const fin = inicio + clientesPorPagina;
  const clientesPagina = clientes.slice(inicio, fin);

  let html = "";
  clientesPagina.forEach((cliente) => {
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
  $("#clientesTableBody").html(html);
}

function generarPaginacion() {
  const totalPaginas = Math.ceil(clientes.length / clientesPorPagina);

  $("#pagina-actual").text(`Página ${paginaActual} de ${totalPaginas}`);

  $("#prev-page").toggleClass("disabled", paginaActual <= 1);
  $("#next-page").toggleClass("disabled", paginaActual >= totalPaginas);

  $("#prev-page a")
    .off("click")
    .on("click", function (e) {
      e.preventDefault();
      if (paginaActual > 1) {
        paginaActual--;
        mostrarClientesPaginados();
        generarPaginacion();
      }
    });

  $("#next-page a")
    .off("click")
    .on("click", function (e) {
      e.preventDefault();
      if (paginaActual < totalPaginas) {
        paginaActual++;
        mostrarClientesPaginados();
        generarPaginacion();
      }
    });
}

function cambiarPagina(pagina) {
  paginaActual = pagina;
  mostrarClientesPaginados();
  generarPaginacion();
}

function showClienteModal() {
  document.getElementById("clienteForm").reset();
  clienteModal.show();
}

function guardarCliente() {
  const formData = {
    cedula: $("[name=cedula]").val(),
    nombre: $("[name=nombre]").val(),
    apellido: $("[name=apellido]").val(),
    direccion: $("[name=direccion]").val(),
    telefono: $("[name=telefono]").val(),
  };

  $.ajax({
    url: "../api/clientes.php",
    type: "POST",
    data: JSON.stringify(formData),
    contentType: "application/json",
    success: function (response) {
      if (response.success) {
        clienteModal.hide();
        cargarClientes();
      } else {
        alert("Error al guardar cliente: " + response.message);
      }
    },
    error: function () {
      alert("Error al conectar con el servidor");
    },
  });
}
