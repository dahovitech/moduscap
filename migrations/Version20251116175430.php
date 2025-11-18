<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration for Order and OrderItem entities
 */
final class Version20251116175430 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create Order and OrderItem entities for customer quote management';
    }

    public function up(Schema $schema): void
    {
        // Orders table
        $this->addSql('CREATE TABLE orders (
            id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 
            order_number VARCHAR(50) NOT NULL UNIQUE, 
            status VARCHAR(20) NOT NULL, 
            subtotal NUMERIC(12, 2) NOT NULL DEFAULT 0, 
            total NUMERIC(12, 2) NOT NULL DEFAULT 0, 
            rejection_reason TEXT DEFAULT NULL, 
            created_at DATETIME NOT NULL, 
            updated_at DATETIME NOT NULL, 
            paid_at DATETIME DEFAULT NULL, 
            approved_at DATETIME DEFAULT NULL, 
            client_notes TEXT DEFAULT NULL, 
            client_name VARCHAR(255) DEFAULT NULL, 
            client_email VARCHAR(255) DEFAULT NULL, 
            client_phone VARCHAR(20) DEFAULT NULL, 
            client_address TEXT DEFAULT NULL, 
            payment_proof VARCHAR(100) DEFAULT NULL, 
            approved_by_id INTEGER DEFAULT NULL, 
            FOREIGN KEY (approved_by_id) REFERENCES users (id) ON DELETE SET NULL
        )');

        // Create indexes for orders
        $this->addSql('CREATE INDEX idx_orders_status ON orders (status)');
        $this->addSql('CREATE INDEX idx_orders_client_email ON orders (client_email)');
        $this->addSql('CREATE INDEX idx_orders_created_at ON orders (created_at)');
        $this->addSql('CREATE INDEX idx_orders_order_number ON orders (order_number)');

        // Order items table
        $this->addSql('CREATE TABLE order_items (
            id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 
            quantity INTEGER NOT NULL DEFAULT 1, 
            unit_price NUMERIC(10, 2) NOT NULL DEFAULT 0, 
            options_price NUMERIC(10, 2) NOT NULL DEFAULT 0, 
            total_price NUMERIC(10, 2) NOT NULL DEFAULT 0, 
            customization_notes TEXT DEFAULT NULL, 
            selected_options TEXT DEFAULT NULL, 
            created_at DATETIME NOT NULL, 
            updated_at DATETIME NOT NULL, 
            order_id INTEGER NOT NULL, 
            product_id INTEGER NOT NULL, 
            FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE, 
            FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE
        )');

        // Create indexes for order items
        $this->addSql('CREATE INDEX idx_order_items_order_id ON order_items (order_id)');
        $this->addSql('CREATE INDEX idx_order_items_product_id ON order_items (product_id)');

        // Insert some sample data for testing
        $this->addSql("
            INSERT INTO orders (
                id, order_number, status, subtotal, total, created_at, updated_at, client_name, client_email
            ) VALUES 
            (1, 'ORD-20251116-ABC123', 'pending', '85000.00', '85000.00', datetime('now'), datetime('now'), 'John Doe', 'john.doe@example.com'),
            (2, 'ORD-20251116-DEF456', 'approved', '115000.00', '115000.00', datetime('now'), datetime('now'), 'Jane Smith', 'jane.smith@example.com')
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX idx_orders_status');
        $this->addSql('DROP INDEX idx_orders_client_email');
        $this->addSql('DROP INDEX idx_orders_created_at');
        $this->addSql('DROP INDEX idx_orders_order_number');
        $this->addSql('DROP INDEX idx_order_items_order_id');
        $this->addSql('DROP INDEX idx_order_items_product_id');
        
        $this->addSql('DROP TABLE order_items');
        $this->addSql('DROP TABLE orders');
    }
}