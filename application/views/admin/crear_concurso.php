<?php $this->load->view('layouts/header'); ?>

<h2>Crear Nuevo Concurso</h2>

<form action="<?= base_url('admin/guardar_concurso') ?>" method="post" enctype="multipart/form-data" id="form-concurso">
    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label">Título del Concurso</label>
                <input type="text" name="titulo" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Descripción (máx. 500 caracteres)</label>
                <textarea name="descripcion" class="form-control" rows="3" maxlength="500"></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Subir Arte (PNG ≤5MB)</label>
                <input type="file" name="imagen" class="form-control" accept="image/png">
            </div>
        </div>
    </div>

    <h4 class="mt-4">Seleccionar Nominados Iniciales</h4>
    <p class="text-muted">Seleccione los funcionarios de la lista de disponibles y agréguelos a nominados.</p>

    <!-- Dos paneles: Disponibles y Nominados -->
    <div class="row">
        <!-- Panel: Usuarios Disponibles -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Usuarios Disponibles (<span id="count-disponibles"><?= count($usuarios) ?></span>)</h5>
                </div>
                <div class="card-body">
                    <!-- Filtros -->
                    <div class="mb-3 p-2 bg-light rounded">
                        <div class="row g-2">
                            <div class="col-12">
                                <input type="text" id="buscar-disponibles" class="form-control form-control-sm" placeholder="Buscar por nombre...">
                            </div>
                            <div class="col-md-6">
                                <select id="filtro-provincia" class="form-select form-select-sm">
                                    <option value="">Todas las provincias</option>
                                    <?php
                                    $provincias = array_unique(array_column($usuarios, 'provincia'));
                                    foreach ($provincias as $prov): ?>
                                        <option value="<?= $prov ?>"><?= $prov ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <select id="filtro-ciudad" class="form-select form-select-sm">
                                    <option value="">Todas las ciudades</option>
                                    <?php
                                    $ciudades = array_unique(array_column($usuarios, 'ciudad'));
                                    foreach ($ciudades as $ciudad): ?>
                                        <option value="<?= $ciudad ?>"><?= $ciudad ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <select id="filtro-genero" class="form-select form-select-sm">
                                    <option value="">Todos los géneros</option>
                                    <option value="MASCULINO">Masculino</option>
                                    <option value="FEMENINO">Femenino</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <select id="filtro-rol" class="form-select form-select-sm">
                                    <option value="">Todos los roles</option>
                                    <?php
                                    $roles = array_unique(array_column($usuarios, 'rol_familiar'));
                                    $roles = array_filter($roles);
                                    foreach ($roles as $rol): ?>
                                        <option value="<?= $rol ?>"><?= $rol ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Controles de paginación -->
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <small class="text-muted">Página <span id="pagina-disponibles">1</span> de <span id="total-paginas-disponibles">1</span></small>
                        <div>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="prev-disponibles">◀</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="next-disponibles">▶</button>
                        </div>
                    </div>

                    <!-- Lista de disponibles -->
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-sm table-hover" id="tabla-disponibles">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>Nombre</th>
                                    <th>Ciudad</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody id="lista-disponibles">
                                <!-- Se llena con JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Panel: Nominados Iniciales -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Nominados Iniciales (<span id="count-nominados">0</span>)</h5>
                </div>
                <div class="card-body">
                    <!-- Controles de paginación -->
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <small class="text-muted">Página <span id="pagina-nominados">1</span> de <span id="total-paginas-nominados">1</span></small>
                        <div>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="prev-nominados">◀</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="next-nominados">▶</button>
                        </div>
                    </div>

                    <!-- Lista de nominados -->
                    <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                        <table class="table table-sm table-hover">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>Nombre</th>
                                    <th>Ciudad</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody id="lista-nominados">
                                <!-- Se llena con JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Inputs hidden para envío (múltiples valores) -->
    <div id="inputs-nominados"></div>

    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-3">
        <button type="submit" class="btn btn-primary">Guardar Concurso y Nominados</button>
        <a href="<?= base_url('admin/index') ?>" class="btn btn-secondary">Cancelar</a>
    </div>
</form>

<?php $this->load->view('layouts/footer'); ?>

<script>
// Datos de usuarios desde PHP
const usuarios = <?= json_encode(array_map(function($u) {
    return [
        'email' => $u['email'],
        'nombre' => $u['apellidos'] . ' ' . $u['nombres'],
        'apellidos' => $u['apellidos'],
        'nombres' => $u['nombres'],
        'provincia' => $u['provincia'] ?? '',
        'ciudad' => $u['ciudad'] ?? '',
        'genero' => $u['genero'] ?? '',
        'rol_familiar' => $u['rol_familiar'] ?? ''
    ];
}, $usuarios)) ?>;

// Variables de estado
let disponibles = [...usuarios];
let nominados = [];
const POR_PAGINA = 20;
let paginaDisponibles = 1;
let paginaNominados = 1;

