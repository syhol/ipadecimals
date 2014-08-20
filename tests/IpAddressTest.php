<?php 
namespace SyHolloway\IpaDecimals\Tests;

use SyHolloway\IpaDecimals\IpAddress;

class IpAddressTests extends TestCase
{
    private function checkIpsMatch($ip1, $ip2)
    {
        $this->assertEquals($ip1->getIp(), $ip2->getIp());
        $this->assertEquals($ip1->getDecimal(), $ip2->getDecimal());
        $this->assertEquals($ip1->getVersion(), $ip2->getVersion());
    }

    public function testObjectCanBeCreatedWithBasicIpV4()
    {
        $ipv4 = new IpAddress('10.22.99.1');
        $this->assertInstanceOf('SyHolloway\IpaDecimals\IpAddress', $ipv4);
    }

    public function testIpV4KeepsItsDataConsistent()
    {
        $ipv4 = new IpAddress('10.22.99.1');
        $this->assertEquals('10.22.99.1', $ipv4->getIp());
    }

    public function testIpV4CanConvertItselfToDecimal()
    {
        $ipv4 = new IpAddress('10.22.99.1');
        $this->assertEquals('169239297', $ipv4->getDecimal());
    }

    public function testIpV4KnowsWhatVersionItIs()
    {
        $ipv4 = new IpAddress('10.22.99.1');
        $this->assertEquals(4, $ipv4->getVersion());
    }

    public function testIpV4CanBeCreatedFromFactoryMethod()
    {
        $ipv4 = new IpAddress('10.22.99.1');
        $factoryIpv4 = IpAddress::fromIp('10.22.99.1');
        $this->checkIpsMatch($ipv4, $factoryIpv4);
    }

    public function testIpV4CanBeRecreatedFromDecimal()
    {
        $ipv4 = new IpAddress('10.22.99.1');
        $decimalIpv4 = IpAddress::fromDecimal($ipv4->getDecimal());
        $this->checkIpsMatch($ipv4, $decimalIpv4);
    }

    public function testObjectCanBeCreatedWithBasicIpV6()
    {
        $ipv6 = new IpAddress('fe80:1:2:3:a:bad:1dea:dad');
    }

    public function testIpV6KeepsItsDataConsistent()
    {
        $ipv6 = new IpAddress('fe80:1:2:3:a:bad:1dea:dad');
        $this->assertEquals('fe80:1:2:3:a:bad:1dea:dad', $ipv6->getIp());
    }

    public function testIpV6CanConvertItselfToDecimal()
    {
        $ipv6 = new IpAddress('fe80:1:2:3:a:bad:1dea:dad');
        $this->assertEquals('338288525006491670075265523502295944621', $ipv6->getDecimal());
    }

    public function testIpV6KnowsWhatVersionItIs()
    {
        $ipv6 = new IpAddress('fe80:1:2:3:a:bad:1dea:dad');
        $this->assertEquals(6, $ipv6->getVersion());
    }

    public function testIpV6CanBeCreatedFromFactoryMethod()
    {
        $ipv6 = new IpAddress('fe80:1:2:3:a:bad:1dea:dad');
        $factoryIpv6 = IpAddress::fromIp('fe80:1:2:3:a:bad:1dea:dad');
        $this->checkIpsMatch($ipv6, $factoryIpv6);
    }

    public function testIpV6CanBeRecreatedFromDecimal()
    {
        $ipv6 = new IpAddress('fe80:1:2:3:a:bad:1dea:dad');
        $decimalIpv6 = IpAddress::fromDecimal($ipv6->getDecimal());
        $this->checkIpsMatch($ipv6, $decimalIpv6);
    }


    public function testIpVersionsCanBeChecked()
    {
        $ipv4 = new IpAddress('10.22.99.1');
        $ipv6 = new IpAddress('fe80:1:2:3:a:bad:1dea:dad');
        $this->assertTrue($ipv4->checkVersion(4));
        $this->assertTrue( ! $ipv4->checkVersion(6) );
        $this->assertTrue($ipv6->checkVersion(6));
        $this->assertTrue( ! $ipv6->checkVersion(4) );
    }

    /**
     * @expectedException SyHolloway\IpaDecimals\NotValidIpException
     */
    public function testObjectThrowsErrorWhenCreatedWithInputTooShort()
    {
        $ipv4 = new IpAddress('10.22.99');
    }

    /**
     * @expectedException SyHolloway\IpaDecimals\NotValidIpException
     */
    public function testObjectThrowsErrorWhenCreatedWithInputTooLong()
    {
        $ipv4 = new IpAddress('10.22.99.1.2');
    }

    /**
     * @expectedException SyHolloway\IpaDecimals\NotValidIpException
     */
    public function testObjectThrowsErrorWhenCreatedWithIpSegmentLargerThan255()
    {
        $ipv4 = new IpAddress('10.262.99.1');
    }

    /**
     * @expectedException SyHolloway\IpaDecimals\NotValidIpException
     */
    public function testObjectThrowsErrorWhenCreatedWithNonIp()
    {
        $notanip = new IpAddress('foobar');
    }

}