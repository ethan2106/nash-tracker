<?php

namespace App\Service;

use App\Model\HistoriqueMesuresModel;
use App\Model\ReportsModel;

/**
 * ExportService - Service pour l'export des données utilisateur
 * Gère l'export en PDF et CSV des données de santé.
 */
class ExportService
{
    public function __construct(
        private ReportsModel $reportsModel,
        private HistoriqueMesuresModel $historiqueModel
    ) {
    }

    /**
     * Exporter les données en PDF.
     */
    public function exportToPDF(int $userId, string $period = '7days'): string
    {
        $data = $this->getExportData($userId, $period);

        // Utiliser TCPDF pour générer le PDF
        require_once __DIR__ . '/../../vendor/autoload.php';

        // Classe anonyme personnalisée pour en-tête/pied de page
        $pdf = new class(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false) extends \TCPDF
        {
            public string $reportTitle = '';

            public string $reportSubtitle = '';

            public function Header(): void
            {
                // Bandeau bleu en-tête
                $this->SetY(8);
                $this->SetFillColor(30, 64, 175);
                $this->Rect(0, 0, $this->getPageWidth(), 20, 'F');

                // Icône (favicon) si disponible
                $faviconSvg = realpath(__DIR__ . '/../../public/favicon/favicon.svg');
                if ($faviconSvg && is_file($faviconSvg))
                {
                    // Positionner une petite icône
                    $this->ImageSVG($faviconSvg, 12, 4, 10, 0.0, '', '', '', 0, false);
                }

                // Titre
                $this->SetTextColor(255, 255, 255);
                $this->SetFont('helvetica', 'B', 12);
                $this->SetXY(25, 5);
                $this->Cell(0, 8, $this->reportTitle !== '' ? $this->reportTitle : 'Rapport médical de santé', 0, 1, 'L', false, '', 0, false, 'T', 'M');
                $this->SetFont('helvetica', '', 8);
                $this->SetX(25);
                $this->Cell(0, 6, $this->reportSubtitle !== '' ? $this->reportSubtitle : 'Document confidentiel — Suivi Nash', 0, 0, 'L');
            }

            public function Footer(): void
            {
                $this->SetY(-15);
                $this->SetDrawColor(226, 232, 240);
                $this->SetLineWidth(0.2);
                $this->Line(15, $this->GetY(), $this->getPageWidth() - 15, $this->GetY());

                $this->SetY(-12);
                $this->SetFont('helvetica', '', 8);
                $this->SetTextColor(107, 114, 128);
                $this->Cell(0, 6, 'Confidentiel — Usage médical uniquement', 0, 0, 'L');
                $this->Cell(0, 6, 'Page ' . $this->getAliasNumPage() . ' / ' . $this->getAliasNbPages(), 0, 0, 'R');
            }
        };

        // Configuration du PDF
        $pdf->SetCreator('Suivi Nash');
        $pdf->SetAuthor('Suivi Nash');
        $pdf->SetTitle('Rapport de Santé - ' . date('d/m/Y'));
        $pdf->SetSubject('Rapport nutritionnel et de santé');
        $pdf->setPrintHeader(true);
        $pdf->setPrintFooter(true);
        $pdf->SetMargins(15, 25, 15);
        $pdf->SetHeaderMargin(10);
        $pdf->SetFooterMargin(15);
        $pdf->SetAutoPageBreak(true, 20);

        // Renseigner le header
        $pdf->reportTitle = 'Rapport médical de santé';
        $pdf->reportSubtitle = 'Généré le ' . date('d/m/Y à H:i') . ' • Période : ' . $this->getPeriodLabel($period);

        // Page de couverture
        $pdf->AddPage();
        $cover = $this->generatePDFCoverHTML($data, $period);
        $pdf->writeHTML($cover, true, false, true, false, '');

        // Saut de page pour le contenu détaillé
        $pdf->AddPage();
        $html = $this->generatePDFHTML($data, $period);
        $pdf->writeHTML($html, true, false, true, false, '');

        return $pdf->Output('', 'S');
    }

