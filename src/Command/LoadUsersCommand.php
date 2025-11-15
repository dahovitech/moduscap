<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Commande pour charger les utilisateurs
 */
#[AsCommand(
    name: 'app:load-users',
    description: 'Charge les utilisateurs en base de données',
)]
class LoadUsersCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('👥 Chargement des utilisateurs MODUSCAP');

        try {
            // Vérifier si des utilisateurs existent déjà
            $existingUsers = $this->entityManager->getRepository(User::class)->findAll();
            if (!empty($existingUsers)) {
                $io->warning('⚠️  Des utilisateurs existent déjà en base. Suppression...');
                foreach ($existingUsers as $user) {
                    $this->entityManager->remove($user);
                }
                $this->entityManager->flush();
                $io->note('📊 Utilisateurs existants supprimés');
            }

            // Configuration des utilisateurs
            $usersData = [
                [
                    'email' => 'admin@moduscap.store',
                    'password' => 'admin123',
                    'roles' => ['ROLE_ADMIN'],
                    'firstName' => 'Administrateur',
                    'lastName' => 'Système'
                ],
                [
                    'email' => 'manager@moduscap.store',
                    'password' => 'manager123',
                    'roles' => ['ROLE_MANAGER'],
                    'firstName' => 'Manager',
                    'lastName' => 'Commercial'
                ],
                [
                    'email' => 'user@moduscap.store',
                    'password' => 'user123',
                    'roles' => ['ROLE_USER'],
                    'firstName' => 'Utilisateur',
                    'lastName' => 'Standard'
                ]
            ];

            // Configuration de la barre de progression
            $progressBar = new ProgressBar($output, count($usersData));
            $progressBar->setFormat('  %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%');
            $progressBar->start();

            $createdUsers = 0;

            foreach ($usersData as $userData) {
                $user = new User();
                $user->setEmail($userData['email'])
                     ->setRoles($userData['roles'])
                     ->setFirstName($userData['firstName'])
                     ->setLastName($userData['lastName']);

                // Hachage du mot de passe
                $hashedPassword = $this->passwordHasher->hashPassword(
                    $user,
                    $userData['password']
                );
                $user->setPassword($hashedPassword);

                $this->entityManager->persist($user);
                $createdUsers++;

                $progressBar->advance();
            }

            $this->entityManager->flush();
            $progressBar->finish();
            $output->writeln(''); // Ligne vide après la barre de progression

            $io->success("✅ {$createdUsers} utilisateurs créés avec succès !");
            
            // Afficher un résumé
            $io->section('📋 Résumé des utilisateurs créés:');
            foreach ($usersData as $index => $userData) {
                $io->writeln("   • {$userData['email']} ({$userData['firstName']} {$userData['lastName']})");
            }

            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $io->error('❌ Erreur lors du chargement des utilisateurs: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}