<?php

  use Neu\Model\ModelParser;
  use Neu\Model\Property\Ignore;

  class SerializableWithComputedProperty {
    public string $name;
    #[Ignore]
    private int $_callCount = 0;
  }

  it('should exclude any ignored properties on serialization', function() {
    $parser = new ModelParser();
    $obj = new SerializableWithComputedProperty();
    $obj->name = 'John Doe';
    $serialized = $parser->serialize($obj);
    expect($serialized['name'])->toEqual('John Doe');
    expect($serialized)->not->toHaveKey('_callCount');
  });

  it('should not write a given value to an ignored property', function() {
    $parser = new ModelParser();
    $data = ['name' => 'John Doe', '_callCount' => 3];
    $expected = new SerializableWithComputedProperty();
    $expected->name = 'John Doe';
    $obj = $parser->deserialize($data, SerializableWithComputedProperty::class);
    expect($obj)->toEqual($expected);
  });
