<?php
// ============================================================
//  SecureLab - Dashboard (lista de tickets)
// ============================================================
require_once 'php/config.php';
 
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}
 
$usuario_id   = $_SESSION['user_id'];
$usuario_name = $_SESSION['user_name'];
 
$conn    = conectarDB();
$stmt    = $conn->prepare(
    "SELECT id, titulo, descripcion, archivo_nombre, archivo_ruta, estado, creado_en, respuesta_admin
     FROM tickets WHERE usuario_id = ? ORDER BY creado_en DESC"
);
$stmt->bind_param('i', $usuario_id);
$stmt->execute();
$tickets = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
 
$mensaje_ok    = isset($_GET['ok']);
$mensaje_error = $_GET['error'] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SecureLab — Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand">
            <span class="brand-icon">⬡</span>
            <span class="brand-name">Secure<strong>Lab</strong></span>
        </div>
        <div class="nav-right">
            <span class="nav-user">👤 <?= htmlspecialchars($usuario_name) ?></span>
            <a href="php/logout.php" class="btn-logout">Cerrar sesión</a>
        </div>
    </nav>
 
    <div class="dashboard-layout">
 
        <!-- Sidebar -->
        <aside class="sidebar">
            <ul>
                <li class="active"><span>🎫</span> Mis tickets</li>
            </ul>
        </aside>
 
        <!-- Main content -->
        <main class="main-content">
            <div class="page-header">
                <div>
                    <h1>Mis Tickets</h1>
                    <p class="subtitle">Gestiona tus solicitudes de soporte</p>
                </div>
                <button class="btn-primary" onclick="toggleModal(true)">+ Nuevo ticket</button>
            </div>
 
            <?php if ($mensaje_ok): ?>
                <div class="alert alert-ok">✅ Ticket creado correctamente.</div>
            <?php endif; ?>
            <?php if ($mensaje_error): ?>
                <div class="alert alert-error">⚠️ Error: <?= htmlspecialchars($mensaje_error) ?></div>
            <?php endif; ?>
 
            <!-- Tabla de tickets -->
            <?php if (empty($tickets)): ?>
                <div class="empty-state">
                    <div class="empty-icon">🎫</div>
                    <p>No tienes tickets aún. ¡Crea el primero!</p>
                </div>
            <?php else: ?>
            <table class="tickets-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Título</th>
                        <th>Descripción</th>
                        <th>Adjunto</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th>Respuesta</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($tickets as $t): ?>
                    <tr>
                        <td class="td-id"><?= $t['id'] ?></td>
                        <td class="td-titulo"><?= htmlspecialchars($t['titulo']) ?></td>
                        <td class="td-desc"><?= htmlspecialchars(substr($t['descripcion'], 0, 60)) ?>...</td>
                        <td class="td-file">
                            <?php if ($t['archivo_ruta']): ?>
                                <a href="php/file.php?file=<?= urlencode($t['archivo_ruta']) ?>"
                                   target="_blank" class="link-file">
                                    📎 <?= htmlspecialchars($t['archivo_nombre']) ?>
                                </a>
                            <?php else: ?>
                                <span class="no-file">—</span>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge badge-<?= $t['estado'] ?>"><?= ucfirst($t['estado']) ?></span></td>
                        <td class="td-date"><?= date('d/m/Y H:i', strtotime($t['creado_en'])) ?></td>
                        <td class="td-respuesta">
                            <?php if ($t['respuesta_admin']): ?>
                                <span class="respuesta-texto">💬 <?= htmlspecialchars($t['respuesta_admin']) ?></span>
                            <?php else: ?>
                                <span class="no-file">Pendiente</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </main>
    </div>
 
    <!-- Modal: nuevo ticket -->
    <div class="modal-overlay" id="modalOverlay" onclick="toggleModal(false)">
        <div class="modal" onclick="event.stopPropagation()">
            <div class="modal-header">
                <h2>Nuevo Ticket</h2>
                <button class="modal-close" onclick="toggleModal(false)">✕</button>
            </div>
            <form action="php/create_ticket.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Título *</label>
                    <input type="text" name="titulo" placeholder="Describe brevemente el problema" required>
                </div>
                <div class="form-group">
                    <label>Descripción *</label>
                    <textarea name="descripcion" rows="4" placeholder="Explica el problema con detalle..." required></textarea>
                </div>
                <div class="form-group">
                    <label>Adjuntar archivo <span class="hint">(imagen, PDF, etc.)</span></label>
                    <div class="file-drop" onclick="document.getElementById('fileInput').click()">
                        <span class="file-icon">📂</span>
                        <span id="fileLabel">Haz clic o arrastra un archivo aquí</span>
                        <input type="file" id="fileInput" name="archivo"
                               accept=".jpg,.jpeg,.png,.gif,.pdf,.txt,.docx"
                               onchange="updateLabel(this)" style="display:none">
                    </div>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn-secondary" onclick="toggleModal(false)">Cancelar</button>
                    <button type="submit" class="btn-primary">Crear Ticket</button>
                </div>
            </form>
        </div>
    </div>
 
    <script>
        function toggleModal(show) {
            document.getElementById('modalOverlay').classList.toggle('visible', show);
        }
        function updateLabel(input) {
            const label = document.getElementById('fileLabel');
            label.textContent = input.files[0] ? input.files[0].name : 'Haz clic o arrastra un archivo aquí';
        }
    </script>
</body>
</html>
