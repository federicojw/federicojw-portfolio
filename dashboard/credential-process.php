<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    exit('Unauthorized');
}

$json_file = 'credentials.json';

function getCredentials() {
    global $json_file;
    if (!file_exists($json_file)) return [];
    $data = file_get_contents($json_file);
    return json_decode($data, true) ?: [];
}

function saveCredentials($credentials) {
    global $json_file;
    file_put_contents($json_file, json_encode($credentials, JSON_PRETTY_PRINT));
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// --- TAMBAH CREDENTIAL ---
if ($action === 'add') {
    $credentials = getCredentials();
    
    $filePath = '';
    if (isset($_FILES['certificate_file']) && $_FILES['certificate_file']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) { 
            mkdir($uploadDir, 0755, true); 
        }
        $fileName = time() . '_' . preg_replace("/[^a-zA-Z0-9._-]/", "", basename($_FILES['certificate_file']['name']));
        $targetFile = $uploadDir . $fileName;
        if (move_uploaded_file($_FILES['certificate_file']['tmp_name'], $targetFile)) {
            $filePath = $targetFile;
        }
    }

    $rawDate = $_POST['issue_date'] ?? date('Y-m-d');
    $timestamp = strtotime($rawDate);
    $formattedDate = date('d F Y', $timestamp); // Contoh: 01 January 2026
    $monthYear = strtoupper(date('F Y', $timestamp)); // Contoh: OCTOBER 2025

    $customDesc = trim($_POST['description'] ?? '');
    // Otomatis gabungkan: [DESC] · Issued [01 January 2026]
    $combinedDescription = $customDesc . ' · Issued ' . $formattedDate;

    $newCred = [
        "id" => time(),
        "title" => $_POST['title'] ?? '',
        "institute" => strtoupper($_POST['institute'] ?? ''),
        "description_raw" => $customDesc,
        "description" => $combinedDescription,
        "issue_date" => $rawDate,
        "formatted_date" => $formattedDate,
        "month_year" => $monthYear,
        "file_path" => $filePath,
        "is_pinned" => false,
        "pin_order" => 999
    ];

    $credentials[] = $newCred;
    saveCredentials($credentials);
    header('Location: index.php?tab=credentials&status=cred_added');
    exit;
}

// --- EDIT CREDENTIAL ---
if ($action === 'edit') {
    $id = $_POST['id'] ?? '';
    $credentials = getCredentials();

    foreach ($credentials as &$c) {
        if ($c['id'] == $id) {
            $c['title'] = $_POST['title'] ?? '';
            $c['institute'] = strtoupper($_POST['institute'] ?? '');
            
            $customDesc = trim($_POST['description'] ?? '');
            $c['description_raw'] = $customDesc;
            
            if (!empty($_POST['issue_date'])) {
                $rawDate = $_POST['issue_date'];
                $timestamp = strtotime($rawDate);
                $c['issue_date'] = $rawDate;
                $c['formatted_date'] = date('d F Y', $timestamp);
                $c['month_year'] = strtoupper(date('F Y', $timestamp));
            }

            // Gabungkan ulang deskripsi dengan tanggal terbaru
            $c['description'] = $customDesc . ' · Issued ' . ($c['formatted_date'] ?? '');

            if (isset($_POST['delete_file']) && $_POST['delete_file'] == '1') {
                if (!empty($c['file_path']) && file_exists($c['file_path'])) {
                    unlink($c['file_path']);
                }
                $c['file_path'] = '';
            }

            if (isset($_FILES['certificate_file']) && $_FILES['certificate_file']['error'] === UPLOAD_ERR_OK) {
                if (!empty($c['file_path']) && file_exists($c['file_path'])) {
                    unlink($c['file_path']);
                }
                $uploadDir = 'uploads/';
                if (!is_dir($uploadDir)) { 
                    mkdir($uploadDir, 0755, true); 
                }
                $fileName = time() . '_' . preg_replace("/[^a-zA-Z0-9._-]/", "", basename($_FILES['certificate_file']['name']));
                $targetFile = $uploadDir . $fileName;
                if (move_uploaded_file($_FILES['certificate_file']['tmp_name'], $targetFile)) {
                    $c['file_path'] = $targetFile;
                }
            }

            break;
        }
    }
    unset($c);
    saveCredentials($credentials);
    header('Location: index.php?tab=credentials&status=cred_updated');
    exit;
}

// --- TOGGLE PIN ---
if ($action === 'toggle_pin') {
    $id = $_POST['id'] ?? $_GET['id'] ?? '';
    $credentials = getCredentials();

    $maxOrder = -1;
    foreach ($credentials as $c) {
        if (isset($c['is_pinned']) && $c['is_pinned']) {
            $currentOrder = $c['pin_order'] ?? 0;
            if ($currentOrder > $maxOrder && $currentOrder != 999) {
                $maxOrder = $currentOrder;
            }
        }
    }

    foreach ($credentials as &$c) {
        if ($c['id'] == $id) {
            $c['is_pinned'] = !($c['is_pinned'] ?? false);
            if ($c['is_pinned']) {
                $c['pin_order'] = $maxOrder + 1;
            } else {
                $c['pin_order'] = 999;
            }
            break;
        }
    }
    unset($c);
    saveCredentials($credentials);

    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' || isset($_POST['ajax'])) {
        exit('Success');
    }
    header('Location: index.php?tab=credentials');
    exit;
}

// --- REORDER PINNED ---
if ($action === 'reorder_pinned') {
    $rawOrder = $_POST['order'] ?? '[]';
    $pinnedOrderIds = json_decode($rawOrder, true);
    $credentials = getCredentials();

    foreach ($credentials as &$c) {
        if (isset($c['is_pinned']) && $c['is_pinned']) {
            $pos = array_search($c['id'], $pinnedOrderIds);
            if ($pos !== false) {
                $c['pin_order'] = $pos;
            } else {
                $c['pin_order'] = 999;
            }
        }
    }
    unset($c);

    saveCredentials($credentials);
    exit('Sorted Pinned Credentials');
}

// --- DELETE ---
if ($action === 'delete') {
    $id = $_GET['id'] ?? '';
    $credentials = getCredentials();
    
    foreach ($credentials as $c) {
        if ($c['id'] == $id && !empty($c['file_path']) && file_exists($c['file_path'])) {
            unlink($c['file_path']);
        }
    }

    $credentials = array_values(array_filter($credentials, fn($c) => $c['id'] != $id));
    saveCredentials($credentials);
    header('Location: index.php?tab=credentials&status=cred_deleted');
    exit;
}
?>