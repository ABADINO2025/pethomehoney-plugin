<?php
/**
 * pethome_guardas_agregar.php  ·  Formulario completo “Agregar Guarda”
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// 0) Tipos de Mascota dinámicos
$tipos_mascota = get_option( 'pethome_tipos_mascotas', [] );
if ( ! is_array( $tipos_mascota ) || empty( $tipos_mascota ) ) {
    $tipos_mascota = [
        [ 'tipo' => 'Mestizo', 'recargo' => 0 ],
    ];
}

// 1) Procesar POST
if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['guardar_guarda'] ) ) {
    $fechas = explode( ',', sanitize_text_field( $_POST['calendario_fechas'] ) );
    foreach ( $fechas as $fecha ) {
        $booking_data = [
            'bookable_product_id' => $product_id,
            'customer_id'         => $user_id,
            'start_date'          => $fecha,
            'end_date'            => $fecha,
            'cost'                => $coste_por_dia,
        ];
        wc_create_booking( $booking_data );
    }
}

// 2) Productos Booking
$bookings = wc_get_products( [ 'type' => 'booking', 'limit' => -1 ] );
function pethome_get_booking_daily_cost( WC_Product_Booking $product ) {
    $block_cost = (float) get_post_meta( $product->get_id(), '_wc_booking_block_cost', true );
    $base_cost  = (float) get_post_meta( $product->get_id(), '_wc_booking_cost',       true );
    return $block_cost > 0 ? $block_cost : ( $base_cost > 0 ? $base_cost : 0 );
}

// 3) Servicios Creados
$servicios_raw  = get_option( 'pethome_precios_base', [] );
$servicios_norm = [];
foreach ( $servicios_raw as $idx => $srv ) {
    if ( empty( $srv['servicio'] ) || ! isset( $srv['precio'] ) ) continue;
    $servicios_norm[] = [
        'id'     => 'svc_' . $idx,
        'nombre' => $srv['servicio'],
        'precio' => (float) $srv['precio'],
    ];
}

// 4) Mapa PHP→JS
$price_map = [];
foreach ( $bookings as $b ) {
    $price_map[ 'bk_' . $b->get_id() ] = pethome_get_booking_daily_cost( $b );
}
foreach ( $servicios_norm as $s ) {
    $price_map[ $s['id'] ] = $s['precio'];
}
?>

<!-- ═════════════ Recursos Flatpickr ═════════════ -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
  <input type="hidden" name="action" value="pethome_guardas_save">
  <?php wp_nonce_field( 'pethome_guardas_form', 'pethome_guardas_nonce' ); ?>

  <!-- ╔═════════════════════╗  GUARDA  ╚═════════════════════╝ -->
  <div class="section-block guarda-section">
    <h2>Guarda</h2>
    <div class="guarda-subsections">
      <!-- IZQUIERDA 30%: calendario inline -->
      <div class="sub-left">
        <div class="fechas-calendar"></div>
      </div>
      <!-- DERECHA 70% -->
      <div class="sub-right">

        <!-- 1) Horas -->
        <div class="campo-horas">
          <div>
            <label for="hora_ingreso">Hora Ingreso</label>
            <input type="text" id="hora_ingreso" name="hora_ingreso" required>
          </div>
          <div>
            <label for="hora_egreso">Hora Egreso</label>
            <input type="text" id="hora_egreso" name="hora_egreso" required>
          </div>
        </div>

        <!-- 2) Producto / Servicio + Días -->
        <div class="fila-producto-dias">
          <div class="campo-producto">
            <label for="producto_reserva">Producto / Servicio</label>
            <select id="producto_reserva" name="producto_reserva" required>
              <option value="">— Seleccionar —</option>
              <?php if ( $bookings ) : ?>
                <optgroup label="Productos Booking">
                  <?php foreach ( $bookings as $b ) :
                    $daily = pethome_get_booking_daily_cost( $b ); ?>
                    <option value="<?php echo 'bk_' . $b->get_id(); ?>">
                      <?php echo esc_html( $b->get_name() ); ?> — <?php echo wc_price( $daily ); ?>
                    </option>
                  <?php endforeach; ?>
                </optgroup>
              <?php endif; ?>
              <?php if ( $servicios_norm ) : ?>
                <optgroup label="Servicios Creados">
                  <?php foreach ( $servicios_norm as $s ) : ?>
                    <option value="<?php echo esc_attr( $s['id'] ); ?>">
                      <?php echo esc_html( $s['nombre'] ); ?> — <?php echo wc_price( $s['precio'] ); ?>
                    </option>
                  <?php endforeach; ?>
                </optgroup>
              <?php endif; ?>
            </select>
          </div>
          <div class="campo-dias">
            <label for="dias">Días</label>
            <input type="number" id="dias" name="dias" readonly>
          </div>
        </div>

        <!-- 3) Precios Diario / Total -->
        <div class="campo-precios">
          <div class="precio-item">
            <label for="precio_diario">Precio Diario</label>
            <input type="text" id="precio_diario" readonly>
          </div>
          <div class="precio-item">
            <label for="precio_total">Precio Total</label>
            <input type="text" id="precio_total" readonly>
          </div>
        </div>

        <!-- 4) Fechas ordenadas -->
        <div class="fechas-input">
          <label for="calendario_fechas">Fechas</label>
          <input
            type="text"
            id="calendario_fechas"
            name="calendario_fechas"
            readonly
            required
          >
        </div>
      </div>
    </div>
  </div>

  <!-- ╔═════════════════════╗   DATOS CLIENTE   ╚═════════════════════╝ -->
  <div class="section-block cliente-section">
    <h2>Datos Cliente</h2>
    <div class="cliente-grid">
      <div class="item-nombre">
        <label for="cliente_nombre">Nombre</label>
        <input type="text" id="cliente_nombre" name="cliente_nombre" required>
      </div>
      <div class="item-apellido">
        <label for="cliente_apellido">Apellido</label>
        <input type="text" id="cliente_apellido" name="cliente_apellido" required>
      </div>
      <div class="item-dni">
        <label for="cliente_dni">DNI</label>
        <input type="text" id="cliente_dni" name="cliente_dni" required>
      </div>
      <div class="item-alias">
        <label for="cliente_alias_bancario">Alias Bancario/CBU</label>
        <input type="text" id="cliente_alias_bancario" name="cliente_alias_bancario">
      </div>
      <div class="item-calle">
        <label for="cliente_calle">Calle</label>
        <input type="text" id="cliente_calle" name="cliente_calle">
      </div>
      <div class="item-numero">
        <label for="cliente_numero">Número</label>
        <input type="text" id="cliente_numero" name="cliente_numero">
      </div>
      <div class="item-barrio">
        <label for="cliente_barrio">Barrio</label>
        <input type="text" id="cliente_barrio" name="cliente_barrio">
      </div>
      <div class="item-email email-field">
        <label for="cliente_email">Email</label>
        <input
          type="email"
          id="cliente_email"
          name="cliente_email"
          required
          pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$"
          placeholder="usuario@dominio.com"
          oninvalid="this.setCustomValidity('Introduce un email válido, ej usuario@dominio.com')"
          oninput="this.setCustomValidity('')"
        >
      </div>
      <div class="item-telefono">
        <label for="cliente_telefono">Teléfono</label>
        <input
          type="text"
          id="cliente_telefono"
          name="cliente_telefono"
          readonly
          placeholder="+54 xxxx-xxxxxxx"
        >
      </div>
    </div>
  </div>

  <!-- ╔═════════════════════╗    DATOS MASCOTA  ╚═════════════════════╝ -->
  <div class="section-block mascota-section">
    <h2>Datos de la Mascota</h2>
    <div class="mascota-grid">
      <!-- 1) Imagen Mascota -->
      <div class="item-imagen">
        <label>Imagen Mascota</label>
        <input type="hidden" name="imagen_mascota" id="imagen_mascota_input">
        <button type="button"
                class="media-button button button-primary"
                data-target="imagen_mascota_input"
                data-preview="imagen_mascota_preview">
          <span class="dashicons dashicons-format-image"></span>
          Seleccionar imagen
        </button>
        <div class="preview-container">
          <img id="imagen_mascota_preview" src="" alt="Preview Mascota">
        </div>
      </div>
      <!-- 2) Nombre Mascota -->
      <div class="item-nombre">
        <label for="nombre_mascota">Nombre Mascota</label>
        <input type="text" name="nombre_mascota" id="nombre_mascota" required>
      </div>
      <!-- 3) Tipo Mascota -->
      <div class="item-tipo">
        <label for="tipo_mascota">Tipo Mascota</label>
        <select name="tipo_mascota" id="tipo_mascota" required>
          <?php foreach ( $tipos_mascota as $item ) :
            $nombre = is_array( $item ) && isset( $item['tipo'] ) ? $item['tipo'] : strval( $item );
          ?>
            <option value="<?php echo esc_attr( $nombre ); ?>"
              <?php selected( $nombre, 'Mestizo' ); ?>>
              <?php echo esc_html( $nombre ); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <!-- 4) Raza -->
      <div class="item-raza">
        <label for="raza">Raza</label>
        <select name="raza" id="raza" required>
          <option value="mestizo" selected>Mestizo</option>
          <option value="caniche">Caniche</option>
          <option value="salchicha">Salchicha</option>
          <option value="labrador">Labrador</option>
        </select>
      </div>
      <!-- 5) Sexo -->
      <div class="item-sexo">
        <label for="sexo">Sexo</label>
        <select name="sexo" id="sexo">
          <option value="hembra">Hembra</option>
          <option value="macho">Macho</option>
        </select>
      </div>
      <!-- 6) Edad (Años) -->
      <div class="item-edadA">
        <label for="edad_anios">Edad (Años)</label>
        <input type="number" name="edad_anios" id="edad_anios" min="0">
      </div>
      <!-- 7) Edad (Meses) -->
      <div class="item-edadM">
        <label for="edad_meses">Edad (Meses)</label>
        <input type="number" name="edad_meses" id="edad_meses" min="0" max="11">
      </div>
      <!-- 8) Tamaño -->
      <div class="item-tamano">
        <label for="tamanio">Tamaño</label>
        <select name="tamanio" id="tamanio">
          <option value="chico">Chico</option>
          <option value="mediano">Mediano</option>
          <option value="grande">Grande</option>
        </select>
      </div>
      <!-- 9) Cuidador -->
      <div class="item-cuidador">
        <label for="cuidador_asignado">Cuidador</label>
        <select name="cuidador_asignado" id="cuidador_asignado" required>
          <option value="">— Seleccionar —</option>
          <?php
            $cuidadores = get_option( 'pethome_cuidadores', [] );
            if ( is_array( $cuidadores ) ) {
              foreach ( $cuidadores as $cuidador ) {
                $label = trim( ( $cuidador['nombre'] ?? '' ) . ' ' . ( $cuidador['apellido'] ?? '' ) );
                echo '<option value="' . esc_attr( $label ) . '">' . esc_html( $label ) . '</option>';
              }
            }
          ?>
        </select>
      </div>
    </div>
  </div>

  <!-- ╔═════════════════════╗  SOCIABILIDAD  ╚═════════════════════╝ -->
  <div class="section-block sociabilidad-section">
    <h2>🧠 Sociabilidad</h2>
    <div class="pethome-grid grid-2">
      <div>
        <label for="sociable_ninios">¿Sociable con niños?</label>
        <select id="sociable_ninios" name="sociable_ninios" data-p-select>
          <option value="si" data-p="0">Es Sociable</option>
          <option value="no" data-p="20" selected>No es Sociable (+20 %)</option>
        </select>
      </div>
      <div>
        <label for="sociable_mascotas">¿Sociable con mascotas?</label>
        <select id="sociable_mascotas" name="sociable_mascotas" data-p-select>
          <option value="si" data-p="0">Es Sociable</option>
          <option value="no" data-p="20" selected>No es Sociable (+20 %)</option>
        </select>
      </div>
    </div>
  </div>

  <!-- ╔═════════════════════╗  SANIDAD  ╚═════════════════════╝ -->
  <div class="section-block sanidad-section">
    <h2>💉 Sanidad</h2>
    <div class="pethome-grid grid-3">
      <div>
        <label for="vacunacion">Vacunación</label>
        <select id="vacunacion" name="vacunacion" data-p-select>
          <option value="vacunado" data-p="0">Vacunado</option>
          <option value="sin_vacuna" data-p="5" selected>Sin Vacunar (+5 %)</option>
        </select>
      </div>
      <div>
        <label for="castracion">Castración</label>
        <select id="castracion" name="castracion" data-p-select>
          <option value="castrado" data-p="0">Castrado</option>
          <option value="no_castrado" data-p="2" selected>No Castrado (+2 %)</option>
        </select>
      </div>
      <div>
        <label for="heces">Heces</label>
        <select id="heces" name="heces">
          <option value="afuera">Sólo Afuera</option>
          <option value="adentro">Adentro</option>
          <option value="mixed" selected>Indistintamente</option>
        </select>
      </div>
    </div>
  </div>

  <!-- ╔═════════════════════╗  SEGURIDAD  ╚═════════════════════╝ -->
  <div class="section-block seguridad-section">
    <h2>🛡 Seguridad</h2>
    <div class="pethome-grid grid-2">
      <div>
        <label for="pechera">Pechera</label>
        <select id="pechera" name="pechera" data-p-select>
          <option value="con" data-p="0">Con Pechera</option>
          <option value="sin" data-p="20" selected>Sin Pechera (+20 %)</option>
        </select>
      </div>
      <div>
        <label for="seguro">Seguro</label>
        <select id="seguro" name="seguro" data-p-select>
          <option value="con_cobertura" data-p="-10">Tengo cobertura de salud (-10 %)</option>
          <option value="sin_cobertura" data-p="0" selected>No tengo cobertura</option>
        </select>
      </div>
    </div>
  </div>

  <!-- ═══════════════ Modal Teléfono ═══════════════ -->
  <div id="telefono-modal"
       style="display:none;
              position:fixed;
              top:0; left:0;
              width:100%; height:100%;
              background:rgba(0,0,0,0.6);
              display:flex;
              align-items:center;
              justify-content:center;
              z-index:10000;">
    <div class="modal-content"
         style="background:#fff;
                padding:20px;
                border-radius:8px;
                text-align:center;">
      <input id="telefono_modal_input"
             type="text"
             style="font-size:24px;
                    width:80%;
                    padding:8px;
                    margin-bottom:16px;
                    text-align:center;">
      <div class="keypad"
           style="display:grid;
                  grid-template-columns:repeat(3,60px);
                  gap:10px;
                  justify-content:center;">
        <?php for($i=1;$i<=9;$i++) : ?>
          <button type="button" class="key-btn"><?php echo $i; ?></button>
        <?php endfor; ?>
        <button type="button" class="key-btn">⌫</button>
        <button type="button" class="key-btn">0</button>
        <button type="button" class="key-btn">C</button>
      </div>
      <button type="button" id="telefono_modal_ok"
              style="margin-top:16px;
                     padding:10px 20px;
                     font-size:18px;
                     border:none;
                     background:#5e4365;
                     color:#fff;
                     border-radius:6px;
                     cursor:pointer;">
        OK
      </button>
    </div>
  </div>

  <p><input type="submit" name="guardar_guarda" class="button-primary" value="Guardar Guarda"></p>
</form>

<!-- ══════════════ ESTILOS ══════════════ -->
<style>
.section-block        { background:#f9f9f9; border:2px solid #ccc; border-radius:16px; padding:20px; margin:30px 0; }
.section-block h2     { color:#5e4365; margin-bottom:20px; }
.button-primary       { background:#5e4365; color:#fff; border:none; border-radius:6px; padding:10px 20px; font-weight:bold; cursor:pointer; }
.button-primary:hover { background:#7a5d8d; }

/* Guarda */
.guarda-subsections { display:flex; gap:24px; }
.sub-left { flex:0 0 30%; }
.sub-left .fechas-calendar { width:100%; }
.sub-right { flex:1; display:flex; flex-direction:column; gap:12px; }

