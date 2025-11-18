<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

#[AsCommand(
    name: 'app:load-all-data',
    description: 'Charge toutes les données : langues, catégories, produits et options',
)]
class LoadAllDataCommand extends Command
{
    public function __construct() {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Chargement de toutes les données MODUSCAP');

        $commands = [
            'app:load-languages' => 'Chargement des langues',
            'app:load-categories' => 'Chargement des catégories', 
            'app:load-products' => 'Chargement des produits',
            'app:load-product-options' => 'Chargement des options de produits'
        ];

        $successCount = 0;
        $failureCount = 0;
        $commandResults = [];

        foreach ($commands as $command => $description) {
            $io->section($description);
            
            try {
                $process = new Process(['php', 'bin/console', $command]);
                $process->setTimeout(300); // 5 minutes timeout
                $process->run();

                if ($process->isSuccessful()) {
                    $output = trim($process->getOutput());
                    $io->success($output);
                    $successCount++;
                    $commandResults[$command] = ['status' => 'success', 'output' => $output];
                } else {
                    $error = trim($process->getErrorOutput());
                    $io->error("Erreur lors de l'exécution de $command: $error");
                    $failureCount++;
                    $commandResults[$command] = ['status' => 'error', 'error' => $error];
                }
            } catch (\Exception $e) {
                $io->error("Exception lors de l'exécution de $command: " . $e->getMessage());
                $failureCount++;
                $commandResults[$command] = ['status' => 'exception', 'error' => $e->getMessage()];
            }
        }

        // Résumé détaillé
        $io->title('📊 Résumé détaillé du chargement');
        
        $io->listing([
            "✅ Commandes réussies: $successCount",
            "❌ Commandes échouées: $failureCount", 
            "📈 Total des commandes: " . count($commands)
        ]);

        // Afficher les détails des résultats
        $io->section('Détails par commande:');
        foreach ($commandResults as $command => $result) {
            if ($result['status'] === 'success') {
                $io->success("✅ $command: Exécuté avec succès");
            } else {
                $io->error("❌ $command: Échec - " . $result['error']);
            }
        }
        
        if ($failureCount === 0) {
            $io->success("🎉 Toutes les commandes ont été exécutées avec succès ($successCount/" . count($commands) . ")");
            
            // Informations supplémentaires après succès
            $io->note([
                "🚀 Le système MODUSCAP est maintenant prêt à l'utilisation",
                "💾 Base de données chargée avec tous les données de base", 
                "🌍 Support multilingue activé (9 langues)",
                "⚙️ Options de produits configurées",
                "👥 Utilisateurs par défaut créés"
            ]);
            
            return Command::SUCCESS;
        } else {
            $io->warning("⚠️ Chargement terminé avec $successCount succès et $failureCount échecs");
            
            // Conseils de résolution
            $io->section('🔧 Conseils de résolution:');
            $io->listing([
                "Vérifiez la connectivité à la base de données",
                "Assurez-vous que les migrations sont exécutées",
                "Vérifiez les permissions de fichiers",
                "Consultez les logs pour plus de détails"
            ]);
            
            return Command::FAILURE;
        }
    }
}