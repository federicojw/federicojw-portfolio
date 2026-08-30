<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}

function formatPeriodLabel($sm, $sy, $em, $ey) {
    $start = trim(ucfirst(strtolower($sm)) . ' ' . $sy);
    
    if (strtoupper($em) === 'PRESENT') {
        $end = 'PRESENT';
    } else {
        $end = trim(ucfirst(strtolower($em)) . ' ' . $ey);
    }
    
    if (empty($start) && empty($end)) return '';
    if (empty($start)) return $end;
    if (empty($end)) return $start;
    if (trim(strtoupper($start)) === trim(strtoupper($end))) return $start;
    
    return $start . ' - ' . $end;
}

$projects_file = 'projects.json';
$projects = file_exists($projects_file) ? json_decode(file_get_contents($projects_file), true) : [];

usort($projects, function($a, $b) {
    $aPin = isset($a['is_pinned']) && $a['is_pinned'] ? 1 : 0;
    $bPin = isset($b['is_pinned']) && $b['is_pinned'] ? 1 : 0;
    return $bPin - $aPin;
});
$projects_count = count($projects);

$exp_file = 'experiences.json';
$experiences = file_exists($exp_file) ? json_decode(file_get_contents($exp_file), true) : [];
$exp_count = count($experiences);

$cred_file = 'credentials.json';
$credentials = file_exists($cred_file) ? json_decode(file_get_contents($cred_file), true) : [];
$cred_count = count($credentials);

