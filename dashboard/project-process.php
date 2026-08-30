<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    exit('Unauthorized');
}

$json_file = 'projects.json';

function getProjects() {
    global $json_file;
    if (!file_exists($json_file)) return [];
    $data = file_get_contents($json_file);
    return json_decode($data, true) ?: [];
}

function saveProjects($projects) {
    global $json_file;
    file_put_contents($json_file, json_encode($projects, JSON_PRETTY_PRINT));
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'add' || $action === 'edit') {
    $id = $_POST['id'] ?? time();
    $projects = getProjects();

    $project_icon = $_POST['project_icon'] ?? 'code'; // Menangkap pilihan ikon/logo dari form
    $project_type = strtoupper(trim($_POST['project_type'] ?? 'WEB DEVELOPMENT'));
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $tech = $_POST['tech'] ?? '';
    $btn_label = $_POST['btn_label'] ?? 'View site';
    $link = $_POST['link'] ?? '';

    $start_month = $_POST['start_month'] ?? '';
    $start_year = $_POST['start_year'] ?? '';
    $end_month = $_POST['end_month'] ?? '';
    $end_year = $_POST['end_year'] ?? '';

    if ($action === 'add') {
        $newProject = [
            "id" => time(),
            "project_icon" => $project_icon, // Menyimpan project icon
            "project_type" => $project_type,
            "title" => $title,
            "description" => $description,
            "tech" => $tech,
            "btn_label" => $btn_label,
            "link" => $link,
            "start_month" => $start_month,
            "start_year" => $start_year,
            "end_month" => $end_month,
            "end_year" => $end_year,
            "is_pinned" => false
        ];
        
        $insertIndex = 0;
        foreach ($projects as $i => $p) {
            if (isset($p['is_pinned']) && $p['is_pinned']) {
                $insertIndex = $i + 1;
            } else {
                break;
            }
        }
        array_splice($projects, $insertIndex, 0, [$newProject]);
    } else {
        foreach ($projects as &$p) {
            if ($p['id'] == $id) {
                $p['project_icon'] = $project_icon; // Memperbarui project icon saat edit
                $p['project_type'] = $project_type;
                $p['title'] = $title;
                $p['description'] = $description;
                $p['tech'] = $tech;
                $p['btn_label'] = $btn_label;
                $p['link'] = $link;
                $p['start_month'] = $start_month;
                $p['start_year'] = $start_year;
                $p['end_month'] = $end_month;
                $p['end_year'] = $end_year;
                break;
            }
        }
        unset($p);
    }

    saveProjects($projects);
    header('Location: index.php?tab=projects&status=saved');
    exit;
}

if ($action === 'toggle_pin') {
    $id = $_POST['id'] ?? $_GET['id'] ?? '';
    $projects = getProjects();
    
    $targetIndex = -1;
    foreach ($projects as $i => $p) {
        if ($p['id'] == $id) {
            $targetIndex = $i;
            break;
        }
    }
    
    if ($targetIndex !== -1) {
        $projects[$targetIndex]['is_pinned'] = !($projects[$targetIndex]['is_pinned'] ?? false);
        $targetProject = $projects[$targetIndex];
        
        array_splice($projects, $targetIndex, 1);
        
        if ($targetProject['is_pinned']) {
            $insertIndex = 0;
            foreach ($projects as $i => $p) {
                if (isset($p['is_pinned']) && $p['is_pinned']) {
                    $insertIndex = $i + 1;
                } else {
                    break;
                }
            }
            array_splice($projects, $insertIndex, 0, [$targetProject]);
        } else {
            $insertIndex = 0;
            foreach ($projects as $i => $p) {
                if (isset($p['is_pinned']) && $p['is_pinned']) {
                    $insertIndex = $i + 1;
                } else {
                    break;
                }
            }
            array_splice($projects, $insertIndex, 0, [$targetProject]);
        }
        
        saveProjects($projects);
    }
    
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' || isset($_POST['ajax'])) {
        exit('Success');
    }

    header('Location: index.php?tab=projects');
    exit;
}

if ($action === 'reorder') {
    $rawOrder = $_POST['order'] ?? '[]';
    $newOrderIds = json_decode($rawOrder, true);
    $currentProjects = getProjects();
    
    $indexedProjects = [];
    foreach ($currentProjects as $p) {
        $indexedProjects[$p['id']] = $p;
    }
    
    $reorderedProjects = [];
    if (is_array($newOrderIds)) {
        foreach ($newOrderIds as $id) {
            if (isset($indexedProjects[$id])) {
                $reorderedProjects[] = $indexedProjects[$id];
                unset($indexedProjects[$id]);
            }
        }
    }
    
    foreach ($indexedProjects as $p) {
        $reorderedProjects[] = $p;
    }
    
    saveProjects($reorderedProjects);
    exit('Sorted');
}

if ($action === 'delete') {
    $id = $_GET['id'] ?? '';
    $projects = getProjects();
    $projects = array_filter($projects, fn($p) => $p['id'] != $id);
    saveProjects(array_values($projects));
    header('Location: index.php?tab=projects&status=deleted');
    exit;
}
?>