<?php

namespace App\Components;

use Exception;
use GuzzleHttp\Client;
use SimpleXMLElement;

class ParseDataClient
{
    public function parse()
    {
        // Создаю объект GuzzleHttp клиента
        $client = new Client();

        // URL с данными о квартирах
        $url = 'http://neometria.ru/api/flats/';

        try {
            $data = [];

            for ($i = 0; $i <= 4520; $i += 20) {
                // Отправляю GET запрос и получаю JSON данные
                $response = $client->get($url . "?limit=20&offset={$i}", [
                    'headers' => [
                        'Accept' => 'application/json',
                    ],
                    'verify' => false,
                ]);
                $jsonData = $response->getBody()->getContents();

                // Преобразую JSON в массив
                $jsonDecodedData = json_decode($jsonData, true);

                $data = array_merge($data, $jsonDecodedData['results']);
            }

            // Создаю корневой элемент XML
            $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><complexes timestamp="' . time() . '"/>');

            // Добавляю данные в XML
            $this->addComplexesDataToXml($xml, $data);

            // Преобразую SimpleXMLElement в файл XML
            $xml->asXML('output.xml');


            return 'Данные успешно сохранены ' . realpath('output.xml');

        } catch (Exception $e) {
            echo 'Произошла ошибка: ' . $e->getMessage();
        }
    }

    private function addComplexesDataToXml(SimpleXMLElement $xml, array $data)
    {
        $complexesData = [];

        // Добавляю данные в массив, группируя по названию коплекса
        // и названию здания
        foreach ($data as $item) {
            $complexName = $item['project_name'];
            $buildingName = $item['building'];

            if (!isset($complexesData[$complexName])) {
                $complexesData[$complexName] = [
                    'complex_slug' => $item['project_slug'],
                    'complex_name' => $item['project_name'],
                    'buildings' => [],
                ];
            }

            if (!isset($complexesData[$complexName]['buildings'][$buildingName])) {
                $complexesData[$complexName]['buildings'][$buildingName] = [
                    'building_id' => $item['building_id'],
                    'building_name' => $item['building'],
                    'flats' => [],
                ];
            }

            $complexesData[$complexName]['buildings'][$buildingName]['flats'][] = [
                'apartment' => $item['number'],
                'rooms' => $item['rooms'],
                'price' => $item['price'],
                'area' => $item['area'],
                'plan' => $item['plan'],
            ];
        }

        // Добавляю данные в SimpleXMLElement
        foreach ($complexesData as $complexData) {
            $complex = $xml->addChild('complex');
            $complex->addChild('slug', $complexData['complex_slug']);
            $complex->addChild('name', $complexData['complex_name']);
            $buildings = $complex->addChild('buildings');

            foreach ($complexData['buildings'] as $buildingData) {
                $building = $buildings->addChild('building');
                $building->addChild('id', $buildingData['building_id']);
                $building->addChild('name', $buildingData['building_name']);
                $flats = $building->addChild('flats');

                foreach ($buildingData['flats'] as $flatData) {
                    $flat = $flats->addChild('flat');
                    $flat->addChild('apartment', $flatData['apartment']);
                    $flat->addChild('rooms', $flatData['rooms']);
                    $flat->addChild('price', $flatData['price']);
                    $flat->addChild('area', $flatData['area']);
                    $flat->addChild('plan', $flatData['plan']);
                }
            }
        }
    }
}
