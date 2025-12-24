<?php

namespace App\Controller;

use App\Service\MedicamentService;

class MedicamentController extends BaseApiController
{
    private MedicamentService $medicamentService;

    public function __construct(MedicamentService $medicamentService)
    {
        $this->medicamentService = $medicamentService;
    }

    public function handleMedicamentsPage()
    {
        $medicaments = $this->medicamentService->getAllActiveMedicaments();
        require_once __DIR__ . '/../View/medicaments.php';
    }

    public function handleApiGetMedicaments()
    {
        $medicaments = $this->medicamentService->getAllActiveMedicaments();
        $this->jsonResponse(['success' => true, 'medicaments' => $medicaments]);
    }

    public function handleApiGetMedicament()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $this->jsonResponse(['success' => false, 'message' => 'ID manquant']);
            return;
        }

        $medicament = $this->medicamentService->getMedicamentById((int)$id);
        if ($medicament) {
            $this->jsonResponse(['success' => true, 'medicament' => $medicament]);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Médicament non trouvé']);
        }
    }

    public function handleApiGetPrisesJour()
    {
        $date = $_GET['date'] ?? date('Y-m-d');
        $medicaments = $this->medicamentService->getMedicamentsWithPrisesForDate($date);
        $this->jsonResponse(['success' => true, 'date' => $date, 'medicaments' => $medicaments]);
    }

    public function handleApiMarquerPris()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $medicamentId = $input['medicament_id'] ?? null;
        $date = $input['date'] ?? date('Y-m-d');
        $periode = $input['periode'] ?? null;

        if (!$medicamentId || !$periode) {
            $this->jsonResponse(['success' => false, 'message' => 'Paramètres manquants']);
            return;
        }

        try {
            $result = $this->medicamentService->marquerPrise((int)$medicamentId, $date, $periode);
            $this->jsonResponse($result);
        } catch (\InvalidArgumentException $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function handleApiAnnulerPris()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $medicamentId = $input['medicament_id'] ?? null;
        $date = $input['date'] ?? date('Y-m-d');
        $periode = $input['periode'] ?? null;

        if (!$medicamentId || !$periode) {
            $this->jsonResponse(['success' => false, 'message' => 'Paramètres manquants']);
            return;
        }

        try {
            $result = $this->medicamentService->annulerPrise((int)$medicamentId, $date, $periode);
            $this->jsonResponse($result);
        } catch (\InvalidArgumentException $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function handleApiCreateMedicament()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $medicament = $this->medicamentService->createMedicament($input);

        if ($medicament) {
            $this->jsonResponse(['success' => true, 'medicament' => $medicament]);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Erreur lors de la création']);
        }
    }

    public function handleApiUpdateMedicament()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $input['id'] ?? null;

        if (!$id) {
            $this->jsonResponse(['success' => false, 'message' => 'ID manquant']);
            return;
        }

        $medicament = $this->medicamentService->updateMedicament((int)$id, $input);

        if ($medicament) {
            $this->jsonResponse(['success' => true, 'medicament' => $medicament]);
        } elseif ($medicament === null) {
            $this->jsonResponse(['success' => false, 'message' => 'Médicament non trouvé']);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Erreur lors de la mise à jour']);
        }
    }

    public function handleApiDeleteMedicament()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $input['id'] ?? null;

        if (!$id) {
            $this->jsonResponse(['success' => false, 'message' => 'ID manquant']);
            return;
        }

        $success = $this->medicamentService->deleteMedicament((int)$id);

        if (!$success) {
            $this->jsonResponse(['success' => false, 'message' => 'Médicament non trouvé']);
        } else {
            $this->jsonResponse(['success' => $success]);
        }
    }

    public function handleApiHistorique()
    {
        $startDate = $_GET['start'] ?? date('Y-m-01');
        $endDate = $_GET['end'] ?? date('Y-m-t');
        $data = $this->medicamentService->getHistoriquePrises(1, $startDate, $endDate); // userId hardcoded for now

        $this->jsonResponse([
            'success' => true,
            'historique' => $data['historique'],
            'stats' => $data['stats']
        ]);
    }

    public function handleApiMedicamentsJour()
    {
        $date = $_GET['date'] ?? date('Y-m-d');
        $userId = $this->requireAuthJson();
        $medicaments = $this->medicamentService->getMedicamentsJour($date);

        $this->jsonResponse(['success' => true, 'medicaments' => $medicaments]);
    }
}
