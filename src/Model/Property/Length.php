<?php

  namespace Neu\Model\Property;

  use Attribute;
  use Neu\Model\Exceptions\InvalidConfiguration;
  use Neu\Model\Exceptions\ValidationError;
  use Neu\Model\ModelAttribute;
  use Neu\Model\TransformGroup;

  /**
   * @package Neu\Model\Property
   */
  #[Attribute(Attribute::TARGET_PROPERTY)]
  class Length implements ModelAttribute {
    /**
     * @param int $min
     * @param int $max
     * @param int $exact
     * @throws InvalidConfiguration
     */
    public function __construct(
      private int $min = -1,
      private int $max = -1,
      private int $exact = -1,
    ) {
      if ($exact !== -1) {
        if ($exact < 1) {
          throw new InvalidConfiguration('Provide an exact length of at least 1!');
        }
        if ($max !== -1 || $min !== -1) {
          throw new InvalidConfiguration('If an exact length is given, the "min" and "max" parameters must be omitted!');
        }
      } else {
        $err = [];
        if ($min !== -1 && $min < 0) {
          $err[] = 'Please provide a minimum length of at least 0 in the configuration, ' . $min . ' given!';
        }
        if ($max !== -1 && $max < 1) {
          $err[] = 'Please provide a maximum length of at least 1 in the configuration, ' . $max . ' given!';
        }
        if ($err) {
          throw new InvalidConfiguration(join(' ', $err));
        }
      }
      if ($min === $max && $max === $exact && $exact === -1) {
        throw new InvalidConfiguration('Please set at least one of the parameters "min", "max", or "exact"!');
      }
    }

    /**
     * @throws ValidationError
     */
    private function validateLength(string $str): void {
      $valueLength = strlen($str);
      $errors = [];
      if ($this->exact !== -1) {
        if ($valueLength !== $this->exact) {
          $errors[] = 'Length must be exactly ' . $this->exact . ', but was ' . $valueLength . ' instead!';
        }
      } else {
        if ($this->min !== -1 && $valueLength < $this->min) {
          $errors[] = 'Length must be at least ' . $this->min . ', but was ' . $valueLength . ' instead!';
        }
        if ($this->max !== -1 && $valueLength > $this->max) {
          $errors[] = 'Length must be at most ' . $this->max . ', but was ' . $valueLength . ' instead!';
        }
      }
      if ($errors) {
        throw new ValidationError($errors);
      }
    }

    /**
     * @param TransformGroup $group
     * @throws ValidationError
     */
    function deserialize(TransformGroup $group): void {
      if (!is_string($group->value)) {
        throw new \InvalidArgumentException('Cannot check length of types other than string, "' . get_debug_type($group->value) . '" given!');
      }
      $this->validateLength($group->value);
    }

    /**
     * @param TransformGroup $group
     * @throws ValidationError
     */
    function serialize(TransformGroup $group): void {
      // TODO is this actually needed?
//      if (!is_string($group->value)) {
//        throw new \InvalidArgumentException('Cannot check length of types other than string, "' . get_debug_type($group->value) . '" given!');
//      }
      $this->validateLength($group->value);
    }
  }
