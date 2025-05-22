<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// ELIMINAR: function pethome_configuracion_panel() {
// Y ELIMINAR EL CIERRE DE LLAVE AL FINAL DEL ARCHIVO

// 1) Leer opciones existentes
$precios_base    = get_option( 'pethome_precios_base', [] );
$cliente_mensaje = get_option( 'pethome_cliente_mensaje', '' );
$tipos_mascotas  = get_option( 'pethome_tipos_mascotas', [] );
$razas           = get_option( 'pethome_razas', [] );

// Índices de edición por GET
$editando_precio = isset( $_GET['editar'] )        ? intval( $_GET['editar'] )        : -1;
$editando_tipo   = isset( $_GET['editar_tipo'] )   ? intval( $_GET['editar_tipo'] )   : -1;
$editando_raza   = isset( $_GET['editar_raza'] )   ? intval( $_GET['editar_raza'] )   : -1;

// 2) Eliminar servicio
if ( isset( $_GET['borrar_precio'] ) && is_numeric( $_GET['borrar_precio'] ) ) {
    $i = intval( $_GET['borrar_precio'] );
    if ( isset( $precios_base[ $i ] ) ) {
        unset( $precios_base[ $i ] );
        update_option( 'pethome_precios_base', array_values( $precios_base ) );
    }
    wp_safe_redirect( remove_query_arg( [ 'borrar_precio', 'editar' ], menu_page_url( 'pethome_configuracion', false ) ) );
    exit;
}

// 3) Agregar/editar servicios y mensaje
if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['pethome_guardar_configuracion'] ) ) {
    // Agregar nuevo servicio
    if ( ! empty( $_POST['nuevo_servicio'] ) && ! empty( $_POST['nuevo_precio'] ) ) {
        $precios_base[] = [
            'servicio' => sanitize_text_field( $_POST['nuevo_servicio'] ),
            'precio'   => floatval( $_POST['nuevo_precio'] ),
        ];
        update_option( 'pethome_precios_base', $precios_base );
    }
    // Editar servicio existente
    if ( isset( $_POST['editar_servicio_guardar'] ) ) {
        $i = intval( $_POST['indice_edit'] );
        if ( isset( $precios_base[ $i ] ) ) {
            $precios_base[ $i ] = [
                'servicio' => sanitize_text_field( $_POST['servicio_editado'] ),
                'precio'   => floatval( $_POST['precio_editado'] ),
            ];
            update_option( 'pethome_precios_base', $precios_base );
        }
        wp_safe_redirect( remove_query_arg( 'editar', menu_page_url( 'pethome_configuracion', false ) ) );
        exit;
    }
    // Mensaje WhatsApp
    if ( isset( $_POST['cliente_mensaje'] ) ) {
        update_option( 'pethome_cliente_mensaje', sanitize_textarea_field( $_POST['cliente_mensaje'] ) );
    }
}

// 4) Eliminar tipo de mascota
if ( isset( $_GET['borrar_tipo'] ) && is_numeric( $_GET['borrar_tipo'] ) ) {
    $i = intval( $_GET['borrar_tipo'] );
    if ( isset( $tipos_mascotas[ $i ] ) ) {
        unset( $tipos_mascotas[ $i ] );
        update_option( 'pethome_tipos_mascotas', array_values( $tipos_mascotas ) );
    }
    wp_safe_redirect( remove_query_arg( [ 'borrar_tipo', 'editar_tipo' ], menu_page_url( 'pethome_configuracion', false ) ) );
    exit;
}

// 5) Agregar nuevo tipo de mascota
if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['guardar_tipos_mascotas'] ) ) {
    $tipo    = sanitize_text_field( $_POST['tipo_mascota'] );
    $recargo = floatval( $_POST['recargo_mascota'] );
    $tipos_mascotas[] = [ 'tipo' => $tipo, 'recargo' => $recargo ];
    update_option( 'pethome_tipos_mascotas', $tipos_mascotas );
    wp_safe_redirect( menu_page_url( 'pethome_configuracion', false ) );
    exit;
}

// 6) Editar tipo de mascota
if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['editar_tipo_guardar'] ) ) {
    $i = intval( $_POST['indice_tipo'] );
    if ( isset( $tipos_mascotas[ $i ] ) ) {
        $tipos_mascotas[ $i ] = [
            'tipo'    => sanitize_text_field( $_POST['tipo_mascota_edit'] ),
            'recargo' => floatval( $_POST['recargo_mascota_edit'] ),
        ];
        update_option( 'pethome_tipos_mascotas', $tipos_mascotas );
    }
    wp_safe_redirect( remove_query_arg( 'editar_tipo', menu_page_url( 'pethome_configuracion', false ) ) );
    exit;
}

