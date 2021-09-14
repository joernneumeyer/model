<?php

  namespace Neu\Model;

  use Closure;
  use Neu\Model\Exceptions\ValidationError;
  use ReflectionProperty;

  /**
   * @package Neu\Model
   */
  interface ModelAttribute {
    /**
     * @param TransformGroup $group
     * @throws ValidationError
     */
    function deserialize(TransformGroup $group): void;
    /**
     * @param TransformGroup $group
     * @throws ValidationError
     */
    function serialize(TransformGroup $group): void;
  }
