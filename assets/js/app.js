/* =========================================
   MODAL
========================================= */

const modalUsuario =
new bootstrap.Modal(
    document.getElementById("usuarioModal")
);

/* =========================================
   CARGAR USUARIOS
========================================= */

document.addEventListener(
    "DOMContentLoaded",
    cargarUsuarios
);

function cargarUsuarios(){

    fetch("obtener.php")

    .then(response => response.json())

    .then(data => {

        let tabla = "";

        data.forEach(usuario => {

            /* BADGE ROL */

            let badgeRol = "";

            if(usuario.rol == "admin"){

                badgeRol = `
                
                <span class="badge badge-admin">

                    Admin

                </span>

                `;

            }else if(
                usuario.rol == "supervisor"
            ){

                badgeRol = `
                
                <span class="badge badge-supervisor">

                    Supervisor

                </span>

                `;

            }else{

                badgeRol = `
                
                <span class="badge badge-user">

                    Usuario

                </span>

                `;

            }

            /* BOTONES */

            let botones = "";

            /* EDITAR */

            if(usuario.editable){

                botones += `

                <button
                class="btn btn-edit"

                onclick="abrirModalEditar(
                    '${usuario.id}',
                    '${usuario.nombre}',
                    '${usuario.email}',
                    '${usuario.rol}'
                )">

                    <i class="bi bi-pencil-square"></i>

                </button>

                `;

            }

            /* ELIMINAR SOLO NO ADMIN */

            if(usuario.rol != "admin"){

                botones += `

                <button
                class="btn btn-delete"

                onclick="eliminarUsuario(
                    ${usuario.id}
                )">

                    <i class="bi bi-trash"></i>

                </button>

                `;

            }

            /* FILA */

            tabla += `
            
            <tr>

                <td>

                    ${usuario.id}

                </td>

                <td>

                    <strong>

                        ${usuario.nombre}

                    </strong>

                </td>

                <td>

                    ${usuario.email}

                </td>

                <td>

                    ${badgeRol}

                </td>

                <td>

                    ${botones}

                </td>

            </tr>

            `;

        });

        document.getElementById(
            "tablaUsuarios"
        ).innerHTML = tabla;

    });

}

/* =========================================
   MODAL NUEVO
========================================= */

function abrirModalNuevo(){

    document.getElementById(
        "tituloModal"
    ).innerText =
    "Nuevo Usuario";

    document.getElementById(
        "idUsuario"
    ).value = "";

    document.getElementById(
        "nombre"
    ).value = "";

    document.getElementById(
        "email"
    ).value = "";

    document.getElementById(
        "password"
    ).value = "";

    document.getElementById(
        "rol"
    ).value = "usuario";

    modalUsuario.show();

}

/* =========================================
   MODAL EDITAR
========================================= */

function abrirModalEditar(
    id,
    nombre,
    email,
    rol
){

    document.getElementById(
        "tituloModal"
    ).innerText =
    "Editar Usuario";

    document.getElementById(
        "idUsuario"
    ).value = id;

    document.getElementById(
        "nombre"
    ).value = nombre;

    document.getElementById(
        "email"
    ).value = email;

    document.getElementById(
        "password"
    ).value = "";

    document.getElementById(
        "rol"
    ).value = rol;

    modalUsuario.show();

}

/* =========================================
   GUARDAR USUARIO
========================================= */

function guardarUsuario(){

    const id =
    document.getElementById(
        "idUsuario"
    ).value;

    const nombre =
    document.getElementById(
        "nombre"
    ).value;

    const email =
    document.getElementById(
        "email"
    ).value;

    const password =
    document.getElementById(
        "password"
    ).value;

    const rol =
    document.getElementById(
        "rol"
    ).value;

    /* FORM DATA */

    let formData =
    new FormData();

    formData.append(
        "nombre",
        nombre
    );

    formData.append(
        "email",
        email
    );

    formData.append(
        "password",
        password
    );

    formData.append(
        "rol",
        rol
    );

    /* URL */

    let url =
    "guardar.php";

    /* ACTUALIZAR */

    if(id != ""){

        formData.append(
            "id",
            id
        );

        url =
        "actualizar.php";

    }

    /* FETCH */

    fetch(url,{

        method:"POST",

        body:formData

    })

    .then(response => response.text())

    .then(data => {

    /* ERROR */

    if(
        data.includes("No autorizado")
        ||
        data.includes("No puedes")
        ||
        data.includes("Error")
    ){

        Swal.fire({

            icon:"error",

            title:"Acceso denegado",

            text:data

        });

        return;

    }

    /* SUCCESS */

    Swal.fire({

        icon:"success",

        title:"Correcto",

        text:data,

        timer:1500,

        showConfirmButton:false

    });

    modalUsuario.hide();

    cargarUsuarios();

});

   

}

/* =========================================
   ELIMINAR
========================================= */

function eliminarUsuario(id){

    Swal.fire({

        title:"¿Eliminar usuario?",

        text:"Esta acción no se puede deshacer",

        icon:"warning",

        showCancelButton:true,

        confirmButtonText:"Sí, eliminar",

        cancelButtonText:"Cancelar"

    })

    .then((result)=>{

        if(result.isConfirmed){

            let formData =
            new FormData();

            formData.append(
                "id",
                id
            );

            fetch(
                "eliminar.php",
                {

                    method:"POST",

                    body:formData

                }
            )

            .then(response => response.text())

            .then(data => {

    /* ERROR */

    if(
        data.includes("No autorizado")
        ||
        data.includes("Error")
    ){

        Swal.fire({

            icon:"error",

            title:"Acceso denegado",

            text:data

        });

        return;

    }

    /* SUCCESS */

    Swal.fire({

        icon:"success",

        title:"Eliminado",

        text:data,

        timer:1500,

        showConfirmButton:false

    });

    cargarUsuarios();

});

          

        }

    });

}