// 7) Eliminar raza
if ( isset( $_GET['borrar_raza'] ) && is_numeric( $_GET['borrar_raza'] ) ) {
    $i = intval( $_GET['borrar_raza'] );
    if ( isset( $razas[ $i ] ) ) {
        unset( $razas[ $i ] );
        update_option( 'pethome_razas', array_values( $razas ) );
    }
    wp_safe_redirect( remove_query_arg( [ 'borrar_raza', 'editar_raza' ], menu_page_url( 'pethome_configuracion', false ) ) );
    exit;
}

// 8) Agregar nueva raza
if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['guardar_razas'] ) ) {
    $raza    = sanitize_text_field( $_POST['raza'] );
    $recargo = floatval( $_POST['recargo_raza'] );
    $razas[] = [ 'raza' => $raza, 'recargo' => $recargo ];
    update_option( 'pethome_razas', $razas );
    wp_safe_redirect( menu_page_url( 'pethome_configuracion', false ) );
    exit;
}

// 9) Editar raza
if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['editar_raza_guardar'] ) ) {
    $i = intval( $_POST['indice_raza'] );
    if ( isset( $razas[ $i ] ) ) {
        $razas[ $i ] = [
            'raza'    => sanitize_text_field( $_POST['raza_edit'] ),
            'recargo' => floatval( $_POST['recargo_raza_edit'] ),
        ];
        update_option( 'pethome_razas', $razas );
    }
    wp_safe_redirect( remove_query_arg( 'editar_raza', menu_page_url( 'pethome_configuracion', false ) ) );
    exit;
}
?>

