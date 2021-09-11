<?php

  namespace Neu\Model;

  /**
   * @package Neu\Model
   */
  final class TransformGroup {
    public function __construct(
      public \ReflectionProperty $property,
      public object $source,
      public mixed $value = null,
      public string $name = '',
      public array $modelData = [],
      public bool $ignoreField = false,
    ) {
    }
  }
