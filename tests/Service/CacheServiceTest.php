<?php

namespace Tests\Service;

use App\Service\CacheService;
use PHPUnit\Framework\TestCase;

/**
 * Tests pour CacheService
 * Teste toutes les fonctionnalités de cache avec isolation par répertoire temporaire
 */
class CacheServiceTest extends TestCase
{
    private CacheService $cacheService;

    protected function setUp(): void
    {
        $this->cacheService = new CacheService();

        // Forcer l'activation du cache pour les tests
        $this->cacheService->setEnabled(true);

        // Nettoyer le cache avant chaque test
        $this->cacheService->clearAll();
    }

    protected function tearDown(): void
    {
        // Nettoyer le cache après chaque test
        $this->cacheService->clearAll();
    }

    // Tests pour isEnabled/setEnabled

    public function testIsEnabledDefault(): void
    {
        $cacheService = new CacheService();
        // En environnement de test (APP_ENV=testing), le cache devrait être activé par défaut
        $this->assertTrue($cacheService->isEnabled());
    }

    public function testSetEnabled(): void
    {
        $this->cacheService->setEnabled(false);
        $this->assertFalse($this->cacheService->isEnabled());

        $this->cacheService->setEnabled(true);
        $this->assertTrue($this->cacheService->isEnabled());
    }

    // Tests pour get/set

    public function testSetAndGet(): void
    {
        $namespace = 'test';
        $key = 'test_key';
        $value = 'test_value';

        // Vérifier que la clé n'existe pas
        $this->assertNull($this->cacheService->get($namespace, $key));

        // Stocker une valeur
        $result = $this->cacheService->set($namespace, $key, $value);
        $this->assertTrue($result);

        // Récupérer la valeur
        $retrieved = $this->cacheService->get($namespace, $key);
        $this->assertEquals($value, $retrieved);
    }

    public function testGetWithDisabledCache(): void
    {
        $this->cacheService->setEnabled(false);

        $namespace = 'test';
        $key = 'test_key';
        $value = 'test_value';

        // Stocker ne devrait rien faire
        $this->cacheService->set($namespace, $key, $value);

        // Récupérer devrait retourner null
        $this->assertNull($this->cacheService->get($namespace, $key));
    }

    public function testSetWithComplexData(): void
    {
        $namespace = 'test';
        $key = 'complex_key';
        $value = [
            'array' => [1, 2, 3],
            'object' => (object)['prop' => 'value'],
            'string' => 'test',
            'number' => 42
        ];

        $this->cacheService->set($namespace, $key, $value);
        $retrieved = $this->cacheService->get($namespace, $key);

        $this->assertEquals($value, $retrieved);
    }

    // Tests pour delete

    public function testDelete(): void
    {
        $namespace = 'test';
        $key = 'delete_key';
        $value = 'delete_value';

        // Stocker puis supprimer
        $this->cacheService->set($namespace, $key, $value);
        $this->assertEquals($value, $this->cacheService->get($namespace, $key));

        $result = $this->cacheService->delete($namespace, $key);
        $this->assertTrue($result);

        $this->assertNull($this->cacheService->get($namespace, $key));
    }

    public function testDeleteNonExistentKey(): void
    {
        $result = $this->cacheService->delete('test', 'non_existent');
        $this->assertTrue($result); // Symfony cache retourne true même si la clé n'existe pas
    }

    // Tests pour clearNamespace

    public function testClearNamespace(): void
    {
        $namespace = 'test_namespace';

        // Stocker plusieurs clés dans le même namespace
        $this->cacheService->set($namespace, 'key1', 'value1');
        $this->cacheService->set($namespace, 'key2', 'value2');
        $this->cacheService->set('other_namespace', 'key3', 'value3');

        // Vérifier qu'elles existent
        $this->assertEquals('value1', $this->cacheService->get($namespace, 'key1'));
        $this->assertEquals('value2', $this->cacheService->get($namespace, 'key2'));
        $this->assertEquals('value3', $this->cacheService->get('other_namespace', 'key3'));

        // Vider le namespace
        $result = $this->cacheService->clearNamespace($namespace);
        $this->assertTrue($result);

        // Vérifier que les clés du namespace sont supprimées
        $this->assertNull($this->cacheService->get($namespace, 'key1'));
        $this->assertNull($this->cacheService->get($namespace, 'key2'));

        // Mais pas celles d'autres namespaces
        $this->assertEquals('value3', $this->cacheService->get('other_namespace', 'key3'));
    }