<div class="wrap" style="margin:30px;">
  <h1 style="color:#5e4365;font-size:32px;text-align:center;">⚙️ <?php _e( 'Configuración General', 'pethomehoney-plugin' ); ?></h1>

  <div style="background:#f9f9f9;padding:20px;border-radius:16px;border:2px solid #ccc;">
    <h2 style="color:#5e4365;">💵 <?php _e( 'Precios Base de Servicios', 'pethomehoney-plugin' ); ?></h2>
    <form method="post">
      <?php wp_nonce_field( 'pethome_guardar_configuracion', 'pethome_guardar_configuracion' ); ?>
      <table class="widefat striped">
        <thead>
          <tr>
             <th><?php _e( 'Servicio', 'pethomehoney-plugin' ); ?></th>
             <th><?php _e( 'Precio', 'pethomehoney-plugin' ); ?></th>
             <th><?php _e( 'Agregar', 'pethomehoney-plugin' ); ?></th>
             <th></th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><input type="text" name="nuevo_servicio" class="regular-text" required placeholder="<?php esc_attr_e( 'Ej: Guardería Diurna', 'pethomehoney-plugin' ); ?>"></td>
            <td><input type="number" step="0.01" name="nuevo_precio" class="regular-text" required placeholder="<?php esc_attr_e( 'Ej: 2500.00', 'pethomehoney-plugin' ); ?>"></td>
            <td><button type="submit" name="pethome_guardar_configuracion" class="button button-primary">➕ <?php _e( 'Agregar', 'pethomehoney-plugin' ); ?></button></td>
            <td></td>
          </tr>
        </tbody>
      </table>
    </form>
    <h3 style="margin-top:30px;">📋 <?php _e( 'Servicios Creados', 'pethomehoney-plugin' ); ?></h3>
    <table class="widefat striped">
      <thead><tr>
          <th><?php _e( 'Servicio', 'pethomehoney-plugin' ); ?></th>
          <th><?php _e( 'Precio', 'pethomehoney-plugin' ); ?></th>
          <th><?php _e( 'Eliminar', 'pethomehoney-plugin' ); ?></th>
          <th><?php _e( 'Editar', 'pethomehoney-plugin' ); ?></th>
      </tr></thead>
      <tbody>
        <?php if ( $precios_base ) : ?>
          <?php foreach ( $precios_base as $idx => $srv ) : ?>
            <?php if ( $editando_precio === $idx ) : ?>
              <tr><form method="post">
                <?php wp_nonce_field( 'pethome_guardar_configuracion', 'pethome_guardar_configuracion' ); ?>
                <td><input type="text" name="servicio_editado" value="<?php echo esc_attr( $srv['servicio'] ); ?>" class="regular-text" required></td>
                <td><input type="number" step="0.01" name="precio_editado" value="<?php echo esc_attr( $srv['precio'] ); ?>" class="regular-text" required></td>
                <td><input type="hidden" name="indice_edit" value="<?php echo $idx; ?>"><button type="submit" name="editar_servicio_guardar" class="button button-primary">💾 <?php _e( 'Guardar', 'pethomehoney-plugin' ); ?></button></td>
                <td><a href="admin.php?page=pethome_configuracion" class="button">❌ <?php _e( 'Cancelar', 'pethomehoney-plugin' ); ?></a></td>
              </form></tr>
            <?php else : ?>
              <tr>
                <td><?php echo esc_html( $srv['servicio'] ); ?></td>
                <td>$<?php echo number_format( $srv['precio'], 2 ); ?></td>
                <td><a href="?page=pethome_configuracion&borrar_precio=<?php echo $idx; ?>" class="button button-small">🗑️</a></td>
                <td><a href="?page=pethome_configuracion&editar=<?php echo $idx; ?>" class="button button-small">✏️</a></td>
              </tr>
            <?php endif; ?>
          <?php endforeach; ?>
        <?php else : ?>
          <tr><td colspan="4"><?php _e( 'No hay servicios creados todavía.', 'pethomehoney-plugin' ); ?></td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <div style="background:#f9f9f9;padding:20px;border-radius:16px;border:2px solid #ccc;margin-top:30px;">
    <h2 style="color:#5e4365;">🐾 <?php _e( 'Tipos de Mascotas', 'pethomehoney-plugin' ); ?></h2>
    <form method="post">
      <?php wp_nonce_field( 'pethome_tipos_action', 'pethome_tipos_nonce' ); ?>
      <table class="widefat striped">
        <thead><tr>
            <th><?php _e( 'Tipo', 'pethomehoney-plugin' ); ?></th>
            <th><?php _e( '% Recargo/Descuento', 'pethomehoney-plugin' ); ?></th>
            <th><?php _e( 'Agregar', 'pethomehoney-plugin' ); ?></th>
            <th></th>
        </tr></thead>
        <tbody>
          <tr>
            <td><input type="text" name="tipo_mascota" class="regular-text" required placeholder="<?php esc_attr_e( 'Ej: Perro', 'pethomehoney-plugin' ); ?>"></td>
            <td><input type="number" step="0.01" name="recargo_mascota" class="small-text" required> %</td>
            <td><button type="submit" name="guardar_tipos_mascotas" class="button button-primary">➕ <?php _e( 'Agregar', 'pethomehoney-plugin' ); ?></button></td>
            <td></td>
          </tr>
        </tbody>
      </table>
    </form>
    <?php if ( $tipos_mascotas ) : ?>
      <h3 style="margin-top:30px;">📋 <?php _e( 'Mascotas Creadas', 'pethomehoney-plugin' ); ?></h3>
      <table class="widefat striped">
        <thead><tr>
            <th><?php _e( 'Tipo', 'pethomehoney-plugin' ); ?></th>
            <th><?php _e( '% Recargo', 'pethomehoney-plugin' ); ?></th>
            <th><?php _e( 'Editar', 'pethomehoney-plugin' ); ?></th>
            <th><?php _e( 'Eliminar', 'pethomehoney-plugin' ); ?></th>
        </tr></thead>
        <tbody>
          <?php foreach ( $tipos_mascotas as $idx => $t ) : ?>
            <?php if ( $editando_tipo === $idx ) : ?>
              <tr><form method="post">
                <?php wp_nonce_field( 'pethome_tipos_action', 'pethome_tipos_nonce' ); ?>
                <td><input type="text" name="tipo_mascota_edit" value="<?php echo esc_attr( $t['tipo'] ); ?>" class="regular-text" required></td>
                <td><input type="number" step="0.01" name="recargo_mascota_edit" value="<?php echo esc_attr( $t['recargo'] ); ?>" class="small-text" required> %</td>
                <td><input type="hidden" name="indice_tipo" value="<?php echo $idx; ?>"><button type="submit" name="editar_tipo_guardar" class="button button-primary">💾 <?php _e( 'Guardar', 'pethomehoney-plugin' ); ?></button></td>
                <td><a href="admin.php?page=pethome_configuracion" class="button">❌ <?php _e( 'Cancelar', 'pethomehoney-plugin' ); ?></a></td>
              </form></tr>
            <?php else : ?>
              <tr>
                <td><?php echo esc_html( $t['tipo'] ); ?></td>
                <td><?php echo esc_html( $t['recargo'] ); ?> %</td>
                <td><a href="?page=pethome_configuracion&editar_tipo=<?php echo $idx; ?>" class="button button-small"><span class="dashicons dashicons-edit"></span></a></td>
                <td><a href="?page=pethome_configuracion&borrar_tipo=<?php echo $idx; ?>" class="button button-small">✕</a></td>
              </tr>
            <?php endif; ?>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

  <div style="background:#f9f9f9;padding:20px;border-radius:16px;border:2px solid #ccc;margin-top:30px;">
    <h2 style="color:#5e4365;">🐶 <?php _e( 'Razas', 'pethomehoney-plugin' ); ?></h2>
    <form method="post">
      <?php wp_nonce_field( 'pethome_razas_action', 'pethome_razas_nonce' ); ?>
      <table class="widefat striped">
        <thead><tr>
            <th><?php _e( 'Raza', 'pethomehoney-plugin' ); ?></th>
            <th><?php _e( '% Recargo/Descuento', 'pethomehoney-plugin' ); ?></th>
            <th><?php _e( 'Agregar', 'pethomehoney-plugin' ); ?></th>
            <th></th>
        </tr></thead>
        <tbody>
          <tr>
            <td><input type="text" name="raza" class="regular-text" required placeholder="<?php esc_attr_e( 'Ej: Labrador', 'pethomehoney-plugin' ); ?>"></td>
            <td><input type="number" step="0.01" name="recargo_raza" class="small-text" required> %</td>
            <td><button type="submit" name="guardar_razas" class="button button-primary">➕ <?php _e( 'Agregar', 'pethomehoney-plugin' ); ?></button></td>
            <td></td>
          </tr>
        </tbody>
      </table>
    </form>
    <?php if ( $razas ) : ?>
      <h3 style="margin-top:30px;">📋 <?php _e( 'Razas Creadas', 'pethomehoney-plugin' ); ?></h3>
      <table class="widefat striped">
        <thead><tr>
            <th><?php _e( 'Raza', 'pethomehoney-plugin' ); ?></th>
            <th><?php _e( '% Recargo', 'pethomehoney-plugin' ); ?></th>
            <th><?php _e( 'Editar', 'pethomehoney-plugin' ); ?></th>
            <th><?php _e( 'Eliminar', 'pethomehoney-plugin' ); ?></th>
        </tr></thead>
        <tbody>
          <?php foreach ( $razas as $idx => $r ) : ?>
            <?php if ( $editando_raza === $idx ) : ?>
              <tr><form method="post">
                <?php wp_nonce_field( 'pethome_razas_action', 'pethome_razas_nonce' ); ?>
                <td><input type="text" name="raza_edit" value="<?php echo esc_attr( $r['raza'] ); ?>" class="regular-text" required></td>
                <td><input type="number" step="0.01" name="recargo_raza_edit" value="<?php echo esc_attr( $r['recargo'] ); ?>" class="small-text" required> %</td>
                <td><input type="hidden" name="indice_raza" value="<?php echo $idx; ?>"><button type="submit" name="editar_raza_guardar" class="button button-primary">💾 <?php _e( 'Guardar', 'pethomehoney-plugin' ); ?></button></td>
                <td><a href="admin.php?page=pethome_configuracion" class="button">❌ <?php _e( 'Cancelar', 'pethomehoney-plugin' ); ?></a></td>
              </form></tr>
            <?php else : ?>
              <tr>
                <td><?php echo esc_html( $r['raza'] ); ?></td>
                <td><?php echo esc_html( $r['recargo'] ); ?> %</td>
                <td><a href="?page=pethome_configuracion&editar_raza=<?php echo $idx; ?>" class="button button-small"><span class="dashicons dashicons-edit"></span></a></td>
                <td><a href="?page=pethome_configuracion&borrar_raza=<?php echo $idx; ?>" class="button button-small">✕</a></td>
              </tr>
            <?php endif; ?>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

  <div style="margin-top:40px;background:#f9f9f9;padding:20px;border-radius:16px;border:2px solid #ccc;">
    <h2 style="color:#5e4365;">📨 <?php _e( 'Personalización del Mensaje WhatsApp', 'pethomehoney-plugin' ); ?></h2>
    <form method="post">
      <?php wp_nonce_field( 'pethome_guardar_configuracion', 'pethome_guardar_configuracion' ); ?>
      <table class="form-table">
        <tr>
          <th><label for="cliente_mensaje"><?php _e( 'Texto personalizado', 'pethomehoney-plugin' ); ?></label></th>
          <td>
            <textarea id="cliente_mensaje" name="cliente_mensaje" rows="5" style="width:100%;" placeholder="<?php esc_attr_e( 'Ej: Nombre: {cliente_nombre}\nDNI: {cliente_dni}', 'pethomehoney-plugin' ); ?>"><?php echo esc_textarea( $cliente_mensaje ); ?></textarea>
          </td>
        </tr>
      </table>
      <p><button type="submit" name="pethome_guardar_configuracion" class="button button-primary">💾 <?php _e( 'Guardar Mensaje', 'pethomehoney-plugin' ); ?></button></p>
    </form>
  </div>
</div>```
