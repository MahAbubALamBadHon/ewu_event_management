/* ================================
   DATABASE CREATION
================================ */
DROP DATABASE IF EXISTS ewu_event_management;
CREATE DATABASE ewu_event_management;
USE ewu_event_management;

/* ================================
   MANAGER TABLE
================================ */
CREATE TABLE manager (
    manager_id VARCHAR(5) PRIMARY KEY,
    manager_pass VARCHAR(60) NOT NULL,
    manager_name VARCHAR(100),
    manager_phone VARCHAR(20),
    manager_email VARCHAR(100)
);

/* ================================
   VENUES TABLE
================================ */
CREATE TABLE venues (
    venue_id VARCHAR(5) PRIMARY KEY,
    venue_name VARCHAR(100),
    venue_capacity INT,
    manager_id VARCHAR(5),
    venue_cost DECIMAL(12,2),
    house VARCHAR(100),
    road VARCHAR(100),
    city VARCHAR(100),
    FOREIGN KEY (manager_id) REFERENCES manager(manager_id)
);

/* ================================
   MEALS TABLE
================================ */
CREATE TABLE meals (
    meal_id VARCHAR(5) PRIMARY KEY,
    meal_name VARCHAR(100),
    meal_type VARCHAR(50),
    meal_cost DECIMAL(12,2),
    catering_company VARCHAR(100)
);

/* ================================
   EVENTS TABLE
================================ */
CREATE TABLE events (
    event_id VARCHAR(5) PRIMARY KEY,
    event_name VARCHAR(100),
    event_type ENUM('ESPORTS','PRESENTATION','PRIZE_GIVING','MEETING','SEMINAR','WORKSHOP','ENTERTAINMENT'),
    event_date DATE,
    venue_id VARCHAR(5),
    meal_id VARCHAR(5),
    guest_count INT,
    ticket_cost DECIMAL(12,2),
    FOREIGN KEY (venue_id) REFERENCES venues(venue_id),
    FOREIGN KEY (meal_id) REFERENCES meals(meal_id),
    UNIQUE (venue_id, event_date, event_type)
);

/* ================================
   CUSTOMERS TABLE
================================ */
CREATE TABLE customers (
    customer_id VARCHAR(5) PRIMARY KEY,
    customer_pass VARCHAR(60) NOT NULL,
    customer_name VARCHAR(100),
    customer_address VARCHAR(200),
    customer_contact VARCHAR(20)
);

/* ================================
   BOOKINGS TABLE
================================ */
CREATE TABLE bookings (
    booking_id VARCHAR(5) PRIMARY KEY,
    booking_date DATE,
    event_id VARCHAR(5),
    customer_id VARCHAR(5),
    total_cost DECIMAL(12,2),
    FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE CASCADE
);

/* ================================
   GUESTS TABLE
================================ */
CREATE TABLE guests (
    guest_id VARCHAR(5) PRIMARY KEY,
    guest_name VARCHAR(100),
    guest_contact VARCHAR(20),
    event_id VARCHAR(5),
    customer_id VARCHAR(5),
    FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE CASCADE
);

/* ================================
   SPONSORS TABLE
================================ */
CREATE TABLE sponsors (
    sponsor_id VARCHAR(5) PRIMARY KEY,
    sponsor_address VARCHAR(200),
    sponsor_funding DECIMAL(12,2),
    event_id VARCHAR(5),
    FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE
);

/* ================================
   CASHFLOW TABLE
================================ */
CREATE TABLE cashflow (
    payment_id VARCHAR(5) PRIMARY KEY,
    customer_id VARCHAR(5),
    food_cost DECIMAL(12,2),
    venue_cost DECIMAL(12,2),
    ticket_earning DECIMAL(12,2),
    sponsor_funding DECIMAL(12,2),
    payment_method VARCHAR(50),
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE CASCADE
);

/* ================================
   FEEDBACK TABLE
================================ */
CREATE TABLE feedback (
    feedback_id INT AUTO_INCREMENT PRIMARY KEY,
    event_id VARCHAR(5),
    customer_id VARCHAR(5),
    recommendation VARCHAR(100),
    review VARCHAR(500),
    rating INT,
    FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE CASCADE
);

/* ================================
   LOGISTICS TABLE
================================ */
CREATE TABLE logistics (
    venue_id VARCHAR(5),
    object_type VARCHAR(50),
    quantity INT,
    status VARCHAR(50),
    PRIMARY KEY (venue_id, object_type),
    FOREIGN KEY (venue_id) REFERENCES venues(venue_id) ON DELETE CASCADE
);

/* ================================
   SAMPLE DATA INSERTION
================================ */
INSERT INTO manager VALUES
('M001','admin123','Admin One','01711111111','admin@ewu.edu');

INSERT INTO venues VALUES
('V01','Blue Moon Hall',600,'M001',25000,'12A','Road 5','Dhaka');

INSERT INTO meals VALUES
('ML01','Buffet Lunch','Lunch',1200,'ABC Catering');

INSERT INTO events VALUES
('E01','FORTNITE FINALS','ESPORTS','2025-10-20','V01','ML01',100,300);

INSERT INTO customers VALUES
('C001','pass123','Sanbi','Mohammadpur','01888888888');

INSERT INTO bookings VALUES
('B01','2025-09-02','E01','C001',300);

INSERT INTO guests VALUES
('G01','Abir','01111111111','E01','C001');

INSERT INTO sponsors VALUES
('S01','XYZ Company',11000,'E01');

INSERT INTO cashflow VALUES
('P01','C001',1200,25000,300,11000,'Bkash');

INSERT INTO feedback (event_id, customer_id, recommendation, review, rating)
VALUES ('E01','C001','Recommended','Great Event',5);

INSERT INTO logistics VALUES
('V01','Projector',2,'Available');

/* ================================
   VIEWS (REPORTING)
================================ */
CREATE VIEW event_summary AS
SELECT 
    e.event_id,
    e.event_name,
    e.event_type,
    e.event_date,
    v.venue_name,
    e.ticket_cost
FROM events e
JOIN venues v ON e.venue_id = v.venue_id;

CREATE VIEW booking_details AS
SELECT 
    b.booking_id,
    c.customer_name,
    e.event_name,
    b.total_cost
FROM bookings b
JOIN customers c ON b.customer_id = c.customer_id
JOIN events e ON b.event_id = e.event_id;