$active_tab = $_GET['tab'] ?? 'projects';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard | Federico Justian Wijono</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
  <style>
    :root {
      --bg: #0d1117;
      --panel: #101722;
      --border: #263244;
      --border-bright: #34445b;
      --text: #e6edf3;
      --text-soft: #a9b6c7;
      --text-muted: #718096;
      --cyan: #22d3ee;
      --cyan-bright: #67e8f9;
      --red: #f87171;
      --mono: "JetBrains Mono", monospace;
      --sans: "Inter", sans-serif;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      min-height: 100vh; background: var(--bg); color: var(--text); font-family: var(--sans); display: flex; flex-direction: column;
    }
    body.modal-open { overflow: hidden; }
    header {
      height: 70px; padding: 0 40px; display: flex; align-items: center; justify-content: space-between;
      border-bottom: 1px solid var(--border); background: rgba(13, 17, 23, 0.85); backdrop-filter: blur(12px);
    }
    .header-left { display: flex; align-items: center; gap: 20px; }
    .brand { font-family: var(--mono); font-size: 0.85rem; font-weight: 600; color: var(--cyan); }
    .it-rotator-badge {
      font-family: var(--mono); font-size: 0.75rem; color: var(--text-soft); display: inline-flex; align-items: center; gap: 8px;
      background: rgba(34, 211, 238, 0.03); border: 1px solid var(--border); padding: 6px 12px; border-radius: 8px; white-space: nowrap; transition: all 0.3s ease;
    }
    .it-rotator-badge:hover { border-color: var(--cyan); background: rgba(34, 211, 238, 0.08); box-shadow: 0 0 12px rgba(34, 211, 238, 0.2); color: var(--cyan-bright); }
    .it-rotator-badge span.dynamic-icon, .it-rotator-badge span.dynamic-text { display: inline-block; transition: opacity 0.3s ease, transform 0.3s ease; }
    .it-rotator-badge span.dynamic-text { color: var(--cyan-bright); font-weight: 600; text-align: left; }
    .header-right { display: flex; align-items: center; gap: 15px; }
    .greeting-rotator { font-family: var(--mono); font-size: 0.8rem; color: var(--text-soft); display: inline-flex; align-items: center; justify-content: flex-end; gap: 4px; text-align: right; white-space: nowrap; }
    .greeting-rotator span.dynamic-text { color: var(--cyan-bright); font-weight: 600; display: inline-block; text-align: right; transition: opacity 0.3s ease, transform 0.3s ease; }
    .logout-btn { padding: 8px 14px; border: 1px solid rgba(239, 68, 68, 0.4); border-radius: 7px; color: var(--red); background: rgba(239, 68, 68, 0.05); font-family: var(--mono); font-size: 0.7rem; text-decoration: none; flex-shrink: 0; }
    .logout-btn:hover { background: rgba(239, 68, 68, 0.15); }
    .main-container { max-width: 1000px; width: 100%; margin: 40px auto; padding: 0 20px; flex: 1; }
    .dashboard-title h1 { font-size: 2rem; letter-spacing: -0.03em; display: flex; align-items: center; gap: 10px; }
    .dashboard-title p { color: var(--text-muted); font-family: var(--mono); font-size: 0.75rem; margin-top: 6px; }
    .tabs { display: flex; gap: 10px; margin: 30px 0; border-bottom: 1px solid var(--border); padding-bottom: 12px; }
    .tab-btn {
      padding: 10px 18px; border: 1px solid var(--border); border-radius: 8px; background: var(--panel); color: var(--text-soft); font-family: var(--mono); font-size: 0.75rem; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; transition: all 0.2s ease;
    }
    .tab-btn.active, .tab-btn:hover { border-color: var(--cyan); color: var(--cyan-bright); background: rgba(34, 211, 238, 0.05); }
    .tab-badge { display: inline-flex; align-items: center; justify-content: center; padding: 2px 7px; border-radius: 5px; background: rgba(255, 255, 255, 0.07); color: var(--text-muted); font-size: 0.65rem; font-weight: 600; transition: all 0.2s ease; }
    .tab-btn.active .tab-badge, .tab-btn:hover .tab-badge { background: rgba(34, 211, 238, 0.15); color: var(--cyan-bright); }
    .panel-content { display: none; border: 1px solid var(--border); border-radius: 12px; background: var(--panel); padding: 28px; }
    .panel-content.active { display: block; }
    .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .section-header h2 { display: flex; align-items: center; gap: 10px; font-size: 1.2rem; }
    .header-actions { display: flex; gap: 10px; align-items: center; }
    .btn-action {
      padding: 8px 14px; border: 1px solid var(--cyan); border-radius: 7px; background: rgba(34, 211, 238, 0.1); color: var(--cyan-bright); font-family: var(--mono); font-size: 0.7rem; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; transition: background 0.2s ease, border-color 0.2s ease;
    }
    .btn-action:hover { background: rgba(34, 211, 238, 0.25); border-color: var(--cyan-bright); }
    .btn-reorder { border-color: var(--border-bright); background: transparent; color: var(--text-soft); }
    .btn-reorder:hover { border-color: var(--cyan); color: var(--cyan-bright); background: rgba(34, 211, 238, 0.05); }
    .btn-action:not(.btn-reorder):not(.btn-cancel):not(.btn-delete-modal) { background-color: transparent; color: var(--text-soft); border: 1px solid var(--border-bright); transition: all 0.2s ease-in-out; }
    .btn-action:not(.btn-reorder):not(.btn-cancel):not(.btn-delete-modal):hover { background-color: rgba(34, 211, 238, 0.08); color: var(--cyan-bright); border-color: var(--cyan); box-shadow: 0 0 12px rgba(34, 211, 238, 0.2); }
    table { width: 100%; border-collapse: collapse; margin-top: 15px; }
    th, td { text-align: left; padding: 10px 14px; border-bottom: 1px solid var(--border); font-size: 0.85rem; vertical-align: middle; }
    th { font-family: var(--mono); font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; }
    td a { color: var(--cyan); text-decoration: none; }
    td a:hover { text-decoration: underline; }
    .btn-edit-cert {
      display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border: 1px solid var(--border-bright); border-radius: 6px; background: rgba(34, 211, 238, 0.03); color: var(--cyan-bright); font-family: var(--mono); font-size: 0.7rem; cursor: pointer; text-decoration: none; transition: all 0.25s ease;
    }
    .btn-edit-cert:hover { border-color: var(--cyan); background: rgba(34, 211, 238, 0.1); box-shadow: 0 0 12px rgba(34, 211, 238, 0.25); }
    .pin-btn { background: rgba(255, 255, 255, 0.02); color: var(--text-muted); border-color: var(--border); text-decoration: none; cursor: pointer; }
    .pin-btn:hover { border-color: var(--cyan); color: var(--cyan-bright); background: rgba(34, 211, 238, 0.05); text-decoration: none; }
    .pin-btn.pinned { background: rgba(34, 211, 238, 0.15); color: var(--cyan-bright); border-color: var(--cyan); text-decoration: none; }
    .drag-handle { display: none; cursor: grab; color: var(--text-muted); font-size: 1rem; user-select: none; align-items: center; }
    body.reorder-active .drag-handle, body.exp-reorder-active .drag-handle, body.cred-reorder-active .drag-handle { display: inline-flex; }
    body.reorder-active tr, body.exp-reorder-active tr, body.cred-reorder-active tr { cursor: grab; }
    .modal-overlay { position: fixed; inset: 0; background: rgba(13, 17, 23, 0.45); backdrop-filter: blur(3px); display: none; place-items: center; z-index: 1000; padding: 20px; }
    .modal-overlay.active { display: grid; }
    .modal-box {
      width: 100%; max-width: 580px; max-height: 90vh; background: var(--panel); border: 1px solid var(--border-bright); border-radius: 12px; padding: 32px; overflow-y: auto; box-shadow: 0 20px 40px rgba(0,0,0,0.4); transform: scale(0.96); opacity: 0; transition: transform 0.2s ease, opacity 0.2s ease;
    }
    .modal-overlay.active .modal-box { transform: scale(1); opacity: 1; }
    .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; border-bottom: 1px solid var(--border); padding-bottom: 14px; }
    .modal-header h3 { font-size: 1.2rem; font-family: var(--mono); color: var(--cyan); display: flex; align-items: center; gap: 10px; }
    .form-group { margin-bottom: 18px; }
    label { display: block; margin-bottom: 6px; font-family: var(--mono); font-size: 0.75rem; color: var(--text-muted); }
    input, textarea, select {
      width: 100%; padding: 12px; border: 1px solid var(--border-bright); border-radius: 8px; background: #080d14; color: var(--text); font-family: var(--sans); font-size: 0.95rem; transition: border-color 0.2s ease, background 0.2s ease;
    }
    input:hover, textarea:hover, select:hover { border-color: var(--cyan-bright); background: #0c131f; }
    input:focus, textarea:focus, select:focus { border-color: var(--cyan); outline: none; background: #0c131f; }
    input[type="file"] { color: var(--text-soft); padding: 9px 12px; cursor: pointer; }
    input[type="file"]::file-selector-button {
      background: rgba(34, 211, 238, 0.1); border: 1px solid var(--cyan); color: var(--cyan-bright); padding: 6px 12px; border-radius: 6px; font-family: var(--mono); font-size: 0.75rem; cursor: pointer; margin-right: 12px; transition: all 0.2s ease;
    }
    input[type="file"]::file-selector-button:hover { background: rgba(34, 211, 238, 0.25); border-color: var(--cyan-bright); }
    input[type="checkbox"] {
      appearance: none; -webkit-appearance: none; width: 18px; height: 18px; background-color: #080d14; border: 1px solid var(--border-bright); border-radius: 4px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; transition: all 0.2s ease; vertical-align: middle; position: relative; flex-shrink: 0;
    }
    input[type="checkbox"]:hover { border-color: var(--cyan-bright); }
    input[type="checkbox"]:checked { background-color: var(--cyan); border-color: var(--cyan); }
    input[type="checkbox"]:checked::after { content: ""; width: 5px; height: 10px; border: solid #080d14; border-width: 0 2px 2px 0; transform: rotate(45deg); position: absolute; top: 2px; }
    textarea.fixed-scroll-desc { resize: none; height: 110px; overflow-y: auto; line-height: 1.5; }
    .modal-footer { display: flex; justify-content: space-between; align-items: center; margin-top: 28px; border-top: 1px solid var(--border); padding-top: 18px; }
    .modal-footer-left { display: flex; gap: 10px; }
    .btn-cancel { background: transparent; border: 1px solid var(--border-bright); color: var(--text-muted); }
    .btn-cancel:hover { background: rgba(255,255,255,0.05); color: var(--text); border-color: var(--text-muted); }
    .btn-delete-modal { background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.4); color: var(--red); }
    .btn-delete-modal:hover { background: rgba(239, 68, 68, 0.2); border-color: var(--red); }
    .sortable-ghost { opacity: 0.3; background: rgba(34, 211, 238, 0.05) !important; }
    .sortable-drag { background: var(--panel) !important; border: 1px solid var(--cyan); box-shadow: 0 10px 25px rgba(0,0,0,0.5); }
  </style>
</head>
<body>

  <header>
    <div class="header-left">
      <div class="brand">&lt;/&gt; federicojw.com // ADMIN PANEL</div>
      <div class="it-rotator-badge">
        <span id="it-icon" class="dynamic-icon">💻</span> <span id="it-rotator-word" class="dynamic-text">FULL-STACK DEVELOPER</span>
      </div>
    </div>
    <div class="header-right">
      <div class="greeting-rotator">
        <span id="rotator-word" class="dynamic-text">HALO</span>&nbsp;FICO
      </div>
      <a href="index.php?logout=true" class="logout-btn">Log out ↗</a>
    </div>
  </header>

  <div class="main-container">
    <div class="dashboard-title">
      <h1>⚙️ Portfolio Control Center</h1>
      <p>Manage your projects, experiences, and credentials dynamically.</p>
    </div>

    <!-- TAB MENU -->
    <div class="tabs">
      <button class="tab-btn <?php echo $active_tab === 'projects' ? 'active' : ''; ?>" onclick="switchTab(event, 'projects')">
        📂 Projects <span class="tab-badge"><?php echo $projects_count; ?></span>
      </button>
      <button class="tab-btn <?php echo $active_tab === 'experience' ? 'active' : ''; ?>" onclick="switchTab(event, 'experience')">
        💼 Experience <span class="tab-badge"><?php echo $exp_count; ?></span>
      </button>
      <button class="tab-btn <?php echo $active_tab === 'credentials' ? 'active' : ''; ?>" onclick="switchTab(event, 'credentials')">
        🏆 Credentials <span class="tab-badge"><?php echo $cred_count; ?></span>
      </button>
    </div>

    <!-- PROJECTS PANEL -->
    <div id="projects" class="panel-content <?php echo $active_tab === 'projects' ? 'active' : ''; ?>">
      <div class="section-header">
        <h2>⚡ Manage Projects</h2>
        <div class="header-actions">
          <div id="reorder-actions" style="display: none; gap: 8px;">
            <button class="btn-action" onclick="saveReorder()">💾 Save Order</button>
            <button class="btn-action btn-cancel" onclick="toggleReorder()">❌ Cancel</button>
          </div>
          <button class="btn-action btn-reorder" id="btn-reorder-toggle" onclick="toggleReorder()">📊 Reorder</button>
          <button class="btn-action" onclick="openAddModal()">➕ Add Project</button>
        </div>
      </div>

      <table>
        <thead>
          <tr>
            <th>Type & Title</th>
            <th>Tech Stack</th>
            <th>Link</th>
            <th>Action</th>
            <th style="text-align: right;">Pin</th>
          </tr>
        </thead>
        <tbody id="sortable-projects">
          <?php if (empty($projects)): ?>
            <tr><td colspan="5" style="text-align: center; color: var(--text-muted);">No projects found.</td></tr>
          <?php else: ?>
            <?php foreach ($projects as $p): ?>
              <?php 
                $isPinned = !empty($p['is_pinned']) && $p['is_pinned'];
                $projType = !empty($p['project_type']) ? $p['project_type'] : 'WEB DEVELOPMENT';
                $projPeriod = formatPeriodLabel($p['start_month'] ?? '', $p['start_year'] ?? '', $p['end_month'] ?? '', $p['end_year'] ?? '');
                $projIcon = $p['project_icon'] ?? 'code';
              ?>
              <tr data-id="<?php echo $p['id']; ?>" data-pinned="<?php echo $isPinned ? 'true' : 'false'; ?>">
                <td>
                  <div style="display: flex; align-items: center; gap: 12px;">
                    <span class="drag-handle" title="Drag to reorder">☰</span>
                    <div>
                      <div style="font-size: 0.65rem; font-family: var(--mono); color: var(--text-muted); text-transform: uppercase; display: flex; align-items: center; gap: 6px;">
                        <span style="color: var(--cyan);">[<?php echo htmlspecialchars($projIcon); ?>]</span> 
                        <?php echo htmlspecialchars($projType); ?> <?php if(!empty($projPeriod)): ?>• <?php echo htmlspecialchars($projPeriod); ?><?php endif; ?>
                      </div>
                      <strong style="line-height: 1.4;"><?php echo htmlspecialchars($p['title']); ?></strong>
                    </div>
                  </div>
                </td>
                <td>
                  <div style="display: flex; flex-wrap: wrap; gap: 4px;">
                    <?php 
                      $techs = explode(',', $p['tech']);
                      foreach ($techs as $t):
                        $cleanTech = trim($t);
                        if (!empty($cleanTech)):
                    ?>
                      <span style="font-family: var(--mono); font-size: 0.65rem; padding: 2px 6px; border: 1px solid var(--border-bright); border-radius: 4px; background: rgba(34, 211, 238, 0.03); color: var(--cyan);"><?php echo htmlspecialchars($cleanTech); ?></span>
                    <?php 
                        endif;
                      endforeach; 
                    ?>
                  </div>
                </td>
                <td><a href="<?php echo htmlspecialchars($p['link']); ?>" target="_blank"><?php echo htmlspecialchars($p['btn_label'] ?? 'View site'); ?> ↗</a></td>
                <td>
                  <div class="action-links">
                    <button class="btn-edit-cert" onclick="openEditModal(
                      '<?php echo $p['id']; ?>',
                      '<?php echo htmlspecialchars($p['project_type'] ?? 'WEB DEVELOPMENT', ENT_QUOTES); ?>',
                      '<?php echo htmlspecialchars($p['title'], ENT_QUOTES); ?>',
                      '<?php echo htmlspecialchars($p['description'], ENT_QUOTES); ?>',
                      '<?php echo htmlspecialchars($p['tech'], ENT_QUOTES); ?>',
                      '<?php echo htmlspecialchars($p['btn_label'] ?? 'View site', ENT_QUOTES); ?>',
                      '<?php echo htmlspecialchars($p['link'], ENT_QUOTES); ?>',
                      '<?php echo htmlspecialchars($p['start_month'] ?? '', ENT_QUOTES); ?>',
                      '<?php echo htmlspecialchars($p['start_year'] ?? '', ENT_QUOTES); ?>',
                      '<?php echo htmlspecialchars($p['end_month'] ?? '', ENT_QUOTES); ?>',
                      '<?php echo htmlspecialchars($p['end_year'] ?? '', ENT_QUOTES); ?>',
                      '<?php echo htmlspecialchars($projIcon, ENT_QUOTES); ?>'
                    )">✏️ Edit ↗</button>
                  </div>
                </td>
                <td style="text-align: right;">
                  <button type="button" 
                          onclick="togglePin('<?php echo $p['id']; ?>', this)" 
                          class="btn-edit-cert pin-btn <?php echo $isPinned ? 'pinned' : ''; ?>" 
                          title="<?php echo $isPinned ? 'Unpin project' : 'Pin project'; ?>">
                    📍 <span class="pin-text"><?php echo $isPinned ? 'Pinned' : 'Pin'; ?></span>
                  </button>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- EXPERIENCE PANEL -->
    <div id="experience" class="panel-content <?php echo $active_tab === 'experience' ? 'active' : ''; ?>">
      <div class="section-header">
        <h2>💼 Manage Experience</h2>
        <div class="header-actions">
          <div id="exp-reorder-actions" style="display: none; gap: 8px;">
            <button class="btn-action" onclick="saveExpReorder()">💾 Save Pinned Order</button>
            <button class="btn-action btn-cancel" onclick="toggleExpReorder()">❌ Cancel</button>
          </div>
          <button class="btn-action btn-reorder" id="btn-exp-reorder-toggle" onclick="toggleExpReorder()">📊 Reorder Pinned</button>
          <!-- Perbaikan: Memanggil fungsi openExpAddModal() dengan benar -->
          <button class="btn-action" onclick="openExpAddModal()">➕ Add Experience</button>
        </div>
      </div>

      <table>
        <thead>
          <tr>
            <th>Period</th>
            <th>Category & Title</th>
            <th>Location</th>
            <th>Image & Tag</th>
            <th>Action</th>
            <th style="text-align: right;">Pin</th>
          </tr>
        </thead>
        <tbody id="sortable-experiences">
          <?php 
            usort($experiences, function($a, $b) {
                $aPin = isset($a['is_pinned']) && $a['is_pinned'] ? 1 : 0;
                $bPin = isset($b['is_pinned']) && $b['is_pinned'] ? 1 : 0;
                if ($aPin !== $bPin) return $bPin - $aPin;
                
                if ($aPin && $bPin) {
                    $aOrder = $a['pin_order'] ?? 999;
                    $bOrder = $b['pin_order'] ?? 999;
                    if ($aOrder !== $bOrder) return $aOrder - $bOrder;
                }

                $timeA = strtotime("1 " . ($a['start_month']??'JAN') . " " . ($a['start_year']??'2024'));
                $timeB = strtotime("1 " . ($b['start_month']??'JAN') . " " . ($b['start_year']??'2024'));
                return $timeB - $timeA;
            });

            if (empty($experiences)): 
          ?>
            <tr><td colspan="6" style="text-align: center; color: var(--text-muted);">No experiences found.</td></tr>
          <?php else: ?>
            <?php foreach ($experiences as $e): ?>
              <?php 
                $isPinned = !empty($e['is_pinned']) && $e['is_pinned'];
                $periodText = formatPeriodLabel($e['start_month'] ?? '', $e['start_year'] ?? '', $e['end_month'] ?? '', $e['end_year'] ?? '');
              ?>
              <tr data-id="<?php echo $e['id']; ?>" data-pinned="<?php echo $isPinned ? 'true' : 'false'; ?>">
                <td>
                  <div style="display: flex; align-items: center; gap: 8px;">
                    <?php if ($isPinned): ?>
                      <span class="drag-handle" title="Drag to reorder pinned">☰</span>
                    <?php endif; ?>
                    <span style="font-family: var(--mono); font-size: 0.75rem; color: var(--cyan);"><?php echo htmlspecialchars($periodText); ?></span>
                  </div>
                </td>
                <td>
                  <div style="font-size: 0.65rem; font-family: var(--mono); color: var(--text-muted); text-transform: uppercase;"><?php echo htmlspecialchars($e['category']); ?></div>
                  <strong style="line-height: 1.4;"><?php echo htmlspecialchars($e['title']); ?></strong>
                </td>
                <td style="color: var(--text-soft);"><?php echo htmlspecialchars($e['location']); ?></td>
                <td>
                  <div style="display: flex; align-items: center; gap: 8px;">
                    <?php if (!empty($e['image'])): ?>
                      <img src="<?php echo htmlspecialchars($e['image']); ?>" alt="Exp Image" style="width: 60px; height: 34px; object-fit: cover; border-radius: 4px; border: 1px solid var(--border);">
                    <?php else: ?>
                      <span style="color: var(--text-muted); font-size: 0.75rem;">No image</span>
                    <?php endif; ?>
                    
                    <?php if (!empty($e['image_tag'])): ?>
                      <span style="font-family: var(--mono); font-size: 0.65rem; padding: 2px 6px; border: 1px solid var(--border-bright); border-radius: 4px; background: rgba(34, 211, 238, 0.03); color: var(--cyan);">
                        <?php echo htmlspecialchars($e['image_tag']); ?>
                      </span>
                    <?php endif; ?>
                  </div>
                </td>
                <td>
                  <!-- Perbaikan: Memanggil openExpEditModal() dengan parameter yang aman dari kutip -->
                  <button class="btn-edit-cert" onclick="openExpEditModal(
                    '<?php echo $e['id']; ?>',
                    '<?php echo htmlspecialchars($e['start_month'] ?? '', ENT_QUOTES); ?>',
                    '<?php echo htmlspecialchars($e['start_year'] ?? '', ENT_QUOTES); ?>',
                    '<?php echo htmlspecialchars($e['end_month'] ?? '', ENT_QUOTES); ?>',
                    '<?php echo htmlspecialchars($e['end_year'] ?? '', ENT_QUOTES); ?>',
                    '<?php echo htmlspecialchars($e['category'] ?? '', ENT_QUOTES); ?>',
                    '<?php echo htmlspecialchars($e['title'] ?? '', ENT_QUOTES); ?>',
                    '<?php echo htmlspecialchars($e['location'] ?? '', ENT_QUOTES); ?>',
                    '<?php echo htmlspecialchars($e['description'] ?? '', ENT_QUOTES); ?>',
                    '<?php echo htmlspecialchars($e['image_tag'] ?? '', ENT_QUOTES); ?>',
                    '<?php echo htmlspecialchars($e['image'] ?? '', ENT_QUOTES); ?>'
                  )">✏️ Edit</button>
                </td>
                <td style="text-align: right;">
                  <button type="button" 
                          onclick="toggleExpPin('<?php echo $e['id']; ?>', this)" 
                          class="btn-edit-cert pin-btn <?php echo $isPinned ? 'pinned' : ''; ?>" 
                          title="<?php echo $isPinned ? 'Unpin experience' : 'Pin experience'; ?>">
                    📍 <span class="pin-text"><?php echo $isPinned ? 'Pinned' : 'Pin'; ?></span>
                  </button>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- CREDENTIALS PANEL -->
    <div id="credentials" class="panel-content <?php echo $active_tab === 'credentials' ? 'active' : ''; ?>">
      <div class="section-header">
        <h2>🏆 Manage Credentials</h2>
        <div class="header-actions">
          <div id="cred-reorder-actions" style="display: none; gap: 8px;">
            <button class="btn-action" onclick="saveCredReorder()">💾 Save Pinned Order</button>
            <button class="btn-action btn-cancel" onclick="toggleCredReorder()">❌ Cancel</button>
          </div>
          <button class="btn-action btn-reorder" id="btn-cred-reorder-toggle" onclick="toggleCredReorder()">📊 Reorder Pinned</button>
          <button class="btn-action" onclick="openCredAddModal()">➕ Add Credential</button>
        </div>
      </div>

      <table>
        <thead>
          <tr>
            <th>Date / Month-Year</th>
            <th>Institute & Title</th>
            <th>Description</th>
            <th>Certificate File</th>
            <th>Action</th>
            <th style="text-align: right;">Pin</th>
          </tr>
        </thead>
        <tbody id="sortable-credentials">
          <?php 
            usort($credentials, function($a, $b) {
                $aPin = isset($a['is_pinned']) && $a['is_pinned'] ? 1 : 0;
                $bPin = isset($b['is_pinned']) && $b['is_pinned'] ? 1 : 0;
                if ($aPin !== $bPin) return $bPin - $aPin;
                
                if ($aPin && $bPin) {
                    $aOrder = $a['pin_order'] ?? 999;
                    $bOrder = $b['pin_order'] ?? 999;
                    if ($aOrder !== $bOrder) return $aOrder - $bOrder;
                }

                $timeA = !empty($a['issue_date']) ? strtotime($a['issue_date']) : 0;
                $timeB = !empty($b['issue_date']) ? strtotime($b['issue_date']) : 0;
                return $timeB - $timeA;
            });

            if (empty($credentials)): 
          ?>
            <tr><td colspan="6" style="text-align: center; color: var(--text-muted);">No credentials found.</td></tr>
          <?php else: ?>
            <?php foreach ($credentials as $c): ?>
              <?php 
                $isPinned = !empty($c['is_pinned']) && $c['is_pinned'];
                $descRaw = $c['description_raw'] ?? ($c['description'] ?? '');
              ?>
              <tr data-id="<?php echo $c['id']; ?>" data-pinned="<?php echo $isPinned ? 'true' : 'false'; ?>">
                <td>
                  <div style="display: flex; align-items: center; gap: 8px;">
                    <?php if ($isPinned): ?>
                      <span class="drag-handle" title="Drag to reorder pinned">☰</span>
                    <?php endif; ?>
                    <div>
                      <span style="font-family: var(--mono); font-size: 0.75rem; color: var(--cyan);"><?php echo htmlspecialchars($c['formatted_date'] ?? ''); ?></span>
                      <div style="font-size: 0.65rem; font-family: var(--mono); color: var(--text-muted);"><?php echo htmlspecialchars($c['month_year'] ?? ''); ?></div>
                    </div>
                  </div>
                </td>
                <td>
                  <div style="font-size: 0.65rem; font-family: var(--mono); color: var(--text-muted); text-transform: uppercase;"><?php echo htmlspecialchars($c['institute']); ?></div>
                  <strong style="line-height: 1.4;"><?php echo htmlspecialchars($c['title']); ?></strong>
                </td>
                <td style="color: var(--text-soft); font-size: 0.8rem; max-width: 220px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?php echo htmlspecialchars($descRaw); ?>">
                  <?php echo htmlspecialchars($descRaw); ?>
                </td>
                <td>
                  <?php if (!empty($c['file_path'])): ?>
                    <div style="display: flex; gap: 8px;">
                      <a href="<?php echo htmlspecialchars($c['file_path']); ?>" target="_blank" class="btn-edit-cert">View ↗</a>
                      <a href="<?php echo htmlspecialchars($c['file_path']); ?>" download class="btn-edit-cert">Download ⬇</a>
                    </div>
                  <?php else: ?>
                    <span style="color: var(--text-muted); font-size: 0.75rem;">No file</span>
                  <?php endif; ?>
                </td>
                <td>
                  <button class="btn-edit-cert" onclick="openCredEditModal(
                    '<?php echo $c['id']; ?>',
                    '<?php echo htmlspecialchars($c['title'], ENT_QUOTES); ?>',
                    '<?php echo htmlspecialchars($c['institute'], ENT_QUOTES); ?>',
                    '<?php echo htmlspecialchars($descRaw, ENT_QUOTES); ?>',
                    '<?php echo htmlspecialchars($c['issue_date'], ENT_QUOTES); ?>',
                    '<?php echo htmlspecialchars($c['file_path'], ENT_QUOTES); ?>'
                  )">✏️ Edit</button>
                </td>
                <td style="text-align: right;">
                  <button type="button" 
                          onclick="toggleCredPin('<?php echo $c['id']; ?>', this)" 
                          class="btn-edit-cert pin-btn <?php echo $isPinned ? 'pinned' : ''; ?>" 
                          title="<?php echo $isPinned ? 'Unpin credential' : 'Pin credential'; ?>">
                    📍 <span class="pin-text"><?php echo $isPinned ? 'Pinned' : 'Pin'; ?></span>
                  </button>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- POPUP MODAL PROJECT -->
  <div id="projectModal" class="modal-overlay">
    <div class="modal-box">
      <div class="modal-header">
        <h3 id="modalTitle">&lt;/&gt; Project Details</h3>
      </div>
      <form action="project-process.php" method="POST" id="projectFormElement" onsubmit="return validateProjectDates()">
        <input type="hidden" name="action" id="form-action" value="add">
        <input type="hidden" name="id" id="form-id">
        
        <div class="form-group">
          <label>Project Icon / Logo Type</label>
          <select name="project_icon" id="form-project-icon" required>
            <option value="code">&lt;/&gt; (Code / Web)</option>
            <option value="lightning">⚡ (Automation / Speed)</option>
            <option value="laptop">💻 (Laptop / Software)</option>
            <option value="rocket">🚀 (Launch / App Dev)</option>
            <option value="chart">📈 (Analytics / Data)</option>
            <option value="globe">🌐 (Global / Network)</option>
            <option value="shield">🔒 (Security / Cyber)</option>
            <option value="gear">⚙️ (System / Backend)</option>
            <option value="smartphone">📱 (Mobile App)</option>
            <option value="bot">🤖 (AI / Automation Bot)</option>
          </select>
        </div>

        <div class="form-group">
          <label>Project Type (e.g. WEB DEVELOPMENT, AUTOMATION, APP DEV)</label>
          <input type="text" name="project_type" id="form-project-type" placeholder="WEB DEVELOPMENT, AUTOMATION, etc" required>
        </div>
        <div class="form-group">
          <label>Project Title</label>
          <input type="text" name="title" id="form-title" placeholder="Website Development, Portfolio App, etc" required>
        </div>
        <div class="form-group">
          <label>Description</label>
          <textarea name="description" id="form-description" class="fixed-scroll-desc" required></textarea>
        </div>
        <div class="form-group">
          <label>Tech Stack</label>
          <input type="text" name="tech" id="form-tech" placeholder="HTML, CSS, JavaScript, PHP, MySQL, Git, etc" required>
        </div>

        <div style="display: flex; gap: 10px;">
          <div class="form-group" style="flex: 1;">
            <label>Start Month (Optional)</label>
            <select name="start_month" id="proj-start-month">
              <option value="">None / Not specified</option>
              <option value="JANUARY">JANUARY</option><option value="FEBRUARY">FEBRUARY</option><option value="MARCH">MARCH</option><option value="APRIL">APRIL</option><option value="MAY">MAY</option><option value="JUNE">JUNE</option><option value="JULY">JULY</option><option value="AUGUST">AUGUST</option><option value="SEPTEMBER">SEPTEMBER</option><option value="OCTOBER">OCTOBER</option><option value="NOVEMBER">NOVEMBER</option><option value="DECEMBER">DECEMBER</option>
            </select>
          </div>
          <div class="form-group" style="flex: 1;">
            <label>Start Year (Optional)</label>
            <select name="start_year" id="proj-start-year">
              <option value="">None / Not specified</option>
              <?php 
                $current_year = (int)date('Y');
                for($y = $current_year; $y >= $current_year - 15; $y--): 
              ?>
                <option value="<?php echo $y; ?>"><?php echo $y; ?></option>
              <?php endfor; ?>
            </select>
          </div>
        </div>

        <div style="display: flex; gap: 10px;">
          <div class="form-group" style="flex: 1;">
            <label>End Month (Optional)</label>
            <select name="end_month" id="proj-end-month">
              <option value="">None / Not specified</option>
              <option value="JANUARY">JANUARY</option><option value="FEBRUARY">FEBRUARY</option><option value="MARCH">MARCH</option><option value="APRIL">APRIL</option><option value="MAY">MAY</option><option value="JUNE">JUNE</option><option value="JULY">JULY</option><option value="AUGUST">AUGUST</option><option value="SEPTEMBER">SEPTEMBER</option><option value="OCTOBER">OCTOBER</option><option value="NOVEMBER">NOVEMBER</option><option value="DECEMBER">DECEMBER</option>
            </select>
          </div>
          <div class="form-group" style="flex: 1;">
            <label>End Year (Optional)</label>
            <select name="end_year" id="proj-end-year">
              <option value="">None / Not specified</option>
              <?php for($y = $current_year; $y >= $current_year - 15; $y--): ?>
                <option value="<?php echo $y; ?>"><?php echo $y; ?></option>
              <?php endfor; ?>
            </select>
          </div>
        </div>

        <div class="form-group">
          <label>Button Label</label>
          <input type="text" name="btn_label" id="form-btn-label" placeholder="View Site, View Live, etc" required>
        </div>
        <div class="form-group">
          <label>Project Link / URL</label>
          <input type="text" name="link" id="form-link" placeholder="https://..." required>
        </div>
        <div class="modal-footer">
          <div class="modal-footer-left">
            <button type="submit" class="btn-action" id="modal-submit-btn">Save Project</button>
            <button type="button" class="btn-action btn-cancel" onclick="closeModal()">Cancel</button>
          </div>
          <button type="button" class="btn-action btn-delete-modal" id="btn-delete-trigger" style="display: none;">Delete</button>
        </div>
      </form>
    </div>
  </div>

  <!-- POPUP MODAL EXPERIENCE -->
  <div id="expModal" class="modal-overlay">
    <div class="modal-box">
      <div class="modal-header">
        <h3 id="expModalTitle">&lt;/&gt; Experience Details</h3>
      </div>
      <form action="experience-process.php" method="POST" enctype="multipart/form-data" id="expFormElement" onsubmit="return validateExpDates()">
        <input type="hidden" name="action" id="exp-form-action" value="add">
        <input type="hidden" name="id" id="exp-form-id">
        
        <div style="display: flex; gap: 10px;">
          <div class="form-group" style="flex: 1;">
            <label>Start Month</label>
            <select name="start_month" id="exp-start-month" required>
              <option value="" disabled selected>Select Month</option>
              <option value="JANUARY">JANUARY</option><option value="FEBRUARY">FEBRUARY</option><option value="MARCH">MARCH</option><option value="APRIL">APRIL</option><option value="MAY">MAY</option><option value="JUNE">JUNE</option><option value="JULY">JULY</option><option value="AUGUST">AUGUST</option><option value="SEPTEMBER">SEPTEMBER</option><option value="OCTOBER">OCTOBER</option><option value="NOVEMBER">NOVEMBER</option><option value="DECEMBER">DECEMBER</option>
            </select>
          </div>
          <div class="form-group" style="flex: 1;">
            <label>Start Year</label>
            <select name="start_year" id="exp-start-year" required>
              <option value="" disabled selected>Select Year</option>
              <?php for($y = $current_year; $y >= $current_year - 15; $y--): ?>
                <option value="<?php echo $y; ?>"><?php echo $y; ?></option>
              <?php endfor; ?>
            </select>
          </div>
        </div>

        <div class="form-group" style="margin: 4px 0 14px 0;">
          <label style="display: inline-flex; align-items: center; gap: 8px; cursor: pointer; color: var(--text-soft); font-size: 0.8rem;">
            <input type="checkbox" id="exp-current-role" name="is_current" value="1" onchange="toggleCurrentRole()">
            I am currently working in this role
          </label>
        </div>

        <div style="display: flex; gap: 10px;" id="end-date-container">
          <div class="form-group" style="flex: 1;" id="end-month-group">
            <label>End Month</label>
            <select name="end_month" id="exp-end-month" required>
              <option value="" disabled selected>Select Month</option>
              <option value="JANUARY">JANUARY</option><option value="FEBRUARY">FEBRUARY</option><option value="MARCH">MARCH</option><option value="APRIL">APRIL</option><option value="MAY">MAY</option><option value="JUNE">JUNE</option><option value="JULY">JULY</option><option value="AUGUST">AUGUST</option><option value="SEPTEMBER">SEPTEMBER</option><option value="OCTOBER">OCTOBER</option><option value="NOVEMBER">NOVEMBER</option><option value="DECEMBER">DECEMBER</option>
            </select>
          </div>
          <div class="form-group" style="flex: 1;" id="end-year-group">
            <label>End Year</label>
            <select name="end_year" id="exp-end-year" required>
              <option value="" disabled selected>Select Year</option>
              <?php for($y = $current_year; $y >= $current_year - 15; $y--): ?>
                <option value="<?php echo $y; ?>"><?php echo $y; ?></option>
              <?php endfor; ?>
            </select>
          </div>
        </div>

        <div class="form-group">
          <label>Category (e.g. WORK, ORGANIZATION, VOLUNTEER, etc)</label>
          <input type="text" name="category" id="exp-category" placeholder="WORK, ORGANIZATION, VOLUNTEER, etc" required>
        </div>
        <div class="form-group">
          <label>Experience Title</label>
          <input type="text" name="title" id="exp-title" placeholder="Software Engineer, Committee Member, etc" required>
        </div>
        <div class="form-group">
          <label>Location / Institution</label>
          <input type="text" name="location" id="exp-location" placeholder="BINUS University, PT Tech Company, etc" required>
        </div>
        <div class="form-group">
          <label>Description</label>
          <textarea name="description" id="exp-description" class="fixed-scroll-desc" required></textarea>
        </div>
        <div class="form-group">
          <label>Upload Image (16:9 Ratio)</label>
          <input type="file" name="image" id="exp-image-input" accept="image/*" onchange="validateImageSize(this)">
          <div id="current-image-preview" style="margin-top: 8px; font-size: 0.75rem; color: var(--cyan);"></div>
          <div style="font-size: 0.7rem; color: var(--text-muted); margin-top: 4px;">* Maksimal ukuran file gambar adalah 2MB</div>
        </div>
        <div class="form-group" id="delete-image-container" style="display: none;">
          <label style="display: inline-flex; align-items: center; gap: 6px; cursor: pointer;">
            <input type="checkbox" name="delete_image" value="1"> Hapus foto yang ada saat ini
          </label>
        </div>
        
        <div class="form-group">
          <label>Location Type</label>
          <select name="image_tag" id="exp-image-tag" required>
            <option value="" disabled selected>Select Location Type</option>
            <option value="ON-SITE">ON-SITE</option>
            <option value="REMOTE">REMOTE</option>
            <option value="HYBRID">HYBRID</option>
          </select>
        </div>

        <div class="modal-footer">
          <div class="modal-footer-left">
            <button type="submit" class="btn-action" id="exp-modal-submit-btn">Save Experience</button>
            <button type="button" class="btn-action btn-cancel" onclick="closeExpModal()">Cancel</button>
          </div>
          <button type="button" class="btn-action btn-delete-modal" id="btn-exp-delete-trigger" style="display: none;">Delete</button>
        </div>
      </form>
    </div>
  </div>

  <!-- POPUP MODAL CREDENTIALS -->
  <div id="credModal" class="modal-overlay">
    <div class="modal-box">
      <div class="modal-header">
        <h3 id="credModalTitle">&lt;/&gt; Credential Details</h3>
      </div>
      <form action="credential-process.php" method="POST" enctype="multipart/form-data" id="credFormElement">
        <input type="hidden" name="action" id="cred-form-action" value="add">
        <input type="hidden" name="id" id="cred-form-id">
        
        <div class="form-group">
          <label>Credential Title</label>
          <input type="text" name="title" id="cred-title" placeholder="Machine Learning Specialization, etc" required>
        </div>
        <div class="form-group">
          <label>Institute (e.g. NVIDIA, GOOGLE, AWS, etc)</label>
          <input type="text" name="institute" id="cred-institute" placeholder="NVIDIA, GOOGLE, etc" required>
        </div>
        <div class="form-group">
          <label>Description (e.g. Certificate of Competency)</label>
          <input type="text" name="description" id="cred-description" placeholder="Certificate of Competency, etc" required>
        </div>
        <div class="form-group">
          <label>Issue Date (DD/MM/YYYY or Picker)</label>
          <input type="date" name="issue_date" id="cred-issue-date" required>
        </div>
        <div class="form-group">
          <label>Upload Certificate File (PDF / Image)</label>
          <input type="file" name="certificate_file" id="cred-file" accept=".pdf,image/*">
          <div id="current-cred-file-preview" style="margin-top: 8px; font-size: 0.75rem; color: var(--cyan);"></div>
        </div>
        <div class="form-group" id="delete-cred-file-container" style="display: none;">
          <label style="display: inline-flex; align-items: center; gap: 6px; cursor: pointer;">
            <input type="checkbox" name="delete_file" value="1"> Hapus file sertifikat yang ada saat ini
          </label>
        </div>

        <div class="modal-footer">
          <div class="modal-footer-left">
            <button type="submit" class="btn-action" id="cred-modal-submit-btn">Save Credential</button>
            <button type="button" class="btn-action btn-cancel" onclick="closeCredModal()">Cancel</button>
          </div>
          <button type="button" class="btn-action btn-delete-modal" id="btn-cred-delete-trigger" style="display: none;">Delete</button>
        </div>
      </form>
    </div>
  </div>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
  <script>
    const itItems = [{ text: "FULL-STACK DEVELOPER", icon: "💻" }, { text: "FRONT-END ENGINEER", icon: "🎨" }, { text: "BACK-END SYSTEM", icon: "⚙️" }, { text: "DATABASE ADMINISTRATOR", icon: "🗄️" }, { text: "C PROGRAMMING ENTHUSIAST", icon: "⌨️" }, { text: "JAVASCRIPT DEVELOPER", icon: "⚡" }, { text: "PHP LARAVEL EXPERT", icon: "🚀" }, { text: "SOFTWARE ENGINEER", icon: "🛠️" }, { text: "UI/UX DESIGNER", icon: "✨" }, { text: "CYBER SECURITY ANALYST", icon: "🔒" }, { text: "CLOUD ARCHITECT", icon: "☁️" }, { text: "DEVOPS ENGINEER", icon: "♾️" }, { text: "AI & ML ENTHUSIAST", icon: "🤖" }, { text: "MOBILE APP DEVELOPER", icon: "📱" }, { text: "GAME DEVELOPER", icon: "🎮" }, { text: "API INTEGRATION SPECIALIST", icon: "🔌" }, { text: "SYSTEM ADMINISTRATOR", icon: "🖥️" }, { text: "ALGORITHM DESIGNER", icon: "📈" }, { text: "DEBUGGING MASTER", icon: "🐛" }, { text: "OPEN SOURCE CONTRIBUTOR", icon: "🐙" }];

    let itIndex = 0;
    const itWordElem = document.getElementById('it-rotator-word');
    const itIconElem = document.getElementById('it-icon');
    let lastItIndex = -1;

    function rotateItWord() {
      if (!itWordElem || !itIconElem) return;
      itWordElem.style.opacity = 0; itWordElem.style.transform = 'translateY(4px)';
      itIconElem.style.opacity = 0; itIconElem.style.transform = 'translateY(4px)';
      setTimeout(() => {
        let randomIndex;
        do { randomIndex = Math.floor(Math.random() * itItems.length); } while (randomIndex === lastItIndex && itItems.length > 1);
        lastItIndex = randomIndex; itIndex = randomIndex;
        itWordElem.innerText = itItems[itIndex].text;
        itIconElem.innerText = itItems[itIndex].icon;
        itWordElem.style.opacity = 1; itWordElem.style.transform = 'translateY(0px)';
        itIconElem.style.opacity = 1; itIconElem.style.transform = 'translateY(0px)';
      }, 300);
    }
    setInterval(rotateItWord, 3000);

    const greetings = ["HALO", "HELLO", "你好", "こんにちは", "안녕하세요", "BONJOUR", "HOLA", "CIAO", "GUTEN TAG", "OLÁ", "ПРИВЕТ", "مرحبًا", "שלום", "سلام", "MERHABA"];
    let currentIndex = 0;
    const rotatorElement = document.getElementById('rotator-word');
    function rotateGreeting() {
      if (!rotatorElement) return;
      rotatorElement.style.opacity = 0; rotatorElement.style.transform = 'translateY(4px)';
      setTimeout(() => {
        currentIndex = (currentIndex + 1) % greetings.length;
        rotatorElement.innerText = greetings[currentIndex];
        rotatorElement.style.opacity = 1; rotatorElement.style.transform = 'translateY(0px)';
      }, 300);
    }
    setInterval(rotateGreeting, 2500);

    function switchTab(evt, tabName) {
      document.querySelectorAll('.panel-content').forEach(c => c.classList.remove('active'));
      document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
      document.getElementById(tabName).classList.add('active');
      evt.currentTarget.classList.add('active');
      const url = new URL(window.location);
      url.searchParams.set('tab', tabName);
      window.history.pushState({}, '', url);
    }

    const monthMap = { "JANUARY":1, "FEBRUARY":2, "MARCH":3, "APRIL":4, "MAY":5, "JUNE":6, "JULY":7, "AUGUST":8, "SEPTEMBER":9, "OCTOBER":10, "NOVEMBER":11, "DECEMBER":12 };

    function validateDateRange(sm, sy, em, ey) {
      if (!sm || !sy || !em || !ey) return true;
      if (em === 'PRESENT') return true;
      
      const sYear = parseInt(sy), eYear = parseInt(ey);
      const sMonth = monthMap[sm], eMonth = monthMap[em];

      if (eYear < sYear) return false;
      if (eYear === sYear && eMonth < sMonth) return false;
      return true;
    }

    function validateExpDates() {
      const sm = document.getElementById('exp-start-month').value;
      const sy = document.getElementById('exp-start-year').value;
      const isCurrent = document.getElementById('exp-current-role').checked;
      
      if (isCurrent) return true;

      const em = document.getElementById('exp-end-month').value;
      const ey = document.getElementById('exp-end-year').value;

      if (!validateDateRange(sm, sy, em, ey)) {
        alert("⚠️ Invalid Date Range: End date cannot be earlier than start date.");
        return false;
      }
      return true;
    }

    function validateProjectDates() {
      const sm = document.getElementById('proj-start-month').value;
      const sy = document.getElementById('proj-start-year').value;
      const em = document.getElementById('proj-end-month').value;
      const ey = document.getElementById('proj-end-year').value;

      if (sm && sy && em && ey) {
        if (!validateDateRange(sm, sy, em, ey)) {
          alert("⚠️ Invalid Project Date Range: End date cannot be earlier than start date.");
          return false;
        }
      }
      return true;
    }

    function validateImageSize(input) {
      if (input.files && input.files[0]) {
        const fileSize = input.files[0].size / 1024 / 1024; // dalam MB
        if (fileSize > 2) {
          alert("⚠️ Ukuran file terlalu besar (" + fileSize.toFixed(2) + " MB). Maksimal ukuran file adalah 2MB!");
          input.value = ""; // Reset input file
        }
      }
    }

    function openAddModal() {
      document.getElementById('projectFormElement').reset();
      document.getElementById('form-action').value = 'add';
      document.getElementById('form-id').value = '';
      document.getElementById('form-project-icon').value = 'code';
      document.getElementById('modalTitle').innerHTML = '➕ Add New Project';
      document.getElementById('modal-submit-btn').innerText = 'Save Project';
      document.getElementById('btn-delete-trigger').style.display = 'none';

      const modalBox = document.querySelector('#projectModal .modal-box');
      if (modalBox) modalBox.scrollTop = 0;

      document.getElementById('projectModal').classList.add('active');
      document.body.classList.add('modal-open');
    }

    function openEditModal(id, type, title, desc, tech, btnLabel, link, sm, sy, em, ey, icon) {
      document.getElementById('projectFormElement').reset();
      document.getElementById('form-action').value = 'edit';
      document.getElementById('form-id').value = id;
      document.getElementById('form-project-icon').value = icon || 'code';
      document.getElementById('form-project-type').value = type;
      document.getElementById('form-title').value = title;
      document.getElementById('form-description').value = desc;
      document.getElementById('form-tech').value = tech;
      document.getElementById('form-btn-label').value = btnLabel;
      document.getElementById('form-link').value = link;
      
      document.getElementById('proj-start-month').value = sm || '';
      document.getElementById('proj-start-year').value = sy || '';
      document.getElementById('proj-end-month').value = em || '';
      document.getElementById('proj-end-year').value = ey || '';

      document.getElementById('modalTitle').innerHTML = `✏️ Edit ${title}`;
      document.getElementById('modal-submit-btn').innerText = 'Save Changes';
      
      const deleteBtn = document.getElementById('btn-delete-trigger');
      deleteBtn.style.display = 'inline-flex';
      deleteBtn.onclick = function() {
        if (confirm(`Are you sure you want to delete project "${title}"?`)) {
          window.location.href = 'project-process.php?action=delete&id=' + id;
        }
      };

      const modalBox = document.querySelector('#projectModal .modal-box');
      if (modalBox) modalBox.scrollTop = 0;

      document.getElementById('projectModal').classList.add('active');
      document.body.classList.add('modal-open');
    }

    function closeModal() {
      document.getElementById('projectModal').classList.remove('active');
      document.body.classList.remove('modal-open');
    }

    function togglePin(id, btnElement) {
      fetch('project-process.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
        body: 'action=toggle_pin&ajax=1&id=' + id
      }).then(response => {
        if (response.ok) { window.location.href = 'index.php?tab=projects'; }
      });
    }

    let sortableInstance = null;
    const tableBody = document.getElementById('sortable-projects');
    let originalProjectsHTML = '';

    function toggleReorder() {
      const isDraggingActive = document.body.classList.contains('reorder-active');
      if (isDraggingActive) {
        if (originalProjectsHTML && tableBody) {
          tableBody.innerHTML = originalProjectsHTML;
        }
        document.body.classList.remove('reorder-active');
        document.getElementById('reorder-actions').style.display = 'none';
        document.getElementById('btn-reorder-toggle').style.display = 'inline-flex';
        if (sortableInstance) { sortableInstance.destroy(); sortableInstance = null; }
      } else {
        if (tableBody) originalProjectsHTML = tableBody.innerHTML;
        document.body.classList.add('reorder-active');
        document.getElementById('reorder-actions').style.display = 'flex';
        document.getElementById('btn-reorder-toggle').style.display = 'none';

        if (tableBody && !sortableInstance) {
          sortableInstance = Sortable.create(tableBody, {
            handle: '.drag-handle', animation: 150, ghostClass: 'sortable-ghost', chosenClass: 'sortable-drag',
            onMove: function (evt) {
              const draggedPinned = evt.dragged.getAttribute('data-pinned') === 'true';
              const relatedPinned = evt.related.getAttribute('data-pinned') === 'true';
              return draggedPinned === relatedPinned;
            }
          });
        }
      }
    }

    function saveReorder() {
      if (!tableBody) return;
      const rows = tableBody.querySelectorAll('tr');
      const newOrder = [];
      rows.forEach(row => { const id = row.getAttribute('data-id'); if (id) newOrder.push(id); });

      fetch('project-process.php', {
        method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=reorder&order=' + encodeURIComponent(JSON.stringify(newOrder))
      }).then(response => {
        if (response.ok) { window.location.href = 'index.php?tab=projects'; }
      });
    }

    function toggleCurrentRole() {
      const isChecked = document.getElementById('exp-current-role').checked;
      const endMonthGroup = document.getElementById('end-month-group');
      const endYearGroup = document.getElementById('end-year-group');
      const endMonthSelect = document.getElementById('exp-end-month');
      const endYearSelect = document.getElementById('exp-end-year');

      if (isChecked) {
        endMonthGroup.style.display = 'none'; endYearGroup.style.display = 'none';
        endMonthSelect.removeAttribute('required'); endYearSelect.removeAttribute('required');
        endMonthSelect.value = ''; endYearSelect.value = '';
      } else {
        endMonthGroup.style.display = 'block'; endYearGroup.style.display = 'block';
        endMonthSelect.setAttribute('required', 'required'); endYearSelect.setAttribute('required', 'required');
      }
    }

    function openExpAddModal() {
      document.getElementById('expFormElement').reset();
      document.getElementById('exp-form-action').value = 'add';
      document.getElementById('exp-form-id').value = '';
      document.getElementById('exp-current-role').checked = false;
      toggleCurrentRole();
      
      document.getElementById('current-image-preview').innerHTML = '';
      document.getElementById('delete-image-container').style.display = 'none';

      document.getElementById('expModalTitle').innerHTML = '➕ Add New Experience';
      document.getElementById('exp-modal-submit-btn').innerText = 'Save Experience';
      document.getElementById('btn-exp-delete-trigger').style.display = 'none';

      const modalBox = document.querySelector('#expModal .modal-box');
      if (modalBox) modalBox.scrollTop = 0;

      document.getElementById('expModal').classList.add('active');
      document.body.classList.add('modal-open');
    }

    function openExpEditModal(id, sm, sy, em, ey, cat, title, loc, desc, tag, img) {
      document.getElementById('expFormElement').reset();
      document.getElementById('exp-form-action').value = 'edit';
      document.getElementById('exp-form-id').value = id;
      document.getElementById('exp-start-month').value = sm;
      document.getElementById('exp-start-year').value = sy;
      
      const isPresent = (em.toUpperCase() === 'PRESENT' || !em);
      document.getElementById('exp-current-role').checked = isPresent;
      toggleCurrentRole();

      if (!isPresent) {
        document.getElementById('exp-end-month').value = em;
        document.getElementById('exp-end-year').value = ey;
      }

      document.getElementById('exp-category').value = cat;
      document.getElementById('exp-title').value = title;
      document.getElementById('exp-location').value = loc;
      document.getElementById('exp-description').value = desc;
      document.getElementById('exp-image-tag').value = tag;

      const previewDiv = document.getElementById('current-image-preview');
      const deleteContainer = document.getElementById('delete-image-container');
      if (img) {
        previewDiv.innerHTML = `Current image: <a href="${img}" target="_blank" style="color: var(--cyan);">View Image ↗</a>`;
        deleteContainer.style.display = 'block';
      } else {
        previewDiv.innerHTML = 'No image uploaded.';
        deleteContainer.style.display = 'none';
      }

      document.getElementById('expModalTitle').innerHTML = `✏️ Edit Experience`;
      document.getElementById('exp-modal-submit-btn').innerText = 'Save Changes';
      
      const deleteBtn = document.getElementById('btn-exp-delete-trigger');
      deleteBtn.style.display = 'inline-flex';
      deleteBtn.onclick = function() {
        if (confirm(`Are you sure you want to delete this experience?`)) {
          window.location.href = 'experience-process.php?action=delete&id=' + id;
        }
      };

      const modalBox = document.querySelector('#expModal .modal-box');
      if (modalBox) modalBox.scrollTop = 0;

      document.getElementById('expModal').classList.add('active');
      document.body.classList.add('modal-open');
    }

    function closeExpModal() {
      document.getElementById('expModal').classList.remove('active');
      document.body.classList.remove('modal-open');
    }

    function toggleExpPin(id, btnElement) {
      fetch('experience-process.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
        body: 'action=toggle_pin&ajax=1&id=' + id
      }).then(response => {
        if (response.ok) { window.location.href = 'index.php?tab=experience'; }
      });
    }

    let expSortableInstance = null;
    const expTableBody = document.getElementById('sortable-experiences');
    let originalExpHTML = '';

    function toggleExpReorder() {
      const isDraggingActive = document.body.classList.contains('exp-reorder-active');
      if (isDraggingActive) {
        if (originalExpHTML && expTableBody) {
          expTableBody.innerHTML = originalExpHTML;
        }
        document.body.classList.remove('exp-reorder-active');
        document.getElementById('exp-reorder-actions').style.display = 'none';
        document.getElementById('btn-exp-reorder-toggle').style.display = 'inline-flex';
        if (expSortableInstance) { expSortableInstance.destroy(); expSortableInstance = null; }
      } else {
        if (expTableBody) originalExpHTML = expTableBody.innerHTML;
        document.body.classList.add('exp-reorder-active');
        document.getElementById('exp-reorder-actions').style.display = 'flex';
        document.getElementById('btn-exp-reorder-toggle').style.display = 'none';

        if (expTableBody && !expSortableInstance) {
          expSortableInstance = Sortable.create(expTableBody, {
            handle: '.drag-handle', animation: 150, ghostClass: 'sortable-ghost', chosenClass: 'sortable-drag',
            onMove: function (evt) {
              const draggedPinned = evt.dragged.getAttribute('data-pinned') === 'true';
              const relatedPinned = evt.related.getAttribute('data-pinned') === 'true';
              return draggedPinned && relatedPinned;
            }
          });
        }
      }
    }

    function saveExpReorder() {
      if (!expTableBody) return;
      const rows = expTableBody.querySelectorAll('tr');
      const newPinnedOrder = [];
      rows.forEach(row => {
        const id = row.getAttribute('data-id');
        const isPinned = row.getAttribute('data-pinned') === 'true';
        if (id && isPinned) newPinnedOrder.push(id);
      });

      fetch('experience-process.php', {
        method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=reorder_pinned&order=' + encodeURIComponent(JSON.stringify(newPinnedOrder))
      }).then(response => {
        if (response.ok) { window.location.href = 'index.php?tab=experience'; }
      });
    }

    // --- CREDENTIALS MODAL & ACTIONS ---
    function openCredAddModal() {
      document.getElementById('credFormElement').reset();
      document.getElementById('cred-form-action').value = 'add';
      document.getElementById('cred-form-id').value = '';
      
      document.getElementById('current-cred-file-preview').innerHTML = '';
      document.getElementById('delete-cred-file-container').style.display = 'none';

      document.getElementById('credModalTitle').innerHTML = '➕ Add New Credential';
      document.getElementById('cred-modal-submit-btn').innerText = 'Save Credential';
      document.getElementById('btn-cred-delete-trigger').style.display = 'none';

      const modalBox = document.querySelector('#credModal .modal-box');
      if (modalBox) modalBox.scrollTop = 0;

      document.getElementById('credModal').classList.add('active');
      document.body.classList.add('modal-open');
    }

    function openCredEditModal(id, title, institute, descRaw, issueDate, filePath) {
      document.getElementById('credFormElement').reset();
      document.getElementById('cred-form-action').value = 'edit';
      document.getElementById('cred-form-id').value = id;
      document.getElementById('cred-title').value = title;
      document.getElementById('cred-institute').value = institute;
      document.getElementById('cred-description').value = descRaw;
      document.getElementById('cred-issue-date').value = issueDate;

      const previewDiv = document.getElementById('current-cred-file-preview');
      const deleteContainer = document.getElementById('delete-cred-file-container');
      if (filePath) {
        previewDiv.innerHTML = `Current file: <a href="${filePath}" target="_blank" style="color: var(--cyan);">View File ↗</a>`;
        deleteContainer.style.display = 'block';
      } else {
        previewDiv.innerHTML = 'No file uploaded.';
        deleteContainer.style.display = 'none';
      }

      document.getElementById('credModalTitle').innerHTML = `✏️ Edit Credential`;
      document.getElementById('cred-modal-submit-btn').innerText = 'Save Changes';
      
      const deleteBtn = document.getElementById('btn-cred-delete-trigger');
      deleteBtn.style.display = 'inline-flex';
      deleteBtn.onclick = function() {
        if (confirm(`Are you sure you want to delete this credential?`)) {
          window.location.href = 'credential-process.php?action=delete&id=' + id;
        }
      };

      const modalBox = document.querySelector('#credModal .modal-box');
      if (modalBox) modalBox.scrollTop = 0;

      document.getElementById('credModal').classList.add('active');
      document.body.classList.add('modal-open');
    }

    function closeCredModal() {
      document.getElementById('credModal').classList.remove('active');
      document.body.classList.remove('modal-open');
    }

    function toggleCredPin(id, btnElement) {
      fetch('credential-process.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
        body: 'action=toggle_pin&ajax=1&id=' + id
      }).then(response => {
        if (response.ok) { window.location.href = 'index.php?tab=credentials'; }
      });
    }

    let credSortableInstance = null;
    const credTableBody = document.getElementById('sortable-credentials');
    let originalCredHTML = '';

    function toggleCredReorder() {
      const isDraggingActive = document.body.classList.contains('cred-reorder-active');
      if (isDraggingActive) {
        if (originalCredHTML && credTableBody) {
          credTableBody.innerHTML = originalCredHTML;
        }
        document.body.classList.remove('cred-reorder-active');
        document.getElementById('cred-reorder-actions').style.display = 'none';
        document.getElementById('btn-cred-reorder-toggle').style.display = 'inline-flex';
        if (credSortableInstance) { credSortableInstance.destroy(); credSortableInstance = null; }
      } else {
        if (credTableBody) originalCredHTML = credTableBody.innerHTML;
        document.body.classList.add('cred-reorder-active');
        document.getElementById('cred-reorder-actions').style.display = 'flex';
        document.getElementById('btn-cred-reorder-toggle').style.display = 'none';

        if (credTableBody && !credSortableInstance) {
          credSortableInstance = Sortable.create(credTableBody, {
            handle: '.drag-handle', animation: 150, ghostClass: 'sortable-ghost', chosenClass: 'sortable-drag',
            onMove: function (evt) {
              const draggedPinned = evt.dragged.getAttribute('data-pinned') === 'true';
              const relatedPinned = evt.related.getAttribute('data-pinned') === 'true';
              return draggedPinned && relatedPinned;
            }
          });
        }
      }
    }

    function saveCredReorder() {
      if (!credTableBody) return;
      const rows = credTableBody.querySelectorAll('tr');
      const newPinnedOrder = [];
      rows.forEach(row => {
        const id = row.getAttribute('data-id');
        const isPinned = row.getAttribute('data-pinned') === 'true';
        if (id && isPinned) newPinnedOrder.push(id);
      });

      fetch('credential-process.php', {
        method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=reorder_pinned&order=' + encodeURIComponent(JSON.stringify(newPinnedOrder))
      }).then(response => {
        if (response.ok) { window.location.href = 'index.php?tab=credentials'; }
      });
    }
  </script>
</body>
</html>