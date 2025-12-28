<?php

namespace App\Controller;

use App\Model\ReportsModel;
use App\Service\ExportService;
use Exception;

require_once __DIR__ . '/../../vendor/autoload.php'; // Pour TCPDF

/**
 * ReportsController - Gère la génération de rapports PDF et CSV.
 * Responsabilités :
 * - Génération de rapports détaillés (repas, médicaments, IMC)
 * - Export en PDF et CSV via ExportService
 * - Interactions avec ReportsModel pour récupérer les données
 * - Gestion des périodes d'export.
 */
class ReportsController extends BaseApiController
{
    public function __construct(
        private ReportsModel $reportsModel,
        private ExportService $exportService
    ) {
    }

    /**
     * Gérer les requêtes d'export.
     */
    public function handleExport()
    {
        $userId = $this->requireAuth();

        $type = $_GET['type'] ?? 'pdf'; // pdf ou csv
        $period = $_GET['period'] ?? '7days'; // 7days, 30days, 90days, 1year

        try
        {
            if ($type === 'csv')
            {
                $this->exportCSV($userId, $period);
            } else
            {
                $this->exportPDF($userId, $period);
            }
        } catch (Exception $e)
        {
            error_log('Erreur export: ' . $e->getMessage());
            $_SESSION['flash'] = [
                'type' => 'error',
                'msg' => 'Erreur lors de l\'export des données',
            ];
            header('Location: ?page=settings');
            exit;
        }
    }

    /**
     * Exporter en PDF.
     */
    private function exportPDF(int $userId, string $period)
    {
        $pdfContent = $this->exportService->exportToPDF($userId, $period);

        $filename = 'rapport_sante_' . date('Y-m-d') . '.pdf';

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($pdfContent));
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');

        echo $pdfContent;
        exit;
    }

    /**
     * Exporter en CSV.
     */
    private function exportCSV(int $userId, string $period)
    {
        $csvContent = $this->exportService->exportToCSV($userId, $period);

        $filename = 'rapport_sante_' . date('Y-m-d') . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($csvContent));
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');

        echo $csvContent;
        exit;
    }

    /**
     * Générer et télécharger le rapport PDF (méthode legacy pour compatibilité).
     * @deprecated Utiliser handleExport() à la place
     */
    public function generateReport()
    {
        ob_start(); // Prevent any output before PDF

        try
        {
            $userId = $this->requireAuth();

            $data = $this->reportsModel->getReportData($userId);

            if (!$data)
            {
                throw new Exception('Impossible de récupérer les données du rapport');
            }

            // Créer le PDF avec HTML pour un rendu intuitif
            $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

            // Configuration du PDF
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('Votre App Santé');
            $pdf->SetTitle('Rapport de Santé');
            $pdf->SetSubject('Rapport personnel de suivi santé');
            $pdf->SetKeywords('santé, IMC, repas');

            // Supprimer les en-têtes et pieds de page par défaut
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);

            // Ajouter une page
            $pdf->AddPage();

            // Générer le HTML
            $html = $this->generateReportHTML($data);

            // Écrire le HTML dans le PDF
            $pdf->writeHTML($html, true, false, true, false, '');

            // Sortie du PDF
            $filename = 'rapport_sante_' . date('Y-m-d') . '.pdf';
            $pdf->Output($filename, 'D'); // Télécharger
        } catch (Exception $e)
        {
            error_log('Erreur génération PDF: ' . $e->getMessage());
            $_SESSION['flash'] = [
                'type' => 'error',
                'msg' => 'Erreur lors de la génération du rapport',
            ];
            header('Location: ?page=home');
            exit;
        }
    }

    /**
     * Générer le HTML du rapport pour un rendu intuitif.
     */
    private function generateReportHTML($data)
    {
        $html = '
        <style>
            body { font-family: Arial, sans-serif; color: #333; }
            h1 { color: #2c5aa0; text-align: center; border-bottom: 2px solid #2c5aa0; padding-bottom: 10px; }
            h2 { color: #2c5aa0; margin-top: 20px; border-left: 4px solid #2c5aa0; padding-left: 10px; }
            table { width: 100%; border-collapse: collapse; margin: 10px 0; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; font-weight: bold; }
            .highlight { background-color: #fff3cd; }
            .good { color: green; }
            .warning { color: orange; }
            .danger { color: red; }
            p { margin: 5px 0; }
        </style>

        <h1>Rapport de Santé</h1>
        <p style="text-align: center; font-style: italic;">Généré le ' . date('d/m/Y à H:i') . '</p>

        <h2>Informations Utilisateur</h2>
        <table>
            <tr><td><strong>Nom d\'utilisateur:</strong></td><td>' . htmlspecialchars($data['user']['username'] ?? 'N/A') . '</td></tr>
            <tr><td><strong>Email:</strong></td><td>' . htmlspecialchars($data['user']['email'] ?? 'N/A') . '</td></tr>
        </table>';

        if ($data['imc'] ?? null)
        {
            $imc = $data['imc'];
            $imcClass = '';
            if (($imc['imc_cat'] ?? '') == 'Normal')
            {
                $imcClass = 'good';
            } elseif (($imc['imc_cat'] ?? '') == 'Surpoids' || ($imc['imc_cat'] ?? '') == 'Obésité')
            {
                $imcClass = 'warning';
            }
            $html .= '
            <h2>Indice de Masse Corporelle (IMC)</h2>
            <table>
                <tr><td><strong>IMC:</strong></td><td class="' . $imcClass . '">' . ($imc['imc'] ?? 'N/A') . '</td></tr>
                <tr><td><strong>Catégorie:</strong></td><td class="' . $imcClass . '">' . ($imc['imc_cat'] ?? 'N/A') . '</td></tr>
                <tr><td><strong>Poids:</strong></td><td>' . ($imc['poids'] ?? 'N/A') . ' kg</td></tr>
                <tr><td><strong>Taille:</strong></td><td>' . ($imc['taille'] ?? 'N/A') . ' cm</td></tr>
            </table>';
        }

        $html .= '<h2>Repas d\'Aujourd\'hui</h2>';
        if (!empty($data['today_meals']))
        {
            foreach ($data['today_meals'] as $meal)
            {
                $html .= '<h3>' . ucfirst($meal['meal_type'] ?? 'Repas') . '</h3>';
                if (!empty($meal['aliments']))
                {
                    $html .= '<ul>';
                    foreach ($meal['aliments'] as $aliment)
                    {
                        $html .= '<li>' . htmlspecialchars($aliment['nom'] ?? 'Aliment') . ' (' . ($aliment['quantite'] ?? 0) . 'g)</li>';
                    }
                    $html .= '</ul>';
                } else
                {
                    $html .= '<p>Aucun aliment</p>';
                }
            }
        } else
        {
            $html .= '<p>Aucun repas enregistré aujourd\'hui</p>';
        }

        $html .= '<h2>Résumé</h2>
        <p><strong>Total Calories Aujourd\'hui:</strong> ' . $data['today_calories'] . ' kcal</p>';

        return $html;
    }

    /**
     * Gérer la page des rapports (méthode legacy).
     * @deprecated
     */
    public function handleReportsPage()
    {
        $this->generateReport();
    }
}
