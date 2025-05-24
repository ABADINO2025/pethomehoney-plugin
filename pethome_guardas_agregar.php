<?php
/**
 * pethome_guardas_agregar.php  ·  Formulario completo “Agregar Guarda”
 * – Selector único con Productos Booking + Servicios Creados
 * – Precios reales de Booking (_wc_booking_block_cost / _wc_booking_cost)
 * – Cálculo dinámico del precio diario y total
 * – Incluye todas las secciones: Cliente, Mascota, Sociabilidad, Sanidad, Seguridad
 * – Modal numérico para teléfono y formateo de DNI
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/*──────────── 1. PRODUCTOS BOOKING ────────────*/
// Se recomienda usar WP_Query o wc_get_products con argumentos más específicos si hay muchos productos.
// Para este caso, limit: -1 está bien si el número de productos booking no es excesivo.
$bookings = wc_get_products( [ 'type' => 'booking', 'limit' => -1 ] );

/**
 * Obtener el costo diario real de un producto de booking.
 *
 * @param WC_Product_Booking $product El objeto del producto de booking.
 * @return float El costo diario del producto.
 */
function pethome_get_booking_daily_cost( WC_Product_Booking $product ) {
    $id = $product->get_id();
    // Prioriza el costo por bloque (diario) sobre el costo base fijo.
    $block_cost = (float) get_post_meta( $id, '_wc_booking_block_cost', true );
    $base_cost  = (float) get_post_meta( $id, '_wc_booking_cost', true );

    if ( $block_cost > 0 ) {
        return $block_cost;
    }
    if ( $base_cost > 0 ) {
        return $base_cost;
    }
    return 0;
}

/*──────────── 2. SERVICIOS CREADOS ────────────*/
// Guardados en opción `pethome_precios_base`
$servicios_creados = get_option( 'pethome_precios_base', [] );

/*──────────── 3. TIPOS Y RAZAS DE MASCOTAS ────────────*/
$tipos_mascota = get_option( 'pethome_tipos_mascotas', [] );
$razas         = get_option( 'pethome_razas', [] );

// Para pasar las razas al JS de forma organizada por tipo
$razas_por_tipo = [];
foreach ( $razas as $raza_data ) {
    if ( isset( $raza_data['tipo_mascota'] ) && isset( $raza_data['raza'] ) ) {
        $tipo = sanitize_title( $raza_data['tipo_mascota'] ); // Sanitizar el tipo para usar como clave
        if ( ! isset( $razas_por_tipo[$tipo] ) ) {
            $razas_por_tipo[$tipo] = [];
        }
        $razas_por_tipo[$tipo][] = sanitize_text_field( $raza_data['raza'] );
    }
}

/*──────────── 4. DATOS DE LA GUARDA (para edición) ────────────*/
$post_id = isset($_GET['post']) ? intval($_GET['post']) : 0; // Obtener el ID del post si estamos editando
$guarda_data = [];

if ( $post_id ) {
    // Si estamos editando, cargar los datos existentes
    // Se recomienda una función auxiliar para cargar metadatos para evitar repetición y mejorar la legibilidad.
    $meta_keys = [
        'pethome_guarda_nombre',
        'pethome_guarda_ubicacion',
        'pethome_guarda_latitud',
        'pethome_guarda_longitud',
        'pethome_guarda_tarifa_base',
        'pethome_guarda_descripcion',
        'pethome_guarda_imagen_id',
        'pethome_cliente_nombre',
        'pethome_cliente_dni',
        'pethome_cliente_email',
        'pethome_cliente_telefono',
        'pethome_reserva_observaciones',
        'pethome_reserva_cuidador_asignado',
        'pethome_reserva_cargos',
        'pethome_reserva_entrega',
        'pethome_reserva_saldo_final',
        'pethome_reserva_fechas',
        'pethome_reserva_hora_ingreso',
        'pethome_reserva_hora_egreso',
        'pethome_reserva_servicio',
        'pethome_mascota_nombre', // Nuevo campo
        'pethome_mascota_tipo',   // Nuevo campo
        'pethome_mascota_raza',   // Nuevo campo
        'pethome_mascota_edad',   // Nuevo campo
        'pethome_mascota_peso',   // Nuevo campo
        'pethome_mascota_sexo',   // Nuevo campo
        'pethome_mascota_castrada', // Nuevo campo
        'pethome_mascota_enfermedades', // Nuevo campo
        'pethome_mascota_medicamentos', // Nuevo campo
        'pethome_mascota_alergias', // Nuevo campo
        'pethome_mascota_sociable_perros', // Nuevo campo
        'pethome_mascota_sociable_gatos', // Nuevo campo
        'pethome_mascota_sociable_ninios', // Nuevo campo
        'pethome_mascota_agresivo_personas', // Nuevo campo
        'pethome_mascota_agresivo_otros_animales', // Nuevo campo
        'pethome_mascota_observaciones_sociabilidad', // Nuevo campo
        'pethome_mascota_vacunas_completas', // Nuevo campo
        'pethome_mascota_desparasitado', // Nuevo campo
        'pethome_mascota_antipulgas', // Nuevo campo
        'pethome_mascota_veterinario_nombre', // Nuevo campo
        'pethome_mascota_veterinario_telefono', // Nuevo campo
        'pethome_mascota_observaciones_sanidad', // Nuevo campo
        'pethome_mascota_chip', // Nuevo campo
        'pethome_mascota_collar_identificacion', // Nuevo campo
        'pethome_mascota_observaciones_seguridad', // Nuevo campo
    ];

    foreach ( $meta_keys as $key ) {
        $guarda_data[str_replace('pethome_', '', $key)] = get_post_meta( $post_id, $key, true );
    }

    // Asegurar que los valores numéricos sean floats
    $guarda_data['guarda_tarifa_base'] = (float) $guarda_data['guarda_tarifa_base'];
    $guarda_data['reserva_cargos']     = (float) ($guarda_data['reserva_cargos'] ?? 0);
    $guarda_data['reserva_entrega']    = (float) ($guarda_data['reserva_entrega'] ?? 0);
    $guarda_data['reserva_saldo_final']= (float) ($guarda_data['reserva_saldo_final'] ?? 0);

    // Mapear nombres de keys para compatibilidad con el front-end si es necesario
    $guarda_data['cliente_nombre_reserva']   = $guarda_data['cliente_nombre'];
    $guarda_data['cliente_dni_reserva']      = $guarda_data['cliente_dni'];
    $guarda_data['cliente_email_reserva']    = $guarda_data['cliente_email'];
    $guarda_data['cliente_telefono_reserva'] = $guarda_data['cliente_telefono'];
    $guarda_data['booking_product_or_service'] = $guarda_data['reserva_servicio'];
}

$guarda_imagen_url = '';
if ( isset($guarda_data['guarda_imagen_id']) && $guarda_data['guarda_imagen_id'] ) {
    $guarda_imagen_url = wp_get_attachment_image_url( $guarda_data['guarda_imagen_id'], 'thumbnail' );
}

