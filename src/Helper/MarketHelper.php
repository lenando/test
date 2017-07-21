<?php

namespace ElasticExportlenandoDE\Helper;

use ElasticExport\Helper\ElasticExportCoreHelper;
use Plenty\Plugin\Log\Loggable;

class MarketHelper {
  use Loggable;

  private $elasticExportHelper;
  private $configCache = [];

  public function __construct(ElasticExportCoreHelper $elasticExportHelper)
  {
    $this->elasticExportHelper = $elasticExportHelper;
  }

  public function getConfig($market = 'market.kauflux'): array
  {
    if ( is_array($this->configCache) && empty($this->configCache) ) {
      $this->configCache = $this->elasticExportHelper->getConfig($market);
    }

    return $this->configCache;
  }

  public function getConfigValue(string $key): string
  {
    $config = $this->getConfig();

    if ( is_array($config) && array_key_exists($key, $config) ) {
      return (string) $config[$key];
    }

    return '';
  }
}
