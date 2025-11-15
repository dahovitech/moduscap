<?php

namespace App\Command;

use App\Entity\Setting;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Helper\ProgressBar;

/**
 * Commande pour charger les paramètres système (site, contact, emails)
 */
#[AsCommand(
    name: 'app:load-settings',
    description: 'Charge les paramètres système (site, contact, emails) en base de données',
)]
class LoadSettingsCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('⚙️  Chargement des paramètres MODUSCAP');

        try {
            // Vérifier si des paramètres existent déjà
            $existingSettings = $this->entityManager->getRepository(Setting::class)->findAll();
            if (!empty($existingSettings)) {
                $io->warning('⚠️  Des paramètres existent déjà en base. Suppression...');
                foreach ($existingSettings as $setting) {
                    $this->entityManager->remove($setting);
                }
                $this->entityManager->flush();
                $io->note('📊 Paramètres existants supprimés');
            }

            // Configuration des paramètres système (utilisant la vraie structure de l'entité Setting)
            $settingsData = [
                'siteName' => 'moduscap.store',
                'phone' => '+33 1 23 45 67 89',
                'whatsapp' => '+33 6 12 34 56 78',
                'address' => '123 Rue de l\'Innovation, 75001 Paris, France',
                'email' => 'contact@moduscap.store',
                'emailSender' => 'noreply@moduscap.store',
                'emailReceived' => 'admin@moduscap.store',
                'paymentInfo' => 'Mode de paiement: Virement bancaire - IBAN: FR76 1234 5678 9012 3456 789' . "\n" .
                               'Coordonnées bancaires: Banque ModusCap - BIC: MODUFRPPXXX'
            ];

            // Configuration de la barre de progression
            $progressBar = new ProgressBar($output, count($settingsData));
            $progressBar->setFormat('  %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%');
            $progressBar->start();

            $createdSettings = 0;

            // Créer une seule instance de Setting avec tous les paramètres
            $setting = new Setting();
            
            foreach ($settingsData as $methodName => $value) {
                $setterMethod = 'set' . ucfirst($methodName);
                if (method_exists($setting, $setterMethod)) {
                    $setting->$setterMethod($value);
                    $createdSettings++;
                }
                $progressBar->advance();
            }

            $this->entityManager->persist($setting);

            $this->entityManager->flush();
            $progressBar->finish();
            $output->writeln(''); // Ligne vide après la barre de progression

            $io->success("✅ {$createdSettings} paramètres créés avec succès !");
            
            $io->section('📋 Paramètres système créés:');
            foreach ($settingsData as $key => $value) {
                $io->writeln("   • <info>{$key}</info>: " . substr($value, 0, 50) . (strlen($value) > 50 ? '...' : ''));
            }

            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $io->error('❌ Erreur lors du chargement des paramètres: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}