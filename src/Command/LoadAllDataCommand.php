<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

/**
 * Commande maestra pour charger toutes les données MODUSCAP dans l'ordre correct
 */
#[AsCommand(
    name: 'app:load-all-data',
    description: 'Charge toutes les données MODUSCAP (Users → Settings → Langues → Produits → Options → Médias)',
)]
class LoadAllDataCommand extends Command
{
    private array $commandNames = [
        'app:load-users' => '👥 Utilisateurs',
        'app:load-settings' => '⚙️ Paramètres système', 
        'app:load-languages' => '🌐 Langues',
        'app:load-products' => '🚀 Produits et catégories',
        'app:load-product-options' => '🔧 Options de produits',
        'app:load-product-media' => '🖼️ Médias produits'
    ];

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('🎯 Chargement complet des données MODUSCAP');
        $io->note('Ordre d\'exécution: Utilisateurs → Paramètres → Langues → Produits → Options → Médias');

        $successCount = 0;
        $failureCount = 0;
        $executedCommands = [];

        foreach ($this->commandNames as $commandName => $description) {
            try {
                $io->section("🚀 Exécution de: {$description}");
                
                // Trouver la commande
                $command = $this->getApplication()->find($commandName);
                
                if (!$command) {
                    throw new \Exception("Commande non trouvée: {$commandName}");
                }

                // Exécuter la commande
                $returnCode = $command->run($input, $output);
                
                if ($returnCode === SymfonyCommand::SUCCESS) {
                    $executedCommands[] = [
                        'name' => $commandName,
                        'description' => $description,
                        'status' => 'success'
                    ];
                    $successCount++;
                } else {
                    $executedCommands[] = [
                        'name' => $commandName,
                        'description' => $description,
                        'status' => 'failure',
                        'return_code' => $returnCode
                    ];
                    $failureCount++;
                }

                // Pause entre les commandes pour une meilleure lisibilité
                if ($commandName !== array_key_last($this->commandNames)) {
                    $io->note("⏳ Pause de 2 secondes...");
                    sleep(2);
                }

            } catch (\Exception $e) {
                $executedCommands[] = [
                    'name' => $commandName,
                    'description' => $description,
                    'status' => 'error',
                    'error' => $e->getMessage()
                ];
                $failureCount++;
                $io->error("❌ Erreur lors de l'exécution de {$description}: " . $e->getMessage());
            }
        }

        // Résumé final
        $io->title('📊 Résumé du chargement complet');
        
        $io->section('✅ Commandes exécutées avec succès:');
        foreach ($executedCommands as $cmd) {
            if ($cmd['status'] === 'success') {
                $io->writeln("   • {$cmd['description']}");
            }
        }

        if ($failureCount > 0) {
            $io->section('❌ Échecs et erreurs:');
            foreach ($executedCommands as $cmd) {
                if ($cmd['status'] === 'failure' || $cmd['status'] === 'error') {
                    $status = $cmd['status'] === 'failure' ? 'Échec' : 'Erreur';
                    $io->writeln("   • {$cmd['description']} - {$status}");
                    if (isset($cmd['error'])) {
                        $io->writeln("     📝 Détail: {$cmd['error']}");
                    }
                    if (isset($cmd['return_code'])) {
                        $io->writeln("     🔢 Code retour: {$cmd['return_code']}");
                    }
                }
            }
        }

        // Statistiques finales
        $io->section('📈 Statistiques:');
        $io->writeln("   • ✅ Commandes réussies: {$successCount}");
        $io->writeln("   • ❌ Commandes échouées: {$failureCount}");
        $io->writeln("   • 📊 Total des commandes: " . count($this->commandNames));

        if ($failureCount === 0) {
            $io->success('🎉 Toutes les données ont été chargées avec succès !');
            $io->note('💡 Votre site MODUSCAP est maintenant prêt à fonctionner.');
            return Command::SUCCESS;
        } else {
            $io->warning('⚠️  Le chargement s\'est terminé avec des erreurs.');
            $io->note('💡 Vérifiez les erreurs ci-dessus et réexécutez les commandes individuellement si nécessaire.');
            return Command::FAILURE;
        }
    }
}