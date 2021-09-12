<?php

  namespace Neu\Model;

  use ReflectionProperty;

  /**
   * @package Neu\Model
   */
  final class TransformGroup {
    /**
     * @param ReflectionProperty $property
     * @param object $source
     * @param mixed|null $value
     * @param string $name
     * @param array<string, mixed> $modelData
     * @param bool $ignoreField
     */
    public function __construct(
      public ReflectionProperty $property,
      public object $source,
      public mixed $value = null,
      public string $name = '',
      public array $modelData = [],
      public bool $ignoreField = false,
    ) {
    }
  }
