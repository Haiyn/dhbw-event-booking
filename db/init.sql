CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE TABLE IF NOT EXISTS users (
    user_id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    username VARCHAR(32) UNIQUE NOT NULL,
    email VARCHAR(32) UNIQUE NOT NULL,
    password VARCHAR(32) NOT NULL,
    first_name VARCHAR(32),
    last_name VARCHAR(32),
    age SMALLINT,
    verification_hash VARCHAR(32),
    verified BOOLEAN,
    registration_date TIMESTAMP DEFAULT NOW()
);
CREATE TYPE visibility AS ENUM ('invite-only', 'public');
CREATE TABLE IF NOT EXISTS events (
    event_id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    creator_id UUID REFERENCES users (user_id),
    creation_date TIMESTAMP DEFAULT NOW(),
    title VARCHAR(32),
    description VARCHAR(256),
    location VARCHAR(32),
    date DATE,
    time TIME,
    visibility visibility,
    maximum_attendees SMALLINT,
    price NUMERIC(8, 2)
);
CREATE TYPE status AS ENUM ('invited', 'accepted');
CREATE TABLE IF NOT EXISTS bookings (
    booking_id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    event_id UUID REFERENCES events (event_id),
    user_id UUID REFERENCES users (user_id),
    status status,
    accepted_date TIMESTAMP DEFAULT NOW()
);
CREATE TABLE IF NOT EXISTS sessions (
    session_id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id UUID REFERENCES users (user_id),
    login_time TIME,
    ip_address VARCHAR(16),
    user_agent TEXT
);