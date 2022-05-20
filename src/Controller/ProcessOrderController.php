<?php

namespace App\Controller;

use App\Service\ProcessOrder\ProcessOrderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProcessOrderController extends AbstractController
{
    private $processOrderService;

    public function __construct(
        ProcessOrderInterface $processOrderService
    ) {
        $this->processOrderService = $processOrderService;
    }

    /**
     * @Route("/", name="app_process_order")
     */
    public function index(Request $request): Response
    {
        $orderId = $request->query->get('orderId');
        $site = $request->query->get('site');

        return $this->json($this->processOrderService->process((int) $orderId, $site));
    }
}
