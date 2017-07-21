<?php

namespace ElasticExportlenandoDE\Helper;

use Illuminate\Support\Collection;
use Plenty\Modules\StockManagement\Stock\Contracts\StockRepositoryContract;
use Plenty\Modules\StockManagement\Stock\Models\Stock;
use Plenty\Plugin\Log\Loggable;
use Plenty\Repositories\Models\PaginatedResult;

class StockHelper {
  use Loggable;

  const STOCK_WAREHOUSE_TYPE = 'sales';
  const STOCK_AVAILABLE_LIMITED = 0;
  const STOCK_AVAILABLE_NOT_LIMITED = 1;
  const STOCK_NOT_AVAILABLE = 2;
  const STOCK_MAXIMUM_VALUE = 0;

  private $stockRepository;
  private $marketHelper;

  public function __construct(StockRepositoryContract $stockRepository, MarketHelper $marketHelper)
  {
    $this->stockRepository = $stockRepository;
    $this->marketHelper = $marketHelper;
  }

  public function getStock($variation): int
  {
    $stockNet = 0;

    if ( $this->stockRepository instanceof StockRepositoryContract ) {
      $this->stockRepository->setFilters(['variationId' => $variation['id']]);
      $stockResult = $this->stockRepository->listStockByWarehouseType(self::STOCK_WAREHOUSE_TYPE, ['stockNet'], 1, 1);

      if ( $stockResult instanceof PaginatedResult ) {
        $result = $stockResult->getResult();

        if ( $result instanceof Collection ) {
          foreach ($result as $model) {
            if ( $model instanceof Stock ) {
              $stockNet = (int) $model->stockNet;
            }
          }
        }
      }
    }

    $stock = self::STOCK_MAXIMUM_VALUE;

    if ( $this->marketHelper->getConfigValue('stockCondition') != 'N' ) {
      if ( $variation['data']['variation']['stockLimitation'] == self::STOCK_AVAILABLE_NOT_LIMITED && $stockNet > 0 ) {
        if ( $stockNet > 999 ) {
          $stock = 999;
        }
        else {
          $stock = $stockNet;
        }
      }
      elseif ( $variation['data']['variation']['stockLimitation'] == self::STOCK_AVAILABLE_LIMITED && $stockNet > 0 ) {
        if ( $stockNet > 999 ) {
          $stock = 999;
        }
        else {
          $stock = $stockNet;
        }
      }
    }

    return $stock;
  }

  public function isValid($variation): bool
  {
    $stock = $this->getStock($variation);

    if ( $this->marketHelper->getConfigValue('stockCondition') != 'N' && $stock <= 0 ) {
      return false;
    }

    return true;
  }
}
