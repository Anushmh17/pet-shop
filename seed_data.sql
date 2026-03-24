-- Pet Shop Proposal Seed Data
-- Import this to make the dashboard look spectacular for your proposal.

USE petshop_db;

-- Clear old data first if you want a fresh start
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE pet_images;
TRUNCATE TABLE sales;
TRUNCATE TABLE drawer;
TRUNCATE TABLE pets;
SET FOREIGN_KEY_CHECKS = 1;

-- 1. SEED PETS (With Breeds/Varieties)
INSERT INTO pets (id, name, category, pet_variety, source, type, qty, price, cost, icon, alert_level)
VALUES 
(1, 'Labrador', 'dog', 'Chocolate Hunter', 'Dealer Supplied', 'Single', 3, 15000.00, 12000.00, '🐶', 10),
(2, 'Siamese Cat', 'cat', 'Royal Blue Point', 'Customer Supplied', 'Single', 2, 9500.00, 7000.00, '🐱', 5),
(3, 'Indian Fantail', 'bird', 'Pigeon', 'Dealer Supplied', 'Pair/Couple', 8, 2500.00, 1800.00, '🕊️', 10),
(4, 'Red Cap Oranda', 'fish', 'Goldfish', 'Dealer Supplied', 'Single', 25, 450.00, 300.00, '🐠', 20),
(5, 'Sun Conure', 'bird', 'Parrot', 'Dealer Supplied', 'Single', 4, 35000.00, 30000.00, '🦜', 5),
(6, 'Dwarf Hotot', 'rabbit', 'Snow White', 'Customer Supplied', 'Single', 5, 4500.00, 3500.00, '🐰', 10),
(7, 'African Grey', 'bird', 'Parrot', 'Dealer Supplied', 'Single', 1, 85000.00, 75000.00, '🦜', 2),
(8, 'Fancy Guppy', 'fish', 'Delta Tail', 'Dealer Supplied', 'Single', 100, 150.00, 80.00, '🐟', 50),
(9, 'German Shepherd', 'dog', 'Show Line', 'Dealer Supplied', 'Single', 2, 25000.00, 20000.00, '🐕', 5);

-- 2. SEED SALES (Rich history for charts)
-- Current Month: March 2026

-- Today (Mar 24)
INSERT INTO sales (pet_id, pet_name, pet_icon, qty, price, total, sale_date)
VALUES 
(4, 'Red Cap Oranda', '🐠', 2, 450.00, 900.00, '2026-03-24'),
(1, 'Labrador', '🐶', 1, 15000.00, 15000.00, '2026-03-24');

-- Yesterday (Mar 23)
INSERT INTO sales (pet_id, pet_name, pet_icon, qty, price, total, sale_date)
VALUES 
(3, 'Indian Fantail', '🕊️', 1, 2500.00, 2500.00, '2026-03-23'),
(8, 'Fancy Guppy', '🐟', 10, 150.00, 1500.00, '2026-03-23');

-- Earlier this month
INSERT INTO sales (pet_id, pet_name, pet_icon, qty, price, total, sale_date)
VALUES 
(5, 'Sun Conure', '🦜', 1, 35000.00, 35000.00, '2026-03-15'),
(2, 'Siamese Cat', '🐱', 1, 9500.00, 9500.00, '2026-03-10'),
(6, 'Dwarf Hotot', '🐰', 1, 4500.00, 4500.00, '2026-03-05');

-- Last Month: February 2026
INSERT INTO sales (pet_id, pet_name, pet_icon, qty, price, total, sale_date)
VALUES 
(1, 'Labrador', '🐶', 1, 15000.00, 15000.00, '2026-02-28'),
(3, 'Indian Fantail', '🕊️', 2, 2500.00, 5000.00, '2026-02-20'),
(8, 'Fancy Guppy', '🐟', 20, 150.00, 3000.00, '2026-02-14'),
(4, 'Red Cap Oranda', '🐠', 10, 450.00, 4500.00, '2026-02-05');

-- 3. SEED CASH DRAWER (With logic carryover)
-- Yesterday (Mar 23)
INSERT INTO drawer (entry_date, drawer_data)
VALUES ('2026-03-23', '{
    "openingBalance": 10000.00,
    "cashIn": 4000.00,
    "cashOut": 1500.00,
    "closingBalance": 12500.00,
    "entries": [
        {"type": "Cash In", "desc": "Pet Sale - Indian Fantail", "amount": 2500},
        {"type": "Cash In", "desc": "Pet Sale - Fancy Guppy", "amount": 1500},
        {"type": "Cash Out", "desc": "Premium Bird Feed", "amount": 1000},
        {"type": "Cash Out", "desc": "Electricity Bill", "amount": 500}
    ]
}');

-- Today (Mar 24)
INSERT INTO drawer (entry_date, drawer_data)
VALUES ('2026-03-24', '{
    "openingBalance": 12500.00,
    "cashIn": 15900.00,
    "cashOut": 2000.00,
    "closingBalance": 26400.00,
    "entries": [
        {"type": "Cash In", "desc": "Pet Sale - Red Cap Oranda", "amount": 900},
        {"type": "Cash In", "desc": "Pet Sale - Labrador", "amount": 15000},
        {"type": "Cash Out", "desc": "Staff Salary Advance", "amount": 2000}
    ]
}');
