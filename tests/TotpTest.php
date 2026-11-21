<?php

use PHPUnit\Framework\TestCase;
use TotpPhp\Base32;
use TotpPhp\Totp;

class TotpTest extends TestCase
{
    protected $totp;
    protected $base32;

    protected function setUp(): void
    {
        $this->totp = new Totp();
        $this->base32 = new Base32();
    }

    public function testCreateSecretDefaultLength()
    {
        $secret = $this->totp->createSecret();
        $this->assertIsString($secret);
        $this->assertEquals(16, strlen($secret));  // Default length should be 16
    }

    public function testCreateSecretCustomLength()
    {
        $secretLength = 32;
        $secret = $this->totp->createSecret($secretLength);
        $this->assertIsString($secret);
        $this->assertEquals($secretLength, strlen($secret));  // Custom length
    }

    public function testCreateSecretInvalidLengthThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->totp->createSecret(10);  // Invalid length less than 16
    }

    public function testGetQrCodePayload()
    {
        $secret = 'ABC=';
        $label = 'user@example.com';
        $issuer = 'My App';

        $payload = $this->totp->getQrCodePayload($secret, $label, $issuer);

        $this->assertSame(
            'otpauth://totp/user%40example.com?secret=ABC%3D&issuer=My%20App',
            $payload
        );
    }

    public function testGetQrCodePayloadEncodesReservedCharacters()
    {
        $label = 'user+tag@example.com?admin=true#profile';
        $issuer = 'My App & Co';

        $payload = $this->totp->getQrCodePayload('ABC=', $label, $issuer);

        $this->assertSame(
            'otpauth://totp/' . rawurlencode($label) . '?secret=' . rawurlencode('ABC=') . '&issuer=' . rawurlencode($issuer),
            $payload
        );
    }

    public function testGetCode()
    {
        $secret = $this->totp->createSecret();
        $code = $this->totp->getCode($secret);
        $this->assertIsString($code);
        $this->assertEquals(6, strlen($code));  // Code length should be 6
    }

    public function testGetCodeMatchesRfc6238Sha1Vector()
    {
        $secret = 'GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ';

        $this->assertSame('287082', $this->totp->getCode($secret, 1));
    }

    public function testGetCodeRejectsInvalidBase32Secret()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->totp->getCode('INVALID!');
    }

    public function testGetCodeRejectsEmptySecret()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->totp->getCode('');
    }

    public function testGetCodeRejectsZeroSecret()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->totp->getCode('0');
    }

    public function testGetCodeUsesFull64BitTimeSlice()
    {
        $secret = 'GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ';

        $this->assertSame('999456', $this->totp->getCode($secret, 4294967296));
    }

    public function testVerifyCodeCorrect()
    {
        $secret = $this->totp->createSecret();
        $code = $this->totp->getCode($secret);
        $isValid = $this->totp->verifyCode($secret, $code);
        $this->assertTrue($isValid);  // Code should be valid
    }

    public function testVerifyCodeIncorrect()
    {
        $secret = $this->totp->createSecret();
        $invalidCode = '123456';  // Invalid code
        $isValid = $this->totp->verifyCode($secret, $invalidCode);
        $this->assertFalse($isValid);  // Code should be invalid
    }

    public function testTimingSafeEquals()
    {
        $safeString = 'abcdef';
        $userString = 'abcdef';
        $result = $this->invokeMethod($this->totp, 'timingSafeEquals', [$safeString, $userString]);
        $this->assertTrue($result);  // Strings are identical

        $userString = 'abcxyz';
        $result = $this->invokeMethod($this->totp, 'timingSafeEquals', [$safeString, $userString]);
        $this->assertFalse($result);  // Strings are different
    }

    /**
     * Helper function to invoke private/protected methods for testing.
     */
    protected function invokeMethod($object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $parameters);
    }
}
