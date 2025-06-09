<?php
require_once '../config.php';
require_once '../Services/AdminUserService.php';
// Create an instance of the Employee class
$adminUserObj = new AdminUsers($conn);
// Get the request method
$method = $_SERVER['REQUEST_METHOD'];
// Get the requested endpoint
$endpoint = $_SERVER['PATH_INFO'];
// Set the response content type
header('Content-Type: application/json');
// Process the request
switch ($method) {
    case 'GET':
        if (preg_match('#^/check-credentials/([^/]+)/([^/]+)$#', $endpoint, $matches)) { // '/user-admin/johnsmith/mysecretpass'
            // Get employee by ID
            $username = $matches[1];
            $password = $matches[2];
            $adminUser = $adminUserObj->CheckCredentials($username, $password);
            echo json_encode($adminUser);
        }
        elseif (preg_match('#^/check-guid/([^/]+)$#', $endpoint, $matches)) { // '/user-admin/secretguid'
            // Get employee by ID
            $guid = $matches[1];
            $adminUser = $adminUserObj->CheckGuid($guid);
            echo json_encode($adminUser);
        }
        break;
}
?>