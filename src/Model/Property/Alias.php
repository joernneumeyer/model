<?php

  namespace Neu\Model\Property;

  use Attribute;
  use Neu\Model\ModelAttribute;
  use Neu\Model\TransformGroup;

  /**
   * @package Neu\Model\Property
   */
  #[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
  final class Alias implements ModelAttribute {
    public function __construct(
      public string $name
    ) {
    }

    function deserialize(TransformGroup $group): void {
      $group->value = $group->modelData[$this->name];
    }

    function serialize(TransformGroup $group): void {
      $group->name = $this->name;
    }
  }
