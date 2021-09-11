<?php

  namespace Neu\Model\Property;

  use Attribute;
  use Neu\Model\ModelAttribute;
  use Neu\Model\TransformGroup;

  /**
   * @package Neu\Model\Property
   */
  #[Attribute(Attribute::TARGET_PROPERTY)]
  class Ignore implements ModelAttribute {
    function deserialize(TransformGroup $group): void {
      $group->ignoreField = true;
    }

    function serialize(TransformGroup $group): void {
      $group->ignoreField = true;
    }
  }
