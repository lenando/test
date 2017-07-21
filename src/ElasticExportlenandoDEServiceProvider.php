<?php

namespace ElasticExportlenandoDE;

use Plenty\Modules\DataExchange\Services\ExportPresetContainer;
use Plenty\Plugin\DataExchangeServiceProvider;

class ElasticExportlenandoDEServiceProvider extends DataExchangeServiceProvider {

  public function register()
  {

  }

  public function exports(ExportPresetContainer $container)
  {
    $container->add(
      'lenandoDE-Plugin',
      'ElasticExportlenandoDE\ResultField\lenandoDE',
      'ElasticExportlenandoDE\Generator\lenandoDE',
      '',
      true,
      true
    );
  }

}