    // Tests pour clearAll

    public function testClearAll(): void
    {
        // Stocker dans différents namespaces
        $this->cacheService->set('ns1', 'key1', 'value1');
        $this->cacheService->set('ns2', 'key2', 'value2');

        // Vérifier qu'elles existent
        $this->assertEquals('value1', $this->cacheService->get('ns1', 'key1'));
        $this->assertEquals('value2', $this->cacheService->get('ns2', 'key2'));

        // Vider tout le cache
        $result = $this->cacheService->clearAll();
        $this->assertTrue($result);

        // Vérifier que tout est supprimé
        $this->assertNull($this->cacheService->get('ns1', 'key1'));
        $this->assertNull($this->cacheService->get('ns2', 'key2'));
    }

    // Tests pour remember

    public function testRememberWithCallback(): void
    {
        $namespace = 'test';
        $key = 'remember_key';
        $callCount = 0;

        $callback = function() use (&$callCount) {
            $callCount++;
            return 'computed_value_' . $callCount;
        };

        // Premier appel - devrait exécuter le callback
        $result1 = $this->cacheService->remember($namespace, $key, $callback);
        $this->assertEquals('computed_value_1', $result1);
        $this->assertEquals(1, $callCount);

        // Deuxième appel - devrait retourner la valeur cachée
        $result2 = $this->cacheService->remember($namespace, $key, $callback);
        $this->assertEquals('computed_value_1', $result2);
        $this->assertEquals(1, $callCount); // Callback n'a pas été appelé à nouveau
    }

    public function testRememberWithTtl(): void
    {
        $namespace = 'test';
        $key = 'ttl_key';

        $callCount = 0;
        $callback = function() use (&$callCount) {
            $callCount++;
            return 'ttl_value_' . $callCount;
        };

        // Stocker avec TTL très court (1 seconde)
        $result1 = $this->cacheService->remember($namespace, $key, $callback, 1);
        $this->assertEquals('ttl_value_1', $result1);

        // Attendre que le TTL expire
        sleep(2);

        // Le cache devrait avoir expiré
        $result2 = $this->cacheService->remember($namespace, $key, $callback, 1);
        $this->assertEquals('ttl_value_2', $result2);
        $this->assertEquals(2, $callCount);
    }

    public function testRememberWithDisabledCache(): void
    {
        $this->cacheService->setEnabled(false);

        $callCount = 0;
        $callback = function() use (&$callCount) {
            $callCount++;
            return 'no_cache_value';
        };

        // Avec cache désactivé, le callback devrait être appelé à chaque fois
        $result1 = $this->cacheService->remember('test', 'key', $callback);
        $this->assertEquals('no_cache_value', $result1);

        $result2 = $this->cacheService->remember('test', 'key', $callback);
        $this->assertEquals('no_cache_value', $result2);

        $this->assertEquals(2, $callCount);
    }

    // Tests pour les constantes TTL

    public function testTtlConstants(): void
    {
        $this->assertEquals(300, CacheService::TTL_SHORT);
        $this->assertEquals(1800, CacheService::TTL_MEDIUM);
        $this->assertEquals(3600, CacheService::TTL_LONG);
        $this->assertEquals(86400, CacheService::TTL_DAY);
        $this->assertEquals(604800, CacheService::TTL_WEEK);
    }

    // Tests pour les namespaces prédéfinis

    public function testNamespaceConstants(): void
    {
        $this->assertEquals('openfoodfacts', CacheService::NAMESPACE_OPENFOODFACTS);
    }
}