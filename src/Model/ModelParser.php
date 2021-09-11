<?php

  namespace Neu\Model;

  use InvalidArgumentException;
  use ReflectionClass;
  use ReflectionObject;
  use ReflectionProperty;

  /**
   * @package Neu\Model
   */
  final class ModelParser {
    /**
     * @param ReflectionProperty $property
     * @return ModelAttribute[]
     */
    private static function propertyModelAttributes(ReflectionProperty $property): array {
      $processorRefs = array_filter($property->getAttributes(), fn($attr) => is_subclass_of($attr->getName(), ModelAttribute::class));
      return array_map(fn($ref) => $ref->newInstance(), $processorRefs);
    }

    public function serialize(object $obj): array {
      $data = [];
      $ref = new ReflectionObject($obj);
      foreach ($ref->getProperties() as $property) {
        $property->setAccessible(true);
        $group = new TransformGroup(property: $property, source: $obj, value: $property->getValue($obj), name: $property->getName());
        if (class_exists($property->getType()?->getName())) {
          $group->value = self::serialize($group->value);
        }
        foreach (self::propertyModelAttributes($property) as $processor) {
          $processor->serialize($group);
        }
        if ($group->ignoreField) continue;
        $data[$group->name] = $group->value;
      }
      return $data;
    }

    public function deserialize(array $data, string $type): object {
      if (!class_exists($type)) {
        throw new InvalidArgumentException('Cannot deserialize array to an object of undefined type "' . $type . '"!');
      }
      $ref = new ReflectionClass($type);
      $ctorArgsCount = $ref->getConstructor()?->getNumberOfRequiredParameters();
      if ($ctorArgsCount !== null && $ctorArgsCount > 0) {
        throw new InvalidArgumentException('Cannot deserialize data into an object of type "' . $type . '" as its constructor is non-trivial!');
      }
      $obj = $ref->newInstance();
      foreach ($ref->getProperties() as $property) {
        $property->setAccessible(true);
        $group = new TransformGroup(property: $property, source: $obj, value: $data[$property->getName()] ?? null, name: $property->getName(), modelData: $data);
        if (class_exists($property->getType()?->getName())) {
          $group->value = self::deserialize($group->value, $property->getType()->getName());
        }
        foreach (self::propertyModelAttributes($property) as $processor) {
          $processor->deserialize($group);
        }
        if ($group->ignoreField) continue;
        $obj->{$group->name} = $group->value;
      }
      return $obj;
    }
  }
