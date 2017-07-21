<?php

namespace ElasticExportlenandoDE\Helper;

use Plenty\Modules\Item\Property\Contracts\PropertyMarketReferenceRepositoryContract;
use Plenty\Modules\Item\Property\Contracts\PropertyNameRepositoryContract;
use Plenty\Modules\Item\Property\Models\PropertyMarketReference;
use Plenty\Modules\Item\Property\Models\PropertyName;
use Plenty\Plugin\Log\Loggable;

class PropertyHelper {
  use Loggable;

  const LENANDO_DE = 116.00;
  const PROPERTY_TYPE_TEXT = 'text';
  const PROPERTY_TYPE_SELECTION = 'selection';
  const PROPERTY_TYPE_EMPTY = 'empty';
  const PROPERTY_TYPE_INT = 'int';
  const PROPERTY_TYPE_FLOAT = 'float';

  private $itemPropertyCache = [];
  private $propertyNameRepository;
  private $propertyMarketReferenceRepository;

  public function __construct(
    PropertyNameRepositoryContract $propertyNameRepository,
    PropertyMarketReferenceRepositoryContract $propertyMarketReferenceRepository
  ) {
    $this->propertyNameRepository = $propertyNameRepository;
    $this->propertyMarketReferenceRepository = $propertyMarketReferenceRepository;
  }

  public function getPropertyListDescription($variation, string $lang = 'de')
  {
    $properties = $this->getItemPropertyList($variation, $lang);
    $propertyDescription = '';

    foreach ( $properties as $property ) {
      $propertyDescription .= '<br />'.$property;
    }

    return $propertyDescription;
  }

  private function getItemPropertyList($variation, string $lang = 'de'): array
  {
    if ( ! array_key_exists($variation['data']['item']['id'], $this->itemPropertyCache) ) {
      $list = [];

      foreach ( $variation['data']['properties'] as $property ) {
        if ( ! is_null($property['property']['id']) && $property['property']['valueType'] != 'file' ) {
          $propertyName = $this->propertyNameRepository->findOne($property['property']['id'], $lang);
          $propertyMarketReference = $this->propertyMarketReferenceRepository->findOne($property['property']['id'], self::LENANDO_DE);

          if ( ! ( $propertyName instanceof PropertyName ) || ! ( $propertyMarketReference instanceof PropertyMarketReference) || is_null($propertyName) || is_null($propertyMarketReference) || $propertyMarketReference->componentId === 0 || $property['property']['valueType'] === self::PROPERTY_TYPE_EMPTY ) {
            $this->getLogger(__METHOD__)->debug('ElasticExportlenandoDE::item.variationPropertyNotAdded', [
              'ItemId' => $variation['data']['item']['id'],
              'VariationId' => $variation['id'],
              'Property' => $property,
              'ExternalComponent' => $propertyMarketReference->externalComponent,
            ]);

            continue;
          }
          elseif ( $property['property']['valueType'] == self::PROPERTY_TYPE_TEXT ) {
            if ( is_array($property['texts']) ) {
              $list[(string) $propertyMarketReference->propertyId] = $property['texts']['value'];
            }
          }
          elseif ( $property['property']['valueType'] == self::PROPERTY_TYPE_SELECTION ) {
            if ( is_array($property['selection']) ) {
              $list[(string) $propertyMarketReference->propertyId] = $property['selection']['name'];
            }
          }
          elseif ( $property['property']['valueType'] == self::PROPERTY_TYPE_INT ) {
            if ( ! is_null($property['valueInt']) ) {
              $list[(string) $propertyMarketReference->propertyId] = $property['valueInt'];
            }
          }
          elseif ( $property['property']['valueType'] == self::PROPERTY_TYPE_FLOAT ) {

            if ( ! is_null($property['valueFloat']) ) {
              $list[(string) $propertyMarketReference->propertyId] = $property['valueFloat'];
            }
          }
        }
      }

      $this->itemPropertyCache[$variation['data']['item']['id']] = $list;
      $this->getLogger(__METHOD__)->debug('ElasticExportlenandoDE::item.variationPropertyList', [
        'ItemId' => $variation['data']['item']['id'],
        'VariationId' => $variation['id'],
        'PropertyList' => count($list) > 0 ? $list : 'no properties',
      ]);
    }

    return $this->itemPropertyCache[$variation['data']['item']['id']];
  }
}
