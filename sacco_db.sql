
CREATE DATABASE IF NOT EXISTS sacco_db;
USE sacco_db;
CREATE TABLE repayments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    loan_id INT,
    amount DECIMAL(10,2),
    repayment_date DATE,
    FOREIGN KEY (loan_id) REFERENCES loans(id)
);





CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    role ENUM('Admin', 'Staff', 'Member') NOT NULL
);

INSERT INTO users (username, password, role) VALUES
('admin', 'admin123', 'Admin'),
('staff1', 'staff123', 'Staff'),
('member1', 'member123', 'Member');

CREATE TABLE loans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    member_name VARCHAR(100),
    amount DECIMAL(10,2),
    loan_date DATE
);


CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) UNIQUE,
    value VARCHAR(50)
);

-- Insert default interest rate
INSERT INTO settings (name, value) VALUES ('interest_rate', '12.00');


CREATE DATABASE IF NOT EXISTS saccopos;
USE saccopos;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50),
  password VARCHAR(255),
  role ENUM('Admin','Staff','Member')
);



-- Create the database
CREATE DATABASE IF NOT EXISTS vov_sacco;
USE vov_sacco;

-- Create the loans table
CREATE TABLE IF NOT EXISTS loans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT NOT NULL,
    loan_amount DECIMAL(10,2) NOT NULL,
    interest_rate DECIMAL(5,2) NOT NULL,
    loan_date DATE NOT NULL,
    due_date DATE NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'Pending'
);

-- Insert 10 sample loan records
INSERT INTO loans (member_id, loan_amount, interest_rate, loan_date, due_date, status) VALUES
(1, 5000.00, 10.00, '2025-01-10', '2025-07-10', 'Approved'),
(2, 7000.00, 8.50, '2025-02-15', '2025-08-15', 'Pending'),
(3, 12000.00, 11.00, '2025-03-01', '2025-09-01', 'Approved'),
(4, 3000.00, 9.00, '2025-03-20', '2025-09-20', 'Rejected'),
(5, 10000.00, 10.50, '2025-04-05', '2025-10-05', 'Approved'),
(6, 8000.00, 9.50, '2025-04-25', '2025-10-25', 'Approved'),
(7, 15000.00, 12.00, '2025-05-10', '2025-11-10', 'Pending'),
(8, 2000.00, 7.00, '2025-06-01', '2025-12-01', 'Approved'),
(9, 9500.00, 10.00, '2025-06-15', '2025-12-15', 'Approved'),
(10, 4000.00, 8.00, '2025-07-01', '2026-01-01', 'Pending');



-- Create the database (if not already created)
CREATE DATABASE IF NOT EXISTS vov_sacco;
USE vov_sacco;

-- Create summary table
CREATE TABLE IF NOT EXISTS loan_accounts_summary (
    id INT AUTO_INCREMENT PRIMARY KEY,
    member_name VARCHAR(100) NOT NULL,
    loan_amount DECIMAL(10,2) NOT NULL,
    issued_date DATE NOT NULL,
    interest_per_month DECIMAL(10,2) NOT NULL,
    monthly_installment DECIMAL(10,2) NOT NULL,
    total_repayments DECIMAL(10,2) NOT NULL,
    loan_balance DECIMAL(10,2) NOT NULL
);

-- Insert 10 sample loan records
INSERT INTO loan_accounts_summary (member_name, loan_amount, issued_date, interest_per_month, monthly_installment, total_repayments, loan_balance) VALUES
('John Mwangi', 10000.00, '2025-01-01', 250.00, 1250.00, 6250.00, 3750.00),
('Mary Wanjiku', 8000.00, '2025-02-15', 200.00, 1000.00, 3000.00, 5000.00),
('Peter Otieno', 15000.00, '2025-03-10', 375.00, 1875.00, 7500.00, 7500.00),
('Grace Achieng', 5000.00, '2025-04-05', 125.00, 625.00, 2500.00, 2500.00),
('David Kamau', 12000.00, '2025-05-20', 300.00, 1500.00, 4500.00, 7500.00),
('Nancy Njeri', 7000.00, '2025-06-01', 175.00, 875.00, 3500.00, 3500.00),
('Samuel Kiptoo', 9000.00, '2025-06-20', 225.00, 1125.00, 2250.00, 6750.00),
('Beatrice Chebet', 11000.00, '2025-07-01', 275.00, 1375.00, 1375.00, 9625.00),
('James Omondi', 6000.00, '2025-07-05', 150.00, 750.00, 1500.00, 4500.00),
('Jane Wambui', 13000.00, '2025-07-10', 325.00, 1625.00, 0.00, 13000.00);


INSERT INTO users (username, password, role) VALUES
('admin', 'admin123', 'Admin'),
('staff1', 'staff123', 'Staff'),
('member1', 'member123', 'Member'),
('member2', 'member123', 'Member'),
('member3', 'member123', 'Member'),
('member4', 'member123', 'Member'),
('member5', 'member123', 'Member'),
('staff2', 'staff123', 'Staff'),
('admin2', 'admin123', 'Admin'),
('member6', 'member123', 'Member');

-- Add tables for: members, savings, loans, repayments, expenses, settings
-- (I can send all full SQL CREATE/INSERTs in a downloadable file once the tool is ready)

