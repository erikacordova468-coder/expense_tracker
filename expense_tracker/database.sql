-- Pocket Ledger: Expense Tracker
-- Author: Christy Ramos
-- Create a database named `expense_tracker` in phpMyAdmin, select it, then Import this file.
-- (On InfinityFree: create the database in the control panel, open phpMyAdmin, select it, then Import.)

DROP TABLE IF EXISTS expenses;
DROP TABLE IF EXISTS categories;

CREATE TABLE categories (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    name           VARCHAR(60)   NOT NULL UNIQUE,
    color          CHAR(7)       NOT NULL DEFAULT '#2f5d46',
    monthly_budget DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    created_at     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE expenses (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    title          VARCHAR(120)  NOT NULL,
    category_id    INT           NOT NULL,
    amount         DECIMAL(10,2) NOT NULL,
    expense_date   DATE          NOT NULL,
    payment_method ENUM('Cash','GCash','Maya','Card','Bank Transfer') NOT NULL DEFAULT 'Cash',
    notes          VARCHAR(255)  DEFAULT NULL,
    created_at     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_expense_category FOREIGN KEY (category_id)
        REFERENCES categories(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

INSERT INTO categories (name, color, monthly_budget) VALUES
('Food & Drinks', '#c2542d', 3000.00),
('Transport',     '#2f6f8f', 1200.00),
('School',        '#6b4fa0', 1500.00),
('Load & Bills',  '#b8860b', 800.00),
('Leisure',       '#2f7d5b', 1000.00);

INSERT INTO expenses (title, category_id, amount, expense_date, payment_method, notes) VALUES
('Lunch at canteen',      1,  85.00, CURDATE(),                         'Cash',   NULL),
('Jeepney fare',          2,  26.00, CURDATE(),                         'Cash',   'Going to school'),
('Printing requirements', 3, 120.00, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'GCash',  NULL),
('Mobile load',           4, 100.00, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'GCash',  'Promo load'),
('Milk tea with friends', 1, 130.00, DATE_SUB(CURDATE(), INTERVAL 3 DAY), 'Maya',   NULL),
('Movie ticket',          5, 250.00, DATE_SUB(CURDATE(), INTERVAL 4 DAY), 'Cash',   NULL),
('Tricycle fare',         2,  40.00, DATE_SUB(CURDATE(), INTERVAL 5 DAY), 'Cash',   NULL);