// Renderizar lista de disponibles
function renderDisponibles() {
    // Aplicar filtros
    const provincia = document.getElementById('filtro-provincia').value;
    const ciudad = document.getElementById('filtro-ciudad').value;
    const genero = document.getElementById('filtro-genero').value;
    const rol = document.getElementById('filtro-rol').value;
    const buscar = document.getElementById('buscar-disponibles').value.toLowerCase();

    let filtrados = disponibles.filter(u => {
        return (!provincia || u.provincia === provincia) &&
               (!ciudad || u.ciudad === ciudad) &&
               (!genero || u.genero === genero) &&
               (!rol || u.rol_familiar === rol) &&
               (!buscar || u.nombre.toLowerCase().includes(buscar));
    });

    // Paginación
    const totalPaginas = Math.ceil(filtrados.length / POR_PAGINA) || 1;
    const inicio = (paginaDisponibles - 1) * POR_PAGINA;
    const paginaItems = filtrados.slice(inicio, inicio + POR_PAGINA);

    // Actualizar controles
    document.getElementById('pagina-disponibles').textContent = paginaDisponibles;
    document.getElementById('total-paginas-disponibles').textContent = totalPaginas;
    document.getElementById('count-disponibles').textContent = filtrados.length;
    document.getElementById('prev-disponibles').disabled = paginaDisponibles === 1;
    document.getElementById('next-disponibles').disabled = paginaDisponibles === totalPaginas;

    // Renderizar
    const tbody = document.getElementById('lista-disponibles');
    if (paginaItems.length === 0) {
        tbody.innerHTML = '<tr><td colspan="3" class="text-center text-muted">No hay usuarios disponibles</td></tr>';
    } else {
        tbody.innerHTML = paginaItems.map(u => `
            <tr>
                <td>${u.apellidos} ${u.nombres}</td>
                <td>${u.ciudad}</td>
                <td>
                    <button type="button" class="btn btn-sm btn-primary" onclick="agregarNominado('${u.email}')">
                        Agregar
                    </button>
                </td>
            </tr>
        `).join('');
    }
}

// Renderizar lista de nominados
function renderNominados() {
    const totalPaginas = Math.ceil(nominados.length / POR_PAGINA) || 1;
    const inicio = (paginaNominados - 1) * POR_PAGINA;
    const paginaItems = nominados.slice(inicio, inicio + POR_PAGINA);

    // Actualizar controles
    document.getElementById('pagina-nominados').textContent = paginaNominados;
    document.getElementById('total-paginas-nominados').textContent = totalPaginas;
    document.getElementById('count-nominados').textContent = nominados.length;
    document.getElementById('prev-nominados').disabled = paginaNominados === 1;
    document.getElementById('next-nominados').disabled = paginaNominados === totalPaginas;

    // Renderizar
    const tbody = document.getElementById('lista-nominados');
    if (paginaItems.length === 0) {
        tbody.innerHTML = '<tr><td colspan="3" class="text-center text-muted">No hay nominados agregados</td></tr>';
    } else {
        tbody.innerHTML = paginaItems.map(u => `
            <tr>
                <td>${u.apellidos} ${u.nombres}</td>
                <td>${u.ciudad}</td>
                <td>
                    <button type="button" class="btn btn-sm btn-danger" onclick="quitarNominado('${u.email}')">
                        Quitar
                    </button>
                </td>
            </tr>
        `).join('');
    }

    // Generar múltiples inputs hidden (uno por cada nominado)
    const container = document.getElementById('inputs-nominados');
    container.innerHTML = nominados.map(n => 
        `<input type="hidden" name="nominado_id[]" value="${n.email}">`
    ).join('');
}

// Agregar nominado
function agregarNominado(email) {
    const usuario = disponibles.find(u => u.email === email);
    if (usuario) {
        disponibles = disponibles.filter(u => u.email !== email);
        nominados.push(usuario);
        renderDisponibles();
        renderNominados();
        paginaNominados = 1;
    }
}

// Quitar nominado
function quitarNominado(email) {
    const usuario = nominados.find(u => u.email === email);
    if (usuario) {
        nominados = nominados.filter(u => u.email !== email);
        disponibles.push(usuario);
        // Ordenar por nombre
        disponibles.sort((a, b) => a.nombre.localeCompare(b.nombre));
        renderDisponibles();
        renderNominados();
    }
}

// Event listeners para filtros
['provincia', 'ciudad', 'genero', 'rol'].forEach(campo => {
    const el = document.getElementById('filtro-' + campo);
    if (el) el.addEventListener('change', () => { paginaDisponibles = 1; renderDisponibles(); });
});

document.getElementById('buscar-disponibles').addEventListener('keyup', () => { paginaDisponibles = 1; renderDisponibles(); });

// Paginación disponibles
document.getElementById('prev-disponibles').addEventListener('click', () => {
    if (paginaDisponibles > 1) { paginaDisponibles--; renderDisponibles(); }
});
document.getElementById('next-disponibles').addEventListener('click', () => {
    const total = Math.ceil(disponibles.filter(u => {
        const p = document.getElementById('filtro-provincia').value;
        const c = document.getElementById('filtro-ciudad').value;
        const g = document.getElementById('filtro-genero').value;
        const r = document.getElementById('filtro-rol').value;
        const b = document.getElementById('buscar-disponibles').value.toLowerCase();
        return (!p || u.provincia === p) && (!c || u.ciudad === c) && (!g || u.genero === g) && (!r || u.rol_familiar === r) && (!b || u.nombre.toLowerCase().includes(b));
    }).length / POR_PAGINA);
    if (paginaDisponibles < total) { paginaDisponibles++; renderDisponibles(); }
});

// Paginación nominados
document.getElementById('prev-nominados').addEventListener('click', () => {
    if (paginaNominados > 1) { paginaNominados--; renderNominados(); }
});
document.getElementById('next-nominados').addEventListener('click', () => {
    const total = Math.ceil(nominados.length / POR_PAGINA);
    if (paginaNominados < total) { paginaNominados++; renderNominados(); }
});

// Validar al enviar
document.getElementById('form-concurso').addEventListener('submit', function(e) {
    if (nominados.length === 0) {
        e.preventDefault();
        alert('Debe agregar al menos un nominado inicial.');
        return;
    }
});

// Inicializar
renderDisponibles();
renderNominados();
</script>