<?php

namespace App\Service\SimlaApi;

use DateTime;
use DateTimeZone;
use RetailCrm\Api\Enum\ByIdentifier;
use RetailCrm\Api\Factory\SimpleClientFactory;
use RetailCrm\Api\Interfaces\ApiExceptionInterface;
use RetailCrm\Api\Model\Entity\Orders\Order;
use RetailCrm\Api\Model\Entity\Packs\OrderProductPack;
use RetailCrm\Api\Model\Filter\Packs\OrderProductPackFilter;
use RetailCrm\Api\Model\Request\BySiteRequest;
use RetailCrm\Api\Model\Request\Orders\OrdersEditRequest;
use RetailCrm\Api\Model\Request\Packs\PacksCreateRequest;
use RetailCrm\Api\Model\Request\Packs\PacksRequest;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class SimlaApi implements SimlaApiInterface
{
    private $apiClient;

    public function __construct(
        ContainerBagInterface $params
    )
    {
        $this->apiClient = SimpleClientFactory::createClient($params->get('crm.api_url'), $params->get('crm.api_key'));
    }

    public function orderGet(int $orderId, string $site)
    {
        try {
            $response = $this->apiClient->orders->get(
                $orderId,
                new BySiteRequest(ByIdentifier::ID, $site)
            );
        } catch (ApiExceptionInterface $exception) {
            echo sprintf(
                'Error from RetailCRM API (status code: %d): %s',
                $exception->getStatusCode(),
                $exception->getMessage()
            );

            if (count($exception->getErrorResponse()->errors) > 0) {
                echo PHP_EOL . 'Errors: ' . implode(', ', $exception->getErrorResponse()->errors);
            }

            return;
        }

        return $response->order;
    }

    public function orderEdit(Order $order, string $site)
    {
        $request        = new OrdersEditRequest();
        $request->by    = ByIdentifier::ID;
        $request->site  = $site;
        $request->order = $order;

        try {
            $response = $this->apiClient->orders->edit($order->id, $request);
        } catch (ApiExceptionInterface $exception) {
            echo sprintf(
                'Error from RetailCRM API (status code: %d): %s',
                $exception->getStatusCode(),
                $exception->getMessage()
            );

            if (count($exception->getErrorResponse()->errors) > 0) {
                echo PHP_EOL . 'Errors: ' . implode(', ', $exception->getErrorResponse()->errors);
            }

            return;
        }

        return $response->order;
    }

    public function packListByOrderId(int $orderId)
    {
        $request                  = new PacksRequest();
        $request->filter          = new OrderProductPackFilter();
        $request->filter->orderId = $orderId;

        try {
            $response = $this->apiClient->packs->list($request);
        } catch (ApiExceptionInterface $exception) {
            echo sprintf(
                'Error from RetailCRM API (status code: %d): %s',
                $exception->getStatusCode(),
                $exception->getMessage()
            );

            if (count($exception->getErrorResponse()->errors) > 0) {
                echo PHP_EOL . 'Errors: ' . implode(', ', $exception->getErrorResponse()->errors);
            }

            return;
        }

        return $response->packs;
    }
}