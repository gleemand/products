<?php

namespace App\Service\ItemsList;

use DateTime;
use DateTimeZone;
use RetailCrm\Api\Model\Entity\Orders\Items\OrderProduct;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class ItemsList implements ItemsListInterface
{
    private $fileName;

    private $apiUrl;

    public function __construct(
        ContainerBagInterface $params
    ) {
        $this->fileName = __DIR__ . $params->get('app.items_file');
        $this->apiUrl = $params->get('crm.api_url');
    }

    public function addItem(OrderProduct $item)
    {
        $string = json_encode([
            'name' => $item->offer->displayName,
            'quantity' => $item->quantity,
            'price' => $item->initialPrice,
            'purchasePrice' => $item->purchasePrice,
            'url' => $this->apiUrl . '?filter%5Bname%5D=' . ($item->offer->article ?? $item->offer->displayName),
            'statusDate' => (
                new DateTime('now', new DateTimeZone('America/Bogota'))
            )->format('Y-m-d'),
        ]);

        return file_put_contents($this->fileName, $string . "\n", FILE_APPEND | LOCK_EX);
    }

    public function getItems()
    {
        $items = [];
        $strings = file($this->fileName);

        foreach ($strings as $string) {
            $items[] = json_decode($string, true);
        }

        return $items;
    }

    public function resetList()
    {
        return file_put_contents($this->fileName, '');
    }
}