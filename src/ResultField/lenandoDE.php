<?php

namespace ElasticExportlenandoDE\ResultField;

use Plenty\Modules\Cloud\elasticSearch\Lib\ElasticSearch;
use Plenty\Modules\DataExchange\Contracts\ResultFields;
use Plenty\Modules\DataExchange\Models\FormatSetting;
use Plenty\Modules\Helper\Services\ArrayHelper;
use Plenty\Modules\Item\Search\Mutators\BarcodeMutator;
use Plenty\Modules\Item\Search\Mutators\ImageMutator;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Source\Mutator\BuiltIn\LanguageMutator;
use Plenty\Modules\Item\Search\Mutators\KeyMutator;
use Plenty\Modules\Item\Search\Mutators\SkuMutator;
use Plenty\Modules\Item\Search\Mutators\DefaultCategoryMutator;

class lenandoDE extends ResultFields {
  const LENANDO_DE = 116.00;

  private $arrayHelper;

  public function __construct(ArrayHelper $arrayHelper)
  {
    $this->arrayHelper = $arrayHelper;
  }

  public function generateResultFields(array $formatSettings = []): array
  {
    $settings = $this->arrayHelper->buildMapFromObjectList($formatSettings, 'key', 'value');
    $reference = $settings->get('referrerId') ? $settings->get('referrerId') : self::LENANDO_DE;

    $this->setOrderByList(['item.id', ElasticSearch::SORTING_ORDER_ASC]);

    $itemDescriptionFields = ['texts.urlPath', 'texts.lang', 'texts.keywords'];

    switch($settings->get('nameId')) {
      case 1:
        $itemDescriptionFields[] = 'texts.name1';
        break;
      case 2:
        $itemDescriptionFields[] = 'texts.name2';
        break;
      case 3:
        $itemDescriptionFields[] = 'texts.name3';
        break;
      default:
        $itemDescriptionFields[] = 'texts.name1';
    }

    if ( $settings->get('descriptionType') == 'itemShortDescription' || $settings->get('previewTextType') == 'itemShortDescription' ) {
      $itemDescriptionFields[] = 'texts.shortDescription';
    }

    if ( $settings->get('descriptionType') == 'itemDescription' || $settings->get('descriptionType') == 'itemDescriptionAndTechnicalData' || $settings->get('previewTextType') == 'itemDescription' || $settings->get('previewTextType') == 'itemDescriptionAndTechnicalData' ) {
      $itemDescriptionFields[] = 'texts.description';
    }

    $itemDescriptionFields[] = 'texts.technicalData';

    $imageMutator = pluginApp(ImageMutator::class);

    if ( $imageMutator instanceof ImageMutator ) {
      $imageMutator->addMarket($reference);
    }

    $keyMutator = pluginApp(KeyMutator::class);

    if ( $keyMutator instanceof KeyMutator ) {
      $keyMutator->setKeyList($this->getKeyList());
      $keyMutator->setNestedKeyList($this->getNestedKeyList());
    }

    $LanguageMutator = pluginApp(LanguageMutator::class, [[$settings->get('lang')]]);
    $skuMutator = pluginApp(SkuMutator::class);

    if ( $skuMutator instanceof SkuMutator ) {
      $skuMutator->setMarket($reference);
    }

    $defaultCategoryMutator = pluginApp(DefaultCategoryMutator::class);

    if ( $defaultCategoryMutator instanceof DefaultCategoryMutator ) {
      $defaultCategoryMutator->setPlentyId($settings->get('plentyId'));
    }

    $barcodeMutator = pluginApp(BarcodeMutator::class);

    if ( $barcodeMutator instanceof BarcodeMutator ) {
      $barcodeMutator->addMarket($reference);
    }

    $fields = [
      [
        'item.id',
        'item.manufacturer.id',
        'item.free1',
        'item.free2',
        'item.free3',
        'item.free4',
        'item.free5',
        'item.free6',
        'item.free7',
        'item.free8',
        'item.free9',
        'item.free10',
        'item.storeSpecial',
        'id',
        'variation.availability.id',
        'variation.stockLimitation',
        'variation.vatId',
        'variation.model',
        'variation.weightG',
        'variation.number',
        'images.item.urlMiddle',
        'images.item.urlPreview',
        'images.item.urlSecondPreview',
        'images.item.url',
        'images.item.path',
        'images.item.position',
        'images.variation.urlMiddle',
        'images.variation.urlPreview',
        'images.variation.urlSecondPreview',
        'images.variation.url',
        'images.variation.path',
        'images.variation.position',
        'unit.content',
        'unit.id',
        'skus.sku',
        'defaultCategories.id',
        'attributes.attributeValueSetId',
        'attributes.attributeId',
        'attributes.valueId',
        'attributes.names.name',
        'attributes.names.lang',
        'barcodes.code',
        'barcodes.type',
        'properties.property.id',
        'properties.property.valueType',
        'properties.selection.name',
        'properties.selection.lang',
        'properties.texts.value',
        'properties.texts.lang',
        'properties.valueInt',
        'properties.valueFloat',
      ],
      [
        $keyMutator,
        $LanguageMutator,
        $skuMutator,
        $defaultCategoryMutator,
        $barcodeMutator,
      ]
    ];

    if ( $reference != -1 ) {
      $fields[1][] = $imageMutator;
    }

    foreach ( $itemDescriptionFields as $itemDescriptionField ) {
      $fields[0][] = $itemDescriptionField;
    }

    return $fields;
  }

  private function getKeyList()
  {
    return [
      'item.id',
      'item.manufacturer.id',
      'item.free1',
      'item.free2',
      'item.free3',
      'item.free4',
      'item.free5',
      'item.free6',
      'item.free7',
      'item.free8',
      'item.free9',
      'item.free10',
      'item.storeSpecial',
      'variation.availability.id',
      'variation.stockLimitation',
      'variation.vatId',
      'variation.model',
      'variation.weightG',
      'unit.content',
      'unit.id',
    ];
  }

  private function getNestedKeyList()
  {
    $nestedKeyList['keys'] = [
      'images.item',
      'images.variation',
      'texts',
      'defaultCategories',
      'barcodes',
      'attributes',
      'properties',
    ];

    $nestedKeyList['nestedKeys'] = [
      'images.item' => [
        'urlMiddle',
        'urlPreview',
        'urlSecondPreview',
        'url',
        'path',
        'position',
      ],
      'image.variation' => [
        'urlMiddle',
        'urlPreview',
        'urlSecondPreview',
        'url',
        'path',
        'position',
      ],
      'texts' => [
        'urlPath',
        'lang',
        'texts.keywords',
        'name1',
        'name2',
        'name3',
        'shortDescription',
        'description',
        'technicalData',
      ],
      'defaultCategories' => [
        'id',
      ],
      'barcodes' => [
        'code',
        'type',
      ],
      'attributes' => [
        'attributeValueSetId',
        'attributeId',
        'valueId',
        'names.name',
        'names.lang',
      ],
      'properties' => [
        'property.id',
        'property.valueType',
        'selection.name',
        'selection.lang',
        'texts.value',
        'texts.lang',
        'valueInt',
        'valueFloat',
      ],
    ];

    return $nestedKeyList;
  }
}
