<?php

namespace App\Command;

use App\Service\ItemsList\ItemsListInterface;
use App\Service\SimlaApi\SimlaApiInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class SendListCommand extends Command
{
    protected static $defaultName = 'sendList';
    protected static $defaultDescription = 'Send email with product list';

    private $list;
    private $email;

    public function __construct(
        ItemsListInterface $list,
        ContainerBagInterface $params
    ) {
        $this->list = $list;
        $this->email = $params->get('app.email');

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $subject  = 'Instar prod. al proveedor';
        $headers  = "From: " . $this->email . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $message  = '<p><strong>Instar prod. al proveedor</strong></p>';
        $message .= '<p><table border="1"><tr><th>Producto</th><th>Cantidad</th><th>Precio de compra</th><th>Precio de venta</th><th>URL</th><th>Status Changed</th></tr>';

        $items = $this->list->getItems();

        if (!count($items)) {
            $io->success('NOTHING TO SEND');

            return Command::SUCCESS;
        }

        $totalPrice = 0;
        $totalPurchasePrice = 0;

        foreach ($items as $item) {
            $message .= sprintf(
                '<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',
                $item['name'],
                $item['quantity'],
                $item['purchasePrice'],
                $item['price'],
                $item['url'],
                $item['statusDate']
            );

            $totalPrice += $item['price'];
            $totalPurchasePrice += $item['purchasePrice'];
        }

        $message .= '</table></p>';

        $message .= sprintf(
            '<p>Total Precio de compra: %s</p><p>Total Precio de venta: %s</p>',
            $item['purchasePrice'],
            $item['price'],
        );

        $result = mail($this->email, $subject, $message, $headers);

        $this->list->resetList();

        if ($result) {
            $io->success('DONE');

            return Command::SUCCESS;
        }

        $io->error('ERROR');

        return Command::FAILURE;
    }
}
