<?php

  namespace LengthTest {

    use Neu\Model\Property\Length;

    class MinLengthUser {
      #[Length(min: 4)]
      public string $username;
    }

    class MaxLengthUser {
      #[Length(max: 10)]
      public string $username;
    }

    class ExactLengthUser {
      #[Length(exact: 7)]
      public string $username;
    }

    class Address {
      #[Length(min: 2)]
      public string $zip;
    }

    class UserWithAddress {
      #[Length(max: 20)]
      public string  $username;
      public Address $address;
    }
  }

  namespace {

    use LengthTest\ExactLengthUser;
    use LengthTest\MaxLengthUser;
    use LengthTest\MinLengthUser;
    use LengthTest\UserWithAddress;
    use Neu\Model\Exceptions\InvalidConfiguration;
    use Neu\Model\Exceptions\ValidationError;
    use Neu\Model\Property\Length;

    it('should throw if an invalid minimum length is provided', function () {
      new Length(min: -3);
    })->throws(InvalidConfiguration::class);

    it('should throw if an invalid maximum length is provided', function () {
      new Length(max: -3);
    })->throws(InvalidConfiguration::class);

    it('should throw if an invalid exact length is provided', function () {
      new Length(exact: -3);
    })->throws(InvalidConfiguration::class);

    it('should throw, if no constructor parameter is provided', function() {
      new Length();
    })->throws(InvalidConfiguration::class);

    it('deserialization - should throw, if anything other than a string shall be validated', function() {
      $data = ['username' => 445432];
      parser()->deserialize($data, MinLengthUser::class);
    })->throws(InvalidArgumentException::class);

    it('deserialization - should allow a string that is longer than the minimum length', function () {
      $data = ['username' => 'John Doe'];
      parser()->deserialize($data, MinLengthUser::class);
      expect(true)->toBeTrue();
    });

    it('deserialization - should allow a string that is shorter than the maximum length', function () {
      $data = ['username' => 'John Doe'];
      parser()->deserialize($data, MaxLengthUser::class);
      expect(true)->toBeTrue();
    });

    it('deserialization - should allow a string which matches the exact length', function () {
      $data = ['username' => 'foobar1'];
      parser()->deserialize($data, ExactLengthUser::class);
      expect(true)->toBeTrue();
    });

    it('deserialization - should throw, if a string is shorter than the minimum length', function () {
      try {
        parser()->deserialize(['username' => 'f'], MinLengthUser::class);
        throw new Exception();
      } catch (ValidationError $e) {
        expect($e->errors)->toHaveKey('username');
      }
    });

    it('deserialization - should throw, if a string is longer than the maximum length', function () {
      try {
        parser()->deserialize(['username' => 'foobarfoobar'], MaxLengthUser::class);
        throw new Exception();
      } catch (ValidationError $e) {
        expect($e->errors)->toHaveKey('username');
      }
    });

    it('deserialization - should throw, if the string does not match the exact length', function() {
      try {
        parser()->deserialize(['username' => 'foobarfoobar'], ExactLengthUser::class);
        throw new Exception();
      } catch (ValidationError $e) {
        expect($e->errors)->toHaveKey('username');
      }
    });

    it('deserialization - should throw, if a nested property is invalid', function () {
      try {
        parser()->deserialize(['username' => 'foobarfoobar', 'address' => ['zip' => 'a']], UserWithAddress::class);
        throw new Exception();
      } catch (ValidationError $e) {
        expect($e->errors)->toHaveKey('address.zip');
      }
    });

  // ---
    it('serialization - should allow a string that is longer than the minimum length', function () {
      $obj = new MinLengthUser();
      $obj->username = 'John Doe';
      parser()->serialize($obj);
      expect($obj->username)->toEqual('John Doe');
    });

    it('serialization - should allow a string that is shorter than the maximum length', function () {
      $obj = new MaxLengthUser();
      $obj->username = 'John Doe';
      parser()->serialize($obj);
      expect($obj->username)->toEqual('John Doe');
    });

    it('serialization - should allow a string which matches the exact length', function () {
      $obj = new ExactLengthUser();
      $obj->username = 'doedoe1';
      parser()->serialize($obj);
      expect($obj->username)->toEqual('doedoe1');
    });

    it('serialization - should throw, if a string is shorter than the minimum length', function () {
      try {
        $obj = new MinLengthUser();
        $obj->username = 'f';
        parser()->serialize($obj);
        throw new Exception();
      } catch (ValidationError $e) {
        expect($e->errors)->toHaveKey('username');
      }
    });

    it('serialization - should throw, if a string is longer than the maximum length', function () {
      try {
        $obj = new MaxLengthUser();
        $obj->username = 'foobarfoobar';
        parser()->serialize($obj);
        throw new Exception();
      } catch (ValidationError $e) {
        expect($e->errors)->toHaveKey('username');
      }
    });

    it('serialization - should throw, if the string does not match the exact length', function() {
      try {
        $obj = new ExactLengthUser();
        $obj->username = 'barbazfoobar';
        parser()->serialize($obj);
        throw new Exception();
      } catch (ValidationError $e) {
        expect($e->errors)->toHaveKey('username');
      }
    });

    it('serialization - should throw, if a nested property is invalid', function () {
      try {
        $user = new UserWithAddress();
        $user->username = 'JohnDoe';
        $user->address = new \LengthTest\Address();
        $user->address->zip = '2';
        parser()->serialize($user);
        throw new Exception();
      } catch (ValidationError $e) {
        expect($e->errors)->toHaveKey('address.zip');
      }
    });
  }
