<?php

namespace App\Command;

use App\Entity\Site;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class SiteStatusCommand extends Command
{

    protected static $defaultName = 'site:status';

    private $container;
    private $mailer;

    public function __construct(ContainerInterface $container, MailerInterface $mailer)
    {
        parent::__construct();
        $this->container = $container;
        $this->mailer = $mailer;
    }

    protected function configure()
    {
        $this
            ->setDescription('Permet de modifier les status de tous les sites existants en base')
            ->addArgument('status', InputArgument::REQUIRED, 'Le status qui va être insérer en base')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $em = $this->container->get('doctrine')->getManager();
        $sites = $em->getRepository(Site::class)->findAll();

        $input = (int) $input->getArgument('status');

        if (300 < $input && $input < 600) {
            $email = (new Email())
                ->text("Bonjour, vos sites sont désormais indisponibles suite à vos modifications, veuillez modifier le statut du site pour confirmer la disponibilité de ce dernier")
                ->to("admin@test.com")
                ->from("siteAdministror@gmail.com");

            //….
            $this->mailer->send($email);
        }

        foreach ($sites as $site) {
            $site->setStatus((int)$input);
            $em->persist($site);
            $em->flush();
        }
        $output->writeln('Site successfully updated !');

        return 0;
    }
}
