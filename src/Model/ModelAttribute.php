<?php

  namespace Neu\Model;

  use Closure;
  use ReflectionProperty;

  /**
   * @package Neu\Model
   */
  interface ModelAttribute {
    function deserialize(TransformGroup $group): void;
    function serialize(TransformGroup $group): void;
  }
