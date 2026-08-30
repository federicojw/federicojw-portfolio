<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    exit('Unauthorized');
}

$json_file = 'experiences.json';

function getExperiences() {
    global $json_file;
    if (!file_exists($json_file)) return [];
    $data = file_get_contents($json_file);
    return json_decode($data, true) ?: [];
}

function saveExperiences($experiences) {
    global $json_file;
    file_put_contents($json_file, json_encode($experiences, JSON_PRETTY_PRINT));
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// --- TAMBAH EXPERIENCE ---
if ($action === 'add') {
    $experiences = getExperiences();
    
    $imagePath = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) { 
            mkdir($uploadDir, 0755, true); 
        }
        $fileName = time() . '_' . preg_replace("/[^a-zA-Z0-9._-]/", "", basename($_FILES['image']['name']));
        $targetFile = $uploadDir . $fileName;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            $imagePath = $targetFile;
        }
    }

    $isCurrent = isset($_POST['is_current']) && $_POST['is_current'] == '1';

    $newExp = [
        "id" => time(),
        "start_month" => $_POST['start_month'],
        "start_year" => $_POST['start_year'],
        "end_month" => $isCurrent ? 'PRESENT' : $_POST['end_month'],
        "end_year" => $isCurrent ? 'PRESENT' : $_POST['end_year'],
        "category" => strtoupper($_POST['category']),
        "title" => $_POST['title'],
        "location" => $_POST['location'],
        "description" => $_POST['description'],
        "image" => $imagePath,
        "image_tag" => $_POST['image_tag'] ?? '',
        "is_pinned" => false,
        "pin_order" => 999
    ];

    $experiences[] = $newExp;
    saveExperiences($experiences);
    header('Location: index.php?tab=experience&status=exp_added');
    exit;
}

// --- EDIT EXPERIENCE ---
if ($action === 'edit') {
    $id = $_POST['id'] ?? '';
    $experiences = getExperiences();
    $isCurrent = isset($_POST['is_current']) && $_POST['is_current'] == '1';

    foreach ($experiences as &$e) {
        if ($e['id'] == $id) {
            $e['start_month'] = $_POST['start_month'];
            $e['start_year'] = $_POST['start_year'];
            $e['end_month'] = $isCurrent ? 'PRESENT' : $_POST['end_month'];
            $e['end_year'] = $isCurrent ? 'PRESENT' : $_POST['end_year'];
            $e['category'] = strtoupper($_POST['category']);
            $e['title'] = $_POST['title'];
            $e['location'] = $_POST['location'];
            $e['description'] = $_POST['description'];
            $e['image_tag'] = $_POST['image_tag'];

            if (isset($_POST['delete_image']) && $_POST['delete_image'] == '1') {
                if (!empty($e['image']) && file_exists($e['image'])) {
                    unlink($e['image']);
                }
                $e['image'] = '';
            }

            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                if (!empty($e['image']) && file_exists($e['image'])) {
                    unlink($e['image']);
                }
                $uploadDir = 'uploads/';
                if (!is_dir($uploadDir)) { 
                    mkdir($uploadDir, 0755, true); 
                }
                $fileName = time() . '_' . preg_replace("/[^a-zA-Z0-9._-]/", "", basename($_FILES['image']['name']));
                $targetFile = $uploadDir . $fileName;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                    $e['image'] = $targetFile;
                }
            }

            break;
        }
    }
    unset($e);
    saveExperiences($experiences);
    header('Location: index.php?tab=experience&status=exp_updated');
    exit;
}

// --- TOGGLE PIN ---
if ($action === 'toggle_pin') {
    $id = $_POST['id'] ?? $_GET['id'] ?? '';
    $experiences = getExperiences();

    // Cari max pin_order yang ada saat ini di item yang pinned
    $maxOrder = -1;
    foreach ($experiences as $e) {
        if (isset($e['is_pinned']) && $e['is_pinned']) {
            $currentOrder = $e['pin_order'] ?? 0;
            if ($currentOrder > $maxOrder && $currentOrder != 999) {
                $maxOrder = $currentOrder;
            }
        }
    }

    foreach ($experiences as &$e) {
        if ($e['id'] == $id) {
            $e['is_pinned'] = !($e['is_pinned'] ?? false);
            if ($e['is_pinned']) {
                // Berikan pin_order tepat setelah item pin terakhir (paling bawah dari grup pin)
                $e['pin_order'] = $maxOrder + 1;
            } else {
                $e['pin_order'] = 999;
            }
            break;
        }
    }
    unset($e);
    saveExperiences($experiences);

    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' || isset($_POST['ajax'])) {
        exit('Success');
    }
    header('Location: index.php?tab=experience');
    exit;
}

// --- REORDER KHUSUS ITEM PINNED ---
if ($action === 'reorder_pinned') {
    $rawOrder = $_POST['order'] ?? '[]';
    $pinnedOrderIds = json_decode($rawOrder, true);
    $experiences = getExperiences();

    foreach ($experiences as &$e) {
        if (isset($e['is_pinned']) && $e['is_pinned']) {
            $pos = array_search($e['id'], $pinnedOrderIds);
            if ($pos !== false) {
                $e['pin_order'] = $pos;
            } else {
                $e['pin_order'] = 999;
            }
        }
    }
    unset($e);

    saveExperiences($experiences);
    exit('Sorted Pinned');
}

// --- DELETE EXPERIENCE ---
if ($action === 'delete') {
    $id = $_GET['id'] ?? '';
    $experiences = getExperiences();
    
    foreach ($experiences as $e) {
        if ($e['id'] == $id && !empty($e['image']) && file_exists($e['image'])) {
            unlink($e['image']);
        }
    }

    $experiences = array_values(array_filter($experiences, fn($e) => $e['id'] != $id));
    saveExperiences($experiences);
    header('Location: index.php?tab=experience&status=exp_deleted');
    exit;
}
?>