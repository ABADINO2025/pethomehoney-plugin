<?php
/**
 * Panel de gestión de cuidadores
 */
function pethome_cuidadores_panel() {
    echo '<h1 style="color:#5e4365;">👤 Gestión de Cuidadores</h1>';

    // Procesar formulario de alta/edición
    if ( $_SERVER["REQUEST_METHOD"] === "POST" && ! empty( $_POST["cuidador_alias"] ) ) {
        $cuidadores = get_option( 'pethome_cuidadores', [] );

        $nuevo = [
            "alias"                => sanitize_text_field( $_POST["cuidador_alias"] ),
            "nombre"               => sanitize_text_field( $_POST["cuidador_nombre"] ),
            "apellido"             => sanitize_text_field( $_POST["cuidador_apellido"] ),
            "email"                => sanitize_email( $_POST["cuidador_email"] ),
            "telefono"             => sanitize_text_field( $_POST["cuidador_telefono"] ),
            "calle"                => sanitize_text_field( $_POST["cuidador_domicilio_calle"] ),
            "numero"               => sanitize_text_field( $_POST["cuidador_domicilio_numero"] ),
            "piso"                 => sanitize_text_field( $_POST["cuidador_domicilio_piso"] ),
            "barrio"               => sanitize_text_field( $_POST["cuidador_domicilio_barrio"] ),
            "dni"                  => sanitize_text_field( $_POST["cuidador_dni"] ),
            "alias_bancario"       => sanitize_text_field( $_POST["cuidador_alias_bancario"] ),
            "temp1"                => sanitize_text_field( $_POST["cuidador_temp1"] ),
            "imagen"               => esc_url_raw( $_POST["imagen_cuidador"]   ?? '' ),
            "dni_frente"           => esc_url_raw( $_POST["imagen_dni_frente"] ?? '' ),
            "dni_dorso"            => esc_url_raw( $_POST["imagen_dni_dorso"]  ?? '' ),
            "phh"                  => esc_url_raw( $_POST["imagen_phh"]        ?? '' ),
            "domicilio_img"        => esc_url_raw( $_POST["imagen_domicilio"] ?? '' ),
        ];

        $cuidadores[] = $nuevo;
        update_option( 'pethome_cuidadores', $cuidadores );
        echo '<div class="updated"><p>✅ Cuidador guardado correctamente.</p></div>';
    }

    // Estilos
    echo '<style>
    .section-block {
        background: #f9f9f9;
        border: 2px solid #ccc;
        border-radius: 16px;
        padding: 20px;
        margin-top: 30px;
    }
    .section-block h2 {
        margin-bottom: 20px;
        color: #5e4365;
    }
    .pethome-grid { display: grid; gap: 16px; }
    .grid-4 { grid-template-columns: repeat(4, 1fr); }
    .grid-5 { grid-template-columns: repeat(5, 1fr); }
    .pethome-grid label { display: block; font-weight: bold; margin-bottom: 4px; color: #5e4365; }
    .pethome-grid input[type="text"],
    .pethome-grid input[type="email"] {
        width: 100%; background: #f0f0f1; padding: 8px; border: 1px solid #ccc; border-radius: 6px;
    }
    .media-button {
        display: block; width: 100%; text-align: center; height: 46px; line-height: 46px;
        background: #5e4365; color: #fff; font-weight: bold; border: none; border-radius: 6px;
        cursor: pointer; margin-bottom: 10px;
    }
    .media-button:hover { background: #7a5d8d; }
    .preview-image { margin-top: 10px; width: 100%; height: auto; border-radius: 6px; }
    table.cuidadores-listado {
        width: 100%; border-collapse: separate; border-spacing: 0; margin-top: 20px;
        border: 1px solid #ccc; border-radius: 20px; overflow: hidden;
    }
    table.cuidadores-listado th, table.cuidadores-listado td {
        padding: 8px; border: 1px solid #ccc; text-align: left;
    }
    table.cuidadores-listado th { background-color: #eee; color: #5e4365; }
    table.cuidadores-listado tr:hover { background-color: #f3eef4; }
    table.cuidadores-listado th:first-child { border-top-left-radius: 12px; }
    table.cuidadores-listado th:last-child  { border-top-right-radius: 12px; }
    table.cuidadores-listado tr:last-child td:first-child { border-bottom-left-radius: 12px; }
    table.cuidadores-listado tr:last-child td:last-child  { border-bottom-right-radius: 12px; }
    .guardar-cuidador {
        display: block; margin: 20px auto; width: 200px; height: 46px; line-height: 46px;
        background: #5e4365; color: #fff; font-weight: bold; border: none; border-radius: 6px;
        cursor: pointer;
    }
    .guardar-cuidador:hover { background: #7a5d8d; }
    </style>';

    // Formulario
    echo '<form method="post">';
    echo '<div class="section-block"><h2>👤 Datos del Cuidador</h2><div class="pethome-grid grid-4">';
    $fields = [
        ['cuidador_alias','Alias'], ['cuidador_nombre','Nombre'], ['cuidador_apellido','Apellido'],
        ['cuidador_email','Email'], ['cuidador_telefono','Teléfono'], ['cuidador_domicilio_calle','Calle'],
        ['cuidador_domicilio_numero','Número'], ['cuidador_domicilio_piso','Piso'],
        ['cuidador_domicilio_barrio','Barrio'], ['cuidador_dni','DNI'],
        ['cuidador_alias_bancario','Alias Bancario'], ['cuidador_temp1','Temp1'],
    ];
    foreach ( $fields as $f ) {
        echo "<label>{$f[1]}<input type='text' name='{$f[0]}' required></label>";
    }
    echo '</div></div>';

    echo '<div class="section-block"><h2>📂 Archivos del Cuidador</h2><div class="pethome-grid grid-5">';
    $imgFields = [
        ['imagen_cuidador','📸 Cuidador'],
        ['imagen_dni_frente','🪪 DNI Frente'],
        ['imagen_dni_dorso','🪪 DNI Dorso'],
        ['imagen_phh','📘 PHH'],
        ['imagen_domicilio','🏠 Domicilio'],
    ];
    foreach ( $imgFields as $img ) {
        echo "<div>
            <label>{$img[1]}</label>
            <input type='hidden' id='{$img[0]}_input' name='{$img[0]}'>
            <button type='button' class='media-button' data-target='{$img[0]}_input' data-preview='{$img[0]}_preview'>
                Seleccionar imagen
            </button>
            <img id='{$img[0]}_preview' class='preview-image' src='' style='display:none;'>
        </div>";
    }
    echo '</div></div>';

    echo '<input type="submit" class="guardar-cuidador" value="Guardar Cuidador">';
    echo '</form>';

    // Listado de cuidadores
    $cuidadores = get_option( 'pethome_cuidadores', [] );
    if ( ! empty( $cuidadores ) ) {
        echo '<div class="section-block"><h2>📋 Listado de Cuidadores</h2>';
        echo '<table class="cuidadores-listado"><thead><tr>
            <th class="foto-cuidador">Foto</th>
            <th>Alias</th><th>Nombre y Apellido</th><th>Domicilio</th>
            <th>Teléfono</th><th>Email</th><th>Alias Bancario</th>
        </tr></thead><tbody>';
        foreach ( $cuidadores as $i => $c ) {
            $img = ! empty( $c['imagen'] ) ? esc_url( $c['imagen'] ) : '';
            echo '<tr>';
            echo '<td class="foto-cuidador">';
                if ( $img ) {
                    echo "<img src='{$img}' style='width:100px;height:100px;border-radius:8px;object-fit:cover;'>";
                }
            echo '</td>';
            $alias = esc_html( $c['alias'] ?? '' );
            echo "<td><a href='admin.php?page=pethome_cuidador_editar&id={$i}'>{$alias}</a></td>";
            $nombre = esc_html( trim( ($c['nombre'] ?? '') . ' ' . ($c['apellido'] ?? '') ) );
            echo "<td>{$nombre}</td>";
            $dom   = esc_html( ($c['calle'] ?? '') . ' ' . ($c['numero'] ?? '') );
            echo "<td>{$dom}</td>";
            echo '<td>' . esc_html( $c['telefono'] ?? '' ) . '</td>';
            echo '<td>' . esc_html( $c['email'] ?? '' ) . '</td>';
            echo '<td>' . esc_html( $c['alias_bancario'] ?? '' ) . '</td>';
            echo '</tr>';
        }
        echo '</tbody></table></div>';
    } else {
        echo '<p>ℹ️ No hay cuidadores registrados aún.</p>';
    }

    // Script para media uploader de WP
    echo '<script>
    jQuery(function($){
        $(".media-button").on("click", function(e){
            e.preventDefault();
            var target  = $("#" + $(this).data("target"));
            var preview = $("#" + $(this).data("preview"));
            var frame = wp.media({
                title: "Seleccionar imagen",
                multiple: false,
                library: { type: "image" },
                button: { text: "Usar esta imagen" }
            });
            frame.on("select", function(){
                var attach = frame.state().get("selection").first().toJSON();
                target.val(attach.url);
                preview.attr("src", attach.url).show();
            });
            frame.open();
        });
    });
    </script>';
}
