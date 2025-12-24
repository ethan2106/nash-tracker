<?php

namespace App\Service;

/**
 * Service pour interagir avec l'API OpenFoodFacts
 * Responsabilités :
 * - Recherche de produits
 * - Récupération de détails de produits
 * - Normalisation des données nutritionnelles
 * - Cache des résultats API.
 */
class OpenFoodFactsService
{
    private const API_BASE_URL = 'https://world.openfoodfacts.org/cgi/search.pl';

    private const PRODUCT_API_URL = 'https://world.openfoodfacts.org/api/v0/product/';

    private CacheService $cache;

    public function __construct(?CacheService $cache = null)
    {
        $this->cache = $cache ?? new CacheService();
    }

    /**
     * Recherche des produits via OpenFoodFacts.
     */
    public function search(string $query): array
    {
        if (empty($query) || strlen($query) < 2)
        {
            return [];
        }

        // Vérifier le cache
        $cachedResult = $this->cache->get(CacheService::NAMESPACE_OPENFOODFACTS, 'search_' . md5($query));
        if ($cachedResult !== null)
        {
            error_log("CACHE HIT for query: $query");

            return $cachedResult;
        }
        error_log("CACHE MISS for query: $query");

        try
        {
            // Construction de l'URL de recherche API v0 (plus fiable)
            $params = [
                'search_terms' => $query,
                'search_simple' => 1,
                'action' => 'process',
                'json' => 1,
                'page_size' => 20,
                'fields' => 'product_name,brands,image_url,nutriments,code',
            ];

            $url = self::API_BASE_URL . '?' . http_build_query($params);

            // Debug: log de l'URL appelée
            error_log('OpenFoodFacts Search URL: ' . $url);
            error_log('Search query: ' . $query);

            // Appel API avec cURL (plus fiable que file_get_contents)
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 30, // Timeout augmenté à 30 secondes
                CURLOPT_CONNECTTIMEOUT => 10, // Timeout de connexion augmenté à 10 secondes
                CURLOPT_USERAGENT => 'SuiviNash/1.0',
                CURLOPT_HTTPHEADER => [
                    'Accept: application/json',
                    'Content-Type: application/json',
                ],
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 3,
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            $totalTime = curl_getinfo($ch, CURLINFO_TOTAL_TIME);

            // Debug: log des informations
            error_log("OpenFoodFacts API call - URL: $url, HTTP Code: $httpCode, Time: {$totalTime}s, Error: $curlError");

            if ($response === false)
            {
                return ['error' => 'Erreur de connexion à l\'API OpenFoodFacts: ' . $curlError];
            }

            if ($httpCode !== 200)
            {
                return ['error' => 'Erreur HTTP ' . $httpCode . ' depuis l\'API OpenFoodFacts'];
            }

            $data = json_decode($response, true);

            if ($data === null)
            {
                return ['error' => 'Erreur de parsing de la réponse API'];
            }

            // Debug: log du nombre de résultats
            $productsKey = isset($data['products']) ? 'products' : (isset($data['data']) ? 'data' : null);
            error_log('API Response - Products found: ' . ($productsKey ? count($data[$productsKey]) : 0));

            if (!$productsKey || !isset($data[$productsKey]) || !is_array($data[$productsKey]))
            {
                return ['error' => 'Format de réponse API inattendu'];
            }

            $products = $data[$productsKey];

            // Traitement des résultats
            $results = [];
            foreach ($products as $product)
            {
                if (empty($product['product_name']))
                {
                    continue; // Skip products without name
                }

                $results[] = [
                    'name' => $product['product_name'],
                    'brands' => $product['brands'] ?? '',
                    'image' => $product['image_url'] ?? '',
                    'code' => $product['code'] ?? '',
                    'nutriments' => $this->normalizeNutriments($product['nutriments'] ?? []),
                ];
            }

            // Mettre en cache le résultat
            $this->cache->set(CacheService::NAMESPACE_OPENFOODFACTS, 'search_' . md5($query), $results, CacheService::TTL_MEDIUM);

            return $results;
        } catch (\Exception $e)
        {
            return ['error' => 'Erreur lors de la recherche: ' . $e->getMessage()];
        }
    }

    /**
     * Récupère les détails d'un produit spécifique.
     */
    public function getProduct(string $barcode): array
    {
        // Vérifier le cache
        $cachedResult = $this->cache->get(CacheService::NAMESPACE_OPENFOODFACTS, 'product_' . md5($barcode));
        if ($cachedResult !== null)
        {
            return $cachedResult;
        }

        try
        {
            $url = self::PRODUCT_API_URL . urlencode($barcode) . '.json';

            // Appel API avec cURL
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 30, // Timeout augmenté à 30 secondes
                CURLOPT_CONNECTTIMEOUT => 10, // Timeout de connexion augmenté à 10 secondes
                CURLOPT_USERAGENT => 'SuiviNash/1.0',
                CURLOPT_HTTPHEADER => [
                    'Accept: application/json',
                    'Content-Type: application/json',
                ],
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 3,
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);

            if ($response === false)
            {
                return ['error' => 'Erreur de connexion à l\'API OpenFoodFacts: ' . $curlError];
            }

            if ($httpCode !== 200)
            {
                return ['error' => 'Produit non trouvé (HTTP ' . $httpCode . ')'];
            }

            $data = json_decode($response, true);

            if ($data === null || !isset($data['product']))
            {
                return ['error' => 'Produit non trouvé'];
            }

            $product = $data['product'];

            $result = [
                'name' => $product['product_name'] ?? 'Nom inconnu',
                'brands' => $product['brands'] ?? '',
                'image' => $product['image_url'] ?? '',
                'barcode' => $barcode,
                'nutriments' => $this->normalizeNutriments($product['nutriments'] ?? []),
                'ingredients' => $product['ingredients_text'] ?? '',
                'labels' => $product['labels'] ?? '',
                'categories' => $product['categories'] ?? '',
            ];

            // Mettre en cache le résultat
            $this->cache->set(CacheService::NAMESPACE_OPENFOODFACTS, 'product_' . md5($barcode), $result, CacheService::TTL_MEDIUM);

            return $result;
        } catch (\Exception $e)
        {
            return ['error' => 'Erreur lors de la récupération du produit: ' . $e->getMessage()];
        }
    }

    /**
     * Normalise les données nutritionnelles.
     */
    private function normalizeNutriments(array $nutriments): array
    {
        $normalized = [];

        // Mapping des clés OpenFoodFacts vers nos clés standard
        $mapping = [
            'energy-kcal' => 'energy-kcal',
            'energy-kcal_100g' => 'energy-kcal_100g',
            'proteins' => 'proteins',
            'proteins_100g' => 'proteins_100g',
            'carbohydrates' => 'carbohydrates',
            'carbohydrates_100g' => 'carbohydrates_100g',
            'fat' => 'fat',
            'fat_100g' => 'fat_100g',
            'fiber' => 'fiber',
            'fiber_100g' => 'fiber_100g',
            'sugars' => 'sugars',
            'sugars_100g' => 'sugars_100g',
            'saturated-fat' => 'saturated-fat',
            'saturated-fat_100g' => 'saturated-fat_100g',
            'salt' => 'salt',
            'salt_100g' => 'salt_100g',
        ];

        foreach ($mapping as $apiKey => $ourKey)
        {
            if (isset($nutriments[$apiKey]))
            {
                $normalized[$ourKey] = (float)$nutriments[$apiKey];
            }
        }

        return $normalized;
    }
}