// Valores por defecto para nuevos posts (si no se están editando)
$default_values = [
    'guarda_nombre' => '',
    'guarda_ubicacion' => '',
    'guarda_latitud' => '',
    'guarda_longitud' => '',
    'guarda_tarifa_base' => '',
    'guarda_descripcion' => '',
    'guarda_imagen_id' => '',
    'cliente_nombre_reserva' => '',
    'cliente_dni_reserva' => '',
    'cliente_email_reserva' => '',
    'cliente_telefono_reserva' => '',
    'reserva_observaciones' => '',
    'reserva_cuidador_asignado' => '',
    'reserva_cargos' => '0',
    'reserva_entrega' => '0', // Inicialmente 0, se calcula después
    'reserva_saldo_final' => '0', // Inicialmente 0, se calcula después
    'calendario_fechas' => '',
    'hora_ingreso_reserva' => '10:00',
    'hora_egreso_reserva' => '18:00',
    'booking_product_or_service' => '',
    'mascota_nombre' => '',
    'mascota_tipo' => '',
    'mascota_raza' => '',
    'mascota_edad' => '',
    'mascota_peso' => '',
    'mascota_sexo' => '',
    'mascota_castrada' => '',
    'mascota_enfermedades' => '',
    'mascota_medicamentos' => '',
    'mascota_alergias' => '',
    'mascota_sociable_perros' => '',
    'mascota_sociable_gatos' => '',
    'mascota_sociable_ninios' => '',
    'mascota_agresivo_personas' => '',
    'mascota_agresivo_otros_animales' => '',
    'mascota_observaciones_sociabilidad' => '',
    'mascota_vacunas_completas' => '',
    'mascota_desparasitado' => '',
    'mascota_antipulgas' => '',
    'mascota_veterinario_nombre' => '',
    'mascota_veterinario_telefono' => '',
    'mascota_observaciones_sanidad' => '',
    'mascota_chip' => '',
    'mascota_collar_identificacion' => '',
    'mascota_observaciones_seguridad' => '',
];

$guarda_data = array_merge($default_values, $guarda_data);

