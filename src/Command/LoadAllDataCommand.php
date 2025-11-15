<?php

namespace App\Command;

use App\Command\LoadProductsCommand;
use App\Command\LoadProductMediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Commande pour charger toutes les données en une fois
 * Équivaut à l'ancien fixture mais avec plus de flexibilité
 */
#[AsCommand(
    name: 'app:load-all-data',
    description: 'Charge tous les produits et leurs médias en une fois',
)]
class LoadAllDataCommand extends Command
{
    public function __construct(
        private LoadProductsCommand $loadProductsCommand,
        private LoadProductMediaCommand $loadProductMediaCommand
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('🚀 Chargement complet des données MODUSCAP');

        try {
            // ÉTAPE 1: Charger les produits
            $io->section('📦 ÉTAPE 1: Chargement des produits');
            $result1 = $this->loadProductsCommand->run($input, $output);
            
            if ($result1 !== Command::SUCCESS) {
                $io->error('❌ Échec du chargement des produits');
                return Command::FAILURE;
            }

            // ÉTAPE 2: Charger les médias
            $io->section('🖼️  ÉTAPE 2: Chargement des médias');
            $result2 = $this->loadProductMediaCommand->run($input, $output);
            
            if ($result2 !== Command::SUCCESS) {
                $io->error('❌ Échec du chargement des médias');
                return Command::FAILURE;
            }

            $io->success('✅ Toutes les données ont été chargées avec succès !');
            $io->newLine(2);
            $io->note('💡 Utilisations possibles:');
            $io->listing([
                'php bin/console app:load-products (charge seulement les produits)',
                'php bin/console app:load-product-media (charge seulement les médias)',
                'php bin/console app:load-all-data (charge tout)'
            ]);

            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $io->error('❌ Erreur lors du chargement complet: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}