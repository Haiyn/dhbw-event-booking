CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE TABLE IF NOT EXISTS users (
    user_id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    username VARCHAR(32) UNIQUE NOT NULL,
    email VARCHAR(32) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(32),
    last_name VARCHAR(32),
    age SMALLINT,
    verification_hash VARCHAR(32) UNIQUE NOT NULL,
    verified BOOLEAN NOT NULL,
    registration_date TIMESTAMP  NOT NULL DEFAULT NOW()
);
CREATE TYPE visibility AS ENUM ('invite-only', 'public');
CREATE TABLE IF NOT EXISTS events (
    event_id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    creator_id UUID  NOT NULL REFERENCES users (user_id),
    creation_date TIMESTAMP  NOT NULL DEFAULT NOW(),
    title VARCHAR(32)  NOT NULL,
    description VARCHAR(256)  NOT NULL,
    location VARCHAR(32),
    date DATE  NOT NULL,
    time TIME,
    visibility visibility  NOT NULL,
    maximum_attendees SMALLINT,
    price NUMERIC(8, 2)
);
CREATE TYPE status AS ENUM ('invited', 'accepted');
CREATE TABLE IF NOT EXISTS bookings (
    booking_id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    event_id UUID  NOT NULL REFERENCES events (event_id),
    user_id UUID  NOT NULL REFERENCES users (user_id),
    status status NOT NULL,
    accepted_date TIMESTAMP DEFAULT NOW()
);
CREATE TABLE IF NOT EXISTS sessions (
    session_id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id UUID  NOT NULL REFERENCES users (user_id),
    ip_address VARCHAR(16) NOT NULL,
    user_agent TEXT NOT NULL
);
