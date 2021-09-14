<?php

  namespace Neu\Model;

  use InvalidArgumentException;
  use Neu\Model\Exceptions\ValidationError;
  use ReflectionClass;
  use ReflectionException;
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

    /**
     * Serializes an object into an associative array.
     * @param object $obj
     * @return array<string, mixed>
     * @throws ValidationError
     */
    public function serialize(object $obj): array {
      $data = [];
      $ref  = new ReflectionObject($obj);
      $errors = [];
      foreach ($ref->getProperties() as $property) {
        $property->setAccessible(true);
        $group = new TransformGroup(property: $property, source: $obj, value: $property->getValue($obj), name: $property->getName());
        if ($property->getType() instanceof \ReflectionNamedType && class_exists($property->getType()->getName())) {
          try {
            $group->value = self::serialize($group->value);
          } catch (ValidationError $e) {
            foreach ($e->errors as $key => $error) {
              $errors[$property->getName() . '.' . $key] = $error;
            }
          }
        }
        foreach (self::propertyModelAttributes($property) as $processor) {
          try {
            $processor->serialize($group);
          } catch (ValidationError $e) {
            foreach ($e->errors as $error) {
              $errors[$property->getName()][] = $error;
            }
          }
        }
        if ($group->ignoreField) {
          continue;
        }
        $data[$group->name] = $group->value;
      }
      if ($errors) {
        throw new ValidationError($errors);
      }
      return $data;
    }

    /**
     * @param array<string, mixed> $data
     * @param string $type
     * @return object
     * @throws ReflectionException
     * @throws ValidationError
     */
    public function deserialize(array $data, string $type): object {
      if (!class_exists($type)) {
        throw new InvalidArgumentException('Cannot deserialize array to an object of undefined type "' . $type . '"!');
      }
      $ref           = new ReflectionClass($type);
      $ctorArgsCount = $ref->getConstructor()?->getNumberOfRequiredParameters();
      if ($ctorArgsCount !== null && $ctorArgsCount > 0) {
        throw new InvalidArgumentException('Cannot deserialize data into an object of type "' . $type . '" as its constructor is non-trivial!');
      }
      $obj    = $ref->newInstance();
      $errors = [];
      foreach ($ref->getProperties() as $property) {
        $property->setAccessible(true);
        $group = new TransformGroup(property: $property, source: $obj, value: $data[$property->getName()] ?? null, name: $property->getName(), modelData: $data);
        if ($property->getType() instanceof \ReflectionNamedType && class_exists($property->getType()->getName())) {
          try {
            $group->value = $this->deserialize($group->value, $property->getType()->getName());
          } catch (ValidationError $e) {
            foreach ($e->errors as $key => $error) {
              $errors[$property->getName() . '.' . $key] = $error;
            }
            continue;
          }
        }
        foreach (self::propertyModelAttributes($property) as $processor) {
          try {
            $processor->deserialize($group);
          } catch (ValidationError $e) {
            foreach ($e->errors as $error) {
              $errors[$property->getName()][] = $error;
            }
          }
        }
        if ($group->ignoreField) {
          continue;
        }
        $obj->{$group->name} = $group->value;
      }
      if ($errors) {
        throw new ValidationError($errors);
      }
      return $obj;
    }
  }
