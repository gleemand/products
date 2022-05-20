<?php

namespace App\Service\ProcessOrder;

use App\Service\ItemsList\ItemsListInterface;
use App\Service\SimlaApi\SimlaApiInterface;
use RetailCrm\Api\Model\Entity\Orders\Order;

class ProcessOrder implements ProcessOrderInterface
{
    private $api;

    private $list;

    public function __construct(
        SimlaApiInterface $api,
        ItemsListInterface $list
    ) {
        $this->api = $api;
        $this->list = $list;
    }

    public function process(int $orderId, string $site): array
    {
        $packs = $this->api->packListByOrderId($orderId);
        $order = $this->api->orderGet($orderId, $site);

        if (!$order || !$packs) {
            return [
                'status' => 'NOK',
                'message' => 'orderId = ' . $orderId . ' - no order or packs',
            ];
        }

        $isChanged = false;

        $orderChanges = new Order();
        $orderChanges->id = $order->id;
        $orderChanges->status = 'solicitando-productos-al-proveedor';
        $orderChanges->items = $order->items;

        foreach ($packs as $pack) {
            if ('proveedor' === $pack->store) {
                $isChanged = true;
                
                foreach ($orderChanges->items as $key => $item) {
                    if ($pack->item->id === $item->id) {
                        $this->list->addItem($item);

                        $orderChanges->items[$key]->status = 'cancelacion';

                        continue 2;
                    }
                }
            }
        }

        if ($isChanged) {
            $result = $this->api->orderEdit($orderChanges, $site);

            if ($result) {
                return [
                    'status' => 'OK',
                    'message' => 'orderId = ' . $orderId,
                ];
            }
        }

        return [
            'status' => 'NOK',
            'message' => 'no changes',
        ];
    }
}