    /**
     * Exporter les données en CSV.
     */
    public function exportToCSV(int $userId, string $period = '7days'): string
    {
        $data = $this->getExportData($userId, $period);

        $csv = 'Rapport de Santé - ' . date('d/m/Y') . "\n\n";

        // Informations utilisateur
        $csv .= "INFORMATIONS UTILISATEUR\n";
        $csv .= "Nom,Pseudo,Email,Date d'inscription\n";
        $csv .= '"' . ($data['user']['pseudo'] ?? '') . '",';
        $csv .= '"' . ($data['user']['pseudo'] ?? '') . '",';
        $csv .= '"' . ($data['user']['email'] ?? '') . '",';
        $csv .= '"' . ($data['user']['date_inscription'] ?? '') . "\"\n\n";

        // IMC actuel
        if ($data['imc'])
        {
            $objectiveLoss = $data['imc']['calories_perte'] ?? max((int)($data['imc']['bmr'] ?? 0), (int)(($data['imc']['tdee'] ?? 0) - 500));
            $csv .= "IMC ACTUEL\n";
            $csv .= "IMC,Catégorie,Poids,Taille,BMR,TDEE,Objectif perte (kcal/j)\n";
            $csv .= number_format($data['imc']['imc'], 1, ',', '') . ',';
            $csv .= '"' . ($data['imc']['imc_cat'] ?? '') . '",';
            $csv .= number_format($data['imc']['poids'], 1, ',', '') . ',';
            $csv .= number_format($data['imc']['taille'], 0, ',', '') . ',';
            $csv .= number_format($data['imc']['bmr'], 0, ',', '') . ',';
            $csv .= number_format($data['imc']['tdee'], 0, ',', '') . ',';
            $csv .= number_format($objectiveLoss, 0, ',', '') . "\n\n";
        }

        // Historique mesures
        if (!empty($data['historique_mesures']))
        {
            $csv .= "HISTORIQUE POIDS/IMC\n";
            $csv .= "Date,Poids (kg),IMC\n";
            foreach ($data['historique_mesures'] as $mesure)
            {
                $csv .= date('d/m/Y', strtotime($mesure['date_mesure'] ?? 'now')) . ',';
                $csv .= number_format($mesure['poids'], 1, ',', '') . ',';
                $csv .= number_format($mesure['imc'], 1, ',', '') . "\n";
            }
            $csv .= "\n";
        }

        // Repas du jour
        if (!empty($data['today_meals']))
        {
            $csv .= "REPAS D'AUJOURD'HUI\n";
            $csv .= "Aliment,Quantité,Calories,Protéines (g),Glucides (g),Lipides (g)\n";
            foreach ($data['today_meals'] as $meal)
            {
                if (!empty($meal['aliments']))
                {
                    foreach ($meal['aliments'] as $aliment)
                    {
                        $csv .= '"' . ($aliment['nom'] ?? '') . '",';
                        $csv .= '"' . ($aliment['quantite'] ?? 0) . 'g",';
                        $csv .= number_format(($aliment['calories'] ?? 0) * ($aliment['quantite'] ?? 0) / 100, 0, ',', '') . ',';
                        // Pour les macros, on n'a pas ces données, on met 0
                        $csv .= '0,';
                        $csv .= '0,';
                        $csv .= "0\n";
                    }
                }
            }
            $csv .= "\nTotal Calories Aujourd'hui:," . number_format($data['today_calories'], 0, ',', '') . "\n";
        }

        return $csv;
    }

    /**
     * Récupérer les données d'export.
     */
    private function getExportData(int $userId, string $period = '7days'): array
    {
        $data = $this->reportsModel->getReportData($userId);

        // Ajouter l'historique des mesures selon la période
        $days = $this->getPeriodDays($period);
        $data['historique_mesures'] = $this->historiqueModel->getHistorique($userId, $days);

        return $data;
    }

    /**
     * Convertir la période en nombre de jours.
     */
    private function getPeriodDays(string $period): int
    {
        switch ($period)
        {
            case '30days': return 30;
            case '90days': return 90;
            case '1year': return 365;
            default: return 7;
        }
    }

