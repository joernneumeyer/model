<?php

  use Neu\Model\Property\ArrayOf;

  class ClassWithStrings {
    #[ArrayOf('string')]
    public array $strings;
  }

  class Address {
    public string $zip;
  }

  class ClassWithArrayOfObjects {
    #[ArrayOf(Address::class)]
    public array $addresses;
  }

  class ClassWithInvalidType {
    #[ArrayOf('asfcsdv')]
    public array $foo;
  }

  it('should serialize an array of strings', function () {
    $obj          = new ClassWithStrings();
    $obj->strings = ['a', 'bar'];
    $serialized   = parser()->serialize($obj);
    expect($serialized)->toMatchArray(['strings' => ['a', 'bar']]);
  });

  it('should serialize an array of objects', function () {
    $obj            = new ClassWithArrayOfObjects();
    $a1             = new Address();
    $a1->zip        = '5911';
    $a2             = new Address();
    $a2->zip        = '3345';
    $obj->addresses = [$a1, $a2];
    $serialized     = parser()->serialize($obj);
    expect($serialized)->toMatchArray(['addresses' => [
      [
        'zip' => '5911',
      ],
      [
        'zip' => '3345',
      ],
    ]]);
  });

  it('should deserialize an object with an array of strings', function() {
    $data = ['strings' => ['a', 'bar']];
    $obj          = new ClassWithStrings();
    $obj->strings = ['a', 'bar'];
    $deserialized = parser()->deserialize($data, ClassWithStrings::class);
    expect($deserialized)->toEqual($obj);
  });

  it('should deserialize an object with an array of objects', function() {
    $data = ['addresses' => [
      [
        'zip' => '5911',
      ],
      [
        'zip' => '3345',
      ],
    ]];
    $obj            = new ClassWithArrayOfObjects();
    $a1             = new Address();
    $a1->zip        = '5911';
    $a2             = new Address();
    $a2->zip        = '3345';
    $obj->addresses = [$a1, $a2];
    $deserialized = parser()->deserialize($data, ClassWithArrayOfObjects::class);
    expect($deserialized)->toEqual($obj);
  });

  it('should throw if an invalid type if provided', function() {
    parser()->deserialize([], ClassWithInvalidType::class);
  })->throws(InvalidArgumentException::class);
