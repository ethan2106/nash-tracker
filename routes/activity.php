<?php
use App\Controller\ActivityController;

$activityController = new ActivityController();

switch ($page) {
    case 'activity':
        require_once '../src/View/activity.php';
        break;
    case 'activity/add':
        // Sanitize POST data
        $fields = ['type_activite', 'duree_minutes', 'calories'];
        $originalPost = $_POST;
        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                $value = $_POST[$field];
                if (is_array($value)) {
                    $value = $value[0] ?? '';
                }
                $_POST[$field] = $value;
            }
        }
        $result = $activityController->ajouterActivite();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($result);
        exit;
    case 'activity/delete':
        // Sanitize POST data
        $originalPost = $_POST;
        if (isset($_POST['activite_id'])) {
            $value = $_POST['activite_id'];
            if (is_array($value)) {
                $value = $value[0] ?? '';
            }
            $_POST['activite_id'] = $value;
        }
        $result = $activityController->supprimerActivite();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($result);
        exit;
    case 'activity/today':
        header('Content-Type: application/json; charset=utf-8');
        $result = $activityController->getActivitesAujourdhui();
        echo json_encode($result);
        exit;
    case 'activity/history':
        header('Content-Type: application/json; charset=utf-8');
        $result = $activityController->getHistoriqueActivites();
        echo json_encode($result);
        exit;
}
