<?php

  namespace Neu\Model\Property;

  use InvalidArgumentException;
  use Neu\Model\ModelAttribute;
  use Neu\Model\ModelParser;
  use Neu\Model\TransformGroup;

  #[\Attribute(\Attribute::TARGET_PROPERTY)]
  class ArrayOf implements ModelAttribute {
    private static array $scalar_types = ['int', 'string', 'bool', 'float'];

    private static function parser() {
      static $parser;
      if (!$parser) {
        $parser = new ModelParser();
      }
      return $parser;
    }

    public function __construct(
      private string $type,
    ) {
      if (!class_exists($this->type) && !in_array($this->type, self::$scalar_types)) {
        throw new InvalidArgumentException('Cannot parse an array of type "' . $type . '"!');
      }
    }

    function deserialize(TransformGroup $group): void {
      if (class_exists($this->type)) {
        $group->value = array_map(fn($data) => self::parser()->deserialize($data, $this->type), $group->value);
      }
    }

    function serialize(TransformGroup $group): void {
      $group->value = array_map(fn($obj) => in_array(get_debug_type($obj), self::$scalar_types) ? $obj : self::parser()->serialize($obj), $group->value);
    }
  }
