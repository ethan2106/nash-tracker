<?php

use App\Repository\FoodRepositoryInterface;
use App\Service\CacheService;
use App\Service\FoodDataService;
use PHPUnit\Framework\TestCase;

class FoodDataServiceTest extends TestCase
{
    private $foodRepositoryMock;

    private $cacheMock;

    private $foodDataService;

    protected function setUp(): void
    {
        $this->foodRepositoryMock = $this->createMock(FoodRepositoryInterface::class);
        $this->cacheMock = $this->createMock(CacheService::class);
        $this->foodDataService = new FoodDataService($this->foodRepositoryMock, $this->cacheMock);
    }

    /**
     * Test récupération des aliments sauvegardés avec cache.
     */
    public function testGetSavedFoodsUsesCache()
    {
        $expectedFoods = [['id' => 1, 'nom' => 'Test Food']];

        $this->cacheMock->expects($this->once())
            ->method('remember')
            ->with('foods', 'foods_saved__0', $this->isCallable())
            ->willReturnCallback(function ($namespace, $key, $callback)
            {
                return $callback();
            });

        $this->foodRepositoryMock->expects($this->once())
            ->method('getSavedFoods')
            ->with(null, 0)
            ->willReturn($expectedFoods);

        $result = $this->foodDataService->getSavedFoods();

        $this->assertEquals($expectedFoods, $result);
    }

    /**
     * Test récupération avec pagination.
     */
    public function testGetSavedFoodsWithPagination()
    {
        $limit = 10;
        $offset = 20;
        $expectedFoods = [['id' => 1, 'nom' => 'Test Food']];

        $this->cacheMock->expects($this->once())
            ->method('remember')
            ->with('foods', 'foods_saved_10_20', $this->isCallable())
            ->willReturnCallback(function ($namespace, $key, $callback)
            {
                return $callback();
            });

        $this->foodRepositoryMock->expects($this->once())
            ->method('getSavedFoods')
            ->with($limit, $offset)
            ->willReturn($expectedFoods);

        $result = $this->foodDataService->getSavedFoods($limit, $offset);

        $this->assertEquals($expectedFoods, $result);
    }

    /**
     * Test recherche d'aliments avec cache.
     */
    public function testSearchSavedFoodsUsesCache()
    {
        $query = 'test query';
        $expectedResults = [['id' => 1, 'nom' => 'Test Food']];

        $this->cacheMock->expects($this->once())
            ->method('remember')
            ->with('foods', $this->matchesRegularExpression('/^foods_search_[a-f0-9]{32}$/'), $this->isCallable())
            ->willReturnCallback(function ($namespace, $key, $callback)
            {
                return $callback();
            });

        $this->foodRepositoryMock->expects($this->once())
            ->method('searchSavedFoods')
            ->with($query)
            ->willReturn($expectedResults);

        $result = $this->foodDataService->searchSavedFoods($query);

        $this->assertEquals($expectedResults, $result);
    }

    /**
     * Test comptage des aliments avec cache.
     */
    public function testCountSavedFoodsUsesCache()
    {
        $expectedCount = 42;

        $this->cacheMock->expects($this->once())
            ->method('remember')
            ->with('foods', 'foods_count', $this->isCallable())
            ->willReturnCallback(function ($namespace, $key, $callback)
            {
                return $callback();
            });

        $this->foodRepositoryMock->expects($this->once())
            ->method('countSavedFoods')
            ->willReturn($expectedCount);

        $result = $this->foodDataService->countSavedFoods();

        $this->assertEquals($expectedCount, $result);
    }

    /**
     * Test récupération des données de catalogue.
     */
    public function testGetCatalogData()
    {
        $page = 2;
        $perPage = 12;
        $totalFoods = 50;
        $foods = [
            ['id' => 1, 'nom' => 'Food 1'],
            ['id' => 2, 'nom' => 'Food 2'],
        ];

        $this->cacheMock->method('remember')
            ->willReturnCallback(function ($namespace, $key, $callback)
            {
                return $key === 'foods_count' ? 50 : $callback();
            });

        $this->foodRepositoryMock->expects($this->once())
            ->method('getSavedFoods')
            ->with(12, 12) // (page-1) * perPage = 12
            ->willReturn($foods);

        $result = $this->foodDataService->getCatalogData($page, $perPage);

        $this->assertEquals($foods, $result['foods']);
        $this->assertEquals(50, $result['totalFoods']);
        $this->assertEquals(5, $result['totalPages']); // ceil(50/12) = 5
        $this->assertEquals(2, $result['currentPage']);
        $this->assertEquals(12, $result['perPage']);
    }

    /**
     * Test invalidation du cache.
     */
    public function testInvalidateCache()
    {
        $this->cacheMock->expects($this->once())
            ->method('clearNamespace')
            ->with('foods');

        $this->foodDataService->invalidateCache();
    }
}
