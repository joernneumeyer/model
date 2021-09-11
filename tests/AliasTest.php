<?php

  use Neu\Model\Property\Alias;
  use Neu\Model\ModelParser;

  class TestAlias {
    #[Alias(name: 'FullName')]
    public string $name;
  }

  it('should serialize the object with aliased names', function () {
    $parser = new ModelParser();
    $obj = new TestAlias();
    $obj->name = 'foobar';
    $serialized = $parser->serialize($obj);
    expect($serialized)->toMatchArray(['FullName' => 'foobar']);
  });

  it('should deserialize the object from an aliased field name', function() {
    $parser = new ModelParser();
    $data = ['FullName' => 'foobar'];
    $obj = $parser->deserialize($data, TestAlias::class);
    $actual = new TestAlias();
    $actual->name = $data['FullName'];
    expect($obj)->toEqual($actual);
  });
