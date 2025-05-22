<?php
function pethome_importador_booking_func() {
?>
<div class="wrap" style="margin: 40px;">
    <h1 style="text-align:center; color:#5e4365; font-size:32px; margin-bottom:20px;">📦 Importar Reservas desde Booking</h1>

    <div class="section-block" style="background: #f9f9f9; padding: 20px; border-radius: 16px; border: 2px solid #ccc;">
        <div style="text-align:center; margin-bottom: 20px;">
            <a href="#" id="btnImportarBooking" class="page-title-action" style="font-size:16px; font-weight:bold; background-color:#5e4365; color:white; padding:8px 16px; border-radius:5px; text-decoration:none;">📦 Importar Reservas desde Booking</a>
        </div>
    </div>

    <!-- Overlay -->
    <div id="overlayImportando" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(94, 67, 101, 0.92); z-index: 9999; color: white; display: none;
    flex-direction: column; justify-content: center; align-items: center; font-size: 22px;
    font-weight: bold;">
        <div class="spinner" style="border: 6px solid #f3f3f3; border-top: 6px solid #ffffff; border-radius: 50%;
        width: 60px; height: 60px; animation: girar 1s linear infinite;"></div>

        <div style="margin-top: 30px; width: 80%; max-width: 500px; padding: 0 10px;">
            <div id="barra-progreso-import" style="width: 100%; background: #eee; border-radius: 10px;
            height: 48px; box-shadow: inset 0 0 5px #ccc; overflow: hidden;">
                <div id="progreso-interno-import"></div>
            </div>
        </div>

        <div id="mensajeImportacion" style="margin-top: 20px; font-size: 28px;"></div>
    </div>

    <style>
        @keyframes girar {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @keyframes flujo {
            0% { background-position: 0 0; }
            100% { background-position: 200% 0; }
        }

        #progreso-interno-import {
            height: 100%;
            width: 0;
            background: repeating-linear-gradient(
                45deg,
                #ffffff,
                #ffffff 10px,
                #e5e5f0 10px,
                #e5e5f0 20px
            );
            background-size: 200% 100%;
            animation: flujo 1s linear infinite;
            color: #5e4365;
            text-align: right;
            line-height: 48px;
            padding-right: 10px;
            border-radius: 10px;
            transition: width 0.4s ease;
            font-weight: bold;
        }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const btn = document.getElementById("btnImportarBooking");
        const overlay = document.getElementById("overlayImportando");
        const progreso = document.getElementById("progreso-interno-import");
        const mensaje = document.getElementById("mensajeImportacion");

        btn.addEventListener("click", function (e) {
            e.preventDefault();
            overlay.style.display = "flex";
            progreso.style.width = "0%";
            progreso.innerText = "";
            mensaje.innerText = "";

            fetch(ajaxurl + "?action=importar_bookings_pethome")
                .then(res => res.json())
                .then(data => {
                    const total = data.total;
                    const ids = data.ids;
                    if (!total) return;

                    let procesados = 0;
                    let guardasImportadas = 0;

                    function importarUno() {
                        if (procesados >= ids.length) {
                            progreso.style.width = "100%";
                            progreso.innerText = "100%";
                            mensaje.innerText = `✅ Se han importado ${guardasImportadas} guardas`;
                            setTimeout(() => {
                                window.location.href = "admin.php?page=pethome_reservas";
                            }, 3000);
                            return;
                        }

                        const id = ids[procesados];
                        fetch(ajaxurl + "?action=importar_booking_pethome&id=" + id)
                            .then(res => res.json())
                            .then(respuesta => {
                                if (respuesta.insertado) {
                                    guardasImportadas++;
                                }
                                procesados++;
                                let porcentaje = Math.round((procesados / total) * 100);
                                progreso.style.width = porcentaje + "%";
                                progreso.innerText = porcentaje + "%";
                                setTimeout(importarUno, 200);
                            });
                    }

                    importarUno();
                });
        });
    });
    </script>
</div>
<?php } ?>
