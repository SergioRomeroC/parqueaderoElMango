// =========================================================
// RELOJ
// =========================================================

function actualizarReloj(){
    const reloj = document.getElementById("reloj");
    if(reloj){
        reloj.textContent = new Date().toLocaleString("es-CO", {
            weekday: "long", day: "numeric", month: "long",
            hour: "2-digit", minute: "2-digit", second: "2-digit"
        });
    }
}

// =========================================================
// TEMA CLARO / OSCURO (se recuerda entre visitas)
// =========================================================

function temaActual(){
    return document.documentElement.getAttribute("data-theme") === "light" ? "light" : "dark";
}

function pintarBotonTema(){
    const b = document.getElementById("btnTema");
    if(b){
        b.textContent = temaActual() === "dark" ? "☀️ Modo claro" : "🌙 Modo oscuro";
    }
}

function cambiarTema(){
    const nuevo = temaActual() === "dark" ? "light" : "dark";
    document.documentElement.setAttribute("data-theme", nuevo);
    try{ localStorage.setItem("tema_mango", nuevo); }catch(e){}
    pintarBotonTema();
    if(typeof window.recargarGraficos === "function"){
        window.recargarGraficos();
    }
}

// =========================================================
// BUSCADOR (placa o propietario)
// =========================================================

function buscarVehiculo(){
    const campo = document.getElementById("buscar");
    if(!campo){ return; }

    const filtro = campo.value.trim().toUpperCase();
    const filas = document.querySelectorAll("#tablaVehiculos tbody tr");
    let visibles = 0;

    filas.forEach(function(fila){
        const texto = (fila.dataset.buscar || "").toUpperCase();
        const mostrar = texto.indexOf(filtro) > -1;
        fila.style.display = mostrar ? "" : "none";
        if(mostrar){ visibles++; }
    });

    const vacio = document.getElementById("sinResultados");
    if(vacio){ vacio.style.display = visibles === 0 ? "" : "none"; }
}

// =========================================================
// CONFIRMACIONES
// =========================================================

function confirmarEliminar(){
    return confirm("¿Desea eliminar este vehículo? Esta acción no se puede deshacer.");
}

function confirmarSalida(){
    return confirm("¿Registrar la salida y cobrar este vehículo?");
}

function confirmarCierre(){
    return confirm("¿Cerrar la caja? El recaudo pendiente quedará en $ 0, pero el historial y los reportes se conservan.");
}

// =========================================================
// REGISTRO: tarifas sugeridas según tipo de vehículo
// =========================================================

function sugerirTarifa(){
    if(typeof TARIFAS_SUGERIDAS === "undefined"){ return; }
    const tipo = document.getElementById("tipo");
    const modo = document.getElementById("tarifa_tipo");
    const valor = document.getElementById("tarifa_valor");
    const nota = document.getElementById("notaTarifa");
    if(!tipo || !modo || !valor){ return; }

    const sugerida = (TARIFAS_SUGERIDAS[tipo.value] || {})[modo.value];
    if(sugerida !== undefined && !valor.dataset.editado){
        valor.value = sugerida;
    }
    if(nota){
        nota.textContent = modo.value === "minuto"
            ? "Se cobra este valor por cada minuto (se redondea hacia arriba)."
            : "Se cobra este valor fijo sin importar el tiempo.";
    }
}

document.addEventListener("DOMContentLoaded", function(){
    pintarBotonTema();
    actualizarReloj();
    setInterval(actualizarReloj, 1000);

    const valor = document.getElementById("tarifa_valor");
    if(valor){
        valor.addEventListener("input", function(){ valor.dataset.editado = "1"; });
        ["tipo","tarifa_tipo"].forEach(function(id){
            const el = document.getElementById(id);
            if(el){
                el.addEventListener("change", function(){
                    delete valor.dataset.editado;
                    sugerirTarifa();
                });
            }
        });
        sugerirTarifa();
    }

    const placa = document.getElementById("placa");
    if(placa){
        placa.addEventListener("input", function(){
            placa.value = placa.value.toUpperCase().replace(/[^A-Z0-9-]/g, "");
        });
    }
});

// Actualiza el tiempo transcurrido y el valor estimado de los vehículos dentro
setInterval(function(){
    document.querySelectorAll("[data-entrada]").forEach(function(el){
        const entrada = parseInt(el.dataset.entrada, 10) * 1000;
        const minutos = Math.max(1, Math.ceil((Date.now() - entrada) / 60000));
        const h = Math.floor(minutos / 60);
        el.textContent = h > 0 ? h + " h " + (minutos % 60) + " min" : minutos + " min";

        const est = document.querySelector('[data-estimado="' + el.dataset.id + '"]');
        if(est && est.dataset.modo === "minuto"){
            const v = minutos * parseFloat(est.dataset.valor);
            est.textContent = "$ " + Math.round(v).toLocaleString("es-CO");
        }
    });
}, 30000);
