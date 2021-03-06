<?php

set_include_path(dirname(dirname(__FILE__)) . ':' . get_include_path());

require_once 'Services/OpenStreetMap.php';

require_once 'HTTP/Request2.php';
require_once 'HTTP/Request2/Adapter/Mock.php';
require_once 'PHPUnit/Framework/TestCase.php';

class MapquestReverseGeocodeTest extends PHPUnit_Framework_TestCase
{
    /**
     * testReverse
     *
     * @return void
     */
    public function testReverse()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(fopen(__DIR__ . '/responses/capabilities.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/mapquestReverseGeocodeIrishTimes.xml', 'rb'));
        $mock->addResponse(fopen(__DIR__ . '/responses/mapquestReverseGeocodeChurchtown.xml', 'rb'));

        $config = array(
            'server' => 'http://open.mapquestapi.com/nominatim/v1/',
            'adapter' => $mock
        );
        $osm = new Services_OpenStreetMap($config);
        $nominatim = new Services_OpenStreetMap_Nominatim($osm->getTransport());
        $nominatim->setServer('mapquest');
        $xml = $nominatim
            ->setFormat('xml')
            ->reverseGeocode("53.3459641", "-6.2548149");
        $this->AssertEquals($xml[0]->addressparts->house, "The Irish Times");
        $this->AssertEquals($xml[0]->addressparts->house_number, "24-28");
        $this->AssertEquals($xml[0]->addressparts->road, "Tara Street");
        $this->AssertEquals($xml[0]->addressparts->city, "Dublin");
        $this->AssertEquals($xml[0]->addressparts->county, "County Dublin");
        $this->AssertEquals($xml[0]->addressparts->state_district, "Leinster");
        $this->AssertEquals($xml[0]->addressparts->postcode, "2");
        $this->AssertEquals($xml[0]->addressparts->country, "Ireland");
        $this->AssertEquals($xml[0]->addressparts->country_code, "ie");

        $nominatim->setCountryCodes('ie');
        $res = $nominatim->search('churchtown');
        $this->assertEquals(count($res), 10);

    }
}

?>