/* Horas */
.campo-horas { display:grid; grid-template-columns:repeat(2,1fr); gap:12px; }
.campo-horas label { display:block; margin-bottom:4px; color:#5e4365; font-weight:bold; }
.campo-horas input { width:100%; padding:6px; background:#f0f0f1; border:1px solid #ccc; border-radius:6px; }

/* Producto / Servicio + Días */
.fila-producto-dias { display:flex; gap:12px; margin-bottom:12px; }
.campo-producto { flex:0 0 80%; display:flex; flex-direction:column; }
.campo-producto label { margin-bottom:4px; color:#5e4365; font-weight:bold; }
.campo-producto select {
  width:100%; padding:6px 8px; background:#f0f0f1; border:1px solid #ccc; border-radius:6px; box-sizing:border-box; height:40px;
}
.campo-dias { flex:0 0 20%; display:flex; flex-direction:column; }
.campo-dias label { margin-bottom:4px; color:#5e4365; font-weight:bold; }
.campo-dias input {
  width:100%; padding:6px 8px; background:#f0f0f1; border:1px solid #ccc; border-radius:6px; box-sizing:border-box; height:40px;
}

/* Precios */
.campo-precios { display:grid; grid-template-columns:repeat(2,1fr); gap:12px; }
.precio-item label { display:block; margin-bottom:4px; color:#5e4365; font-weight:bold; }
.precio-item input { width:100%; padding:6px; background:#f0f0f1; border:1px solid #ccc; border-radius:6px; }

/* Fechas */
.fechas-input { margin-top:16px; }
.fechas-input label { display:block; margin-bottom:4px; color:#5e4365; font-weight:bold; }
.fechas-input input { width:100%; padding:6px; background:#f0f0f1; border:1px solid #ccc; border-radius:6px; }

/* Cliente */
.cliente-grid {
  display:grid;
  grid-template-columns:1fr 1fr 1fr;
  grid-template-areas:
    "nombre apellido dni"
    "alias calle numero"
    "barrio email telefono";
  gap:16px;
}
.item-nombre   { grid-area:nombre; }
.item-apellido { grid-area:apellido; }
.item-dni      { grid-area:dni; }
.item-alias    { grid-area:alias; }
.item-calle    { grid-area:calle; }
.item-numero   { grid-area:numero; }
.item-barrio   { grid-area:barrio; }
.item-email    { grid-area:email; }
.item-telefono { grid-area:telefono; }
.cliente-grid label { display:block; margin-bottom:4px; color:#5e4365; font-weight:bold; }
.cliente-grid input,
.cliente-grid select { width:100%; padding:6px; background:#f0f0f1; border:1px solid #ccc; border-radius:6px; }

/* Email fija */
.email-field { position:relative; }
.email-field input { text-align:center; }
.email-field::before {
  content:"@"; position:absolute; top:50%; left:5%; transform:translate(-50%,-50%); color:#aaa; pointer-events:none;
}

/* Mascota */
.mascota-section { margin-top:30px; }
.mascota-section h2 { color:#5e4365; margin-bottom:16px; }
.mascota-grid {
  display:grid;
  grid-template-columns:180px 1fr 1fr;
  grid-template-areas:
    "imagen nombre tipo"
    "imagen raza sexo"
    "imagen edadA edadM"
    "imagen tamano cuidador";
  gap:16px;
}
.item-imagen   { grid-area:imagen; }
.item-nombre   { grid-area:nombre; }
.item-tipo     { grid-area:tipo; }
.item-raza     { grid-area:raza; }
.item-sexo     { grid-area:sexo; }
.item-edadA    { grid-area:edadA; }
.item-edadM    { grid-area:edadM; }
.item-tamano   { grid-area:tamano; }
.item-cuidador { grid-area:cuidador; }
.mascota-grid label { display:block; margin-bottom:4px; color:#5e4365; font-weight:bold; }
.mascota-grid input,
.mascota-grid select {
  width:100%; padding:6px; background:#f0f0f1; border:1px solid #ccc; border-radius:6px; box-sizing:border-box;
}
.media-button.button { margin-bottom:10px; border-radius:6px; }
.preview-container {
  width:100%; min-height:200px; background:#f0f0f1; border:1px solid #ccc; border-radius:15px;
  display:flex; align-items:center; justify-content:center; overflow:hidden; box-shadow:0 4px 8px rgba(0,0,0,0.2);
}
.preview-container img { width:100%; height:auto; display:none; border-radius:15px; }

/* Sociabilidad */
.pethome-grid.grid-2 { display:grid; grid-template-columns:repeat(2,1fr); gap:16px; }
.pethome-grid.grid-2 > div { display:flex; flex-direction:column; }
.pethome-grid.grid-2 label { display:block; margin-bottom:4px; color:#5e4365; font-weight:bold; }
.pethome-grid.grid-2 select { width:100%; padding:6px; background:#f0f0f1; border:1px solid #ccc; border-radius:6px; }

/* Sanidad */
.pethome-grid.grid-3 { display:grid; grid-template-columns:repeat(3,1fr); gap:16px; }
.pethome-grid.grid-3 > div { display:flex; flex-direction:column; }
.pethome-grid.grid-3 label { display:block; margin-bottom:4px; color:#5e4365; font-weight:bold; }
.pethome-grid.grid-3 select { width:100%; padding:6px; background:#f0f0f1; border:1px solid #ccc; border-radius:6px; }

/* Seguridad */
.pethome-grid.grid-2:last-of-type > div { display:flex; flex-direction:column; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function(){
  // Flatpickr fechas
  const calendar = flatpickr('.fechas-calendar',{
    inline:true, mode:'multiple', dateFormat:'d/m/Y', locale:'es',
    onChange(selected, _, inst){
      selected.sort((a,b)=>a-b);
      const str = selected.map(d=>inst.formatDate(d,'d/m/Y')).join(', ');
      document.getElementById('calendario_fechas').value = str;
      document.getElementById('dias').value = selected.length;
      recalcCosts();
    }
  });

  // Timepickers
  flatpickr('#hora_ingreso',{ enableTime:true, noCalendar:true, time_24hr:true, dateFormat:'H:i' });
  flatpickr('#hora_egreso' ,{ enableTime:true, noCalendar:true, time_24hr:true, dateFormat:'H:i' });

  // Recalc costos
  const prices = <?php echo wp_json_encode( $price_map ); ?>;
  document.getElementById('producto_reserva').addEventListener('change', recalcCosts);
  function recalcCosts(){
    const days  = calendar.selectedDates.length;
    const daily = parseFloat(prices[ document.getElementById('producto_reserva').value ] || 0);
    document.getElementById('precio_diario').value = daily ? daily.toFixed(2) : '';
    document.getElementById('precio_total').value = (daily && days) ? (daily*days).toFixed(2) : '';
  }
  recalcCosts();

  // Auto‐formato DNI
  document.getElementById('cliente_dni').addEventListener('input', function(){
    let v = this.value.replace(/\D/g,'').slice(0,8);
    let rev = v.split('').reverse().join('');
    let grp = rev.match(/.{1,3}/g);
    if(grp) v = grp.join('.').split('').reverse().join('');
    this.value = v;
  });

  // Modal Teléfono
  const phoneMain = document.getElementById('cliente_telefono');
  const phoneModal = document.getElementById('telefono-modal');
  const phoneModalInput = document.getElementById('telefono_modal_input');
  const phoneModalOk    = document.getElementById('telefono_modal_ok');
  const keypadBtns      = phoneModal.querySelectorAll('.key-btn');

  phoneMain.addEventListener('click', e=>{
    e.preventDefault();
    phoneModal.style.display = 'flex';
    phoneModalInput.value = phoneMain.value;
    phoneModalInput.focus();
  });

  keypadBtns.forEach(btn=>{
    btn.addEventListener('click', ()=>{
      const v = btn.textContent;
      if(v==='⌫') phoneModalInput.value = phoneModalInput.value.slice(0,-1);
      else if(v==='C') phoneModalInput.value = '';
      else phoneModalInput.value += v;
      phoneModalInput.focus();
    });
  });

  phoneModalOk.addEventListener('click', ()=>{
    phoneMain.value = phoneModalInput.value;
    phoneModal.style.display = 'none';
  });

  phoneModal.addEventListener('click', ()=>{
    phoneMain.value = phoneModalInput.value;
    phoneModal.style.display = 'none';
  });
  phoneModal.querySelector('.modal-content').addEventListener('click', e=>e.stopPropagation());

  // Media selector mascota
  jQuery(function($){
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
        var att = frame.state().get('selection').first().toJSON();
        target.val(att.url);
        preview.attr('src', att.url).show();
      });
      frame.open();
    });
  });

});
</script>
