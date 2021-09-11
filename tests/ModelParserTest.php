<?php

  use Neu\Model\ModelParser;

  class SerializableClass {
    public string $id;
  }

  class NonTrivialClass {
    public function __construct(
      public string $name,
    ) {
    }
  }

  it('should serialize the object into an associative array', function() {
    $parser = new ModelParser();
    $obj = new SerializableClass();
    $obj->id = '4ed2';
    $serialized = $parser->serialize($obj);
    expect($serialized)->toMatchArray(['id' => '4ed2']);
  });

  it('should deserialize the array into an object', function() {
    $parser = new ModelParser();
    $data = ['id' => '442a'];
    $obj = $parser->deserialize($data, SerializableClass::class);
    $actual = new SerializableClass();
    $actual->id = $data['id'];
    expect($obj)->toEqual($actual);
  });

  it('should throw if an invalid type for deserialization is provided', function() {
    $parser = new ModelParser();
    $parser->deserialize([], 'asfsdf');
  })->throws(InvalidArgumentException::class);

  it('should throw if a type with a non-trivial constructor is provided', function() {
    $parser = new ModelParser();
    $parser->deserialize([], NonTrivialClass::class);
  })->throws(InvalidArgumentException::class);