    /**
     * Générer le HTML pour le PDF.
     */
    private function generatePDFHTML(array $data, string $period): string
    {
        $html = '
        <style>
            /* Design médical professionnel */
            body { 
                font-family: "Helvetica", "Arial", sans-serif; 
                color: #1f2937;
                line-height: 1.5;
            }
            
            /* En-tête principal */
            .header {
                background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
                padding: 25px;
                margin: -15px -15px 30px -15px;
                color: white;
            }
            .header h1 {
                margin: 0;
                font-size: 24px;
                font-weight: 700;
                letter-spacing: 0.5px;
            }
            .header .subtitle {
                margin-top: 8px;
                font-size: 13px;
                opacity: 0.95;
            }
            
            /* Sections */
            .section {
                margin-bottom: 25px;
                page-break-inside: avoid;
            }
            .section-title {
                color: #1e40af;
                font-size: 16px;
                font-weight: 700;
                margin-bottom: 12px;
                padding-bottom: 6px;
                border-bottom: 3px solid #3b82f6;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            
            /* Cartes d\'information */
            .info-card {
                background: #f8fafc;
                border: 1px solid #e2e8f0;
                border-left: 4px solid #3b82f6;
                padding: 15px;
                margin-bottom: 15px;
                border-radius: 4px;
            }
            .info-card.alert {
                background: #fef3c7;
                border-left-color: #f59e0b;
            }
            .info-card.success {
                background: #d1fae5;
                border-left-color: #10b981;
            }
            .info-card.warning {
                background: #fee2e2;
                border-left-color: #ef4444;
            }
            
            /* Tableaux */
            table {
                width: 100%;
                border-collapse: collapse;
                margin: 10px 0;
                font-size: 11px;
                background: white;
            }
            th {
                background: #1e40af;
                color: white;
                font-weight: 600;
                padding: 10px;
                text-align: left;
                font-size: 11px;
                text-transform: uppercase;
                letter-spacing: 0.3px;
                vertical-align: middle;
            }
            td {
                border: 1px solid #e5e7eb;
                padding: 8px 10px;
                color: #374151;
                vertical-align: middle;
                line-height: 1.25;
            }
            tr:nth-child(even) {
                background-color: #f9fafb;
            }
            
            /* Métriques clés */
            .metrics-grid {
                display: table;
                width: 100%;
                margin: 15px 0;
            }
            .metric-box {
                display: table-cell;
                width: 33.33%;
                padding: 12px;
                text-align: center;
                background: #f8fafc;
                border: 1px solid #e2e8f0;
            }
            .metric-value {
                font-size: 22px;
                font-weight: 700;
                color: #1e40af;
                display: block;
                margin-bottom: 4px;
            }
            .metric-label {
                font-size: 10px;
                color: #6b7280;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            
            /* Footer */
            .footer {
                margin-top: 40px;
                padding-top: 15px;
                border-top: 2px solid #e5e7eb;
                text-align: center;
                font-size: 9px;
                color: #9ca3af;
            }
            .footer strong {
                color: #1e40af;
            }
            
            /* Badges */
            .badge {
                display: inline-block;
                padding: 4px 10px;
                border-radius: 12px;
                font-size: 10px;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.3px;
            }
            .badge-info { background: #dbeafe; color: #1e40af; }
            .badge-success { background: #d1fae5; color: #047857; }
            .badge-warning { background: #fed7aa; color: #c2410c; }
            .badge-danger { background: #fee2e2; color: #dc2626; }
        </style>

        <div style="margin-bottom: 10px;">
            <strong>Période d\'analyse :</strong> <span class="badge badge-info">' . $this->getPeriodLabel($period) . '</span>
        </div>';

        // Informations utilisateur
        $html .= '
        <div class="section">
            <div class="section-title">Informations Patient</div>
            <table>
                <tr>
                    <th width="30%">Identifiant</th>
                    <td>' . htmlspecialchars($data['user']['pseudo'] ?? 'N/A') . '</td>
                </tr>
                <tr>
                    <th>Email de contact</th>
                    <td>' . htmlspecialchars($data['user']['email'] ?? 'N/A') . '</td>
                </tr>
                <tr>
                    <th>Suivi depuis le</th>
                    <td>' . date('d/m/Y', strtotime($data['user']['date_inscription'] ?? 'now')) . '</td>
                </tr>
            </table>
        </div>';

        // IMC actuel
        if ($data['imc'])
        {
            $imcValue = $data['imc']['imc'];
            $imcCategory = $data['imc']['imc_cat'] ?? 'Non défini';
            $cardClass = 'info-card';

            // Déterminer la couleur selon l'IMC
            if ($imcValue < 18.5)
            {
                $cardClass = 'info-card warning';
                $badge = 'badge-warning';
            } elseif ($imcValue >= 18.5 && $imcValue < 25)
            {
                $cardClass = 'info-card success';
                $badge = 'badge-success';
            } elseif ($imcValue >= 25 && $imcValue < 30)
            {
                $cardClass = 'info-card alert';
                $badge = 'badge-warning';
            } else
            {
                $cardClass = 'info-card warning';
                $badge = 'badge-danger';
            }

            $objectiveLoss = $data['imc']['calories_perte'] ?? max((int)($data['imc']['bmr'] ?? 0), (int)(($data['imc']['tdee'] ?? 0) - 500));

            $html .= '
            <div class="section">
                <div class="section-title">Mesures Anthropométriques Actuelles</div>
                <div class="' . $cardClass . '">
                    <div style="text-align: center; margin-bottom: 15px;">
                        <span class="badge ' . $badge . '">' . strtoupper($imcCategory) . '</span>
                    </div>
                    <div class="metrics-grid">
                        <div class="metric-box">
                            <span class="metric-value">' . number_format($imcValue, 1, ',', ' ') . '</span>
                            <span class="metric-label">IMC</span>
                        </div>
                        <div class="metric-box">
                            <span class="metric-value">' . number_format($data['imc']['poids'], 1, ',', ' ') . '</span>
                            <span class="metric-label">Poids (kg)</span>
                        </div>
                        <div class="metric-box">
                            <span class="metric-value">' . number_format($data['imc']['taille'], 0, ',', ' ') . '</span>
                            <span class="metric-label">Taille (cm)</span>
                        </div>
                    </div>
                    <div class="metrics-grid" style="margin-top: 10px;">
                        <div class="metric-box">
                            <span class="metric-value">' . number_format($data['imc']['bmr'], 0, ',', ' ') . '</span>
                            <span class="metric-label">BMR (kcal/j)</span>
                        </div>
                        <div class="metric-box">
                            <span class="metric-value">' . number_format($data['imc']['tdee'], 0, ',', ' ') . '</span>
                            <span class="metric-label">TDEE (kcal/j)</span>
                        </div>
                        <div class="metric-box">
                            <span class="metric-value">' . number_format($objectiveLoss, 0, ',', ' ') . '</span>
                            <span class="metric-label">Objectif perte (kcal/j)</span>
                        </div>
                    </div>
                </div>
            </div>';
        }

        // Historique mesures
        if (!empty($data['historique_mesures']))
        {
            $html .= '
            <div class="section">
                <div class="section-title">Évolution Poids et IMC</div>
                <table>
                    <thead>
                        <tr>
                            <th>Date de mesure</th>
                            <th style="text-align: center;">Poids (kg)</th>
                            <th style="text-align: center;">IMC</th>
                            <th style="text-align: center;">Variation</th>
                        </tr>
                    </thead>
                    <tbody>';

            $previousPoids = null;
            foreach ($data['historique_mesures'] as $mesure)
            {
                $variation = '';
                if ($previousPoids !== null)
                {
                    $diff = $mesure['poids'] - $previousPoids;
                    if ($diff > 0)
                    {
                        $variation = '<span style="color: #dc2626;">+' . number_format(abs($diff), 1, ',', ' ') . ' kg</span>';
                    } elseif ($diff < 0)
                    {
                        $variation = '<span style="color: #10b981;">-' . number_format(abs($diff), 1, ',', ' ') . ' kg</span>';
                    } else
                    {
                        $variation = '<span style="color: #6b7280;">stable</span>';
                    }
                }
                $previousPoids = $mesure['poids'];

                $html .= '<tr>
                    <td>' . date('d/m/Y', strtotime($mesure['date_mesure'] ?? 'now')) . '</td>
                    <td style="text-align: center; font-weight: 600;">' . number_format($mesure['poids'], 1, ',', ' ') . '</td>
                    <td style="text-align: center; font-weight: 600;">' . number_format($mesure['imc'], 1, ',', ' ') . '</td>
                    <td style="text-align: center;">' . $variation . '</td>
                </tr>';
            }
            $html .= '</tbody></table></div>';
        }

        // Repas du jour
        if (!empty($data['today_meals']))
        {
            $html .= '
            <div class="section">
                <div class="section-title">Apports Nutritionnels du Jour</div>
                <table>
                    <thead>
                        <tr>
                            <th>Aliment</th>
                            <th style="text-align: center;">Quantité</th>
                            <th style="text-align: center;">Calories</th>
                            <th style="text-align: center;">Protéines (g)</th>
                            <th style="text-align: center;">Glucides (g)</th>
                            <th style="text-align: center;">Lipides (g)</th>
                        </tr>
                    </thead>
                    <tbody>';

            foreach ($data['today_meals'] as $meal)
            {
                if (!empty($meal['aliments']))
                {
                    foreach ($meal['aliments'] as $aliment)
                    {
                        $calories = ($aliment['calories'] ?? 0) * ($aliment['quantite'] ?? 0) / 100;

                        $html .= '<tr>
                            <td>' . htmlspecialchars($aliment['nom'] ?? 'N/A') . '</td>
                            <td style="text-align: center;">' . htmlspecialchars(($aliment['quantite'] ?? 0) . ' g') . '</td>
                            <td style="text-align: center; font-weight: 600;">' . number_format($calories, 0, ',', ' ') . '</td>
                            <td style="text-align: center;">-</td>
                            <td style="text-align: center;">-</td>
                            <td style="text-align: center;">-</td>
                        </tr>';
                    }
                }
            }

            $html .= '</tbody></table>
            <div class="info-card alert">
                <strong>⚡ Total calorique de la journée :</strong> 
                <span style="font-size: 18px; font-weight: 700; color: #1e40af;">' . number_format($data['today_calories'], 0, ',', ' ') . ' kcal</span>';

            // Comparaison avec l'objectif si disponible
            if (isset($data['imc']['tdee']))
            {
                $diff = $data['today_calories'] - ($data['imc']['tdee'] - 500);
                if ($diff > 0)
                {
                    $html .= '<br><span style="color: #dc2626;">⚠️ Excédent de ' . number_format(abs($diff), 0, ',', ' ') . ' kcal par rapport à l\'objectif</span>';
                } else
                {
                    $html .= '<br><span style="color: #10b981;">✓ Conforme à l\'objectif (-' . number_format(abs($diff), 0, ',', ' ') . ' kcal de marge)</span>';
                }
            }

            $html .= '</div></div>';
        }

        // Footer médical
        $html .= '
        <div class="footer">
            <strong>DOCUMENT CONFIDENTIEL</strong><br>
            Rapport généré automatiquement par le système Suivi Nash<br>
            Date de génération : ' . date('d/m/Y à H:i:s') . '<br>
            <br>
            <em>Ce document est destiné à un usage médical. Les données présentées sont fournies à titre informatif 
            et ne remplacent pas un avis médical professionnel. Consultez votre médecin pour toute question de santé.</em>
        </div>';

        return $html;
    }

    /**
     * Génère la page de couverture (vision rapide des KPI).
     */
    private function generatePDFCoverHTML(array $data, string $period): string
    {
        $imc = $data['imc']['imc'] ?? null;
        $poids = $data['imc']['poids'] ?? null;
        $tdee = $data['imc']['tdee'] ?? null;

        $html = '
        <style>
            .cover {
                margin-top: 8px;
            }
            .cover-title {
                font-size: 22px;
                font-weight: 800;
                color: #1e40af;
                margin-bottom: 6px;
            }
            .cover-subtitle { color: #374151; font-size: 12px; margin-bottom: 16px; }
            .kpi-grid { display: table; width: 100%; }
            .kpi { display: table-cell; width: 25%; padding: 14px; border: 1px solid #e5e7eb; background: #f8fafc; text-align:center; }
            .kpi .label { font-size: 10px; text-transform: uppercase; color: #6b7280; letter-spacing: .4px; }
            .kpi .value { font-size: 20px; font-weight: 800; color: #111827; margin-top: 4px; }
            .note { margin-top: 16px; font-size: 10px; color: #6b7280; }
        </style>
        <div class="cover">
            <div class="cover-title">Rapport synthétique — ' . $this->getPeriodLabel($period) . '</div>
            <div class="cover-subtitle">Aperçu des indicateurs clés du patient</div>
            <div class="kpi-grid">
                <div class="kpi"><div class="label">IMC</div><div class="value">' . ($imc !== null ? number_format($imc, 1, ',', ' ') : '-') . '</div></div>
                <div class="kpi"><div class="label">Poids (kg)</div><div class="value">' . ($poids !== null ? number_format($poids, 1, ',', ' ') : '-') . '</div></div>
                <div class="kpi"><div class="label">TDEE (kcal/j)</div><div class="value">' . ($tdee !== null ? number_format($tdee, 0, ',', ' ') : '-') . '</div></div>
            </div>
            <div class="note">Document généré automatiquement par Suivi Nash — ' . date('d/m/Y à H:i') . '</div>
        </div>';

        return $html;
    }

    /**
     * Obtenir le label de la période.
     */
    private function getPeriodLabel(string $period): string
    {
        switch ($period)
        {
            case '30days': return '30 derniers jours';
            case '90days': return '90 derniers jours';
            case '1year': return 'Dernière année';
            default: return '7 derniers jours';
        }
    }
}