/*──────────── HTML DEL FORMULARIO ────────────*/
?>
<div class="wrap pethome-admin-wrap">
    <h1 style="color:#5e4365;"><?php echo $post_id ? __('Editar Guarda', 'pethomehoney-plugin') : __('Agregar Nueva Guarda', 'pethomehoney-plugin'); ?></h1>

    <form method="post" action="" id="pethome-guarda-form">
        <?php wp_nonce_field('pethome_guarda_save_details', 'pethome_guarda_nonce'); ?>
        <input type="hidden" name="action" value="save_pethome_guarda_data">
        <?php if ($post_id): ?>
            <input type="hidden" name="post_id" value="<?php echo esc_attr($post_id); ?>">
        <?php endif; ?>

        <div class="pethome-section">
            <h2 style="color:#5e4365;"><?php _e('Datos Generales del Cuidador', 'pethomehoney-plugin'); ?></h2>
            <div class="pethome-details-grid">
                <div>
                    <label for="pethome_guarda_nombre"><?php _e('Nombre Completo', 'pethomehoney-plugin'); ?></label>
                    <input type="text" id="pethome_guarda_nombre" name="pethome_guarda_nombre" value="<?php echo esc_attr($guarda_data['guarda_nombre']); ?>" placeholder="<?php esc_attr_e('Nombre del cuidador', 'pethomehoney-plugin'); ?>" required>
                </div>
                <div>
                    <label for="pethome_guarda_ubicacion"><?php _e('Ubicación (Dirección/Ciudad)', 'pethomehoney-plugin'); ?></label>
                    <input type="text" id="pethome_guarda_ubicacion" name="pethome_guarda_ubicacion" value="<?php echo esc_attr($guarda_data['guarda_ubicacion']); ?>" placeholder="<?php esc_attr_e('Ej: Buenos Aires, CABA', 'pethomehoney-plugin'); ?>">
                </div>
                <div>
                    <label for="pethome_guarda_latitud"><?php _e('Latitud', 'pethomehoney-plugin'); ?></label>
                    <input type="text" id="pethome_guarda_latitud" name="pethome_guarda_latitud" value="<?php echo esc_attr($guarda_data['guarda_latitud']); ?>" placeholder="<?php esc_attr_e('Ej: -34.6037', 'pethomehoney-plugin'); ?>">
                </div>
                <div>
                    <label for="pethome_guarda_longitud"><?php _e('Longitud', 'pethomehoney-plugin'); ?></label>
                    <input type="text" id="pethome_guarda_longitud" name="pethome_guarda_longitud" value="<?php echo esc_attr($guarda_data['guarda_longitud']); ?>" placeholder="<?php esc_attr_e('Ej: -58.3816', 'pethomehoney-plugin'); ?>">
                </div>
                <div>
                    <label for="pethome_guarda_tarifa_base"><?php _e('Tarifa Base por Día', 'pethomehoney-plugin'); ?></label>
                    <input type="number" step="0.01" id="pethome_guarda_tarifa_base" name="pethome_guarda_tarifa_base" value="<?php echo esc_attr($guarda_data['guarda_tarifa_base']); ?>" placeholder="0.00" min="0">
                </div>
                <div class="item-imagen">
                    <label for="pethome_guarda_imagen_id"><?php _e('Imagen del Cuidador', 'pethomehoney-plugin'); ?></label>
                    <input type="hidden" id="pethome_guarda_imagen_id" name="pethome_guarda_imagen_id" value="<?php echo esc_attr($guarda_data['guarda_imagen_id']); ?>">
                    <button type="button" class="button media-button" data-target="pethome_guarda_imagen_id" data-preview="pethome_guarda_imagen_preview"><?php _e('Subir/Seleccionar Imagen', 'pethomehoney-plugin'); ?></button>
                    <div id="pethome_guarda_imagen_preview" class="preview-container">
                        <?php if ( $guarda_imagen_url ) : ?>
                            <img src="<?php echo esc_url( $guarda_imagen_url ); ?>" style="max-width:150px; height:auto;">
                        <?php endif; ?>
                    </div>
                </div>
                <div style="grid-column: 1 / -1;">
                    <label for="pethome_guarda_descripcion"><?php _e('Descripción Detallada', 'pethomehoney-plugin'); ?></label>
                    <textarea id="pethome_guarda_descripcion" name="pethome_guarda_descripcion" rows="5" placeholder="<?php esc_attr_e('Experiencia, servicios adicionales, etc.', 'pethomehoney-plugin'); ?>"><?php echo esc_textarea($guarda_data['guarda_descripcion']); ?></textarea>
                </div>
            </div>
        </div>

        <div class="pethome-section">
            <h2 style="color:#5e4365;"><?php _e('Datos del Cliente', 'pethomehoney-plugin'); ?></h2>
            <div class="pethome-details-grid">
                <div>
                    <label for="cliente_nombre_reserva"><?php _e('Nombre Completo', 'pethomehoney-plugin'); ?></label>
                    <input type="text" id="cliente_nombre_reserva" name="pethome_cliente_nombre" value="<?php echo esc_attr($guarda_data['cliente_nombre_reserva']); ?>" placeholder="<?php esc_attr_e('Nombre completo del cliente', 'pethomehoney-plugin'); ?>">
                </div>
                <div>
                    <label for="cliente_dni_reserva"><?php _e('DNI', 'pethomehoney-plugin'); ?></label>
                    <input type="text" id="cliente_dni_reserva" name="pethome_cliente_dni" value="<?php echo esc_attr($guarda_data['cliente_dni_reserva']); ?>" placeholder="<?php esc_attr_e('DNI del cliente (opcional)', 'pethomehoney-plugin'); ?>">
                </div>
                <div>
                    <label for="cliente_email_reserva"><?php _e('Email', 'pethomehoney-plugin'); ?></label>
                    <input type="email" id="cliente_email_reserva" name="pethome_cliente_email" value="<?php echo esc_attr($guarda_data['cliente_email_reserva']); ?>" placeholder="<?php esc_attr_e('Email del cliente', 'pethomehoney-plugin'); ?>">
                </div>
                <div>
                    <label for="cliente_telefono_reserva"><?php _e('Teléfono / WhatsApp', 'pethomehoney-plugin'); ?></label>
                    <div class="phone-input-group">
                        <input type="text" id="cliente_telefono_reserva" name="pethome_cliente_telefono" value="<?php echo esc_attr($guarda_data['cliente_telefono_reserva']); ?>" placeholder="<?php esc_attr_e('+54 9 11...', 'pethomehoney-plugin'); ?>">
                        <button type="button" class="button phone-keypad-btn">🔢</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="pethome-section">
            <h2 style="color:#5e4365;"><?php _e('Datos de la Mascota', 'pethomehoney-plugin'); ?></h2>
            <div class="pethome-details-grid">
                <div>
                    <label for="pethome_mascota_nombre"><?php _e('Nombre de la Mascota', 'pethomehoney-plugin'); ?></label>
                    <input type="text" id="pethome_mascota_nombre" name="pethome_mascota_nombre" value="<?php echo esc_attr($guarda_data['mascota_nombre']); ?>" placeholder="<?php esc_attr_e('Ej: Rufo', 'pethomehoney-plugin'); ?>">
                </div>
                <div>
                    <label for="pethome_mascota_tipo"><?php _e('Tipo de Mascota', 'pethomehoney-plugin'); ?></label>
                    <select id="pethome_mascota_tipo" name="pethome_mascota_tipo">
                        <option value=""><?php _e('Seleccionar tipo', 'pethomehoney-plugin'); ?></option>
                        <?php foreach ( $tipos_mascota as $tipo ) : ?>
                            <option value="<?php echo esc_attr( sanitize_title( $tipo['nombre'] ) ); ?>" <?php selected( $guarda_data['mascota_tipo'], sanitize_title( $tipo['nombre'] ) ); ?>>
                                <?php echo esc_html( $tipo['nombre'] ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="pethome_mascota_raza"><?php _e('Raza', 'pethomehoney-plugin'); ?></label>
                    <select id="pethome_mascota_raza" name="pethome_mascota_raza">
                        <option value=""><?php _e('Seleccionar raza', 'pethomehoney-plugin'); ?></option>
                        <?php
                        // Si hay un tipo de mascota seleccionado al cargar, precargar las razas correspondientes.
                        if ( isset( $guarda_data['mascota_tipo'] ) && ! empty( $guarda_data['mascota_tipo'] ) && isset( $razas_por_tipo[sanitize_title( $guarda_data['mascota_tipo'] )] ) ) {
                            foreach ( $razas_por_tipo[sanitize_title( $guarda_data['mascota_tipo'] )] as $raza ) {
                                echo '<option value="' . esc_attr( $raza ) . '" ' . selected( $guarda_data['mascota_raza'], $raza, false ) . '>' . esc_html( $raza ) . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
                <div>
                    <label for="pethome_mascota_edad"><?php _e('Edad', 'pethomehoney-plugin'); ?></label>
                    <input type="text" id="pethome_mascota_edad" name="pethome_mascota_edad" value="<?php echo esc_attr($guarda_data['mascota_edad']); ?>" placeholder="<?php esc_attr_e('Ej: 3 años', 'pethomehoney-plugin'); ?>">
                </div>
                <div>
                    <label for="pethome_mascota_peso"><?php _e('Peso (kg)', 'pethomehoney-plugin'); ?></label>
                    <input type="number" step="0.1" id="pethome_mascota_peso" name="pethome_mascota_peso" value="<?php echo esc_attr($guarda_data['mascota_peso']); ?>" placeholder="<?php esc_attr_e('Ej: 15.5', 'pethomehoney-plugin'); ?>" min="0">
                </div>
                <div>
                    <label for="pethome_mascota_sexo"><?php _e('Sexo', 'pethomehoney-plugin'); ?></label>
                    <select id="pethome_mascota_sexo" name="pethome_mascota_sexo">
                        <option value=""><?php _e('Seleccionar', 'pethomehoney-plugin'); ?></option>
                        <option value="macho" <?php selected($guarda_data['mascota_sexo'], 'macho'); ?>><?php _e('Macho', 'pethomehoney-plugin'); ?></option>
                        <option value="hembra" <?php selected($guarda_data['mascota_sexo'], 'hembra'); ?>><?php _e('Hembra', 'pethomehoney-plugin'); ?></option>
                    </select>
                </div>
                <div>
                    <label for="pethome_mascota_castrada"><?php _e('¿Está Castrada?', 'pethomehoney-plugin'); ?></label>
                    <select id="pethome_mascota_castrada" name="pethome_mascota_castrada">
                        <option value=""><?php _e('Seleccionar', 'pethomehoney-plugin'); ?></option>
                        <option value="si" <?php selected($guarda_data['mascota_castrada'], 'si'); ?>><?php _e('Sí', 'pethomehoney-plugin'); ?></option>
                        <option value="no" <?php selected($guarda_data['mascota_castrada'], 'no'); ?>><?php _e('No', 'pethomehoney-plugin'); ?></option>
                    </select>
                </div>
                <div style="grid-column: 1 / -1;">
                    <label for="reserva_observaciones"><?php _e('Observaciones de la Reserva', 'pethomehoney-plugin'); ?></label>
                    <textarea id="reserva_observaciones" name="pethome_reserva_observaciones" rows="3" placeholder="<?php esc_attr_e('Notas adicionales sobre la reserva de la mascota', 'pethomehoney-plugin'); ?>"><?php echo esc_textarea($guarda_data['reserva_observaciones']); ?></textarea>
                </div>
                <div>
                    <label for="reserva_cuidador_asignado"><?php _e('Cuidador Asignado', 'pethomehoney-plugin'); ?></label>
                    <input type="text" id="reserva_cuidador_asignado" name="pethome_reserva_cuidador_asignado" value="<?php echo esc_attr($guarda_data['reserva_cuidador_asignado']); ?>" placeholder="<?php esc_attr_e('Nombre del cuidador asignado a esta mascota', 'pethomehoney-plugin'); ?>">
                </div>
            </div>
        </div>

        <div class="pethome-section">
            <h2 style="color:#5e4365;"><?php _e('Sociabilidad', 'pethomehoney-plugin'); ?></h2>
            <div class="pethome-details-grid">
                <div>
                    <label for="pethome_mascota_sociable_perros"><?php _e('Sociable con Perros', 'pethomehoney-plugin'); ?></label>
                    <select id="pethome_mascota_sociable_perros" name="pethome_mascota_sociable_perros">
                        <option value=""><?php _e('Seleccionar', 'pethomehoney-plugin'); ?></option>
                        <option value="si" <?php selected($guarda_data['mascota_sociable_perros'], 'si'); ?>><?php _e('Sí', 'pethomehoney-plugin'); ?></option>
                        <option value="no" <?php selected($guarda_data['mascota_sociable_perros'], 'no'); ?>><?php _e('No', 'pethomehoney-plugin'); ?></option>
                        <option value="a_veces" <?php selected($guarda_data['mascota_sociable_perros'], 'a_veces'); ?>><?php _e('A veces', 'pethomehoney-plugin'); ?></option>
                    </select>
                </div>
                <div>
                    <label for="pethome_mascota_sociable_gatos"><?php _e('Sociable con Gatos', 'pethomehoney-plugin'); ?></label>
                    <select id="pethome_mascota_sociable_gatos" name="pethome_mascota_sociable_gatos">
                        <option value=""><?php _e('Seleccionar', 'pethomehoney-plugin'); ?></option>
                        <option value="si" <?php selected($guarda_data['mascota_sociable_gatos'], 'si'); ?>><?php _e('Sí', 'pethomehoney-plugin'); ?></option>
                        <option value="no" <?php selected($guarda_data['mascota_sociable_gatos'], 'no'); ?>><?php _e('No', 'pethomehoney-plugin'); ?></option>
                        <option value="a_veces" <?php selected($guarda_data['mascota_sociable_gatos'], 'a_veces'); ?>><?php _e('A veces', 'pethomehoney-plugin'); ?></option>
                    </select>
                </div>
                <div>
                    <label for="pethome_mascota_sociable_ninios"><?php _e('Sociable con Niños', 'pethomehoney-plugin'); ?></label>
                    <select id="pethome_mascota_sociable_ninios" name="pethome_mascota_sociable_ninios">
                        <option value=""><?php _e('Seleccionar', 'pethomehoney-plugin'); ?></option>
                        <option value="si" <?php selected($guarda_data['mascota_sociable_ninios'], 'si'); ?>><?php _e('Sí', 'pethomehoney-plugin'); ?></option>
                        <option value="no" <?php selected($guarda_data['mascota_sociable_ninios'], 'no'); ?>><?php _e('No', 'pethomehoney-plugin'); ?></option>
                        <option value="a_veces" <?php selected($guarda_data['mascota_sociable_ninios'], 'a_veces'); ?>><?php _e('A veces', 'pethomehoney-plugin'); ?></option>
                    </select>
                </div>
                <div>
                    <label for="pethome_mascota_agresivo_personas"><?php _e('Agresivo con Personas', 'pethomehoney-plugin'); ?></label>
                    <select id="pethome_mascota_agresivo_personas" name="pethome_mascota_agresivo_personas">
                        <option value=""><?php _e('Seleccionar', 'pethomehoney-plugin'); ?></option>
                        <option value="si" <?php selected($guarda_data['mascota_agresivo_personas'], 'si'); ?>><?php _e('Sí', 'pethomehoney-plugin'); ?></option>
                        <option value="no" <?php selected($guarda_data['mascota_agresivo_personas'], 'no'); ?>><?php _e('No', 'pethomehoney-plugin'); ?></option>
                    </select>
                </div>
                <div>
                    <label for="pethome_mascota_agresivo_otros_animales"><?php _e('Agresivo con Otros Animales', 'pethomehoney-plugin'); ?></label>
                    <select id="pethome_mascota_agresivo_otros_animales" name="pethome_mascota_agresivo_otros_animales">
                        <option value=""><?php _e('Seleccionar', 'pethomehoney-plugin'); ?></option>
                        <option value="si" <?php selected($guarda_data['mascota_agresivo_otros_animales'], 'si'); ?>><?php _e('Sí', 'pethomehoney-plugin'); ?></option>
                        <option value="no" <?php selected($guarda_data['mascota_agresivo_otros_animales'], 'no'); ?>><?php _e('No', 'pethomehoney-plugin'); ?></option>
                    </select>
                </div>
                <div style="grid-column: 1 / -1;">
                    <label for="pethome_mascota_observaciones_sociabilidad"><?php _e('Observaciones de Sociabilidad', 'pethomehoney-plugin'); ?></label>
                    <textarea id="pethome_mascota_observaciones_sociabilidad" name="pethome_mascota_observaciones_sociabilidad" rows="3" placeholder="<?php esc_attr_e('Comportamiento, hábitos, miedos, etc.', 'pethomehoney-plugin'); ?>"><?php echo esc_textarea($guarda_data['mascota_observaciones_sociabilidad']); ?></textarea>
                </div>
            </div>
        </div>

        <div class="pethome-section">
            <h2 style="color:#5e4365;"><?php _e('Sanidad', 'pethomehoney-plugin'); ?></h2>
            <div class="pethome-details-grid">
                <div>
                    <label for="pethome_mascota_enfermedades"><?php _e('Enfermedades/Condiciones Médicas', 'pethomehoney-plugin'); ?></label>
                    <input type="text" id="pethome_mascota_enfermedades" name="pethome_mascota_enfermedades" value="<?php echo esc_attr($guarda_data['mascota_enfermedades']); ?>" placeholder="<?php esc_attr_e('Separar por comas', 'pethomehoney-plugin'); ?>">
                </div>
                <div>
                    <label for="pethome_mascota_medicamentos"><?php _e('Medicamentos (cuáles y cada cuánto)', 'pethomehoney-plugin'); ?></label>
                    <input type="text" id="pethome_mascota_medicamentos" name="pethome_mascota_medicamentos" value="<?php echo esc_attr($guarda_data['mascota_medicamentos']); ?>" placeholder="<?php esc_attr_e('Ej: Insulina (1 vez al día)', 'pethomehoney-plugin'); ?>">
                </div>
                <div>
                    <label for="pethome_mascota_alergias"><?php _e('Alergias (alimentos, medicamentos, etc.)', 'pethomehoney-plugin'); ?></label>
                    <input type="text" id="pethome_mascota_alergias" name="pethome_mascota_alergias" value="<?php echo esc_attr($guarda_data['mascota_alergias']); ?>" placeholder="<?php esc_attr_e('Separar por comas', 'pethomehoney-plugin'); ?>">
                </div>
                <div>
                    <label for="pethome_mascota_vacunas_completas"><?php _e('Vacunas Completas', 'pethomehoney-plugin'); ?></label>
                    <select id="pethome_mascota_vacunas_completas" name="pethome_mascota_vacunas_completas">
                        <option value=""><?php _e('Seleccionar', 'pethomehoney-plugin'); ?></option>
                        <option value="si" <?php selected($guarda_data['mascota_vacunas_completas'], 'si'); ?>><?php _e('Sí', 'pethomehoney-plugin'); ?></option>
                        <option value="no" <?php selected($guarda_data['mascota_vacunas_completas'], 'no'); ?>><?php _e('No', 'pethomehoney-plugin'); ?></option>
                    </select>
                </div>
                <div>
                    <label for="pethome_mascota_desparasitado"><?php _e('Desparasitado', 'pethomehoney-plugin'); ?></label>
                    <select id="pethome_mascota_desparasitado" name="pethome_mascota_desparasitado">
                        <option value=""><?php _e('Seleccionar', 'pethomehoney-plugin'); ?></option>
                        <option value="si" <?php selected($guarda_data['mascota_desparasitado'], 'si'); ?>><?php _e('Sí', 'pethomehoney-plugin'); ?></option>
                        <option value="no" <?php selected($guarda_data['mascota_desparasitado'], 'no'); ?>><?php _e('No', 'pethomehoney-plugin'); ?></option>
                    </select>
                </div>
                <div>
                    <label for="pethome_mascota_antipulgas"><?php _e('Antipulgas/Garrapatas', 'pethomehoney-plugin'); ?></label>
                    <select id="pethome_mascota_antipulgas" name="pethome_mascota_antipulgas">
                        <option value=""><?php _e('Seleccionar', 'pethomehoney-plugin'); ?></option>
                        <option value="si" <?php selected($guarda_data['mascota_antipulgas'], 'si'); ?>><?php _e('Sí', 'pethomehoney-plugin'); ?></option>
                        <option value="no" <?php selected($guarda_data['mascota_antipulgas'], 'no'); ?>><?php _e('No', 'pethomehoney-plugin'); ?></option>
                    </select>
                </div>
                <div>
                    <label for="pethome_mascota_veterinario_nombre"><?php _e('Veterinario (Nombre)', 'pethomehoney-plugin'); ?></label>
                    <input type="text" id="pethome_mascota_veterinario_nombre" name="pethome_mascota_veterinario_nombre" value="<?php echo esc_attr($guarda_data['mascota_veterinario_nombre']); ?>" placeholder="<?php esc_attr_e('Nombre del veterinario', 'pethomehoney-plugin'); ?>">
                </div>
                <div>
                    <label for="pethome_mascota_veterinario_telefono"><?php _e('Veterinario (Teléfono)', 'pethomehoney-plugin'); ?></label>
                    <input type="text" id="pethome_mascota_veterinario_telefono" name="pethome_mascota_veterinario_telefono" value="<?php echo esc_attr($guarda_data['mascota_veterinario_telefono']); ?>" placeholder="<?php esc_attr_e('Teléfono del veterinario', 'pethomehoney-plugin'); ?>">
                </div>
                <div style="grid-column: 1 / -1;">
                    <label for="pethome_mascota_observaciones_sanidad"><?php _e('Observaciones de Sanidad', 'pethomehoney-plugin'); ?></label>
                    <textarea id="pethome_mascota_observaciones_sanidad" name="pethome_mascota_observaciones_sanidad" rows="3" placeholder="<?php esc_attr_e('Historial médico, restricciones dietéticas, etc.', 'pethomehoney-plugin'); ?>"><?php echo esc_textarea($guarda_data['mascota_observaciones_sanidad']); ?></textarea>
                </div>
            </div>
        </div>

        <div class="pethome-section">
            <h2 style="color:#5e4365;"><?php _e('Seguridad', 'pethomehoney-plugin'); ?></h2>
            <div class="pethome-details-grid">
                <div>
                    <label for="pethome_mascota_chip"><?php _e('¿Tiene Chip de Identificación?', 'pethomehoney-plugin'); ?></label>
                    <select id="pethome_mascota_chip" name="pethome_mascota_chip">
                        <option value=""><?php _e('Seleccionar', 'pethomehoney-plugin'); ?></option>
                        <option value="si" <?php selected($guarda_data['mascota_chip'], 'si'); ?>><?php _e('Sí', 'pethomehoney-plugin'); ?></option>
                        <option value="no" <?php selected($guarda_data['mascota_chip'], 'no'); ?>><?php _e('No', 'pethomehoney-plugin'); ?></option>
                    </select>
                </div>
                <div>
                    <label for="pethome_mascota_collar_identificacion"><?php _e('¿Usa Collar con Identificación?', 'pethomehoney-plugin'); ?></label>
                    <select id="pethome_mascota_collar_identificacion" name="pethome_mascota_collar_identificacion">
                        <option value=""><?php _e('Seleccionar', 'pethomehoney-plugin'); ?></option>
                        <option value="si" <?php selected($guarda_data['mascota_collar_identificacion'], 'si'); ?>><?php _e('Sí', 'pethomehoney-plugin'); ?></option>
                        <option value="no" <?php selected($guarda_data['mascota_collar_identificacion'], 'no'); ?>><?php _e('No', 'pethomehoney-plugin'); ?></option>
                    </select>
                </div>
                <div style="grid-column: 1 / -1;">
                    <label for="pethome_mascota_observaciones_seguridad"><?php _e('Observaciones de Seguridad', 'pethomehoney-plugin'); ?></label>
                    <textarea id="pethome_mascota_observaciones_seguridad" name="pethome_mascota_observaciones_seguridad" rows="3" placeholder="<?php esc_attr_e('Comportamiento en la calle, manejo de correa, etc.', 'pethomehoney-plugin'); ?>"><?php echo esc_textarea($guarda_data['mascota_observaciones_seguridad']); ?></textarea>
                </div>
            </div>
        </div>

        <div class="pethome-section">
            <h2 style="color:#5e4365;"><?php _e('Guarda', 'pethomehoney-plugin'); ?></h2>

            <div class="pethome-main-booking-grid">
<div class="pethome-calendar-column" style="grid-area: calendar;">
    <label for="calendario_fechas"><?php _e('Seleccionar Fechas', 'pethomehoney-plugin'); ?></label>
    <input type="hidden" id="calendario_fechas" name="pethome_reserva_fechas" value="<?php echo esc_attr( isset($guarda_data['reserva_fechas']) ? $guarda_data['reserva_fechas'] : '' ); ?>">
    <div id="pethome_flatpickr_inline_calendar_container"></div> </div>

                <div class="grid-item-input" style="grid-area: hora-ingreso;">
                    <label for="hora_ingreso_reserva"><?php _e('Hora de Ingreso', 'pethomehoney-plugin'); ?></label>
                    <input type="time" id="hora_ingreso_reserva" name="pethome_reserva_hora_ingreso" value="<?php echo esc_attr($guarda_data['hora_ingreso_reserva']); ?>">
                </div>

                <div class="grid-item-input" style="grid-area: hora-egreso;">
                    <label for="hora_egreso_reserva"><?php _e('Hora de Egreso', 'pethomehoney-plugin'); ?></label>
                    <input type="time" id="hora_egreso_reserva" name="pethome_reserva_hora_egreso" value="<?php echo esc_attr($guarda_data['hora_egreso_reserva']); ?>">
                </div>

                <div class="grid-item-display days-field" style="grid-area: dias;">
                    <label><?php _e('Días', 'pethomehoney-plugin'); ?></label>
                    <div class="display-value-container">
                        <p id="cantidad_dias_reserva" style="font-weight: bold; font-size: 1.1em;"></p>
                    </div>
                </div>

                <div class="grid-item-select service-product-field" style="grid-area: servicio-producto;">
                    <label for="booking_product_or_service"><?php _e('Seleccionar Servicio/Producto', 'pethomehoney-plugin'); ?></label>
                    <select id="booking_product_or_service" name="pethome_reserva_servicio">
                        <option value=""><?php _e('Seleccionar...', 'pethomehoney-plugin'); ?></option>
                        <optgroup label="<?php _e('Productos de Booking', 'pethomehoney-plugin'); ?>">
                            <?php foreach ( $bookings as $product ) :
                                $product_cost = pethome_get_booking_daily_cost( $product ); ?>
                                <option value="booking_product:<?php echo esc_attr( $product->get_id() ); ?>"
                                        data-cost="<?php echo esc_attr( $product_cost ); ?>"
                                        <?php selected( $guarda_data['booking_product_or_service'], 'booking_product:' . $product->get_id() ); ?>>
                                    <?php echo esc_html( $product->get_name() ); ?> (<?php echo wc_price( $product_cost ); ?>/día)
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
<optgroup label="<?php _e('Servicios Creados', 'pethomehoney-plugin'); ?>">
    <?php foreach ( $servicios_creados as $idx => $servicio ) :
        // Definimos variables seguras para evitar warnings si las claves faltan
        $servicio_id = isset($servicio['id']) ? $servicio['id'] : $idx; // Usamos el índice si no hay ID
        $servicio_nombre = isset($servicio['nombre']) ? $servicio['nombre'] : __('Servicio sin nombre', 'pethomehoney-plugin');
        $servicio_precio_base = isset($servicio['precio_base']) ? (float) $servicio['precio_base'] : 0.0; // Aseguramos que sea float
    ?>
        <option value="custom_service:<?php echo esc_attr( $idx ); ?>"
                data-cost="<?php echo esc_attr( $servicio_precio_base ); ?>"
                <?php selected( $guarda_data['booking_product_or_service'], 'custom_service:' . $idx ); ?>>
            <?php echo esc_html( $servicio_nombre ); ?> (<?php echo wc_price( $servicio_precio_base ); ?>/día)
        </option>
    <?php endforeach; ?>
</optgroup>
                    </select>
                </div>

                <div class="grid-item-display" style="grid-area: costo-diario;">
                    <label><?php _e('Costo Diario', 'pethomehoney-plugin'); ?></label>
                    <div class="display-value-container">
                        <p id="costo_diario_reserva" style="font-weight: bold; font-size: 1.1em;">0</p>
                    </div>
                </div>

                <div class="grid-item-display" style="grid-area: sub-total;">
                    <label><?php _e('Sub Total', 'pethomehoney-plugin'); ?></label>
                    <div class="display-value-container">
                        <p id="sub_total_reserva" style="font-weight: bold; font-size: 1.1em;"></p>
                    </div>
                </div>

                <div class="grid-item-input" style="grid-area: cargos;">
                    <label for="reserva_cargos"><?php _e('Cargos Adicionales', 'pethomehoney-plugin'); ?></label>
                    <input type="number" id="reserva_cargos" name="pethome_reserva_cargos" step="0.01" min="0" value="<?php echo esc_attr($guarda_data['reserva_cargos']); ?>">
                </div>

                <div class="grid-item-display" style="grid-area: entrega;">
                    <label><?php _e('Entrega (10%)', 'pethomehoney-plugin'); ?></label>
                    <div class="display-value-container">
                        <p id="reserva_entrega" style="font-weight: bold; font-size: 1.1em;"></p>
                    </div>
                </div>

                <div class="grid-item-display" style="grid-area: precio-total;">
                    <label><?php _e('Precio Total', 'pethomehoney-plugin'); ?></label>
                    <div class="display-value-container">
                        <p id="precio_total_reserva" style="font-weight: bold; font-size: 1.3em; color: #4CAF50;">0</p>
                    </div>
                </div>

                <div class="grid-item-display" style="grid-area: saldo-final;">
                    <label><?php _e('Saldo (90%)', 'pethomehoney-plugin'); ?></label>
                    <div class="display-value-container">
                        <p id="reserva_saldo_final" style="font-weight: bold; font-size: 1.3em; color: #DC3545;"></p>
                    </div>
                </div>
                 <div class="grid-item-input grid-item-fechas-seleccionadas" style="grid-area: fechas-display;">
                    <label for="fechas_seleccionadas_texto"><?php _e('Fechas Seleccionadas', 'pethomehoney-plugin'); ?></label>
                    <input type="text" id="fechas_seleccionadas_texto" readonly placeholder="<?php esc_attr_e('Fechas de la reserva', 'pethomehoney-plugin'); ?>">
                </div>
            </div>
        </div>

        <button type="submit" name="guardar_guarda" class="button button-primary button-large" style="margin-top: 20px;"><?php echo $post_id ? __('Actualizar Guarda', 'pethomehoney-plugin') : __('Agregar Guarda', 'pethomehoney-plugin'); ?></button>
    </form>
</div>

<div id="phone-modal-wrap" class="phone-modal-wrap">
    <div class="modal-content">
        <input type="text" id="phone-modal-input" placeholder="+54 9 11...">
        <div class="keypad-grid">
            <button class="keypad-btn">1</button>
            <button class="keypad-btn">2</button>
            <button class="keypad-btn">3</button>
            <button class="keypad-btn">4</button>
            <button class="keypad-btn">5</button>
            <button class="keypad-btn">6</button>
            <button class="keypad-btn">7</button>
            <button class="keypad-btn">8</button>
            <button class="keypad-btn">9</button>
            <button class="keypad-btn">C</button>
            <button class="keypad-btn">0</button>
            <button class="keypad-btn">⌫</button>
        </div>
        <button id="phone-modal-ok" class="button button-primary">OK</button>
    </div>
</div>

<style>
    /* Estilos Generales del Admin */
    .pethome-admin-wrap {
        padding: 20px;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        margin-top: 20px;
    }

    .pethome-section {
        background: #f9f9f9;
        padding: 20px;
        border-radius: 16px;
        border: 1px solid #eee;
        margin-bottom: 30px;
    }

    .pethome-details-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }

    .pethome-details-grid div {
        display: flex;
        flex-direction: column;
    }

    .pethome-details-grid label,
    .pethome-main-booking-grid label /* Global label style for the grid */
    {
        font-weight: bold;
        margin-bottom: 5px;
        color: #666;
    }

    .pethome-details-grid input[type="text"],
    .pethome-details-grid input[type="email"],
    .pethome-details-grid input[type="number"],
    .pethome-details-grid select,
    .pethome-details-grid textarea,
    .pethome-main-booking-grid input[type="text"],
    .pethome-main-booking-grid input[type="time"],
    .pethome-main-booking-grid input[type="number"],
    .pethome-main-booking-grid select
    {
        padding: 8px 12px;
        border: 1px solid #ccc;
        border-radius: 4px;
        font-size: 1em;
        box-sizing: border-box;
        width: 100%;
    }

    .phone-input-group {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .phone-input-group input {
        flex-grow: 1;
    }

    .phone-keypad-btn {
        padding: 6px 10px;
        font-size: 1.2em;
        cursor: pointer;
    }

    /* Flatpickr */
    .flatpickr-calendar {
        border: 1px solid #ccc;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        border-radius: 8px;
        padding: 10px;
        width: 100%;
        box-sizing: border-box;
    }

    /* Modal Teléfono */
    .phone-modal-wrap {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 1000;
    }

    .phone-modal-wrap .modal-content {
        background: #fff;
        padding: 25px;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        text-align: center;
        width: 300px;
        max-width: 90%;
    }

    #phone-modal-input {
        width: calc(100% - 20px);
        padding: 10px;
        margin-bottom: 20px;
        font-size: 1.2em;
        text-align: center;
        border: 1px solid #ddd;
        border-radius: 5px;
    }

    .keypad-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
        margin-bottom: 20px;
    }

    .keypad-btn {
        padding: 15px;
        font-size: 1.5em;
        background: #f0f0f0;
        border: 1px solid #ddd;
        border-radius: 5px;
        cursor: pointer;
        transition: background 0.2s;
    }

    .keypad-btn:hover {
        background: #e0e0e0;
    }

    /* Media Selector */
    .item-imagen .preview-container {
        margin-top: 10px;
        border: 1px solid #eee;
        padding: 5px;
        min-height: 100px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .item-imagen .preview-container img {
        max-width: 100%;
        max-height: 200px;
        height: auto;
    }

    /* --- ESTILOS ESPECÍFICOS DE LA SECCIÓN DE GUARDA --- */

    .pethome-main-booking-grid {
        display: grid;
        /* Columna 1: Calendario (flexible) */
        /* Columna 2 y 3: Dos columnas iguales para los campos de datos */
        grid-template-columns: minmax(250px, 1fr) repeat(2, minmax(150px, 0.7fr));
        grid-template-areas:
            "calendar hora-ingreso hora-egreso"
            "calendar dias servicio-producto"
            "calendar costo-diario sub-total"
            "calendar cargos entrega"
            "calendar precio-total saldo-final"
            "fechas-display fechas-display fechas-display"; /* <-- NUEVA FILA PARA EL CAMPO DE FECHAS SELECCIONADAS */
        column-gap: 20px; /* Mantén el espacio entre columnas */
        row-gap: 10px;    /* Reduce el espacio vertical entre filas */
        align-items: start;
    }

    /* Define el área para el nuevo div de fechas */
    .grid-item-fechas-seleccionadas {
        grid-area: fechas-display; /* Asigna el área de la grilla */
    }

    /* Ajustes para el input de fechas seleccionadas */
    .grid-item-fechas-seleccionadas label {
        margin-bottom: 5px; /* Espacio entre el label y el input */
        font-weight: bold;
    }
    .grid-item-fechas-seleccionadas input[type="text"] {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #ccc;
        border-radius: 8px;
        background-color: #f9f9f9; /* Un color para destacarlo */
        box-sizing: border-box; /* Incluye padding y border en el ancho total */
        min-height: 40px; /* Asegura un buen tamaño */
    }

    /* Estilo para los contenedores de los elementos dentro del grid */
    .pethome-main-booking-grid > div {
        display: flex;
        flex-direction: column;
    }

    /* Asignar áreas de la grilla (redundante con style inline pero útil para referencia) */
    .pethome-calendar-column { grid-area: calendar; }
    /* ... (resto de las definiciones de grid-area si son necesarias y no inline) ... */

    /* Contenedor de valor para "Días", "Costo Diario", "Sub Total", "Entrega", "Precio Total", "Saldo Final" */
    .display-value-container {
        padding: 8px 12px;
        border: 1px solid #ccc;
        border-radius: 4px;
        background-color: #f0f0f0; /* Color de fondo similar a un input deshabilitado */
        box-sizing: border-box;
        display: flex; /* Para centrar el texto dentro */
        align-items: center; /* Centrar verticalmente */
        justify-content: center; /* Centrar horizontalmente */
        height: 38px; /* Altura fija para que coincida con inputs */
    }

    /* Alineación de texto dentro del contenedor de valor */
    .display-value-container p {
        margin: 0; /* Elimina el margen predeterminado del párrafo */
        padding: 0; /* Elimina el padding predeterminado del párrafo */
        flex-grow: 1; /* Permite que el párrafo ocupe el espacio disponible */
        text-align: center; /* Centra el texto por defecto */
    }

    /* Alinear a la derecha los valores numéricos si es necesario */
    .grid-item-display[style*="grid-area: dias"] .display-value-container p,
    .grid-item-display[style*="grid-area: costo-diario"] .display-value-container p,
    .grid-item-display[style*="grid-area: sub-total"] .display-value-container p,
    .grid-item-display[style*="grid-area: entrega"] .display-value-container p,
    .grid-item-display[style*="grid-area: precio-total"] .display-value-container p,
    .grid-item-display[style*="grid-area: saldo-final"] .display-value-container p {
        text-align: right; /* Alinea a la derecha para números */
    }

    /* Es crucial para que el calendario se ajuste verticalmente */
    .flatpickr-calendar {
        height: auto;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }
    .flatpickr-months, .flatpickr-weeks, .flatpickr-days {
        flex-shrink: 0;
    }
    .flatpickr-days {
        flex-grow: 1;
    }

    /* Media Queries para responsividad */
    @media (max-width: 992px) { /* Tabletas y pantallas más pequeñas */
        .pethome-main-booking-grid {
            grid-template-columns: 1fr 1fr; /* Dos columnas */
            grid-template-areas:
                "calendar calendar"
                "hora-ingreso hora-egreso"
                "dias servicio-producto"
                "costo-diario sub-total"
                "cargos entrega"
                "precio-total saldo-final"
                "fechas-display fechas-display"; /* <-- NUEVA FILA EN EL MEDIA QUERY */
            gap: 20px;
            justify-items: stretch;
        }
    }

    @media (max-width: 768px) { /* Dispositivos móviles */
        .pethome-main-booking-grid {
            grid-template-columns: 1fr; /* Una columna */
            grid-template-areas:
                "calendar"
                "hora-ingreso"
                "hora-egreso"
                "dias"
                "servicio-producto"
                "costo-diario"
                "sub-total"
                "cargos"
                "entrega"
                "precio-total"
                "saldo-final"
                "fechas-display"; /* <-- NUEVA FILA EN EL MEDIA QUERY DE MÓVIL */
            gap: 20px;
        }
        /* Asegurar que los elementos llenen el ancho completo en una sola columna */
        .grid-item-input,
        .grid-item-select,
        .grid-item-display {
            width: 100%;
            margin-left: 0;
            margin-right: 0;
            justify-self: stretch;
        }
        /* En móviles, centrar el texto de los valores para una mejor legibilidad */
        .display-value-container p {
            text-align: center;
        }
    }
</style>

<script>
    jQuery(document).ready(function($) {
        /*────────── Variables de la Guarda ──────────*/
        // No es necesario que tarifaBaseGuarda sea una constante global si solo se usa en calculateCosts.
        // Se puede leer directamente del input dentro de la función o pasar como argumento.

        /*────────── Elementos del Formulario de Reserva ──────────*/
        const calendarioFechasInput    = document.getElementById('calendario_fechas');
        const horaIngresoInput         = document.getElementById('hora_ingreso_reserva');
        const horaEgresoInput          = document.getElementById('hora_egreso_reserva');
        const servicioProductoSelect   = document.getElementById('booking_product_or_service');
        const cantidadDiasDisplay      = document.getElementById('cantidad_dias_reserva');
        const costoDiarioDisplay       = document.getElementById('costo_diario_reserva');
        const subTotalDisplay          = document.getElementById('sub_total_reserva');
        const reservaCargosInput       = document.getElementById('reserva_cargos');
        const reservaEntregaDisplay    = document.getElementById('reserva_entrega');
        const precioTotalDisplay       = document.getElementById('precio_total_reserva');
        const reservaSaldoFinalDisplay = document.getElementById('reserva_saldo_final');
        const fechasSeleccionadasTexto = document.getElementById('fechas_seleccionadas_texto'); // Nuevo elemento

        let selectedDates = []; // <--- ¡DEJÁ ESTA SOLA DECLARACIÓN!
        let flatpickrInstance;

// ... (tu código JavaScript que tengas antes de la inicialización de Flatpickr) ...

/*────────── Inicialización de Flatpickr ──────────*/

// Conseguimos la referencia al NUEVO div contenedor donde Flatpickr se va a dibujar
const inlineCalendarContainer = document.getElementById('pethome_flatpickr_inline_calendar_container');

// Aca NO va más: let selectedDates = []; // <--- ¡ESTA LÍNEA DEBE SER ELIMINADA!

// Solo inicializamos Flatpickr si encontramos ambos elementos, para evitar errores
if (calendarioFechasInput && inlineCalendarContainer) {
    flatpickrInstance = flatpickr(inlineCalendarContainer, { // <<< ¡ESTE ES EL CAMBIO FUNDAMENTAL! Apuntamos al DIV.
        mode: "range",
        dateFormat: "Y-m-d",
        minDate: "today",
        locale: "es", // Asegurate que el paquete de idioma 'es' de Flatpickr esté cargado (vía wp_enqueue_script)
        inline: true, // ¡ESTO ES LO QUE HACE QUE EL CALENDARIO ESTÉ SIEMPRE VISIBLE!

        // onReady se encarga de cargar las fechas existentes en el calendario inline
        onReady: function(selectedDatesArr, dateStr, instance) {
            // Si el input de fechas ya tiene un valor (por ejemplo, si estás editando una guarda)
            if (calendarioFechasInput.value) {
                // Asumimos que el formato es "AAAA-MM-DD a AAAA-MM-DD" o una sola fecha
                const initialDates = calendarioFechasInput.value.split(' a ');
                if (initialDates.length > 0) {
                    instance.setDate(initialDates, true); // el 'true' hace que también se dispare el onChange
                }
            }
            selectedDates = selectedDatesArr; // Actualizamos la variable global/local de selectedDates
            updateCalculations();
            updateFechasSeleccionadasTexto();
        },

        // onChange actualiza el input de texto a medida que se seleccionan las fechas en el calendario inline
        onChange: function(selectedDatesArr, dateStr, instance) {
            selectedDates = selectedDatesArr;
            calendarioFechasInput.value = dateStr; // Actualizamos el valor del input de texto "oculto"
            updateCalculations(); // Llama a tu función de cálculos
            updateFechasSeleccionadasTexto(); // Llama a tu función para actualizar el texto de fechas seleccionadas
        },

        // onClose es menos crítico para un calendario inline, pero lo dejamos por si acaso
        onClose: function(selectedDatesArr, dateStr, instance) {
            selectedDates = selectedDatesArr;
            updateCalculations();
            updateFechasSeleccionadasTexto();
        }
    });

    // Como el calendario ya está siempre visible con `inline: true`,
    // no necesitás un evento 'focus' en el input para "abrirlo".
    // Si tenías algo como: `calendarioFechasInput.addEventListener('focus', function() { flatpickrInstance.open(); });`
    // lo podés sacar.

} else {
    // Este mensaje en la consola te va a avisar si no encuentra los elementos HTML
    console.error("Error: No se encontró el input 'calendario_fechas' o el contenedor 'pethome_flatpickr_inline_calendar_container'.");
}

        /*────────── Función de Cálculo de Costos ──────────*/
        function updateCalculations() {
            let cantidadDias = 0;
            if (selectedDates.length === 2) {
                const start = selectedDates[0].getTime();
                const end = selectedDates[1].getTime();
                // Calcular días completos, incluyendo el día de inicio y fin si la hora de egreso es posterior a la de ingreso
                cantidadDias = Math.ceil(Math.abs(end - start) / (1000 * 60 * 60 * 24)) + 1; // +1 para incluir el día de ingreso
            } else if (selectedDates.length === 1) {
                cantidadDias = 1; // Un solo día seleccionado
            }
            cantidadDiasDisplay.textContent = cantidadDias > 0 ? cantidadDias : '0';

            const selectedOption = servicioProductoSelect.options[servicioProductoSelect.selectedIndex];
            const costoDiario = parseFloat(selectedOption ? selectedOption.dataset.cost : '0');
            costoDiarioDisplay.textContent = formatCurrency(costoDiario);

            const subTotal = costoDiario * cantidadDias;
            subTotalDisplay.textContent = formatCurrency(subTotal);

            const cargosAdicionales = parseFloat(reservaCargosInput.value) || 0;

            const precioTotal = subTotal + cargosAdicionales;
            precioTotalDisplay.textContent = formatCurrency(precioTotal);

            const entrega = precioTotal * 0.10; // 10% del total
            reservaEntregaDisplay.textContent = formatCurrency(entrega);

            const saldoFinal = precioTotal - entrega; // 90% restante
            reservaSaldoFinalDisplay.textContent = formatCurrency(saldoFinal);
        }

        function formatCurrency(value) {
            // Asumiendo que el plugin de WooCommerce ya carga el formato de moneda.
            // Si no, se puede implementar un formato básico:
            return parseFloat(value).toLocaleString('es-AR', { style: 'currency', currency: 'ARS' });
        }

        function updateFechasSeleccionadasTexto() {
            if (selectedDates.length === 2) {
                const startDate = flatpickr.formatDate(selectedDates[0], "d/m/Y");
                const endDate = flatpickr.formatDate(selectedDates[1], "d/m/Y");
                fechasSeleccionadasTexto.value = `${startDate} - ${endDate}`;
            } else if (selectedDates.length === 1) {
                const singleDate = flatpickr.formatDate(selectedDates[0], "d/m/Y");
                fechasSeleccionadasTexto.value = singleDate;
            } else {
                fechasSeleccionadasTexto.value = '';
            }
        }

        /*────────── Event Listeners para Recalcular Costos ──────────*/
        servicioProductoSelect.addEventListener('change', updateCalculations);
        reservaCargosInput.addEventListener('input', updateCalculations);
        horaIngresoInput.addEventListener('change', updateCalculations); // Si las horas afectan los días
        horaEgresoInput.addEventListener('change', updateCalculations);   // Si las horas afectan los días

        // Inicializar cálculo de costos al cargar la página
        updateCalculations();

        /*────────── Lógica de Razas Dinámicas ──────────*/
        const mascotaTipoSelect = document.getElementById('pethome_mascota_tipo');
        const mascotaRazaSelect = document.getElementById('pethome_mascota_raza');
        // Asegúrate de que pethomehoney_vars esté definido globalmente por wp_localize_script
        const razasPorTipo = <?php echo json_encode($razas_por_tipo); ?>;

        function updateRazas() {
            const selectedTipo = mascotaTipoSelect.value;
            mascotaRazaSelect.innerHTML = '<option value=""><?php _e("Seleccionar raza", "pethomehoney-plugin"); ?></option>';

            if (selectedTipo && razasPorTipo[selectedTipo]) {
                razasPorTipo[selectedTipo].forEach(raza => {
                    const option = document.createElement('option');
                    option.value = raza;
                    option.textContent = raza;
                    mascotaRazaSelect.appendChild(option);
                });
            }
            // Si se está editando y la raza actual coincide con las nuevas opciones, la re-selecciona
            const currentRaza = '<?php echo esc_js($guarda_data['mascota_raza']); ?>';
            if (currentRaza) {
                mascotaRazaSelect.value = currentRaza;
            }
        }

        mascotaTipoSelect.addEventListener('change', updateRazas);

        // Llamar a updateRazas al cargar la página para precargar si hay un tipo seleccionado
        updateRazas();

        /*────────── Modal Numérico para Teléfono y Formateo de DNI ──────────*/
        const phoneModalWrap = document.getElementById('phone-modal-wrap');
        const phoneModalInput = document.getElementById('phone-modal-input');
        const phoneKeypadBtns = document.querySelectorAll('.keypad-btn');
        const phoneModalOkBtn = document.getElementById('phone-modal-ok');
        const phoneMainInput = document.getElementById('cliente_telefono_reserva');
        const phoneKeypadOpenBtn = document.querySelector('.phone-keypad-btn');

        let activePhoneInput = null; // Para saber qué input activó el modal

        phoneKeypadOpenBtn.addEventListener('click', function() {
            activePhoneInput = phoneMainInput;
            phoneModalInput.value = activePhoneInput.value;
            phoneModalWrap.style.display = 'flex';
        });

        phoneKeypadBtns.forEach(button => {
            button.addEventListener('click', function() {
                const value = this.textContent;
                if (value === 'C') {
                    phoneModalInput.value = '';
                } else if (value === '⌫') {
                    phoneModalInput.value = phoneModalInput.value.slice(0, -1);
                } else {
                    phoneModalInput.value += value;
                }
            });
        });

        phoneModalOkBtn.addEventListener('click', function() {
            if (activePhoneInput) {
                activePhoneInput.value = phoneModalInput.value;
            }
            phoneModalWrap.style.display = 'none';
        });

        phoneModalWrap.addEventListener('click', function(event) {
            if (event.target === phoneModalWrap) {
                phoneModalWrap.style.display = 'none';
            }
        });

        // Formateo de DNI
        const dniInput = document.getElementById('cliente_dni_reserva');
        if (dniInput) {
            dniInput.addEventListener('input', function() {
                // Eliminar cualquier caracter que no sea dígito
                let dniValue = this.value.replace(/\D/g, '');
                // Limitar a 8 dígitos (formato DNI argentino)
                dniValue = dniValue.substring(0, 8);
                // Si hay 8 dígitos, intentar formatear (ej: XX.XXX.XXX)
                if (dniValue.length > 2 && dniValue.length <= 5) {
                    dniValue = dniValue.substring(0,2) + '.' + dniValue.substring(2);
                } else if (dniValue.length > 5) {
                     dniValue = dniValue.substring(0,2) + '.' + dniValue.substring(2,5) + '.' + dniValue.substring(5);
                }
                this.value = dniValue;
            });
        }

        // Formato inicial para el teléfono principal (cliente_telefono_reserva)
        // Solo aplica el formato al perder el foco para no interferir con la entrada
        const phoneMain = document.getElementById('cliente_telefono_reserva');
        if (phoneMain) {
            phoneMain.addEventListener('input', function() {
                // Limpiar caracteres no numéricos, excepto el '+'
                let cleaned = this.value.replace(/[^0-9+]/g, '');

                // Asegurar que solo haya un '+' al principio
                if (cleaned.startsWith('+') && cleaned.indexOf('+', 1) !== -1) {
                    cleaned = '+' + cleaned.substring(1).replace(/\+/g, '');
                } else if (!cleaned.startsWith('+') && cleaned.length > 0) {
                    cleaned = '+' + cleaned; // Añadir '+' si no está al inicio
                }

                this.value = cleaned;
            });

            phoneMain.addEventListener('focus', function() {
                // Si el campo está vacío, sugerir el inicio del número de Argentina
                if (this.value === '') {
                    this.value = '+549';
                }
            });
            phoneMain.addEventListener('blur', function() {
                // Si el campo queda solo con '+54' o '+549' después de perder el foco, lo limpiamos
                if (this.value === '+54' || this.value === '+549') {
                    this.value = '';
                }
            });
        }

        /*────────── Media selector de imagen para Guarda ──────────*/
        $('.media-button').on('click', function(e){
            e.preventDefault();
            var target  = $('#' + $(this).data('target'));
            var preview = $('#' + $(this).data('preview'));
            var frame   = wp.media({
                title:'Seleccionar imagen',
                button:{text:'Usar esta imagen'},
                multiple:false,
                library:{type:'image'}
            });

            frame.on('select', function(){
                var attachment = frame.state().get('selection').first().toJSON();
                target.val(attachment.id);
                preview.html('<img src="' + attachment.url + '" style="max-width:150px; height:auto;">');
            });

            frame.open();
        });

    });
</script>
