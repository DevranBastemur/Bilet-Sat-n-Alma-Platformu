CREATE TABLE "User" (
    "id"	TEXT NOT NULL UNIQUE,
    "full_name"	TEXT NOT NULL,
    "email"	TEXT NOT NULL UNIQUE,
    "password"	TEXT NOT NULL,
    "role"	TEXT NOT NULL DEFAULT 'kullanici',
    "balance"	REAL NOT NULL DEFAULT 0,
    "company_id"	TEXT,
    "created_at"	TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY("id"),
    FOREIGN KEY("company_id") REFERENCES "Bus_Company"("id")
);

CREATE TABLE "Trips" (
    "id"	TEXT NOT NULL UNIQUE,
    "company_id"	TEXT NOT NULL,
    "departure_city"	TEXT NOT NULL,CREATE TABLE "User" (
    "id"	TEXT NOT NULL UNIQUE,
    "full_name"	TEXT NOT NULL,
    "email"	TEXT NOT NULL UNIQUE,
    "password"	TEXT NOT NULL,
    "role"	TEXT NOT NULL DEFAULT 'kullanici',
    "balance"	REAL NOT NULL DEFAULT 0,
    "company_id"	TEXT,
    "created_at"	TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY("id"),
    FOREIGN KEY("company_id") REFERENCES "Bus_Company"("id")
);

CREATE TABLE "Trips" (
    "id"	TEXT NOT NULL UNIQUE,
    "company_id"	TEXT NOT NULL,
    "departure_city"	TEXT NOT NULL,
    "destination_city"	TEXT NOT NULL,
    "departure_time"	DATETIME NOT NULL,
    "arrival_time"	DATETIME NOT NULL,
    "price"	REAL NOT NULL,
    "capacity"	INTEGER NOT NULL,
    PRIMARY KEY("id"),
    FOREIGN KEY("company_id") REFERENCES "Bus_Company"("id")
);

CREATE TABLE "Tickets" (
    "id"	TEXT,
    "trip_id"	TEXT NOT NULL,
    "user_id"	TEXT NOT NULL,
    "status"	TEXT NOT NULL DEFAULT 'active',
    "total_price"	INTEGER NOT NULL,
    "created_at"	TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "coupon_code"	VARCHAR(255) NULL,
    PRIMARY KEY("id"),
    FOREIGN KEY("trip_id") REFERENCES "Trips"("id"),
    FOREIGN KEY("user_id") REFERENCES "User"("id")
);

CREATE TABLE "Coupons" (
    "id"	TEXT,
    "code"	TEXT NOT NULL,
    "discount"	REAL NOT NULL,
    "usage_limit"	INTEGER NOT NULL,
    "expire_date"	DATETIME NOT NULL,
    "created_at"	TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "company_id"	TEXT,
    PRIMARY KEY("id"),
    FOREIGN KEY("company_id") REFERENCES "Bus_Company"("id")
);

CREATE TABLE "Bus_Company" (
    "id"	TEXT NOT NULL UNIQUE,
    "name"	TEXT NOT NULL,
    "logo_path"	TEXT,
    "created_at"	TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY("id")
);

CREATE TABLE "booked_seats" (
    "id"	TEXT NOT NULL UNIQUE,
    "ticket_id"	TEXT NOT NULL,
    "seat_number"	INTEGER NOT NULL,
    PRIMARY KEY("id"),
    FOREIGN KEY("ticket_id") REFERENCES "Tickets"("id")
);

    "destination_city"	TEXT NOT NULL,
    "departure_time"	DATETIME NOT NULL,
    "arrival_time"	DATETIME NOT NULL,
    "price"	REAL NOT NULL,
    "capacity"	INTEGER NOT NULL,
    PRIMARY KEY("id"),
    FOREIGN KEY("company_id") REFERENCES "Bus_Company"("id")
);

CREATE TABLE "Tickets" (
    "id"	TEXT,
    "trip_id"	TEXT NOT NULL,
    "user_id"	TEXT NOT NULL,
    "status"	TEXT NOT NULL DEFAULT 'active',
    "total_price"	INTEGER NOT NULL,
    "created_at"	TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "coupon_code"	VARCHAR(255) NULL,
    PRIMARY KEY("id"),
    FOREIGN KEY("trip_id") REFERENCES "Trips"("id"),
    FOREIGN KEY("user_id") REFERENCES "User"("id")
);

CREATE TABLE "Coupons" (
    "id"	TEXT,
    "code"	TEXT NOT NULL,
    "discount"	REAL NOT NULL,
    "usage_limit"	INTEGER NOT NULL,
    "expire_date"	DATETIME NOT NULL,
    "created_at"	TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "company_id"	TEXT,
    PRIMARY KEY("id"),
    FOREIGN KEY("company_id") REFERENCES "Bus_Company"("id")
);

CREATE TABLE "Bus_Company" (
    "id"	TEXT NOT NULL UNIQUE,
    "name"	TEXT NOT NULL,
    "logo_path"	TEXT,
    "created_at"	TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY("id")
);

CREATE TABLE "booked_seats" (
    "id"	TEXT NOT NULL UNIQUE,
    "ticket_id"	TEXT NOT NULL,
    "seat_number"	INTEGER NOT NULL,
    PRIMARY KEY("id"),
    FOREIGN KEY("ticket_id") REFERENCES "Tickets"("id")
);
